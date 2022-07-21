<?php
/**
 * Created by PhpStorm.
 * User: nguyenpv
 * Date: 21/07/2022
 * Time: 09:06
 */
namespace App\Helpers;


class Context
{
    CONST PARTNER_ACCESS_TOKEN = 'partner_access_token';
    protected $data = [];

    protected static $_instance = null;

    protected function __construct()
    {

    }

    public static function getInstance()
    {
        if(static::$_instance === null)
            static::$_instance = new Context();

        return static::$_instance;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function get($key, $default = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

}
