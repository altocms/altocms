<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */
if (!defined('ALTO_DIR')) die('');

/*
 *  ALTO_DIR    - root directory of current site
 *  ALTO_CORE   - root directory of Alto CMS scripts
 */
defined('ALTO_CORE') || define('ALTO_CORE', ALTO_DIR);

/**
 * Default paths for primary config
 */
$config = [];

$config['path']['dir']['engine'] = ALTO_CORE . '/engine/';        // Path to engine
$config['path']['dir']['libs']   = ALTO_CORE . '/engine/libs/';   // Path to library classes
$config['path']['dir']['vendor'] = ALTO_CORE . '/vendor/';        // Path to vendor directory (for composer)
$config['path']['dir']['common'] = ALTO_CORE . '/common/';        // Path to common components
$config['path']['dir']['config'] = ALTO_CORE . '/common/config/'; // Path to main config directory
$config['path']['dir']['app']    = ALTO_DIR . '/app/';            // Path to application directory

return $config;

// EOF