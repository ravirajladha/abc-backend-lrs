<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class VideoController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getContents($subjectId)
    {
        $studentId = DB::table('students')->where('auth_id', $this->getLoggedUserId())->value('id');
        $studentAuthId = $this->getLoggedUserId();

        // Subject Details by $subjectId
        $subject = DB::table('subjects')
            ->select('id', 'name', 'image')
            ->where('id', $subjectId)
            ->first();

        // Mini projects $subjectId
        $mini_projects = DB::table('mini_projects')
            ->select('id', 'name', 'image')
            ->where('subject_id', $subjectId)
            ->get();

        // List of Chapters from the Subject
        $chapters = DB::table('chapters as c')
            ->select('c.id', 'c.title', 'c.image', 'c.lock_status')
            ->where('c.subject_id', $subjectId)
            ->get();

        // List of Videos from the Subject for each Chapter
        foreach ($chapters as $chapter) {
            $chapter->videos = DB::table('videos as v')
                ->select('v.*', 'c.title as chapter', DB::raw('COALESCE(vl.watch_time, 0) as watch_time'),DB::raw('COALESCE(vl.status, 0) as video_complete_status'), 'el.active as elab_status')
                ->leftJoin('chapters as c', 'c.id', 'v.chapter_id')
                ->leftJoin('student_video_logs as vl', function ($join) use ($studentAuthId) {
                    $join->on('vl.video_id', '=', 'v.id')
                         ->where('vl.student_id', '=', $studentAuthId);
                })
                ->leftJoin('elabs as el', 'el.id', '=', 'v.elab_id')
                ->where('v.subject_id', $subjectId)
                ->where('v.chapter_id', $chapter->id)
                ->orderBy('v.id')
                ->get();

            // Include Assessment Results for each Video in the Chapter
            foreach ($chapter->videos as $video) {
                $video->assessment_results = DB::table('assessment_results as a')
                    ->select('a.*', 'assessment.no_of_questions as total_score')
                    ->join('assessments as assessment', 'a.assessment_id', '=', 'assessment.id')
                    ->where('a.student_id', $studentId)
                    ->where('a.video_id', $video->id)
                    ->get();
            }
        }

        $video = null;

        //Video Playback
        $videoPlayback = DB::table('student_video_logs as logs')
            ->select('v.*', 'logs.watch_time')
            ->leftJoin('videos as v', 'v.id', 'logs.video_id')
            ->leftJoin('subjects as sub', 'sub.id', 'v.subject_id')
            ->where('logs.student_id', $this->getLoggedUserId())
            ->where('v.subject_id', $subjectId)
            ->orderBy('logs.updated_at', 'desc')
            ->whereNotNull('logs.video_id')
            ->first();

        if ($videoPlayback) {
            $videoPlayback->assessment_results = DB::table('assessment_results as a')
            ->select('a.*', 'assessment.no_of_questions as total_score')
            ->join('assessments as assessment', 'a.assessment_id', '=', 'assessment.id')
            ->where('a.student_id', $studentId)
            ->where('a.video_id', $videoPlayback->id)
            ->get();

            $video = $videoPlayback;
        } else {
            // Latest Video from the Subject
            $latestVideo = DB::table('videos as v')
                ->select('v.*')
                ->where('v.subject_id', $subjectId)
                // ->orderBy('v.created_at', 'desc')
                ->first();

            if ($latestVideo) {
                $video = $latestVideo;
            }
        }

        // Trianer Id by $subjectId
        $trainer = DB::table('trainer_subjects as ts')
            ->where('ts.subject_id', $subjectId)
            ->leftJoin('trainers as t', 't.id', 'ts.trainer_id')
            ->first();

        // Final Contents Structure
        $contents = [
            'subject' => $subject,
            'chapters' => $chapters,
            'video' => $video,
            'trainer' => $trainer,
            'mini_projects' => $mini_projects,
        ];

        return $this->sendResponse(['contents' => $contents]);
    }



    /**
     * Store video.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeVideoDetails(Request $request)
    {
        $rules = [
            'subject_id' => 'required|numeric',
            'course_id' => 'required|numeric',
            'chapter_id' => 'required|numeric',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'required|file|mimes:mp4,mov,avi',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $video = new Video();
            $video->course_id = $request->course_id;
            $video->subject_id = $request->subject_id;
            $video->chapter_id = $request->chapter_id;
            $video->title = $request->title;
            $video->description = $request->description;
            $video->assessment_id = $request->assessment;
            $video->ebook_id  = $request->ebook_id;
            $video->elab_id  = $request->elab_id;
            $video->ebook_module_id  = $request->ebook_module_id;
            $video->ebook_sections = $request->ebook_sections;

            if ($request->hasFile('url')) {
                $file = $request->file('url');
                $extension = $file->extension();
                $filename = Str::random(4) . time() . '.' . $extension;
                $file->move('uploads/videos', $filename);
                $video->url = 'videos/' . $filename;
            } else {
                return $this->sendError('Video file is required.');
            }

            if ($video->save()) {
                return $this->sendResponse([], 'Video created successfully');
            } else {
                return $this->sendResponse([], 'Failed to save video.');
            }
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Video  $video
     * @return \Illuminate\Http\JsonResponse
     */

     //this is the old function, as i have added the method whiich checks that elab is activate or not
    //  once tested, the below function can be removed

    // public function getVideoDetails($videoId)
    // {
    //     $video = DB::table('videos as v')
    //         ->select('v.*', 'a.title as assessment_title', 'e.title as ebook_title', 'el.title as elab_title')
    //         ->leftJoin('assessments as a', 'a.id', '=', 'v.assessment_id')
    //         ->leftJoin('elabs as el', 'el.id', '=', 'v.elab_id')
    //         ->leftJoin('ebooks as e', 'e.id', '=', 'v.ebook_id')
    //         ->where('v.id', $videoId)
    //         ->first();

    //     return $this->sendResponse(['video' => $video], 'Video fetched successfully');
    // }


    public function getVideoDetails($videoId)
{
    $video = DB::table('videos as v')
        ->select('v.*', 'a.title as assessment_title', 'e.title as ebook_title', 'el.title as elab_title')
        ->leftJoin('assessments as a', 'a.id', '=', 'v.assessment_id')
        ->leftJoin('elabs as el', function($join) {
            $join->on('el.id', '=', 'v.elab_id')
                 ->where('el.active', '=', 1);
        })
        ->leftJoin('ebooks as e', 'e.id', '=', 'v.ebook_id')
        ->where('v.id', $videoId)
        ->first();

    return $this->sendResponse(['video' => $video], 'Video fetched successfully');
}



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Video  $video
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateVideoDetails(Request $request, $videoId)
    {
        $rules = [
            'subject_id' => 'required|numeric',
            'course_id' => 'required|numeric',
            'chapter_id' => 'required|numeric',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'nullable|file|mimes:mp4,mov,avi|max:50000',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $video = Video::findOrFail($videoId);

            $video->course_id = $request->course_id;
            $video->subject_id = $request->subject_id;
            $video->chapter_id = $request->chapter_id;
            $video->title = $request->title;
            $video->description = $request->description;
            $video->assessment_id = $request->assessment;
            $video->ebook_id  = $request->ebook_id;
            $video->elab_id  = $request->elab_id;
            $video->ebook_module_id  = $request->ebook_module_id;
            $video->ebook_sections = $request->ebook_sections;

            if ($request->hasFile('url')) {
                // Delete the existing file
                if ($video->url) {
                    Storage::delete($video->url);
                }

                $file = $request->file('url');
                $extension = $file->extension();
                $filename = Str::random(4) . time() . '.' . $extension;
                $file->move('uploads/videos', $filename);
                $video->url = 'videos/' . $filename;
            }

            if ($video->save()) {
                return $this->sendResponse([], 'Video updated successfully');
            } else {
                return $this->sendResponse([], 'Failed to update video.');
            }
        }
    }



    /**
     * Remove the specified class from storage.
     *
     */
    public function deleteVideoDetails(Request $request, $videoId)
    {
        $validator = Validator::make(
            array_merge($request->all(), ['videoId' => $videoId]),
            [
                'videoId' => 'required',
            ]
        );
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $class = Video::find($videoId);
            $class->delete();
        }

        return $this->sendResponse([], 'Video deleted successfully');
    }
}
