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

class ForgotPasswordController extends BaseController
{
    /**
     * Mask the mobile number to show only the last 4 digits.
     *
     * @param string $phoneNumber
     * @return string
     */
    private function maskMobileNumber($phoneNumber)
    {
        return substr($phoneNumber, 0, -4) . str_repeat('x', strlen($phoneNumber) - 4);
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
            // for now otp is 5555
            // $otp = rand(100000, 999999);
            $otp = 5555;

            // Store OTP in cache with a 10-minute expiration
            Cache::put('otp_' . $auth->id, $otp, now()->addMinutes(10));

            // Mask the mobile number
            $maskedNumber = $this->maskMobileNumber($auth->phone_number);

            // Call the function for sending OTP to user's mobile number
            // $this->sendOtp($auth->phone_number, $otp);

            return $this->sendResponse([], "Email verified and OTP sent to mobile number {$maskedNumber}");
        }
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:auth,email',
            'otp' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $auth = Auth::where('email', $request->email)->first();
        $cachedOtp = Cache::get('otp_' . $auth->id);

        if (strval($cachedOtp) === strval($request->otp)) {
            // Clear the OTP from the cache
            Cache::forget('otp_' . $auth->id);
            return $this->sendResponse([], "OTP verified successfully.");
        } else {
            return $this->sendError('Invalid OTP.', ['otp'=>"Invalid OTP"], 400);
        }
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:auth,email',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $user = Auth::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();
        return $this->sendResponse([], "Password reset successfully.");
    }
}