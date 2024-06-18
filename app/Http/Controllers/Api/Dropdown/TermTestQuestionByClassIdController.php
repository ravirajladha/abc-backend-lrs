<?php

namespace App\Http\Controllers\Api\Dropdown;

use App\Http\Controllers\Api\BaseController;
use App\Models\JobQuestion;
use Illuminate\Http\Request;

class TermTestQuestionByClassIdController extends BaseController
{
    public function __invoke(Request $request)
    // {
    //     $termQuestions = TermTestQuestion::where('subject_id', $request->subjectId)
    //         ->get();
    //     $questionCount = $termQuestions->count();
    //     return $this->sendResponse(['term_questions' => $termQuestions, 'term_question_count' => $questionCount]);
    // }
    {
        $classIds = explode(',', $request->query('classIds'));
        $termQuestions = JobQuestion::whereIn('class_id', $classIds)->get();
        $questionCount = $termQuestions->count();
        return $this->sendResponse(['term_questions' => $termQuestions, 'term_question_count' => $questionCount]);
    }
}
