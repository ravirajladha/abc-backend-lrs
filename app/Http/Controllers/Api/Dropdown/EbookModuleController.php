<?php

namespace App\Http\Controllers\Api\Dropdown;

use App\Models\EbookModule;

use Illuminate\Http\Request;

use App\Http\Controllers\Api\BaseController;

class EbookModuleController extends BaseController
{
    public function __invoke(Request $request)
    {
        $ebook_modules = EbookModule::select('id', 'title')->where('ebook_id', $request->ebookId)->get();
        return $this->sendResponse(['ebook_modules' => $ebook_modules]);
    }
}
