<?php

namespace App\Http\Controllers\Api;

use App\Models\Chapter;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Support\Facades\DB;
//changed
class ChapterController extends BaseController
{

    /**
     * Display a listing of the chapters by subject.
     *
     * @return object
     */
    public function getChapterListBySubject(Request $request, $subjectId, $courseId)
    {
        $userType = $request->attributes->get('type');
        if ($userType === 'admin' || $userType = 'internship_admin' || $userType === 'trainer') {
            $validator = Validator::make(['subjectId' => $subjectId, 'courseId' => $courseId], [
                'subjectId' => 'required',
                'courseId' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator);
            } else {
                $course = DB::table('courses as cou')
                    ->where('cou.id', $courseId)
                    ->value('cou.name');
                $chapters = DB::table('chapters as c')
                    ->select('c.*', 'sub.name as subject_name', 'cou.name as course_name')
                    ->leftJoin('subjects as sub', 'sub.id', 'c.subject_id')
                    ->leftJoin('subjects as sub', 'sub.id', 'c.course_id')
                    ->where('c.subject_id', $subjectId)
                    ->where('c.course_id', $courseId)
                    ->get();
                return $this->sendResponse(['chapters' => $chapters, 'course' => $course]);
            }
        } else {
            return $this->sendAuthError("Not authorized fetch Chapters list.");
        }
    }

    /**
     * Display a listing of the chapters by subject.
     *
     * @return object
     */
    public function getChapterListByCourse($courseId)
    {
        $validator = Validator::make(['courseId' => $courseId], [
            'courseId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $chapters = Chapter::where('course_id', $courseId)->get();
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
            'subject_id' => 'required',
            'course_id' => 'required',
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
                    $existingChaptersCount = Chapter::where('course_id', $request->course_id)->count();
                    $isFirstChapter = $existingChaptersCount == 0;

                    $chapter = new Chapter();
                    $chapter->subject_id = $request->subject_id;
                    $chapter->course_id = $request->course_id;
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
                ->select('c.title as chapter_name', 'subject.name as subject_name', 'c.description as chapter_description', 'c.image as chapter_image', 'c.subject_id', 'c.course_id', 's.name as course_name')
                ->leftJoin('courses as s', 'c.course_id', 's.id')
                ->leftJoin('subjects as subject', 'c.subject_id', 'subject.id')
                ->where('c.id', $chapterId)
                ->first();
            $contents = DB::table('videos as v')
                ->where('v.chapter_id', $chapterId)
                ->get();
            $studentsCount = DB::table('students as s')
                ->where('s.subject_id', $chapter->subject_id)
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
