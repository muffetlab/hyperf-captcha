<?php

declare(strict_types=1);

namespace Muffetlab\Captcha\Driver;

use Muffetlab\Captcha\Captcha;

/**
 * Riddle captcha class.
 *
 * @author     Loong <loong2460@gmail.com>
 * @copyright  (c) 2021 Muffet Laboratory
 * @license    MIT License
 */
class Riddle extends Captcha
{
    /**
     * @var string Captcha riddle
     */
    private string $riddle;

    /**
     * Generates a new Captcha challenge.
     *
     * @return string The challenge answer
     */
    public function generateChallenge(): string
    {
        // Load riddles from the current language
        $riddles = config('captcha.riddles');

        // Pick a random riddle
        $riddle = $riddles[array_rand($riddles)];

        // Store the question for output
        $this->riddle = $riddle[0];

        // Return the answer
        $this->response = (string) $riddle[1];

        return $this->response;
    }

    /**
     * Outputs the Captcha riddle.
     *
     * @return string
     */
    public function render(): string
    {
        return $this->riddle;
    }

}
