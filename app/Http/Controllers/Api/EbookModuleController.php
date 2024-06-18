<?php

namespace App\Http\Controllers\Api;

use App\Models\Ebook;
use App\Models\EbookModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class EbookModuleController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @param  int  $ebookId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEbookModuleList($ebookId)
    {
        $validator = Validator::make(['ebookId' => $ebookId], [
            'ebookId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $ebookModules = DB::table('ebook_modules')
                ->where('ebook_id', $ebookId)
                ->get();

            return $this->sendResponse(['ebookModules' => $ebookModules]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  int  $ebookId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeEbookModuleDetails($ebookId, Request $request)
    {
        $validator = Validator::make(array_merge(['ebookId' => $ebookId], $request->all()), [
            'ebookId' => 'required',
            'moduleTitles' => 'required|array',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $ebook = Ebook::find($ebookId);

            if (!$ebook) {
                return $this->sendError('Ebook not found');
            }

            $moduleTitles = $request->moduleTitles;

            foreach ($moduleTitles as $index => $moduleTitle) {
                $ebookModule = new EbookModule();
                $ebookModule->ebook_id = $ebook->id;
                $ebookModule->title = $moduleTitle;
                $ebookModule->save();
            }

            return $this->sendResponse([], 'Ebook Modules added successfully');
        }
    }

    /**
     * Display the details of the specified resource.
     *
     * @param  int  $ebookModuleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEbookModuleDetails($ebookModuleId)
    {
        $validator = Validator::make(['ebookModuleId' => $ebookModuleId], [
            'ebookModuleId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $ebookModule = EbookModule::find($ebookModuleId);
            if (!$ebookModule) {
                return $this->sendError('Ebook Module not found');
            }
            return $this->sendResponse(['ebookModule' => $ebookModule]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $ebookModuleId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateEbookModuleDetails($ebookModuleId, Request $request)
    {
        $validator = Validator::make(array_merge(['ebookModuleId' => $ebookModuleId], $request->all()), [
            'ebookModuleId' => 'required',
            'moduleTitle' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $ebookModule = EbookModule::find($ebookModuleId);

            if (!$ebookModule) {
                return $this->sendError('Ebook Module not found');
            }

            $ebookModule->title = $request->moduleTitle;
            $ebookModule->save();

            return $this->sendResponse([], 'Ebook Module updated successfully');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $ebookModuleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteEbookModuleDetails($ebookModuleId)
    {
        $ebookModule = EbookModule::find($ebookModuleId);

        if (!$ebookModule) {
            return $this->sendError('Ebook Module not found');
        }

        $ebookModule->delete();

        return $this->sendResponse([], 'Ebook Module deleted successfully');
    }
}
