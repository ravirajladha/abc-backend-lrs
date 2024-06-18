<?php

namespace App\Http\Controllers\Api;

use App\Models\ProjectReportModule;
use App\Models\ProjectReportSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;

class ProjectReportSectionController extends BaseController
{
    /**
     * Display a listing of the sections by module and Project Report.
     *
     * @param  int  $projectReportId
     * @param  int  $moduleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjectReportSectionList($projectReportId, $moduleId)
    {
        $validator = Validator::make(['projectReportId' => $projectReportId, 'moduleId' => $moduleId], [
            'projectReportId' => 'required',
            'moduleId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $projectReportSections = DB::table('project_report_sections')
                ->where('project_report_id', $projectReportId)
                ->where('project_report_module_id', $moduleId)
                ->get();

            return $this->sendResponse(['projectReportSections' => $projectReportSections]);
        }
    }

    /**
     * Store newly created sections in storage.
     *
     * @param  int  $projectReportId
     * @param  int  $moduleId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeProjectReportSectionDetails($projectReportId, $moduleId, Request $request)
    {
        $validator = Validator::make(array_merge(['projectReportId' => $projectReportId, 'moduleId' => $moduleId], $request->all()), [
            'projectReportId' => 'required',
            'moduleId' => 'required',
            'sectionTitles' => 'required|array',
            'sectionTitles.*' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $projectReportModule = ProjectReportModule::find($moduleId);

            if (!$projectReportModule) {
                return $this->sendError('Project Report Module not found');
            }

            $sectionTitles = $request->sectionTitles;

            foreach ($sectionTitles as $index => $sectionTitle) {
                $projectReportSection = new ProjectReportSection();
                $projectReportSection->project_report_id = $projectReportId;
                $projectReportSection->project_report_module_id = $projectReportModule->id;
                $projectReportSection->title = $sectionTitle;
                $projectReportSection->save();
            }

            return $this->sendResponse([], 'Project Report Sections added successfully');
        }
    }
    /**
     * Fetch the details of the specified resource.
     *
     * @param  int  $projectReportSectionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjectReportSectionDetails($projectReportSectionId)
    {
        $validator = Validator::make(['projectReportSectionId' => $projectReportSectionId], [
            'projectReportSectionId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $projectReportSection = ProjectReportSection::find($projectReportSectionId);
            if (!$projectReportSection) {
                return $this->sendError('Case Study Section not found');
            }
            return $this->sendResponse(['projectReportSection' => $projectReportSection]);
        }
    }
    /**
     * Update the specified section in storage.
     *
     * @param  int  $sectionId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProjectReportSectionDetails($sectionId, Request $request)
    {
        $validator = Validator::make(array_merge(['sectionId' => $sectionId], $request->all()), [
            'sectionId' => 'required',
            'sectionTitle' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $projectReportSection = ProjectReportSection::find($sectionId);

            if (!$projectReportSection) {
                return $this->sendError('Project Report Section not found');
            }

            $projectReportSection->title = $request->sectionTitle;
            $projectReportSection->save();

            return $this->sendResponse([], 'Project Report Section updated successfully');
        }
    }
}
