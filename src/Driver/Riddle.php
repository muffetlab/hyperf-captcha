<?php

declare(strict_types=1);

/**
 * Riddle captcha class.
 *
 * @author     Loong <loong2460@gmail.com>
 * @copyright  (c) 2021 Muffet Laboratory
 * @license    MIT License
 */

namespace Muffetlab\Captcha\Driver;

use Muffetlab\Captcha\Captcha;

class Riddle extends Captcha
{
    /**
     * @var string Captcha riddle
     */
    private $riddle;

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
     * @return mixed
     */
    public function render()
    {
        return $this->riddle;
    }

}
