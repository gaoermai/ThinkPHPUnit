<?php

spl_autoload_register(function ($class) {
    if ('\\' == $class[0]) {
        $class = substr($class, 1);
    }

    if (
        0 === strpos($class, 'Symfony\\Component\\Yaml\\')
        &&
        file_exists($file = __DIR__.'/../../../'.str_replace('\\', '/', $class).'.php')
    ) {
        require_once $file;
    }
});
