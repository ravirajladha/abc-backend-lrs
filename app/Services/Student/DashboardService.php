<?php

namespace App\Services\Student;

use App\Models\Student;
use App\Models\StudentVideoLog;

use App\Http\Helpers\DateTimeHelper;

use Illuminate\Support\Facades\DB;

use App\Http\Constants\TermTestConstants;

use App\Services\Admin\TermTestService;

use App\Models\ZoomCallUrl;
use Carbon\Carbon;
class DashboardService
{
    public function getStudentDashboardItems($studentAuthId)
    {
        $student = Student::where('auth_id', $studentAuthId)->first();
        $studentId = $student->id;
        // $classId = $student->class_id;

        $login_logs = DB::table('student_auth_logs')
            ->where('student_id', $studentAuthId)
            ->count();

        if ($login_logs > 0) {
            $last_login_at = DB::table('student_auth_logs')
                ->where('student_id', $studentAuthId)
                ->orderBy('created_at', 'desc')
                ->value('login_at');
        } else {
            $last_login_at = null;
        }

        $avg_assessment_score = DB::table('assessment_results')->where('student_id', $studentId)->avg('score');

        $total_watch_time = StudentVideoLog::where('student_id', $studentAuthId)->sum('watch_time');

        $videoStartedCountBySubject = DB::table('student_video_logs as logs')
            ->select(
                'sub.name as subject_name',
                'v.subject_id as subject_id',
                DB::raw('COUNT(DISTINCT logs.video_id) as started_videos')
            )
            ->leftJoin('videos as v', 'v.id', 'logs.video_id')
            ->leftJoin('subjects as sub', 'sub.id', 'v.subject_id')
            ->where('logs.student_id', $studentAuthId)
            ->groupBy('v.subject_id')
            ->groupBy('sub.name')
            ->get();

        $totalVideosByClass = DB::table('videos as v')
            ->select(
                'c.subject_id as subject_id',
                DB::raw('MAX(sub.name) as subject_name'),
                DB::raw('COUNT(DISTINCT v.id) as total_videos')
            )
            ->leftJoin('chapters as c', 'c.id', 'v.chapter_id')
            ->leftJoin('subjects as sub', 'sub.id', 'c.subject_id')
            // ->where('c.class_id', $student->class_id)
            ->groupBy('c.subject_id')
            ->get();

        $videoStats = [];

        $indexedStartedVideos = [];

        foreach ($videoStartedCountBySubject as $startedVideo) {
            $indexedStartedVideos[$startedVideo->subject_id] = $startedVideo;
        }

        foreach ($totalVideosByClass as $totalVideo) {
            $subjectId = $totalVideo->subject_id;
            if (isset($indexedStartedVideos[$subjectId])) {
                $videoStats[] = (object)[
                    'subject_id' => $subjectId,
                    'subject_name' => $indexedStartedVideos[$subjectId]->subject_name,
                    'started_video_count' => $indexedStartedVideos[$subjectId]->started_videos,
                    'total_video_count' => $totalVideo->total_videos,
                ];
            } else {
                $videoStats[] = (object)[
                    'subject_id' => $subjectId,
                    'subject_name' => $totalVideo->subject_name,
                    'started_video_count' => 0,
                    'total_video_count' => $totalVideo->total_videos,
                ];
            }
        }

        $resultService = new ResultService();

        $firstTermResult = $resultService->getTermTestTotalResult($studentId);
        // $secondTermResult = $resultService->getTermTestTotalResult($studentId, TermTestConstants::SECOND_TERM);
        // $thirdTermResult = $resultService->getTermTestTotalResult($studentId, TermTestConstants::THIRD_TERM);

        $termTestService = new TermTestService();
        $firstTermTotalMarks = $termTestService->getTermTestTotalMarks($studentId);
        // $secondTermTotalMarks = $termTestService->getTermTestTotalMarks($classId, TermTestConstants::SECOND_TERM);
        // $thirdTermTotalMarks = $termTestService->getTermTestTotalMarks($classId, TermTestConstants::THIRD_TERM);

        $dateTimeHelper = new DateTimeHelper();

        $today = Carbon::today()->toDateString();

        $zoomCall = ZoomCallUrl::where('date', $today)->select('url')->first();

        $res = [
            'id' => $studentId,
            'student_name' => $student->name,
            'last_login_at' => $last_login_at,
            'video_stats' => !empty($videoStats) ? $videoStats : null,
            'total_watch_time' => !empty($total_watch_time) ?  $dateTimeHelper->formatTime($total_watch_time) : null,
            'avg_assessment_score' => $avg_assessment_score !== null ? ($avg_assessment_score !== 0.00 ? number_format(round($avg_assessment_score, 2), 2) : '0.00') : null,
            'first_term_results' => $firstTermResult !== 0 ? $firstTermResult : 0,
            'first_term_total_marks' => $firstTermTotalMarks !== 0 ? $firstTermTotalMarks : null,
            // 'second_term_results' => $secondTermResult !== 0 ? $secondTermResult : 0,
            // 'second_term_total_marks' => $secondTermTotalMarks !== 0 ? $secondTermTotalMarks : null,
            // 'third_term_results' => $thirdTermResult !== 0 ? $thirdTermResult : 0,
            // 'third_term_total_marks' => $thirdTermTotalMarks !== 0 ? $thirdTermTotalMarks : null,
            'zoomCall' => $zoomCall,
        ];

        return $res;
    }
}
