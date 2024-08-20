<?php

namespace App\Http\Controllers\Api;

use App\Models\Chapter;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;
//changed
class ChapterController extends BaseController
{

    /**
     * Display a listing of the chapters by subject.
     *
     * @return object
     */
    public function getChapterListBySubject(Request $request, $subjectId, $courseId)
    {
        $userType = $request->attributes->get('type');
        
        // Log the user type and IDs for debugging
        Log::info('Fetching chapters for subject', [
            'userType' => $userType,
            'subjectId' => $subjectId,
            'courseId' => $courseId
        ]);
        
        if ($userType === 'admin' || $userType === 'internship_admin' || $userType === 'trainer') {
            $validator = Validator::make(['subjectId' => $subjectId, 'courseId' => $courseId], [
                'subjectId' => 'required',
                'courseId' => 'required',
            ]);
    
            if ($validator->fails()) {
                // Log validation errors
                Log::error('Validation failed for getChapterListBySubject', [
                    'errors' => $validator->errors()->toArray()
                ]);
                return $this->sendValidationError($validator);
            } else {
                try {
                    $course = DB::table('courses as cou')
                        ->where('cou.id', $courseId)
                        ->value('cou.name');
    
                    $chapters = DB::table('chapters as c')
                        ->select('c.*', 'sub.name as subject_name', 'cou.name as course_name')
                        ->leftJoin('subjects as sub', 'sub.id', 'c.subject_id')
                        ->leftJoin('courses as cou', 'cou.id', 'c.course_id')
                        ->where('c.subject_id', $subjectId)
                        ->where('c.course_id', $courseId)
                        ->get();
                    
                    // Log the fetched chapters and course name
                    Log::info('Chapters fetched successfully', [
                        'course' => $course,
                        'chapters' => $chapters->toArray()
                    ]);
                    
                    return $this->sendResponse(['chapters' => $chapters, 'course' => $course]);
                } catch (\Exception $e) {
                    // Log the exception if something goes wrong
                    Log::error('Error fetching chapters for getChapterListBySubject', [
                        'exception' => $e->getMessage()
                    ]);
                    return $this->sendError('An error occurred while fetching chapters.');
                }
            }
        } else {
            // Log unauthorized access attempt
            Log::warning('Unauthorized attempt to fetch chapters', [
                'userType' => $userType,
                'subjectId' => $subjectId,
                'courseId' => $courseId
            ]);
            return $this->sendAuthError("Not authorized to fetch Chapters list.");
        }
    }
    

