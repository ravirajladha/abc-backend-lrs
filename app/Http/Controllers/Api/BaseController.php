<?php

namespace App\Http\Controllers\Api;

use App\Models\Auth;
use App\Models\AuthToken;
use App\Models\Subject;
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
    protected function sendResponseWithToken($token = null, $auth, $ip_address = null, $browser = null)
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
                ->where('s.auth_id', $auth->id)
                ->first();

            // $subjects = Subject::join('courses', 'subjects.course_id', '=', 'courses.id')
            //     ->join('chapters', 'subjects.id', '=', 'chapters.subject_id')
            //     ->join('chapter_logs', 'chapters.id', '=', 'chapter_logs.chapter_id')
            //     ->select('subjects.id', 'subjects.name', 'courses.name as course_name')
            //     ->where('chapter_logs.video_complete_status', 1)
            //     ->where('chapter_logs.student_id', $student->auth_id)
            //     ->groupBy('subjects.id', 'subjects.name', 'subjects.image', 'courses.name')
            //     ->get();

            if ($student) {
                if ($auth->type === AuthConstants::TYPE_STUDENT) {
                    StudentAuthLog::create([
                        'student_id' => $auth->id,
                        'login_at' => Carbon::now(),
                        'ip_address' => $ip_address,
                        'browser' => $browser,
                    ]);
                }

                $response['student_data'] = [
                    'student_id' => $student->id,
                    'student_auth_id' => $student->auth_id,
                    'student_name' => $auth->name,
                    // 'class_id' => null,
                    // 'class_name' => null,
                    'student_unique_code' => $student->student_unique_code,
                    // 'subjects' => $subjects !== null ? $subjects : null,
                    'profile_image' => $student->profile_image,
                    'dob' => $student->dob,
                    'address' => $student->address,
                    'is_paid' => $student->is_paid,
                    'city' => $student->city,
                    'state' => $student->state,
                    'pincode' => $student->pincode,
                ];
            }
        }
        if ($auth->type === AuthConstants::TYPE_INTERNSHIP_ADMIN) {
            $school = DB::table('schools')
                    ->select('name', 'school_type')
                    ->where('auth_id', $auth->id)
                    ->first();
            $response['user']['school_type'] = $school->school_type;
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
