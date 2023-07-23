<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\Validator;
use App\Models\SubCategory;
use App\Models\category_subCategory;
use App\Models\SubCategoryPhotos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Http\Services\ApiServices;
use App\DTO\SubCategoryDTO;
use Illuminate\Routing\Controller as BaseController;

class ApiSubCategoryController extends BaseController
{
    public function __construct(
        public ApiServices $apiServices,
    ) {
       
    }
    /** 
     * @OA\Post(
     *     path="/api/create/sub/category",
     *     summary="Request that added a new sub category",
     *     description="",
     *     tags={"Sub Category Section"},
     *     @OA\Parameter(
     *        name="category_id",
     *        in="query",
     *        description="Please write a category ID",
     *        required=true,
     *        allowEmptyValue=true,
     *     ),
     *     @OA\Parameter(
     *        name="title",
     *        in="query",
     *        description="Please write a category title",
     *        required=true,
     *        allowEmptyValue=true,
     *     ),
     *     @OA\Parameter(
     *        name="photoFileName",
     *        in="query",
     *        description="Please write a category Photo File Name",
     *        required=true,
     *        allowEmptyValue=true,
     *     ),
     *     @OA\Response(
     *        response=200,
     *        description="OK",
     *        @OA\MediaType(
     *            mediaType="application/json",
     *        )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="validation error"
     *     )
     *   ),
     * )
     */
    public function createSubCategory(Request $request)
    {
        if($this->apiServices->isAdministrator(Auth::id())){
            $data = $request->all();
            $validator = $this->validateSubCategory($data);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }
            $userDTO = new SubCategoryDTO();
            $userDTO->category_id = $data['category_id'];
            $userDTO->photoFileName = $data['photoFileName'];
            $userDTO->title = $data['title'];
            
            $sub_category = SubCategory::insertGetId([
                'title' => $request['title'],
                'status' => 'active',
                'photoFileName' => $request['photoFileName'],
                'photoFilePath' => 'test',
                'updated_at' => now(),
                'created_at' => now(),
            ]);
            category_subCategory::create([
                'category_id' => $request['category_id'],
                'sub_category_id' => $sub_category,
                'updated_at' => now(),
                'created_at' => now(),
            ]);
            $image = array();
            $sub_category_photos = SubCategory::where('id',$sub_category)->first();
            if($file = $request->file('image')){
                foreach($file as $file){
                    $image_name = md5(rand(1000,10000));
                    $ext = strtolower($file->getClientOriginalExtension());
                    $image_full_name = $image_name.'.'.$ext;
                    if (!File::exists('Sub_category_images'.'/'.$sub_category_photos['photoFileName'])) {
                        File::makeDirectory('Sub_category_images'.'/'.$sub_category_photos['photoFileName']);
                    }
                    $uploade_path = public_path('Sub_category_images'.'/'.$sub_category_photos['photoFileName']);
                    $image_url = $uploade_path.$image_full_name;
                    $file->move($uploade_path,$image_full_name);
                    $image[] = $image_url;
                    SubCategoryPhotos::create([
                        'name' => $image_full_name,
                        'path' => $uploade_path,
                        'sub_category_id' => $sub_category_photos->id,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]);
                }
            }

            return response()->json(['subCategories'=>$this->apiServices->subCategoryAndImages()]);
        } else {
            return response()->json(['No access']);
        }
    }

     /** 
     * @OA\Get(
     *     path="/api/get/sub/catagories",
     *     summary="Request which returns sub categories",
     *     description="",
     *     tags={"Sub Category Section"},
     *     @OA\Response(
     *        response=200,
     *        description="OK",
     *        @OA\MediaType(
     *            mediaType="application/json",
     *        )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="validation error"
     *     )
     *   ),
     * )
     */
    public function getSubCategories(Request $request)
    {
        return response()->json(['subCategories'=>$this->apiServices->subCategoryAndImages()]);
    }

    private function validateSubCategory(array $data)
    {
        $rules = [
            'category_id' => 'required',
            'title'=>'required|unique:sub_categories,title',
            'photoFileName' => 'required|unique:sub_categories,photoFileName',
        ];

        return Validator::make($data, $rules);
    }
}

