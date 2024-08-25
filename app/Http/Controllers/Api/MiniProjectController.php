<?php

namespace App\Http\Controllers\Api;

use App\Models\{MiniProject, MiniProjectTask, MiniProjectSubmission, MiniProjectStudent,ElabSubmission};
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Support\Facades\File;

class MiniProjectController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getMiniProjects()
    {
        $miniProjects = DB::table('mini_projects as mp')
            ->select(
                'mp.id',
                'mp.name',
                'mp.description',
                'mp.image',
                'mp.is_active',
                'mp.subject_id',
                'subject.name as subject_name',
                'mp.course_id',
                'c.name as course_name',
                DB::raw('IFNULL(mps.participant_count, 0) as participant_count'),
                DB::raw('COALESCE(COUNT(mpt.id), 0) as task_count') // Count of mini_project_tasks
            )
            ->leftJoin('courses as c', 'mp.course_id', 'c.id')
            ->leftJoin('subjects as subject', 'mp.subject_id', 'subject.id')
            ->leftJoin(DB::raw('(SELECT mini_project_id, COUNT(student_id) AS participant_count FROM mini_project_students GROUP BY mini_project_id) as mps'), 'mp.id', '=', 'mps.mini_project_id')
            ->leftJoin('mini_project_tasks as mpt', 'mp.id', '=', 'mpt.mini_project_id')
            ->groupBy('mp.id', 'mp.name', 'mp.description', 'mp.image', 'mp.is_active', 'mp.subject_id', 'subject.name', 'mp.course_id', 'c.name', 'mps.participant_count')  // Group by mini_project id to get count per mini_project
            ->get();

        return $this->sendResponse(['miniProjects' => $miniProjects]);
    }


    public function getMiniProjectDetails($miniProjectId)
    {
        try {
            // Retrieve the mini project details using a join query
            $miniProject = MiniProject::select(
                'mini_projects.*',
                'courses.name as course_name',
                'subjects.name as subject_name'
            )
            ->leftJoin('subjects', 'mini_projects.subject_id', '=', 'subjects.id')
            ->leftJoin('courses', 'mini_projects.course_id', '=', 'courses.id')
                ->findOrFail($miniProjectId);

            // If the mini project is found, return the details
            return $this->sendResponse(['miniProject' => $miniProject]);
        } catch (\Exception $e) {
            // Handle exceptions or errors
            return $this->sendResponse(['error' => $e->getMessage()]);
        }
    }
    public function getMiniProjectTaskProcesses($projectId, $status)
    {
        // Fetch mini project details
        $miniProject = DB::table('mini_projects')
            ->select('name')
            ->where('id', $projectId)
            ->first();
        $miniProjectTaskProcesses = DB::table('mini_project_submissions')
            ->where('id', $projectId)
            ->where('status', $status)
            ->get();

        // Fetch mini project tasks


        return $this->sendResponse(['miniProject' => $miniProject, 'miniProjectTaskProcesses' => $miniProjectTaskProcesses]);
    }


    public function getAllMiniProjectTasksByProjectId($miniProjectId)
    {
        // Fetch internship details
        $miniProject = DB::table('mini_projects')
            ->select('name')
            ->where('id', $miniProjectId)
            ->first();

        $mini_project_tasks = DB::table('mini_project_tasks')
        ->select('mini_project_tasks.id', 'mini_project_tasks.name', 'mini_project_tasks.description', 'mini_project_tasks.elab_id', 'elabs.title as elab_name', 'mini_project_tasks.is_active') // Adjust these columns as needed
        ->leftJoin('elabs', 'mini_project_tasks.elab_id', '=', 'elabs.id')
        ->where('mini_project_tasks.mini_project_id', $miniProjectId)
        ->get();

        return $this->sendResponse([
            'miniProject' => $miniProject,
            'mini_project_tasks' => $mini_project_tasks
        ]);
    }

    public function getMiniProjectTasksByProjectId($miniProjectId, $studentId)
    {
        // Fetch mini project details
        $miniProject = DB::table('mini_projects')
            ->select('name')
            ->where('id', $miniProjectId)
            ->first();

        $miniProjectTasks = DB::table('mini_project_tasks as mpt')
            ->select('mpt.id', 'mpt.name as mini_project_task_name', 'mpt.description', 'mpt.elab_id', 'elab.title as elab_title', 'mps.status', 'mps.id as submission_id', 'mps.elab_submission_id', 'mps.created_at as code_submitted_at')
            ->leftJoin('elabs as elab', 'mpt.elab_id', 'elab.id')
            ->leftJoin('mini_project_submissions as mps', function ($join) use ($miniProjectId, $studentId) {
                $join->on('mpt.id', '=', 'mps.mini_project_task_id')
                    ->where('mps.mini_project_id', '=', $miniProjectId)
                    ->where('mps.student_id', '=', $studentId);
            })
            ->where('mpt.mini_project_id', '=', $miniProjectId)
            ->where('mpt.is_active', 1) // Filter tasks where is_active is equal to 1
            ->get();

        $hasStartedMiniProject = MiniProjectStudent::where('student_id', $studentId)
            ->where('mini_project_id', $miniProjectId)
            ->where('status', 1)
            ->exists();


        return $this->sendResponse(['miniProject' => $miniProject, 'miniProjectTasks' => $miniProjectTasks,  'hasStartedMiniProject' => $hasStartedMiniProject,]);
    }

    public function getMiniProjectTasksById($miniProjectTaskId)
    {
        try {
            // Retrieve the mini project details using a join query
            $miniProjectTask = MiniProjectTask::select(
                'mini_project_tasks.*',
                'elabs.title as elab_name'
            )
                ->leftJoin('elabs', 'mini_project_tasks.elab_id', '=', 'elabs.id')
                ->findOrFail($miniProjectTaskId);

            // If the mini project is found, return the details
            return $this->sendResponse(['miniProjectTask' => $miniProjectTask]);
        } catch (\Exception $e) {
            // Handle exceptions or errors
            return $this->sendResponse(['error' => $e->getMessage()]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeMiniProjectDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'subject' => 'required|exists:subjects,id',
            'course' => 'required|exists:courses,id',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $loggedUserId = $this->getLoggedUserId();

            $miniProject = new MiniProject();
            $miniProject->name = $request->name;
            $miniProject->description = $request->description;
            $miniProject->subject_id = $request->subject;
            $miniProject->course_id = $request->course;
 
            $miniProject->created_by = $loggedUserId;
    
            if (!empty($request->file('image'))) {
                $extension = $request->file('image')->extension();
                $filename = Str::random(4) . time() . '.' . $extension;
                $miniProject->image = $request->file('image')->move(('uploads/images/mini-project'), $filename);
            } else {
                $miniProject->image = null;
            }
            if ($miniProject->save()) {
                return $this->sendResponse([], 'Mini project created successfully.');
            } else {
                return $this->sendError([], 'Mini project could not created.');
            }
        }
    }
    /**
     * Store a newly created mini project task resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeMiniProjectTaskDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'elabId' => 'required|exists:elabs,id',
            'projectId' => 'required|exists:mini_projects,id',

        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $loggedUserId = $this->getLoggedUserId();

            $miniProject = new MiniProjectTask();
            $miniProject->name = $request->name;
            $miniProject->elab_id = $request->elabId;
            $miniProject->mini_project_id = $request->projectId;
            $miniProject->description = $request->description;
            $miniProject->created_by = $loggedUserId;

            if ($miniProject->save()) {
                return $this->sendResponse([], 'Mini project task created successfully.');
            } else {
                return $this->sendError([], 'Mini project task could not created.');
            }
        }
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MiniProject  $miniProject
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $miniProject)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:50',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $miniProject = MiniProject::find($miniProject);

        if (!$request->has('name')) {
            return $this->sendError('Mini Project name is required.');
        }
        $loggedUserId = $this->getLoggedUserId();

        $miniProject->name = $request->name;
        $miniProject->description = $request->description;
        $miniProject->updated_by = $loggedUserId;
        $miniProject->is_active = $request->is_active;

        if ($request->hasFile('image')) {
            // Delete the previous image if it exists
            if ($miniProject->image) {
                File::delete(public_path($miniProject->image));
            }
            $image = $request->file('image');
            $extension = $image->extension();
            $filename = Str::random(4) . time() . '.' . $extension;
            $image->move(public_path('uploads/images/mini-project'), $filename);
            $miniProject->image = 'uploads/images/mini-project/' . $filename;
        }


        $miniProject->save();

        return $this->sendResponse(['miniProject' => $miniProject], 'Mini Project updated successfully');
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MiniProjectTask  $miniProjectTask
     * @return \Illuminate\Http\Response
     */
    public function updateTask(Request $request, $miniProjectTask)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:50',
            'elabId' => 'required|max:50',

        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $miniProjectTask = MiniProjectTask::find($miniProjectTask);

        if (!$request->has('name')) {
            return $this->sendError('Mini Project name is required.');
        }
        $loggedUserId = $this->getLoggedUserId();

        $miniProjectTask->name = $request->name;
        $miniProjectTask->elab_id = $request->elabId;
        $miniProjectTask->description = $request->description;
        $miniProjectTask->is_active = $request->is_active;
        $miniProjectTask->updated_by = $loggedUserId;

        $miniProjectTask->save();

        return $this->sendResponse(['miniProjectTask' => $miniProjectTask], 'Mini Project updated successfully');
    }

    public function startStudentMiniProject(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'miniProjectTaskId' => 'required|integer',
            'studentId' => 'required|integer',
            'elabId' => 'required|integer',
            'courseId' => 'required|integer',
            'miniProjectId' => 'required|integer',
            // Add more validation rules if needed
        ]);

        $miniProjectStudent = MiniProjectStudent::where('student_id', $validatedData['studentId'])
        ->where('subject_id', $validatedData['subjectId'])
        ->where('mini_project_id', $validatedData['miniProjectId'])
        ->first();

        if (!$miniProjectStudent) {
            // Create a new MiniProjectStudent if it doesn't exist
            $miniProjectStudent = MiniProjectStudent::create([
                'student_id' => $validatedData['studentId'],
                'course_id' => $validatedData['courseId'],
                'mini_project_id' => $validatedData['miniProjectId'],
                'start_datetime' => now(),
            ]);
        }



        // Check if an existing MiniProjectSubmission record exists for the given criteria
        $existingSubmission = MiniProjectSubmission::where('student_id', $validatedData['studentId'])
            ->where('course_id', $validatedData['courseId'])
            ->where('mini_project_id', $validatedData['miniProjectId'])
            ->where('mini_project_task_id', $validatedData['miniProjectTaskId'])
            ->first();

        if ($existingSubmission) {
            // Update the existing submission

            return $this->sendResponse(['submission_id' => $existingSubmission->id], 'Mini project submission updated successfully.');
        } else {
            // Create a new MiniProjectSubmission instance
            $submission = new MiniProjectSubmission();
            $submission->mini_project_task_id = $validatedData['miniProjectTaskId'];
            $submission->student_id = $validatedData['studentId'];
            $submission->elab_id = $validatedData['elabId'];
            $submission->course_id = $validatedData['courseId'];
            $submission->mini_project_id = $validatedData['miniProjectId'];
            $submission->mini_project_student_id = $miniProjectStudent->id;
            $submission->status = 1;
            // Add more fields if needed

            // Save the new submission
            $submission->save();

            // Return a success response indicating the submission was created
            return $this->sendResponse(['submission_id' => $submission->id], 'Mini project submission created successfully.');
        }

        // try {
        //     // Check if the combination of studentId and miniProjectId exists in mini_project_students table
        //     $miniProjectStudent = MiniProjectStudent::firstOrCreate([
        //         'student_id' => $validatedData['studentId'],
        //         'subject_id' => $validatedData['subjectId'],
        //         'mini_project_id' => $validatedData['miniProjectId'],
        //     ]);

        //     // Check if an existing MiniProjectSubmission record exists for the given criteria
        //     $existingSubmission = MiniProjectSubmission::where('student_id', $validatedData['studentId'])
        //         ->where('subject_id', $validatedData['subjectId'])
        //         ->where('mini_project_id', $validatedData['miniProjectId'])
        //         ->where('mini_project_task_id', $validatedData['miniProjectTaskId'])
        //         ->first();

        //     if ($existingSubmission) {
        //         // Update the existing submission

        //         return $this->sendResponse(['submission_id' => $existingSubmission->id], 'Mini project submission updated successfully.');
        //     } else {
        //         // Create a new MiniProjectSubmission instance
        //         $submission = new MiniProjectSubmission();
        //         $submission->mini_project_task_id = $validatedData['miniProjectTaskId'];
        //         $submission->student_id = $validatedData['studentId'];
        //         $submission->elab_id = $validatedData['elabId'];
        //         $submission->subject_id = $validatedData['subjectId'];
        //         $submission->mini_project_id = $validatedData['miniProjectId'];
        //         $submission->mini_project_student_id = $miniProjectStudent->id;
        //         $submission->status = 1;
        //         // Add more fields if needed

        //         // Save the new submission
        //         $submission->save();

        //         // Return a success response indicating the submission was created
        //         return $this->sendResponse(['submission_id' => $submission->id], 'Mini project submission created successfully.');
        //     }
        // } catch (\Exception $e) {
        //     // Return an error response if something goes wrong
        //     return $this->sendResponse([], 'Failed to start Mini Project');
        // }
    }

    public function checkMiniProjectStudentStatus($studentId, $miniProjectId)
    {
        // Check if there is a record with the given student_id, mini_project_id, and status == 1
        $status = MiniProjectStudent::where('student_id', $studentId)
            ->where('mini_project_id', $miniProjectId)
            ->where('status', 1)
            ->exists();

        return $this->sendResponse(['status' => $status]);
    }


    public function completeStatusForStudent(Request $request)
    {
        $userId = $request->input('studentId');
        $miniProjectId = $request->input('miniProjectId');

        // Fetch all mini project tasks for the given miniProjectId
        $miniProjectTasks = DB::table('mini_project_tasks')
            ->select('id')
            ->where('mini_project_id', $miniProjectId)
            ->where('is_active', 1)
            ->get();

        // Fetch mini project submissions for the specified userId and miniProjectId
        $submittedTasks = DB::table('mini_project_submissions')
            ->whereIn('mini_project_task_id', $miniProjectTasks->pluck('id'))
            ->where('student_id', $userId)
            ->pluck('mini_project_task_id')
            ->toArray();

        // Check if all tasks have submissions
        $allTasksSubmitted = count($miniProjectTasks) === count($submittedTasks);

        if ($allTasksSubmitted) {
            // Update the status in the mini_project_students table if all tasks are submitted
            MiniProjectStudent::where('student_id', $userId)
                ->where('mini_project_id', $miniProjectId)
                ->update([
                    'status' => true,
                    'end_datetime' => now() // Set the end_datetime to the current time
                ]); // Assuming 'status' is a boolean field

            // Return a success response if all tasks are submitted
            return $this->sendResponse([], 'Status updated successfully.', status: true);
        } else {
            // Return a response indicating that some tasks have not been completed
            return $this->sendResponse([], 'Some tasks have not been completed. Completion not possible.', status: false);
        }
    }


    public function getMiniProjectParticipants($miniProjectId)
    {

        $students = DB::table('mini_project_students')
            ->join('students', 'mini_project_students.student_id', '=', 'students.auth_id')
            ->select('mini_project_students.student_id', 'students.name', 'mini_project_students.id as id', 'mini_project_students.certificate')
            ->where('mini_project_students.mini_project_id', $miniProjectId)
            ->get();
        // Fetch all tasks for the given mini project
        $tasks = DB::table('mini_project_tasks')
            ->select('id', 'name')
            ->where('mini_project_id', $miniProjectId)
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
                $submission = DB::table('mini_project_submissions')
                    ->where('student_id', $student->student_id)
                    ->where('mini_project_task_id', $task->id)
                    ->first();

                // Determine the task presence status and elab_id
                if ($submission) {
                    // Case: Task is present
                    $status = $submission->status == 2 ? 'Completed' : 'Pending';
                    $elab_submission_id = $submission->status == 2 ? $submission->elab_submission_id : null;
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

    public function deleteMiniProjectParticipant(Request $request, $miniProjectStudentId)
    {
        try {
            // Find the mini project student record
            $miniProjectStudent = MiniProjectStudent::findOrFail($miniProjectStudentId);

            // Retrieve MiniProjectSubmission records and corresponding details
            $submissions = MiniProjectSubmission::where('mini_project_student_id', $miniProjectStudentId)->get();  
            $details = [];

            // Extract the details needed from each submission
            foreach ($submissions as $submission) {
                $detail = [
                    'mini_project_id' => $submission->mini_project_id,
                    'mini_project_task_id' => $submission->mini_project_task_id,
                    'student_id' => $submission->student_id,
                ];
                $details[] = $detail;

                // Delete MiniProjectSubmission records
                MiniProjectSubmission::where('mini_project_student_id', $miniProjectStudentId)->delete();


                // Delete ElabSubmission records using the extracted details
                ElabSubmission::where('mini_project_id', $submission->mini_project_id)
                             ->where('mini_project_task_id', $submission->mini_project_task_id)
                             ->where('student_id', $submission->student_id)
                             ->delete();
            }


            // Delete the mini project student record
            $miniProjectStudent->delete();

            // Return the details before deletion along with a success response
            return $this->sendResponse(['details' => $details], 'Mini project participant deleted successfully');
        } catch (\Exception $e) {
            // Return an error response if deletion fails
            return response()->json(['message' =>  'Failed to delete mini project participant']);
        }
    }
    public function deleteMiniProject(Request $request, $miniProjectId)
    {
        try {
            // Find the mini project  record
            $miniProject = MiniProject::findOrFail($miniProjectId);

            // Delete the record
            $miniProject->delete();
            MiniProjectTask::where('mini_project_id', $miniProjectId)->delete();
            MiniProjectSubmission::where('mini_project_id', $miniProjectId)->delete();
            MiniProjectStudent::where('mini_project_id', $miniProjectId)->delete();
            ElabSubmission::where('mini_project_id', $miniProjectId)->delete();

            // Return a success response
            return $this->sendResponse([], 'MiniProject deleted successfully');
        } catch (\Exception $e) {
            // Return an error response if deletion fails
            return response()->json(['message' =>  'Failed to delete MiniProject']);
        }
    }
    public function deleteMiniProjectTask(Request $request, $miniProjectTaskId)
    {
        try {
            // Find the mini project task record
            $miniProjectTask = MiniProjectTask::findOrFail($miniProjectTaskId);
            // Delete the record
            MiniProjectSubmission::where('mini_project_task_id', $miniProjectTaskId)->delete();

            MiniProjectStudent::where('mini_project_id', $miniProjectTask->mini_project_id)->delete();
            ElabSubmission::where('mini_project_task_id', $miniProjectTaskId)->delete();

            $miniProjectTask->delete();

            // Return a success response
            return $this->sendResponse([], 'MiniProjectTask Task deleted successfully');
        } catch (\Exception $e) {
            // Return an error response if deletion fails
            return response()->json(['message' =>  'Failed to delete MiniProjectTask Task']);
        }
    }
}
