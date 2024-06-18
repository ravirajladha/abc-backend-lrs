<?php

namespace App\Http\Controllers\Api;

use App\Models\EbookElement;
use App\Models\EbookSection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EbookElementController extends BaseController
{


    /**
     * Get elements by Ebook Section ID.
     *
     * @param  int  $ebookSectionId
     * @return \Illuminate\Support\Collection|\Illuminate\Http\JsonResponse
     */
    public function getElementsByEbookSectionId($ebookSectionId)
    {
        $validator = Validator::make(['ebookSectionId' => $ebookSectionId], [
            'ebookSectionId' => 'required|exists:ebook_sections,id',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $elements = DB::table('ebook_elements')
                ->join('ebook_sections', 'ebook_elements.ebook_section_id', '=', 'ebook_sections.id')
                ->join('ebook_modules', 'ebook_sections.ebook_module_id', '=', 'ebook_modules.id')
                ->join('ebooks', 'ebook_modules.ebook_id', '=', 'ebooks.id')
                ->select('ebook_elements.*')
                ->where('ebook_elements.ebook_section_id', $ebookSectionId)
                ->get();

            return $elements;
        }
    }

    public function getElementTypeList(){
        $elementTypes = DB::table('ebook_element_types')
                        ->get();
        return $this->sendResponse(['elementTypes' => $elementTypes]);

    }

    public function getElementById($ebookElementId){
        $validator = Validator::make(['ebookElementId' => $ebookElementId], [
            'ebookElementId' => 'required|exists:ebook_elements,id',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $element = DB::table('ebook_elements')
                        ->where('id', '=', $ebookElementId)
                        ->first();
            return $this->sendResponse(['element' => $element]);
        }
    }

    /**
     * Store and update elements in the ebook elements table
     *
     * @param  int  $ebookElementId - null for create and id value while updating
     * @return \Illuminate\Support\Collection|\Illuminate\Http\JsonResponse
     */
    public function storeOrUpdateElement(Request $request,$ebookElementId = null) {
        $validator = Validator::make(array_merge(['ebookElementId' => $ebookElementId], $request->all()), [
            'element_type_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {

            $ebookElement = EbookElement::findOrNew($ebookElementId);
            $ebookElement->ebook_section_id = $request->section_id;
            $ebookElement->ebook_element_type_id = $request->element_type_id;

            // heading and paragraph
            if ($request->element_type_id == 1) {
                $ebookElement->heading_type = $request->heading_type;
                $ebookElement->heading = $request->heading;
            }
            if ($request->element_type_id == 2 || $request->element_type_id == 13) {
                $ebookElement->paragraph = $request->paragraph;
            }
            // image
            if (!empty($request->file('image'))) {
                $extension1 = $request->file('image')->extension();
                $filename = Str::random(4) . time() . '.' . $extension1;
                $ebookElement->image = $request->file('image')->move(('uploads/images/ebook'), $filename);
            } else {
                $ebookElement->image = null;
            }
            // image-2-option
            if ($request->element_type_id == 4) {
                $ebookElement->image_type = $request->image_type;
                if ($request->image_heading_1) {
                    $ebookElement->image_heading_1 = $request->image_heading_1;
                }
                $ebookElement->image_text_1 = $request->image_text_1;
                $ebookElement->image_desc_1 = $request->image_desc_1;
                $ebookElement->image_text_2 = $request->image_text_2;
                $ebookElement->image_desc_2 = $request->image_desc_2;
            }
            // image-3-option
            if ($request->element_type_id == 5) {
                $ebookElement->image_type = $request->image_type;
                $ebookElement->image_heading_1 = $request->image_heading_1;
                $ebookElement->image_subheading_1 = $request->image_subheading_1;
                if ($request->image_subtext_1) {
                    $ebookElement->image_subtext_1 = $request->image_subtext_1;
                }
                if ($request->image_subtext_2) {
                    $ebookElement->image_subtext_2 = $request->image_subtext_2;
                }
                if ($request->image_subtext_3) {
                    $ebookElement->image_subtext_3 = $request->image_subtext_3;
                }
                $ebookElement->image_text_1 = $request->image_text_1;
                $ebookElement->image_desc_1 = $request->image_desc_1;
                $ebookElement->image_text_2 = $request->image_text_2;
                $ebookElement->image_desc_2 = $request->image_desc_2;
                $ebookElement->image_text_3 = $request->image_text_3;
                $ebookElement->image_desc_3 = $request->image_desc_3;
            }
            // image-4-option
            if ($request->element_type_id == 6) {
                $ebookElement->image_type = $request->image_type;
                $ebookElement->image_heading_1 = $request->image_heading_1;
                if ($request->image_subheading_1) {
                    $ebookElement->image_subheading_1 = $request->image_subheading_1;
                }
                if ($request->image_subtext_1) {
                    $ebookElement->image_subtext_1 = $request->image_subtext_1;
                }
                if ($request->image_subtext_2) {
                    $ebookElement->image_subtext_2 = $request->image_subtext_2;
                }
                if ($request->image_subtext_3) {
                    $ebookElement->image_subtext_3 = $request->image_subtext_3;
                }
                if ($request->image_subtext_4) {
                    $ebookElement->image_subtext_4 = $request->image_subtext_4;
                }
                $ebookElement->image_text_1 = $request->image_text_1;
                $ebookElement->image_desc_1 = $request->image_desc_1;
                $ebookElement->image_text_2 = $request->image_text_2;
                $ebookElement->image_desc_2 = $request->image_desc_2;
                $ebookElement->image_text_3 = $request->image_text_3;
                $ebookElement->image_desc_3 = $request->image_desc_3;
                $ebookElement->image_text_4 = $request->image_text_4;
                $ebookElement->image_desc_4 = $request->image_desc_4;
            }
            if ($request->element_type_id == 7) {
                $ebookElement->image_type = $request->image_type;
                $ebookElement->image_heading_1 = $request->image_heading_1;
                if ($request->image_subheading) {
                    $ebookElement->image_subheading = $request->image_subheading;
                }
                if ($request->image_subheading_2) {
                    $ebookElement->image_subheading_2 = $request->image_subheading_2;
                }
                if ($request->image_heading_2) {
                    $ebookElement->image_heading_2 = $request->image_heading_2;
                }
                $ebookElement->image_text_1 = $request->image_text_1;
                $ebookElement->image_desc_1 = $request->image_desc_1;
                $ebookElement->image_text_2 = $request->image_text_2;
                $ebookElement->image_desc_2 = $request->image_desc_2;
                $ebookElement->image_text_3 = $request->image_text_3;
                $ebookElement->image_desc_3 = $request->image_desc_3;
                $ebookElement->image_text_4 = $request->image_text_4;
                $ebookElement->image_desc_4 = $request->image_desc_4;
                $ebookElement->image_text_5 = $request->image_text_5;
                $ebookElement->image_desc_5 = $request->image_desc_5;
            }
            if ($request->element_type_id == 8) {
                $ebookElement->image_type = $request->image_type;
                $ebookElement->image_heading_1 = $request->image_heading_1;
                if ($request->image_subheading) {
                    $ebookElement->image_subheading = $request->image_subheading;
                }
                if ($request->image_subheading_2) {
                    $ebookElement->image_subheading_2 = $request->image_subheading_2;
                }
                if ($request->image_heading_2) {
                    $ebookElement->image_heading_2 = $request->image_heading_2;
                }
                $ebookElement->image_text_1 = $request->image_text_1;
                $ebookElement->image_desc_1 = $request->image_desc_1;
                $ebookElement->image_text_2 = $request->image_text_2;
                $ebookElement->image_desc_2 = $request->image_desc_2;
                $ebookElement->image_text_3 = $request->image_text_3;
                $ebookElement->image_desc_3 = $request->image_desc_3;
                $ebookElement->image_text_4 = $request->image_text_4;
                $ebookElement->image_desc_4 = $request->image_desc_4;
                $ebookElement->image_text_5 = $request->image_text_5;
                $ebookElement->image_desc_5 = $request->image_desc_5;
                $ebookElement->image_text_6 = $request->image_text_6;
                $ebookElement->image_desc_6 = $request->image_desc_6;
            }
            if ($request->element_type_id == 9) {
                $ebookElement->image_type = $request->image_type;
                $ebookElement->image_heading_1 = $request->image_heading_1;

                $ebookElement->image_text_1 = $request->image_text_1;
                $ebookElement->image_desc_1 = $request->image_desc_1;
                $ebookElement->image_text_2 = $request->image_text_2;
                $ebookElement->image_desc_2 = $request->image_desc_2;
                $ebookElement->image_text_3 = $request->image_text_3;
                $ebookElement->image_desc_3 = $request->image_desc_3;
                $ebookElement->image_text_4 = $request->image_text_4;
                $ebookElement->image_desc_4 = $request->image_desc_4;
                $ebookElement->image_text_5 = $request->image_text_5;
                $ebookElement->image_desc_5 = $request->image_desc_5;
                $ebookElement->image_text_6 = $request->image_text_6;
                $ebookElement->image_desc_6 = $request->image_desc_6;
                $ebookElement->image_text_7 = $request->image_text_7;
                $ebookElement->image_desc_7 = $request->image_desc_7;
            }
            if ($request->element_type_id == 10) {
                $ebookElement->image_type = $request->image_type;
                $ebookElement->image_heading_1 = $request->image_heading_1;
                if ($request->image_heading_2) {
                    $ebookElement->image_heading_2 = $request->image_heading_2;
                }

                $ebookElement->image_text_1 = $request->image_text_1;
                $ebookElement->image_desc_1 = $request->image_desc_1;
                $ebookElement->image_text_2 = $request->image_text_2;
                $ebookElement->image_desc_2 = $request->image_desc_2;
                $ebookElement->image_text_3 = $request->image_text_3;
                $ebookElement->image_desc_3 = $request->image_desc_3;
                $ebookElement->image_text_4 = $request->image_text_4;
                $ebookElement->image_desc_4 = $request->image_desc_4;
                $ebookElement->image_text_5 = $request->image_text_5;
                $ebookElement->image_desc_5 = $request->image_desc_5;
                $ebookElement->image_text_6 = $request->image_text_6;
                $ebookElement->image_desc_6 = $request->image_desc_6;
                $ebookElement->image_text_7 = $request->image_text_7;
                $ebookElement->image_desc_7 = $request->image_desc_7;
                $ebookElement->image_text_8 = $request->image_text_8;
                $ebookElement->image_desc_8 = $request->image_desc_8;
            }
            if ($request->element_type_id == 11) {
                $ebookElement->image_type = $request->image_type;
                $ebookElement->image_heading_1 = $request->image_heading_1;

                $ebookElement->image_text_1 = $request->image_text_1;
                $ebookElement->image_desc_1 = $request->image_desc_1;
                $ebookElement->image_text_2 = $request->image_text_2;
                $ebookElement->image_desc_2 = $request->image_desc_2;
                $ebookElement->image_text_3 = $request->image_text_3;
                $ebookElement->image_desc_3 = $request->image_desc_3;
                $ebookElement->image_text_4 = $request->image_text_4;
                $ebookElement->image_desc_4 = $request->image_desc_4;
                $ebookElement->image_text_5 = $request->image_text_5;
                $ebookElement->image_desc_5 = $request->image_desc_5;
                $ebookElement->image_text_6 = $request->image_text_6;
                $ebookElement->image_desc_6 = $request->image_desc_6;
                $ebookElement->image_text_7 = $request->image_text_7;
                $ebookElement->image_desc_7 = $request->image_desc_7;
                $ebookElement->image_text_8 = $request->image_text_8;
                $ebookElement->image_desc_8 = $request->image_desc_8;
                $ebookElement->image_text_9 = $request->image_text_9;
                $ebookElement->image_desc_9 = $request->image_desc_9;
                $ebookElement->image_text_10 = $request->image_text_10;
                $ebookElement->image_desc_10 = $request->image_desc_10;
            }
             // list type
            if ($request->element_type_id == 12) {
                $ebookElement->list_type = $request->list_type;
                $ebookElement->list_heading = $request->list_heading;
                $ebookElement->list_points = implode('#@#', $request->list_points);
            }
            if ($request->element_type_id == 15) {
                $ebookElement->example_text = implode('#@#', $request->example_text);
                $ebookElement->example_description = implode('#@#', $request->example_description);
                $ebookElement->practice_description = implode('#@#', $request->practice_description);
            }
            if ($request->element_type_id == 14) {
                if (!empty($request->file('gif_file'))) {
                    $extension1 = $request->file('gif_file')->extension();
                    $filename = Str::random(4) . time() . '.' . $extension1;
                    $ebookElement->image = $request->file('gif_file')->move(('uploads'), $filename);
                } else {
                    $ebookElement->image = null;
                }
            }

            if ($request->element_type_id == 16) {
                $example_gif = [];
                foreach ($request->example_gif as $index => $example_gif_file) {
                    if (!empty($example_gif_file) && $example_gif_file instanceof \Illuminate\Http\UploadedFile) {
                        $extension = $example_gif_file->getClientOriginalExtension();
                        $filename = Str::random(4) . time() . '.' . $extension;
                        $path = $example_gif_file->move(('uploads/images/ebook'), $filename);
                        $example_gif[] = $path;
                    } else {
                        if($ebookElement){
                            $example_gif[] = explode('#@#',$ebookElement->example_description)[$index];
                        }else{
                            $example_gif[] = null;
                        }
                    }
                }
                $ebookElement->example_description = implode('#@#', $example_gif);
                $ebookElement->practice_description = implode('#@#', $request->practice_description);
            }
            if ($request->element_type_id == 17) {
                $ebookElement->example_image_text = implode('#@#', $request->example_image_text);
                $ebookElement->example_description = implode('#@#', $request->example_description);
                $ebookElement->practice_description = implode('#@#', $request->practice_description);
            }
            if ($request->element_type_id == 18) {
                $ebookElement->button_text = implode('#@#', $request->button_text);
            }
            if ($request->element_type_id == 19) {
                $ebookElement->button_text = implode('#@#', $request->button_text);
            }
            if ($request->element_type_id == 20) {
                $ebookElement->single_button_type = $request->single_button_type;
            }

            $ebookElement->save();

            return $this->sendResponse([], 'Ebook Element added successfully');
        }
    }

      /**
     * Remove the specified ebook element from the database.
     *
     * @param  int  $ebookId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteElement($ebookElementId)
    {
        $ebookElement = EbookElement::find($ebookElementId);

        if (!$ebookElement) {
            return $this->sendError('Ebook Element not found');
        }

        $ebookElement->delete();

        return $this->sendResponse([], 'Ebook Element deleted successfully');
    }
}
