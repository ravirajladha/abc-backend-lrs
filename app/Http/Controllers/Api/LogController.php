<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\StudentVideoLog;

use App\Http\Constants\VideoConstants;


class LogController extends BaseController
{
    /**
     * Update the video for the specified student.
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    // public function storeVideoLog(Request $request)
    // {
    //     $log = StudentVideoLog::where('student_id', $request->student_id)
    //         ->where('video_id', $request->video_id)
    //         ->first();

    //     if ($log) {
    //         $log->watch_time = $request->timestamp;
    //         $log->total_watch_time = $request->timestamp;
    //         $log->status = $request->status ? 1 : 0;
    //         $log->save();
    //     } else {
    //         $log = new StudentVideoLog();
    //         $log->student_id = $request->student_id;
    //         $log->video_id = $request->video_id;
    //         $log->watch_time = $request->timestamp;
    //         $log->total_watch_time = $request->timestamp;
    //         // $log->status = VideoConstants::STATUS_STARTED;
    //         $log->status = $request->status ? 1 : 0;
    //         $log->save();
    //     }
    //     return $this->sendResponse([], 'Video Log update successfully');
    // }

    public function storeVideoLog(Request $request)
    {
        $log = StudentVideoLog::where('student_id', $request->student_id)
            ->where('video_id', $request->video_id)
            ->first();

        if ($log) {
            $log->watch_time = $request->timestamp;
            $log->total_watch_time = $request->timestamp;
            if($log->status != 1){
                $log->status = $request->status ? 1 : 0;
            }
            $log->save();
        } else {
            $log = new StudentVideoLog();
            $log->student_id = $request->student_id;
            $log->video_id = $request->video_id;
            $log->watch_time = $request->timestamp;
            $log->total_watch_time = $request->timestamp;
            // $log->status = VideoConstants::STATUS_STARTED;
            $log->status = $request->status ? 1 : 0;
            $log->save();
        }

        // if all the videos of one chapter is completed mark the cahpater as completed

        // Retrieve the chapter_id associated with the updated video log
        $chapterId = DB::table('videos')->where('id', $request->video_id)->value('chapter_id');


        // if there is no assessment in any video of the cahpter then update teh assessment_status to 1 i.e complete.
        $assessmentStatus = DB::table('chapter_logs')
            ->where('student_id', $request->student_id)
            ->where('chapter_id', $chapterId)
            ->value('assessment_complete_status');
        if($assessmentStatus == 0){
            // fetch the video ids of chapter where tehre is no assessment
            $videosHasAssessment = DB::table('videos')->where('chapter_id', $chapterId)->whereNotNull('assessment_id')->count();
            if($videosHasAssessment == 0){
                DB::table('chapter_logs')
                ->updateOrInsert(
                    ['student_id' => $request->student_id, 'chapter_id' => $chapterId,],
                    ['assessment_complete_status' => 1, 'updated_at' => now(), 'created_at' => DB::raw('IFNULL(created_at, NOW())')]
                );
            }
        }


        // Fetch all video IDs of the chapter from the videos table
        $videoIds = DB::table('videos')->where('chapter_id', $chapterId)->pluck('id');

        // Check if all videos of the chapter are completed for the given student
        $allVideosCompleted = StudentVideoLog::where('student_id', $request->student_id)
            ->whereIn('video_id', $videoIds)
            ->where('status', 1) // Assuming 1 represents completed status
            ->count() == count($videoIds);


        if ($allVideosCompleted) {
            $currentStatus = DB::table('chapter_logs')
            ->where('student_id', $request->student_id)
            ->where('chapter_id', $chapterId)
            ->value('video_complete_status');
            // Update the chapter completion status to indicate that the chapter is complete
            if ( $currentStatus != 1) {
            DB::table('chapter_logs')
                ->updateOrInsert(
                    ['student_id' => $request->student_id, 'chapter_id' => $chapterId,],
                    ['video_complete_status' => 1,'updated_at' => now(), 'created_at' => DB::raw('IFNULL(created_at, NOW())')]
                );
            }
        } else {
            // If no row exists for the chapter in chapter_logs table, create a new row with status 0
            DB::table('chapter_logs')
                ->updateOrInsert(
                    ['student_id' => $request->student_id, 'chapter_id' => $chapterId,],
                    ['video_complete_status' => 0, 'updated_at' => now(), 'created_at' => DB::raw('IFNULL(created_at, NOW())')]
                );
        }
        return $this->sendResponse([], 'Video Log update successfully');
    }
}
