<?php
    /**
     * トークンを生成する
     */
    function generateToken($length) {
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }

    function generateRandomNumberID($length) {
        $number = '';
        for ($i = 0; $i < $length; $i) { 
            $number .= substr(hexdec(bin2hex(random_bytes(5))), 0, 5);
            $i += 5;
        }
        return substr($number, 0, $length);
    }