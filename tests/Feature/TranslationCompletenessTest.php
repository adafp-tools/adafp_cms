<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class TranslationCompletenessTest extends TestCase
{
    private const LOCALES = ['fr', 'pt', 'ar'];
    private const GROUPS = ['panel', 'forms', 'auth', 'pagination', 'passwords', 'validation'];
    private string $langBase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->langBase = dirname(__DIR__, 2) . '/lang';
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('phpGroupProvider')]
    public function test_php_group_has_all_en_keys(string $locale, string $group): void
    {
        $enFile  = "{$this->langBase}/en/{$group}.php";
        $locFile = "{$this->langBase}/{$locale}/{$group}.php";

        $this->assertFileExists($enFile, "EN source missing: lang/en/{$group}.php");
        $this->assertFileExists($locFile, "Translation missing: lang/{$locale}/{$group}.php");

        $en  = require $enFile;
        $loc = require $locFile;

        $missing = array_diff_key($en, $loc);
        $this->assertEmpty(
            $missing,
            "lang/{$locale}/{$group}.php is missing keys: " . implode(', ', array_keys($missing))
        );
    }

    public static function phpGroupProvider(): array
    {
        $rows = [];
        foreach (self::LOCALES as $locale) {
            foreach (self::GROUPS as $group) {
                $rows["{$locale}/{$group}"] = [$locale, $group];
            }
        }
        return $rows;
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('jsonLocaleProvider')]
    public function test_json_file_has_minimum_required_keys(string $locale): void
    {
        $jsonFile = "{$this->langBase}/{$locale}.json";
        $this->assertFileExists($jsonFile, "Translation JSON missing: lang/{$locale}.json");

        $data = json_decode(file_get_contents($jsonFile), true);
        $this->assertIsArray($data, "lang/{$locale}.json is not valid JSON");
        $this->assertNotEmpty($data, "lang/{$locale}.json is empty");

        // These keys are used in theme/installer/email views — must all be present
        $required = [
            'Skip to content',
            'Language',
            'Main menu',
            'Footer menu',
            'Read more',
            'Latest News',
            'Upcoming Events',
            'Leadership',
            'Download',
            'No documents published yet.',
            'News',
            'No posts published yet.',
            'Installation',
            'Requirements',
            'Database',
            'Site details',
            'Owner account',
            'Finalize',
            'Continue →',
            'Organisation name',
            'Default language',
            'Active languages',
            'Full name',
            'Email address',
            'Password',
            'Confirm password',
            'Installation complete',
            'Go to admin panel →',
        ];

        foreach ($required as $key) {
            $this->assertArrayHasKey(
                $key,
                $data,
                "lang/{$locale}.json missing required key: \"{$key}\""
            );
        }
    }

    public static function jsonLocaleProvider(): array
    {
        return array_map(fn($l) => [$l], self::LOCALES);
    }

    public function test_no_lrm_bidi_marks_in_ar_files(): void
    {
        $arFiles = glob($this->langBase . '/ar/*.php');
        $arFiles[] = $this->langBase . '/ar.json';

        foreach ($arFiles as $file) {
            if (!file_exists($file)) {
                continue;
            }
            $content = file_get_contents($file);
            $this->assertStringNotContainsString(
                "\xe2\x80\x8e",
                $content,
                basename($file) . ' contains U+200E LRM bidi marks'
            );
        }
    }

    public function test_no_adafp_string_in_lang_files(): void
    {
        $files = array_merge(
            glob($this->langBase . '/**/*.php') ?: [],
            glob($this->langBase . '/*.json') ?: []
        );

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $this->assertStringNotContainsString(
                'adafp',
                strtolower($content),
                basename($file) . ' contains "adafp" (neutrality violation)'
            );
        }
    }
}
