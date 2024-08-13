<?php

namespace App\Http\Controllers\Api;

use App\Models\ForumAnswer;
use App\Models\ForumQuestion;
use App\Models\ForumAnswerVote;
use App\Models\Student;

use App\Http\Constants\ForumConstants;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ForumController extends BaseController
{
    public function getStudentForumList(Request $request)
    {
        $forum = DB::table('forum_questions as f')
            ->select(
                'f.id as question_id',
                'f.question',
                'f.dislikes_count',
                'f.likes_count',
                'f.student_id',
                'f.created_at',
                's.profile_image',
                's.name as student_name',
                'a.vote_count',
                DB::raw('COUNT(a.id) as answers_count')
            )
            ->leftJoin('students as s', 's.id', 'f.student_id')
            ->leftJoin('forum_answers as a', 'a.question_id', 'f.id')
            ->where('f.status', ForumConstants::STATUS_ACTIVE)
            ->orderBy('f.created_at', 'desc')
            ->groupBy('f.id', 'f.question', 'f.dislikes_count', 'f.likes_count', 'f.student_id', 'f.created_at', 's.profile_image', 's.name', 'a.vote_count');

        $studentId = $request->studentId;

        if ($studentId !== null && $studentId !== 'undefined') {
            $forum->where('f.student_id', $studentId);
        } else {
            $forum->whereNull('f.student_id');
        }

        $forums = $forum->limit(3)->get();

        if ($forums->isNotEmpty()) {
            return $this->sendResponse(['forum' => $forums], 'Forum fetched successfully');
        }

        return $this->sendError('Failed to fetch forum');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getForumQuestionDetails(Request $request, $forumId)
    {
        $forum = [];
        $question = DB::table('forum_questions as f')
            ->select('f.question', 'f.dislikes_count', 'f.likes_count', 'f.student_id', 's.profile_image', 's.name as student_name', 'f.created_at')
            ->leftJoin('students as s', 's.id', 'f.student_id')
            ->where('f.status', ForumConstants::STATUS_ACTIVE)
            ->where('f.id', $forumId)
            ->first();
        // $answers = DB::table('forum_answers as f')
        //     ->select('f.id','f.answer', 'f.vote_count', 'f.student_id', 's.name as student_name', 's.profile_image', 'f.created_at')
        //     ->leftJoin('students as s', 's.id', 'f.student_id')
        //     ->where('f.status', ForumConstants::STATUS_ACTIVE)
        //     ->where('f.question_id', $forumId)
        //     ->get();

        $loggedUserId = $this->getLoggedUserId();
        $student = Student::where('auth_id', $loggedUserId)->first();
        $answers = DB::table('forum_answers as f')
            ->select(
                'f.id',
                'f.answer',
                'f.vote_count',
                'f.student_id',
                's.name as student_name',
                's.profile_image',
                'f.created_at',
                DB::raw('IFNULL(v.vote_type, 0) as vote_type')
            )
            ->leftJoin('students as s', 's.id', 'f.student_id')
            ->leftJoin('forum_answer_votes as v', function ($join) use ($student) {
                $join->on('v.answer_id', '=', 'f.id')
                    ->where('v.student_id', '=', $student->id);
            })
            ->where('f.status', ForumConstants::STATUS_ACTIVE)
            ->where('f.question_id', $forumId)
            ->orderBy('f.vote_count', 'desc')
            ->get();

        $forum = [
            'question' => $question,
            'answers' => $answers,
        ];

        if ($question !== null) {
            return $this->sendResponse(['forum' => $forum], 'Forum fetched successfully');
        }

        return $this->sendError('Failed to fetch forum');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeForumQuestion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string',
        ], [
            'question.required' => 'Uh-oh! It seems you submitted an empty question.',
            'question.string' => 'Oops! Encountered some issues with your question. Verify your input.',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $forum_question = new ForumQuestion();
            $forum_question->student_id = $request->studentId;
          
            $forum_question->question = $request->question;
            $forum_question->status = ForumConstants::STATUS_ACTIVE;
            $forum_question->save();
            return $this->sendResponse([], 'Forum submitted successfully');
        }
    }

    public function storeForumAnswer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'answer' => 'required|string',
        ], [
            'answer.required' => 'Uh-oh! It seems you submitted an empty answer.',
            'answer.string' => 'Oops! Encountered some issues with your answer. Verify your input.',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $forum_answer = new ForumAnswer();
            $forum_answer->student_id = $request->studentId;
          
            $forum_answer->question_id = $request->forumId;
            $forum_answer->answer = $request->answer;
            $forum_answer->status = ForumConstants::STATUS_ACTIVE;
            $forum_answer->save();
            return $this->sendResponse(['forum_answer' => $forum_answer], 'Your answer submitted successfully');
        }
    }

    /**
     * Show resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchForumQuestion(Request $request, $question)
    {
        $keyword = $request->question;

        $questions = ForumQuestion::where('question', 'like', '%' . $question . '%')->where('status',1)->get();

        if ($questions->isNotEmpty()) {
            return $this->sendResponse(['questions' => $questions], 'Questions found successfully');
        } else {
            return $this->sendError('No matching questions found');
        }
    }

    /**
     * Store forum anser votes.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeVote(Request $request)
    {
        $existingVote = ForumAnswerVote::where('answer_id', $request->answerId)
            ->where('student_id', $request->studentId)
            ->first();
        $forumAnswer = ForumAnswer::find($request->answerId);

        // If the user has already voted, toggle the vote type or delete the vote
        if ($existingVote) {
            if ($existingVote->vote_type == $request->voteType) {
                $existingVote->delete(); // Remove the vote if clicking again on the same button
                if ($request->voteType == 1) {
                    $forumAnswer->vote_count -= 1;
                } elseif ($request->voteType == -1) {
                    $forumAnswer->vote_count += 1;
                }
            } else {
                $existingVote->vote_type = $request->voteType;
                $existingVote->save();
                if ($request->voteType == 1) {
                    $forumAnswer->vote_count += 2;
                } elseif ($request->voteType == -1) {
                    $forumAnswer->vote_count -= 2;
                }
            }
        } else {
            // Otherwise, create a new vote record
            ForumAnswerVote::create([
                'answer_id' => $request->answerId,
                'student_id' => $request->studentId,
                'vote_type' => $request->voteType,
            ]);
            if ($request->voteType == 1) {
                $forumAnswer->vote_count += 1;
            } elseif ($request->voteType == -1) {
                $forumAnswer->vote_count -= 1;
            }
        }

        // Update total vote count in forum_answer table
        // if ($forumAnswer) {
        //     if ($request->voteType == 1) {
        //         $forumAnswer->vote_count += 1;
        //     } elseif ($request->voteType == -1) {
        //         $forumAnswer->vote_count -= 1;
        //     }
        //     $forumAnswer->save();
        // }
        $forumAnswer->save();

        return $this->sendResponse(['forumAnswer' => $forumAnswer], 'Vote recorded successfully');
    }

    /**
     * Display a listing of the Forum questions.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getForumQuestionsList()
    {
        $forumQuestions = DB::table('forum_questions as f')
            ->select('f.id', 'f.question', 'f.student_id', 'f.status', 's.profile_image', 's.name as student_name', 'f.created_at')
            ->leftJoin('students as s', 's.id', 'f.student_id')
            ->paginate(10);
        // $forumQuestions = ForumQuestion::get();
        return $this->sendResponse(['forumQuestions' => $forumQuestions], 'Forum questions fetched successfully.');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getForumQuestionAnswers(Request $request, $forumId)
    {
        $forum = [];
        $question = DB::table('forum_questions as f')
            ->select('f.question', 'f.student_id', 's.profile_image', 's.name as student_name', 'f.created_at')
            ->leftJoin('students as s', 's.id', 'f.student_id')
            ->where('f.id', $forumId)
            ->first();

        $loggedUserId = $this->getLoggedUserId();
        $student = Student::where('auth_id', $loggedUserId)->first();
        $answers = DB::table('forum_answers as f')
            ->select(
                'f.id',
                'f.answer',
                'f.vote_count',
                'f.student_id',
                's.name as student_name',
                's.profile_image',
                'f.created_at',
                'f.status',
                DB::raw('IFNULL(v.vote_type, 0) as vote_type')
            )
            ->leftJoin('students as s', 's.id', 'f.student_id')
            ->leftJoin('forum_answer_votes as v', 'v.answer_id', 'f.id')
            // ->where('f.status', ForumConstants::STATUS_ACTIVE)
            ->where('f.question_id', $forumId)
            ->orderBy('f.vote_count', 'desc')
            ->get();

        $forum = [
            'question' => $question,
            'answers' => $answers,
        ];

        if ($question !== null) {
            return $this->sendResponse(['forum' => $forum], 'Forum fetched successfully');
        }

        return $this->sendError('Failed to fetch forum');
    }
    public function updateStatus(Request $request)
    {
        // Find the forum post by ID
        $forum = ForumQuestion::find($request->forum_id);
        // Update the status
        $forum->status = $request->status;
        $forum->save();
        return $this->sendResponse(['forum' => $forum], 'Forum status updated successfully');
    }
    public function updateAnswerStatus(Request $request)
    {
        // Find the forum post by ID
        $forumAnswer = ForumAnswer::find($request->answer_id);
        // Update the status
        $forumAnswer->status = $request->status;
        $forumAnswer->save();
        return $this->sendResponse(['forumAnswer' => $forumAnswer], 'Forum Answer status updated successfully');
    }
}
