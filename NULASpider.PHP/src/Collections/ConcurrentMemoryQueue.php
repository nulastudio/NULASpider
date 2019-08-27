<?php

namespace nulastudio\Collections;

use nulastudio\Collections\MemoryQueue;
use nulastudio\Threading\LockManager;

class ConcurrentMemoryQueue extends MemoryQueue
{
    private $token = '';

    public function __construct()
    {
        parent::__construct();
        $this->token = strtoupper(md5(uniqid(mt_rand(), true)));
    }

    protected function init()
    {
        LockManager::getLock($this->token);
        try {
            parent::init();
        } catch (\Exception $e) {
            throw $e;
        } finally {
            LockManager::releaseLock($this->token);
        }
    }

    public function pop()
    {
        LockManager::getLock($this->token);
        $val = null;
        try {
            $val = parent::pop();
        } catch (\Exception $e) {
            throw $e;
        } finally {
            LockManager::releaseLock($this->token);
        }
        return $val;
    }
    public function push($value)
    {
        LockManager::getLock($this->token);
        try {
            return parent::push($value);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            LockManager::releaseLock($this->token);
        }
        return false;
    }
    public function exists($value)
    {
        LockManager::getLock($this->token);
        $val = null;
        try {
            $val = parent::exists($value);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            LockManager::releaseLock($this->token);
        }
        return $val;
    }
    public function peek()
    {
        LockManager::getLock($this->token);
        $val = null;
        try {
            $val = parent::peek();
        } catch (\Exception $e) {
            throw $e;
        } finally {
            LockManager::releaseLock($this->token);
        }
        return $val;
    }
    public function count()
    {
        LockManager::getLock($this->token);
        $val = null;
        try {
            $val = parent::count();
        } catch (\Exception $e) {
            throw $e;
        } finally {
            LockManager::releaseLock($this->token);
        }
        return $val;
    }
    public function empty()
    {
        LockManager::getLock($this->token);
        try {
            parent::empty();
        } catch (\Exception $e) {
            throw $e;
        } finally {
            LockManager::releaseLock($this->token);
        }
    }
    public function reindex()
    {
        LockManager::getLock($this->token);
        try {
            parent::reindex();
        } catch (\Exception $e) {
            throw $e;
        } finally {
            LockManager::releaseLock($this->token);
        }
    }
    public function serialize($value)
    {
        LockManager::getLock($this->token);
        $val = null;
        try {
            $val = parent::serialize($value);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            LockManager::releaseLock($this->token);
        }
        return $val;
    }
    public function unserialize($str)
    {
        LockManager::getLock($this->token);
        $val = null;
        try {
            $val = parent::unserialize($str);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            LockManager::releaseLock($this->token);
        }
        return $val;
    }
}
