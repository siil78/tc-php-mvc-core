<?php

namespace siil78\phpmvc;

class Request {

    public function getPath() {

        //získej path z globální proměnné
        //příklad null coalescing operator
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        //zjisti jestli v url jsou nějaké argumenty
        $position = strpos($path, '?');
        //url je bez argumentů
        if ($position === false) {
            return $path;
        }
        //ošetři url s argumenty a vrať hodnotu
        return $path = substr($path, 0, $position);
    }   

    public function method() {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function isGet() {
        return $this->method() === 'get';
    }

    public function isPost() {
        return $this->method() === 'post';
    }

    public function getBody() {

        $body = [];

        if ($this->method() === 'get') {
            foreach($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        };

        if ($this->method() === 'post') {
            foreach($_POST as $key => $value) {
                $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        };

        return $body;
    }
}