<?php

namespace App\Http\Controllers\Api\Dropdown;

use App\Http\Controllers\Api\BaseController;
use App\Models\TestQuestion;
use Illuminate\Http\Request;

class TestQuestionController extends BaseController
{
    public function __invoke(Request $request)
    {
        $questions = TestQuestion::where('course_id', $request->courseId)
            ->get();
        $questionCount = $questions->count();
        return $this->sendResponse(['questions' => $questions, 'question_count' => $questionCount]);
    }
}
