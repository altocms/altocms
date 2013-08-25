<?php

function smarty_modifier_cfg($key, $instance = Config::DEFAULT_CONFIG_ROOT) {

    return Config::Get($key, $instance);
}

// EIF