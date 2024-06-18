<?php

namespace App\Http\Controllers\Api;

use App\Models\Classes;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\Api\BaseController;
use App\Services\Admin\ResultService;

class ClassesController extends BaseController
{
    /**
     * Display a listing of the classes.
     *
     * @return \Illuminate\Http\Response
     */
    public function getClassesList()
    {
        $classes = Classes::orderBy('position')->orderBy('created_at')->get();
        return $this->sendResponse(['classes' => $classes]);
    }
    

    /**
     * Store a newly created class in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function storeClassDetails(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'class_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('classes', 'name'),
            ],
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $class = new Classes;
            $class->name = $request->class_name;
            $class->save();
        }
        return $this->sendResponse([], 'Class added successfully');
    }

    /**
     * Display the specified class.
     *
     */
    public function getClassDetails($classId)
    {
        $validator = Validator::make(['class_id' => $classId], [
            'class_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $class = Classes::find($classId);
        }

        return $this->sendResponse(['class' => $class]);
    }

    /**
     * Update the specified class in storage.
     *
     */
    public function updateClassDetails(Request $request, $classId)
{
    $validator = Validator::make(
        array_merge($request->all(), ['classId' => $classId]),
        [
            'classId' => 'required|exists:classes,id',
            'class_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('classes', 'name')->ignore($classId),
            ],
            'status' => 'required|boolean',
            'position' => 'required|integer',
        ]
    );

    if ($validator->fails()) {
        return $this->sendValidationError($validator);
    } else {
        $class = Classes::find($classId);
        $class->update([
            'name' => $request->class_name,
            'status' => $request->status,
            'position' => $request->position,
        ]);
    }

    return $this->sendResponse(['class' => $class], 'Class updated successfully');
}


    /**
     * Remove the specified class from storage.
     *
     */
    public function deleteClassDetails(Request $request, $classId)
    {
        $validator = Validator::make(
            array_merge($request->all(), ['classId' => $classId]),
            [
                'classId' => 'required',
            ]
        );
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $class = Classes::find($classId);
            $class->delete();
        }

        return $this->sendResponse([], 'Class deleted successfully');
    }


    public function getClassResults(Request $request, $classId)
    {
        $resultService = new ResultService();

        $results = $resultService->getClassResults($classId, $request->term);

        return $this->sendResponse(['results' => $results], '');
    }
}
