<?php

namespace App\Http\Controllers\Api;

use App\Models\ProjectReportElement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;

class ProjectReportElementController extends BaseController
{
    /**
     * Store and update elements in the project report elements table
     *
     * @param  int  $projectReportElementId - null for create and id value while updating
     * @return \Illuminate\Support\Collection|\Illuminate\Http\JsonResponse
     */
    public function storeOrUpdateElement(Request $request,$projectReportElementId = null) {
        $validator = Validator::make(array_merge(['projectReportElementId' => $projectReportElementId], $request->all()), [
            'element_type_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {

            $projectReportElement = ProjectReportElement::findOrNew($projectReportElementId);
            $projectReportElement->project_report_section_id = $request->section_id;
            $projectReportElement->project_report_element_type_id = $request->element_type_id;

            // paragraph
            if ($request->element_type_id == 1) {
                $projectReportElement->paragraph = $request->paragraph;
            }
            $projectReportElement->save();

            return $this->sendResponse([], 'Project Report Element added successfully');
        }
    }
     /**
     * Remove the specified Project Report element from the database.
     *
     * @param  int  $projectReportElementId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteElement($projectReportElementId)
    {
        $projectReportElement = ProjectReportElement::find($projectReportElementId);

        if (!$projectReportElement) {
            return $this->sendError('Project Report Element not found');
        }

        $projectReportElement->delete();

        return $this->sendResponse([], 'Project Report Element deleted successfully');
    }

     /**
     * Fetch the specified Project Report element from the database.
     *
     * @param  int  $projectReportElementId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getElementById($projectReportElementId){
        $validator = Validator::make(['projectReportElementId' => $projectReportElementId], [
            'projectReportElementId' => 'required|exists:project_report_elements,id',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $element = DB::table('project_report_elements')
                        ->where('id', '=', $projectReportElementId)
                        ->first();
            return $this->sendResponse(['element' => $element]);
        }
    }
}

