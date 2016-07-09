<?php

if (!function_exists('captcha_img')) {

    /**
     * @return mixed
     */
    function captcha_img()
    {
        return app('securimage')->getHtml();
    }
}
