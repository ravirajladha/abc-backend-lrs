<?php

namespace App\Http\Controllers\Api;

use App\Models\ProjectReport;
use App\Models\ProjectReportModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;

class ProjectReportModuleController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @param  int  $projectReportId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjectReportModuleList($projectReportId)
    {
        $validator = Validator::make(['projectReportId' => $projectReportId], [
            'projectReportId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $projectReportModules = DB::table('project_report_modules')
                ->where('project_report_id', $projectReportId)
                ->get();

            return $this->sendResponse(['projectReportModules' => $projectReportModules]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  int  $projectReportId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeProjectReportModuleDetails($projectReportId, Request $request)
    {
        $validator = Validator::make(array_merge(['projectReportId' => $projectReportId], $request->all()), [
            'projectReportId' => 'required',
            'moduleTitles' => 'required|array',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $projectReport = ProjectReport::find($projectReportId);

            if (!$projectReport) {
                return $this->sendError('Project Report not found');
            }

            $moduleTitles = $request->moduleTitles;

            foreach ($moduleTitles as $index => $moduleTitle) {
                $projectReportModule = new ProjectReportModule();
                $projectReportModule->project_report_id = $projectReport->id;
                $projectReportModule->title = $moduleTitle;
                $projectReportModule->save();
            }

            return $this->sendResponse([], 'Project Report Modules added successfully');
        }
    }

    /**
     * Display the details of the specified resource.
     *
     * @param  int  $projectReportModuleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjectReportModuleDetails($projectReportModuleId)
    {
        $validator = Validator::make(['projectReportModuleId' => $projectReportModuleId], [
            'projectReportModuleId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $projectReportModule = ProjectReportModule::find($projectReportModuleId);
            if (!$projectReportModule) {
                return $this->sendError('Project Report Module not found');
            }
            return $this->sendResponse(['projectReportModule' => $projectReportModule]);
        }
    }
       /**
     * Update the specified section in storage.
     *
     * @param  int  $sectionId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProjectReportModuleDetails($moduleId, Request $request)
    {
        $validator = Validator::make(array_merge(['moduleId' => $moduleId], $request->all()), [
            'moduleId' => 'required',
            'moduleTitle' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $projectReportModule = ProjectReportModule::find($moduleId);

            if (!$projectReportModule) {
                return $this->sendError('Project Report Section not found');
            }

            $projectReportModule->title = $request->moduleTitle;
            $projectReportModule->save();

            return $this->sendResponse([], 'Project Report Section updated successfully');
        }
    }
}
