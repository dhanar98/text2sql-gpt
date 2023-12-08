<?php

namespace App\Http\Controllers;

use App\Traits\DatabaseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use stdClass;

class ChatGPTController extends Controller
{
    use DatabaseTrait;

    public function welcome()
    {
        try {
            $fetchedDbTableNames = $this->getAllTableNames();
            if (count($fetchedDbTableNames) === 0) {
                return view('error');
            }

            return view('welcome', ['fetchedDbTableNames' => $fetchedDbTableNames]);
        } catch (\Exception $e) {
            Log::error("Welcome Page ====> {$e->getMessage()}");
        }
    }

    public function sqlCreateStatementFromDb(Request $request)
    {
        try {

            $tableName = $request->input('tableName');
            $tableSchema = $this->getCreateSqlStatement($tableName);

            Log::debug("translatedTextToSQL ===> {$tableSchema}");

            $result = new stdClass;
            $result->fetchedSqlStatement = $tableSchema ? $tableSchema : '';

            return response()->json([
                'status' => 'SUCCESS',
                'data' => $result,
                'message' => 'CREATED QUERY ADDED.',
            ]);
        } catch (\Exception $e) {
            Log::error("sqlCreateStatementFromDb ===> {$e->getMessage()} ");

            return response()->json([
                'status' => 'ERROR',
                'data' => [],
                'message' => 'database error',
            ], 400);
        }
    }

    public function sqlQueryFromChatGpt(Request $request)
    {
        try {

            $humanPrompt = $request->input('humanPrompt');
            $createSqlStatement = $request->input('createSqlStatement');

            Log::debug("Human Prompt: {$humanPrompt}");
            Log::debug("Create SQL Statement: {$createSqlStatement}");

            if (is_null($humanPrompt)) {
                return response()->json([
                    'status' => 'ERROR',
                    'data' => [],
                    'message' => 'Human Prompt Needed',
                ], 422);
            }

            $translatedTextToSQL = $this->translateToSQL($humanPrompt, $createSqlStatement);
            Log::debug("translatedTextToSQL ===> {$translatedTextToSQL}");

            $result = new stdClass;
            $result->translatedTextToSQL = $translatedTextToSQL ? $translatedTextToSQL : '';

            return response()->json([
                'status' => 'SUCCESS',
                'data' => $result,
                'message' => 'Text Converted To SQL',
            ], 200);

        } catch (\Exception $e) {
            Log::error("sqlQueryFromChatGpt ===> {$e->getMessage()}");

            // Check if the exception is a 429 Too Many Requests error
            if ($e instanceof \GuzzleHttp\Exception\ClientException && $e->getCode() == 429) {
                return response()->json([
                    'status' => 'ERROR',
                    'data' => [],
                    'message' => 'Too Many Requests. Please try again later.',
                ], 429);
            }

            return response()->json([
                'status' => 'ERROR',
                'data' => [],
                'message' => 'chatgpt error',
            ], 400);
        }
    }

    private function translateToSQL(string $query, ?string $tableSchema = '')
    {

        $prompt = "Translate this natural language query into SQL without changing the case of the entries given by me:\n\n\"$query\"\n\n".($tableSchema ? "Use this table schema:\n\n$tableSchema\n\n" : '').'SQL Query:';

        $client = new \GuzzleHttp\Client;
        $response = $client->post('https://api.openai.com/v1/completions', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.config('chatgpt.chatgpt_api_key'),
            ],
            'json' => [
                'prompt' => $prompt,
                'temperature' => 0.5,
                'max_tokens' => 2048,
                'n' => 1,
                'stop' => '\n',
                'model' => 'text-davinci-003',
                'frequency_penalty' => 0.5,
                'presence_penalty' => 0.5,
                'logprobs' => 10,
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        Log::debug('RESPONSE FROM CHATGPT ======> '.json_encode($data, 128));

        if ($response->getStatusCode() !== 200) {
            throw new \Exception($data['error'] ?? 'Error translating to SQL.');
        }

        return trim($data['choices'][0]['text']);
    }
}
