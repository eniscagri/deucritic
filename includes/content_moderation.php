<?php

if (!function_exists('dcFindProhibitedContent')) {
    function dcFindProhibitedContent(string $text): ?string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = strtr($text, [
            'ç' => 'c',
            'ğ' => 'g',
            'ı' => 'i',
            'ö' => 'o',
            'ü' => 'u',
            'İ' => 'i',
            '0' => 'o',
            '1' => 'i',
            '3' => 'e',
            '4' => 'a',
            '5' => 's',
            '7' => 't',
            '@' => 'a',
            '$' => 's',
        ]);

        if (preg_match('/(?:^|[^\p{L}\p{N}])(?:\+[\s-]*18|18[\s-]*\+)(?:$|[^\p{L}\p{N}])/u', $text)) {
            return '+18';
        }

        $prohibited = [
            'hakaret' => ['amk', 'aq', 'amina', 'anan', 'orospu', 'pic', 'yarrak', 'sik', 'got', 'ibne', 'pezevenk', 'kahpe', 'fuck', 'shit', 'bitch', 'bastard', 'dick', 'pussy', 'cunt', 'slut', 'whore', 'asshole', 'motherfucker'],
            '+18' => ['porno', 'porn', 'pornhub', 'onlyfans', 'seks', 'sex', 'sexual', 'erotik', 'erotica', 'nude', 'nudes', 'naked', 'xxx', 'hentai', 'blowjob', 'handjob', 'cumshot', 'deepthroat', 'escort'],
        ];
        $separator = '[\s\p{P}\p{S}]*';
        foreach ($prohibited as $category => $terms) {
            foreach ($terms as $term) {
                if (strlen($term) < 3) {
                    continue;
                }

                $pattern = implode($separator, str_split($term));
                if (preg_match('/(?<![\p{L}\p{N}])' . $pattern . '(?![\p{L}\p{N}])/u', $text)) {
                    return $category;
                }
            }
        }

        preg_match_all('/[\p{L}\p{N}]+/u', $text, $matches);
        $suffixes = ['lar', 'ler', 'larim', 'lerim', 'siniz', 'sun', 'sin', 'dir', 'tir', 'er', 'ar', 'erim', 'arim', 'eyim', 'ayim', 'eriz', 'ti', 'di', 'mis', 'mus', 'me', 'ma', 'im', 'in', 'i', 'iyor', 'acak', 'ecek', 'ing', 'ed', 'ers'];

        foreach ($matches[0] as $token) {
            foreach ($prohibited as $category => $terms) {
                foreach ($terms as $term) {
                    if ($token === $term) {
                        return $category;
                    }

                    if (strpos($token, $term) === 0) {
                        $suffix = substr($token, strlen($term));
                        if (in_array($suffix, $suffixes, true)) {
                            return $category;
                        }
                    }
                }
            }
        }

        return null;
    }
}
