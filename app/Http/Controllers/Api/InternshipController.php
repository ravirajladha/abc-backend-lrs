<?php

namespace App\Http\Controllers\Api;

use App\Models\Student;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;
use App\Models\{Internship, InternshipCertificate, InternshipTask, InternshipSubmission,ElabSubmission};

class InternshipController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getInternships()
{
    
    $internships = DB::table('internships as mp')
        ->select(
            'mp.id',
            'mp.name',
            'mp.description',
            'mp.image',
            'mp.is_active',
            'mp.subject_id',
            'subject.name as subject_name',
            // 'mp.subject_id',
            // 's.name as subject_name',
            DB::raw('IFNULL(mps.participant_count, 0) as participant_count'),
            DB::raw('COALESCE(COUNT(it.id), 0) as task_count') // Count of internship_tasks
        )
        // ->leftJoin('subjects as s', 'mp.subject_id', 's.id')
        ->leftJoin('subjects as subject', 'mp.subject_id', 'subject.id')
        ->leftJoin(DB::raw('(SELECT internship_id, COUNT(student_id) AS participant_count FROM internship_certificates GROUP BY internship_id) as mps'), 'mp.id', '=', 'mps.internship_id')
        ->leftJoin('internship_tasks as it', 'mp.id', '=', 'it.internship_id')
        ->groupBy('mp.id', 'mp.name', 'mp.description', 'mp.image', 'mp.is_active', 'mp.subject_id', 'subject.name','mps.participant_count') // Group by all non-aggregated columns
        ->get();

    return $this->sendResponse(['internships' => $internships]);
}
//     public function getInternshipsForStudent(Request $request)
// {
//     // dd($request->all());
//     Log::info(['student id', $request->studentId]);
  
//     $internships = DB::table('internships as mp')
//         ->select(
//             'mp.id',
//             'mp.name',
//             'mp.description',
//             'mp.image',
//             'mp.is_active',
//             'mp.class_id',
//             'class.name as class_name',
//             // 'mp.subject_id',
//             // 's.name as subject_name',
//             DB::raw('IFNULL(mps.participant_count, 0) as participant_count'),
//             DB::raw('COALESCE(COUNT(it.id), 0) as task_count') // Count of internship_tasks
//         )
//         // ->leftJoin('subjects as s', 'mp.subject_id', 's.id')
//         ->leftJoin('classes as class', 'mp.class_id', 'class.id')
//         ->leftJoin(DB::raw('(SELECT internship_id, COUNT(student_id) AS participant_count FROM internship_certificates GROUP BY internship_id) as mps'), 'mp.id', '=', 'mps.internship_id')
//         ->leftJoin('internship_tasks as it', 'mp.id', '=', 'it.internship_id')
//         ->groupBy('mp.id', 'mp.name', 'mp.description', 'mp.image', 'mp.is_active', 'mp.class_id', 'class.name','mps.participant_count') // Group by all non-aggregated columns
//         ->get();

//     return $this->sendResponse(['internships' => $internships]);
// }

