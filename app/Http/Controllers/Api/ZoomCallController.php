<?php

namespace App\Http\Controllers\Api;

use App\Models\ZoomCallUrl;
use App\Models\LiveSessionClick;
use App\Models\Course;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Http\Constants\AuthConstants;

class ZoomCallController extends BaseController
{
    /**
     * Display a listing of the zoom Call Urls.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getZoomCallList()
    {
        $loggedUser = $this->getLoggerUser();
        Log::info('user', ['loggedUser' => $loggedUser]);
        if ($loggedUser->type === AuthConstants::TYPE_TRAINER) {
            $courseIds = Course::where('trainer_id', $loggedUser->id)
            ->pluck('id')->toArray();
            $zoomCallUrls = ZoomCallUrl::select('zoom_call_urls.*', 'courses.name as course_name', 'subjects.name as subject_name')
            ->join('courses', 'zoom_call_urls.course_id', '=', 'courses.id')
            ->join('subjects', 'zoom_call_urls.subject_id', '=', 'subjects.id')
            ->whereIn('zoom_call_urls.course_id', $courseIds)
            ->get();
        } else {
            $zoomCallUrls = ZoomCallUrl::select('zoom_call_urls.*', 'courses.name as course_name', 'subjects.name as subject_name')
            ->join('courses', 'zoom_call_urls.course_id', '=', 'courses.id')
            ->join('subjects', 'zoom_call_urls.subject_id', '=', 'subjects.id')
            ->get();
        }


        return $this->sendResponse(['zoomCallUrls' => $zoomCallUrls]);
    }

    /**
     *  Store the zoom Call Urls.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeZoomCall(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'course_id' => 'required|exists:courses,id',
            'subject_id' => 'required|exists:subjects,id',
            'session_type' => 'required',
            'url' => 'required|url',
            'passcode' => 'required|string', // Assuming passcode is a required field
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        if (ZoomCallUrl::where('date', $request->date)
                       ->where('time', $request->time)
                       ->where('course_id', $request->course_id)
                    //    ->where('session_type', $request->session_type)
                       ->exists()) {
            $validator = Validator::make($request->all(), [
                'date' => ['date' => 'Zoom call already exists for this course, date and time.']
            ]);

            return $this->sendValidationError($validator);
        }
        $loggedUserId = $this->getLoggedUserId();

        $zoomCallUrl = new ZoomCallUrl();
        $zoomCallUrl->date = $request->input('date');
        $zoomCallUrl->time = $request->input('time'); // Store the time
        $zoomCallUrl->url = $request->input('url');
        $zoomCallUrl->subject_id = $request->input('subject_id');
        $zoomCallUrl->course_id = $request->input('course_id');
        $zoomCallUrl->session_type = $request->input('session_type');
        $zoomCallUrl->passcode = $request->input('passcode'); // Store the passcode
        $zoomCallUrl->created_by = $loggedUserId;

        if ($zoomCallUrl->save()) {
            return $this->sendResponse(['zoomCallUrl' => $zoomCallUrl], 'Zoom call created successfully.');
        } else {
            return $this->sendResponse([], 'Failed to create Zoom call.');
        }
    }

    public function getZoomCallById($id)
    {
        $zoomCall = DB::table('zoom_call_urls')
        ->where('id', $id)
        ->first();
        if (!$zoomCall) {
            return $this->sendError('Zoom Call not found.', [], 404);
        }

        return $this->sendResponse(['zoomCall' => $zoomCall], 'Zoom Call fetched successfully.');
    }

    public function updateZoomCall(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'url' => 'required|url',
            'course_id' => 'required|exists:courses,id',
            'subject_id' => 'required|exists:subjects,id',
            'session_type' => 'required',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        // Check if a Zoom call with the same date and time exists, excluding the current record
        if (ZoomCallUrl::where('date', $request->date)
                       ->where('time', $request->time)
                       ->where('course_id', $request->course_id)
                    //    ->where('session_type', $request->session_type)
                       ->where('id', '!=', $id)
                       ->exists()) {
            $validator = Validator::make($request->all(), [
                'date' => ['Zoom call already exists for this date and time.']
            ]);

            return $this->sendValidationError($validator);
        }

        $zoomCallUrl = ZoomCallUrl::find($id);
        if (!$zoomCallUrl) {
            return $this->sendError('Zoom Call not found.', [], 404);
        }
        $loggedUserId = $this->getLoggedUserId();


        $zoomCallUrl->date = $request->input('date');
        $zoomCallUrl->time = $request->input('time');
        $zoomCallUrl->url = $request->input('url');
        $zoomCallUrl->subject_id = $request->input('subject_id');
        $zoomCallUrl->course_id = $request->input('course_id');
        $zoomCallUrl->session_type = $request->input('session_type');
        $zoomCallUrl->passcode = $request->input('password'); // Updated to passcode
        $zoomCallUrl->updated_by = $loggedUserId;
        if ($zoomCallUrl->save()) {
            return $this->sendResponse(['zoomCallUrl' => $zoomCallUrl], 'Zoom Call updated successfully.');
        } else {
            return $this->sendError('Failed to update Zoom Call.', [], 500);
        }
    }

    public function trackLiveSessionClick($sessionId)
    {
        $loggedUserId = $this->getLoggedUserId();

        // Validate the sessionId
        $validator = Validator::make(['sessionId' => $sessionId], [
            'sessionId' => 'required|exists:zoom_call_urls,id',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        // Check if the student has already clicked on this session
        $existingClick = LiveSessionClick::where('student_id', $loggedUserId)
            ->where('session_id', $sessionId)
            ->first();

        if ($existingClick) {
            return $this->sendResponse([], 'You have already clicked on this session.');
        }

        // Create a new click record
        LiveSessionClick::create([
            'student_id' => $loggedUserId,
            'session_id' => $sessionId,
            'clicked_at' => now(),
        ]);

        return $this->sendResponse([], 'Live session click tracked successfully.');
    }
    public function getStudentsBySessionId($sessionId)
    {
        // Validate the sessionId
        $validator = Validator::make(['sessionId' => $sessionId], [
            'sessionId' => 'required|exists:zoom_call_urls,id',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        // Fetch the students who clicked the session link (attended the session)
        $students = DB::table('live_session_clicks')
            ->join('auth', 'live_session_clicks.student_id', '=', 'auth.id')
            ->where('live_session_clicks.session_id', $sessionId)
            ->select('auth.id', 'auth.username as name', 'auth.email', 'live_session_clicks.clicked_at')
            ->get();

        // Return the list of students
        return $this->sendResponse(['students' => $students], 'List of students who attended the session.');
    }


}
