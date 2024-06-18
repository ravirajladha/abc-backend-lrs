<?php

namespace App\Http\Controllers\Api\Dropdown;

use App\Models\Ebook;

use Illuminate\Http\Request;

use App\Http\Controllers\Api\BaseController;

class EbookController extends BaseController
{
    public function __invoke(Request $request)
    {
        $ebooks = Ebook::select('id', 'title')->where('subject_id', $request->subjectId)->get();
        return $this->sendResponse(['ebooks' => $ebooks]);
    }
}