public function getInternshipsForStudent(Request $request)
{

    $studentId = $request->studentId;

    $internships = DB::table('internships as mp')
        ->select(
            'mp.id',
            'mp.name',
            'mp.description',
            'mp.image',
            'mp.is_active',
            'mp.subject_id',
            'subject.name as subject_name',
            DB::raw('IFNULL(mps.participant_count, 0) as participant_count'),
            DB::raw('COALESCE(COUNT(it.id), 0) as task_count'),
            DB::raw('CASE WHEN ic.certificate IS NOT NULL THEN "completed" ELSE "incomplete" END as status') // Internship status based on certificate
        )
        ->leftJoin('subjects as subject', 'mp.subject_id', 'subject.id')
        ->leftJoin(DB::raw('(SELECT internship_id, COUNT(student_id) AS participant_count FROM internship_certificates GROUP BY internship_id) as mps'), 'mp.id', '=', 'mps.internship_id')
        ->leftJoin('internship_tasks as it', 'mp.id', '=', 'it.internship_id')
        ->leftJoin('internship_certificates as ic', function($join) use ($studentId) {
            $join->on('mp.id', '=', 'ic.internship_id')
                 ->where('ic.student_id', '=', $studentId);
        })
        ->groupBy('mp.id', 'mp.name', 'mp.description', 'mp.image', 'mp.is_active', 'mp.subject_id', 'subject.name', 'mps.participant_count', 'ic.certificate')
        ->get();
        Log::info(['internshgip detail id', $internships]);

    return $this->sendResponse(['internships' => $internships]);
}

    

    public function getInternshipTasksTaskProcesses($projectId, $status)
    {
        // Fetch  internship  details
        $internship = DB::table('internships')
            ->select('name')
            ->where('id', $projectId)
            ->first();
        $internshipTaskProcesses = DB::table('internship_submissions')
            ->where('id', $projectId)
            ->where('status', $status)
            ->get();

        // Fetch internship tasks


        return $this->sendResponse(['internship' => $internship, 'internshipTaskProcesses' => $internshipTaskProcesses]);
    }

    
    public function getInternshipDetails($internshipId)
    {
        try {
            // Retrieve the mini project details using a join query
            $internship = Internship::select(
                'internships.*',
                'subjects.name as subject_name',
                // 'subjects.name as subject_name'
            )
                ->leftJoin('subjects', 'internships.subject_id', '=', 'subjects.id')
                // ->leftJoin('subjects', 'internships.subject_id', '=', 'subjects.id')
                ->findOrFail($internshipId);

            // If the mini project is found, return the details
            return $this->sendResponse(['miniProject' => $internship]);
        } catch (\Exception $e) {
            // Handle exceptions or errors
            return $this->sendResponse(['error' => $e->getMessage()]);
        }
    }

    public function getAllInternshipTasksByProjectId($internshipId)
    {
        // Fetch internship details
        $internship = DB::table('internships')
            ->select('name')
            ->where('id', $internshipId)
            ->first();

            $internship_tasks = DB::table('internship_tasks')
            ->select('internship_tasks.id', 'internship_tasks.name', 'internship_tasks.description', 'internship_tasks.elab_id', 'elabs.title as elab_name', 'internship_tasks.is_active') // Adjust these columns as needed
            ->leftJoin('elabs', 'internship_tasks.elab_id', '=', 'elabs.id')
            ->where('internship_tasks.internship_id', $internshipId)
            ->get();

        return $this->sendResponse([
            'internship' => $internship,
            'internship_tasks' => $internship_tasks
        ]);
    }


    public function getInternshipTasksByProjectId($internshipId, $studentId)
    {
        // Fetch internship details
        $internship = DB::table('internships')
            ->select('name')
            ->where('id', $internshipId)
            ->first();

        $internshipTasks = DB::table('internship_tasks as mpt')
            ->select('mpt.id', 'mpt.name as internship_task_name', 'mpt.description', 'mpt.elab_id', 'elab.title as elab_title', 'mps.status','mps.elab_submission_id','mps.created_at as code_submitted_at', 'mps.id as submission_id')
            ->leftJoin('elabs as elab', 'mpt.elab_id', 'elab.id')
            ->leftJoin('internship_submissions as mps', function ($join) use ($internshipId, $studentId) {
                $join->on('mpt.id', '=', 'mps.internship_task_id')
                    ->where('mps.internship_id', '=', $internshipId)
                    ->where('mps.student_id', '=', $studentId);
            })
            ->where('mpt.internship_id', '=', $internshipId)
            ->where('mpt.is_active', 1) // Filter tasks where is_active is equal to 1
            ->get();


        $certificateGenerated = DB::table('internship_certificates')
            ->where('student_id', $studentId)
            ->where('internship_id', $internshipId)
            ->where('status', 1)
            ->first();

        return $this->sendResponse(['internship' => $internship, 'internshipTasks' => $internshipTasks,  'certificateGenerated' => $certificateGenerated]);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeInternshipDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'subject' => 'required|exists:subjects,id',
            // 'subject' => 'required|exists:subjects,id',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $internship = new Internship();
            $internship->name = $request->name;
            $internship->description = $request->description;
            $internship->subject_id = $request->subject;
            // $internship->subject_id = $request->subject;

            if (!empty($request->file('image'))) {
                $extension = $request->file('image')->extension();
                $filename = Str::random(4) . time() . '.' . $extension;
                $internship->image = $request->file('image')->move(('uploads/images/internship'), $filename);
            } else {
                $internship->image = null;
            }
            if ($internship->save()) {
                return $this->sendResponse([], 'Internship created successfully.');
            } else {
                return $this->sendError([], 'Internship could not created.');
            }
        }
    }
    /**
     * Store a newly created Internship task resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeInternshipTaskDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'elabId' => 'required|exists:elabs,id',
            'internshipId' => 'required|exists:internships,id',

        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $internship = new InternshipTask();
            $internship->name = $request->name;
            $internship->elab_id = $request->elabId;
            $internship->internship_id = $request->internshipId;
            $internship->description = $request->description;

            if ($internship->save()) {
                return $this->sendResponse([], 'Internship task created successfully.');
            } else {
                return $this->sendError([], 'Internship task could not created.');
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    public function startStudentInternship(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'internshipTaskId' => 'required|integer',
            'studentId' => 'required|integer',
            'elabId' => 'required|integer',
            'internshipId' => 'required|integer',
            // Add more validation rules if needed
        ]);
    
        try {
            $internshipStudent = InternshipCertificate::where('student_id', $validatedData['studentId'])
            ->where('internship_id', $validatedData['internshipId'])
            ->first();

        if (!$internshipStudent) {
            // Create a new InternshipCertificate if it doesn't exist
            $internshipStudent = InternshipCertificate::create([
                'student_id' => $validatedData['studentId'],
                'internship_id' => $validatedData['internshipId'],
                'start_datetime' => now(),
            ]);
        }

            // Check if an existing InternshipSubmission record exists for the given criteria
            $existingSubmission = InternshipSubmission::where('student_id', $validatedData['studentId'])
                ->where('internship_id', $validatedData['internshipId'])
                ->where('internship_task_id', $validatedData['internshipTaskId'])
                ->first();
    
            if ($existingSubmission) {
                // Update the existing submission
                // $existingSubmission->elab_id = $validatedData['elabId'];

             
                // $existingSubmission->save();
    
                // Return a success response indicating the submission was updated
                return $this->sendResponse(['submission_id' => $existingSubmission->id], 'Internship submission updated successfully.');
            } else {
                // Create a new InternshipSubmission instance
                $submission = new InternshipSubmission();
                $submission->internship_task_id = $validatedData['internshipTaskId'];
                $submission->student_id = $validatedData['studentId'];
                $submission->elab_id = $validatedData['elabId'];
                $submission->internship_id = $validatedData['internshipId'];
                $submission->internship_certificate_id = $internshipStudent->id;
                $submission->status = 1;
                // Add more fields if needed
      
                // Save the new submission
                $submission->save();
    
                // Return a success response indicating the submission was created
                return $this->sendResponse(['submission_id' => $submission->id], 'Internship submission created successfully.');
            }
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return $this->sendResponse([], 'Failed to start Internship');
        }
    }


    
   
    public function generateCertificate(Request $request)
    {

        $userId = $request->input('studentId');
        $internshipId = $request->input('internshipId');
    
        // Fetch all mini project tasks for the given miniProjectId
        $internshipTasks = DB::table('internship_tasks')
            ->select('id')
            ->where('internship_id', $internshipId)
            ->where('is_active', 1)
            ->get();
    
        // Fetch internship submissions for the specified userId and miniProjectId
        $submittedTasks = DB::table('internship_submissions')
            ->whereIn('internship_task_id', $internshipTasks->pluck('id'))
            ->where('student_id', $userId)
            ->pluck('internship_task_id')
            ->toArray();
    
        // Check if all tasks have submissions
        $allTasksSubmitted = count($internshipTasks) === count($submittedTasks);



        $userId = $request->input('studentId');
        $internshipId = $request->input('internshipId');

        // Fetch student and internship details from the database
        $student = Student::where('auth_id', $userId)->first();
        $internship = Internship::find($internshipId);

        $certificate = InternshipCertificate::where('student_id', $userId)
        ->where('internship_id', $internshipId)
        ->first();
        
        if (!$student || !$internship || !$allTasksSubmitted || !$certificate) {
            // return response()->json(['error' => 'Student or Internship not found or Internship task not completed'], 404);
            return $this->sendResponse([], 'Student or Internship not found or Internship task not completed',status:true);

        }


        // Check if a certificate already exists for the student and internship
     
            // $certificate =      InternshipCertificate::where('student_id', $userId)
            //     ->where('internship_id', $internshipId)
            //     ->update(['status' => true]);


        // Unique date and time for file naming
        $unqdate = date("Ymd");
        $unqtime = time();
        $new_file_name = $userId . "-" . $internshipId . "-" . $unqdate . "" . $unqtime . ".jpg";

        // Prepare the text content
        $name = $student->name;
       
        $today = date("Y-m-d");
        $formattedDate = date("d-m-y", strtotime($today));

        $data_and_place = $formattedDate;
        $internshipName = $internship->name;

        // Load the base image
        $file_name = 'pass/certificate.jpg';
        $img_source = imagecreatefromjpeg($file_name);

        // Font and color settings
        $font = 'fonts/ARIBL0.ttf';
        $text_color = imagecolorallocate($img_source, 0, 0, 0);

        // Place the student name onto the image
        // Calculate the width of the text
        $nameBoundingBox = imagettfbbox(30, 0, $font, $name);
        $nameWidth = $nameBoundingBox[4] - $nameBoundingBox[0];
        // Adjust the x-coordinate to center horizontally
        $nameX = (2000 - $nameWidth) / 2;
        // Place the text
        imagettftext($img_source,42, 0, $nameX, 635, $text_color, $font, $name);

        // Place the internship name onto the image
        // Calculate the width of the text
        $internshipNameBoundingBox = imagettfbbox(30, 0, $font, $internshipName);
        $internshipNameWidth = $internshipNameBoundingBox[4] - $internshipNameBoundingBox[0];
        // Adjust the x-coordinate to center horizontally
        $internshipNameX = (2000 - $internshipNameWidth) / 2;
        // Place the text
        imagettftext($img_source, 30, 0, $internshipNameX, 920, $text_color, $font, $internshipName);
        // Place the date and place onto the image
        imagettftext($img_source, 20, 0, 1295, 1067, $text_color, $font, $data_and_place);

        // Save the new image
        ImageJpeg($img_source, 'uploads/pass/' . $new_file_name);
        // imagedestroy($img_source); // Free up memory

        $filePath = 'uploads/pass/' . $new_file_name;

        if ($certificate) {
            // Certificate already exists, update its fields
            $filePath = 'uploads/pass/' . $userId . "-" . $internshipId . "-" . date("Ymd") . time() . ".jpg";
            $certificate->certificate = $filePath;
            $certificate->status  = true;
            $certificate->end_datetime = now();
            $certificate->save();
            return $this->sendResponse([], 'Certificate updated successfully.');
        } else {
            // Certificate doesn't exist, create a new one
            //need to check 
            $filePath = 'uploads/pass/' . $userId . "-" . $internshipId . "-" . date("Ymd") . time() . ".jpg";

            $submission = new InternshipCertificate();
            $submission->student_id = $userId;
            $certificate->status  = true;
            $certificate->end_datetime = now();

            $submission->certificate =  $filePath;
            $submission->internship_id = $internshipId;
            $submission->save();
            return $this->sendResponse([], 'Certificate created successfully.');
        }
    }

      /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MiniProject  $miniProject
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $internship)

    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:50',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $internship = Internship::find($internship);

        if (!$request->has('name')) {
            return $this->sendError('Mini Project name is required.');
        }

        $internship->name = $request->name;
        $internship->description = $request->description;
        $internship->is_active = $request->is_active;

        if ($request->hasFile('image')) {
            // Delete the previous image if it exists
            if ($internship->image) {
                File::delete(public_path($internship->image));
            }
            $image = $request->file('image');
            $extension = $image->extension();
            $filename = Str::random(4) . time() . '.' . $extension;
            $image->move(public_path('uploads/images/mini-project'), $filename);
            $internship->image = 'uploads/images/mini-project/' . $filename;
        }
        $internship->save();

        return $this->sendResponse(['internship' => $internship], 'Internship updated successfully');
    }

    public function getInternshipTasksById($internshipTaskId)
    {
        try {
            // Retrieve the mini project details using a join query
            $internshipTask = InternshipTask::select(
                'internship_tasks.*',
                'elabs.title as elab_name'
            )
                ->leftJoin('elabs', 'internship_tasks.elab_id', '=', 'elabs.id')
                ->findOrFail($internshipTaskId);

            // If the internship is found, return the details
            return $this->sendResponse(['internshipTask' => $internshipTask]);
        } catch (\Exception $e) {
            // Handle exceptions or errors
            return $this->sendResponse(['error' => $e->getMessage()]);
        }
    }

       /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MiniProjectTask  $miniProjectTask
     * @return \Illuminate\Http\Response
     */
    public function updateTask(Request $request, $internshipTaskId)

    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:150',
            'elabId' => 'required|max:50',

        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $internshipTask = InternshipTask::find($internshipTaskId);

        if (!$request->has('name')) {
            return $this->sendError('InternshipTask name is required.');
        }

        $internshipTask->name = $request->name;
        $internshipTask->elab_id = $request->elabId;
        $internshipTask->description = $request->description;
        $internshipTask->is_active = $request->is_active;
        $internshipTask->save();

        return $this->sendResponse(['internshipTask' => $internshipTask], 'Mini Project updated successfully');
    }

    public function getInternshipParticipants($internshipId)
    {
        
            $students = DB::table('internship_certificates')
            ->join('students', 'internship_certificates.student_id', '=', 'students.auth_id')
            ->select('internship_certificates.student_id', 'students.name', 'internship_certificates.id as id', 'internship_certificates.certificate')
            ->where('internship_certificates.internship_id', $internshipId)
            ->get();
            // Fetch all tasks for the given mini project
            $tasks = DB::table('internship_tasks')
                ->select('id', 'name')
                ->where('internship_id', $internshipId)
                ->get();
        
            // Initialize an array to hold student-task associations
            $data = [];
        
            // Iterate through each student
            foreach ($students as $student) {
                // Initialize an array to hold task presence for the current student
                $taskPresence = [];
        
                // Iterate through each task
                foreach ($tasks as $task) {
                    // Check if the current task is present for the current student
                    $submission = DB::table('internship_submissions')
                        ->where('student_id', $student->student_id)
                        ->where('internship_task_id', $task->id)
                        ->first();
        
                    // Determine the task presence status and elab_id
                    if ($submission) {
                        // Case: Task is present
                        $status = $submission->status ==2 ? 'Completed' : 'Pending';
                        $elab_submission_id = $submission->status ==2 ? $submission->elab_submission_id : null;
                        $elabId = $submission->elab_id;
                    } else {
                        // Case: Task is not present
                        $status = 'Not Present';
                        $elabId = null;
                        $elab_submission_id = null;
                    }
        
                    // Add task presence status and elab_id to the array
                    $taskPresence[] = [
                        'status' => $status,
                        'elab_submission_id' => $elab_submission_id,
                        'elab_id' => $elabId,
                    ];
                }
        
                // Add student-task association to the data array
                $data[] = [
                  
                    'student_id' => $student->student_id,
                    'student_name' => $student->name,
                    'task_presence' => $taskPresence,
                    'id' => $student->id,
                    'certificate' => $student->certificate,
                ];
            }
    
        return $this->sendResponse(['data' => $data]);
    

}

