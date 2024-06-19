<?php

namespace App\Http\Controllers\Api;

use App\Models\Ebook;
use App\Models\School;
use App\Models\Wallet;
use App\Models\WalletLog;
use App\Models\Student;
use App\Models\CaseStudy;
use App\Models\AuthToken;
use App\Models\Auth;

use App\Models\ParentModel;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ProjectReport;
use App\Models\Auth as AuthModel;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Hash;
use App\Http\Constants\AuthConstants;
use Illuminate\Support\Facades\Validator;
use App\Services\Student\DashboardService;

class StudentController extends BaseController
{
    /**
     * Fetch dashboard items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboard(Request $request, DashboardService $dashboardService)
    {
        $userType = $request->attributes->get('type');
        if ($userType === 'parent' || $userType = 'student') {
            $dashboard =  $dashboardService->getStudentDashboardItems($request->studentId);
            return $this->sendResponse(['dashboard' => $dashboard]);
        } else {
            return $this->sendAuthError("Not authorized.");
        }
    }

    /**
     * Display a listing of the students.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudentsList(Request $request)
    {
        $userType = $request->attributes->get('type');
        if ($userType === 'admin' || $userType = 'school') {
            $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');
            $students = DB::table('students as s')
                ->select('s.*', 'a.*', 'c.name as class_name')
                ->leftJoin('auth as a', 'a.id', '=', 's.auth_id')
                ->leftJoin('classes as c', 'c.id', '=', 's.class_id')
                ->where('school_id', $schoolId)
                ->get();
            return $this->sendResponse(['students' => $students]);
        } else {
            return $this->sendAuthError("Not authorized fetch schools list.");
        }
    }
    /**
     * Display a listing of the public students.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPublicStudentDetailsFromStudent(Request $request)
    {
        $userType = $request->attributes->get('type');
        if ($userType === 'admin') {
            $classId = $request->query('classId');
            $sectionId = $request->query('sectionId');
            // $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');
            $query = DB::table('students as s')
                ->select('s.*', 'a.*','s.id as student_id', 'c.name as class_name','sec.name as section_name')
                ->leftJoin('auth as a', 'a.id', '=', 's.auth_id')
                ->leftJoin('classes as c', 'c.id', '=', 's.class_id')
                ->leftJoin('sections as sec', 'sec.id', '=', 's.section_id')
                ->where('s.student_type', true);
                // ->where('school_id', $schoolId)

                if ($classId) {
                    $query->where('s.class_id', $classId);
                }

                if ($sectionId) {
                    $query->where('s.section_id', $sectionId);
                }

                $students = $query->paginate(10);
            return $this->sendResponse(['students' => $students]);
        } else {
            return $this->sendAuthError("Not authorized fetch schools list.");
        }
    }
    /**
     * Display a listing of the public students.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPrivateStudentDetailsFromStudent(Request $request)
    {
        $userType = $request->attributes->get('type');
        if ($userType === 'admin') {
            $schoolId = $request->query('schoolId');
            $classId = $request->query('classId');
            $sectionId = $request->query('sectionId');
            // $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');
            $query = DB::table('students as s')
                ->select('s.*', 'a.*','s.id as student_id', 'c.name as class_name','sec.name as section_name')
                ->leftJoin('auth as a', 'a.id', '=', 's.auth_id')
                ->leftJoin('classes as c', 'c.id', '=', 's.class_id')
                ->leftJoin('sections as sec', 'sec.id', '=', 's.section_id')
                // ->where('school_id', $schoolId)
                ->where('s.student_type', false);
                if ($schoolId) {
                    $query->where('s.school_id', $schoolId);
                }
                if ($classId) {
                    $query->where('s.class_id', $classId);
                }

                if ($sectionId) {
                    $query->where('s.section_id', $sectionId);
                }

                $students = $query->paginate(10);
            return $this->sendResponse(['students' => $students]);
        } else {
            return $this->sendAuthError("Not authorized fetch schools list.");
        }
    }

    /**
     * Display a listing of the students by class.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudentsByClassAndSection(Request $request, $classId, $sectionId)
    {
        $userType = $request->attributes->get('type');
        if ($userType === 'admin' || $userType = 'school') {
            $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');
            $query = DB::table('students as s')
            ->select('s.id as student_id', 's.*', 'a.*', 'c.name as class_name', 'sections.name as section_name')
            ->leftJoin('auth as a', 'a.id', '=', 's.auth_id')
            ->leftJoin('classes as c', 'c.id', '=', 's.class_id')
            ->leftJoin('sections', 'sections.id', '=', 's.section_id')
            ->where('school_id', $schoolId);

            if ($classId !== "null") {
                $query->where('s.class_id', $classId);
            }
            if ($sectionId !== "null") {
                $query->where('s.section_id', $sectionId);
            }

            $students = $query->get();
            return $this->sendResponse(['students' => $students]);
        } else {
            return $this->sendAuthError("Not authorized fetch schools list.");
        }
    }
    /**
     * Display the specified student.
     *
     * @param $studentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudentDetails(Request $request, $studentId)
    {
        $res = [];
        $userType = $request->attributes->get('type');
        if ($userType === 'admin' || $userType = 'school' || $userType === 'teacher') {
            $validator = Validator::make(['student_id' => $studentId], [
                'student_id' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->sendValidationError($validator);
            } else {
                // $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');
                $student = DB::table('students as s')
                    ->select('s.*', 'c.name as class', 'sec.name as section', 'p.name as parent', 'p.parent_code')
                    ->where('s.auth_id', $studentId)
                    // ->where('s.school_id', $schoolId)
                    ->leftJoin('classes as c', 'c.id', 's.class_id')
                    ->leftJoin('sections as sec', 'sec.id', 's.section_id')
                    ->leftJoin('parents as p', 'p.id', 's.parent_id')
                    ->first();
                Log::info('Fetched student details: ', ['student' => $student]);
                if ($student) {
                    $auth = AuthModel::where('id', $studentId)
                        ->where('type', AuthConstants::TYPE_STUDENT)
                        ->first();
                }

                if ($student && $auth) {
                    $res = [
                        'id' => $student->id,
                        'auth_id' => $student->auth_id,

                        'school_id' => $student->school_id,
                        'class_id' => $student->class_id,
                        'section_id' => $student->section_id,
                        'class' => $student->class,
                        'section' => $student->section,
                        'name' => $student->name,
                        'email' => $auth->email,
                        'username' => $auth->username,
                        'phone_number' => $auth->phone_number,
                        'roll_number' => $student->roll_number,
                        'profile_image' => $student->profile_image,
                        'dob' => $student->dob,
                        'address' => $student->address,
                        'city' => $student->city,
                        'state' => $student->state,
                        'pincode' => $student->pincode,
                        'remarks' => $student->remarks,
                        'parent_name' => $student->parent,
                        'parent_id' => $student->parent_id,
                        'parent_code' => $student->parent_code,
                        // 'parent_name' => $student->parent_name,
                    ];

                    return $this->sendResponse(['student' => $res]);
                } else {
                    return $this->sendResponse([], 'Failed to fetch student details.1');
                }
            }
        } else {
            return $this->sendAuthError("Not authorized fetch schools list.");
        }
    }
    public function getStudentDetailsFromStudent($studentId)
    {

        $validator = Validator::make(['student_id' => $studentId], [
            'student_id' => 'required',
        ]);
        if (isset($validator) && $validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        // Fetch the student model
        // $schoolId = School::where('auth_id',  $this->getLoggedUserId())->value('id');
        $student_detail = Student::find($studentId);

        $student = DB::table('students as s')
        ->leftJoin('classes as c', 's.class_id', '=', 'c.id')
        ->leftJoin('sections as sec', 's.section_id', '=', 'sec.id')
        ->leftJoin('schools as sch', 's.school_id', '=', 'sch.id')
        ->leftJoin('parents as par', 's.parent_id', '=', 'par.id')
        ->select(
            's.*',
            'c.name as class_name',
            'sec.name as section_name',
            'sch.name as school_name',
            'par.name as parent_name',
            'par.parent_code'
        )
        ->where('s.auth_id', $student_detail->auth_id)
        ->first();
        Log::info('Fetched student details: ', ['student' => $student]);
        if ($student) {
            $auth = AuthModel::where('id', $student_detail->auth_id)
                ->where('type', AuthConstants::TYPE_STUDENT)
                ->first();
        }



        if (!$student) {
            return $this->sendResponse([], 'Student not found.', 404);
        }



        $subjects = DB::table('subjects as s')
        ->select('s.id', 's.name', 's.image')
        ->leftJoin('classes as c', 's.class_id', '=', 'c.id')
        ->where('s.class_id', $student->class_id)
        ->get();

        // Preparing response
        $res = [
            'student_id' => $student->id,
            'student_auth_id' => $student->auth_id,
            'student_name' => $student->name,
            'parent_id' => $student->parent_id ? $student->parent_id : null,
            'parent_code' => $student->parent_code ? $student->parent_code : null,
            'parent_name' => $student->parent_name ? $student->parent_name : null,
            // 'parent' => $student->parent,
            'school_id' => $student->school_id,
            'school_name' => $student->school_name,
            'class_id' => $student->class_id,
            'class_name' => $student->class_name ? $student->class_name : null,
            'section_id' => $student->section_id,
            'section_name' => $student->section_name ? $student->section_name : null,

            // 'class' => $student->class,
            // 'section' => $student->section,
            'name' => $student->name,
            'email' => $auth->email,
            'username' => $auth->username,
            'phone_number' => $auth->phone_number,
            'roll_number' => $student->roll_number,
            'profile_image' => $student->profile_image,
            'dob' => $student->dob,
            'address' => $student->address,
            'city' => $student->city,
            'state' => $student->state,
            'pincode' => $student->pincode,
            'remarks' => $student->remarks,
            'subjects' => $subjects !== null ? $subjects : null,
        ];

        return $this->sendResponse(['student' => $res]);
    }



    /**
     * Store student
     *
     * @param Request $request
     * @return void
     */
    public function storeStudentDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:auth,email',
            'phone_number' => 'required|string|min:10|max:10',
            'class_id' => 'required|exists:classes,id',
        ]);

        $loggedUser = $this->getLoggerUser();
        if ($loggedUser->type === AuthConstants::TYPE_SCHOOL) {
            $initials = School::where('auth_id', $this->getLoggedUserId())
                ->selectRaw("CONCAT(LEFT(name, 1), IF(LOCATE(' ', name), LEFT(SUBSTRING_INDEX(name, ' ', -1), 1), '')) as starting_letters")
                ->value('starting_letters');
            $schoolId = School::where('auth_id', $loggedUser->id)->value('id');
            $parentId = null; // assigining parent as null as student added by school
            $studentType = 0;
        } elseif ($loggedUser->type === AuthConstants::TYPE_PARENT) {
            $studentName = $request->name;
            $initials = substr($studentName, 0, 1);
            $schoolId = 1; // Assigning schoolId 1 if its parent. a default school added through seeder
            $parentId = ParentModel::where('auth_id', $loggedUser->id)->value('id');
            $studentType = 1;
        }
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $length = 6;
            $min = pow(10, $length - 1);
            $max = pow(10, $length) - 1;
            $rand_number = mt_rand($min, $max);

              // Generate email if not provided
        $email = $request->email ?: $initials . $rand_number . '@gmail.com';

        // Ensure the generated email is unique
        while (AuthModel::where('email', $email)->exists()) {
            $rand_number = mt_rand($min, $max);
            $email = $initials . $rand_number . '@gmail.com';
        }
            $auth = AuthModel::create([
                'username' => $initials . $rand_number,
                'password' => Hash::make('abc123'),
                'email' => $email,
                'phone_number' => $request->phone_number,
                'type' => AuthConstants::TYPE_STUDENT,
                'status' => AuthConstants::STATUS_ACTIVE,
            ]);

            if ($auth) {
                $student = Student::create([
                    'auth_id' => $auth->id,
                    'school_id' => $schoolId,
                    'parent_id' => $parentId,
                    'student_type' => $studentType,
                    'class_id' => $request->class_id,
                    'section_id' => $request->section_id,
                    'name' => $request->name,
                    'profile_image' => $request->profile_image,
                    'dob' => $request->doj,
                    'phone_number' => $request->phone_number,
                    'address' => $request->address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'pincode' => $request->pincode,
                    'description' => $request->description,
                ]);
            }
            if ($auth && $student) {
                return $this->sendResponse([], 'Student added successfully');
            }
        }
    }


    /**
     * Update the parent for the specified student.
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateParentDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'parent_code' => 'required|exists:parents,parent_code|numeric',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $studentId = $this->getLoggedUserId();
            $parent = ParentModel::where('parent_code', $request->parent_code)->first();

            if ($parent) {
                $student = Student::where('auth_id', $studentId)->first();
                $student->update([
                    'parent_id' => $parent->id,
                ]);
                return $this->sendResponse(['parent_id' => $parent->id, 'parent_name' => $parent->name, 'parent_code' => $parent->parent_code], 'Connected to parent successfully!');
            }
        }
        return $this->sendError('Failed to connected to parent', [], 400);
    }


    public function updateStudentPassword(Request $request, $studentId)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6',
            'confirmPassword' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $auth = AuthModel::where('id', $studentId)->first();

        if ($auth) {
            $auth->update([
                'password' => bcrypt($request->password),
            ]);

            $updatedAuth = AuthModel::select('id', 'username', 'email', 'phone_number')
                ->where('id', $studentId)
                ->first();

            return $this->sendResponse(['auth' => $updatedAuth], 'Credentials updated successfully.');
        } else {
            return $this->sendError('Failed to update admin credentials', [], 404);
        }
    }

    /**
     * Show the form for editing the specified student.
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStudentDetails(Request $request, $studentId)
    {
        $res = [];
        $validator = Validator::make(array_merge($request->all(), ['studentId' => $studentId]), [
            'name' => 'required|string|max:255',
            'studentId' => 'required',
            'password' => 'nullable|min:6',
            'email' => 'nullable|string|email|max:255|unique:auth,email,' . $studentId,
            'phone_number' => 'required|string|min:10|max:10',
            // 'class_id' => 'required|numeric',
            // 'section_id' => 'required|numeric',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'dob' => 'required',
            'pincode' => 'required',
            'address' => 'required|string',
            'password' => 'required|string|min:6',
            'confirmPassword' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        // $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');
        $schoolId = 1;
        $student = Student::where('auth_id', $studentId)
            ->where('school_id', $schoolId)
            ->first();

        if ($student) {
            $auth = AuthModel::where('id', $studentId)
                ->where('type', AuthConstants::TYPE_STUDENT)
                ->first();
        }
        if ($auth && $student) {
                    // If no email is provided, generate one using the username
        $email = $request->input('email') ?: $auth->username . '@gmail.com';

         // Ensure the generated email is unique
        //  while (AuthModel::where('email', $email)->exists()) {
        //     $rand_number = mt_rand(1000, 9999);
        //     $email = $auth->username . $rand_number . '@gmail.com';
        // }
            $authData = [
                // 'email' => $request->input('email', $auth->email),
                'email' => $email,
                'password' => $request->input('password') ? Hash::make($request->password) : $auth->password,
                'phone_number' => $request->input('phone_number', $auth->phone_number),
            ];

            $auth->update($authData);

            if ($request->hasFile('profile_image')) {
                if ($student->profile_image) {
                    File::delete(public_path($student->profile_image));
                }

                $extension = $request->file('profile_image')->extension();
                $filename = Str::random(4) . time() . '.' . $extension;
                $student->profile_image = $request->file('profile_image')->move(('uploads/images/student'), $filename);
            }

            $studentData = [
                'name' => $request->input('name', $student->name),
                'class_id' => $request->input('class_id', $student->class_id),
                'section_id' => $request->input('section_id', $student->section_id),
                'dob' => $request->input('dob', $student->dob),
                'phone_number' => $request->input('phone_number', $student->phone_number),
                'address' => $request->input('address', $student->address),
                'city' => $request->input('city', $student->city),
                'state' => $request->input('state', $student->state),
                'pincode' => $request->input('pincode', $student->pincode),
                'description' => $request->input('description', $student->description),
                'dob' => $request->input('dob', $student->dob),
                'pincode' => $request->input('pincode', $student->pincode),
                'address' => $request->input('address', $student->address),
            ];

            $student->update($studentData);

            $res = [
                'id' => $student->id,
                'auth_id' => $student->auth_id,
                'parent_id' => $student->parent_id,
                'school_id' => $student->school_id,
                'class_id' => $student->class_id,
                'email' => $auth->email,
                'username' => $auth->username,
                'phone_number' => $auth->phone_number,
                'roll_number' => $student->roll_number,
                'profile_image' => $student->profile_image,
                'dob' => $student->dob,
                'address' => $student->address,
                'city' => $student->city,
                'state' => $student->state,
                'pincode' => $student->pincode,
                'remarks' => $student->remarks,
            ];
        }

        return $this->sendResponse($res, 'Student updated successfully');
    }


    /**
     * Remove the specified student from storage.
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteStudentDetails(Request $request, $studentId)
    {
        $userType = $request->attributes->get('type');
        if ($userType = 'school') {
            $validator = Validator::make(['studentId' => $studentId], [
                'studentId' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->sendValidationError($validator);
            } else {
                $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');
                $student = Student::where('auth_id', $studentId)->where('school_id', $schoolId)->first();
                $auth = AuthModel::find($studentId);
                if ($student) {
                    $student->delete();
                    $auth->delete();
                } else {
                    return $this->sendError("Trying to delete a invalid student.");
                }
            }

            return $this->sendResponse([], 'Student deleted successfully');
        } else {
            return $this->sendAuthError("Not authorized delete student.");
        }
    }

    /**
     * Fetch the specified parent of the student from storage.
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getParentDetails(Request $request, $studentId)
    {
        $validator = Validator::make(['studentId' => $studentId], [
            'studentId' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $parent = DB::table('students as s')
                ->select('p.id', 'p.name', 'p.auth_id', 'p.parent_code')
                ->leftJoin('parents as p', 'p.id', 's.parent_id')
                ->where('s.id', $studentId)
                ->first();
            return $this->sendResponse(['parent' => $parent], '');
        }
    }
    public function updatePaymentStatus(Request $request, $studentId)
    {
        \Log::error('Failed to update payment status.', ['request' => $request->all()]);

        DB::beginTransaction();
    
        try {
            $student = DB::table('students')->where('id', $studentId)->first();
            
            if (!$student) {
                return $this->sendError('Student not found.');
            }
    
            DB::table('students')
                ->where('id', $studentId)
                ->update(['is_paid' => true]);
    
            $auth = Auth::find($student->auth_id);
    
            // Retrieve the existing token
            $existingToken = AuthToken::where('auth_id', $auth->id)->latest()->first();
    
            // Ensure referral_amount and referrer_amount are set
            $referralAmount = $request->input('referral_amount', 0);
            $referrerAmount = $request->input('referrer_amount', 0);
    
            // Update or create wallet entry for the student
            $wallet = Wallet::updateOrCreate(
                ['auth_id' => $auth->id],
                ['balance' => DB::raw('balance + '.$referralAmount)]
            );
    
            // Log the referral amount
            if ($referralAmount > 0) {
                WalletLog::create([
                    'wallet_id' => $wallet->id,
                    'amount' => $referralAmount,
                    'type' => 'referral',
                ]);
            }
            $referrerName = null;
            // Handle referral code logic
            if ($request->filled('referral_code')) {
                $referrerAuth = Student::where('student_unique_code', $request->referral_code)->first();
                if ($referrerAuth) {
                    $referrerWallet = Wallet::firstOrCreate(['auth_id' => $referrerAuth->auth_id]);
                    $referrerWallet->increment('balance', $referrerAmount);
    
                    // Log the referrer amount
                    if ($referrerAmount > 0) {
                        WalletLog::create([
                            'wallet_id' => $referrerWallet->id,
                            'amount' => $referrerAmount,
                            'type' => 'referrer',
                        ]);
                    }
                    $referrerName = $referrerAuth->name;
                }
            }
    //referrename is pending to send in the response to show in the toast
    //but , need to reload the window, so no toastr will be shown\
    //suucess toast is pending
            DB::commit();
            $ip_address = $request->ip();
            $browser = $request->header('User-Agent');
            return $this->sendResponseWithToken($existingToken->token, $auth,$ip_address,$browser);
    
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to update payment status.', ['error' => $e->getMessage()]);
            return $this->sendError('Failed to update payment status.', [$e->getMessage()]);
        }
  
    }

}
