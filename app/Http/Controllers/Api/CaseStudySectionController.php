<?php

namespace App\Http\Controllers\Api;

use App\Models\CaseStudyModule;
use App\Models\CaseStudySection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;

class CaseStudySectionController extends BaseController
{
    /**
     * Display a listing of the sections by module and Project Report.
     *
     * @param  int  $caseStudyId
     * @param  int  $moduleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCaseStudySectionList($caseStudyId, $moduleId)
    {
        $validator = Validator::make(['caseStudyId' => $caseStudyId, 'moduleId' => $moduleId], [
            'caseStudyId' => 'required',
            'moduleId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $caseStudySections = DB::table('case_study_sections')
                ->where('case_study_id', $caseStudyId)
                ->where('case_study_module_id', $moduleId)
                ->get();

            return $this->sendResponse(['caseStudySections' => $caseStudySections]);
        }
    }

    /**
     * Store newly created sections in storage.
     *
     * @param  int  $caseStudyId
     * @param  int  $moduleId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeProjectReportSectionDetails($caseStudyId, $moduleId, Request $request)
    {
        $validator = Validator::make(array_merge(['caseStudyId' => $caseStudyId, 'moduleId' => $moduleId], $request->all()), [
            'caseStudyId' => 'required',
            'moduleId' => 'required',
            'sectionTitles' => 'required|array',
            'sectionTitles.*' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $caseStudyModule = CaseStudyModule::find($moduleId);

            if (!$caseStudyModule) {
                return $this->sendError('Case Study Module not found');
            }

            $sectionTitles = $request->sectionTitles;

            foreach ($sectionTitles as $index => $sectionTitle) {
                $caseStudySection = new CaseStudySection();
                $caseStudySection->case_study_id = $caseStudyId;
                $caseStudySection->case_study_module_id = $caseStudyModule->id;
                $caseStudySection->title = $sectionTitle;
                $caseStudySection->save();
            }

            return $this->sendResponse([], 'Case Study Sections added successfully');
        }
    }

    /**
     * Fetch the details of the specified resource.
     *
     * @param  int  $caseStudySectionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCaseStudySectionDetails($caseStudySectionId)
    {
        $validator = Validator::make(['caseStudySectionId' => $caseStudySectionId], [
            'caseStudySectionId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $caseStudySection = CaseStudySection::find($caseStudySectionId);
            if (!$caseStudySection) {
                return $this->sendError('Case Study Section not found');
            }
            return $this->sendResponse(['caseStudySection' => $caseStudySection]);
        }
    }
    /**
     * Update the specified section in storage.
     *
     * @param  int  $sectionId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCaseStudySectionDetails($sectionId, Request $request)
    {
        $validator = Validator::make(array_merge(['sectionId' => $sectionId], $request->all()), [
            'sectionId' => 'required',
            'sectionTitle' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $caseStudySection = CaseStudySection::find($sectionId);

            if (!$caseStudySection) {
                return $this->sendError('Case Study Section not found');
            }

            $caseStudySection->title = $request->sectionTitle;
            $caseStudySection->save();

            return $this->sendResponse([], 'Case Study Section updated successfully');
        }
    }
}
