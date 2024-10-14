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
            'created_by' => $loggedUserId,
            'question' => $request->question,
            'answer' => $request->answer,
        ]);

        return $this->sendResponse(['faq' => $faq], 'FAQ created successfully.');
    }

    public function getCourseFaqs($courseId)
    {
        // Validate if the course exists
        $course = Course::findOrFail($courseId);

        $faqs = Faq::where('course_id', $courseId)
            ->select(
                'faqs.id',
                'faqs.question',
                'faqs.answer',
                'faqs.created_at',
            )
            ->orderBy('faqs.created_at', 'desc')
            ->get();

        if ($faqs->isEmpty()) {
            return $this->sendResponse(['faqs' => $faqs], 'No FAQs found for this course.');
        }

        return $this->sendResponse(['faqs' => $faqs], 'FAQs fetched successfully.');
    }

    public function editFaq(Request $request, $faqId)
    {
        $loggedUserId = $this->getLoggedUserId();

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'question' => 'required|string',
            'answer' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        // Find the FAQ and ensure it belongs to the trainer
        $faq = Faq::where('id', $faqId)
            ->where('created_by', $loggedUserId)
            ->first();

        if (!$faq) {
            return $this->sendError('FAQ not found or you do not have permission to edit it.', [], 404);
        }

        // Update the FAQ
        $faq->update([
            'question' => $request->question,
            'answer' => $request->answer,
        ]);

        return $this->sendResponse($faq, 'FAQ updated successfully.');
    }

    public function deleteFaq($faqId)
    {
        $loggedUserId = $this->getLoggedUserId();

        // Find the FAQ and ensure it belongs to the trainer
        $faq = Faq::where('id', $faqId)
            ->where('created_by', $loggedUserId)
            ->first();

        if (!$faq) {
            return $this->sendError('FAQ not found or you do not have permission to delete it.', [], 404);
        }

        // Delete the FAQ
        $faq->delete();

        return $this->sendResponse([], 'FAQ deleted successfully.');
    }
    public function getFaqById($faqId)
    {
        $loggedUserId = $this->getLoggedUserId();

        $faq = Faq::where('id', $faqId)
            ->first();

        if (!$faq) {
            return $this->sendError('FAQ not found.', [], 404);
        }

        return $this->sendResponse(['faq' => $faq], 'FAQ fetched successfully.');
    }


}
