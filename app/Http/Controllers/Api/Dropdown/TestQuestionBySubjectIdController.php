<?php

namespace App\Http\Controllers\Api\Dropdown;

use App\Http\Controllers\Api\BaseController;
use App\Models\PlacementQuestion;
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
        $questions = PlacementQuestion::whereIn('subject_id', $subjectIds)->get();
        $questionCount = $questions->count();
        return $this->sendResponse(['questions' => $questions, 'question_count' => $questionCount]);
    }
}
