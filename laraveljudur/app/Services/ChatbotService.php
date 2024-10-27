<?php

namespace App\Services;

use App\Models\ChatLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ChatbotService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('OPENAI_API_KEY'); 
    }
    public function getChatbotResponse($message)
    {
        try {
            $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo', 
                    'messages' => [
                        ['role' => 'user', 'content' => $message],
                    ],
                ],
            ]);
    
            $responseBody = json_decode($response->getBody()->getContents(), true);
            
            if (isset($responseBody['choices'][0]['message']['content'])) {
                $botResponse = $responseBody['choices'][0]['message']['content'];
    
                ChatLog::create([
                    'user_message' => $message,
                    'bot_response' => $botResponse,
                ]);
    
                return $botResponse;
            } else {
                return 'No valid response from the chatbot.';
            }
        } catch (RequestException $e) {
            return 'Sorry, I could not process your request at this moment.';
        }
    }
    
}
