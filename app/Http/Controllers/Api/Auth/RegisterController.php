<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\Auth as AuthModel;
use App\Models\ParentModel;

use App\Services\Core\AuthService;

use App\Http\Constants\AuthConstants;

use App\Http\Controllers\Api\BaseController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends BaseController
{
    /**
     * Store parent
     *
     * @param Request $request
     */
    public function registerParent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:auth',
            'password' => 'required|string|min:6',
            'confirm_password' => 'required|string|same:password',
            'phone_number' => 'required|string|regex:/^[0-9]{10}$/|unique:auth',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $auth = AuthModel::create([
                'password' => Hash::make($request->password),
                'username' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'type' => AuthConstants::TYPE_PARENT,
                'status' => AuthConstants::STATUS_ACTIVE,
            ]);

            if ($auth) {
                //Generate a 6 digit parent code
                $length = 6;
                $min = pow(10, $length - 1);
                $max = pow(10, $length) - 1;
                $rand_number = mt_rand($min, $max);
                //Create parent
                $parent = ParentModel::create([
                    'auth_id' => $auth->id,
                    'name' => $request->name,
                    'parent_code' => $rand_number,
                ]);
            }

            $authService = new AuthService();

            if ($auth && $parent) {
                if (Hash::check($request->password, $auth->password)) {
                    auth()->login($auth);
                    $token = $authService->createToken($auth->id);
                    $ip_address = $request->ip();
                    $browser = $request->header('User-Agent');

                    return $this->sendResponseWithToken($token, $auth, $ip_address, $browser);
                } else {
                    return $this->sendError('Error Signup.', ['password' => ['Verify your password.']], 400);
                }
            } else {
                return $this->sendError('Invalid details', ['username' => ['Verify your username.']], 400);
            }



            if ($auth && $parent) {
                return $this->sendResponse([], 'Parent registered successfully');
            }
        }
    }
}
