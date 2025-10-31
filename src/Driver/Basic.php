<?php

declare(strict_types=1);

/**
 * Basic captcha class.
 *
 * @author     Loong <loong2460@gmail.com>
 * @copyright  (c) 2021 Muffet Laboratory
 * @license    MIT License
 */

namespace Muffetlab\Captcha\Driver;

use Symfony\Component\String\ByteString;
use Muffetlab\Captcha\Captcha;

class Basic extends Captcha
{
    /**
     * Generates a new Captcha challenge.
     *
     * @return string The challenge answer
     */
    public function generateChallenge(): string
    {
        // Complexity setting is used as character count
        $this->response = (string) ByteString::fromRandom(max(1, Captcha::$config['complexity']), '2345679ACDEFHJKLMNPRSTUVWXYZ');

        return $this->response;
    }

    /**
     * Outputs the Captcha image.
     *
     * @return object
     */
    public function render(): object
    {
        // Creates $this->image
        $this->imageCreate(Captcha::$config['background']);

        // Add a random gradient
        if (empty(Captcha::$config['background'])) {
            $color1 = imagecolorallocate($this->image, mt_rand(200, 255), mt_rand(200, 255), mt_rand(150, 255));
            $color2 = imagecolorallocate($this->image, mt_rand(200, 255), mt_rand(200, 255), mt_rand(150, 255));
            $this->imageGradient($color1, $color2);
        }

        // Add a few random lines
        for ($i = 0, $count = mt_rand(5, Captcha::$config['complexity'] * 4); $i < $count; $i++) {
            $color = imagecolorallocatealpha($this->image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(100, 255), mt_rand(50, 120));
            imageline($this->image, mt_rand(0, Captcha::$config['width']), 0, mt_rand(0, Captcha::$config['width']), Captcha::$config['height'], $color);
        }

        // Calculate character font-size and spacing
        $defaultSize = min(Captcha::$config['width'], Captcha::$config['height'] * 2) / (mb_strlen($this->response) + 1);
        $spacing = (int) (Captcha::$config['width'] * 0.9 / mb_strlen($this->response));

        // Draw each Captcha character with varying attributes
        for ($i = 0, $strlen = mb_strlen($this->response); $i < $strlen; $i++) {
            // Use different fonts if available
            $font = Captcha::$config['fontpath'] . Captcha::$config['fonts'][array_rand(Captcha::$config['fonts'])];

            // Allocate random color, size and rotation attributes to text
            $color = imagecolorallocate($this->image, mt_rand(0, 150), mt_rand(0, 150), mt_rand(0, 150));
            $angle = mt_rand(-40, 20);

            // Scale the character size on image height
            $size = $defaultSize / 10 * mt_rand(8, 12);
            $box = imageftbbox($size, $angle, $font, mb_substr($this->response, $i, 1));

            // Calculate character starting coordinates
            $x = intdiv($spacing, 4) + $i * $spacing;
            $y = intdiv(Captcha::$config['height'], 2) + intdiv($box[2] - $box[5], 4);

            // Write text character to image
            imagefttext($this->image, $size, $angle, $x, $y, $color, $font, mb_substr($this->response, $i, 1));
        }

        // Output
        return $this->imageRender();
    }

}
