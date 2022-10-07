<?php
    /**
     * トークンを生成する
     */
    function generateToken($length) {
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }

    function generateRandomNumberID($length) {
        return substr(hexdec(bin2hex(random_bytes($length))), 0, $length);
    }