// public function deleteInternshipParticipant(Request $request,$internshipStudentId)
//     {
//         try {
//             // Find the mini project student record
//             $internshipCertificate = InternshipCertificate::findOrFail($internshipStudentId);
            

//              // Retrieve MiniProjectSubmission records and corresponding details
//              $submissions = InternshipSubmission::where('internship_certificate_id', $internshipStudentId)->get();
//              $details = [];
     
//              // Extract the details needed from each submission
//              foreach ($submissions as $submission) {
//                  $detail = [
//                      'internship_id' => $submission->internship_id,
//                      'internship_task_id' => $submission->internship_task_id,
//                      'student_id' => $submission->student_id,
//                  ];
//                  $details[] = $detail;
     
//                  // Delete ElabSubmission records using the extracted details
//                  ElabSubmission::where('internship_id', $submission->internship_id)
//                               ->where('internship_task_id', $submission->internship_task_id)
//                               ->where('student_id', $submission->student_id)
//                               ->delete();
//              }
     
//              // Delete MiniProjectSubmission records
//              InternshipSubmission::where('internship_certificate_id', $internshipStudentId)->delete();

//             // Delete the record
//             $internshipCertificate->delete();
            
//             // Return a success response
//             return $this->sendResponse([], 'Internship participant deleted successfully');
         
