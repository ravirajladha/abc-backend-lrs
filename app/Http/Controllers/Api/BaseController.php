<?php

namespace App\Http\Controllers\Api;

use App\Models\Auth;
use App\Models\AuthToken;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Constants\AuthConstants;
use App\Models\StudentAuthLog;
use Carbon\Carbon;
use App\Http\Constants\SubjectTypeConstants;

class BaseController extends Controller
{

    protected $_token;

    public function __construct(Request $request)
    {
        $this->_token = $this->getAccessToken($request);
    }


    /**
     * Send success response
     *
     * @param array $data
     * @param string $message
     * @param boolean $status
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendResponse($data = [], $message = '', $status = true)
    {
        $response = [
            'status' => $status,
            'data'    => $data,
            'message' => $message,
        ];
        return response()->json($response, 200);
    }

    /**
     * Send Response with token
     *
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendResponseWithToken($token=null, $auth, $ip_address=null, $browser=null)
    {
        $response = [
            'status' => true,
            'message' => 'Token',
            'access_token' => $token,
            'token_type' => 'bearer',
            'user_type' => $auth->type,
            'user' => [
                'id' => $auth->id,
                'type' => $auth->type,
                'username' => $auth->username,
                'email' => $auth->email,
                'phone_number' => $auth->phone_number
            ],
        ];
        if ($auth->type === AuthConstants::TYPE_STUDENT) {
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
                ->where('s.auth_id', $auth->id)
                ->first();

            $subjects = DB::table('subjects as s')
                ->select('s.id', 's.name', 's.image')
                ->leftJoin('classes as c', 's.class_id', '=', 'c.id')
                ->where('s.class_id', $student->class_id)
                ->whereIn('subject_type', [SubjectTypeConstants::TYPE_DEFAULT_SUBJECT,SubjectTypeConstants::TYPE_SUB_SUBJECT])
                ->get();

            if ($student) {
                if ($auth->type === AuthConstants::TYPE_STUDENT) {
                    StudentAuthLog::create([
                        'student_id' => $auth->id,
                        'school_id' => $student->school_id,
                        'login_at' => Carbon::now(),
                        'ip_address' => $ip_address,
                        'browser' => $browser,
                    ]);
                }

                $response['student_data'] = [
                    'student_id' => $student->id,
                    'student_auth_id' => $student->auth_id,
                    'student_name' => $student->name,
                    'student_type' => $student->student_type,
                    'class_id' => $student->class_id !== null ? $student->class_id : null,
                    'class_name' => $student->class_name ? $student->class_name : null,
                    'section_id' => $student->section_id !== null ? $student->section_id : null,
                    'section_name' => $student->section_name ? $student->section_name : null,
                    'school_id' => $student->school_id,
                    'school_name' => $student->school_name,
                    'subjects' => $subjects !== null ? $subjects : null,
                    'profile_image' => $student->profile_image,
                    'dob' => $student->dob,
                    'address' => $student->address,
                    'city' => $student->city,
                    'state' => $student->state,
                    'pincode' => $student->pincode,
                    'remarks' => $student->remarks,
                    'parent_id' => $student->parent_id,
                    'parent_code' => $student->parent_code,
                    'parent_name' => $student->parent_name,
                ];
            }
        }
        if ($auth->type === AuthConstants::TYPE_SCHOOL) {
            $school = DB::table('schools')
                    ->select('name','school_type')
                    ->where('auth_id', $auth->id)
                    ->first();
            $response['user']['name'] = $school->name;
            $response['user']['school_type'] = $school->school_type;
        }
        if ($auth->type === AuthConstants::TYPE_ADMIN) {
            $response['user']['name'] = $auth->username;
        }
        if ($auth->type === AuthConstants::TYPE_TEACHER) {
            $teacher = DB::table('teachers')
                    ->select('name')
                    ->where('auth_id', $auth->id)
                    ->first();
            $response['user']['name'] = $teacher->name;
        }
        if ($auth->type === AuthConstants::TYPE_RECRUITER) {
            $recruiter = DB::table('recruiters')
                    ->select('name')
                    ->where('auth_id', $auth->id)
                    ->first();
            $response['user']['name'] = $recruiter->name;
        }
        if ($auth->type === AuthConstants::TYPE_STUDENT) {
            $student = DB::table('students')
                    ->select('name')
                    ->where('auth_id', $auth->id)
                    ->first();
            $response['user']['name'] = $student->name;
        }
        if ($auth->type === AuthConstants::TYPE_PARENT) {
            $response['user']['name'] = $auth->username;
        }
        return response()->json($response, 200);
    }

    /**
     * Send error response
     *
     * @param $message
     * @param array $errorTrace
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendError($message, $errorTrace = [], $code = 200)
    {
        $response = [
            'status' => false,
            'message' => $message,
        ];

        if (!empty($errorTrace)) {
            $response['data'] = $errorTrace;
            $errorMessage = [];
            foreach ($errorTrace as $error) {
                $errorMessage[] = $error[0] ?? '';
            }
            $response['message'] = implode(', ', $errorMessage);
        }

        return response()->json($response, $code);
    }

    /**
     * Send permission error response
     *
     * @param $message
     * @param array $errorTrace
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendAuthError($message, $errorTrace = [], $code = 403)
    {
        $response = [
            'status' => false,
            'message' => $message,
        ];

        if (!empty($errorTrace)) {
            $response['data'] = $errorTrace;
            $errorMessage = [];
            foreach ($errorTrace as $error) {
                $errorMessage[] = $error[0] ?? '';
            }
            $response['message'] = implode(', ', $errorMessage);
        }

        return response()->json($response, $code);
    }

    /**
     * Send validation error message
     *
     * @param [type] $validator
     * @param integer $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendValidationError($validator, $code = 400)
    {
        $response = [
            'status' => false,
            'message' => 'Validation Error',
            'data' => []
        ];
        $errorTrace = $validator->errors()->toArray();
        if (!empty($errorTrace)) {
            $response['data'] = $errorTrace;
            $errorMessage = [];
            foreach ($errorTrace as $error) {
                $errorMessage[] = $error[0] ?? '';
            }
        }
        return response()->json($response, $code);
    }


    /**
     * Get Access token
     *
     * @param Request $request
     *
     * @return void
     */
    protected function getAccessToken(Request $request)
    {
        return $request->bearerToken();
        // $header = $request->header('Authorization');
        // return (Str::startsWith($header, 'Bearer '))? Str::substr($header, 7) : '';
    }

    public function getLoggerUser()
    {
        $authToken = AuthToken::select('auth_id')
            ->where('token', $this->_token)->first();
        return Auth::find($authToken->auth_id);
    }

    public function getLoggedUserId()
    {
        return  $this->getLoggerUser()->id;
    }
}
