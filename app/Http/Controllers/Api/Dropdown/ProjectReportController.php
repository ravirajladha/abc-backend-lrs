<?php

namespace App\Http\Controllers\Api\Dropdown;

use App\Models\ProjectReport;

use Illuminate\Http\Request;

use App\Http\Controllers\Api\BaseController;

class ProjectReportController extends BaseController
{
    public function __invoke(Request $request)
    {
        $projectReports = ProjectReport::select('id', 'title')->where('subject_id', $request->subjectId)->get();
        return $this->sendResponse(['projectReports' => $projectReports]);
    }
}
