<?php

namespace App\Http\Controllers\Api;


use App\Models\Trainer;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\Models\TrainerSubject;
use App\Models\TrainerCourse;

use App\Models\Auth as AuthModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use App\Http\Constants\AuthConstants;

use Illuminate\Support\Facades\Validator;

use App\Services\Recruiter\DashboardService;
use App\Models\{Recruiter};

class RecruiterController extends BaseController
{
    /**
     * Fetch dashboard items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboard(Request $request, DashboardService $dashboardService)
    {
        $recruiterId = Recruiter::where('auth_id', $this->getLoggedUserId())->value('id');
        Log::info("getRecruiterDashboardItems22222");

        $dashboard =  $dashboardService->getRecruiterDashboardItems($recruiterId);
        return $this->sendResponse(['dashboard' => $dashboard]);
    }

    /**
     * Update trainer details
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateRecruiterPassword(Request $request, $recruiterId)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6',
            'confirmPassword' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $auth = AuthModel::where('id', $recruiterId)->first();

        if ($auth) {
            $auth->update([
                'password' => bcrypt($request->password),
            ]);

            $updatedAuth = AuthModel::select('id', 'username', 'email', 'phone_number')
                ->where('id', $recruiterId)
                ->first();

            return $this->sendResponse(['auth' => $updatedAuth], 'Credentials updated successfully.');
        } else {
            return $this->sendError('Failed to update admin credentials', [], 404);
        }
    }

    /**
     * Display a listing of the trainers.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecruitersList(Request $request)
    {
        $userType = $request->attributes->get('type');
        if ($userType === 'admin' || $userType === 'recruiter') {
            // $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');

            $recruiters = DB::table('recruiters as t')
                ->select('t.id', 't.auth_id', 't.name', 't.profile_image','t.doj', 't.address', 't.city', 't.state', 't.pincode', 't.type', 'a.email', 'a.username', 'a.phone_number', 'a.status')
                ->join('auth as a', 't.auth_id', '=', 'a.id')
                ->get();

            return $this->sendResponse(['recruiters' => $recruiters]);
        } else {
            return $this->sendAuthError("Not authorized to fetch recruiters list.");
        }
    }

    /**
     * Display the specified trainer.
     *
     */
    public function getRecruiterDetails($recruiterId)
    {
        $res = [];
 

        $validator = Validator::make(['recruiterId' => $recruiterId], [
            'recruiterId' => 'required',
        ]);
        Log::info('Validating'. $recruiterId);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            // $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');
            // $trainer = Trainer::where('auth_id', $recruiterId)
            //     ->where('school_id', $schoolId)
            //     ->first();
            $recruiter = Recruiter::where('auth_id', $recruiterId)
                ->first();


            if ($recruiter) {
                $auth = AuthModel::where('id', $recruiterId)
                    ->where('type', AuthConstants::TYPE_RECRUITER)
                    ->first();
            }

            if ($recruiter && $auth) {
                $res = [
                    'id' => $recruiter->id,
                    'auth_id' => $recruiterId,
                    'name' => $recruiter->name,
                    'email' => $auth->email,
                    'username' => $auth->username,
                    'phone_number' => $auth->phone_number,
                    // 'emp_id' => $recruiter->emp_id,
                    'profile_image' => $recruiter->profile_image,
                    'doj' => $recruiter->doj,
                    'address' => $recruiter->address,
                    'city' => $recruiter->city,
                    'state' => $recruiter->state,
                    'pincode' => $recruiter->pincode,
                    'description' => $recruiter->description,
                    'type' => $recruiter->type,
                ];
                return $this->sendResponse(['recruiter' => $res]);
            } else {
                return $this->sendResponse([], 'Failed to fetch recruiter details.');
            }
        }
    }


    /**
     * Store trainer
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeRecruiterDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:auth',
            'phone_number' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $auth = AuthModel::create([
                'password' => Hash::make('abc123'),
                'username' => $request->email,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'type' => AuthConstants::TYPE_RECRUITER,
                'status' => AuthConstants::STATUS_ACTIVE,
            ]);

         

            if ($auth) {
                $trainer = Recruiter::create([
                    'auth_id' => $auth->id,
                  
                    'name' => $request->name,
                    'emp_id' => $request->emp_id,
                    'profile_image' => $request->profile_image,
                    'doj' => $request->doj,
                    'phone_number' => $request->phone_number,
                    'address' => $request->address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'pincode' => $request->pincode,
                    'description' => $request->description,
                ]);
            }
            if ($auth && $trainer) {
                return $this->sendResponse([], 'Recruiter added successfully');
            }
        }
        return $this->sendError('Failed to add recruiter');
    }


    /**
     * Get trainer classes and subjects
     *
     * @param int $trainerId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTrainerSubjectsAndCourses($trainerId)
    {
        $trainerSubjectCourses= [];

        $trainer = DB::table('trainers')
            ->select('*')
            ->where('auth_id', $trainerId)
            ->first();

        if (!$trainer) {
            return $this->sendError('Trainer not found');
        }

        $trainerCourses = TrainerCourse::where([
            'trainer_id' => $trainer->id,
         
        ])->get(['subject_id']);

        foreach ($trainerCourses as $course) {
            $trainerSubjectCourses[] =  DB::table('courses as c')
                ->select('c.id as course_id', 'c.subject_id')
                ->where('c.id', $course->course_id)
                ->first();
        }


        return $this->sendResponse(['trainer' => $trainerSubjectCourses], '');
    }


    /**
     * Store or update trainer classes and subjects
     *
     * @param Request $request
     * @param int $trainerId
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeOrUpdateTrainerSubjectsAndCourses(Request $request, $trainerId)
    {
        $validator = Validator::make(array_merge($request->all(), ['trainerId' => $trainerId]), [
            'trainerId' => 'required',
            'trainer_data' => 'required|array',
            'trainer_data.*.course_id' => 'required|exists:courses,id',
            'trainer_data.*.subject_id' => 'required|exists:subjects,id',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $trainer = DB::table('trainers')
            ->select('*')
            ->where('auth_id', $trainerId)
            ->first();
        // $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');

        foreach ($request->input('trainer_data') as $data) {
            TrainerSubject::updateOrCreate(
                [
                    'subject_id' => $data['subject_id'],
                    'trainer_id' => $trainer->id,
                  
                ],
                [
                    'subject_id' => $data['subject_id'],
                    'trainer_id' => $trainer->id,
                  
                ]
            );

            TrainerCourse::updateOrCreate(
                [
                    'course_id' => $data['course_id'],
                    'trainer_id' =>  $trainer->id,
                  
                ],
                [
                    'course_id' => $data['course_id'],
                    'trainer_id' =>  $trainer->id,
                  
                ]
            );
        }

        return $this->sendResponse([], 'Trainer Subjects and Courses added or updated successfully');
    }

    /**
     * Update the specified trainer in storage.
     *
     */
    public function updateRecruiterDetails(Request $request, $recruiterId)
    {
        $res = [];
        $validator = Validator::make(array_merge($request->all(), ['recruiter_id' => $recruiterId]), [
            'name' => 'required|string|max:255',
            'recruiter_id' => 'required|exists:auth,id',
            'password' => 'nullable|min:6',
            'email' => 'required|string|email|max:255',
            'phone_number' => 'numeric|min:10',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust file type and size as needed
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $auth = AuthModel::find($recruiterId);
       

        $recruiter = Recruiter::where('auth_id', $recruiterId)->first();

        if ($auth && $recruiter) {
            $authData = [
                'email' => $request->input('email', $auth->email),
                'password' => $request->input('password') ? Hash::make($request->password) : $auth->password,
                'phone_number' => $request->input('phone_number', $auth->phone_number),
            ];

            $auth->update($authData);

            $recruiterData = [
                'name' => $request->input('name', $recruiter->name),
                'phone_number' => $request->input('phone_number', $recruiter->phone_number),
                'address' => $request->input('address', $recruiter->address),
                'city' => $request->input('city', $recruiter->city),
                'state' => $request->input('state', $recruiter->state),
                'pincode' => $request->input('pincode', $recruiter->pincode),
                'description' => $request->input('description', $recruiter->description),
            ];

            if ($request->hasFile('profile_image')) {
                if ($recruiter->profile_image) {
                    File::delete(public_path($recruiter->profile_image));
                }

                $extension = $request->file('profile_image')->extension();
                $filename = Str::random(4) . time() . '.' . $extension;
                $request->file('profile_image')->move(('uploads/images/recruiter'), $filename);
                $recruiterData['profile_image'] = 'uploads/images/recruiter/' . $filename;
            }

            $recruiter->update($recruiterData);

            $res = [
                'id' => $recruiter->id,
                'auth_id' => $recruiter->auth_id,
                // 'school_id' => $recruiter->school_id,
                'email' => $auth->email,
                'phone_number' => $auth->phone_number,
                'profile_image' => $recruiter->profile_image,
                'doj' => $recruiter->doj,
                'address' => $recruiter->address,
                'city' => $recruiter->city,
                'state' => $recruiter->state,
                'pincode' => $recruiter->pincode,
                'description' => $recruiter->description,
                'type' => $recruiter->type,
            ];

            return $this->sendResponse(['recruiter' => $res], 'Recruiter updated successfully');
        }

        return $this->sendError('Failed to update recruiter details.');
    }


    /**
     * Remove the specified trainer from storage.
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteTrainerDetails(Request $request, $trainerId)
    {
        $userType = $request->attributes->get('type');
        if ($userType = 'admin') {
            $validator = Validator::make(['trainer_id' => $trainerId], [
                'trainer_id' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->sendValidationError($validator);
            } else {
                // $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');
                $trainer = Trainer::where('auth_id', $trainerId)->first();
                $auth = AuthModel::find($trainerId);
                if ($trainer && $auth) {
                    $trainer->delete();
                    $auth->delete();
                } else {
                    return $this->sendError("Trying to delete a invalid trainer.");
                }
            }
            return $this->sendResponse([], 'Trainer deleted successfully');
        } else {
            return $this->sendAuthError("Not authorized delete student.");
        }
    }

    public function getAllStudentsByCourses(Request $request)
    {
        $students = [];

        $trainerId = $this->getLoggedUserId();

        $trainer = DB::table('trainers')
            ->select('*')
            ->where('auth_id', $trainerId)
            ->first();

        if ($trainer) {

            $trainerSubjects = TrainerSubject::where([
                'trainer_id' => $trainer->id,
            ])->get(['subject_id']);

            $trainerCourses = TrainerCourse::where([
                'trainer_id' => $trainer->id,
            ])->get(['course_id']);

            $students_query = DB::table('students as s')
                ->select('s.*', 'sub.name as subject_name')
                ->join('subjects as sub', 's.subject_id', '=', 'sub.id')
                ->whereIn('s.subject_id', $trainerSubjects)
                ->get();

            $students =  $students_query;

            foreach ($students as $student) {
                $qna = DB::table('qna as q')
                    ->where('q.student_id', $student->id)
                    ->where('q.trainer_id', $trainer->id)
                    ->whereNull('q.answer')
                    ->orderBy('created_at', 'desc')
                    ->exists();
                $student->read_status = $qna;
            }



            return $this->sendResponse(['students' => $students], '');
        } else {
            return $this->sendAuthError("Not authorized get students.");
        }
    }
}
