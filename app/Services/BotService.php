<?php

namespace App\Services;

class BotService
{
    protected $intents;

    public function __construct()
    {
        $json = file_get_contents(storage_path('app/intents1.json'));
        $this->intents = json_decode($json, true)['intents'];
    }

    public function getReply(string $message): array
    {
        $message = trim($message);

        // Auto-trigger main menu without typing
        if (strtolower($message) === 'start' || $message === 'Main Menu') {
            $intent = $this->findByTag("main_menu");
            return [
                "type" => "menu",
                "message" => $intent['message'],
                "options" => $intent['options']
            ];
        }

        // If it's a submenu tag
        $intent = $this->findByTag($message);
        if ($intent && $intent['type'] === 'submenu') {
            return [
                "type" => "submenu",
                "message" => "Please choose a question:",
                "options" => $intent['options']
            ];
        }

        // If it's a final answer
        if ($intent && $intent['type'] === 'answer') {
            return [
                "type" => "answer",
                "message" => $intent['response'],
                "showSupport" => true
            ];
        }

        // Fallback
        return [
            "type" => "fallback",
            "message" => "Sorry, I didn’t understand that.",
            "showSupport" => true
        ];
    }

    private function findByTag($tag)
    {
        foreach ($this->intents as $intent) {
            if (strcasecmp($intent['tag'], $tag) === 0) {
                return $intent;
            }
        }
        return null;
    }
}
