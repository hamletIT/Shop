<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Carts;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Services\ApiServices;
use App\DTO\CartDto;
use Illuminate\Routing\Controller as BaseController;

class ApiCartController extends BaseController
{
    public function __construct(
        public ApiServices $apiServices,
    ) {
       
    }
    /**
    * @OA\Post(
    *     path="/api/add/toCart",
    *     summary="Add To Cart",
    *     description="",
    *     tags={"Cart Section"},
    *     @OA\Parameter(
    *        name="product_id",
    *        in="query",
    *        description="Please write product ID",
    *        required=true,
    *        allowEmptyValue=true,
    *     ),
    *     @OA\Parameter(
    *        name="user_id",
    *        in="query",
    *        description="Please write user ID",
    *        required=true,
    *        allowEmptyValue=true,
    *     ),
    *     @OA\Parameter(
    *        name="totalQty",
    *        in="query",
    *        description="Please write totalQty",
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
    public function addToCart(Request $request)
    { 
        if($this->apiServices->isAdministrator(Auth::id())){
            $data = $request->all();
            $validator = $this->validateCart($data);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }
            $userDTO = new CartDto();
            $userDTO->user_id = $data['user_id'];
            $userDTO->product_id = $data['product_id'];
            $userDTO->totalQty = $data['totalQty'];

            $product = Products::where('id',$request->product_id)->first();
            $cartProductNumber = Carts::where('product_id',$request->product_id)->first();
            if (is_null($product)) {
                return response()->json(['product'=>'product not found']);
            }
            $randomNumber = rand(config('app.rand_min'),config('app.rand_max'));
        
            if ($cartProductNumber == null) {
                Carts::insertGetId([
                    'random_number' => $randomNumber,
                    'status' => $product->status,
                    'sessionStartDate' => Carbon::now(config('app.timezone_now'))->toDateTimeString(),
                    'sessionEndDate' => Carbon::now(config('app.timezone_now'))->addWeeks(1)->toDateTimeString(),
                    'totalQty' => $request->totalQty,
                    'product_id' => $product->id,
                    'user_id' => $request->user_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                return response()->json(['error'=>'This product already exists on the cart']);
            }
            $cartResponse = $this->apiServices->getCart($request->user_id);

            return response()->json($cartResponse);
        } else {
            return response()->json(['No access']);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/get/cart/products",
     *     summary="Get Cart Products",
     *     description="",
     *     tags={"Cart Section"},
     *     @OA\Parameter(
     *        name="user_id",
     *        in="query",
     *        description="Please write user ID",
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
     *    ),
     * )
     */
    public function getCartProducts(Request $request)
    {
        $rules = [
            'user_id' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        $userIsset = User::find($request->user_id);
        if ($userIsset == null) {
            return response()->json(["userCart" => []]);
        }
        $cartResponse = $this->apiServices->getCart($request->user_id);

        return response()->json($cartResponse);
    }

    /**
    *  @OA\Delete(
    *     path="/api/delete/cart/products",
    *     summary="Delete Cart Products",
    *     description="",
    *     tags={"Cart Section"},
    *     @OA\Parameter(
    *        name="product_id",
    *        in="query",
    *        description="Please write product ID",
    *        required=true,
    *        allowEmptyValue=true,
    *     ),
    *     @OA\Parameter(
    *        name="user_id",
    *        in="query",
    *        description="Please write user ID",
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
    public function deleteCartProducts(Request $request)
    {
        if($this->apiServices->isAdministrator(Auth::id())){
            $data = $request->all();
            $validator = $this->validateCartDelete($data);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }
            $userDTO = new CartDto();
            $userDTO->user_id = $data['user_id'];
            $userDTO->product_id = $data['product_id'];
           
            $userCart = Carts::where('user_id',$request->user_id)->where('product_id',$request->product_id)->first();

            if ($userCart == null) {
                return response()->json(['cart_products'=>'No data found']);
            } else {
                Carts::where('user_id',$request->user_id)->where('product_id',$request->product_id)->delete();

                $cartResponse = $this->apiServices->getCart($request->user_id);

                return response()->json($cartResponse);
            }
        } else {
            return response()->json(['No access']);
        }
    }

    /**
    *  @OA\Post(
    *     path="/api/add/quantity/forOne/Product",
    *     summary="Add Quantity For One Product",
    *     description="",
    *     tags={"Cart Section"},
    *     @OA\Parameter(
    *        name="product_id",
    *        in="query",
    *        description="Please write product ID",
    *        required=true,
    *        allowEmptyValue=true,
    *     ),
    *     @OA\Parameter(
    *        name="user_id",
    *        in="query",
    *        description="Please write user ID",
    *        required=true,
    *        allowEmptyValue=true,
    *     ),
    *     @OA\Parameter(
    *        name="totalQty",
    *        in="query",
    *        description="Please Write quantity How much do you want to add",
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
    public function AddQuantityForOneProduct(Request $request)
    {
        $rules = [
            'user_id' => 'required|numeric|integer',
            'product_id' => 'required|numeric|integer',
            'totalQty' => 'required|numeric|integer',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        $updateCartProductQty = Carts::where('user_id', $request->user_id)->where('product_id', $request->product_id)->update(['totalQty' => $request->totalQty]);

        if ($updateCartProductQty) {
            $cartResponse = $this->apiServices->getCart($request->user_id);

            return response()->json($cartResponse);
        }
       
        return response()->json(['update' => 'Failed to update']);
    }

    private function validateCart(array $data)
    {
        $rules = [
            'user_id' => 'required|numeric|integer',
            'product_id' => 'required|numeric|integer',
            'totalQty' => 'required|numeric|integer',
        ];

        return Validator::make($data, $rules);
    }

    private function validateCartDelete(array $data)
    {
        $rules = [
            'user_id' => 'required|numeric|integer',
            'product_id' => 'required|numeric|integer',
        ];

        return Validator::make($data, $rules);
    }
}