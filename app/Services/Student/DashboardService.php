<?php

namespace App\Services\Student;

use App\Models\Student;
use App\Models\Auth;
use App\Models\StudentVideoLog;

use App\Http\Helpers\DateTimeHelper;

use Illuminate\Support\Facades\DB;

use App\Http\Constants\TermTestConstants;

use App\Services\Admin\TestService;

use App\Models\ZoomCallUrl;
use Carbon\Carbon;
class DashboardService
{
    public function getStudentDashboardItems($studentAuthId)
    {
        // $student = Student::where('auth_id', $studentAuthId)->first();
        // $studentId = $student->id;
        // $classId = $student->class_id;

        $auth = Auth::where('id', $studentAuthId)->first();

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

        $avg_assessment_score = DB::table('assessment_results')->where('student_id', $studentAuthId)->avg('score');

        $total_watch_time = StudentVideoLog::where('student_id', $studentAuthId)->sum('watch_time');

        $videoStartedCountByCourse = DB::table('student_video_logs as logs')
            ->select(
                'cou.name as course_name',
                'v.course_id as course_id',
                DB::raw('COUNT(DISTINCT logs.video_id) as started_videos')
            )
            ->leftJoin('videos as v', 'v.id', 'logs.video_id')
            ->leftJoin('courses as cou', 'cou.id', 'v.course_id')
            ->where('logs.student_id', $studentAuthId)
            ->groupBy('v.course_id')
            ->groupBy('cou.name')
            ->get();

        $totalVideosByCourse= DB::table('videos as v')
            ->select(
                'c.course_id as course_id',
                DB::raw('MAX(cou.name) as course_name'),
                DB::raw('COUNT(DISTINCT v.id) as total_videos')
            )
            ->leftJoin('chapters as c', 'c.id', 'v.chapter_id')
            ->leftJoin('courses as cou', 'cou.id', 'c.course_id')
            ->groupBy('c.course_id')
            ->get();

        $videoStats = [];

        $indexedStartedVideos = [];

        foreach ($videoStartedCountByCourse as $startedVideo) {
            $indexedStartedVideos[$startedVideo->course_id] = $startedVideo;
        }

        foreach ($totalVideosByCourse as $totalVideo) {
            $courseId = $totalVideo->course_id;
            if (isset($indexedStartedVideos[$courseId])) {
                $videoStats[] = (object)[
                    'course_id' => $courseId,
                    'course_name' => $indexedStartedVideos[$courseId]->course_name,
                    'started_video_count' => $indexedStartedVideos[$courseId]->started_videos,
                    'total_video_count' => $totalVideo->total_videos,
                ];
            } else {
                $videoStats[] = (object)[
                    'course_id' => $courseId,
                    'course_name' => $totalVideo->course_name,
                    'started_video_count' => 0,
                    'total_video_count' => $totalVideo->total_videos,
                ];
            }
        }

        $resultService = new ResultService();

        $testResult = $resultService->getTestTotalResult($studentAuthId);
        // $secondTermResult = $resultService->getTermTestTotalResult($studentAuthId, TermTestConstants::SECOND_TERM);
        // $thirdTermResult = $resultService->getTermTestTotalResult($studentAuthId, TermTestConstants::THIRD_TERM);

        $testService = new TestService();
        $testTotalMarks = $testService->getTestTotalMarks($studentAuthId);
        // $secondTermTotalMarks = $termTestService->getTermTestTotalMarks($classId, TermTestConstants::SECOND_TERM);
        // $thirdTermTotalMarks = $termTestService->getTermTestTotalMarks($classId, TermTestConstants::THIRD_TERM);

        $dateTimeHelper = new DateTimeHelper();

        $today = Carbon::today()->toDateString();

        $zoomCall = ZoomCallUrl::where('date', $today)->select('url')->first();

        $res = [
            'id' => $studentAuthId,
            'student_name' => $auth->username,
            'last_login_at' => $last_login_at,
            'video_stats' => !empty($videoStats) ? $videoStats : null,
            'total_watch_time' => !empty($total_watch_time) ?  $dateTimeHelper->formatTime($total_watch_time) : null,
            'avg_assessment_score' => $avg_assessment_score !== null ? ($avg_assessment_score !== 0.00 ? number_format(round($avg_assessment_score, 2), 2) : '0.00') : null,
            'test_results' => $testResult !== 0 ? $testResult : 0,
            'test_total_marks' => $testTotalMarks !== 0 ? $testTotalMarks : null,
            'zoomCall' => $zoomCall,
        ];

        return $res;
    }
}
