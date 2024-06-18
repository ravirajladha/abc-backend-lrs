<?php

namespace App\Http\Controllers\Api;

use App\Models\Ebook;
use App\Models\EbookModule;
use App\Models\EbookSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class EbookSectionController extends BaseController
{
    /**
     * Display a listing of the sections by module and ebook.
     *
     * @param  int  $ebookId
     * @param  int  $moduleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEbookSectionList($ebookId, $moduleId)
    {
        $validator = Validator::make(['ebookId' => $ebookId, 'moduleId' => $moduleId], [
            'ebookId' => 'required',
            'moduleId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $ebookSections = DB::table('ebook_sections')
                ->where('ebook_id', $ebookId)
                ->where('ebook_module_id', $moduleId)
                ->get();

            return $this->sendResponse(['ebookSections' => $ebookSections]);
        }
    }

    /**
     * Store newly created sections in storage.
     *
     * @param  int  $ebookId
     * @param  int  $moduleId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeEbookSectionDetails($ebookId, $moduleId, Request $request)
    {
        $validator = Validator::make(array_merge(['ebookId' => $ebookId, 'moduleId' => $moduleId], $request->all()), [
            'ebookId' => 'required',
            'moduleId' => 'required',
            'sectionTitles' => 'required|array',
            'sectionTitles.*' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $ebookModule = EbookModule::find($moduleId);

            if (!$ebookModule) {
                return $this->sendError('Ebook Module not found');
            }

            $sectionTitles = $request->sectionTitles;

            foreach ($sectionTitles as $index => $sectionTitle) {
                $ebookSection = new EbookSection();
                $ebookSection->ebook_id = $ebookId;
                $ebookSection->ebook_module_id = $ebookModule->id;
                $ebookSection->title = $sectionTitle;
                $ebookSection->save();
            }

            return $this->sendResponse([], 'Ebook Sections added successfully');
        }
    }

    /**
     * Get the specified section by ID.
     *
     * @param  int  $sectionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEbookSectionById($sectionId)
    {
        $ebookSection = EbookSection::with('ebookModule', 'ebook')->find($sectionId);

        if (!$ebookSection) {
            return $this->sendError('Ebook Section not found');
        }

        return $this->sendResponse(['ebookSection' => $ebookSection]);
    }

    /**
     * Display the details of the specified resource.
     *
     * @param  int  $ebookSectionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEbookSectionDetails($ebookSectionId)
    {
        $validator = Validator::make(['ebookSectionId' => $ebookSectionId], [
            'ebookSectionId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $ebookSection = EbookSection::find($ebookSectionId);
            if (!$ebookSection) {
                return $this->sendError('Ebook Section not found');
            }
            return $this->sendResponse(['ebookSection' => $ebookSection]);
        }
    }
    /**
     * Update the specified section in storage.
     *
     * @param  int  $sectionId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateEbookSectionDetails($sectionId, Request $request)
    {
        $validator = Validator::make(array_merge(['sectionId' => $sectionId], $request->all()), [
            'sectionId' => 'required',
            'sectionTitle' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $ebookSection = EbookSection::find($sectionId);

            if (!$ebookSection) {
                return $this->sendError('Ebook Section not found');
            }

            $ebookSection->title = $request->sectionTitle;
            $ebookSection->save();

            return $this->sendResponse([], 'Ebook Section updated successfully');
        }
    }

    /**
     * Remove the specified section from storage.
     *
     * @param  int  $sectionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteEbookSectionDetails($sectionId)
    {
        $ebookSection = EbookSection::find($sectionId);

        if (!$ebookSection) {
            return $this->sendError('Ebook Section not found');
        }

        $ebookSection->delete();

        return $this->sendResponse([], 'Ebook Section deleted successfully');
    }
}
