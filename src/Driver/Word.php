<?php

declare(strict_types=1);

namespace Muffetlab\Captcha\Driver;

use Muffetlab\Captcha\Captcha;

/**
 * Word captcha class.
 *
 * @author     Loong <loong2460@gmail.com>
 * @copyright  (c) 2021 Muffet Laboratory
 * @license    MIT License
 */
class Word extends Basic
{
    /**
     * Generates a new Captcha challenge.
     *
     * @return string The challenge answer
     */
    public function generateChallenge(): string
    {
        // Load words from the current language and randomize them
        $words = config('captcha.words');
        shuffle($words);

        // Loop over each word...
        foreach ($words as $word) {
            // ...until we find one of the desired length
            if (abs(Captcha::$config['complexity'] - mb_strlen($word)) < 2) {
                $this->response = mb_strtoupper($word);

                return $this->response;
            }
        }

        // Return any random word as final fallback
        $this->response = mb_strtoupper($words[array_rand($words)]);

        return $this->response;
    }

}