//         } catch (\Exception $e) {
//             // Return an error response if deletion fails
//             return response()->json(['message' =>  'Failed to delete Internship participant']);
//         }
//     }

public function deleteInternshipParticipant(Request $request, $internshipStudentId)
{
    Log::info('Starting deletion process for internship participant', ['internshipStudentId' => $internshipStudentId]);

    try {
        // Find the mini project student record
        $internshipCertificate = InternshipCertificate::findOrFail($internshipStudentId);
        Log::info('Internship Certificate found', ['internshipCertificate' => $internshipCertificate]);

        // Retrieve InternshipSubmission records and corresponding details
        $submissions = InternshipSubmission::where('internship_certificate_id', $internshipStudentId)->get();
        $details = [];
 
        if ($submissions->isEmpty()) {
            Log::info('No submissions found for this internship certificate');
        }

        // Extract the details needed from each submission
        foreach ($submissions as $submission) {
            $detail = [
                'internship_id' => $submission->internship_id,
                'internship_task_id' => $submission->internship_task_id,
                'student_id' => $submission->student_id,
            ];
            $details[] = $detail;
 
            Log::info('Deleting InternshipSubmission records', ['internship_certificate_id' => $internshipStudentId]);
            InternshipSubmission::where('internship_certificate_id', $internshipStudentId)->delete();
            
            // Delete ElabSubmission records using the extracted details
            Log::info('Deleting ElabSubmission records', ['detail' => $detail]);
            ElabSubmission::where('internship_id', $submission->internship_id)
                         ->where('internship_task_id', $submission->internship_task_id)
                         ->where('student_id', $submission->student_id)
                         ->delete();
        }
 
        // Delete InternshipSubmission records
    

        // Delete the record
        $internshipCertificate->delete();
        Log::info('Internship Certificate deleted successfully');
        
        // Return a success response
        return $this->sendResponse([], 'Internship participant deleted successfully');
     
    } catch (\Exception $e) {
        Log::error('Failed to delete internship participant', [
            'internshipStudentId' => $internshipStudentId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()  // This will log the stack trace for better error analysis
        ]);
        // Return an error response if deletion fails
        return response()->json(['message' => 'Failed to delete Internship participant']);
    }
}
public function deleteInternship(Request $request,$internshipId)
    {
        try {
            // Find the mini project student record
            $internship = Internship::findOrFail($internshipId);
            
            // Delete the record
            $internship->delete();
            
            InternshipTask::where('internship_id', $internshipId)->delete();
            InternshipSubmission::where('internship_id', $internshipId)->delete();
            InternshipCertificate::where('internship_id', $internshipId)->delete();
            ElabSubmission::where('internship_id', $internshipId)->delete();

            // Return a success response
            return $this->sendResponse([], 'Internship deleted successfully');
         
        } catch (\Exception $e) {
            // Return an error response if deletion fails
            return response()->json(['message' => 'Failed to delete Internship Task: ' . $e->getMessage()], 500);
        }
    }
