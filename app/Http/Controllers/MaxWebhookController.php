<?php

namespace App\Http\Controllers;

use App\Modules\Max\Contracts\WebhookEventStoreInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaxWebhookController extends Controller
{
    public function postWebhook(Request $request, WebhookEventStoreInterface $eventStore): JsonResponse
    {
        $expectedSecret = (string) config('max.webhook.secret', '');
        $providedSecret = (string) $request->header('X-Max-Bot-Api-Secret', '');

        if ($expectedSecret !== '' && ! hash_equals($expectedSecret, $providedSecret)) {
            return response()->json([
                'ok' => false,
                'message' => 'Неверный секрет вебхука MAX.',
            ], 403);
        }

        $storedFile = $eventStore->storeRaw($request->getContent());

        return response()->json([
            'ok' => true,
            'event_id' => $storedFile->id,
        ]);
    }
}
