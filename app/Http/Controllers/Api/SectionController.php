<?php

namespace App\Http\Controllers\Api;

use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends BaseController
{
    /**
     * Display a listing of the sections.
     *
     */
    public function getSectionList()
    {
        $sections = Section::get();
        return $this->sendResponse(['sections' => $sections]);
    }

}
