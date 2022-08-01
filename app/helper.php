<?php

if (!function_exists('func_str_to_upper_utf8')) {
    function func_str_to_upper_utf8($text)
    {
        if (is_null($text)) {
            return null;
        }
        return mb_strtoupper($text, 'utf-8');
    }
}

if (!function_exists('func_str_to_lower_utf8')) {
    function func_str_to_lower_utf8($text)
    {
        if (is_null($text)) {
            return null;
        }
        return mb_strtolower($text, 'utf-8');
    }
}

if (!function_exists('func_filter_items')) {
    function func_filter_items($query, $text)
    {
        $text_array = explode(' ', $text);
        foreach ($text_array as $txt) {
            $trim_txt = trim($txt);
            $query->where('text_filter', 'like', "%$trim_txt%");
        }

        return $query;
    }
}

if (!function_exists('func_pdf_get_lots')) {
    function func_pdf_get_lots($lots)
    {
        $lots_sale = [];
        foreach($lots as $index => $lot){
            if( isset($lot->has_sale) && $lot->has_sale) {
                $lots_sale[] = $lot->series;
            }
        }
        $array_chunks = array_chunk($lots_sale, 4);
        $text = [];
        foreach ($array_chunks as $chunks) {
            $text[] = implode(', ', $chunks);
        }

        return implode(',<br/>', $text);
    }
}
