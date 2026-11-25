<?php

/*
 * Only the values the tests change. They are laid over the config the package ships (config/admin.php)
 * in TestCase::setUp(), so a new key in the package config reaches the tests by itself.
 */
return [
    'name' => 'Open-admin',

    'logo' => '<b>Laravel</b> admin',

    'logo-mini' => '<b>La</b>',

    'version' => '1.5.x-dev',
];
