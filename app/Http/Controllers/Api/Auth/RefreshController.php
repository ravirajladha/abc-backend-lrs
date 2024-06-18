<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\AuthModel;

use Illuminate\Support\Str;
use Illuminate\Http\Request;


use App\Services\Core\AuthService;

use App\Http\Controllers\Api\BaseController;

class RefreshController extends BaseController
{
    public function refreshToken(Request $request, AuthService $authService)
    {
        $bearerToken = $request->header('Authorization');
        $ip_address = $request->ip();
        $browser = $request->header('User-Agent');

        if (!empty($bearerToken)) {
            $token = $authService->createToken($this->getLoggedUserId());
            return $this->sendResponseWithToken($token, $this->getLoggerUser(), $ip_address, $browser);
        }

        return $this->sendError('User not logged in or invalid token', [], 400);
    }
}
