<?php
namespace Fizzday\Requests;

/**
 * 简单HTTP请求封装类
 * Class FizzRequest
 */
class Request
{
    protected static $all=array();
    /**
     * 所有
     * @return array
     */
    public static function all()
    {
        if (self::$all) return self::$all;

        $method = self::method();

        if ($method == 'GET') $data = $_GET;
        else {
            $data = $_POST;

            if (!$data) {
                $php_input = file_get_contents('php://input');
                $data = json_decode($php_input, true);
                if (!$data) {
                    parse_str(file_get_contents('php://input'), $data);
                }
            }
        }

        // 安全过滤
        if (!empty($data) && !get_magic_quotes_gpc()) {
            self::Add_S($data);
        }

        return self::$all = $data;
    }

    /**
     * 单个参数
     * @param string $param
     * @return array|mixed
     */
    public static function input($param = '', $default='')
    {
        $all = self::all();
        if ($param) {
            if (isset($all[$param])) {
                return trim($all[$param]);
            } else {
                if ($default) return $default;
            }
        } else {
            if ($default) return $default;
            return $all;
        }
    }

    /**
     * 获取头信息
     * @param string $head
     * @return null
     */
    public static function header($head='')
    {
        $head_str = 'HTTP_'.strtoupper($head);
        if ($head && isset($_SERVER[$head_str])) {
            return $_SERVER[$head_str];
        }

        return null;
    }

    /**
     * 请求方式
     * @return mixed
     */
    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function __get($param = '')
    {
        return self::input($param);
    }

    public function __set($name, $value)
    {
        self::$all[$name] = $value;
    }

    /**
     * 安全过滤
     * @param $array
     */
    private static function Add_S(&$array){
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (!is_array($value)) {
                    $array[$key] = addslashes($value);
                } else {
                    self::Add_S($array[$key]);
                }
            }
        }
    }
}