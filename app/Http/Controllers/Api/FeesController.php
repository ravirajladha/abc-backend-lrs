<?php

namespace App\Http\Controllers\Api;

use App\Models\Fee;
use App\Models\Student;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;

class FeesController extends BaseController
{
    /**
     * Create a new fee.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeFeeDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class' => 'required|exists:classes,id',
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }
        if (Fee::where('class_id', $request->class)->exists()) {
            $validator = Validator::make($request->all(), [
                'class' => ['exists:classes,id', function ($attribute, $value, $fail) {
                    $fail('A fee already exists for this class, you can edit.');
                }]
            ]);

            return $this->sendValidationError($validator);
        }

        $fee = new Fee();
        $fee->class_id = $request->class;
        $fee->amount = $request->amount;

        if ($fee->save()) {
            return $this->sendResponse($fee, 'Fee created successfully.');
        } else {
            return $this->sendError('Failed to create fee.', [], 500);
        }
    }

        public function getFee()
        {
            $fee = Fee::first();
    
            return $this->sendResponse(['fee' => $fee], 'Fees fetched successfully.');
        }

    public function getFeesList()
    {
        $fees = DB::table('fees')
        ->join('classes', 'fees.class_id', '=', 'classes.id')
        ->select('fees.*', 'classes.name as class_name')
        ->get();

        return $this->sendResponse(['fees' => $fees], 'Fees fetched successfully.');
    }

    public function updateFee(Request $request)
    {
        Log::info(['fees' => $request->all()]);
        $request->validate([
            'amount' => 'required|numeric',
            'slashAmount' => 'required|numeric',
     
            'referralAmount' => 'required|numeric',
            'referrerAmount' => 'required|numeric',
            'benefits' => 'required|string',
            'description' => 'required|string',
        ]);

        $fee = Fee::first();
        if (!$fee) {
            $fee = new Fee();
        }

        $fee->amount = $request->amount;
        $fee->slash_amount = $request->slashAmount;
     
        $fee->referral_amount = $request->referralAmount;
        $fee->referrer_amount = $request->referrerAmount;
        $fee->benefits = $request->benefits;
        $fee->description = $request->description;

        $fee->save();

      
        if ($fee->save()) {
            return $this->sendResponse(['fee' => $fee], 'Fee updated successfully.');
        } else {
            return $this->sendError('Failed to update fee.', [], 500);
        }
    }

    /**
     * Update an existing fee.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateFeeDetails(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $fee = Fee::find($id);
        if (!$fee) {
            return $this->sendError('Fee not found.', [], 404);
        }

        $fee->amount = $request->has('amount') ? $request->amount : $fee->amount;

        if ($fee->save()) {
            return $this->sendResponse(['fee' => $fee], 'Fee updated successfully.');
        } else {
            return $this->sendError('Failed to update fee.', [], 500);
        }
    }

    public function getFeeDetailsById($id)
    {
        $fee = DB::table('fees')
        ->join('classes', 'fees.class_id', '=', 'classes.id')
        ->select('fees.*', 'classes.name as class_name')
        ->where('fees.id',$id)
        ->first();
        if (!$fee) {
            return $this->sendError('Fee not found.', [], 404);
        }

        return $this->sendResponse(['fee' => $fee], 'Fee fetched successfully.');
    }

    public function getFeeByClass($classId)
    {
        $fee = Fee::where('fees.class_id',$classId)
        ->value('amount');
        if (!$fee) {
            return $this->sendError('Fee not found.', [], 404);
        }

        return $this->sendResponse(['fee' => $fee], 'Fee fetched successfully.');
    }

    public function validateReferralName(Request $request)
    {
        \Log::info(['$request' => $request->all()]);
        
        // Validate the request
        $request->validate([
            'referral_code' => 'required|string|exists:students,student_unique_code',
        ]);
    
        // Get the current logged-in user's ID
        $currentStudentId = $this->getLoggedUserId();
        \Log::info(['currentStudentId' => $currentStudentId]);
    
        // Find the student with the provided referral code
        $student = Student::where('student_unique_code', $request->referral_code)->first();
        \Log::info(['$student' => $student, 'currentStudentId' => $currentStudentId]);
    
        // Check if the referral code belongs to the current logged-in user
        if ($student && $student->auth_id === $currentStudentId) {
            return $this->sendError('You cannot use your own unique code as a referral.', [], 400);
        }
    
        // If the student exists, return their name
        if ($student) {
            return $this->sendResponse(['referrer_name' => $student->name], 'Referral code validated successfully.');
        }
    
        // If no student is found, return an error
        return $this->sendError('Invalid referral code.', [], 400);
    }
    
    
    
}


// public function validateReferralName(Request $request)
// {
//     Log::info(['$request' => $request->all()]);
//     $request->validate([
//         'referral_code' => 'required|string|exists:students,student_unique_code',
//     ]);

//     $student = Student::where('student_unique_code', $request->referral_code)->first();

//     if ($student) {
//         return $this->sendResponse(['referrer_name' => $student->name], 'Referral code validated successfully.');
//     }else{
        
//     }

//     return $this->sendError('Invalid referral code.', [], 400);
// }

// thi should not validate , if the user uses its own referal code
