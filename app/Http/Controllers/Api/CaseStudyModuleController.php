<?php

namespace App\Http\Controllers\Api;

use App\Models\CaseStudy;
use App\Models\CaseStudyModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;

class CaseStudyModuleController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @param  int  $caseStudyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCaseStudyModuleList($caseStudyId)
    {
        $validator = Validator::make(['caseStudyId' => $caseStudyId], [
            'caseStudyId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $caseStudyModules = DB::table('case_study_modules')
                ->where('case_study_id', $caseStudyId)
                ->get();

            return $this->sendResponse(['caseStudyModules' => $caseStudyModules]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  int  $caseStudyId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeCaseStudyModuleDetails($caseStudyId, Request $request)
    {
        $validator = Validator::make(array_merge(['caseStudyId' => $caseStudyId], $request->all()), [
            'caseStudyId' => 'required',
            'moduleTitles' => 'required|array',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $caseStudy = CaseStudy::find($caseStudyId);

            if (!$caseStudy) {
                return $this->sendError('Case Study not found');
            }

            $moduleTitles = $request->moduleTitles;

            foreach ($moduleTitles as $index => $moduleTitle) {
                $caseStudyModule = new CaseStudyModule();
                $caseStudyModule->case_study_id = $caseStudy->id;
                $caseStudyModule->title = $moduleTitle;
                $caseStudyModule->save();
            }

            return $this->sendResponse([], 'Case Study Modules added successfully');
        }
    }

      /**
     * Display the details of the specified resource.
     *
     * @param  int  $caseStudyModuleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCaseStudyModuleDetails($caseStudyModuleId)
    {
        $validator = Validator::make(['caseStudyModuleId' => $caseStudyModuleId], [
            'caseStudyModuleId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $caseStudyModule = CaseStudyModule::find($caseStudyModuleId);
            if (!$caseStudyModule) {
                return $this->sendError('Project Report Module not found');
            }
            return $this->sendResponse(['caseStudyModule' => $caseStudyModule]);
        }
    }
       /**
     * Update the specified section in storage.
     *
     * @param  int  $sectionId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCaseStudyModuleDetails($moduleId, Request $request)
    {
        $validator = Validator::make(array_merge(['moduleId' => $moduleId], $request->all()), [
            'moduleId' => 'required',
            'moduleTitle' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $caseStudyModule = CaseStudyModule::find($moduleId);

            if (!$caseStudyModule) {
                return $this->sendError('Case Study Module not found');
            }

            $caseStudyModule->title = $request->moduleTitle;
            $caseStudyModule->save();

            return $this->sendResponse([], 'Case Study Module updated successfully');
        }
    }
}
