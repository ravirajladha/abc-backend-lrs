<?php

namespace App\Http\Controllers\Api;

use App\Models\CaseStudySection;
use App\Models\CaseStudyElement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;

class CaseStudyElementController extends BaseController
{
      /**
     * Store and update elements in the Case study elements table
     *
     * @param  int  $caseStudyElementId - null for create and id value while updating
     * @return \Illuminate\Support\Collection|\Illuminate\Http\JsonResponse
     */
    public function storeOrUpdateElement(Request $request,$caseStudyElementId = null) {
        $validator = Validator::make(array_merge(['caseStudyElementId' => $caseStudyElementId], $request->all()), [
            'element_type_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {

            $caseStudyElement = CaseStudyElement::findOrNew($caseStudyElementId);
            $caseStudyElement->case_study_section_id = $request->section_id;
            $caseStudyElement->case_study_element_type_id = $request->element_type_id;

            // paragraph
            if ($request->element_type_id == 1) {
                $caseStudyElement->paragraph = $request->paragraph;
            }
            if ($request->element_type_id == 2 ) {
                $caseStudyElement->list_type = $request->list_type;
                $caseStudyElement->list_points = implode('#@#', $request->list_points);
            }
            if ($request->element_type_id == 3 ) {
                $caseStudyElement->list_type = $request->list_type;
                $caseStudyElement->list_points = implode('#@#', $request->list_points);
                $caseStudyElement->list_description = implode('#@#', $request->list_description);
            }
            if ($request->element_type_id == 4 ) {
                $caseStudyElement->appendices_heading = implode('#@#', $request->appendices_heading);
                $caseStudyElement->appendices_sub_heading  = implode('#@#', $request->appendices_sub_heading);
                $caseStudyElement->appendices_desc  = implode('#@#', $request->appendices_desc);
            }
            $caseStudyElement->save();

            return $this->sendResponse([], 'Case Study Element added successfully');
        }
    }
     /**
     * Remove the specified Case Study element from the database.
     *
     * @param  int  $caseStudyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteElement($caseStudyId)
    {
        $caseStudyElement = CaseStudyElement::find($caseStudyId);

        if (!$caseStudyElement) {
            return $this->sendError('Case Study Report Element not found');
        }

        $caseStudyElement->delete();

        return $this->sendResponse([], 'Case Study Element deleted successfully');
    }

     /**
     * Fetch the specified Case Study element from the database.
     *
     * @param  int  $caseStudyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getElementById($caseStudyId){
        $validator = Validator::make(['caseStudyId' => $caseStudyId], [
            'caseStudyId' => 'required|exists:case_study_elements,id',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $element = DB::table('case_study_elements')
                        ->where('id', '=', $caseStudyId)
                        ->first();
            return $this->sendResponse(['element' => $element]);
        }
    }
}
