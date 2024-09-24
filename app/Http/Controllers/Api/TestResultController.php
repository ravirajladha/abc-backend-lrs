<?php

namespace App\Http\Controllers\Api;

use App\Models\Test;
use App\Models\TestResult;
use App\Models\TestQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Helpers\DateTimeHelper;

class TestResultController extends BaseController
{
    public function getStudentTestDetailsByCourseId(Request $request)
    {
Log::info("student", ['student id'=> $request->all()]);
        $results = DB::table('tests as test')
            ->select('test.id as test_id', 'test.description as test_description', 'test.question_ids as test_question_ids', 'test.total_score as test_total_score', 'test.course_id as test_course', 'test.time_limit as test_time', 'test.title as test_title', 'result.id as result_id', 'result.percentage as result_percentage', 'result.student_id as student_id', 'result.score as result_score', 'result.response_questions as result_response_questions', 'result.response_answers as result_response_answers', 'result.created_at as result_date')
            ->leftJoin('test_results as result', 'result.test_id', 'test.id')
            ->where('test.course_id', $request->courseId)
            ->where('result.student_id', $request->studentId)
            ->get();


        foreach ($results as $result) {
            if ($result && $result->test_question_ids) {
                $questionIds = explode(',', $result->test_question_ids); //All Questions in the test
                $question_bank = DB::table('test_questions')
                    ->select('id', 'question', 'explanation', 'code', 'option_one', 'option_two', 'option_three', 'option_four', 'answer_key')
                    ->whereIn('id', $questionIds)
                    ->get();

                $selectedQuestionIds = explode(',', $result->result_response_questions); //Selected Questions
                $selectedAnswers = explode(',', $result->result_response_answers); //Answers Given by Students

                $mergedArray = [];

                foreach ($question_bank as $question) {
                    $questionId = $question->id;
                    if (in_array($questionId, $questionIds)) { //$selectedQuestionIds to display given questions
                        $key = array_search($questionId, $questionIds);
                        if (array_key_exists($key, $selectedAnswers)) {
                            $answerKey = $selectedAnswers[$key];
                        } else {
                            $answerKey = null;
                        }

                        $mergedArray[] = [
                            'question_id' => $questionId,
                            'question' => $question->question,
                            'explanation' => $question->explanation,
                            'code' => $question->code,
                            'option_one' => $question->option_one,
                            'option_two' => $question->option_two,
                            'option_three' => $question->option_three,
                            'option_four' => $question->option_four,
                            'answer_key' => $question->answer_key,
                            'student_response' => $answerKey,
                        ];
                    }
                }

                $result->response =  $mergedArray;

                //Show human friendly result date time
                $result->result_date = DateTimeHelper::format($result->result_date, DateTimeHelper::SYSTEM_DATE_TIME_FORMAT, DateTimeHelper::CUSTOM_DATE_TIME_FORMAT);
            }
        }




        if (!$results) {
            return $this->sendError('Test results not found');
        }
        Log::info("student", ['results id'=> $results]);

        return $this->sendResponse(['test_results' => $results], '');
    }
}
