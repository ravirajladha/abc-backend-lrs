<?php

namespace App\Http\Controllers\Api;

use App\Models\Chapter;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Support\Facades\DB;

class ChapterController extends BaseController
{

    /**
     * Display a listing of the chapters by class.
     *
     * @return object
     */
    public function getChapterListByClass(Request $request, $classId, $subjectId)
    {
        $userType = $request->attributes->get('type');
        if ($userType === 'admin' || $userType = 'school' || $userType === 'teacher') {
            $validator = Validator::make(['classId' => $classId, 'subjectId' => $subjectId], [
                'classId' => 'required',
                'subjectId' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator);
            } else {
                $subject = DB::table('subjects as s')
                    ->where('s.id', $subjectId)
                    ->value('s.name');
                $chapters = DB::table('chapters as c')
                    ->select('c.*', 'cl.name as class_name', 's.name as subject_name')
                    ->leftJoin('classes as cl', 'cl.id', 'c.class_id')
                    ->leftJoin('subjects as s', 's.id', 'c.subject_id')
                    ->where('c.class_id', $classId)
                    ->where('c.subject_id', $subjectId)
                    ->get();
                return $this->sendResponse(['chapters' => $chapters, 'subject' => $subject]);
            }
        } else {
            return $this->sendAuthError("Not authorized fetch schools list.");
        }
    }

    /**
     * Display a listing of the chapters by subject.
     *
     * @return object
     */
    public function getChapterListBySubject($subjectId)
    {
        $validator = Validator::make(['subjectId' => $subjectId], [
            'subjectId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $chapters = Chapter::where('subject_id', $subjectId)->get();
            return $this->sendResponse(['chapters' => $chapters]);
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeChapterDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required',
            'subject_id' => 'required',
            'chapter_name' => 'required',
            'chapter_name.*' => 'required|max:100',
            'chapter_description' => 'string',
            'chapter_image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            if (!is_array($request->chapter_name)) {
                $chapterNames = json_decode($request->chapter_name, true);
                if ($chapterNames === null) {
                    $chapterNames = [$request->chapter_name];
                }
            } else {
                $chapterNames = $request->chapter_name;
            }

            if ($chapterNames !== null && !empty($chapterNames)) {
                $chaptersCount = 0;

                foreach ($chapterNames as $chapterName) {
                    $chaptersCount++;

                    // make the first chapter unlocked by default
                    $existingChaptersCount = Chapter::where('subject_id', $request->subject_id)->count();
                    $isFirstChapter = $existingChaptersCount == 0;

                    $chapter = new Chapter();
                    $chapter->class_id = $request->class_id;
                    $chapter->subject_id = $request->subject_id;
                    $chapter->title = $chapterName;
                    $chapter->lock_status = $isFirstChapter ? 1 : 0;
                    $chapter->save();
                }
                return $this->sendResponse(['count' => $chaptersCount], 'Chapter added successfully');
            } else {
                return $this->sendResponse([], 'Failed to add chapters');
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Chapter  $chapter
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChapterDetails($chapterId)
    {
        $validator = Validator::make(['chapterId' => $chapterId], [
            'chapterId' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $contentsCount = 0;
            $studentsCount = 0;
            $chapter =  DB::table('chapters as c')
                ->select('c.title as chapter_name', 'class.name as class_name', 'c.description as chapter_description', 'c.image as chapter_image', 'c.class_id', 'c.subject_id', 's.name as subject_name')
                ->leftJoin('subjects as s', 'c.subject_id', 's.id')
                ->leftJoin('classes as class', 'c.class_id', 'class.id')
                ->where('c.id', $chapterId)
                ->first();
            $contents = DB::table('videos as v')
                ->where('v.chapter_id', $chapterId)
                ->get();
            $studentsCount = DB::table('students as s')
                ->where('s.class_id', $chapter->class_id)
                ->count();
            if ($contents) {
                $contentsCount = count($contents);
                $chapter->no_videos = $contentsCount;
                $chapter->no_students = $studentsCount;
            }
            return $this->sendResponse(['chapter' => $chapter, 'contents' => $contents]);
        }
    }

    /**
     * Update the specified chapter in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateChapterDetails(Request $request, $chapterId)
    {
        $validator = Validator::make(array_merge($request->all(), ['chapter_id' => $chapterId]), [
            'chapter_id' => 'required',
            'chapter_name' => 'required|string|max:100',
            'chapter_description' => 'string',
            // 'chapter_image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $chapter = Chapter::where('id', $chapterId)->first();

            if ($request->has('chapter_name')) {
                $chapter->title = $request->input('chapter_name');
            }

            if ($request->has('chapter_description')) {
                $chapter->description = $request->input('chapter_description');
            }

            // if ($request->hasFile('chapter_image')) {
            //     $extension = $request->file('chapter_image')->extension();
            //     $filename = Str::random(4) . time() . '.' . $extension;
            //     $chapter->image = $request->file('chapter_image')->move(('uploads/images/chapter'), $filename);
            // }

            $chapter->save();
            return $this->sendResponse(['chapter' => $chapter], 'Chapter updated successfully');
        }
        return $this->sendError('Failed to update chapter');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Chapter  $chapter
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteChapterDetails(Request $request, $chapterId)
    {
        $validator = Validator::make(['chapter_id' => $chapterId], [
            'chapter_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $chapter = Chapter::where('id', $chapterId)->first();
            $chapter->delete();
        }

        return $this->sendResponse([], 'Chapter deleted successfully');
    }

    public function updatechapterLockStatus(Request $request, $chapterId){
        $validator = Validator::make(['chapter_id' => $chapterId], [
            'chapter_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $chapter = Chapter::where('id', $chapterId)->first();
            $chapter->lock_status = $request->status;
            $chapter->save();
        }

        return $this->sendResponse(['chapter' => $chapter], 'Chapter status updated successfully');
    }
}
