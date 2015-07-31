<?php

$config['smarty']['dir']['templates'] = array(
    'themes' => '___path.skins.dir___/___view.skin___/themes/',
    'tpls'   => '___path.skins.dir___/___view.skin___/tpls/',
);

/* Theme */
//$config['view']['theme'] = 'default';
$config['view']['theme'] = 'light';
//$config['view']['theme'] = 'green';
//$config['view']['theme'] = 'red';

/* Top bar fixed or static */
//$config['view']['header']['top'] = 'static'; // static or fixed
$config['view']['header']['top'] = 'fixed'; // static or fixed


// Использовать или нет плавающую шапку редактора
$config['view']['float_editor'] = true;

/* Banner under top bar - turn on/off */
$config['view']['header']['banner'] = true;

// Relative path from skin dir OR absolute path on disk OR URL
$config['view']['header']['logo'] = Config::Get('path.skin.url') . 'themes/___view.theme___/img/favicon.png';
$config['view']['header']['name'] = 'START<span>KIT</span>';

// Aliases for sizes of user avatar
$config['module']['uploader']['images']['profile_avatar']['size'] = array(
    'large'  => 100,
    'big'    => 64,
    'medium' => 48,
    'small'  => 36,
    'mini'   => 24,
);

return $config;

// EOF