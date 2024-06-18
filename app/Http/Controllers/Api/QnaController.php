<?php

namespace App\Http\Controllers\Api;

use App\Models\Auth as AuthModel;
use App\Models\Qna;
use App\Models\QnaLog;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class QnaController extends BaseController
{

    /**
     * Show the form for creating a new resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQnaBySubject($studentId, $teacherId, $subjectId = null)
    {
        if ($subjectId) {
            $teacher = DB::table('teacher_subjects')
                ->where('subject_id', $subjectId)
                ->first();
            $sent_messages = QnaLog::where('sender_id', $studentId)->where('receiver_id', $teacherId)->where('subject_id',$subjectId)->get();
            $received_messages = QnaLog::where('sender_id', $teacherId)->where('receiver_id', $studentId)->where('subject_id',$subjectId)->get();
        }else{
            $sent_messages = QnaLog::where('sender_id', $studentId)->where('receiver_id', $teacherId)->get();
            $received_messages = QnaLog::where('sender_id', $teacherId)->where('receiver_id', $studentId)->get();
        }



        $merged_messages = $sent_messages->merge($received_messages)->sortBy('created_at');
        $merged_messages = $merged_messages->values()->all();


        if (is_array($merged_messages) && !empty($merged_messages)) {
            return $this->sendResponse(['merged_messages' => $merged_messages]);
        } else {
            return $this->sendResponse([], 'No messages found');
        }
    }




    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */ public function storeQnaBySubject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            // Check for the answer from qna table and retrieve the answer for an existing question
            $qna = DB::table('qna')->where('question', $request->question)->first();


            if ($qna) {
                // When the qna exists, send the answer from the teacher + store the answer to qna_log
                $this->saveQnaLog($request->subject_id, $qna->id, $qna->question, $request->student_id,  $request->teacher_id);
                $this->saveQnaLog($request->subject_id, $qna->id, $qna->answer,  $request->teacher_id, $request->student_id);
                return $this->sendResponse(['message' => $qna->answer], 'Message sent successfully');
            } else {
                //Id from their table
                $teacher_id = Teacher::where('auth_id', $request->teacher_id)->value('id');
                $student_id = Student::where('auth_id', $request->student_id)->value('id');
                // When there is no qna, store in qna + qna_log
                $new_qna = new Qna();
                $new_qna->class_id = $request->class_id;
                $new_qna->subject_id = $request->subject_id;
                $new_qna->student_id = $student_id;
                $new_qna->teacher_id =  $teacher_id;
                $new_qna->question = $request->question;
                $new_qna->save();

                if ($new_qna) {
                    $message = $this->saveQnaLog($request->subject_id, $new_qna->id, $new_qna->question, $request->student_id,  $request->teacher_id );
                    return $this->sendResponse(['message' => $message], 'Question sent successfully');
                }
            }
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */ public function storeTeacherQnaResponse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'answer' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $teacher_id = Teacher::where('auth_id', $request->teacher_id)->value('id');

            //Save the Teacher Response to QnA
            $qna = Qna::where('id', $request->qna_id)
                ->update([
                    'answer' => $request->answer,
                    'teacher_id' => $teacher_id,
                ]);
            $updatedQna = Qna::find($request->qna_id);

            $message = $this->saveQnaLog($updatedQna->subject_id, $request->qna_id, $request->answer, $request->teacher_id, $request->student_id);

            if ($qna) {
                return $this->sendResponse(['message' => $message], 'Response sent successfully');
            }
        }
    }


    private function saveQnaLog($subject_id, $qna_id, $response, $sender_id = null, $receiver_id = null)
    {
        $message = new QnaLog();
        $message->subject_id = $subject_id;
        $message->qna_id = $qna_id;

        // Check if $sender_id is not null before assigning
        if ($sender_id !== null) {
            $message->sender_id = $sender_id;
        }

        // Check if $receiver_id is not null before assigning
        if ($receiver_id !== null) {
            $message->receiver_id = $receiver_id;
        }

        if ($response !== null) {
            $message->response = $response;
            $message->save();
        }

        return $message;
    }

    /**
     * Find question in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchQuestionByKeyword(Request $request, $question)
    {
        $questions = DB::table('qna')
            ->where('question', 'like', '%' . $question . '%')
            ->orWhere('question', 'like', '%' . $question . '%')
            ->get();

        if ($questions->isNotEmpty()) {
            return $this->sendResponse(['questions' => $questions], 'Questions found successfully');
        } else {
            return $this->sendError('No matching questions found');
        }
    }
}
