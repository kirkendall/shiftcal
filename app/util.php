<?php

function get(&$var, $default=null) {
    return isset($var) ? $var : $default;
}

