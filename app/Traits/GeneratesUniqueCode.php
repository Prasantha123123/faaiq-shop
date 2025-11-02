<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait GeneratesUniqueCode
{
    /**
     * Generate a unique alphanumeric code (contains letters and digits).
     *
     * @param int $length Length of the unique code.
     * @return string
     */
    public function generateUniqueCode($length = 12)
    {
        $length = max(2, (int) $length); // ensure minimum length 2 to allow at least one letter and one digit
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $maxIndex = strlen($chars) - 1;

        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $chars[random_int(0, $maxIndex)];
            }
            $hasLetter = (bool) preg_match('/[A-Z]/', $code);
            $hasDigit  = (bool) preg_match('/\d/', $code);
            // Loop until we have at least one letter and one digit
        } while (! $hasLetter || ! $hasDigit);

        return $code;
    }
}
