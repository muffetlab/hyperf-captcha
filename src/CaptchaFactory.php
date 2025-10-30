<?php

/**
 * Captcha factory.
 *
 * @author     Loong <loong2460@gmail.com>
 * @copyright  (c) 2021 Muffet Laboratory
 * @license    MIT License
 */

namespace Kilofox\Captcha;

use Psr\SimpleCache\CacheInterface;

class CaptchaFactory
{
    /**
     * @var array Default config values
     */
    public static $config = [
        'style' => 'basic',
    ];

    /**
     * @var string The correct Captcha challenge answer
     */
    protected $response;

    /**
     * @var \Psr\SimpleCache\CacheInterface
     */
    protected $cache;

    /**
     * Constructs a new Captcha object.
     *
     * @param object $cache Cache interface
     * @return void
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Creates an instance of Captcha.
     *
     * @param string $key The captcha key
     * @param string $group Config group name
     * @return object
     * @throws RuntimeException
     */
    public function create(string $key, string $group = 'default')
    {
        if (!$key) {
            throw new \RuntimeException('No captcha key provided.');
        }

        // Load the configuration for this group
        $config = config('captcha.' . $group);

        if (isset($config['style'])) {
            CaptchaFactory::$config['style'] = $config['style'];
        }

        // Set the captcha driver class name
        $class = '\\Kilofox\\Captcha\\Driver\\' . ucfirst(CaptchaFactory::$config['style']);

        // Create a new captcha instance
        $instance = new $class($group);

        // Generate a new challenge
        $this->response = $instance->generateChallenge();

        // Update response
        $this->updateResponse($key);

        return $instance;
    }

    /**
     * Updates captcha response.
     *
     * @param string $key The captcha key
     * @return void
     */
    public function updateResponse(string $key)
    {
        if (!$key) {
            throw new \RuntimeException('No captcha key provided.');
        }

        // Store the correct Captcha response in a cache
        $this->cache->set('captcha_response_' . $key, sha1(mb_strtoupper($this->response)), 1200);
    }

    /**
     * Deletes captcha response.
     *
     * @param string $key The captcha key
     * @return void
     */
    public function deleteResponse(string $key)
    {
        // Store the correct Captcha response in a cache
        $this->cache->delete('captcha_response_' . $key);
    }

    /**
     * Validates user's Captcha response and updates response counter.
     *
     * @param string $key The captcha key
     * @param string $response User's captcha response
     * @return bool
     */
    public function valid(string $key, string $response)
    {
        // Challenge result
        $result = sha1(mb_strtoupper($response)) === $this->cache->get('captcha_response_' . $key);

        // Increment response counter
        if ($result === true) {
            $this->validCount($key, $this->cache->get('captcha_valid_count_' . $key) + 1);
        } else {
            $this->invalidCount($key, $this->cache->get('captcha_invalid_count_' . $key) + 1);
        }

        return $result;
    }

    /**
     * Gets or sets the number of valid Captcha responses for this key.
     *
     * @param string $key The captcha key
     * @param int $newCount New counter value
     * @param bool $invalid Trigger invalid counter (for internal use only)
     * @return int Counter value
     */
    public function validCount(string $key, int $newCount = null, bool $invalid = false)
    {
        // Pick the right key to use
        $key = ($invalid ? 'captcha_invalid_count_' : 'captcha_valid_count_') . $key;

        // Update counter
        if ($newCount !== null) {
            // Reset counter
            if ($newCount < 1) {
                $this->cache->delete($key);
            }
            // Set counter to new value
            else {
                $this->cache->set($key, $newCount, 86400);
            }

            // Return new count
            return $newCount;
        }

        // Return current count
        return (int) $this->cache->get($key);
    }

    /**
     * Gets or sets the number of invalid Captcha responses for this key.
     *
     * @param string $key The captcha key
     * @param int $newCount New counter value
     * @return int Counter value
     */
    public function invalidCount(string $key, int $newCount = null)
    {
        return $this->validCount($key, $newCount, true);
    }

    /**
     * Resets the Captcha response counters.
     *
     * @param string $key The captcha key
     * @return void
     */
    public function resetCount(string $key)
    {
        $this->validCount($key, 0);
        $this->validCount($key, 0, true);
    }

}
