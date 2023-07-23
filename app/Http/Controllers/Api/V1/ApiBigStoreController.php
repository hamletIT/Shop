<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BigStores;
use App\Models\User;
use App\Models\BigStorePhotos;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use App\Http\Services\ApiServices;
use App\DTO\BigStoreDTO;

class ApiBigStoreController extends Controller
{
    public function __construct(
        public ApiServices $apiServices,
    ) {
       
    }
    /** 
     * @OA\Get(
     *     path="/api/get/bigStores",
     *     summary="Request that get all big stores",
     *     description="",
     *     tags={"Big Store Section"},
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
    public function getBigStores(Request $request)
    {
        return response()->json(['Big_stores'=>BigStores::with($this->apiServices->StructureOfTheStandardSchema())->get()]);
    }

    /** 
     * @OA\Get(
     *     path="/api/get/bigStore",
     *     summary="Request that get single big stores",
     *     description="",
     *     tags={"Big Store Section"},
     *     @OA\Parameter(
     *        name="big_store_id",
     *        in="query",
     *        description="Please write big store id",
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
    public function getBigStore(Request $request)
    {
        $rules = [
            'big_store_id'=>'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        return response()->json(['Big_stores'=>BigStores::where('id',$request->big_store_id)->with(
            $this->apiServices->StructureOfTheStandardSchema()
        )->get()]);
    }

     /** 
     * @OA\Post(
     *     path="/api/add-Big-Store",
     *     summary="Request that add new Big Store",
     *     description="",
     *     tags={"Big Store Section"},
     *     @OA\Parameter(
     *        name="user_id",
     *        in="query",
     *        description="Please write a user ID",
     *        required=true,
     *        allowEmptyValue=true,
     *     ),
     *     @OA\Parameter(
     *        name="name",
     *        in="query",
     *        description="Please write a big store name",
     *        required=true,
     *        allowEmptyValue=true,
     *     ),
     *     @OA\Parameter(
     *        name="info",
     *        in="query",
     *        description="Please write a big store info",
     *        required=true,
     *        allowEmptyValue=true,
     *     ),
     *     @OA\Parameter(
     *        name="photoFileName",
     *        in="query",
     *        description="Please write a big store Photo File Name",
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
    public function addBigStore(Request $request)
    {
        $data = $request->all();
        $validator = $this->validateBigStore($data);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        $userDTO = new BigStoreDTO();
        $userDTO->name = $data['name'];
        $userDTO->user_id = $data['user_id'];
        $userDTO->info = $data['info'];
        $userDTO->photoFileName = $data['photoFileName'];
        $user = User::find($request['user_id']);
        if ($user == null) {
            return response()->json(['error' => 'Unable to find User by this ID: '.$request['user_id']]);
        }
        $bigStore = BigStores::insertGetId([
            'user_id' => $request['user_id'],
            'name' => $request['name'],
            'status' => 1,
            'info' => $request['info'],
            'photoFileName' => $request['photoFileName'],
            'photoFilePath' => 'test',
            'updated_at' => now(),
            'created_at' => now(),
        ]);
        $image = array();
        $bigStore_photos = BigStores::where('id',$bigStore)->first();
        if($file = $request->file('image')){
            foreach($file as $file){
                $image_name = md5(rand(1000,10000));
                $ext = strtolower($file->getClientOriginalExtension());
                $image_full_name = $image_name.'.'.$ext;
                if (!File::exists('Big_Store_images'.'/'.$bigStore_photos['photoFileName'])) {
                    File::makeDirectory('Big_Store_images'.'/'.$bigStore_photos['photoFileName']);
                }
                $uploade_path = public_path('Big_Store_images'.'/'.$bigStore_photos['photoFileName']);
                $image_url = $uploade_path.$image_full_name;
                $file->move($uploade_path,$image_full_name);
                $image[] = $image_url;
                BigStorePhotos::create([
                    'name' => $image_full_name,
                    'path' => $uploade_path,
                    'big_store_id' => $bigStore_photos->id,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]);
            }
        }

         return response()->json(['Big_stores'=>BigStores::with($this->apiServices->StructureOfTheStandardSchema())->get()]);
    }

    private function validateBigStore(array $data)
    {
        $rules = [
            'user_id' =>'required',
            'name'=>'required|unique:big_stores,name',
            'info' => 'required',
            'photoFileName' => 'required|unique:big_stores,photoFileName',
        ];

        return Validator::make($data, $rules);
    }
}
