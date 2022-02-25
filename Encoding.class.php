<?php

/*
 * Klasa sprawdza konwertuje tekst z polskimi znakami diakrytycznymi zakodowany w windows-1250 lub iso-8859-2 na utf-8
 */

class Encoding {

    const UTF = 'UTF-8';
	const ISO = 'ISO-8859-2';
	const WIN = 'WINDOWS-1250';

    // polskie znaki diakrytyczne różnie kodowane dla iso-8859-2 i windows-1250: ąĄśŚźŹ
    const ISO_SPECIFIC = ["\xb1", "\xa1", "\xb6", "\xa6", "\xbc", "\xac"];
	const WIN_SPECIFIC = ["\xb9", "\xa5", "\x9c", "\x8c", "\x9f", "\x8f"];

    public static function toUTF8(string $text): string {

        // mapa polskich znaków windows-1250 na iso-8859-2
        $win_to_iso = array(
            "\xb9" => "\xb1",
            "\xa5" => "\xa1",
            "\x9c" => "\xb6",
            "\x8c" => "\xa6",
            "\x9f" => "\xbc",
            "\x8f" => "\xac"
        );

        // tylko unikalne znaki do tablicy
        $chars = str_split(count_chars($text, 3));

        // sprawdzamy czy mamy znaki typowe dla danego kodowania
        if (!array_diff(self::ISO_SPECIFIC, $chars)) {
            $encoding = self::ISO;
        } else if (!array_diff(self::WIN_SPECIFIC, $chars)) {
            $encoding = self::WIN;
        } else {
            $encoding = self::UTF;
        }

        // nie ma potrzeby konwersji jesli tekst juz jest w utf-8
        if ($encoding == self::UTF) {
            return $text;
        }

        /*
         * w przypadku gdy iconv jest dostępna - konwertujemy za jej pomoca do utf-8
         * w przypadku gdy iconv jest niedostepna - konwertujemy polskie znaki z windows-1250 na polskie znaki z iso-8859-2
         * kodowanie windows-1250 nie jest obsługiwane przez mb_convert_decoding
         * kodowanie iso-8859-2 jest obslugiwane przez mb_convert_decoding
         */
        if (!function_exists('iconv')) {
            if (!function_exists('mb_convert_encoding')) {
                throw new Exception('Funkcja iconv lub mb_convert_encoding jest niedostępna');
            }

            // konwertujemy tylko w przypadku tekstu w windows-1250 i braku iconv
            if ($encoding == self::WIN) {
                // mapowanie znakow za pomoca str translate
                return mb_convert_encoding(strtr($text, $win_to_iso), self::UTF, self::ISO);
            }
        }

        return iconv($encoding, self::UTF, $text);
    }
}
