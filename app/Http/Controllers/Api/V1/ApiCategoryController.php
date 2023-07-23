<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\CategoryPhotos;
use App\Models\BigStores;
use Illuminate\Support\Facades\Validator;
use App\Http\Services\ApiServices;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use App\DTO\CategoryDTO;
use Illuminate\Routing\Controller as BaseController;

class ApiCategoryController extends BaseController
{
    public function __construct(
        public ApiServices $apiServices,
    ) {
       
    }
    /** 
     * @OA\Post(
     *     path="/api/create/category",
     *     summary="Request that added a new category",
     *     description="",
     *     tags={"Category Section"},
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
     *     @OA\Parameter(
     *        name="big_store_id",
     *        in="query",
     *        description="Please write a big store id",
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
    public function createCategory(Request $request)
    {
        if($this->apiServices->isAdministrator(Auth::id())){
            $data = $request->all();
            $validator = $this->validateCategory($data);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }
            $userDTO = new CategoryDTO();
            $userDTO->title = $data['title'];
            $userDTO->photoFileName = $data['photoFileName'];
            $userDTO->big_store_id = $data['big_store_id'];
            $bigStore = BigStores::find($data['big_store_id']);
            if ($bigStore == null) {
                return response()->json(['error' => 'Unable to find big store by this ID: '.$request['big_store_id']]);
            }
            $category = Category::insertGetId([
                'big_store_id' => $request['big_store_id'],
                'title' => $request['title'],
                'status' => 'active',
                'photoFileName' => $request['photoFileName'],
                'photoFilePath' => 'test',
                'updated_at' => now(),
                'created_at' => now(),
            ]);
            $image = array();
            $category_photos = Category::where('id',$category)->first();
            if($file = $request->file('image')){
                foreach($file as $file){
                    $image_name = md5(rand(1000, 10000));
                    $ext = strtolower($file->getClientOriginalExtension());
                    $image_full_name = $image_name . '.' . $ext;
                    $uploade_path = public_path('Category_images' . '/' . $category_photos['photoFileName']);
        
                    if (!File::exists($uploade_path)) {
                        File::makeDirectory($uploade_path);
                    }
        
                    $file->move($uploade_path, $image_full_name);
                    $image[] = $uploade_path . '/' . $image_full_name;
        
                    CategoryPhotos::create([
                        'name' => $image_full_name,
                        'path' => $uploade_path,
                        'category_id' => $category->id,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]);
                }
            }

            return response()->json(['categories'=>$this->apiServices->categoryAndImages()]);

        } else {
            return response()->json(['No access']);
        }
    }

     /** 
     * @OA\Get(
     *     path="/api/get/catagories",
     *     summary="Request which returns categories",
     *     description="",
     *     tags={"Category Section"},
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
    public function getCategories(Request $request)
    {
        return response()->json(['category' => $this->apiServices->categoryAndImages()]);
    }

    private function validateCategory(array $data)
    {
        $rules = [
            'title'=>'required|unique:categories,title',
            'photoFileName' => 'required|unique:categories,photoFileName',
            'big_store_id' => 'required',
        ];

        return Validator::make($data, $rules);
    }
}
