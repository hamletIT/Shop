<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\Validator;
use App\Models\BigStores;
use App\Models\Products;
use App\Models\Photos;
use App\Models\Prices;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Routing\Controller as BaseController;
use App\Http\Services\ApiServices;
use App\DTO\ProductDTO;


class ApiProductController extends BaseController
{
    public function __construct(
        public ApiServices $apiServices,
    ) {
       
    }
    /** 
    * @OA\Post(
    *     path="/api/create/product",
    *     summary="Request that added a new product",
    *     description="",
    *     tags={"Product Section"},
    *     @OA\Parameter(
    *        name="title",
    *        in="query",
    *        description="Provide product title",
    *        required=true,
    *        allowEmptyValue=true,
    *     ),
    *     @OA\Parameter(
    *        name="description",
    *        in="query",
    *        description="Provide product description",
    *        required=true,
    *        allowEmptyValue=true,
    *     ),
    *     @OA\Parameter(
    *        name="photoFileName",
    *        in="query",
    *        description="Provide product photo file Name",
    *        required=true,
    *        allowEmptyValue=true,
    *     ),
    *     @OA\Parameter(
    *        name="status",
    *        in="query",
    *        description="Provide product status",
    *        required=true,
    *        allowEmptyValue=true,
    *     ),
    *     @OA\Parameter(
    *        name="price",
    *        in="query",
    *        description="Provide product price",
    *        required=true,
    *        allowEmptyValue=true,
    *     ),
    *     @OA\Parameter(
    *        name="totalQty",
    *        in="query",
    *        description="Provide product totalQty",
    *        required=true,
    *        allowEmptyValue=true,
    *     ),
    *     @OA\Parameter(
    *        name="sub_category_id",
    *        in="query",
    *        description="provide category ID or let empty or provide 1 for create default one",
    *        required=true,
    *        allowEmptyValue=true,
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         @OA\MediaType(
    *             mediaType="application/json",
    *         )
    *     ),
    *     @OA\Response(
    *          response=401,
    *          description="Unauthenticated",
    *     ),
    * 
    *     @OA\Response(
    *          response=403,
    *          description="Forbidden"
    *     ),
    *     @OA\Response(
    *          response=429,
    *          description="validation error"
    *     )
    * )
    */
    public function createProduct(Request $request)
    {
        $data = $request->all();
        $validator = $this->validateProduct($data);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }

        $userDTO = new ProductDTO();
        $userDTO->sub_category_id = $data['sub_category_id'];
        $userDTO->title = $data['title'];
        $userDTO->description = $data['description'];
        $userDTO->photoFileName = $data['photoFileName'];
        $userDTO->status = $data['status'];
        $userDTO->price = $data['price'];
        $userDTO->totalQty = $data['totalQty'];
        $issetSubCategory = SubCategory::find($request['sub_category_id']);
        if($issetSubCategory == null){
            return response()->json(['error' => 'Unable to find sub category by this ID: '.$request['sub_category_id']]);
        }
        $product = Products::insertGetId([
            'sub_category_id'=> $request['sub_category_id'],
            'title' => $request['title'],
            'description' => $request['description'],
            'photoFileName' => $request['photoFileName'],
            'photoFilePath' => 'test',
            'status' => $request['status'],
            'totalQty' => $request['totalQty'],
            'updated_at' => now(),
            'created_at' => now(),
        ]);
        Prices::insertGetId([
            'product_id' => $product,
            'title' => $request['title'],
            'productPrice' => $request['price'],
            'status' => $request['status'],
            'updated_at' => now(),
            'created_at' => now(),
        ]);
       
