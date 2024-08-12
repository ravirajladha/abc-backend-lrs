<?php

namespace App\Http\Controllers\Api\Dropdown;

use App\Models\CaseStudy;

use Illuminate\Http\Request;

use App\Http\Controllers\Api\BaseController;

class CaseStudyController extends BaseController
{
    public function __invoke(Request $request)
    {
        $caseStudies = CaseStudy::select('id', 'title')->where('course_id', $request->courseId)->get();
        return $this->sendResponse(['caseStudies' => $caseStudies]);
    }
}
