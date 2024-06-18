<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Models\Auth as AuthModel;

use App\Http\Constants\AuthConstants;

use App\Http\Controllers\Api\BaseController;
use App\Models\School;

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
