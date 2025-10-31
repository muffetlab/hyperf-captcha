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

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The configuration for captcha.',
                    'source' => __DIR__ . '/../publish/captcha.php',
                    'destination' => BASE_PATH . '/config/autoload/captcha.php',
                ],
            ],
        ];
    }

}
