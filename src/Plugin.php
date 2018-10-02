<?php

namespace Browser;

use Cake\Core\BasePlugin;

/**
 * Plugin for Browser
 */
class Plugin extends BasePlugin
{
    public function initialize()
    {
        define('BROWSER_DIR', dirname(__DIR__));
    }
}
