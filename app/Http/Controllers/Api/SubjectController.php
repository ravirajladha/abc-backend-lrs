<?php

namespace App\Http\Controllers\Api;

use App\Models\Classes;
use App\Models\Subject;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

use App\Services\Admin\ResultService;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;

//checked
class SubjectController extends BaseController
{
    /**
     * Display a listing of the subjects.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSubjectsList()
    {

        $loggedUserId = $this->getLoggedUserId();
    

        Log::info('Received subjectId:', ['subjectId' => $loggedUserId]);

        $subjects = Subject::orderBy('position')->orderBy('created_at')->get();
        return $this->sendResponse(['subjects' => $subjects]);
    }
    

    /**
     * Store a newly created subject in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function storeSubjectDetails(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'subject_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subjects', 'name'),
            ],
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $loggedUserId = $this->getLoggedUserId();
            $subject = new Subject;
            $subject->name = $request->subject_name;
            $subject->created_by = $loggedUserId;
            $subject->save();
        }
        return $this->sendResponse([], 'Subject added successfully');
    }

    /**
     * Display the specified subject.
     *
     */
    public function getSubjectDetails($subjectId)
    {
        $validator = Validator::make(['subject_id' => $subjectId], [
            'subject_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $subject = Subject::find($subjectId);
        }

        return $this->sendResponse(['subject' => $subject]);
    }

    /**
     * Update the specified subject in storage.
     *
     */
    public function updateSubjectDetails(Request $request, $subjectId)
    {
        try {
            // Validate the request data
            $validator = Validator::make(
                array_merge($request->all(), ['subjectId' => $subjectId]),
                [
                    'subjectId' => 'required|exists:subjects,id',
                    'subject_name' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('subjects', 'name')->ignore($subjectId),
                    ],
                    'status' => 'required|boolean',
                    'position' => 'required|integer',
                ]
            );
    
            // If validation fails, return the validation error response
            if ($validator->fails()) {
                return $this->sendValidationError($validator);
            }
    
            // Find the subject by ID
            $subject = Subject::find($subjectId);
    
            // If subject is not found, return an error response
            if (!$subject) {
                return $this->sendError('Subject not found!', 404);
            }
            $loggedUserId = $this->getLoggedUserId();
   

            // Update the subject details
            $subject->update([
                'name' => $request->subject_name,
                'status' => $request->status,
                'position' => $request->position,
                'updated_by' => $loggedUserId,
            ]);
    
            // Return the updated subject data in the response
            return $this->sendResponse(['subject' => $subject], 'Subject updated successfully');
        } catch (\Exception $e) {
            // Log the error message for debugging
            \Log::error('Error updating subject: ' . $e->getMessage());
    
            // Return a general error response
            return $this->sendError('An error occurred while updating the subject', 500);
        }
    }
    

    /**
     * Remove the specified subject from storage.
     *
     */
    public function deleteSubjectDetails(Request $request, $subjectId)
    {
        $validator = Validator::make(
            array_merge($request->all(), ['subjectId' => $subjectId]),
            [
                'subjectId' => 'required',
            ]
        );
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $subject = Subject::find($subjectId);
            $subject->delete();
        }

        return $this->sendResponse([], 'Subject deleted successfully');
    }


    public function getSubjectResults(Request $request, $subjectId)
    {
        $resultService = new ResultService();

        $results = $resultService->getSubjectResults($subjectId, $request->term);

        return $this->sendResponse(['results' => $results], '');
    }
}
