<?php

namespace App\Http\Controllers\Api;

use App\Models\Quote;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\Models\Auth as AuthModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Validator;

class QuoteController extends BaseController
{
    /**
     * Update teacher details
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    /**
     * Display a listing of the teachers.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuoteList(Request $request)
    {
        $userType = $request->attributes->get('type');
        if ($userType === 'admin') {
            // $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');

            $quotes = DB::table('quotes as q')
                ->select('q.id', 'q.quote')
                ->where('status', true)
                ->paginate(10);

            return $this->sendResponse(['quotes' => $quotes]);
        } else {
            return $this->sendAuthError("Not authorized to fetch quotes list.");
        }
    }

    /**
     * Display the specified teacher.
     *
     */
    public function getQuoteDetails($quoteId)
    {
        $res = [];

        Log::info('QuoteId' . $quoteId);
        $validator = Validator::make(['quoteId' => $quoteId], [
            'quoteId' => 'required',
        ]);
        Log::info('Validating' . $quoteId);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {

            $res = Quote::where('id', $quoteId)
                ->first();
            return $this->sendResponse(['quote' => $res]);
        }
    }


    /**
     * Store Quote
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeQuote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quote' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $quote = new Quote();
        $quote->quote = $request->input('quote');
        $quote->status = true; // assuming new quotes are active by default
        $quote->save();

        return $this->sendResponse(['quote' => $quote], 'Quote created successfully.');
    }

    /**
     * Store bulk quote
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkStoreQuote(Request $request)
    {
        // Assuming the request contains an array of quotes
        $quotes = $request->input('quotes');

    Log::info('Received quotes:', $request->all());
        // Validate that quotes is an array and each quote is a string with a max length of 500
        $validator = Validator::make($request->all(), [
            'quotes' => 'required|array',
            'quotes.*' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $createdQuotes = [];

        foreach ($quotes as $quoteText) {
            $quote = new Quote();
            $quote->quote = $quoteText;
            $quote->status = true; // assuming new quotes are active by default
            $quote->save();

            $createdQuotes[] = $quote;
        }

        return $this->sendResponse(['quotes' => $createdQuotes], 'Quotes created successfully.');
    }
    /**
     * Store or update teacher classes and subjects
     *
     * @param Request $request
     * @param int $teacherId
     * @return \Illuminate\Http\JsonResponse
     */

    public function updateQuote(Request $request, $quoteId)
    {
        $validator = Validator::make(array_merge($request->all(), ['quoteId' => $quoteId]), [
            'quoteId' => 'required|exists:quotes,id',
            'quote' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $quote = Quote::find($quoteId);
        $quote->quote = $request->input('quote');
        $quote->save();

        return $this->sendResponse(['quote' => $quote], 'Quote updated successfully.');
    }
    /**
     * Remove the specified teacher from storage.
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteQuote($quoteId)
    {
         // Log the incoming quote ID
    Log::info('Received QuoteId for deletion: ' . $quoteId);

    // Validate the quote ID
    $validator = Validator::make(['quoteId' => $quoteId], [
        'quoteId' => 'required|exists:quotes,id',
    ]);

    // Check if validation fails
    if ($validator->fails()) {
        Log::error('Validation failed for QuoteId: ' . $quoteId);
        return $this->sendValidationError($validator);
    }

    try {
        // Fetch the quote from the database
        $quote = Quote::find($quoteId);

        // Check if the quote was found
        if (!$quote) {
            Log::warning('Quote not found for QuoteId: ' . $quoteId);
            return $this->sendError('Quote not found.');
        }

        // Deactivate the quote
        $quote->status = false; // assuming status false means deactivated
        $quote->save();

        // Log successful deactivation
        Log::info('Quote deactivated successfully: ' . $quoteId);

        // Return the deactivated quote in the response
        return $this->sendResponse(['quote' => $quote], 'Quote deactivated successfully.');
    } catch (\Exception $e) {
        // Log any exception that occurs
        Log::error('Error deactivating QuoteId: ' . $quoteId . ' - ' . $e->getMessage());
        return $this->sendError('An error occurred while deactivating the quote.');
    }
    }
}