public function deleteInternshipTask(Request $request,$internshipTaskId)
    {
        try {
            // Find the mini project student record
            $internshipTask = InternshipTask::findOrFail($internshipTaskId);
            
            InternshipSubmission::where('internship_task_id', $internshipTaskId)->delete();
            
            InternshipCertificate::where('internship_id', $internshipTask->internship_id)->delete();
            ElabSubmission::where('internship_task_id', $internshipTaskId)->delete();
            
            $internshipTask->delete();
            
            // Return a success response
            return $this->sendResponse([], 'Internship Task deleted successfully');
         
        } catch (\Exception $e) {
            // Return an error response if deletion fails
            // return response()->json(['message' =>  'Failed to delete Internship Task'.$e->getMessage()]);
            return response()->json(['message' => 'Failed to delete Internship Task: ' . $e->getMessage()], 500);
        }
    }
}
// public function downloadImage($id)
// {
//     $internship = Internship::findOrFail($id);
//     $imagePath = public_path($internship->project_image);

//     if (file_exists($imagePath)) {
//         $filename = basename($imagePath);
//         return response()->download($imagePath, $filename, [
//             'Content-Disposition' => 'attachment; filename="' . $filename . '"'
//         ]);
//     }

//     return response()->json(['message' => 'Image not found'], 404);
// }
