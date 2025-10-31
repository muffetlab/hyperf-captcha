<?php

declare(strict_types=1);

/**
 * Black captcha class.
 *
 * @author     Loong <loong2460@gmail.com>
 * @copyright  (c) 2021 Muffet Laboratory
 * @license    MIT License
 */

namespace Muffetlab\Captcha\Driver;

use Symfony\Component\String\ByteString;
use Muffetlab\Captcha\Captcha;

class Black extends Captcha
{
    /**
     * Generates a new Captcha challenge.
     *
     * @return string The challenge answer
     */
    public function generateChallenge(): string
    {
        // Complexity setting is used as character count
        $this->response = (string) ByteString::fromRandom(max(1, intval(Captcha::$config['complexity'] / 1.5)), '2345679ACDEFHJKLMNPRSTUVWXYZ');

        return $this->response;
    }

    /**
     * Outputs the Captcha image.
     *
     * @return mixed
     */
    public function render()
    {
        // Creates a black image to start from
        $this->imageCreate(Captcha::$config['background']);

        // Add random white/gray arcs, amount depends on complexity setting
        $count = (Captcha::$config['width'] + Captcha::$config['height']) / 2;
        $count = $count / 5 * min(10, Captcha::$config['complexity']);
        for ($i = 0; $i < $count; $i++) {
            imagesetthickness($this->image, mt_rand(1, 2));
            $color = imagecolorallocatealpha($this->image, 255, 255, 255, mt_rand(0, 120));
            imagearc($this->image, mt_rand(-Captcha::$config['width'], Captcha::$config['width']), mt_rand(-Captcha::$config['height'], Captcha::$config['height']), mt_rand(-Captcha::$config['width'], Captcha::$config['width']), mt_rand(-Captcha::$config['height'], Captcha::$config['height']), mt_rand(0, 360), mt_rand(0, 360), $color);
        }

        // Use different fonts if available
        $font = Captcha::$config['fontpath'] . Captcha::$config['fonts'][array_rand(Captcha::$config['fonts'])];

        // Draw the character's white shadows
        $size = (int) min(Captcha::$config['height'] / 2, Captcha::$config['width'] * 0.8 / mb_strlen($this->response));
        $angle = mt_rand(-15 + mb_strlen($this->response), 15 - mb_strlen($this->response));
        $x = mt_rand(1, intval(Captcha::$config['width'] * 0.9) - $size * mb_strlen($this->response));
        $y = intdiv((Captcha::$config['height'] - $size), 2) + $size;
        $color = imagecolorallocate($this->image, 255, 255, 255);
        imagefttext($this->image, $size, $angle, $x + 1, $y + 1, $color, $font, $this->response);

        // Add more shadows for lower complexities
        (Captcha::$config['complexity'] < 10) and imagefttext($this->image, $size, $angle, $x - 1, $y - 1, $color, $font, $this->response);
        (Captcha::$config['complexity'] < 8) and imagefttext($this->image, $size, $angle, $x - 2, $y + 2, $color, $font, $this->response);
        (Captcha::$config['complexity'] < 6) and imagefttext($this->image, $size, $angle, $x + 2, $y - 2, $color, $font, $this->response);
        (Captcha::$config['complexity'] < 4) and imagefttext($this->image, $size, $angle, $x + 3, $y + 3, $color, $font, $this->response);
        (Captcha::$config['complexity'] < 2) and imagefttext($this->image, $size, $angle, $x - 3, $y - 3, $color, $font, $this->response);

        // Finally draw the foreground characters
        $color = imagecolorallocate($this->image, 0, 0, 0);
        imagefttext($this->image, $size, $angle, $x, $y, $color, $font, $this->response);

        // Output
        return $this->imageRender();
    }

}
