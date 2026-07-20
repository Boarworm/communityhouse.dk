<?php

namespace Boarworm\AiTranslate\Classes;

use Exception;

class TranslateManager
{
    protected string $providerName;
    protected ?\Boarworm\AiTranslate\Classes\Providers\AiProviderInterface $provider = null;

    public function __construct()
    {
        $settings = \Boarworm\AiTranslate\Models\Setting::instance();
        $this->providerName = $settings->provider ?: 'claude';

        if ($this->providerName === 'claude') {
            $this->provider = new \Boarworm\AiTranslate\Classes\Providers\ClaudeProvider(
                $settings->claude_api_key ?? '',
                $settings->claude_model ?: 'claude-opus-4-5-20251101'
            );
        } elseif ($this->providerName === 'chatgpt') {
            $this->provider = new \Boarworm\AiTranslate\Classes\Providers\ChatGPTProvider(
                $settings->chatgpt_api_key ?? '',
                $settings->chatgpt_model ?: 'gpt-5.2-2025-12-11'
            );
        }
    }

    public function translate(array $data, string $sourceLang, string $targetLang): array
    {
        if (!$this->provider) {
            throw new Exception("AI Provider not configured correctly.");
        }
        if (empty($data)) {
            return $data;
        }
        return $this->provider->translate($data, $sourceLang, $targetLang);
    }
}
