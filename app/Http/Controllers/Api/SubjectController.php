<?php

namespace App\Http\Controllers\Api;

use App\Models\Subject;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\Api\BaseController;
use App\Services\Admin\ResultService;

class SubjectController extends BaseController
{
    /**
     * Display a listing of the Subjects.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSubjectsList()
    {
        $subjects = Subject::orderBy('created_at')->get();
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
            $subject = new Subject();
            $subject->name = $request->subject_name;
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
            ]
        );

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $subject = Subject::find($subjectId);
            $subject->update([
                'name' => $request->subject_name,
                'status' => $request->status,
            ]);
        }

        return $this->sendResponse(['subject' => $subject], 'subject updated successfully');
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

        return $this->sendResponse([], 'subject deleted successfully');
    }


    public function getSubjectResults(Request $request, $subjectId)
    {
        $resultService = new ResultService();

        $results = $resultService->getClassResults($subjectId, $request->term);

        return $this->sendResponse(['results' => $results], '');
    }
}
