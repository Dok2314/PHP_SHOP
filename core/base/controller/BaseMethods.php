<?php

namespace core\base\controller;

trait BaseMethods
{
    protected $styles;
    protected $scripts;

    protected function init($admin = false)
    {
        if(!$admin) {
            if(USER_CSS_JS['styles']) {
                foreach(USER_CSS_JS['styles'] as $style) $this->styles[] = PATH . TEMPLATE . trim($style, '/');
            }

            if(USER_CSS_JS['scripts']) {
                foreach(USER_CSS_JS['scripts'] as $script) $this->scripts[] = PATH . TEMPLATE . trim($script, '/');
            }
        }else{
            if(ADMIN_CSS_JS['styles']) {
                foreach(ADMIN_CSS_JS['styles'] as $style) $this->styles[] = PATH . TEMPLATE . trim($style, '/');
            }

            if(ADMIN_CSS_JS['scripts']) {
                foreach(ADMIN_CSS_JS['scripts'] as $script) $this->styles[] = PATH . TEMPLATE . trim($script, '/');
            }
        }
    }

    protected function clearStr($str)
    {
        if(is_array($str)) {
            foreach($str as $key => $item) $str[$key] = trim(strip_tags($item));
            return $str;
        }else{
            return trim(strip_tags($str));
        }
    }

    protected function clearNum($num)
    {
        return $num * 1;
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    protected function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    protected function redirect($http = false, $code = false)
    {
        if($code) {
            $codes = [
                '301' => 'HTTP/1.1 301 Move Permanently'
            ];

            if($codes[$code]) header($codes[$code]);
        }

        if($http) $redirect = $http;
            else $redirect = $_SERVER['HTTP_REFERER'] ?? PATH;

            header("Location: $redirect");
    }

    protected function writeLog($message, $file = 'log.txt', $event = 'Fault')
    {
        $dateTime = new \DateTime();

        $str = $event . ": " . $dateTime->format("d-m-Y G:i:s") . " - " . $message . "\r\n";

        file_put_contents("log/" . $file, $str, FILE_APPEND);
    }
}