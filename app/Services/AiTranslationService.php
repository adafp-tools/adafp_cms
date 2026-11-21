<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiTranslationService
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const MODEL   = 'claude-sonnet-4-6';

    private string $apiKey;

    public function __construct()
    {
        $encrypted = config('cms.ai_api_key');

        if (! $encrypted) {
            throw new \RuntimeException('AI translation API key is not configured.');
        }

        $this->apiKey = decrypt($encrypted);
    }

    /**
     * Translate $text into $targetLocale, using $sourceLocale as the source.
     * Returns the translated string.
     */
    public function translate(string $text, string $targetLocale, string $sourceLocale = 'en'): string
    {
        $langName = $this->localeName($targetLocale);

        $response = Http::withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->post(self::API_URL, [
            'model'      => self::MODEL,
            'max_tokens' => 4096,
            'messages'   => [
                [
                    'role'    => 'user',
                    'content' => $this->buildPrompt($text, $langName, $sourceLocale),
                ],
            ],
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('AI translation request failed: ' . $response->body());
        }

        return trim($response->json('content.0.text', ''));
    }

    private function buildPrompt(string $text, string $targetLanguage, string $sourceLocale): string
    {
        return <<<PROMPT
You are a professional political translator. Translate the following text into {$targetLanguage}.
Preserve HTML tags exactly as they are. Return only the translated text — no explanation, no introduction.

Source locale: {$sourceLocale}

Text to translate:
{$text}
PROMPT;
    }

    private function localeName(string $locale): string
    {
        return match ($locale) {
            'ar' => 'Arabic',
            'fr' => 'French',
            'en' => 'English',
            default => $locale,
        };
    }
}
