<?php

namespace App\Http\Controllers\Api;

use App\Models\ZoomCallUrl;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ZoomCallController extends BaseController
{

    /**
     * Display a listing of the zoom Call Urls.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getZoomCallList()
    {
        $zoomCallUrls = ZoomCallUrl::get();
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
            'url' => 'required|url',
            'passcode' => 'required|string', // Assuming passcode is a required field
        ]);
    
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }
    
        if (ZoomCallUrl::where('date', $request->date)
                       ->where('time', $request->time)
                       ->exists()) {
            $validator = Validator::make($request->all(), [
                'date' => ['date' => 'Zoom call already exists for this date and time.']
            ]);
    
            return $this->sendValidationError($validator);
        }
        $loggedUserId = $this->getLoggedUserId();

        $zoomCallUrl = new ZoomCallUrl();
        $zoomCallUrl->date = $request->input('date');
        $zoomCallUrl->time = $request->input('time'); // Store the time
        $zoomCallUrl->url = $request->input('url');
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
        ->where('id',$id)
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
            'password' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }
    
        // Check if a Zoom call with the same date and time exists, excluding the current record
        if (ZoomCallUrl::where('date', $request->date)
                       ->where('time', $request->time)
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
        $zoomCallUrl->passcode = $request->input('password'); // Updated to passcode
        $zoomCallUrl->updated_by = $loggedUserId;
        if ($zoomCallUrl->save()) {
            return $this->sendResponse(['zoomCallUrl' => $zoomCallUrl], 'Zoom Call updated successfully.');
        } else {
            return $this->sendError('Failed to update Zoom Call.', [], 500);
        }
    }
    
}
