<?php

namespace App\Http\Controllers\Api;

use App\Models\RatingReview;
use App\Models\Auth;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RatingReviewController extends BaseController
{
    public function storeRatingReview(Request $request)
    {

        $loggedUserId = $this->getLoggedUserId();
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'rating' => 'nullable|integer|min:1|max:5', // Rating is optional but should be between 1-5
            'review' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $existingReview = RatingReview::where('course_id', $request->course_id)
        ->where('student_id', $loggedUserId)
        ->first();

        if ($existingReview) {
        return $this->sendError('You have already submitted a rating and review for this course.',[], 400);
        }
        // Store the rating and review
        $ratingReview = RatingReview::create([
            'course_id' => $request->course_id,
            'student_id' => $loggedUserId,
            'rating' => $request->rating,
            'review' => $request->review,
        ]);
        return $this->sendResponse(["ratingReview" => $ratingReview], 'Rating and Review updated successfully');
    }

    public function getCourseRatingsAndReviews($courseId)
    {
        // Validate if the course exists
        $course = Course::findOrFail($courseId);

        // Fetch average rating and count of ratings
        $ratingsSummary = RatingReview::where('course_id', $courseId)
            ->selectRaw('AVG(rating) as average_rating')
            ->selectRaw('COUNT(rating) as total_ratings')
            ->selectRaw('SUM(rating = 5) as five_star_count')
            ->selectRaw('SUM(rating = 4) as four_star_count')
            ->selectRaw('SUM(rating = 3) as three_star_count')
            ->selectRaw('SUM(rating = 2) as two_star_count')
            ->selectRaw('SUM(rating = 1) as one_star_count')
            ->first();

        $reviews = RatingReview::where('ratings_reviews.course_id', $courseId)
        ->whereNotNull('review')  // Only fetch reviews with text
        ->join('auth as students', 'ratings_reviews.student_id', '=', 'students.id')  // Join with the auth table for student
        ->leftJoin('auth as trainers', 'ratings_reviews.trainer_id', '=', 'trainers.id')  // Join with auth for trainer
        ->orderBy('ratings_reviews.created_at', 'desc')
        ->select(
            'ratings_reviews.id',
            'ratings_reviews.student_id',
            'students.username as student_name',  // Alias for student's username
            'ratings_reviews.trainer_id',
            'trainers.username as trainer_name',  // Alias for trainer's username
            'ratings_reviews.trainer_reply',
            'ratings_reviews.review',
            'ratings_reviews.rating',
            'ratings_reviews.created_at',
            'ratings_reviews.updated_at'
        )
        ->get();

        return $this->sendResponse([
            'ratings' => $ratingsSummary,
            'reviews' => $reviews
        ], 'Rating and Review fetched successfully.');

    }

    public function storeReviewReply(Request $request)
    {
        $loggedUserId = $this->getLoggedUserId(); // Assuming this method retrieves the logged-in trainer's ID

        $validator = Validator::make($request->all(), [
            'review_id' => 'required|exists:ratings_reviews,id',
            'reply' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        // Fetch the review the trainer is replying to
        $ratingReview = RatingReview::where('id', $request->review_id)
            ->whereNull('trainer_reply')  // Ensure there is no existing reply
            ->firstOrFail();

        // Update the review with the trainer's reply
        $ratingReview->trainer_id = $loggedUserId; // Assign trainer's ID
        $ratingReview->trainer_reply = $request->reply; // Store the reply
        $ratingReview->save();

        return $this->sendResponse(["ratingReview" => $ratingReview], 'Rating and Review updated successfully');
    }

}
