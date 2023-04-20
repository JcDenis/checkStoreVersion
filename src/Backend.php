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
declare(strict_types=1);

namespace Dotclear\Plugin\checkStoreVersion;

use dcCore;
use dcNsProcess;

class Backend extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_CONTEXT_ADMIN')
            && !is_null(dcCore::app()->auth) // nullsafe PHP < 8.0
            && dcCore::app()->auth->isSuperAdmin();

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        dcCore::app()->addBehaviors([
            'pluginsToolsTabsV2' => [BackendBehaviors::class, 'pluginsTabs'],
            'themesToolsTabsV2'  => [BackendBehaviors::class, 'themesTabs'],
        ]);

        return true;
    }
}
