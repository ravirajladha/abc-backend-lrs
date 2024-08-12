<?php

namespace App\Http\Controllers\Api;

use App\Models\Auth as AuthModel;

use App\Http\Constants\AuthConstants;

use App\Http\Controllers\Api\BaseController;

class AuthController extends BaseController
{

    /**
     * Get school details
     *
     * @return object
     */
    public function getDetails()
    {
        $auth = AuthModel::select('id', 'username', 'email', 'phone_number', 'type')
            ->where('id', $this->getLoggedUserId())
            ->where('status', AuthConstants::STATUS_ACTIVE)->first();
        return $this->sendResponse(['auth' => $auth]);
    }
}
