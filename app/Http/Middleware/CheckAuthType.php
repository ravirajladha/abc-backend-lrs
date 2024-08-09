<?php

namespace App\Http\Middleware;

use App\Http\Constants\AuthConstants;
use Closure;
use App\Models\Auth;
use App\Models\AuthToken;
use Illuminate\Http\Request;

class CheckAuthType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $accessToken = $request->bearerToken();

        $authToken = AuthToken::where('token', $accessToken)->first();

        if ($authToken) {
            $auth = Auth::find($authToken->auth_id);

            if ($auth->type == AuthConstants::TYPE_ADMIN) {
                $request->attributes->add(['type' => 'admin']);
            } elseif ($auth->type == AuthConstants::TYPE_TRAINER) {
                $request->attributes->add(['type' => 'trainer']);
            } elseif ($auth->type == AuthConstants::TYPE_INTERNSHIP_ADMIN) {
                $request->attributes->add(['type' => 'internship_admin']);
            } elseif ($auth->type == AuthConstants::TYPE_RECRUITER) {
                $request->attributes->add(['type' => 'recruiter']);
            } else {
                $request->attributes->add(['type' => 'student']);
            }
        }

        return $next($request);
    }
}