        $image = array();
        $products = Products::where('id',$product)->first();
        if($file = $request->file('image')){
            foreach($file as $file){
                $image_name = md5(rand(1000,10000));
                $ext = strtolower($file->getClientOriginalExtension());
                $image_full_name = $image_name.'.'.$ext;
                if (!File::exists('Images'.'/'.$products['photoFileName'])) {
                    File::makeDirectory('Images'.'/'.$products['photoFileName']);
                }
                $uploade_path = public_path('Images'.'/'.$products['photoFileName']);
                $image_url = $uploade_path.$image_full_name;
                $file->move($uploade_path,$image_full_name);
                $image[] = $image_url;
                Photos::create([
                    'name' => $image_full_name,
                    'path' => $uploade_path,
                    'product_id' => $products->id,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]);
            }
        }

        return response()->json(['bigStore' => BigStores::with($this->apiServices->StructureOfTheStandardSchema())->get()]);
    }

    /** 
     * @OA\Put(
     *     path="/api/update/product",
     *     summary="Request which updating something regarding product",
     *     description="",
     *     tags={"Product Section"},
     *     @OA\Parameter(
     *        name="product_id",
     *        in="query",
     *        description="Please write product id",
     *        required=true,
     *        allowEmptyValue=true,
     *     ),
     *     @OA\Parameter(
     *        name="title",
     *        in="query",
     *        description="For example please write new product title",
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
    public function updateProduct(Request $request)
    {
        $rules = [
            'product_id' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }

        $product = Products::where('id',$request->product_id)->with(
            $this->apiServices->productsSchema()
        )->first();
        if ($product == null) {
            return response()->json(['status'=>'No data found']);
        } else {
            $input = $request->all();
            $product->update($input);
            if (isset($request['price'])) {
                Prices::where('product_id',$product->id)->update([
                    'productPrice'=>$request['price'],
                ]);
            }
            if ($product) {
                return response()->json(['updated'=>$product,'update status'=>true]);
            } else {
                return response()->json(['update status'=>false]);
            }
        }
    }

    /**
    * @OA\Delete(
    *     path="/api/delete/product",
    *     summary="Request which deletes product",
    *     description="",
    *     tags={"Product Section"},
    *     @OA\Parameter(
    *        name="product_id",
    *        in="query",
    *        description="Please write product id",
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
    public function deleteProduct(Request $request)
    {
        $rules = [
            'product_id' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        $product = Products::where('id',$request->product_id)->first();
        if ($product == null) {
            return response()->json(['product'=>'No data found']);
        }
        File::deleteDirectory(public_path('Images'.'/'.$product['photoFileName']));

        if ($product == null) {

            return response()->json(['status'=>'No data found']);
        } else {
            $product->delete();
        
            return response()->json(['deleted'=>true,'Product deleted'=>true]);
        }
    }

    /**
    * @OA\Get(
    *     path="/api/filter/product",
    *     summary="Request that search via name",
    *     description="",
    *     tags={"Product Section"},
    *     @OA\Parameter(
    *        name="title",
    *        in="query",
    *        description="Please write product title",
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
    public function filterProduct(Request $request)
    {
        if(!is_null($request['title'])) {

            return response()->json(
                [
                    'product' => Products::Where('title', 'LIKE', '%'.$request['title'].'%')->with(
                    $this->apiServices->productsSchema()
                )->get()
                ]
            );
           
        }

        return response()->json(['product' => []]);
    }

    /**
    * @OA\Get(
    *     path="/api/get/product/list",
    *     summary="Request which returns categories with products, store, photos and options",
    *     description="",
    *     tags={"Product Section"},
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
    public function getProductList(Request $request)
    {
        return response()->json(['bigStore' => BigStores::with(
           $this->apiServices->StructureOfTheStandardSchema()
        )->get()]);
    }

    private function validateProduct(array $data)
    {
        $rules = [
            'sub_category_id' => 'required',
            'title' => 'required',
            'description' => 'required',
            'photoFileName' => 'required',
            'status' => 'required',
            'price' => 'required|numeric|integer',
            'totalQty' => 'required|numeric|integer',
        ];

        return Validator::make($data, $rules);
    }
}