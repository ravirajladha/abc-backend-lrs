<?php

namespace App\Http\Controllers\Api\Dropdown;

use App\Http\Controllers\Api\BaseController;
use App\Models\JobQuestion;
use Illuminate\Http\Request;

class TestQuestionBySubjectIdController extends BaseController
{
    public function __invoke(Request $request)
    // {
    //     $termQuestions = TermTestQuestion::where('subject_id', $request->subjectId)
    //         ->get();
    //     $questionCount = $termQuestions->count();
    //     return $this->sendResponse(['term_questions' => $termQuestions, 'term_question_count' => $questionCount]);
    // }
    {
        $subjectIds = explode(',', $request->query('subjectIds'));
        $questions = JobQuestion::whereIn('subject_id', $subjectIds)->get();
        $questionCount = $questions->count();
        return $this->sendResponse(['questions' => $questions, 'term_question_count' => $questionCount]);
    }
}
