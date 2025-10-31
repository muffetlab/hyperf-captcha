<?php

declare(strict_types=1);

/**
 * Alpha captcha class.
 *
 * @author     Loong <loong2460@gmail.com>
 * @copyright  (c) 2021 Muffet Laboratory
 * @license    MIT License
 */

namespace Muffetlab\Captcha\Driver;

use Symfony\Component\String\ByteString;
use Muffetlab\Captcha\Captcha;

class Alpha extends Captcha
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
     * @return mixed
     */
    public function render()
    {
        // Creates $this->image
        $this->imageCreate(Captcha::$config['background']);

        // Add a random gradient
        if (empty(Captcha::$config['background'])) {
            $color1 = imagecolorallocate($this->image, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));
            $color2 = imagecolorallocate($this->image, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));
            $this->imageGradient($color1, $color2);
        }

        // Add a few random circles
        for ($i = 0, $count = mt_rand(10, Captcha::$config['complexity'] * 3); $i < $count; $i++) {
            $color = imagecolorallocatealpha($this->image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255), mt_rand(80, 120));
            $size = mt_rand(5, intdiv(Captcha::$config['height'], 3));
            imagefilledellipse($this->image, mt_rand(0, Captcha::$config['width']), mt_rand(0, Captcha::$config['height']), $size, $size, $color);
        }

        // Calculate character font-size and spacing
        $defaultSize = min(Captcha::$config['width'], Captcha::$config['height'] * 2) / strlen($this->response);
        $spacing = (int) (Captcha::$config['width'] * 0.9 / strlen($this->response));

        // Background alphabetic character attributes
        $colorLimit = mt_rand(96, 160);
        $chars = 'ABEFGJKLPQRTVY';

        // Draw each Captcha character with varying attributes
        for ($i = 0, $strlen = strlen($this->response); $i < $strlen; $i++) {
            // Use different fonts if available
            $font = Captcha::$config['fontpath'] . Captcha::$config['fonts'][array_rand(Captcha::$config['fonts'])];

            $angle = mt_rand(-40, 20);
            // Scale the character size on image height
            $size = $defaultSize / 10 * mt_rand(8, 12);
            $box = imageftbbox($size, $angle, $font, $this->response[$i]);

            // Calculate character starting coordinates
            $x = intdiv($spacing, 4) + $i * $spacing;
            $y = intdiv(Captcha::$config['height'], 2) + intdiv($box[2] - $box[5], 4);

            // Draw captcha text character
            // Allocate random color, size and rotation attributes to text
            $color = imagecolorallocate($this->image, mt_rand(150, 255), mt_rand(200, 255), mt_rand(0, 255));

            // Write text character to image
            imagefttext($this->image, $size, $angle, $x, $y, $color, $font, $this->response[$i]);

            // Draw "ghost" alphabetic character
            $text_color = imagecolorallocatealpha($this->image, mt_rand($colorLimit + 8, 255), mt_rand($colorLimit + 8, 255), mt_rand($colorLimit + 8, 255), mt_rand(70, 120));
            $char = $chars[mt_rand(0, 13)];
            imagettftext($this->image, $size * 2, mt_rand(-45, 45), ($x - (mt_rand(5, 10))), ($y + (mt_rand(5, 10))), $text_color, $font, $char);
        }

        // Output
        return $this->imageRender();
    }

}
