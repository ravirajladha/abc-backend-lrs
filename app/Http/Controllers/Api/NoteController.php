<?php

namespace App\Http\Controllers\Api;

use App\Models\Note;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NoteController extends BaseController
{
    /**
     * Show the form for creating a new resource.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotesByVideo(Request $request)
    {
        $student_id = Student::where('auth_id', $request->studentId)->value('id');

        $notes = DB::table('notes')
            ->select('*')
            ->where('student_id', $student_id)
            ->where('video_id', $request->videoId)
            ->get();
        return $this->sendResponse(['notes' => $notes]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeNotesByVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'note' => 'required|string',
            'timestamp' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $student_id = Student::where('auth_id', $request->student_id)->value('id');
            $note = new Note();
            $note->student_id = $student_id;
            $note->video_id = $request->video_id;
            $note->content = $request->note;
            $note->timestamp = $request->timestamp;
            $note->save();
            return $this->sendResponse(['note' => $note], 'Note added successfully');
        }
    }
}
