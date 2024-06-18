<?php

namespace App\Http\Controllers\Api\Dropdown;

use App\Models\EbookSection;

use Illuminate\Http\Request;

use App\Http\Controllers\Api\BaseController;

class EbookSectionController extends BaseController
{
    public function __invoke(Request $request)
    {
        $ebook_sections = EbookSection::select('id', 'title')->where('ebook_module_id', $request->ebookModuleId)->get();
        return $this->sendResponse(['ebook_sections' => $ebook_sections]);
    }
}
