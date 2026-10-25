<?php

namespace App\Services;

class TermsAndConditions
{
    public static function getContent(): array
    {
        $termsPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'B2B Wholesale Terms & Conditions.txt';
        $lines = file($termsPath, FILE_IGNORE_NEW_LINES);
        if ($lines === false || count($lines) < 3) {
            throw new \RuntimeException('Unable to load the wholesale terms and conditions.');
        }

        $title = trim(array_shift($lines));
        $introduction = trim(array_shift($lines));
        $sections = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if (preg_match('/^(\d+)\.\s+(.+)$/u', $line, $matches)) {
                $sections[] = [
                    'number' => (int)$matches[1],
                    'title' => trim($matches[2]),
                    'paragraphs' => []
                ];
                continue;
            }

            if (empty($sections)) {
                throw new \RuntimeException('The wholesale terms content has no numbered sections.');
            }
            $sections[count($sections) - 1]['paragraphs'][] = $line;
        }

        return [
            'title' => $title,
            'introduction' => $introduction,
            'sections' => $sections
        ];
    }
}
