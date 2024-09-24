<?php

namespace App\Http\Controllers\Api;

use App\Models\Ebook;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;

class EbookController extends BaseController
{
    /**
     * Display a listing of all ebooks.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEbookList()
    {
        $ebooks = DB::table('ebooks as e')
            ->select('e.id', 'e.title', 'e.description', 'course.name as course_name', 'e.image', 'e.course_id', 'e.subject_id', 's.name as subject_name', 'e.chapter_id')
            ->leftJoin('subjects as s', 'e.subject_id', 's.id')
            ->leftJoin('courses as course', 'e.course_id', 'course.id')
            ->get();

        return $this->sendResponse(['ebooks' => $ebooks]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeEbookDetails(Request $request)
    {
        // return $this->sendError($request->all());
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'course' => 'required|exists:courses,id',
            'subject' => 'required|exists:subjects,id',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {

            $ebook = new Ebook();

            $ebook->title = $request->title;
            $ebook->description = $request->description;
            $ebook->course_id = $request->course;
            $ebook->subject_id = $request->subject;
            // $ebook->chapter_id = $request->chapter;

            if (!empty($request->file('image'))) {
                $extension = $request->file('image')->extension();
                $filename = Str::random(4) . time() . '.' . $extension;
                $ebook->image = $request->file('image')->move(('uploads/images/ebook'), $filename);
            } else {
                $ebook->image = null;
            }

            $ebook->save();

            return $this->sendResponse([], 'Ebook created successfully');
        }
    }

    /**
     * Display the details of the specified resource.
     *
     * @param  int  $ebookId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEbookDetails($ebookId)
    {
        $validator = Validator::make(['ebookId' => $ebookId], [
            'ebookId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $ebook = DB::table('ebooks as e')
                ->select('e.title as ebook_title', 'e.description as ebook_description', 'course.name as course_name', 'e.image as ebook_image', 'e.course_id', 'e.subject_id', 's.name as subject_name')
                ->leftJoin('subjects as s', 'e.subject_id', 's.id')
                ->leftJoin('courses as course', 'e.course_id', 'course.id')
                ->where('e.id', $ebookId)
                ->first();

            return $this->sendResponse(['ebook' => $ebook]);
        }
    }

    /**
     * Update the specified ebook in the database.
     *
     * @param  int  $ebookId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateEbookDetails($ebookId, Request $request)
    {
        $validator = Validator::make(array_merge(['ebookId' => $ebookId], $request->all()), [
            'ebookId' => 'required|exists:ebooks,id',
            'title' => 'required',
            'description' => 'required',
            'course' => 'required|exists:courses,id',
            'subject' => 'required|exists:subjects,id',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',

        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $ebook = Ebook::find($ebookId);

            if (!$ebook) {
                return $this->sendError('Ebook not found');
            }

            $ebook->title = $request->title;
            $ebook->description = $request->description;
            $ebook->course_id = $request->course;
            $ebook->subject_id = $request->subject;

            if (!empty($request->file('image'))) {
                $extension = $request->file('image')->extension();
                $filename = Str::random(4) . time() . '.' . $extension;
                $ebook->image = $request->file('image')->move(('uploads/images/ebook'), $filename);
            }

            $ebook->save();

            return $this->sendResponse([], 'Ebook updated successfully');
        }
    }

    /**
     * Remove the specified ebook from the database.
     *
     * @param  int  $ebookId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteEbookDetails($ebookId)
    {
        $ebook = Ebook::find($ebookId);

        if (!$ebook) {
            return $this->sendError('Ebook not found');
        }

        $ebook->delete();

        return $this->sendResponse([], 'Ebook deleted successfully');
    }

    /**
     * Fetch ebook from the database with related modules, sections, elements.
     *
     * @param  int  $ebookId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEbook($ebookId)
    {
        $validator = Validator::make(['ebookId' => $ebookId], [
            'ebookId' => 'required|exists:ebooks,id',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $ebook = Ebook::with([
                    'modules:id,ebook_id,title',
                    'modules.sections:id,ebook_module_id,title',
                    'modules.sections.elements', // Add other element fields here
                ])
                ->select('ebooks.id', 'ebooks.title', 'ebooks.image')
                ->where('ebooks.id',$ebookId)
                ->first();
                  // If no readable course found, fetch ebook details directly

            return $this->sendResponse(['ebook' => $ebook]);
        }
    }

}
