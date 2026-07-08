<?php

namespace App\Helpers;

class SecurityHelper
{
    /**
     * Membersihkan input Rich Text dari XSS
     * Menggunakan HTML Purifier jika terinstal, jika tidak menggunakan strip_tags sebagai fallback darurat.
     */
    public static function cleanRichText($html)
    {
        if (empty($html)) return $html;

        if (function_exists('clean')) {
            return clean($html); // Menggunakan mews/purifier
        }

        // Fallback jika belum run composer update
        $allowedTags = '<p><br><b><strong><i><em><u><ul><ol><li><a><span><h1><h2><h3><h4><h5><h6><img>';
        return strip_tags($html, $allowedTags);
    }
}
