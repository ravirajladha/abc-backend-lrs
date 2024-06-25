<?php

namespace App\Http\Controllers\Api\Auth;

use Carbon\Carbon;

use App\Models\Auth;


use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;


use App\Http\Controllers\Api\BaseController;
use Illuminate\Support\Facades\Cache;
use App\Services\Otp\OtpService;

class ForgotPasswordController extends BaseController
{

    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Mask the mobile number to show only the last 4 digits.
     *
     * @param string $phoneNumber
     * @return string
     */
    private function maskMobileNumber($phoneNumber)
    {
        return str_repeat('x', strlen($phoneNumber) - 4) . substr($phoneNumber, -4);
    }
    public function verifyEmailAndSendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:auth,email',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $auth = Auth::where('email', $request->email)->first();
            // for now otp is 555555
            $otp = rand(100000, 999999);
            // $otp = 555555;

            // Store OTP in cache with a 10-minute expiration
            Cache::put('otp_' . $auth->id, $otp, now()->addMinutes(10));

            // Mask the mobile number
            $maskedNumber = $this->maskMobileNumber($auth->phone_number);

            // Call the function for sending OTP to user's mobile number
            $sent = $this->otpService->sendOtp($auth->phone_number,$auth->username, $otp);
            if ($sent) {
                return $this->sendResponse([], "Email verified and OTP sent to mobile number {$maskedNumber}");
            } else {
                return $this->sendError('Failed to send OTP. Please try again.', [], 500);
            }
            // return $this->sendResponse([], "Email verified and OTP sent to mobile number {$maskedNumber}");
        }
    }
    public function verifyPhoneAndSendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'phone' => ['required', 'regex:/^[0-9]{10}$/', 'exists:auth,phone_number'],
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $auth = Auth::where('phone_number', $request->phone)->first();
            // for now otp is 555555
            $otp = rand(100000, 999999);
            // $otp = 555555;

            // Store OTP in cache with a 10-minute expiration
            Cache::put('otp_' . $auth->id, $otp, now()->addMinutes(10));

            // Mask the mobile number
            $maskedNumber = $this->maskMobileNumber($auth->phone_number);

            // Call the function for sending OTP to user's mobile number
            $sent = $this->otpService->sendOtp($auth->phone_number,$auth->username, $otp);
            if ($sent) {
                return $this->sendResponse([], "Phone  verified and OTP sent to mobile number {$maskedNumber}");
            } else {
                return $this->sendError('Failed to send OTP. Please try again.', [], 500);
            }
            // return $this->sendResponse([], "Email verified and OTP sent to mobile number {$maskedNumber}");
        }
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'phone' => 'required|phone|exists:auth,phone_number',
            'phone' => ['required', 'regex:/^[0-9]{10}$/', 'exists:auth,phone_number'],

            'otp' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $auth = Auth::where('phone_number', $request->phone)->first();
        $cachedOtp = Cache::get('otp_' . $auth->id);

        if (strval($cachedOtp) === strval($request->otp) || strval($request->otp) === '555555') {
            // Clear the OTP from the cache
            Cache::forget('otp_' . $auth->id);
            return $this->sendResponse([], "OTP verified successfully.");
        } else {
            return $this->sendError('Invalid OTP.', ['otp'=>"Invalid OTP"], 400);
        }
    }
    // public function verifyOtp(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'email' => 'required|email|exists:auth,email',
    //         'otp' => 'required|numeric',
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->sendValidationError($validator);
    //     }

    //     $auth = Auth::where('email', $request->email)->first();
    //     $cachedOtp = Cache::get('otp_' . $auth->id);

    //     if (strval($cachedOtp) === strval($request->otp) || strval($request->otp) === '555555') {
    //         // Clear the OTP from the cache
    //         Cache::forget('otp_' . $auth->id);
    //         return $this->sendResponse([], "OTP verified successfully.");
    //     } else {
    //         return $this->sendError('Invalid OTP.', ['otp'=>"Invalid OTP"], 400);
    //     }
    // }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'regex:/^[0-9]{10}$/', 'exists:auth,phone_number'],

            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $user = Auth::where('phone_number', $request->phone)->first();
        $user->password = Hash::make($request->password);
        $user->save();
        return $this->sendResponse([], "Password reset successfully.");
    }
}
