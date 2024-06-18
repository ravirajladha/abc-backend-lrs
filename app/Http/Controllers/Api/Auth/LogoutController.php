<?php

namespace App\Http\Controllers\Api\Auth;

use Carbon\Carbon;

use App\Models\Auth;
use App\Models\AuthToken;
use App\Models\StudentAuthLog;

use App\Services\Core\AuthService;

use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\Http\Constants\AuthConstants;

use App\Http\Controllers\Api\BaseController;

class LogoutController extends BaseController
{
    public function logout(Request $request, AuthService $authService)
    {
        $bearerToken = $request->header('Authorization');

        if (!empty($bearerToken)) {
            $accessToken = Str::substr($bearerToken, 7);

            $auth_token = AuthToken::where('token', $accessToken)->first();

            $auth = Auth::where('id', $auth_token->auth_id)->first();

            if ($auth->type === AuthConstants::TYPE_STUDENT) {
                $stu = StudentAuthLog::where('student_id', $auth_token->auth_id)
                    ->first();
                $stu->update([
                    'logout_at' => Carbon::now(),
                ]);
            }

            $authService->destroyToken($accessToken);
            return $this->sendResponse([], 'User logged out successfully');
        }

        return $this->sendError('User not logged in or invalid token', [], 400);
    }
}
