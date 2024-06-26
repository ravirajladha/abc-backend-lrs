<?php

namespace App\Http\Controllers\Api;

use App\Models\School;
use App\Models\Classes;
use App\Models\Teacher;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TeacherClasses;
use App\Models\TeacherSubject;

use App\Models\Auth as AuthModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use App\Http\Constants\AuthConstants;

use Illuminate\Support\Facades\Validator;

use App\Services\Recruiter\DashboardService;
use App\Models\{Recruiter, Job, JobApplication,JobTest,RecruiterAuthLog, JobQuestion, JobTestResult};

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
     * Update teacher details
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
     * Display a listing of the teachers.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecruitersList(Request $request)
    {
        $userType = $request->attributes->get('type');
        if ($userType === 'admin' || $userType === 'recruiter') {
            // $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');

            $recruiters = DB::table('recruiters as t')
                ->select('t.id', 't.auth_id', 't.name', 't.profile_image', 't.phone_number', 't.doj', 't.address', 't.city', 't.state', 't.pincode', 't.type', 'a.email', 'a.username', 'a.phone_number', 'a.status')
                ->join('auth as a', 't.auth_id', '=', 'a.id')
                ->get();

            return $this->sendResponse(['recruiters' => $recruiters]);
        } else {
            return $this->sendAuthError("Not authorized to fetch recruiters list.");
        }
    }

    /**
     * Display the specified teacher.
     *
     */
    public function getRecruiterDetails($recruiterId)
    {
        $res = [];
        // $teacher_classes = [];
        // $teacher_subjects = [];

        $validator = Validator::make(['recruiterId' => $recruiterId], [
            'recruiterId' => 'required',
        ]);
        Log::info('Validating'. $recruiterId);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            // $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');
            // $teacher = Teacher::where('auth_id', $recruiterId)
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
     * Store teacher
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

            $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');

            if ($auth) {
                $teacher = Recruiter::create([
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
            if ($auth && $teacher) {
                return $this->sendResponse([], 'Recruiter added successfully');
            }
        }
        return $this->sendError('Failed to add recruiter');
    }


    /**
     * Get teacher classes and subjects
     *
     * @param int $teacherId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTeacherClassesAndSubjects($teacherId)
    {
        $teacherClassSubjects = [];

        $teacher = DB::table('teachers')
            ->select('*')
            ->where('auth_id', $teacherId)
            ->first();

        if (!$teacher) {
            return $this->sendError('Teacher not found');
        }

        $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');

        $teacherSubjects = TeacherSubject::where([
            'teacher_id' => $teacher->id,
            'school_id' => $schoolId,
        ])->get(['subject_id']);

        foreach ($teacherSubjects as $subject) {
            $teacherClassSubjects[] =  DB::table('subjects as s')
                ->select('s.id as subject_id', 's.class_id')
                ->where('s.id', $subject->subject_id)
                ->first();
        }

        // $response = [
        //     'teacher_classes' => $teacherClasses,
        //     'teacher_subjects' => $teacherSubjects,
        // ];

        return $this->sendResponse(['teacher' => $teacherClassSubjects], '');
    }


    /**
     * Store or update teacher classes and subjects
     *
     * @param Request $request
     * @param int $teacherId
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeOrUpdateTeacherClassesAndSubjects(Request $request, $teacherId)
    {
        $validator = Validator::make(array_merge($request->all(), ['teacherId' => $teacherId]), [
            'teacherId' => 'required',
            'teacher_data' => 'required|array',
            'teacher_data.*.class_id' => 'required|exists:classes,id',
            'teacher_data.*.subject_id' => 'required|exists:subjects,id',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $teacher = DB::table('teachers')
            ->select('*')
            ->where('auth_id', $teacherId)
            ->first();
        $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');

        foreach ($request->input('teacher_data') as $data) {
            TeacherClasses::updateOrCreate(
                [
                    'class_id' => $data['class_id'],
                    'teacher_id' => $teacher->id,
                    'school_id' => $schoolId,
                ],
                [
                    'class_id' => $data['class_id'],
                    'teacher_id' => $teacher->id,
                    'school_id' => $schoolId,
                ]
            );

            TeacherSubject::updateOrCreate(
                [
                    'subject_id' => $data['subject_id'],
                    'teacher_id' =>  $teacher->id,
                    'school_id' => $schoolId,
                ],
                [
                    'subject_id' => $data['subject_id'],
                    'teacher_id' =>  $teacher->id,
                    'school_id' => $schoolId,
                ]
            );
        }

        return $this->sendResponse([], 'Teacher Classes and Subjects added or updated successfully');
    }

    /**
     * Update the specified teacher in storage.
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
                'school_id' => $recruiter->school_id,
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
     * Remove the specified teacher from storage.
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteTeacherDetails(Request $request, $teacherId)
    {
        $userType = $request->attributes->get('type');
        if ($userType = 'school') {
            $validator = Validator::make(['teacher_id' => $teacherId], [
                'teacher_id' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->sendValidationError($validator);
            } else {
                $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');
                $teacher = Teacher::where('auth_id', $teacherId)->where('school_id', $schoolId)->first();
                $auth = AuthModel::find($teacherId);
                if ($teacher && $auth) {
                    $teacher->delete();
                    $auth->delete();
                } else {
                    return $this->sendError("Trying to delete a invalid teacher.");
                }
            }
            return $this->sendResponse([], 'Teacher deleted successfully');
        } else {
            return $this->sendAuthError("Not authorized delete student.");
        }
    }

    public function getAllStudentsBySubjects(Request $request)
    {
        $students = [];

        $teacherId = $this->getLoggedUserId();

        $teacher = DB::table('teachers')
            ->select('*')
            ->where('auth_id', $teacherId)
            ->first();

        if ($teacher) {

            $teacherClasses = TeacherClasses::where([
                'teacher_id' => $teacher->id,
            ])->get(['class_id']);

            $teacherSubjects = TeacherSubject::where([
                'teacher_id' => $teacher->id,
            ])->get(['subject_id']);

            $students_query = DB::table('students as s')
                ->select('s.*', 'c.name as class_name')
                ->join('classes as c', 's.class_id', '=', 'c.id')
                ->whereIn('s.class_id', $teacherClasses)
                ->get();

            $students =  $students_query;

            foreach ($students as $student) {
                $qna = DB::table('qna as q')
                    ->where('q.student_id', $student->id)
                    ->where('q.teacher_id', $teacher->id)
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
