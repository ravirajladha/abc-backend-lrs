<?php

namespace App\Services\Core;

use App\Models\AuthToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use \Illuminate\Support\Str;

class AuthService
{

    /**
     * Create a unique token to identify login
     *
     * @param [type] $authId
     * @return string
     */
    public function createToken($authId)
    {
        $token = Str::random(150).now()->timestamp;
        AuthToken::create([
            'auth_id' => $authId,
            'token' => $token,
            'expires_at' => Carbon::now()->addDecade()
        ]);
        Session::put('_auth_token', $token);
        return $token;
    }

    public function refreshExpiry($token, $authId)
    {
        $authToken = AuthToken::where('auth_id', $authId)
            ->where('token', $token)
            ->first();
        if ($authToken) {
            $authToken->expires_at = Carbon::now()->addHour();
            $authToken->save();
        }
    }

    public function destroyToken($token)
    {
        $authToken = AuthToken::where('token', $token)->first();
        if ($authToken) {
            $authToken->delete();
        }
    }
}
