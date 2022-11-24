<?php

namespace App\Http\Controllers;

use App\Mappers\UserMapper;
use App\Models\User\User;
use App\Repositories\UserRepository;
use App\Support\Requests\UserStoreRequest;
use App\Support\Requests\UserUpdateRequest;
use Exception;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    public function __construct(private UserRepository $userRepository, private UserMapper $userMapper)
    {
        $this->userMapper = $this->userMapper;
    }

    /**
     * @OA\Get(
     *     path="/api/users/{user}/{nickname}",
     *     tags={"Users"},
     *     summary="Show user",
     *     description="Show user",
     *     @OA\Parameter(
     *          name="user",
     *          in="path",
     *          description="ID of user",
     *          required=true,
     *          example=1,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *    @OA\Parameter(
     *          name="user",
     *          in="path",
     *          description="nickname of user",
     *          required=true,
     *          example=1,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="User Details",
     *         @OA\JsonContent(ref="#/components/schemas/UserMapper"),
     *     ),
     * )
     *
     * @param User $user
     *
     * @return JsonResponse
     */
    public function index(User $user): JsonResponse
    {
        $d = User::all();
        return \Response::json(
            $d
        );
    }
    public function show(User $user, $nickName): JsonResponse
    {
        // get the single user with nickname first
        $userWithNickName = $user::where('nickname','=',$nickName)->first();

        return \Response::json(
            $this->userMapper->single($userWithNickName),
            200,
            []
        );
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Create user",
     *     description="Create user",
     *     @OA\RequestBody(
     *         @OA\JsonContent(ref="#/components/schemas/UserStoreRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User created",
     *         @OA\JsonContent(ref="#/components/schemas/UserMapper"),
     *     ),
     *     @OA\Response(response=400, description="User cannot be created"),
     *     @OA\Response(response=422, description="Failed validation of given params"),
     * )
     *
     * @param UserStoreRequest $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function store(UserStoreRequest $request): JsonResponse
    {
//        dd($request);
        $user = $this->userRepository->create([
            'nickname' => $request->input('nickname'),
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        return \Response::json($this->userMapper->single($user));
    }


    /**
     * @OA\Put(
     *     path="/api/users/{user}",
     *     tags={"Users"},
     *     summary="Update user",
     *     description="Update user",
     *     @OA\Parameter(
     *          name="user",
     *          in="path",
     *          description="ID of user",
     *          required=true,
     *          example=1,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(ref="#/components/schemas/UserUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User after the update",
     *         @OA\JsonContent(ref="#/components/schemas/UserMapper"),
     *     ),
     *     @OA\Response(response=422, description="Failed validation of given params"),
     * )
     *
     * @param UserUpdateRequest $request
     * @param User              $user
     *
     * @return JsonResponse
     */
    public function update(UserUpdateRequest $request, User $user): JsonResponse
    {
        $data = [
            'nickname' => trim($request->input('nickname')),
            'name'     => trim($request->input('name')),
            'email'    => trim($request->input('email')),
            'password' => Hash::make(trim($request->input('password')) ?: null),
        ];

        $user->fill($data)->save();

        return \Response::json($this->userMapper->single($user));
    }
}
