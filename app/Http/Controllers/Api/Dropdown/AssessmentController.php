<?php

namespace App\Http\Controllers\Api\Dropdown;

use App\Models\Assessment;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;

class AssessmentController extends BaseController
{
    public function __invoke(Request $request)
    {
        $assessments = Assessment::select('course_id', 'subject_id', 'title', 'id')
        ->where('course_id', $request->courseId)
        ->get()
        ->map(function ($assessment) {
            $assessment->title = ucfirst($assessment->title);
            return $assessment;
        });
        return $this->sendResponse(['assessments' => $assessments]);
    }
}
