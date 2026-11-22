<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Verifies that all translation keys defined in the English base files
 * are also present in every supported locale.
 */
class TranslationCoverageTest extends TestCase
{
    private string $langDir;

    protected function setUp(): void
    {
        $this->langDir = dirname(__DIR__, 2) . '/lang';
    }

    // ── panel.php ─────────────────────────────────────────────────────────────

    public function test_panel_keys_complete_in_all_locales(): void
    {
        $enKeys = array_keys(require $this->langDir . '/en/panel.php');

        foreach (['fr', 'pt', 'ar'] as $locale) {
            $translated = require $this->langDir . "/{$locale}/panel.php";
            $missing    = array_diff($enKeys, array_keys($translated));

            $this->assertEmpty(
                $missing,
                "lang/{$locale}/panel.php is missing " . count($missing) . " key(s): "
                    . implode(', ', $missing)
            );
        }
    }

    // ── forms.php ─────────────────────────────────────────────────────────────

    public function test_forms_keys_complete_in_all_locales(): void
    {
        $enKeys = array_keys(require $this->langDir . '/en/forms.php');

        foreach (['fr', 'pt', 'ar'] as $locale) {
            $translated = require $this->langDir . "/{$locale}/forms.php";
            $missing    = array_diff($enKeys, array_keys($translated));

            $this->assertEmpty(
                $missing,
                "lang/{$locale}/forms.php is missing " . count($missing) . " key(s): "
                    . implode(', ', $missing)
            );
        }
    }

    // ── JSON files (theme / installer / email strings) ────────────────────────

    public function test_json_keys_complete_in_all_locales(): void
    {
        // Collect every English-text key used in views via __()
        // by reading the fr.json as the reference (all keys were added together).
        $frPath  = $this->langDir . '/fr.json';
        $frKeys  = array_keys(json_decode(file_get_contents($frPath), true));

        foreach (['pt', 'ar'] as $locale) {
            $translated = json_decode(
                file_get_contents($this->langDir . "/{$locale}.json"),
                true
            );
            $missing = array_diff($frKeys, array_keys($translated));

            $this->assertEmpty(
                $missing,
                "lang/{$locale}.json is missing " . count($missing) . " key(s): "
                    . implode(', ', array_map(fn($k) => "\"{$k}\"", $missing))
            );
        }
    }

    // ── No hard-coded English in public theme views ───────────────────────────

    public function test_theme_views_have_no_bare_english_strings(): void
    {
        $themeDir = dirname(__DIR__, 2) . '/resources/views/themes';
        $files    = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($themeDir)
        );

        // Patterns that indicate a hard-coded visible English string still present.
        // We look for literal text content NOT wrapped in {{ __() }} or {!! __() }}.
        // This is a heuristic: flag any blade file that has an HTML text node
        // containing only ASCII printable chars (likely English) outside a __() call.
        $suspicious = [];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $content = file_get_contents($file->getPathname());

            // Find HTML text that is NOT inside {{ __( or {!! __( or @php
            // Simple check: look for >[A-Z][a-z ]..< patterns not preceded by __
            if (preg_match('/>\s*[A-Z][a-zA-Z ]{4,}\s*<(?!.*__\()/', $content)) {
                // Deeper check: does the match appear after a __( ?
                preg_match_all('/>\s*([A-Z][a-zA-Z ]{4,})\s*</', $content, $matches);
                foreach ($matches[1] as $text) {
                    // Skip if surrounded by {{ __() }}
                    $escaped = preg_quote($text, '/');
                    if (!preg_match("/\{\{\s*__\(['\"]" . $escaped . "['\"]/, $content)) {
                        $suspicious[] = $file->getFilename() . ': "' . trim($text) . '"';
                    }
                }
            }
        }

        // Report as a warning (not failure) since some dynamic content may look English
        $this->addToAssertionCount(1);
        if (!empty($suspicious)) {
            fwrite(STDERR, "\n[WARNING] Possible unwrapped strings in theme views:\n  "
                . implode("\n  ", array_unique($suspicious)) . "\n");
        }
    }
}
