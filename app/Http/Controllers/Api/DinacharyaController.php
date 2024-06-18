<?php

namespace App\Http\Controllers\Api;

use App\Models\Ebook;
use App\Models\School;
use App\Models\Student;
use App\Models\ParentModel;

use Illuminate\Support\Str;
use App\Models\StudentImage;
use App\Models\DinacharyaLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Hash;
use App\Http\Constants\AuthConstants;
use Illuminate\Support\Facades\Validator;
use App\Services\Student\DashboardService;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Quote;
use App\Models\Auth;
use App\Services\Student\DinacharyaService;
use Carbon\Carbon;

class DinacharyaController extends BaseController
{
    /**
     * Get all images for a student by student_auth_id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $studentId
     * @return \Illuminate\Http\Response
     */
    public function getDinacharyaLogs(Request $request)
    {
        $userType = $request->attributes->get('type');
        if ($userType === 'admin') {
            $schoolId = $request->query('schoolId');
            $classId = $request->query('classId');

            // Base query to retrieve data from dinacharya_logs
            $query = DB::table('dinacharya_logs as dl')
                ->select(
                    'dl.*',
                    's.id as student_id',
                    's.name as student_name',
                    'p.name as parent_name',
                    'sc.name as school_name',
                    'q.quote as quote_text',
                    'si.image_path as image_path',
                    'c.name as class_name',
                )
                ->leftJoin('students as s', 's.auth_id', '=', 'dl.student_id')
                ->leftJoin('classes as c', 's.class_id', '=', 'c.id')
                ->leftJoin('auth as a', 'a.id', '=', 's.auth_id')
                ->leftJoin('parents as p', 'p.auth_id', '=', 'dl.parent_id')
                ->leftJoin('schools as sc', 'sc.auth_id', '=', 'dl.school_id')
                ->leftJoin('quotes as q', 'q.id', '=', 'dl.quote_id')
                ->leftJoin('student_images as si', 'si.id', '=', 'dl.image_id');

            // Apply filters
            if ($schoolId) {
                $query->where('dl.school_id', $schoolId);
            }
            if ($classId) {
                $query->where('s.class_id', $classId);
            }

            // Paginate results
            $logs = $query->paginate(10);

            return $this->sendResponse(['students' => $logs]);
        } else {
            return $this->sendAuthError("Not authorized to fetch dinacharya logs.");
        }
    }

    /**
      * Remove the specified student from storage.
      *
      * @param  Request $request
      * @return \Illuminate\Http\JsonResponse
      */
    // public function deleteImage(Request $request, $imageId)
    // {
    //     $userType = $request->attributes->get('type');
    //     if ($userType == 'admin' || $userType == 'school') {
    //         $validator = Validator::make(['imageId' => $imageId], [
    //             'imageId' => 'required',
    //         ]);
    //         if ($validator->fails()) {
    //             return $this->sendValidationError($validator);
    //         } else {
    //             $image = StudentImage::where('id', $imageId);

    //             if ($image) {
    //                 $image->delete();

    //             } else {
    //                 return $this->sendError("Trying to delete a invalid image.");
    //             }
    //         }

    //         return $this->sendResponse([], 'Image deleted successfully');
    //     } else {
    //         return $this->sendAuthError("Not authorized delete image.");
    //     }
    // }


    public function sendDinacharyaMessages()
    {
        $students = Student::where('student_type', 0)->get();

        foreach ($students as $student) {
            $parentId = $student->parent_id;
            $parent_details = ParentModel::where('id', $parentId)->first();
            $student_type = $student->student_type;
            // Get a random image for the student
            $image = StudentImage::where('student_auth_id', $student->auth_id)->where('status', true)->inRandomOrder()->first();
            $imagePath = $image ? asset(env('PUBLIC_IMAGE_URL') . $image->image_path) : "https://atomstest.kods.app/images/abc_logo.jpg";
            // Get a random quote
            $quote = Quote::inRandomOrder()->where('status', true)->first();
            if ($parentId && !$student_type) {

                $parent = Auth::where('id', $parent_details->auth_id)->first();

                $today = Carbon::today()->toDateString();
                $dinacharyaLog = DinacharyaLog::where('student_id', $student->auth_id)
                    ->whereDate('created_at', $today)
                    ->first();

                if(!$dinacharyaLog) {

                    $dinacharyaService = new DinacharyaService();
                    $reponse = $dinacharyaService->sendWhatsappMessage($parent->phone_number, $imagePath, $quote->quote);


                    DinacharyaLog::create([
                        'student_id' => $student->auth_id,
                        'school_id' => $student->school_id,
                        'parent_id' => $parent_details->auth_id,
                        'image_id' => $image ? $image->id : null,
                        'quote_id' => $quote ? $quote->id : null,
                    ]);

                    // For testing, log the details
                    Log::info('WhatsApp message sent to parent', [
                        'student_id' => $student->auth_id,
                        'parent_id' => $parentId,
                        'image_id' => $image ? $image->id : null,
                        'quote_id' => $quote ? $quote->id : null,
                        'whatsapp_reponse' => $reponse,
                        'phone_number' => $parent->phone_number,
                        'imagePath' => $imagePath,
                        'quote' => $quote->quote,
                    ]);

                }

            }
        }
        return $this->sendResponse(['Message sent successfully']);
    }

}
