<?php

namespace App\Http\Controllers\Api;

use App\Models\School;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherClasses;
use App\Models\TeacherSubject;
use App\Models\Auth as AuthModel;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Http\Constants\AuthConstants;

use App\Services\Teacher\DashboardService;

class TeacherController extends BaseController
{
    /**
     * Fetch dashboard items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboard(Request $request, DashboardService $dashboardService)
    {
        $teacherId = Teacher::where('auth_id', $this->getLoggedUserId())->value('id');

        $dashboard =  $dashboardService->getTeacherDashboardItems($teacherId);
        return $this->sendResponse(['dashboard' => $dashboard]);
    }

    /**
     * Update teacher details
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTeacherPassword(Request $request, $teacherId)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6',
            'confirmPassword' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $auth = AuthModel::where('id', $teacherId)->first();

        if ($auth) {
            $auth->update([
                'password' => bcrypt($request->password),
            ]);

            $updatedAuth = AuthModel::select('id', 'username', 'email', 'phone_number')
                ->where('id', $teacherId)
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
    public function getTeachersList(Request $request)
    {
        $userType = $request->attributes->get('type');
        if ($userType === 'admin' || $userType === 'school') {
            $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');

            $teachers = DB::table('teachers as t')
                ->select('t.id', 't.auth_id', 't.school_id', 't.name', 't.emp_id', 't.profile_image', 't.phone_number', 't.doj', 't.address', 't.city', 't.state', 't.pincode', 't.type', 'a.email', 'a.username', 'a.phone_number', 'a.status')
                ->join('auth as a', 't.auth_id', '=', 'a.id')
                // ->where('t.school_id', $schoolId)
                ->get();

            foreach ($teachers as $teacher) {
                $teacher->teacher_subjects = DB::table('teacher_subjects as ts')
                    ->select('ts.subject_id', 's.name as subject_name', 'c.name as class_name')
                    ->leftJoin('subjects as s', 's.id', 'ts.subject_id')
                    ->leftJoin('classes as c', 'c.id', 's.class_id')
                    ->where('ts.teacher_id', $teacher->id)
                    ->get();
                $teacher->teacher_classes = DB::table('teacher_classes as tc')
                    ->select('tc.class_id', 'c.name as class_name')
                    ->leftJoin('classes as c', 'c.id', 'tc.class_id')
                    ->where('tc.teacher_id', $teacher->id)
                    ->get();
            }

            return $this->sendResponse(['teachers' => $teachers]);
        } else {
            return $this->sendAuthError("Not authorized to fetch teachers list.");
        }
    }

    /**
     * Display the specified teacher.
     *
     */
    public function getTeacherDetails($teacherId)
    {
        $res = [];
        $teacher_classes = [];
        $teacher_subjects = [];

        $validator = Validator::make(['teacherId' => $teacherId], [
            'teacherId' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            // $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');
            $teacher = Teacher::where('auth_id', $teacherId)
                // ->where('school_id', $schoolId)
                ->first();


            if ($teacher) {
                $auth = AuthModel::where('id', $teacherId)
                    ->where('type', AuthConstants::TYPE_TEACHER)
                    ->first();

                $teacher_classes = DB::table('teacher_classes as tc')
                    ->select('tc.class_id', 'c.name as class_name')
                    ->leftJoin('classes as c', 'c.id', 'tc.class_id')
                    ->where('tc.teacher_id', $teacher->id)
                    ->get();

                $teacher_subjects = DB::table('teacher_subjects as ts')
                    ->select('ts.subject_id', 's.name as subject_name', 'c.name as class_name')
                    ->leftJoin('subjects as s', 's.id', 'ts.subject_id')
                    ->leftJoin('classes as c', 'c.id', 's.class_id')
                    ->where('ts.teacher_id', $teacher->id)
                    ->get();
            }


            if ($teacher && $auth) {
                $res = [
                    'id' => $teacher->id,
                    'auth_id' => $teacherId,
                    // 'school_id' => $teacher->school_id,
                    'name' => $teacher->name,
                    'email' => $auth->email,
                    'username' => $auth->username,
                    'phone_number' => $auth->phone_number,
                    'emp_id' => $teacher->emp_id,
                    'profile_image' => $teacher->profile_image,
                    'doj' => $teacher->doj,
                    'address' => $teacher->address,
                    'city' => $teacher->city,
                    'state' => $teacher->state,
                    'pincode' => $teacher->pincode,
                    'description' => $teacher->description,
                    'type' => $teacher->type,
                ];
                return $this->sendResponse(['teacher' => $res, 'teacher_classes' => $teacher_classes, 'teacher_subjects' => $teacher_subjects]);
            } else {
                return $this->sendResponse([], 'Failed to fetch trainer details.');
            }
        }
    }


    /**
     * Store teacher
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeTeacherDetails(Request $request)
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
                'type' => AuthConstants::TYPE_TEACHER,
                'status' => AuthConstants::STATUS_ACTIVE,
            ]);

            // $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');

            if ($auth) {
                $teacher = Teacher::create([
                    'auth_id' => $auth->id,
                    'school_id' => 1,
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
                return $this->sendResponse([], 'Trainer added successfully');
            }
        }
        return $this->sendError('Failed to add Trainer');
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
            return $this->sendError('Trainer not found');
        }

        // $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');

        $teacherSubjects = TeacherSubject::where([
            'teacher_id' => $teacher->id,
            // 'school_id' => $schoolId,
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
        // $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');

        foreach ($request->input('teacher_data') as $data) {
            TeacherClasses::updateOrCreate(
                [
                    'class_id' => $data['class_id'],
                    'teacher_id' => $teacher->id,
                    // 'school_id' => $schoolId,
                ],
                [
                    'class_id' => $data['class_id'],
                    'teacher_id' => $teacher->id,
                    // 'school_id' => $schoolId,
                ]
            );

            TeacherSubject::updateOrCreate(
                [
                    'subject_id' => $data['subject_id'],
                    'teacher_id' =>  $teacher->id,
                    // 'school_id' => $schoolId,
                ],
                [
                    'subject_id' => $data['subject_id'],
                    'teacher_id' =>  $teacher->id,
                    // 'school_id' => $schoolId,
                ]
            );
        }

        return $this->sendResponse([], 'Teacher Classes and Subjects added or updated successfully');
    }

    /**
     * Update the specified teacher in storage.
     *
     */
    public function updateTeacherDetails(Request $request, $teacherId)
    {
        $res = [];
        $validator = Validator::make(array_merge($request->all(), ['teacher_id' => $teacherId]), [
            'name' => 'required|string|max:255',
            'teacher_id' => 'required|exists:auth,id',
            'password' => 'nullable|min:6',
            'email' => 'required|string|email|max:255',
            'phone_number' => 'numeric|min:10',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust file type and size as needed
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $auth = AuthModel::find($teacherId);
        // $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');

        // $teacher = Teacher::where('auth_id', $teacherId)->where('school_id', $schoolId)->first();
        $teacher = Teacher::where('auth_id', $teacherId)->first();

        if ($auth && $teacher) {
            $authData = [
                'email' => $request->input('email', $auth->email),
                'password' => $request->input('password') ? Hash::make($request->password) : $auth->password,
                'phone_number' => $request->input('phone_number', $auth->phone_number),
            ];

            $auth->update($authData);

            $teacherData = [
                'name' => $request->input('name', $teacher->name),
                'phone_number' => $request->input('phone_number', $teacher->phone_number),
                'address' => $request->input('address', $teacher->address),
                'city' => $request->input('city', $teacher->city),
                'state' => $request->input('state', $teacher->state),
                'pincode' => $request->input('pincode', $teacher->pincode),
                'description' => $request->input('description', $teacher->description),
            ];

            if ($request->hasFile('profile_image')) {
                if ($teacher->profile_image) {
                    File::delete(public_path($teacher->profile_image));
                }

                $extension = $request->file('profile_image')->extension();
                $filename = Str::random(4) . time() . '.' . $extension;
                $request->file('profile_image')->move(('uploads/images/teacher'), $filename);
                $teacherData['profile_image'] = 'uploads/images/teacher/' . $filename;
            }

            $teacher->update($teacherData);

            $res = [
                'id' => $teacher->id,
                'auth_id' => $teacher->auth_id,
                'school_id' => $teacher->school_id,
                'email' => $auth->email,
                'phone_number' => $auth->phone_number,
                'emp_id' => $teacher->emp_id,
                'profile_image' => $teacher->profile_image,
                'doj' => $teacher->doj,
                'address' => $teacher->address,
                'city' => $teacher->city,
                'state' => $teacher->state,
                'pincode' => $teacher->pincode,
                'description' => $teacher->description,
                'type' => $teacher->type,
            ];

            return $this->sendResponse(['teacher' => $res], 'Trainer updated successfully');
        }

        return $this->sendError('Failed to update trainer details.');
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
        if ($userType = 'admin') {
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
                // ->select('s.*', 'c.name as class_name')
                // ->join('classes as c', 's.class_id', '=', 'c.id')
                // ->whereIn('s.class_id', $teacherClasses)
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

    public function getTeacherClasses()
    {
        $teacherId = Teacher::where('auth_id', $this->getLoggedUserId())->value('id');
        $teacher = DB::table('teachers')
            ->select('*')
            ->where('id', $teacherId)
            ->first();
        if($teacher) {
            $classIds = TeacherClasses::where('teacher_id', $teacher->id)->pluck('class_id')->toArray();

            $classes = DB::table('classes')
                ->whereIn('id', $classIds)
                ->select('id','name')
                ->get();

            return $this->sendResponse(['classes' => $classes], '');
        } else {
            return $teacherId;
        }
    }
    public function getTeacherSubjectsByClass($classId)
    {
        $teacherId = Teacher::where('auth_id', $this->getLoggedUserId())->value('id');
        $teacher = DB::table('teachers')
            ->select('*')
            ->where('id', $teacherId)
            ->first();
        if($teacher) {
            $subjectIds = TeacherSubject::where('teacher_id', $teacher->id)->pluck('subject_id')->toArray();

            // Retrieve subjects for the given class taught by the teacher
            $subjects = DB::table('subjects')
                ->whereIn('id', $subjectIds)
                ->where('class_id', $classId)
                ->select('id','name')
                ->get();

            return $this->sendResponse(['subjects' => $subjects], '');
        } else {
            return $teacherId;
        }
    }


}