    /**
     * Display a listing of the chapters by subject.
     *
     * @return object
     */
    public function getChapterListByCourse($courseId)
    {
        $validator = Validator::make(['courseId' => $courseId], [
            'courseId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $chapters = Chapter::where('course_id', $courseId)->get();
            return $this->sendResponse(['chapters' => $chapters]);
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */


     public function storeChapterDetails(Request $request)
     {
         $validator = Validator::make($request->all(), [
             'subject_id' => 'required',
             'course_id' => 'required',
             'chapter_name' => 'required',
             'chapter_name.*' => 'required|max:100',
             'chapter_description' => 'string',
             'chapter_image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
         ]);
     
         if ($validator->fails()) {
             // Log the validation errors
             Log::error('Validation failed:', $validator->errors()->toArray());
             return $this->sendValidationError($validator);
         } else {
             try {
                 $chapterNames = [];
     
                 if (is_string($request->chapter_name)) {
                     // If it's a single string, convert it to an array
                     $chapterNames = [$request->chapter_name];
                 } elseif (is_array($request->chapter_name)) {
                     // If it's already an array, use it as is
                     $chapterNames = $request->chapter_name;
                 } else {
                     // If it's neither, log an error
                     Log::error('Invalid chapter names format.', ['chapter_name' => $request->chapter_name]);
                     return $this->sendResponse([], 'Invalid chapter names format.');
                 }
     
                 // Now $chapterNames should be a valid array
                 if (!empty($chapterNames)) {
                     $chaptersCount = 0;
     
                     foreach ($chapterNames as $chapterName) {
                         $chaptersCount++;
     
                         // make the first chapter unlocked by default
                         $existingChaptersCount = Chapter::where('course_id', $request->course_id)->count();
                         $isFirstChapter = $existingChaptersCount == 0;
                         $loggedUserId = $this->getLoggedUserId();

                         $chapter = new Chapter();
                         $chapter->subject_id = $request->subject_id;
                         $chapter->course_id = $request->course_id;
                         $chapter->title = $chapterName;
                         $chapter->lock_status = $isFirstChapter ? 1 : 0;
                         $chapter->created_by = $loggedUserId;

                         $chapter->save();
                     }
                     return $this->sendResponse(['count' => $chaptersCount], 'Chapter added successfully');
                 } else {
                     Log::error('Chapter names are not provided or invalid.');
                     return $this->sendResponse([], 'Failed to add chapters');
                 }
             } catch (\Exception $e) {
                 // Log the exception
                 Log::error('Exception occurred while storing chapter details:', ['error' => $e->getMessage()]);
                 return $this->sendResponse([], 'An error occurred while adding the chapter.');
             }
         }
     }
     
     
    

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Chapter  $chapter
     * @return \Illuminate\Http\JsonResponse
     */
  

    public function getChapterDetails($chapterId)
    {
        // Log the incoming chapter ID
        Log::info('Fetching details for chapter ID:', ['chapterId' => $chapterId]);
    
        $validator = Validator::make(['chapterId' => $chapterId], [
            'chapterId' => 'required',
        ]);
        
        if ($validator->fails()) {
            // Log validation errors
            Log::error('Validation failed for chapter ID:', ['errors' => $validator->errors()]);
            return $this->sendValidationError($validator);
        } else {
            $contentsCount = 0;
            $studentsCount = 0;
    
            try {
                $chapter = DB::table('chapters as c')
                    ->select('c.title as chapter_name', 'subject.name as subject_name', 'c.description as chapter_description', 'c.image as chapter_image', 'c.subject_id', 'c.course_id', 's.name as course_name')
                    ->leftJoin('courses as s', 'c.course_id', 's.id')
                    ->leftJoin('subjects as subject', 'c.subject_id', 'subject.id')
                    ->where('c.id', $chapterId)
                    ->first();
    
                // Log the retrieved chapter details
                Log::info('Chapter details retrieved:', ['chapter' => $chapter]);
    
                if (!$chapter) {
                    // Log the case where the chapter is not found
                    Log::warning('Chapter not found:', ['chapterId' => $chapterId]);
                    return $this->sendResponse([], 'Chapter not found.');
                }
    
                $contents = DB::table('videos as v')
                    ->where('v.chapter_id', $chapterId)
                    ->get();
    
                // Log the retrieved contents
                Log::info('Contents retrieved for chapter:', ['chapterId' => $chapterId, 'contents' => $contents]);
    
                if ($contents) {
                    $contentsCount = count($contents);
                    $chapter->no_videos = $contentsCount;
                    // Log the count of videos
                    Log::info('Number of videos for chapter:', ['chapterId' => $chapterId, 'no_videos' => $contentsCount]);
                    // $chapter->no_students = $studentsCount;
                }
    
                // Log the final response before sending
                Log::info('Final response for chapter details:', ['chapter' => $chapter, 'contents' => $contents]);
                return $this->sendResponse(['chapter' => $chapter, 'contents' => $contents]);
            } catch (\Exception $e) {
                // Log any exceptions that occur
                Log::error('Error occurred while fetching chapter details:', ['chapterId' => $chapterId, 'error' => $e->getMessage()]);
                return $this->sendResponse([], 'An error occurred while fetching chapter details.');
            }
        }
    }
    
    

    /**
     * Update the specified chapter in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateChapterDetails(Request $request, $chapterId)
    {
        $validator = Validator::make(array_merge($request->all(), ['chapter_id' => $chapterId]), [
            'chapter_id' => 'required',
            'chapter_name' => 'required|string|max:100',
     
            // 'chapter_image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $chapter = Chapter::where('id', $chapterId)->first();

            if ($request->has('chapter_name')) {
                $chapter->title = $request->input('chapter_name');
            }

          

            // if ($request->hasFile('chapter_image')) {
            //     $extension = $request->file('chapter_image')->extension();
            //     $filename = Str::random(4) . time() . '.' . $extension;
            //     $chapter->image = $request->file('chapter_image')->move(('uploads/images/chapter'), $filename);
            // }
            $loggedUserId = $this->getLoggedUserId();
            $chapter->updated_by = $loggedUserId;

            $chapter->save();
            return $this->sendResponse(['chapter' => $chapter], 'Chapter updated successfully');
        }
        return $this->sendError('Failed to update chapter');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Chapter  $chapter
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteChapterDetails(Request $request, $chapterId)
    {
        $validator = Validator::make(['chapter_id' => $chapterId], [
            'chapter_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $chapter = Chapter::where('id', $chapterId)->first();
            $chapter->delete();
        }

        return $this->sendResponse([], 'Chapter deleted successfully');
    }

    public function updateChapterLockStatus(Request $request, $chapterId){
        $validator = Validator::make(['chapter_id' => $chapterId], [
            'chapter_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $chapter = Chapter::where('id', $chapterId)->first();
            $chapter->lock_status = $request->status;
            $chapter->save();
        }

        return $this->sendResponse(['chapter' => $chapter], 'Chapter status updated successfully');
    }
}
