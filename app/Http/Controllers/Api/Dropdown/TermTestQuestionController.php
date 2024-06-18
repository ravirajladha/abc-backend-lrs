<?php

namespace App\Http\Controllers\Api\Dropdown;

use App\Http\Controllers\Api\BaseController;
use App\Models\TermTestQuestion;
use Illuminate\Http\Request;

class TermTestQuestionController extends BaseController
{
    public function __invoke(Request $request)
    {
        $termQuestions = TermTestQuestion::where('subject_id', $request->subjectId)
            ->get();
        $questionCount = $termQuestions->count();
        return $this->sendResponse(['term_questions' => $termQuestions, 'term_question_count' => $questionCount]);
    }
}
