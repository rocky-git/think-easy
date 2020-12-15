<?php

if (!function_exists('eadmin_success')) {
    function eadmin_success($title, $message)
    {
        return \thinkEasy\facade\Component::notification()->success($title, $message);
    }
}

if (!function_exists('eadmin_error')) {
    function eadmin_error($title, $message)
    {
        return \thinkEasy\facade\Component::notification()->error($title, $message);
    }
}
if (!function_exists('eadmin_info')) {
    function eadmin_info($title, $message)
    {
        return \thinkEasy\facade\Component::notification()->info($title, $message);
    }
}
if (!function_exists('eadmin_warn')) {
    function eadmin_warn($title, $message)
    {
        return  \thinkEasy\facade\Component::notification()->warning($title, $message);
    }
}

if (!function_exists('eadmin_msg_warn')) {
    function eadmin_msg_warn($message)
    {
        return \thinkEasy\facade\Component::message()->warning($message);
    }
}
if (!function_exists('eadmin_msg_success')) {
    function eadmin_msg_success($message)
    {
        return \thinkEasy\facade\Component::message()->success($message);
    }
}
if (!function_exists('eadmin_msg_error')) {
    function eadmin_msg_error($message)
    {
        return \thinkEasy\facade\Component::message()->error($message);
    }
}
if (!function_exists('eadmin_msg_info')) {
    function eadmin_msg_info($message)
    {
        return \thinkEasy\facade\Component::message()->info($message);
    }
}

