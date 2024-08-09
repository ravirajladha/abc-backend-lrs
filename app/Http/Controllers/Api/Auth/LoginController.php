<?php

namespace App\Http\Controllers\Api\Auth;

use Carbon\Carbon;

use App\Models\Auth;
use Illuminate\Http\Request;
use App\Models\StudentAuthLog;
use App\Services\Core\AuthService;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

use App\Http\Constants\AuthConstants;

use Illuminate\Support\Facades\Session;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;

class LoginController extends BaseController
{

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function login(Request $request, AuthService $authService)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $field = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'id';
            $auth = Auth::where('email', $request->username)->first();

            if ($auth) {
                Log::info("admin", $request->all());

                if ($auth->status === 0) {
                    return $this->sendError('Account inactive', ['status' => ['Your account is currently inactive. Please contact support for assistance.']], 400);
                }

                if (Hash::check($request->password, $auth->password)) {
                    auth()->login($auth);
                    $token = $authService->createToken($auth->id);

                    $ip_address = $request->ip();
                    $browser = $request->header('User-Agent');

                    return $this->sendResponseWithToken($token, $auth, $ip_address, $browser);
                } else {
                    // here, you can give the messgae that verify the password
                    return $this->sendError('Invalid credentials', ['password' => ['Invalid credentials.']], 400);
                }
            } else {
                Log::info("not admin", $request->all());
                // you can give the message that verify your username
                return $this->sendError('Invalid credentials', ['username' => ['Invalid credentials.']], 400);
            }
        }
        return $this->sendError('Failed to login. Verify your credentials.', [], 400);
    }
    // public function play()
    // {
    //     $filePath = storage_path('app/public/videos/video_sample.mp4' );

    //     if (!file_exists($filePath)) {
    //         abort(404);
    //     }
    //    // You can return an API response with the URL of the video
    //     return response()->json(['video_url' => asset('storage/videos/video_sample.mp4')]);

    //     // If you want to directly serve the video file, you can use:
    //     // return response()->file($filePath);
    // }
}
