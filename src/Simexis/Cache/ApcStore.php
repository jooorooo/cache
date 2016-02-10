<?php

namespace Simexis\Cache;

use Illuminate\Contracts\Cache\Store;

class ApcStore extends TaggableStore implements Store
{
    /**
     * The APC wrapper instance.
     *
     * @var \Simexis\Cache\ApcWrapper
     */
    protected $apc;

    /**
     * A string that should be prepended to keys.
     *
     * @var string
     */
    protected $prefix;

    /**
     * Create a new APC store.
     *
     * @param  \Simexis\Cache\ApcWrapper  $apc
     * @param  string  $prefix
     * @return void
     */
    public function __construct(ApcWrapper $apc, $prefix = '')
    {
        $this->apc = $apc;
        $this->prefix = rtrim($prefix, '.') . ($prefix ? '.' : '');
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     * @return mixed
     */
    public function get($key)
    {
        $value = $this->apc->get($this->getPrefixWithLocale().$key);

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
        $this->apc->put($this->getPrefixWithLocale().$key, $value, $minutes * 60);
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
        return $this->apc->increment($this->getPrefixWithLocale().$key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return int|bool
     */
    public function decrement($key, $value = 1)
    {
        return $this->apc->decrement($this->getPrefixWithLocale().$key, $value);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return array|bool
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
        if($key == '*') {
            $this->flush();
            return true;
        }

        $pattern = str_replace(['\*', '*'], '.+', preg_quote($this->getPrefixWithLocale(true) . $key));
        $check = false;
        foreach($this->apc->cacheList() AS $cache) {
            if(preg_match('~^' . $pattern . '$~i', $cache['info'])) {
                if(!$this->apc->delete($cache['info']))
                    $check = false;
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
        $this->apc->flush();
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
