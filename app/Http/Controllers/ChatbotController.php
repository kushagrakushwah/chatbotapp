<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BotService;

class ChatbotController extends Controller
{
    public function reply(Request $request, BotService $bot)
    {
        $userMessage = $request->input('message', '');
        $reply = $bot->getReply($userMessage);

        return response()->json([
            'message' => $reply['message'],
            'options' => $reply['options'] ?? [],
            'type' => $reply['type'] ?? null,
            'isLastAnswer' => $reply['type'] === 'answer'
        ]);
    }
}
