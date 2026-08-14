<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ChatGptService
{
    public function getMessage($prompt)
    {
        $maxAttempts = 3; // 1 initial + 2 retries
        
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . config('services.openai.key'),
            ])->post(config('services.openai.url'), [
                "model" => "gpt-3.5-turbo",
                "messages" => [
                    [
                        "role" => "user",
                        "content" => $prompt,
                    ],
                ],
                "temperature" => 0.5,
                "max_tokens" => 1500,
            ]);

            $data = $response->json();

            if (isset($data['error'])) {
                continue;
            }

            $content = $data['choices'][0]['message']['content'] ?? '';

            // Responses that should trigger a retry
            $retryPhrases = [
                "I'm unable to browse the internet",
                "I can't browse the internet",
                "I do not have real-time access",
                "I don't have access to real-time",
                "I recommend checking",
                "prices may have changed"
            ];

            $shouldRetry = false;

            foreach ($retryPhrases as $phrase) {
                if (stripos($content, $phrase) !== false) {
                    $shouldRetry = true;
                    break;
                }
            }


            if (!$shouldRetry) {
                return [
                    'status' => true,
                    'content' => $content,
                ];
            }

            // Wait before retrying
            sleep(1);
        }

        return [
            'status' => false,
            'message' => 'Unable to get a valid response after 3 attempts.',
        ];
    }
}
