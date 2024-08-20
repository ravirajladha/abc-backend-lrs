<?php

namespace App\Http\Controllers\Api\Dropdown;

use Illuminate\Http\Request;

use App\Models\AssessmentQuestion;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\BaseController;

class AssessmentQuestionController extends BaseController
{
    public function __invoke(Request $request)
    {
        // Fetch the count of assessment questions based on the course ID
        $assessmentQuestions = AssessmentQuestion::where('course_id', $request->courseId)->count();

        // Log the fetched data
        Log::info('Assessment Questions Count:', ['course_id' => $request->courseId, 'count' => $assessmentQuestions]);

        // Return the response
        return $this->sendResponse(['assessmentQuestions' => $assessmentQuestions]);
    }
}
