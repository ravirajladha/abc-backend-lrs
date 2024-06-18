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
            'url' => 'required|url',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        if (ZoomCallUrl::where('date', $request->date)->exists()) {
            $validator = Validator::make($request->all(), [
                'date' => [ function ($attribute, $value, $fail) {
                    $fail('Zoom call already exists for this date.');
                }]
            ]);

            return $this->sendValidationError($validator);
        }

        $zoomCallUrl = new ZoomCallUrl();
        $zoomCallUrl->date = $request->input('date');
        $zoomCallUrl->url = $request->input('url');

        if ($zoomCallUrl->save()) {
            return $this->sendResponse(['zoomCallUrl' => $zoomCallUrl], 'Zoom call created successfully.');
        } else {
            return $this->sendResponse([], 'Failed to create zoom call.');
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
            'url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }
        if (ZoomCallUrl::where('date', $request->date)->where('id', '!=', $id)->exists()) {
            $validator = Validator::make($request->all(), [
                'date' => [ function ($attribute, $value, $fail) {
                    $fail('Zoom call already exists for this date.');
                }]
            ]);

            return $this->sendValidationError($validator);
        }

        $zoomCallUrl = ZoomCallUrl::find($id);
        if (!$zoomCallUrl) {
            return $this->sendError('Zoom Call not found.', [], 404);
        }
        $zoomCallUrl->date = $request->input('date');
        $zoomCallUrl->url = $request->input('url');
        if ($zoomCallUrl->save()) {
            return $this->sendResponse(['zoomCallUrl' => $zoomCallUrl], 'Zoom Call updated successfully.');
        } else {
            return $this->sendError('Failed to update zoomCallUrl.', [], 500);
        }
    }
}
