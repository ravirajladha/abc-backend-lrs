<?php

namespace App\Http\Controllers\Api;
use Illuminate\Validation\Rule;

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
use App\Models\Fee;

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
        if ( $userType == 'student') {
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
        if ($userType === 'admin' ) {

            $students = DB::table('students as s')
                ->select('s.*', 'a.*', 'c.name as class_name')
                ->leftJoin('auth as a', 'a.id', '=', 's.auth_id')
                ->get();
            return $this->sendResponse(['students' => $students]);
        } else {
            return $this->sendAuthError("Not authorized fetch students list.");
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
        // Fetch only student and related auth details
        $students = DB::table('students as s')
            ->select('s.*', 'a.*', 's.id as student_id')
            ->leftJoin('auth as a', 'a.id', '=', 's.auth_id')
            ->paginate(10);

        return $this->sendResponse(['students' => $students]);
    } else {
        return $this->sendAuthError("Not authorized to fetch the students list.");
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
                ->select('s.*', 'a.*','s.id as student_id')
                ->leftJoin('auth as a', 'a.id', '=', 's.auth_id')
                ->get();




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
    public function getStudentsByClassAndSection(Request $request)
    {
        $userType = $request->attributes->get('type');
        if ($userType === 'admin' || $userType = 'internship_admin') {

            $query = DB::table('students as s')
            ->select('s.id as student_id', 's.*', 'a.*', 'c.name as class_name', 'sections.name as section_name')
            ->leftJoin('auth as a', 'a.id', '=', 's.auth_id');




            $students = $query->get();
            return $this->sendResponse(['students' => $students]);
        } else {
            return $this->sendAuthError("Not authorized fetch schools list.");
        }
    }

    // get wallet and wallet logs detail from the student auth id
    public function getWalletDetailsAndLogs( $studentAuthId)
    {

        // Fetch the wallet details using the student's auth_id
        $walletDetails = DB::table('wallets as w')
            ->select('w.*')
            ->where('w.auth_id', $studentAuthId)
            ->first();

        if ($walletDetails) {
            // Fetch the wallet logs in descending order using the wallet_id
            $walletLogs = DB::table('wallet_logs as wl')
                ->select('wl.*')
                ->where('wl.wallet_id', $walletDetails->id)
                ->orderBy('wl.created_at', 'desc')
                ->get();

            return $this->sendResponse([
                'wallet_details' => $walletDetails,
                'wallet_logs' => $walletLogs
            ]);
        } else {
            return $this->sendError("No wallet details found for the specified student.");
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
        if ($userType === 'admin' || $userType = 'internship_admin' || $userType === 'trainer') {
            $validator = Validator::make(['student_id' => $studentId], [
                'student_id' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->sendValidationError($validator);
            } else {
                // $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');
                $student = DB::table('students as s')
                    ->select('s.*')
                    ->where('s.auth_id', $studentId)
                    // ->where('s.school_id', $schoolId)
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

                        'name' => $student->name,
                        'email' => $auth->email,
                        'username' => $auth->username,
                        'phone_number' => $auth->phone_number,

                        'profile_image' => $student->profile_image,
                        'dob' => $student->dob,
                        'address' => $student->address,
                        'city' => $student->city,
                        'state' => $student->state,
                        'pincode' => $student->pincode,



                        'student_status' => $student->status,
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


        $student = DB::table('students as s')->select( 's.*')
        ->where('s.auth_id', $studentId)
        ->first();
        Log::info('Fetched student details: ', ['student' => $student]);
        if ($student) {
            $auth = AuthModel::where('id', $studentId)
                ->where('type', AuthConstants::TYPE_STUDENT)
                ->first();
        }

        if (!$student) {
            return $this->sendResponse([], 'Student not found.', 404);
        }

        $courses = DB::table('courses as cou')
        ->select('cou.id', 'cou.name', 'cou.image')
        ->leftJoin('subjects as s', 'cou.subject_id', '=', 's.id')
        ->get();

        // Preparing response
        $res = [
            'student_id' => $student->id,
            'student_auth_id' => $studentId,
            'student_name' => $student->name,
            // 'class' => $student->class,
            // 'section' => $student->section,
            'name' => $student->name,
            'email' => $auth->email,
            'username' => $auth->username,
            'phone_number' => $auth->phone_number,

            'profile_image' => $student->profile_image,
            'dob' => $student->dob,
            'address' => $student->address,
            'city' => $student->city,
            'state' => $student->state,
            'pincode' => $student->pincode,
            // 'remarks' => $student->remarks,
            'courses' => $courses !== null ? $courses : null,
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

        ]);

        $loggedUser = $this->getLoggerUser();

        // added by admin
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            function generateUniqueStudentCode() {
                do {
                    // Generate a random 8-digit number
                    $code = 'S' . sprintf('%08d', mt_rand(1, 99999999));
                } while (Student::where('student_unique_code', $code)->exists());
                return $code;
            }

            $auth = AuthModel::create([
                'username' => $request->name,
                'password' => Hash::make('abc123'),
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'type' => AuthConstants::TYPE_STUDENT,
                'status' => AuthConstants::STATUS_ACTIVE,
            ]);

            if ($auth) {
                $student = Student::create([
                    'auth_id' => $auth->id,
                    'name' => $request->name,
                    'student_unique_code' => generateUniqueStudentCode(),
                ]);

                Wallet::create([
                    'auth_id' => $auth->id,
                    'balance' => 0, // Initial balance
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
        Log::info("studentd ata", $request->all());
        $res = [];
        $validator = Validator::make(array_merge($request->all(), ['studentId' => $studentId]), [
            'name' => 'required|string|max:255',
            'studentId' => 'required',
            'password' => 'nullable|min:6',
            'email' => 'nullable|string|email|max:255|unique:auth,email,' . $studentId,
            'phone_number' => 'required|string|min:10|max:10',

            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'dob' => 'required',
            'pincode' => 'required',
            'address' => 'required|string',
            'status' => 'required',
           'confirmPassword' => 'nullable|required_with:password|string|min:6|same:password',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        // $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');
        $student = Student::where('auth_id', $studentId)
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
                'status' => $request->input('status', $auth->status),
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

                'dob' => $request->input('dob', $student->dob),
                'phone_number' => $request->input('phone_number', $student->phone_number),
                'address' => $request->input('address', $student->address),
                'city' => $request->input('city', $student->city),
                'state' => $request->input('state', $student->state),
                'pincode' => $request->input('pincode', $student->pincode),
                'description' => $request->input('description', $student->description),

                'status' => $request->input('status', $student->status),
            ];

            $student->update($studentData);

            $res = [
                'id' => $student->id,
                'auth_id' => $student->auth_id,

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
                'student_status' => $student->status,
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
        if ($userType = 'admin') {
            $validator = Validator::make(['studentId' => $studentId], [
                'studentId' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->sendValidationError($validator);
            } else {

                $student = Student::where('auth_id', $studentId)->first();
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

    public function updatePaymentStatus(Request $request)
    {
        $loggedUserId = $this->getLoggedUserId();

        $student = Student::where('auth_id',$loggedUserId)->first();
        $studentId = $student->id;
        \Log::info('Starting updatePaymentStatus function.', ['request' => $request->all()]);

        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'referral_code' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        try {
            $student = DB::table('students')->where('id', $studentId)->first();
            \Log::info('Student retrieved.', ['student' => $student]);

            if (!$student) {
                return $this->sendError('Student not found.');
            }

            $auth = Auth::find($student->auth_id);
            \Log::info('Auth retrieved.', ['auth' => $auth]);

            // Assume these values are provided in the request
            $transactionId = Str::uuid();
            $amount = 5000;
            $paymentMethod = 'Online';
            $status = 'success';  // 'failed', 'pending', 'success'
            $errorMessage = $request->input('error_message', null);
            $ip_address = $request->ip();
            $browser = $request->header('User-Agent');
            $referralAmount = $request->input('referral_amount', 0);
            $referrerAmount = $request->input('referrer_amount', 0);

            // Insert transaction record
            DB::table('transactions')->insert([
                'student_id' => $student->auth_id,
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'status' => $status,
                'payment_method' => $paymentMethod,
                'error_message' => $errorMessage,
                'referral_code' => $request->input('referral_code'),
                'referral_amount' => $referralAmount,
                'referrer_amount' => $referrerAmount,
                'ip_address' => $ip_address,
                'browser_info' => $browser,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            \Log::info('Transaction record inserted.', ['transaction_id' => $transactionId]);

            if ($status == 'success') {
                \Log::info('Status is success, updating student is_paid.');

                $updateResult = DB::table('students')
                    ->where('id', $studentId)
                    ->update(['is_paid' => true]);

                // \Log::info('Student update result.', ['updateResult' => $updateResult]);

                // not required now as its one subscription not for individual course

                // DB::table('purchased_courses')->insert([
                //     'student_id' => $studentId,
                //     'course_id' => $request->input('course_id'), // Assume course_id is provided in the request
                //     'transaction_id' => $transactionTableId,
                //     'created_at' => now(),
                //     'updated_at' => now(),
                // ]);


                // Retrieve the existing token
                $existingToken = AuthToken::where('auth_id', $auth->id)->latest()->first();

                // Handle referral code logic only if it's valid and not the user's own code
                if ($request->filled('referral_code') && $student->student_unique_code !== $request->referral_code) {
                    $referrerAuth = Student::where('student_unique_code', $request->referral_code)->first();
                    \Log::info('Referrer auth retrieved.', ['referrerAuth' => $referrerAuth]);

                    if ($referrerAuth) {
                        if ($referralAmount > 0) {
                            $wallet = Wallet::updateOrCreate(
                                ['auth_id' => $auth->id],
                                ['balance' => DB::raw('balance + ' . $referralAmount)]
                            );
                            \Log::info('Wallet updated or created for student.', ['wallet' => $wallet]);

                            WalletLog::create([
                                'wallet_id' => $wallet->id,
                                'amount' => $referralAmount,
                                'type' => 'referral',
                            ]);
                            \Log::info('Referral amount logged.');
                        }

                        $referrerWallet = Wallet::firstOrCreate(['auth_id' => $referrerAuth->auth_id]);
                        \Log::info('Referrer wallet retrieved or created.', ['referrerWallet' => $referrerWallet]);

                        if ($referrerAmount > 0) {
                            $referrerWallet->increment('balance', $referrerAmount);

                            WalletLog::create([
                                'wallet_id' => $referrerWallet->id,
                                'amount' => $referrerAmount,
                                'type' => 'referrer',
                            ]);
                            \Log::info('Referrer amount logged.');
                        }
                    }
                }

                DB::commit();
                \Log::info('Transaction committed successfully.');

                return $this->sendResponseWithToken($existingToken->token, $auth, $ip_address, $browser);
            } else {
                DB::commit();
                \Log::info('Payment status update failed.', ['status' => $status]);
                return $this->sendError('Payment status update failed.', [], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to update payment status.', ['error' => $e->getMessage()]);
            return $this->sendError('Failed to update payment status.', [$e->getMessage()]);
        }
    }


    public function updateStatus(Request $request)
    {
        // Find the forum post by ID
        $student = AuthModel::find($request->student_auth_id);

        // Update the status
        $student->status = $request->status;
        $student->save();
        return $this->sendResponse(['student' => $student], 'Student status updated successfully');
    }

    public function getFeeAndStatus(){
        $loggedUserId = $this->getLoggedUserId();

        $student = DB::table('students')
        ->where('auth_id', $loggedUserId)->first();
        $fee = Fee::first();
        $fee->is_paid = $student->is_paid;
        return $this->sendResponse(['fee' => $fee], 'Fees fetched successfully.');
    }
}
