<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/ifttt/v1/user/info', function (Request $request) {
    if (!\Illuminate\Support\Facades\Auth::guard('api')->check()) {
        return new \Illuminate\Http\JsonResponse(
            [
                'errors' => [
                    ['message' => 'Unauthorized']
                ]
            ],
            \Illuminate\Http\Response::HTTP_UNAUTHORIZED,
            ['content-type' => 'application/json; charset=utf-8']
        );
    }

    $user = \Illuminate\Support\Facades\Auth::guard('api')->user();

    return new \Illuminate\Http\JsonResponse([
        'data' => [
            'name' => $user->name,
            'id' => strval($user->id)
        ]
    ],
    200,
    [
        'content-type' => 'application/json; charset=utf-8'
    ]);
});

Route::post('/ifttt/v1/triggers/pusher_pressed', function() {
    if (!\Illuminate\Support\Facades\Auth::guard('api')->check()) {
        return new \Illuminate\Http\JsonResponse(
            [
                'errors' => [
                    ['message' => 'Unauthorized']
                ]
            ],
            \Illuminate\Http\Response::HTTP_UNAUTHORIZED,
            ['content-type' => 'application/json; charset=utf-8']
        );
    }

    $user = \Illuminate\Support\Facades\Auth::guard('api')->user();

    $query = \Illuminate\Support\Facades\DB::table('push_events')
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc');

    if (\Illuminate\Support\Facades\Request::has('limit')) {
        $query->limit(\Illuminate\Support\Facades\Request::get('limit'));
    }

    if (!\Illuminate\Support\Facades\Request::has('triggerFields.pusher') || !\Illuminate\Support\Facades\Request::has('triggerFields.pushed_times'))
    {
        return new \Illuminate\Http\JsonResponse(
            [
                'errors' => [
                    ['message' => 'No trigger fields']
                ]
            ],
            \Illuminate\Http\Response::HTTP_BAD_REQUEST,
            ['content-type' => 'application/json; charset=utf-8']
        );
    }

    if (\Illuminate\Support\Facades\Request::has('triggerFields')) {
        $triggerFields = \Illuminate\Support\Facades\Request::get('triggerFields');

        if (array_has($triggerFields, 'pusher')) {
            $query->where('pusher', $triggerFields['pusher']);
        }

        if (array_has($triggerFields, 'pushed_times')) {
            $query->where('pushed_times', $triggerFields['pushed_times']);
        }
    }

    $found = $query->get();

    $result = ['data' => []];
    foreach ($found as $event) {
        $result['data'][] = [
            'created_at' => \Carbon\Carbon::parse($event->created_at)->toIso8601String(),
            'latitude' => $event->latitude,
            'longitude' => $event->longitude,
            'meta' => [
                'id' => $event->id,
                'timestamp' => \Carbon\Carbon::parse($event->created_at)->timestamp
            ]
        ];
    }

    return new \Illuminate\Http\JsonResponse($result, 200, [
        'content-type' => 'application/json; charset=utf-8'
    ]);
});

Route::get('/ifttt/v1/status', function(Request $request) {
    if (!$request->hasHeader('IFTTT-Service-Key'))
        throw new \Symfony\Component\HttpKernel\Exception\HttpException( \Illuminate\Http\Response::HTTP_UNAUTHORIZED);

    if ($request->header('IFTTT-Service-Key') != 'AguL3NPalkSilfha_oaat5y8hXI1Mgc6ranb5ROtXfNRzfUrRAaOcI2b67kmWO9z') //TODO: !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        throw new \Symfony\Component\HttpKernel\Exception\HttpException( \Illuminate\Http\Response::HTTP_UNAUTHORIZED);

    return '';
});

Route::post('/ifttt/v1/test/setup', function(Request $request) {
    if (!$request->hasHeader('IFTTT-Service-Key'))
        throw new \Symfony\Component\HttpKernel\Exception\HttpException( \Illuminate\Http\Response::HTTP_UNAUTHORIZED);

    if ($request->header('IFTTT-Service-Key') != 'AguL3NPalkSilfha_oaat5y8hXI1Mgc6ranb5ROtXfNRzfUrRAaOcI2b67kmWO9z') //TODO: !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        throw new \Symfony\Component\HttpKernel\Exception\HttpException( \Illuminate\Http\Response::HTTP_UNAUTHORIZED);

    $user = \App\User::find(1);

    /** @var \App\User $user */
    $token = $user->createToken('', ['ifttt'])->accessToken;

    $result = [
        'data' => [
            'accessToken' => $token,
            'samples' => [
                'triggers' => [
                    'pusher_pressed' => [
                        'pusher' => '2_O_CLOCK',
                        'pushed_times' => '3345'
                    ]
                ]
            ]
        ]
    ];

    $newData = [
        'user_id' => 1,
        'pusher' => '2_O_CLOCK',
        'pushed_times' => '3345',
    ];

    $newData['created_at'] = new \Carbon\Carbon();
    \Illuminate\Support\Facades\DB::table('push_events')->insert($newData);

    $newData['created_at'] = new \Carbon\Carbon();
    \Illuminate\Support\Facades\DB::table('push_events')->insert($newData);

    $newData['created_at'] = new \Carbon\Carbon();
    \Illuminate\Support\Facades\DB::table('push_events')->insert($newData);

    return new \Illuminate\Http\JsonResponse($result, 200, [
        'content-type' => 'application/json; charset=utf-8'
    ]);
});
