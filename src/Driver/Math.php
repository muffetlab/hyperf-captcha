<?php

declare(strict_types=1);

/**
 * Math captcha class.
 *
 * @author     Tinsh <kilofox2000@gmail.com>
 * @copyright  (c) 2021 Kilofox Studio
 * @license    MIT License
 */

namespace Kilofox\Captcha\Driver;

use Kilofox\Captcha\Captcha;

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
    public function generateChallenge()
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
