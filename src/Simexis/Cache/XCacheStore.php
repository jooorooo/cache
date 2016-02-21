<?php

namespace Simexis\Cache;

use Illuminate\Contracts\Cache\Store;
use Illuminate\Cache\RetrievesMultipleKeys;

class XCacheStore extends TaggableStore implements Store
{
    use RetrievesMultipleKeys;
    /**
     * A string that should be prepended to keys.
     *
     * @var string
     */
    protected $prefix;

    /**
     * Create a new WinCache store.
     *
     * @param  string  $prefix
     * @return void
     */
    public function __construct($prefix = '')
    {
        $this->prefix = $prefix;
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     * @return mixed
     */
    public function get($key)
    {
        $value = xcache_get($this->getPrefixWithLocale().$key);

        if (isset($value)) {
            return $value;
        }
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $minutes
     * @return void
     */
    public function put($key, $value, $minutes)
    {
        xcache_set($this->getPrefixWithLocale().$key, $value, $minutes * 60);
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return int
     */
    public function increment($key, $value = 1)
    {
        return xcache_inc($this->getPrefixWithLocale().$key, $value);
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return int
     */
    public function decrement($key, $value = 1)
    {
        return xcache_dec($this->getPrefixWithLocale().$key, $value);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function forever($key, $value)
    {
        return $this->put($key, $value, 0);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function forget($key)
    {
        $pattern = $this->getPrefixWithLocale().$key;
        if(strpos($pattern, '*') === false)
            return xcache_unset($this->getPrefixWithLocale().$key);
        list($prefix) = explode('*', $pattern);
        return xcache_unset_by_prefix($prefix);
    }

    /**
     * Remove all items from the cache.
     *
     * @return void
     */
    public function flush()
    {
        xcache_clear_cache(XC_TYPE_VAR);
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    private function getPrefixWithLocale($flush = false)
    {
        return $this->getPrefix() . ($flush ? '*' : app()->getLocale()) . '.';
    }
}
