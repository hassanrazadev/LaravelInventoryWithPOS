<?php

namespace App\Utils;

class AppUtils
{
    /**
     * @return string
     */
    public static function inputFieldError($errors, $fieldName)
    {
        if ($errors->has($fieldName)) {
            return 'is-invalid';
        }

        return '';
    }

    /**
     * @return string
     */
    public static function formGroupError($errors, $fieldName)
    {
        if ($errors->has($fieldName)) {
            return 'has-error';
        }

        return '';
    }

    /**
     * @return bool|mixed|string
     */
    public static function base64ToImage($image)
    {
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $image = base64_decode($image);

        return $image;
    }

    /**
     * Extract up to two uppercase initials from a display name.
     */
    public static function initials(?string $name): string
    {
        $parts = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);
        if (! $parts) {
            return 'U';
        }

        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }

        return $initials;
    }
}
