<?php

declare(strict_types=1);

/**
 * Captcha class.
 *
 * @author     Loong <loong2460@gmail.com>
 * @copyright  (c) 2021 Muffet Laboratory
 * @license    MIT License
 */

namespace Muffetlab\Captcha;

use Psr\Http\Message\ResponseInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Context;

abstract class Captcha
{
    /**
     * @var string Style-dependent Captcha driver
     */
    protected string $driver;

    /**
     * @var array Default config values
     */
    public static array $config = [
        'width' => 150,
        'height' => 50,
        'complexity' => 4,
        'background' => '',
        'fontpath' => '',
        'fonts' => [],
    ];

    /**
     * @var string The correct Captcha challenge answer
     */
    protected string $response;

    /**
     * @var string Image resource identifier
     */
    protected string $image;

    /**
     * @var string Image type ("png", "gif" or "jpeg")
     */
    protected string $imageType = 'png';

    /**
     * Constructs a new Captcha object.
     *
     * @param string Config group name
     * @return void
     * @throws RuntimeException
     */
    public function __construct(string $group = null)
    {
        // No config group name given
        if (!is_string($group)) {
            $group = 'default';
        }

        // Load and validate config group
        if (!is_array($config = config('captcha.' . $group))) {
            throw new \RuntimeException('Captcha group not defined in ' . $group . ' configuration');
        }

        // All captcha config groups inherit default config group
        if ($group !== 'default') {
            // Load and validate default config group
            if (!is_array($default = config('captcha.default'))) {
                throw new \RuntimeException('Captcha group not defined in default configuration');
            }

            // Merge config group with default config group
            $config += $default;
        }

        // Assign config values to the object
        foreach ($config as $key => $value) {
            if (array_key_exists($key, Captcha::$config)) {
                Captcha::$config[$key] = $value;
            }
        }

        // If using a background image, check if it exists
        if (!empty($config['background'])) {
            Captcha::$config['background'] = str_replace('\\', '/', realpath($config['background']));

            if (!is_file(Captcha::$config['background'])) {
                throw new \RuntimeException('The specified file ' . Captcha::$config['background'] . ' was not found.');
            }
        }

        // If using any fonts, check if they exist
        if (!empty($config['fonts'])) {
            Captcha::$config['fontpath'] = str_replace('\\', '/', realpath($config['fontpath'])) . '/';

            foreach ($config['fonts'] as $font) {
                if (!is_file(Captcha::$config['fontpath'] . $font)) {
                    throw new \RuntimeException('The specified file ' . Captcha::$config['fontpath'] . $font . ' was not found.');
                }
            }
        }
    }

    /**
     * Returns the image type.
     *
     * @param string $filename Filename
     * @return string|bool Image type ("png", "gif" or "jpeg")
     */
    public function imageType(string $filename)
    {
        switch (strtolower(substr(strrchr($filename, '.'), 1))) {
            case 'png':
                return 'png';

            case 'gif':
                return 'gif';

            case 'jpg':
            case 'jpeg':
                // Return "jpeg" and not "jpg" because of the GD2 function names
                return 'jpeg';

            default:
                return false;
        }
    }

    /**
     * Creates an image resource with the dimensions specified in config.
     * If a background image is supplied, the image dimensions are used.
     *
     * @param string $background Path to the background image file
     * @return void
     * @throws RuntimeException If no GD2 support
     */
    public function imageCreate(string $background = null)
    {
        // Check for GD2 support
        if (!function_exists('imagegd2')) {
            throw new \RuntimeException('Captcha requires GD2');
        }

        // Create a new image (black)
        $this->image = imagecreatetruecolor(Captcha::$config['width'], Captcha::$config['height']);

        // Use a background image
        if (!empty($background)) {
            // Create the image using the right function for the filetype
            $function = 'imagecreatefrom' . $this->imageType($background);
            $backgroundImage = $function($background);

            // Resize the image if needed
            if (imagesx($backgroundImage) !== Captcha::$config['width'] || imagesy($backgroundImage) !== Captcha::$config['height']) {
                imagecopyresampled(
                    $this->image,
                    $backgroundImage,
                    0,
                    0,
                    0,
                    0,
                    Captcha::$config['width'],
                    Captcha::$config['height'],
                    imagesx($backgroundImage),
                    imagesy($backgroundImage)
                );
            }

            // Free up resources
            imagedestroy($backgroundImage);
        }
    }

    /**
     * Fills the background with a gradient.
     *
     * @param resource $color1 GD image color identifier for start color
     * @param resource $color2 GD image color identifier for end color
     * @param string $direction Direction: 'horizontal' or 'vertical', 'random' by default
     * @return void
     */
    public function imageGradient($color1, $color2, string $direction = null)
    {
        $directions = ['horizontal', 'vertical'];

        // Pick a random direction if needed
        if (!in_array($direction, $directions)) {
            $direction = $directions[array_rand($directions)];

            // Switch colors
            if (mt_rand(0, 1) === 1) {
                $temp = $color1;
                $color1 = $color2;
                $color2 = $temp;
            }
        }

        // Extract RGB values
        $color1 = imagecolorsforindex($this->image, $color1);
        $color2 = imagecolorsforindex($this->image, $color2);

        // Preparations for the gradient loop
        $steps = $direction === 'horizontal' ? Captcha::$config['width'] : Captcha::$config['height'];

        $r1 = intdiv(($color1['red'] - $color2['red']), $steps);
        $g1 = intdiv(($color1['green'] - $color2['green']), $steps);
        $b1 = intdiv(($color1['blue'] - $color2['blue']), $steps);

        if ($direction === 'horizontal') {
            $x1 = & $i;
            $y1 = 0;
            $x2 = & $i;
            $y2 = Captcha::$config['height'];
        } else {
            $x1 = 0;
            $y1 = & $i;
            $x2 = Captcha::$config['width'];
            $y2 = & $i;
        }

        // Execute the gradient loop
        for ($i = 0; $i <= $steps; $i++) {
            $r2 = $color1['red'] - $i * $r1;
            $g2 = $color1['green'] - $i * $g1;
            $b2 = $color1['blue'] - $i * $b1;
            $color = imagecolorallocate($this->image, $r2, $g2, $b2);

            imageline($this->image, $x1, $y1, $x2, $y2, $color);
        }
    }

    /**
     * Outputs the image to the browser.
     *
     * @return object
     */
    public function imageRender(): object
    {
        ob_start();

        // Pick the correct output function
        $function = 'image' . $this->imageType;
        $function($this->image);
        $content = ob_get_clean();

        // Free up resources
        imagedestroy($this->image);

        return Context::get(ResponseInterface::class)
                ->withHeader('Content-Type', 'image/' . $this->imageType)
                ->withHeader('Content-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0')
                ->withHeader('Pragma', 'no-cache')
                ->withHeader('Connection', 'close')
                ->withBody(new SwooleStream($content));
    }

}
