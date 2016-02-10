<?php

namespace Simexis\Cache;

use Illuminate\Contracts\Cache\Store;

class WinCacheStore extends TaggableStore implements Store
{
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
        $value = wincache_ucache_get($this->getPrefixWithLocale().$key);

        if ($value !== false) {
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
        wincache_ucache_set($this->getPrefixWithLocale().$key, $value, $minutes * 60);
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return int|bool
     */
    public function increment($key, $value = 1)
    {
        return wincache_ucache_inc($this->getPrefixWithLocale().$key, $value);
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return int|bool
     */
    public function decrement($key, $value = 1)
    {
        return wincache_ucache_dec($this->getPrefixWithLocale().$key, $value);
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
        $this->put($key, $value, 0);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function forget($key)
    {
        if($key == '*') {
            $this->flush();
            return true;
        }

        $pattern = str_replace(['\*', '*'], '.+', preg_quote($this->getPrefixWithLocale(true) . $key));
        $check = false;
        $all = wincache_ucache_info();
        if(isset($all['ucache_entries']) && $all['ucache_entries']) {
            foreach ($all['ucache_entries'] AS $cache) {
                if (preg_match('~^' . $pattern . '$~i', $cache['key_name'])) {
                    if (!wincache_ucache_delete($cache['key_name']))
                        $check = false;
                }
            }
        }
        return $check;
    }

    /**
     * Remove all items from the cache.
     *
     * @return void
     */
    public function flush()
    {
        wincache_ucache_clear();
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
