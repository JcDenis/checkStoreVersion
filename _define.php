<?php
/**
 * @brief checkStoreVersion, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugin
 *
 * @author Jean-Christian Denis and Contributors
 *
 * @copyright Jean-Christian Denis
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
if (!defined('DC_RC_PATH')) {
    return null;
}

$this->registerModule(
    'Check store version',
    'Check plugins and themes available version before update',
    'Jean-Christian Denis and Contributors',
    '0.1-dev',
    [
        'requires'    => [['core', '2.19']],
        'permissions' => null,
        'type'        => 'plugin',
        'support'     => 'https://github.com/JcDenis/checkStoreVersion',
        'details'     => 'https://plugins.dotaddict.org/dc2/details/checkStoreVersion',
        'repository'  => 'https://raw.githubusercontent.com/JcDenis/checkStoreVersion/master/'
    ]
);
