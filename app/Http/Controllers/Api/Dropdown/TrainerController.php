<?php

namespace App\Http\Controllers\Api\Dropdown;

use App\Models\Auth;

use Illuminate\Http\Request;

use App\Http\Controllers\Api\BaseController;

class TrainerController extends BaseController
{
    public function __invoke(Request $request)
    {
        $trainers = Auth::select('id', 'username')->where('type', 2)->get();
        return $this->sendResponse(['trainers' => $trainers]);
    }
}
