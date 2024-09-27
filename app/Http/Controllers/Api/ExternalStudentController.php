<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ExternalStudentController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getContents($courseId)
    {
        $studentId = DB::table('students')->where('auth_id', $this->getLoggedUserId())->value('id');
        $studentAuthId = $this->getLoggedUserId();
        Log::info("student", ['studentAuthId' => $studentAuthId]);
        // Course Details by $subjectId
        $course = DB::table('courses')
            ->select('id', 'name', 'image')
            ->where('id', $courseId)
            ->first();

        // Mini projects $subjectId
        $mini_projects = DB::table('mini_projects')
            ->select('id', 'name', 'image')
            ->where('course_id', $courseId)
            ->get();

        // List of Chapters from the Subject
        $chapters = DB::table('chapters as c')
            ->select('c.id', 'c.title', 'c.image')
            ->where('c.course_id', $courseId)
            ->get();

        // List of Videos from the Subject for each Chapter
        foreach ($chapters as $chapter) {
            // Fetch the chapter completion status
            $chapterLog = DB::table('chapter_logs')
            ->where('student_id', $studentAuthId)
            ->where('chapter_id', $chapter->id)
            ->first(['video_complete_status', 'assessment_complete_status','updated_at']);

            if ($chapterLog) {
                if ($chapterLog->video_complete_status == 1 && $chapterLog->assessment_complete_status == 1) {
                    $chapter->progress_status = 2; // completed
                } else {
                    $chapter->progress_status = 1; // progress
                }
            } else {
                $chapter->progress_status = 0; // not started
            }
            // $chapter->superSubjectChaptersCompleted = $superSubjectChaptersCompleted;
            $chapter->completedBufferTime = false;
            if ($chapter->progress_status == 2) {
                // Check if today's date is greater than the date when the chapter was completed
                $completionDate = Carbon::parse($chapterLog->updated_at)->startOfDay();
                $today = Carbon::now()->startOfDay();

                $chapter->completedBufferTime = $today->greaterThan($completionDate);
            }

            $chapter->videos = DB::table('videos as v')
                ->select('v.*', 'c.title as chapter', DB::raw('COALESCE(vl.watch_time, 0) as watch_time'), DB::raw('COALESCE(vl.status, 0) as video_complete_status'), 'el.active as elab_status')
                ->leftJoin('chapters as c', 'c.id', 'v.chapter_id')
                ->leftJoin('student_video_logs as vl', function ($join) use ($studentAuthId) {
                    $join->on('vl.video_id', '=', 'v.id')
                         ->where('vl.student_id', '=', $studentAuthId);
                })
                ->leftJoin('elabs as el', 'el.id', '=', 'v.elab_id')
                ->where('v.course_id', $courseId)
                ->where('v.chapter_id', $chapter->id)
                ->orderBy('v.id')
                ->get();

            // Include Assessment Results for each Video in the Chapter
            foreach ($chapter->videos as $video) {
                $video->assessment_results = DB::table('assessment_results as a')
                    ->select('a.*', 'assessment.no_of_questions as total_score')
                    ->join('assessments as assessment', 'a.assessment_id', '=', 'assessment.id')
                    ->where('a.student_id', $studentAuthId)
                    ->where('a.video_id', $video->id)
                    ->get();
            }
        }

        $video = null;

        //Video Playback
        $videoPlayback = DB::table('student_video_logs as logs')
            ->select('v.*', 'logs.watch_time')
            ->leftJoin('videos as v', 'v.id', 'logs.video_id')
            ->leftJoin('courses as cou', 'cou.id', 'v.course_id')
            ->where('logs.student_id', $this->getLoggedUserId())
            ->where('v.course_id', $courseId)
            ->orderBy('logs.updated_at', 'desc')
            ->whereNotNull('logs.video_id')
            ->first();

        if ($videoPlayback) {
            $videoPlayback->assessment_results = DB::table('assessment_results as a')
            ->select('a.*', 'assessment.no_of_questions as total_score')
            ->join('assessments as assessment', 'a.assessment_id', '=', 'assessment.id')
            ->where('a.student_id', $studentAuthId)
            ->where('a.video_id', $videoPlayback->id)
            ->get();
            $video = $videoPlayback;
        } else {
            // Latest Video from the Subject
            $latestVideo = DB::table('videos as v')
                ->select('v.*')
                ->where('v.course_id', $courseId)
                // ->orderBy('v.created_at', 'desc')
                ->first();

            if ($latestVideo) {
                $video = $latestVideo;
            }
        }

        // Trainer Id by $subjectId
        $trainer = DB::table('trainer_courses as ts')
            ->where('ts.course_id', $courseId)
            ->leftJoin('trainers as t', 't.auth_id', 'ts.trainer_id')
            ->first();

        // Final Contents Structure
        $contents = [
            'course' => $course,
            'chapters' => $chapters,
            'video' => $video,
            'trainer' => $trainer,
            'mini_projects' => $mini_projects,
        ];
        Log::info("chapters", ['chapters' => $chapters]);
        return $this->sendResponse(['contents' => $contents]);
    }



    public function areAllChaptersCompleted($studentAuthId, $chapters)
    {
        foreach ($chapters as $chapter) {
            $chapterLog = DB::table('chapter_logs')
                ->where('student_id', $studentAuthId)
                ->where('chapter_id', $chapter)
                ->first(['video_complete_status', 'assessment_complete_status']);

            if ($chapterLog && (!$chapterLog->video_complete_status || !$chapterLog->assessment_complete_status)) {
                return false; // Return false if any chapter is not completed
            }
        }
        return true; // Return true if all chapters are completed
    }
}
