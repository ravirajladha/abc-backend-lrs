<?php

namespace App\Http\Controllers\Api;

use App\Models\College;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;

class CollegeController extends BaseController
{
    public function getColleges()
    {
        $colleges = College::all();
        return $this->sendResponse(['colleges' => $colleges]);
    }


    public function getCollegeDetails($collegeId)
    {
        $validator = Validator::make(['collegeId' => $collegeId], [
            'collegeId' => 'required|exists:colleges,id',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $college = College::find($collegeId);
        return $this->sendResponse(['college' => $college]);
    }
    public function storeCollege(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'address' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }
        $loggedUserId = $this->getLoggedUserId();

        $college = College::create([
            'name' => $request->name,
            'city' => $request->city,
            'state' => $request->state,
            'address' => $request->address,
            'created_by' => $loggedUserId,
        ]);
        return $this->sendResponse(['college' => $college], 'College created successfully.');
    }
    public function updateStatus($collegeId, Request $request)
    {
        // Validate status
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:0,1',
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        // Find the college using the passed collegeId
        $college = College::find($collegeId);

        // If college found, update the status
        if ($college) {
            $college->status = $request->status;
            $college->save();

            return $this->sendResponse([], 'College status updated successfully.');
        }

        // Return a response if college not found
        return $this->sendResponse([], 'College not found.', 404);
    }


    // Update College Details
    public function updateCollege(Request $request, $collegeId)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'address' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        // Find the college
        $college = College::find($collegeId);
        if (!$college) {
            return $this->sendResponse([], 'College not found.', 404);
        }

        // Update the college details
        $college->update([
            'name' => $request->name,
            'city' => $request->city,
            'state' => $request->state,
            'address' => $request->address,
        ]);

        return $this->sendResponse(['college' => $college], 'College updated successfully.');
    }

}
