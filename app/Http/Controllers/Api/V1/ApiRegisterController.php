<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Services\ApiServices;
use App\DTO\UserDTO;
use Illuminate\Routing\Controller as BaseController;

class ApiRegisterController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct(
        public ApiServices $apiServices,
    ) {
       
    }
    /**
    * @OA\Post(
    *    path="/api/register",
    *    summary="Registeration",
    *    description="",
    *    tags={"User inforamtion Section"},
    *    @OA\Parameter(
    *       name="phone",
    *       in="query",
    *       description="Provide youre phone",
    *       required=true,
    *       allowEmptyValue=true,
    *    ),
    *    @OA\Parameter(
    *       name="name",
    *       in="query",
    *       description="Provide youre name",
    *       required=true,
    *       allowEmptyValue=true,
    *    ),
    *    @OA\Parameter(
    *       name="email",
    *       in="query",
    *       description="Provide youre email",
    *       required=true,
    *       allowEmptyValue=true,
    *    ),
    *    @OA\Parameter(
    *       name="password",
    *       in="query",
    *       description="Provide youre password",
    *       required=true,
    *       allowEmptyValue=true,
    *    ),
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
    public function register(Request $input)
    {
        $data = $input->all();
        $validator = $this->validateUser($data);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        $userDTO = new UserDTO();
        $userDTO->phone = $data['phone'];
        $userDTO->name = $data['name'];
        $userDTO->email = $data['email'];
        $userDTO->password = $data['password'];
        $adminIsset = User::where('name','Admin')->first();

        if($adminIsset == null && $input['name'] == 'Admin'){
            $user = User::create([
                'status' => 1,
                'phone' => $input['phone'],
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'two_factor_secret' => 'admin',
            ]);
        }
        if($input['name'] == 'admin' || $input['name'] == 'super admin'){
            return response()->json('Can`t create a name '.$input['name']);
        }
        if($input['email'] == 'admin@gmail.com' || $input['email'] == 'admin@mail.ru'){
            return response()->json('Can`t create a email '.$input['email']);
        }
        $user = User::create([
            'status' => 0,
            'phone' => $input['phone'],
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        $token = $user->createToken('Token Name')->accessToken;

        return response()->json(['token'=>$token,'user'=>$user]);
    }

    /**
    * @OA\Post(
    *     path="/api/login",
    *     summary="Log in",
    *     description="",
    *     tags={"User inforamtion Section"},
    *     @OA\Parameter(
    *        name="email",
    *        in="query",
    *        description="Please write your e-mail here",
    *        required=true,
    *       allowEmptyValue=true, 
    *     ),
    *     @OA\Parameter(
    *        name="password",
    *        in="query",
    *        description="Please write your password here",
    *        required=true,
    *       allowEmptyValue=true,
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
    public function login(Request $request)
    {
        if (!\Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                'message' => 'Login information is invalid.'
            ], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();
        $token = $user->createToken('Token Name')->accessToken;
       
        return response()->json(['token' => $token,'user' => $user]);
    }

    /**
    * @OA\Post(
    *     path="/api/logout",
    *     summary="Log out",
    *     description="",
    *     tags={"User inforamtion Section"},
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
    public function logout(Request $request) {
        auth()->user()->tokens()->delete();

        return [
            'message' => 'Logged out'
        ];
    }

    private function validateUser(array $data)
    {
        $rules = [
            'phone'   =>'required|unique:users',
            'name'    =>'required|string|max:255',
            'email'   =>'required|string|email|max:255|unique:users',
            'password'=>'required|min:6',
        ];

        return Validator::make($data, $rules);
    }
}