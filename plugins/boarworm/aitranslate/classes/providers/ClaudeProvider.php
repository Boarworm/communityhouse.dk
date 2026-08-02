<?php
namespace Boarworm\AiTranslate\Classes\Providers;

use Illuminate\Support\Facades\Http;
use Exception;

class ClaudeProvider implements AiProviderInterface
{
    protected string $apiKey;
    protected string $model;

    public function __construct(string $apiKey, string $model)
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
    }

    public function translate(array $data, string $sourceLang, string $targetLang): array
    {
        set_time_limit(600);

        if (!$this->apiKey) {
            throw new Exception("Claude API Key not found.");
        }

        $response = Http::timeout(600)->withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => $this->model,
            'max_tokens' => 30000,
            'system' => 'You are a professional translator. Return ONLY valid JSON.',
            'messages' => [['role' => 'user', 'content' => $this->buildPrompt($data, $sourceLang, $targetLang)]],
            'temperature' => 0,
        ]);

        if (!$response->ok()) {
            trace_log($response->body());
            throw new Exception("Claude API Error: " . $response->body());
        }

        return $this->parseResponse($response->json());
    }

    protected function buildPrompt($data, $source, $target)
    {
        return "You are a rigid translation engine. Translate the JSON values below from '{$source}' to '{$target}'.\n" .
               "STRICT RULES:\n" .
               "1. You MUST translate the text. Do NOT just copy the '{$source}' text.\n" .
               "2. If the text appears to be already in '{$target}', paraphrase it slightly to ensure it is valid '{$target}'.\n" .
               "3. Preserve all HTML tags (like <p>, <strong>, <a>) EXACTLY. Do not translate class names or attributes.\n" .
               "4. Keep JSON keys exactly as they are.\n" .
               "5. Return ONLY valid JSON. No markdown formatting.\n" .
               "6. The 'slug' field should be translated to '{$target}'\n" .
               "7. If any double quote character (\") appears inside a string value (including inside HTML content), it MUST be escaped as \\\" so the JSON remains valid. Do not replace it with a different character.\n" .
               "8. Before responding, verify your output is valid, parseable JSON.\n\n" .
               "Input Data:\n" . json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    protected function parseResponse($result)
    {
        $stopReason = $result['stop_reason'] ?? null;
        $rawText = $result['content'][0]['text'] ?? '{}';
        $jsonText = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($rawText));
        $decoded = json_decode($jsonText, true);

        if ($decoded === null) {
            throw new Exception("Claude API returned invalid JSON" . ($stopReason === 'max_tokens' ? ' (response was truncated, max_tokens reached)' : '') . ".");
        }

        return $decoded;
    }
}
