<?php

namespace App\Http\Controllers\Api;

use App\Models\School;
use App\Models\Subject;
use App\Models\Course;
use App\Models\Trainer;
use App\Models\TrainerSubject;
use App\Models\TrainerCourse;
use App\Models\Auth as AuthModel;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Http\Constants\AuthConstants;

use App\Services\Trainer\DashboardService;

class TrainerController extends BaseController
{
    /**
     * Fetch dashboard items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboard(Request $request, DashboardService $dashboardService)
    {
        $trainerId = Trainer::where('auth_id', $this->getLoggedUserId())->value('id');

        $dashboard =  $dashboardService->getTrainerDashboardItems($trainerId);
        return $this->sendResponse(['dashboard' => $dashboard]);
    }

    /**
     * Update trainer details
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTrainerPassword(Request $request, $trainerId)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6',
            'confirmPassword' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $auth = AuthModel::where('id', $trainerId)->first();

        if ($auth) {
            $auth->update([
                'password' => bcrypt($request->password),
            ]);

            $updatedAuth = AuthModel::select('id', 'username', 'email', 'phone_number')
                ->where('id', $trainerId)
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
    public function getTrainersList(Request $request)
    {
        $userType = $request->attributes->get('type');
        if ($userType === 'admin') {
            $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');

            $trainers = DB::table('trainers as t')
                ->select('t.id', 't.auth_id', 't.name', 't.emp_id', 't.profile_image', 't.phone_number', 't.doj', 't.address', 't.city', 't.state', 't.pincode', 't.type', 'a.email', 'a.username', 'a.phone_number', 'a.status')
                ->join('auth as a', 't.auth_id', '=', 'a.id')
                // ->where('t.school_id', $schoolId)
                ->get();

            foreach ($trainers as $trainer) {
                $trainer->trainer_courses = DB::table('trainer_courses as tc')
                    ->select('tc.course_id', 'cou.name as course_name', 's.name as subject_name')
                    ->leftJoin('courses as cou', 'cou.id', 'tc.course_id')
                    ->leftJoin('subjects as s', 's.id', 'cou.subject_id')
                    ->where('tc.trainer_id', $trainer->id)
                    ->get();
                $trainer->trainer_subjects = DB::table('trainer_subjects as ts')
                    ->select('ts.subject_id', 'c.name as subject_name')
                    ->leftJoin('subjects as c', 'c.id', 'ts.subject_id')
                    ->where('ts.trainer_id', $trainer->id)
                    ->get();
            }

            return $this->sendResponse(['trainers' => $trainers]);
        } else {
            return $this->sendAuthError("Not authorized to fetch trainers list.");
        }
    }

    /**
     * Display the specified trainer.
     *
     */
    public function getTrainerDetails($trainerId)
    {
        $res = [];
        $trainer_subjects = [];
        $trainer_courses = [];

        $validator = Validator::make(['trainerId' => $trainerId], [
            'trainerId' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            // $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');
            $trainer = Trainer::where('auth_id', $trainerId)
                // ->where('school_id', $schoolId)
                ->first();

            if ($trainer) {
                $auth = AuthModel::where('id', $trainerId)
                    ->where('type', AuthConstants::TYPE_TRAINER)
                    ->first();

                $trainer_subjects = DB::table('trainer_subjects as ts')
                    ->select('ts.subject_id', 's.name as subject_name')
                    ->leftJoin('subjects as s', 's.id', 'ts.subject_id')
                    ->where('ts.trainer_id', $trainer->id)
                    ->get();

                $trainer_courses = DB::table('trainer_courses as tc')
                    ->select('tc.course_id', 'c.name as course_name', 'c.name as subject_name')
                    ->leftJoin('courses as c', 'c.id', 'tc.course_id')
                    ->leftJoin('subjects as s', 's.id', 'c.subject_id')
                    ->where('tc.trainer_id', $trainer->id)
                    ->get();
            }

            if ($trainer && $auth) {
                $res = [
                    'id' => $trainer->id,
                    'auth_id' => $trainerId,
                    // 'school_id' => $trainer->school_id,
                    'name' => $trainer->name,
                    'email' => $auth->email,
                    'username' => $auth->username,
                    'phone_number' => $auth->phone_number,
                    'emp_id' => $trainer->emp_id,
                    'profile_image' => $trainer->profile_image,
                    'doj' => $trainer->doj,
                    'address' => $trainer->address,
                    'city' => $trainer->city,
                    'state' => $trainer->state,
                    'pincode' => $trainer->pincode,
                    'description' => $trainer->description,
                    'type' => $trainer->type,
                ];
                return $this->sendResponse(['trainer' => $res, 'trainer_subjects' => $trainer_subjects, 'trainer_courses' => $trainer_courses]);
            } else {
                return $this->sendResponse([], 'Failed to fetch trainer details.');
            }
        }
    }


    /**
     * Store trainer
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeTrainerDetails(Request $request)
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
                'type' => AuthConstants::TYPE_TRAINER,
                'status' => AuthConstants::STATUS_ACTIVE,
            ]);

            // $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');

            if ($auth) {
                $trainer = Trainer::create([
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
            if ($auth && $trainer) {
                return $this->sendResponse([], 'Trainer added successfully');
            }
        }
        return $this->sendError('Failed to add Trainer');
    }


    /**
     * Get trainer subjects and Courses
     *
     * @param int $trainerId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTrainerSubjectsAndCourses($trainerId)
    {
        $trainerSubjectsCourses = [];

        $trainer = DB::table('trainers')
            ->select('*')
            ->where('auth_id', $trainerId)
            ->first();

        if (!$trainer) {
            return $this->sendError('Trainer not found');
        }

        // $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');

        $trainerCourses = TrainerCourse::where([
            'trainer_id' => $trainer->id,
            // 'school_id' => $schoolId,
        ])->get(['course_id']);

        foreach ($trainerCourses as $course) {
            $trainerSubjectCourses[] =  DB::table('courses as cou')
                ->select('cou.id as course_id', 'cou.subject_id')
                ->where('cou.id', $course->course_id)
                ->first();
        }

        // $response = [
        //     'trainer_subjects' => $trainerClasses,
        //     'trainer_courses' => $trainerSubjects,
        // ];

        return $this->sendResponse(['trainer' => $trainerSubjectCourses], '');
    }


    /**
     * Store or update trainer subjects and Courses
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
            'trainer_data.*.subject_id' => 'required|exists:subjects,id',
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
                    // 'school_id' => $schoolId,
                ],
                [
                    'subject_id' => $data['subject_id'],
                    'trainer_id' => $trainer->id,
                    // 'school_id' => $schoolId,
                ]
            );

            TrainerCourse::updateOrCreate(
                [
                    'course_id' => $data['course_id'],
                    'trainer_id' =>  $trainer->id,
                    // 'school_id' => $schoolId,
                ],
                [
                    'course_id' => $data['course_id'],
                    'trainer_id' =>  $trainer->id,
                    // 'school_id' => $schoolId,
                ]
            );
        }

        return $this->sendResponse([], 'Trainer Subjects and courses added or updated successfully');
    }

    /**
     * Update the specified trainer in storage.
     *
     */
    public function updateTrainerDetails(Request $request, $trainerId)
    {
        $res = [];
        $validator = Validator::make(array_merge($request->all(), ['trainer_id' => $trainerId]), [
            'name' => 'required|string|max:255',
            'trainer_id' => 'required|exists:auth,id',
            'password' => 'nullable|min:6',
            'email' => 'required|string|email|max:255',
            'phone_number' => 'numeric|min:10',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust file type and size as needed
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $auth = AuthModel::find($trainerId);
        // $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');

        // $trainer = Trainer::where('auth_id', $trainerId)->where('school_id', $schoolId)->first();
        $trainer = Trainer::where('auth_id', $trainerId)->first();

        if ($auth && $trainer) {
            $authData = [
                'email' => $request->input('email', $auth->email),
                'password' => $request->input('password') ? Hash::make($request->password) : $auth->password,
                'phone_number' => $request->input('phone_number', $auth->phone_number),
            ];

            $auth->update($authData);

            $trainerData = [
                'name' => $request->input('name', $trainer->name),
                'phone_number' => $request->input('phone_number', $trainer->phone_number),
                'address' => $request->input('address', $trainer->address),
                'city' => $request->input('city', $trainer->city),
                'state' => $request->input('state', $trainer->state),
                'pincode' => $request->input('pincode', $trainer->pincode),
                'description' => $request->input('description', $trainer->description),
            ];

            if ($request->hasFile('profile_image')) {
                if ($trainer->profile_image) {
                    File::delete(public_path($trainer->profile_image));
                }

                $extension = $request->file('profile_image')->extension();
                $filename = Str::random(4) . time() . '.' . $extension;
                $request->file('profile_image')->move(('uploads/images/trainer'), $filename);
                $trainerData['profile_image'] = 'uploads/images/trainer/' . $filename;
            }

            $trainer->update($trainerData);

            $res = [
                'id' => $trainer->id,
                'auth_id' => $trainer->auth_id,
                'school_id' => $trainer->school_id,
                'email' => $auth->email,
                'phone_number' => $auth->phone_number,
                'emp_id' => $trainer->emp_id,
                'profile_image' => $trainer->profile_image,
                'doj' => $trainer->doj,
                'address' => $trainer->address,
                'city' => $trainer->city,
                'state' => $trainer->state,
                'pincode' => $trainer->pincode,
                'description' => $trainer->description,
                'type' => $trainer->type,
            ];

            return $this->sendResponse(['trainer' => $res], 'Trainer updated successfully');
        }

        return $this->sendError('Failed to update trainer details.');
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
                // ->select('s.*', 'c.name as class_name')
                // ->join('classes as c', 's.class_id', '=', 'c.id')
                // ->whereIn('s.class_id', $trainerClasses)
                ->get();

            $students =  $students_query;

            $students = $students->map(function($student) use ($trainer) {
                $qna = DB::table('qna as q')
                    ->where('q.student_id', $student->id)
                    ->where('q.trainer_id', $trainer->id)
                    ->whereNull('q.answer')
                    ->orderBy('created_at', 'desc')
                    ->first();

                $student->read_status = $qna ? true : false;
                $student->latest_qna_timestamp = $qna ? $qna->created_at : null;

                return $student;
            });

            $students = $students->sortByDesc('latest_qna_timestamp')->values();

            foreach ($students as $student) {
                unset($student->latest_qna_timestamp);
            }



            return $this->sendResponse(['students' => $students], '');
        } else {
            return $this->sendAuthError("Not authorized get students.");
        }
    }

    public function getTrainerSubjects()
    {
        $trainerId = Trainer::where('auth_id', $this->getLoggedUserId())->value('id');
        $trainer = DB::table('trainers')
            ->select('*')
            ->where('id', $trainerId)
            ->first();
        if($trainer) {
            $subjectIds = TrainerSubject::where('trainer_id', $trainer->id)->pluck('subject_id')->toArray();

            $subjects = DB::table('subjects')
                ->whereIn('id', $subjectIds)
                ->select('id','name')
                ->get();

            return $this->sendResponse(['subjects' => $subjects], '');
        } else {
            return $trainerId;
        }
    }
    public function getTrainerCoursesBySubject($subjectId)
    {
        $trainerId = Trainer::where('auth_id', $this->getLoggedUserId())->value('id');
        $trainer = DB::table('trainers')
            ->select('*')
            ->where('id', $trainerId)
            ->first();
        if($trainer) {
            $courseIds = TrainerCourse::where('trainer_id', $trainer->id)->pluck('course_id')->toArray();

            // Retrieve courses for the given subject taught by the trainer
            $courses = DB::table('courses')
                ->whereIn('id', $courseIds)
                ->where('subject_id', $subjectId)
                ->select('id','name','image')
                ->get();

            return $this->sendResponse(['courses' => $courses], '');
        } else {
            return $trainerId;
        }
    }

    // Define the function
    public function countUnrepliedQnAsForTrainer() {
        $trainerId = Trainer::where('auth_id', $this->getLoggedUserId())->value('id');
        $qnaCount =  DB::table('qna')
            ->where('trainer_id', $trainerId)
            ->whereNull('answer')
            ->count();
        return $this->sendResponse(['qnaCount' => $qnaCount], '');
    }

}
