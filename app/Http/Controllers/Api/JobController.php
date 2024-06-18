<?php

namespace App\Http\Controllers\Api;

use App\Models\Job;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;
use App\Models\{JobApplication, JobQuestion, JobTest};

class JobController extends BaseController
{
    public function getJobList(Request $request)
    {
        $userType = $request->attributes->get('type');

        $loggedUserId = $this->getLoggedUserId(); // Assuming you have a method to get the logged user's ID

        if ($userType === 'admin' || $userType === 'recruiter') {
            $jobs = DB::table('jobs as j')
                ->select('j.*', 's.name as student_name', 'a.student_id', 'r.name as recruiter_name', 't.title as test_name', 't.instruction as test_instruction')
                ->leftJoin('job_applications as a', 'a.job_id', '=', 'j.id')
                ->leftJoin('students as s', 's.auth_id', '=', 'a.student_id')
                ->leftJoin('recruiters as r', 'r.auth_id', '=', 'j.recruiter_id')
                ->leftJoin('job_tests as t', 't.id', '=', 'j.test_id');

            if ($userType === 'recruiter') {
                $jobs->where('j.recruiter_id', '=', $loggedUserId);
            }

            // Log::info("Getting jobs query: ", ['query' => $jobs->toSql(), 'bindings' => $jobs->getBindings()]);
            $jobs = $jobs->get();
            // Log::info("getting jobs", $jobs);
            return $this->sendResponse(['jobs' => $jobs]);
        }

        if ($userType === 'student') {

            $student = DB::table('students as s')
                ->select('s.auth_id', 's.class_id')
                ->where('s.auth_id', $this->getLoggedUserId())
                ->first();

            $student_id = $student->auth_id;
            $student_class_id = $student->class_id;

            $jobs = DB::table('jobs as j')
                ->select('j.*', 'r.name as recruiter_name','t.instruction as test_instruction')
                ->leftJoin('recruiters as r', 'r.auth_id', '=', 'j.recruiter_id')
                ->leftJoin('job_tests as t', 't.id', '=', 'j.test_id')
                ->whereRaw("FIND_IN_SET(?, j.class_id)", [$student_class_id])
                ->where('status', true)
                ->get();

            foreach ($jobs as $job) {
                $job->applied = DB::table('job_applications as a')
                    ->where('a.student_id', $student_id)
                    ->where('a.job_id', $job->id)
                    ->exists();
            }

            return $this->sendResponse(['jobs' => $jobs]);
        }
    }

    public function getJobDetails($jobId)
    {
        $job = DB::table('jobs as j')
            ->select('j.*')
            ->where('j.id', $jobId)
            ->first();

        return $this->sendResponse(['job' => $job]);
    }

