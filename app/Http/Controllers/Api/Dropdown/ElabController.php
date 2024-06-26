<?php

namespace App\Http\Controllers\Api\Dropdown;

use App\Models\Elab;

use Illuminate\Http\Request;

use App\Http\Controllers\Api\BaseController;

class ElabController extends BaseController
{
    public function __invoke(Request $request)
    {
        $elabs = Elab::select('id', 'title')->where('subject_id', $request->subjectId)->get();
        return $this->sendResponse(['elabs' => $elabs]);
    }
}
