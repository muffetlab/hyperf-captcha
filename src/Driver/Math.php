<?php

declare(strict_types=1);

/**
 * Math captcha class.
 *
 * @author     Loong <loong2460@gmail.com>
 * @copyright  (c) 2021 Muffet Laboratory
 * @license    MIT License
 */

namespace Muffetlab\Captcha\Driver;

use Muffetlab\Captcha\Captcha;

class Math extends Captcha
{
    /**
     * @var string Captcha math exercise
     */
    private $mathExercise;

    /**
     * Generates a new Captcha challenge.
     *
     * @return string The challenge answer
     */
    public function generateChallenge(): string
    {
        // Easy
        if (Captcha::$config['complexity'] < 4) {
            $numbers[] = mt_rand(1, 5);
            $numbers[] = mt_rand(1, 4);
        }
        // Normal
        elseif (Captcha::$config['complexity'] < 7) {
            $numbers[] = mt_rand(10, 20);
            $numbers[] = mt_rand(1, 10);
        }
        // Difficult, well, not really ;)
        else {
            $numbers[] = mt_rand(100, 200);
            $numbers[] = mt_rand(10, 20);
            $numbers[] = mt_rand(1, 10);
        }

        // Store the question for output
        $this->mathExercise = implode(' + ', $numbers) . ' = ';

        // Return the answer
        $this->response = array_sum($numbers);

        return $this->response;
    }

    /**
     * Outputs the Captcha riddle.
     *
     * @return mixed
     */
    public function render()
    {
        return $this->mathExercise;
    }

}