    public function storeJobDetails(Request $request)
    {
        log::info("Store", $request->all());
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'image' => 'required',
            'annual_ctc' => 'required',
            'location' => 'required',
            'criteria' => 'required',
            'description' => 'required',
            // 'test_id' => 'nullable|exists:job_tests,id',
            'recruiter_id' => 'required',
            'instruction' => 'required',
            'passing_percentage' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->test_id && $value >= 100) {
                        $fail($attribute . ' must be less than 100.');
                    }
                },
            ],
            'selectedClass' => 'required',
        ]);


        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $job = new Job;
            $job->auth_id = $this->getLoggedUserId();
            $job->title = $request->title;

            if (!empty($request->file('image'))) {
                $extension = $request->file('image')->extension();
                $filename = Str::random(4) . time() . '.' . $extension;
                $job->image = $request->file('image')->move(('uploads/images/job'), $filename);
            } else {
                $job->image = null;
            }

            $job->class_Id = $request->selectedClass;
            $job->annual_ctc = $request->annual_ctc;
            $job->location = $request->location;
            $job->criteria = $request->criteria;
            $job->description = $request->description;
            $job->test_id = $request->test_id ? $request->test_id : null;
            $job->recruiter_id = $request->recruiter_id;
            // if ($request->test_id == 0) {
            //     $job->passing_percentage = 0;
            // } else {
            //     $job->passing_percentage = $request->passing_percentage;
            // }

            $job->passing_percentage = $request->test_id ? $request->passing_percentage : 0;
            $job->instruction = $request->instruction;
            $job->status = 1;
            if ($job->save()) {
                return $this->sendResponse([], 'Job added successfully.');
            } else {
                return $this->sendResponse([], 'Failed to create job.');
            }
        }
    }

    public function updateJobDetails(Request $request, $jobId)
    {
        Log::info("updating job", $request->all());
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'annual_ctc' => 'required',
            'location' => 'required',
            'criteria' => 'required',
            'description' => 'required',
            // 'test_id' => 'nullable|exists:job_tests,id',
            'recruiter_id' => 'required',
            'instruction' => 'required',
            'status' => 'required',
            'passing_percentage' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->test_id && $value >= 100) {
                        $fail($attribute . ' must be less than 100.');
                    }
                },
            ],
            'selectedClass' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $job = Job::find($jobId);

            if (!$job) {
                return $this->sendError('Job not found', [], 404);
            }

            $job->title = $request->title;

            if (!empty($request->file('image'))) {
                $extension = $request->file('image')->extension();
                $filename = Str::random(4) . time() . '.' . $extension;
                $job->image = $request->file('image')->move(('uploads/images/job'), $filename);
            }

            $job->annual_ctc = $request->annual_ctc;
            $job->location = $request->location;
            $job->criteria = $request->criteria;
            $job->status = $request->status;
            $job->description = $request->description;
            // $job->test_id = $request->test_id ?: null;

            // $job->test_id = $request->test_id ?: null;
            $job->test_id = $request->test_id ? $request->test_id : null;

            $job->recruiter_id = $request->recruiter_id;
            $job->passing_percentage = $request->test_id ? $request->passing_percentage : 0;
            $job->instruction = $request->instruction;
            $job->class_Id = $request->selectedClass;

            if ($job->save()) {
                return $this->sendResponse([], 'Job updated successfully.');
            } else {
                return $this->sendResponse([], 'Failed to update job.');
            }
        }
    }



    public function deleteJobDetails(Request $request, $jobId)
    {
        // Find the job
        $job = Job::find($jobId);
    
        if (!$job) {
            return response()->json(['message' => 'Job not found'], 404);
        }
    
        // Delete job applications associated with this job
        JobApplication::where('job_id', $jobId)->delete();
    
        // Delete the job
        $job->delete();
    
        return $this->sendResponse([], 'Job deleted successfully');
    }
    
    public function applyJob(Request $request)
    {
        Log::info("requests applyjob", $request->all());

        // Validate with conditional rule for testId
        $validator = Validator::make($request->all(), [
            'jobId' => 'required',
            'studentId' => 'required',
            'schoolId' => 'required',
            'classId' => 'required',
            'testId' => 'nullable', // testId is optional
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }

        $data = $validator->validated();

        // Check if the job application already exists
        $existingSession = JobApplication::where('student_id', $data['studentId'])
            ->where('job_id', $data['jobId'])
            ->first();

        if ($existingSession) {
            return response()->json(['message' => 'Test already taken'], 409); // 409 Conflict
        }

        if (isset($data['testId'])) {
            $token = Str::random(32);

            // Create a new test session
            $testSession = JobApplication::create([
                'student_id' => $data['studentId'],
                'school_id' => $data['schoolId'],
                'test_id' => $data['testId'],
                'class_id' => $data['classId'],
                'job_id' => $data['jobId'],
                'token' => $token,
            ]);

            // return response()->json(['token' => $token, 'status' => 200], 200);
            return $this->sendResponse(['token' => $token, 'status' => 200], 'Job updated successfully.');
        } else {
            // Create a job application without test
            $jobApplication = JobApplication::create([
                'student_id' => $data['studentId'],
                'school_id' => $data['schoolId'],
                'class_id' => $data['classId'],
                'job_id' => $data['jobId'],
                'is_completed' => true,
                'is_pass' => true,
                'is_test' => false,
            ]);
            return $this->sendResponse(['status' => 200, 'message' => 'Job application submitted successfully'], 'Job updated successfully.');

            // return response()->json(['status' => 200, 'message' => 'Job application submitted successfully'], 200);
        }
    }


    public function getStudentJobApplications(Request $request, $jobId)
    {
        $job_applications = DB::table('job_applications as a')
            ->select('j.*', 's.name as student_name', 'a.student_id', 'c.name as class_name', 'a.is_pass')
            ->leftJoin('jobs as j', 'j.id', '=', 'a.job_id')
            ->leftJoin('students as s', 's.auth_id', '=', 'a.student_id')
            ->leftJoin('classes as c', 'c.id', '=', 's.class_id')
            ->where('a.job_id', $jobId)
            ->where('is_completed', true)
            ->get();

        return $this->sendResponse(['job_applications' => $job_applications]);
    }

    public function updateJobApplicationStatus(Request $request, $jobApplicationId)
    {

        $job_application = DB::table('job_applications as a')
            ->select('a.*')
            ->where('a.id', $jobApplicationId)
            ->first();

        $job_application->remarks = $request->remarks ? $request->remarks : null;
        $job_application->status = $request->status ? 1 : 0;

        if ($job_application->save()) {
            return $this->sendResponse([], 'Job application update successfully.');
        } else {
            return $this->sendResponse([], 'Failed to update application.');
        }
    }

    public function getJobTestDetailsByToken(Request $request, $token, $jobId)
    {
        // Find the test result entry using the token
        $testResult = DB::table('job_applications')
            ->where('token', $token)
            ->where('job_id', $jobId)
            ->first();

        $job = DB::table('jobs')
            ->where('id', $jobId)
            ->first();

        // Check if the test result does not exist, which indicates an invalid token or test ID
        if (!$testResult) {
            return $this->sendError("Test or token not found", [], 404); // Use HTTP 404 Not Found for resource not found
        }

        // Check if the test has already been taken
        if ($testResult->token_status == 1) {
            return $this->sendResponse([], "Test has already been taken", false);
        }

        // Update the status to 1 to mark as taken
        DB::table('job_applications')
            ->where('id', $testResult->id)
            ->update(['token_status' => 1]);

        // Fetch the term test details
        $job_test = DB::table('job_tests as a')
            ->select('a.id',   'a.title',  'a.description', 'a.total_score', 'a.time_limit', 'a.no_of_questions',   'a.question_ids', 'a.passing_percentage')
            ->where('a.id', $testResult->test_id)
            ->first();

        if ($job_test && $job_test->question_ids) {
            $questionIds = explode(',', $job_test->question_ids);
            $job_test->questions = DB::table('job_questions')
                ->whereIn('id', $questionIds)
                ->get();
        }

        // Return the test details along with the test_result_id
        return $this->sendResponse(['job_test' => $job_test, 'test_result' => $testResult, 'job' => $job], "Test details retrieved successfully", true);
    }
}
