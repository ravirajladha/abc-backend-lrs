<?php

namespace App\Http\Controllers\Api\Dropdown;

use App\Models\AssessmentQuestion;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;

class AssessmentQuestionController extends BaseController
{
    public function __invoke(Request $request)
    {
        $assessmentQuestions = AssessmentQuestion::where('subject_id', $request->subjectId)->count();
        return $this->sendResponse(['assessmentQuestions' => $assessmentQuestions]);
    }
}
