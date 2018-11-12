<?php

namespace nulastudio\Spider;

class Config implements \ArrayAccess, \Countable
{
    protected $configFile = '';
    protected $config     = [];

    public function __construct($configFile = null)
    {
        if ($configFile) {
            $this->configFile = $configFile;
            $content          = file_get_contents($configFile);
            $config           = json_decode($content, true);
            $this->config     = $config ?: [];
        }
    }

    public static function load($configFile = null)
    {
        return new static($configFile);
    }

    public function get($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    public function offsetGet($key)
    {
        return $this->get($key, null);
    }

    public function set($key, $value)
    {
        $this->config[$key] = $value;
    }

    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    public function exists($key)
    {
        return array_key_exists($key, $this->config);
    }

    public function offsetExists($key)
    {
        return $this->exists($key);
    }

    function unset($key) {
        unset($this->config[$key]);
    }

    public function offsetUnset($key)
    {
        unset($this->config[$key]);
    }

    public function count()
    {
        return count($this->config);
    }

    public function save($configFile = null)
    {
        $content = json_encode($this->config);
        return file_put_contents($configFile ?? $this->configFile, $content);
    }
}
