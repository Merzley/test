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
    $user = \Illuminate\Support\Facades\Auth::guard('api')->user();

    return new \Illuminate\Http\JsonResponse([
        'data' => [
            'name' => $user->name,
            'id' => $user->id
        ]
    ]);
})->middleware('auth:api');

Route::post('/ifttt/v1/triggers/pusher_pressed', function() {
    $user = \Illuminate\Support\Facades\Auth::guard('api')->user();

    $query = \Illuminate\Support\Facades\DB::table('push_events')
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc');

    if (\Illuminate\Support\Facades\Request::has('limit')) {
        $query->limit(\Illuminate\Support\Facades\Request::get('limit'));
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

    $result = [];
    foreach ($found as $event) {
        $result[] = [
            'created_at' => $event->created_at,
            'latitude' => $event->latitude,
            'longitude' => $event->longitude,
            'meta' => [
                'id' => $event->id,
                'timestamp' => \Carbon\Carbon::parse($event->created_at)->timestamp
            ]
        ];
    }

    return new \Illuminate\Http\JsonResponse($result);
})->middleware('auth:api');

Route::get('/ifttt/v1/status', function() {
    return '';
});

Route::post('/ifttt/v1/test/setup', function(Request $request) {
    if (!$request->hasHeader('IFTTT-Service-Key'))
        throw new \Symfony\Component\HttpKernel\Exception\HttpException( \Illuminate\Http\Response::HTTP_FORBIDDEN);

    if ($request->header('IFTTT-Service-Key') != 'SOME_SERVICE_KEY') //TODO: !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        throw new \Symfony\Component\HttpKernel\Exception\HttpException( \Illuminate\Http\Response::HTTP_FORBIDDEN);

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
                        'pushed_times' => '3'
                    ]
                ]
            ]
        ]
    ];

    return new \Illuminate\Http\JsonResponse($result);
});
