<?php

namespace App\Http\Controllers\Api;

use App\Models\Faq;
use App\Models\Auth;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\TrainerCourse;

class FaqController extends BaseController
{
    public function storeFaq(Request $request)
    {
        $loggedUserId = $this->getLoggedUserId();
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'question' => 'required|string',
            'answer' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        // Ensure the trainer is assigned to the course
        $trainerCourse = TrainerCourse::where('course_id', $request->course_id)
            ->where('trainer_id', $loggedUserId)
            ->firstOrFail();

        $faq = Faq::create([
            'course_id' => $request->course_id,
            'trainer_id' => $loggedUserId,
            'question' => $request->question,
            'answer' => $request->answer,
        ]);

        return $this->sendResponse(['faq' => $faq], 'FAQ created successfully.');
    }



}
