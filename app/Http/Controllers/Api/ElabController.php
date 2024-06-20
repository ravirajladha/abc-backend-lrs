<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\InternshipSubmission;
use Illuminate\Support\Facades\Validator;
use App\Http\Constants\MiniProjectConstants;
use App\Http\Controllers\Api\BaseController;
use App\Models\{Elab, ElabSubmission, Video, MiniProjectSubmission};

class ElabController extends BaseController
{
    /**
     * Display a listing of the elab.
     *
     * @return \Illuminate\Http\Response
     */
    public function getElabList()
    {
        $elabs = Elab::with('class', 'subject')->get();
        return $this->sendResponse(['elabs' => $elabs]);
    }
    /**
     * Display a listing of the active elab.
     *
     * @return \Illuminate\Http\Response
     */
    public function getActiveElabs()
    {
        $elabs = Elab::where('active', 1)->get();
        return $this->sendResponse(['elabs' => $elabs]);
    }


    public function fetchSelectedActiveElabs($classId, $subjectId = null)
{
    $query = Elab::where('active', 1)->where('class_id', $classId);

    if ($subjectId !== null) {
        $query->where('subject_id', $subjectId);
    }

    $elabs = $query->get();

    return $this->sendResponse(['elabs' => $elabs]);
}

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeElabDetails(Request $request)
    {
        Log::info("data receiver from elab", $request->all());
        $validator = Validator::make($request->all(), [
            'elabName' => 'required|string',
            // 'selectedLanguage' => 'required|string',
            'selectedClass' => 'required|integer',
            'selectedSubject' => 'required|integer',
            'testcase' => 'required|string',
            'template1' => 'required|string',
            'template2' => 'required|string',
            'dataHarnessCode' => 'required|string',
            'selectedLanguage' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $lab = new Elab;
            $lab->title = $request->elabName;
            // $lab->code = $request->code;
            $lab->class_id = $request->selectedClass;
            $lab->subject_id = $request->selectedSubject;
            $lab->description = $request->description;
            $lab->io_format = $request->io_format;
            $lab->constraints = $request->constraints;
            $lab->io_sample = $request->sampleIO;
            $lab->pseudo_code = $request->pseudocode;
            $lab->testcase = $request->testcase;
            $lab->template1 = $request->template1;
            $lab->template2 = $request->template2;
            $lab->data_harness_code = $request->dataHarnessCode;
            $lab->code_language = $request->selectedLanguage;
            // Set any other properties on $lab as needed
            $lab->save(); // Save the new lab to the database
            // Return a response or redirect
        }

        if ($lab) {
            return $this->sendResponse([], 'Elab added successfully');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Elab  $elab
     * @return \Illuminate\Http\Response
     */


    public function getAllElabs(Request $request)
    {
        // Retrieve all eLabs along with their associated class and subject names
        $elabs = Elab::all();

        // Return the response with eLabs data
        return response()->json(['elabs' => $elabs]);
    }



    /**
     * Display a details of a elab through id.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getElabDetailsByElabId(Request $request, $elabId, $studentId = null)
    {
        $res = [];
        $validator = Validator::make(['elabId' => $elabId], [
            'elabId' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        // Load the eLab along with its associated class and subject, retrieving only their names
        $elab = Elab::with(['class' => function ($query) {
            $query->select('id', 'name'); // Select only the id and name columns
        }, 'subject' => function ($query) {
            $query->select('id', 'name'); // Select only the id and name columns
        }])->find($elabId);

        if (!$elab) {
            return $this->sendResponse([], 'No Details Found!');
        }
        //checking the student_id and the data present in the elab submission table to pass the code written by the student
        // If studentId is provided, fetch details from elab_submission table
        $studentIdFromRequest = $request->input('studentId');

        if ($studentIdFromRequest !== null) {
            $submission = ElabSubmission::where('elab_id', $elabId)
                ->where('student_id', $studentIdFromRequest)
                ->where('type', 1) // Assuming type 1 corresponds to a specific type
                ->first();

            // Attach submission details to the response
            if ($submission) {
                $res['submission'] = $submission;
            }
        }
        // $res['submission_test'] = $studentIdFromRequest;
        // Attach elab details to the response
        $res['elab'] = $elab;

        return $this->sendResponse($res);
    }





    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Elab  $elab
     * @return \Illuminate\Http\Response
     */
    /**
     * Update the specified school in storage.
     *
     */
    public function updateElabDetails($elabId, Request $request)
    {
        $res = [];
        $validator = Validator::make($request->all(), [
            // 'elab_id' => 'required|exists:elabs,id',
            'elabName' => 'required|string',
            // 'code' => 'required|string',
            // 'selectedClass' => 'required', 
            // 'selectedSubject' => 'required', 
            'description' => 'required|string',
            'constraints' => 'required|string',
            'sampleIO' => 'required|string', // Assuming 'sampleIO' is required
            'pseudocode' => 'required|string',
            'testcase' => 'required|string',
            'template1' => 'required|string',
            'template2' => 'required|string',
            'dataHarnessCode' => 'required|string',
            'selectedLanguage' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }


        $elab = Elab::where('id', $elabId)->first();

        if ($elab) {
            // Handle file uploads for school_image and logo (if necessary)
            // You may need to adjust this part based on your file handling logic
            // Update eLab details
            $lab = Elab::find($elabId);
            $lab->title = $request->elabName;
            // $lab->code = $request->code;
            $lab->class_id = $request->selectedClass;
            $lab->subject_id = $request->selectedSubject;
            $lab->description = $request->description;
            $lab->constraints = $request->constraints;
            $lab->io_format = $request->io_format;
            $lab->io_sample = $request->sampleIO;
            $lab->pseudo_code = $request->pseudocode;
            $lab->testcase = $request->testcase;
            $lab->template1 = $request->template1;
            $lab->template2 = $request->template2;
            $lab->data_harness_code = $request->dataHarnessCode;
            $lab->code_language = $request->selectedLanguage;
            $lab->active = $request->active;
            $lab->save();

            // Prepare response data
            $res = [
                'id' => $elabId,
                'elabName' => $lab->title,
                // 'code' => $lab->code,
                'selectedClass' => $lab->class_id,
                'selectedSubject' => $lab->subject_id,
                'description' => $lab->description,
                'constraints' => $lab->constraints,
                'sampleIO' => $lab->io_sample,
                'pseudocode' => $lab->pseudo_code,
                'testcase' => $lab->testcase,
                'template1' => $lab->template1,
                'template2' => $lab->template2,
                'data_harness_code' => $lab->data_harness_code,
                'code_language' => $lab->code_language,

            ];

            return $this->sendResponse(['elab' => $res], 'Elab updated successfully');
        }

        return $this->sendError('Elab or authentication not found.');
    }


    public function updateElabStatus(Request $request, $elabId)
    {
        $elab = Elab::findOrFail($elabId);

        // Assuming you have a 'status' column in your 'elabs' table
        $elab->active = $request->input('status');
        $elab->save();

        // $videos = Video::where('elab_id', $elabId)->get();

        // foreach ($videos as $video) {
        //     $video->elab_status = $request->input('status');
        //     $video->save();
        // }

        return $this->sendResponse([], 'Elab status updated successfully');
    }



    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param  \App\Models\Elab  $elab
    //  * @return \Illuminate\Http\Response
    //  */
    // public function destroy(Elab $elab)
    // {
    //     //
    // }


    /**
     * Display a details of a elab through id for student learning page.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getElabDetail($id)
    {
        // Fetch a single record from the labs table using Eloquent
        // $lab = Lab::where('video_id', $videoId)->latest()->first();
        $lab = Elab::where('id', $id)->first();

        // Check if lab was found
        if ($lab) {
            // Return the lab along with a 200 OK response
            return response()->json([
                'success' => true,
                'data' => $lab
            ], 200);
        } else {
            // If the lab is not found, return a 404 Not Found response
            return response()->json([
                'success' => false,
                'message' => 'Lab not found'
            ], 404);
        }
    }
    public function elabSubmission(Request $request)
    {
        // Log::info('ElabSubmission',['request' => $request->all()]);
        // Validate the request data
        $validator = Validator::make($request->all(), [
            // 'elab_id' => 'required|exists:elabs,id',
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }
        $redirecting_id = null;
        $subject_id = null;
        $mini_project_id = null;
        $miniProjectTaskId = null;
        $internshipId = null;
        $internshipTaskId = null;
        $redirecting_id = $request->redirecting_id;
        if ($request->type == 1) {
            // type subject
            $subject_id = $redirecting_id;
        } elseif ($request->type == 2) {
            //type mini project
            $redirectingIds = explode('&', $request->redirecting_id);
            $subject_id   = $redirectingIds[0];
            $mini_project_id = $redirectingIds[1];
            $miniProjectTaskId = $redirectingIds[2];
        } elseif ($request->type == 3) {
            //type internship
            $redirectingIds = explode('&', $request->redirecting_id);
            $internshipId = $redirectingIds[0];
            $internshipTaskId = $redirectingIds[1];
        } else {
            return $this->sendResponse('Elab not submitted');
        }

        if ($request->type == 1) {
            $submission = ElabSubmission::where('elab_id', $request->lab_id)
                ->where('subject_id', $subject_id)
                ->where('type', 1)
                ->where('student_id', $request->user_id)
                ->first();
        } elseif ($request->type == 2) {
            $submission = ElabSubmission::where('subject_id', $subject_id)
                ->where('type', 2)
                ->where('mini_project_id', $mini_project_id)
                ->where('mini_project_task_id', $miniProjectTaskId)
                ->where('student_id', $request->user_id)
                ->first();
        } elseif ($request->type == 3) {
            $submission = ElabSubmission::where('type', 3)
                ->where('internship_id', $internshipId)
                ->where('internship_task_id', $internshipTaskId)
                ->where('student_id', $request->user_id)
                ->first();
        }

        if ($submission) {
            // Log::info("same task");
            // Submission already exists, update the record
            $submission->update([
                'code' => $request->code,
                'elab_id' => $request->lab_id,
                'status' => $request->status,
                'memory' => $request->memory,
                'time' => $request->time,
                'code_language' => $request->language,
                'code_level' => $request->level,
                'time_taken' => $request->time_taken,
                'start_timestamp' => $request->start_timestamp,
                'end_timestamp' => $request->end_timestamp,
                // Add other fields as needed
            ]);
        } else {
            // Submission does not exist, create a new record
            $submission = new ElabSubmission([
                'code' => $request->code,
                'elab_id' => $request->lab_id,
                'status' => $request->status,
                'memory' => $request->memory,
                'time' => $request->time,
                'code_language' => $request->language,
                'code_level' => $request->level,
                'time_taken' => $request->time_taken,
                'start_timestamp' => $request->start_timestamp,
                'end_timestamp' => $request->end_timestamp,
                'student_id' => $request->user_id,
                'subject_id' => $subject_id,
                'type' => $request->type,
                'mini_project_id' => $mini_project_id,
                'mini_project_task_id' => $miniProjectTaskId,
                'internship_id' => $internshipId,
                'internship_task_id' => $internshipTaskId,
                // Add other fields as needed
            ]);
            $submission->save();
        }

        //saving the elab id for the mini project and changing the status
        if ($request->type == 2) {
            $redirectingIds = explode('&', $request->redirecting_id);
            $mini_project_id = $redirectingIds[1];
            $miniProjectTaskId = $redirectingIds[2];

            // Update Mini Project Submission
            $miniProjectSubmission = MiniProjectSubmission::where('mini_project_task_id', $miniProjectTaskId)->where('mini_project_id', $mini_project_id)->where('student_id', $request->user_id)->first();

            if ($miniProjectSubmission) {
                $miniProjectSubmission->elab_submission_id = $submission->id; // New Elab Submission ID
                $miniProjectSubmission->status = 2; // New status
                // Update any other fields as needed
                $miniProjectSubmission->save();
            }else{
                
            }
        }
        if ($request->type == 3) {
            // Update Mini Project Submission
            $internshipSubmission = InternshipSubmission::where('internship_task_id', $internshipTaskId)->where('internship_id', $internshipId)->where('student_id', $request->user_id)->first();
            if ($internshipSubmission) {
                $internshipSubmission->elab_submission_id = $submission->id; // New Elab Submission ID
                $internshipSubmission->status = 2; // New status // Update any other fields as needed
                $internshipSubmission->save();
            }
        }
        return $this->sendResponse(['data' => $submission], 'Code submitted successfully');
    }

    /**
     * Display a listing of the elab.
     *
     * @return \Illuminate\Http\Response
     */
    public function getElabParticipants($elabId)
    {
        // $elabs = ElabSubmission::where('elab_id', $elabId)
        $elabs = ElabSubmission::where('elab_id', $elabId)
            ->get();

        return $this->sendResponse(['elabs' => $elabs]);
    }
    /**
     * Display a listing of the elab.
     *
     * @return \Illuminate\Http\Response
     */
    public function getElabSubmittedCodeById($id)
    {
        $elab = ElabSubmission::where('id', $id)->first(); // Use first() to retrieve a single model
        return $this->sendResponse(['elab' => $elab]);
    }

    public function getElabSubmissionByStudent(Request $request, $userId, $elabId)
    {
        // Retrieve the latest eLab submission for the given user_id and elab_id
        $latestSubmission = ElabSubmission::where('student_id', $userId)
            ->where('elab_id', $elabId)
            ->latest('created_at')
            ->type(1)
            ->pluck('id') // Order by created_at column in descending order
            ->first(); // Retrieve the latest submission

        if ($latestSubmission) {
            // If a submission is found, return its ID
            return response()->json(['data' => $latestSubmission]);
        } else {
            // If no submission is found, return an empty response or appropriate message
            return response()->json(['data' => null]);
        }
    }

    /**
     * Delete an entry from the elab_submission table.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteElabParticipantCodebase($id)
    {
        try {
            // Find the elab_submission record by id
            $elabSubmission = ElabSubmission::findOrFail($id);

            // Delete the record
            $elabSubmission->delete();

            // Return success response
            return response()->json(['message' => 'Elab submission deleted successfully'], 200);
        } catch (\Exception $e) {
            // Handle any errors
            return response()->json(['error' => 'Failed to delete elab submission'], 500);
        }
    }
}
