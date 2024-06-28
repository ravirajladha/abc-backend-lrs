<?php

namespace App\Http\Controllers\Api\Dropdown;

use App\Models\Subject;
use App\Models\TermTest;

use Illuminate\Support\Facades\Log;
use App\Http\Constants\SubjectTypeConstants;
use App\Http\Controllers\Api\BaseController;

// This controller which show the subjects on select of class, but only those subjects which are already not the part of any term_tests
class SubjectTestController extends BaseController
{
    public function __invoke($classId)
    {
        Log::info(['classId' => $classId]);
        // Fetch subjects for the given class ID
        $subjects = Subject::select('id', 'name')
            ->where('class_id', $classId)
            ->whereIn('subject_type', [SubjectTypeConstants::TYPE_DEFAULT_SUBJECT, SubjectTypeConstants::TYPE_SUB_SUBJECT])
            ->get()
            ->map(function ($subject) {
                $subject->name = ucfirst($subject->name);
                return $subject;
            });
    
        // Fetch subjects that already have a term test assigned
        $assignedSubjects = TermTest::select('subject_id')
            ->whereIn('subject_id', $subjects->pluck('id'))
            ->where('status', true)
            ->get()
            ->pluck('subject_id')
            ->toArray();
    
        // Add flag to subjects to indicate if they already have a test assigned
    $subjects = $subjects->map(function ($subject) use ($assignedSubjects) {
        $subject->isDisabled = in_array($subject->id, $assignedSubjects);
        return $subject;
    });
    Log::info(['subjects' => $subjects]);
        return $this->sendResponse(['subjects' => $subjects]);
    }
    

}
