<?php

namespace App\Http\Controllers\Api;

use App\Models\Faq;
use App\Models\Auth;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\TrainerCourse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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

    public function fetchFaqByCourseId(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'course_id' => 'required|exists:courses,id',
        // ]);
        Log::info("dssd",['couser_id', $request->course_id]);
    
        // if ($validator->fails()) {
        //     return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        // }
        // Fetch FAQs related to the course
        $faqs = Faq::where('course_id', $request->course_id)
                    ->select('question', 'answer')
                    ->get();
    
        if ($faqs->isEmpty()) {
            return $this->sendError('No FAQs found for the provided course.', [], 404);
        }
    
        return $this->sendResponse(['faqs' => $faqs], 'FAQs fetched successfully.');
    }
    
    

}
