<?php
// KCAPTCHA configuration file

$config['alphabet'] = "0123456789abcdefghijklmnopqrstuvwxyz"; // DO NOT CHANGE without changing font files!

// symbols used to draw CAPTCHA
//$config['allowed_symbols'] = "0123456789"; //digits only
//$config['allowed_symbols'] = "23456789abcdegkmnpqsuvxyz"; //alphabet without similar symbols (o=0, 1=l, i=j, t=f)
$config['allowed_symbols']   = "23456789abcdegikpqsvxyz"; //alphabet without similar symbols (o=0, 1=l, i=j, t=f)

// folder with fonts
$config['fonts_dir'] = 'fonts';

// CAPTCHA string length
//$config['length'] = array(3, 5); // random 3 or 4 or 5 symbols
$config['length'] = 3;

// CAPTCHA image size
$config['image']['width'] = 80;
$config['image']['height'] = 60;

// CAPTCHA image colors (RGB, 0-255)
//$foreground_color = array(0, 0, 0);
//$background_color = array(220, 230, 255);
$config['image']['foreground_color'] = array(
    array(0, 80), array(0, 80), array(0, 80)
);
$config['image']['background_color'] = array(
    array(220, 255), array(220, 255), array(220, 255)
);

// JPEG quality of CAPTCHA image (bigger is better quality, but larger file size)
$config['image']['jpeg_quality'] = 90;

// symbol's vertical fluctuation amplitude
$config['image']['fluctuation_amplitude'] = 8;

//noise
//$white_noise_density=0; // no white noise
$config['image']['white_noise_density'] = 1 / 6;
//$black_noise_density=0; // no black noise
$config['image']['black_noise_density'] = 1 / 30;

// increase safety by prevention of spaces between symbols
$config['image']['no_spaces'] = true;

// show credits
$config['show_credits'] = false; // set to false to remove credits line. Credits adds 12 pixels to image height
$config['credits'] = 'www.captcha.ru'; // if empty, HTTP_HOST will be shown

// EOF