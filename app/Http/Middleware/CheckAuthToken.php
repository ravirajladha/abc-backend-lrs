<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use App\Models\AuthToken;
use \Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\Core\DatabaseService;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\DatabaseManager;
use Illuminate\Contracts\Foundation\Application;

class CheckAuthToken
{

    protected $app;
    protected $databaseManager;

    public function __construct(Application $application, DatabaseManager $databaseManager)
    {
        $this->app = $application;
        $this->databaseManager = $databaseManager;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
            $bearerToken = request()->header('Authorization');
            if ('' != $bearerToken) {
                $accessToken = Str::substr($bearerToken, 7);
                $isValidToken = AuthToken::where('token', $accessToken)->first();
                // if ($isValidToken && ($isValidToken->expires_at > now()->toDateTimeString())) {
                if ($isValidToken) {
                    $isValidToken->update([
                        'expires_at' => now()->addHour()
                    ]);
                    return $next($request);
                }
                return response()->json('Token Expired or Invalid Token', 403);

            }

    }
}
