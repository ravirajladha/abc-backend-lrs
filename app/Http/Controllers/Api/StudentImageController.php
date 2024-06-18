<?php

namespace App\Http\Controllers\Api;

use App\Models\Ebook;
use App\Models\School;
use App\Models\Student;
use App\Models\CaseStudy;
use App\Models\ParentModel;

use Illuminate\Support\Str;
use App\Models\StudentImage;
use App\Models\DinacharyaLog;
use Illuminate\Http\Request;
use App\Models\ProjectReport;
use App\Models\Auth as AuthModel;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Hash;
use App\Http\Constants\AuthConstants;
use Illuminate\Support\Facades\Validator;
use App\Services\Student\DashboardService;
use Symfony\Component\HttpFoundation\Response;

class StudentImageController extends BaseController
{
    /**
     * Fetch dashboard items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeStudentImages(Request $request){
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        // $loggedUser = $this->getLoggerUser();
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $student = Student::find($request->student_id);
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }
    
        if ($request->hasfile('images')) {
            foreach ($request->file('images') as $file) {
                // Correctly handling file processing inside the loop
                $extension = $file->extension(); // get extension directly from the file object
                $filename = Str::random(4) . time() . '.' . $extension;
                $path = $file->move(('uploads/images/student/student_images'), $filename);  // move the file to the desired directory


                // Create a new StudentImage record
                StudentImage::create([
                    'student_auth_id' => $student->auth_id,
                    'student_id' => $student->id,
                    'image_path' => $path,
                    'created_by' => 1,  // Assuming you're using Laravel's authentication to identify the user
                    'status' => true,  // Assuming default status is 'active'
                ]);
            }
            return response()->json(['message' => 'Images uploaded successfully']);
        }
    
        return response()->json(['message' => 'No images were provided']);
    }


    /**
     * Get all images for a student by student_auth_id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $studentId
     * @return \Illuminate\Http\Response
     */
   public function getStudentImages(Request $request, $studentId)
{
    if (empty($studentId)) {
        return $this->sendError('No student ID provided', [], 400); // Assuming sendError is similar to sendResponse for error handling.
    }

    $images = StudentImage::where('student_auth_id', $studentId)
                          ->where('status', true) // Assuming you want to filter active images only
                          ->get();

    if ($images->isEmpty()) {
        // return $this->sendError('No images found for this student', [], 404);
        return $this->sendResponse(['images' => []], 'No images found for this student');
    }

    return $this->sendResponse(['images' => $images], 'Images retrieved successfully');
}
   /**
     * Remove the specified student from storage.
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteImage(Request $request, $imageId)
    {
        $userType = $request->attributes->get('type');
        if ($userType == 'admin' || $userType == 'school') {
            $validator = Validator::make(['imageId' => $imageId], [
                'imageId' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->sendValidationError($validator);
            } else {
                $image = StudentImage::find($imageId);
                
                if ($image) {
                    try {
                        $image->status = 0; // Set status to 0 (inactive/deleted)
                        $image->save();
                        return $this->sendResponse([], 'Image status updated to deleted successfully');
                    } catch (\Illuminate\Database\QueryException $e) {
                        return $this->sendError("An error occurred while updating the image status.");
                    }
                } else {
                    return $this->sendError("Trying to delete an invalid image.");
                }
            }
        } else {
            return $this->sendAuthError("Not authorized to delete image.");
        }
    }
    


}
