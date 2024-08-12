<?php

namespace App\Http\Controllers\Api;

use App\Models\CaseStudy;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;

class CaseStudyController extends BaseController
{
    /**
     * Display a listing of all Case Study.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCaseStudyList()
    {
        $caseStudies = DB::table('case_studies as c')
            ->select('c.id', 'c.title', 'c.description', 'subject.name as subject_name', 'c.image', 'c.subject_id', 'c.course_id', 'cou.name as course_name', 'c.chapter_id')
            ->leftJoin('courses as cou', 'c.subject_id', 'cou.id')
            ->leftJoin('subjects as subject', 'c.subject_id', 'subject.id')
            ->get();

        return $this->sendResponse(['caseStudies' => $caseStudies]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeCaseStudyDetails(Request $request)
    {
        // return $this->sendError($request->all());
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'courses' => 'required|exists:courses,id',
            'subject' => 'required|exists:subjects,id',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {

            $caseStudy = new CaseStudy();

            $caseStudy->title = $request->title;
            $caseStudy->description = $request->description;
            $caseStudy->course_id = $request->course;
            $caseStudy->subject_id = $request->subject;

            if (!empty($request->file('image'))) {
                $extension = $request->file('image')->extension();
                $filename = Str::random(4) . time() . '.' . $extension;
                $caseStudy->image = $request->file('image')->move(('uploads/images/case-study'), $filename);
            } else {
                $caseStudy->image = null;
            }

            $caseStudy->save();

            return $this->sendResponse([], 'Case Study created successfully');
        }
    }

    public function getCaseStudy($caseStudyId)
    {
        $validator = Validator::make(['caseStudyId' => $caseStudyId], [
            'caseStudyId' => 'required|exists:case_studies,id',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $caseStudy = CaseStudy::with([
                'modules:id,case_study_id,title',
                'modules.sections:id,case_study_module_id,title',
                'modules.sections.elements', // Add other element fields here
            ])
                ->select('id', 'title', 'image')
                ->find($caseStudyId);

            return $this->sendResponse(['caseStudy' => $caseStudy]);
        }
    }

      /**
     * Update the specified Project report in the database.
     *
     * @param  int  $caseStudyId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCaseStudyDetails($caseStudyId, Request $request)
    {
        $validator = Validator::make(array_merge(['caseStudyId' => $caseStudyId], $request->all()), [
            'caseStudyId' => 'required|exists:case_studies,id',
            'title' => 'required',
            'description' => 'required',
            'course' => 'required|exists:courses,id',
            'subject' => 'required|exists:subjects,id',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',

        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $caseStudy = CaseStudy::find($caseStudyId);

            if (!$caseStudy) {
                return $this->sendError('Case Study not found');
            }

            $caseStudy->title = $request->title;
            $caseStudy->description = $request->description;
            $caseStudy->course_id = $request->course;
            $caseStudy->subject_id = $request->subject;

            if (!empty($request->file('image'))) {
                $extension = $request->file('image')->extension();
                $filename = Str::random(4) . time() . '.' . $extension;
                $caseStudy->image = $request->file('image')->move(('uploads/images/case-study'), $filename);
            }

            $caseStudy->save();

            return $this->sendResponse([], 'Case study updated successfully');
        }
    }
    /**
     * Display the details of the specified resource.
     *
     * @param  int  $caseStudyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCaseStudyDetails($caseStudyId)
    {
        $validator = Validator::make(['caseStudyId' => $caseStudyId], [
            'caseStudyId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $caseStudy = DB::table('case_studies as e')
                ->select('e.title as case_study_title', 'e.description as case_study_description', 'course.name as course_name', 'e.image as case_study_image', 'e.course_id', 'e.subject_id', 's.name as subject_name')
                ->leftJoin('subjects as s', 'e.subject_id', 's.id')
                ->leftJoin('courses as coures', 'e.course_id', 'course.id')
                ->where('e.id', $caseStudyId)
                ->first();

            return $this->sendResponse(['caseStudy' => $caseStudy]);
        }
    }

      /**
     * Remove the specified Project report from the database.
     *
     * @param  int  $caseStudyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCaseStudyDetails($caseStudyId)
    {
        $caseStudy = CaseStudy::find($caseStudyId);

        if (!$caseStudy) {
            return $this->sendError('Case Study not found');
        }

        $caseStudy->delete();

        return $this->sendResponse([], 'Case Study deleted successfully');
    }
}
