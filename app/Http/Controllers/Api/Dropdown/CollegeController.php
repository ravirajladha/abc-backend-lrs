<?php

namespace App\Http\Controllers\Api\Dropdown;

use App\Models\College;

use Illuminate\Http\Request;

use App\Http\Controllers\Api\BaseController;

class CollegeController extends BaseController
{
    public function __invoke(Request $request)
    {
        $colleges = College::select('id', 'name')->where('status', 1)->get();
        return $this->sendResponse(['colleges' => $colleges]);
    }
}
