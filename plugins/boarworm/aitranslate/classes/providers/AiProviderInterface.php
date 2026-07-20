<?php
namespace Boarworm\AiTranslate\Classes\Providers;

interface AiProviderInterface
{
    public function translate(array $data, string $sourceLang, string $targetLang): array;
}
