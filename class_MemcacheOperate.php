<?php

class MemcacheOperate
{

    private $memcache;
    private $keyPrefix;

    const ONE_HOUR = 3600;
    const TEN_MINUTES = 600;
    const FIVE_MINUTES = 300;


    /**
     * Constructor.
     *
     * @param string $keyPrefix
     * @throw Exception
     */
    public function __construct($keyPrefix)
    {
        $this->memcache = new Memcache;
        $this->memcache->connect('localhost');
        if (!is_string($keyPrefix)) {
            throw new Exception('keyPrefix is not string.');
        }
        $this->keyPrefix = $keyPrefix;
    }


    /**
     * sets val memcache for 1 hour.
     *
     * @param string $key
     * @param mix    $val
     */
    public function setOneHour($key, $val)
    {
        return self::set($key, $val, self::ONE_HOUR);
    }


    /**
     * sets val memcache for 10 minutes.
     *
     * @param string $key
     * @param mix    $val
     */
    public function setTenMinutes($key, $val)
    {
        return self::set($key, $val, self::TEN_MINUTES);
    }


    /**
     * sets val memcache for 5 minutes.
     *
     * @param string $key
     * @param mix    $val
     */
    public function setFiveMinutes($key, $val)
    {
        return self::set($key, $val, self::FIVE_MINUTES);
    }


    /**
     * sets val memcache.
     *
     * @param  string $key
     * @param  mix    $val
     * @param  int    $time
     * @return bool   result
     * @throw  Exception
     */
    public function set($key, $val, $time)
    {
        if (empty($key)) {
            throw new Exception('key is empty.');
        }
        if (!ctype_digit(filter_var($time))) {
            throw new Exception('time is not int.');
        }
        // 日変わりのタイミングで不整合が起きるのを防ぐため、set させない
        $nowStamp = date('H:i:s');
        if ($nowStamp >= '23:59:00' || $nowStamp <= '00:01:00') {
            return false;
        }
        // キーの末尾に日付を入れて日変わりで自動更新させる
        $key = self::makeKey($key);
        return $this->memcache->set($key, $val, 0, $time);
    }


    /**
     * get val from memcache.
     *
     * @param  string $key
     * @return mix    $val
     */
    public function get($key)
    {
        $key = self::makeKey($key);
        return $this->memcache->get($key);
    }


    /**
     * delete val from memcache.
     *
     * @param  string $key
     * @return bool  result
     */
    public function delete($key)
    {
        $key = self::makeKey($key);
        return $this->memcache->delete($key);
    }


    /**
     * meke key for memcache.
     *
     * @param string $key
     * @return string
     */
    private function makeKey($key = null)
    {
        if (is_null($key)) {
            throw new Exception('');
        }
        return $this->keyPrefix . $key . date('Y-m-d');
    }
}
