<?php

namespace App\Http\Controllers\Api\Dropdown;

use App\Models\Subject;

use App\Http\Controllers\Api\BaseController;
use App\Http\Constants\SubjectTypeConstants;

class SubjectController extends BaseController
{
    public function __invoke($classId)
    {
        $subjects = Subject::select('id', 'name')->where('class_id', $classId)->whereIn('subject_type', [SubjectTypeConstants::TYPE_DEFAULT_SUBJECT,SubjectTypeConstants::TYPE_SUB_SUBJECT])->get()->map(function ($subject) {
            $subject->name = ucfirst($subject->name);
            return $subject;
        });
        return $this->sendResponse(['subjects' => $subjects]);
    }
}
