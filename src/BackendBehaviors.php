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
if (!defined('DC_CONTEXT_ADMIN')) {
    return null;
}

# only superadmin
if (!$core->auth->isSuperAdmin()) {
    return null;
}

# admin behaviors
$core->addBehavior('pluginsToolsTabs', ['csvBehaviors', 'pluginsToolsTabs']);
$core->addBehavior('themesToolsTabs', ['csvBehaviors', 'themesToolsTabs']);

class csvBehaviors
{
    public const DC_MAX = '2.23.1';

    # admin plugins page tab
    public static function pluginsToolsTabs(dcCore $core): void
    {
        self::modulesToolsTabs($core, $core->plugins, explode(',', DC_DISTRIB_PLUGINS), $core->adminurl->get('admin.plugins'));
    }

    # admin themes page tab
    public static function themesToolsTabs(dcCore $core): void
    {
        self::modulesToolsTabs($core, $core->themes, explode(',', DC_DISTRIB_THEMES), $core->adminurl->get('admin.blog.theme'));
    }

    # generic page tab
    private static function modulesToolsTabs(dcCore $core, dcModules $modules, array $excludes, string $page_url): void
    {
        echo
        '<div class="multi-part" id="csv" title="' . __('Store version') . '">' .
        '<h3>' . __('Check stores versions') . '</h3>';

        if (!method_exists('dcUtils', 'versionsCompare')
         || dcUtils::versionsCompare(DC_VERSION, self::DC_MAX, '>', false)) {
            echo
            '<div class="error"><p>' . sprintf(__('This version does not support Dotclear > %s'), self::DC_MAX) . '</p></div>';

            return;
        }

        $list = [];
        foreach (array_merge($modules->getModules(), $modules->getDisabledModules()) as $id => $module) {
            if (!in_array($id, $excludes)) {
                $list[$id] = $module;
            }
        }

        if (!count($list)) {
            echo
            '<div class="info">' . __('There is no module to check') . '</div>' .
            '</div>';

            return;
        }

        echo
        '<form method="post" action="' . $page_url . '#csv" id="csvform">' .
        '<p><input type="submit" name="csvcheck" value="' . __('Check lastest stores versions') . '" />' .
        $core->formNonce() . '</p>' .
        '</form>';

        if (!empty($_POST['csvcheck'])) {
            $store   = new csvStore($modules, dcCore::app()->blog->settings->system->store_plugin_url, true);
            self::modulesList($list, $store->get(true));
        }

        echo
        '</div>';
    }

    private static function modulesList($modules, $repos)
    {
        echo 
        '<div class="table-outer">' .
        '<table id="mvmodules" class="modules">' .
        '<caption class="hidden">' . html::escapeHTML(__('Modules list')) . '</caption><tr>' .
        '<th class="first nowrap" colspan="3">' . __('Name') . '</th>' .
        '<th class="nowrap count" scope="col">' . __('Current version') . '</th>' .
        '<th class="nowrap count" scope="col">' . __('Latest version') . '</th>' .
        '<th class="nowrap count" scope="col">' . __('Written for Dotclear') . '</th>';

        if (DC_ALLOW_REPOSITORIES) {
            echo
            '<th class="nowrap count" scope="col">' . __('Repository') . '</th>';
        }

        foreach ($modules as $id => $module) {

            if (!isset($repos[$id])) {
                $img = [__('No version available'), 'check-off.png'];
            } elseif (isset($repos[$id]) && dcUtils::versionsCompare(DC_VERSION, $repos[$id]['dc_min'], '>=', false)) {
                $img = [__('No update available'), 'check-wrn.png'];
            } else {
                $img = [__('Newer version available'), 'check-on.png'];
            }
            $img = sprintf('<img alt="%1$s" title="%1$s" src="images/%2$s" />', $img[0], $img[1]);

            $default_icon = false;

            if (file_exists($module['root'] . '/icon.svg')) {
                $icon = dcPage::getPF($id . '/icon.svg');
            } elseif (file_exists($module['root'] . '/icon.png')) {
                $icon = dcPage::getPF($id . '/icon.png');
            } else {
                $icon         = 'images/module.svg';
                $default_icon = true;
            }
            if (file_exists($module['root'] . '/icon-dark.svg')) {
                $icon = [$icon, dcPage::getPF($id . '/icon-dark.svg')];
            } elseif (file_exists($module['root'] . '/icon-dark.png')) {
                $icon = [$icon, dcPage::getPF($id . '/icon-dark.png')];
            } elseif ($default_icon) {
                $icon = [$icon, 'images/module-dark.svg'];
            }

            echo
            '<tr class="line' . (isset($repos[$id]) ? '' : ' offline') . '" id="mvmodules_m_' . html::escapeHTML($id) . '">' .
            '<td class="module-icon nowrap">' . 
            $img . '</td>' .
            '<td class="module-icon nowrap">' . 
            dcAdminHelper::adminIcon($icon, false, html::escapeHTML($id), html::escapeHTML($id)) . '</td>' .
            '<th class="module-name nowrap" scope="row">' . 
            html::escapeHTML($module['name']) . ($id != $module['name'] ? sprintf(__(' (%s)'), $id) : '') .
            '</td>';

            if (isset($repos[$id])) {
                echo
                '<td class="module-version nowrap count">' . 
                html::escapeHTML($repos[$id]['current_version']) . '</td>' .
                '<td class="module-version nowrap count maximal">' . 
                html::escapeHTML($repos[$id]['version']) . '</td>' .
                '<td class="module-version nowrap count">' . 
                html::escapeHTML($repos[$id]['dc_min']) . '</td>';

                if (DC_ALLOW_REPOSITORIES) {
                    echo
                    '<td class="module-repository nowrap count">' . 
                    (empty($module['repository']) ? __('Official repository') : __('Third-party repository')) . '</td>';
                }
            } else {
                echo 
                '<td class="module-current-version nowrap count">' . 
                html::escapeHTML($module['version']) . '</td>' .
                '<td class="module-version nowrap count maximal" colspan="' . (DC_ALLOW_REPOSITORIES ? '3' : '2') . '">' . 
                html::escapeHTML(__('No version available on stores')) . '</td>';
            }

            echo 
            '</tr>';
        }

        echo
        '</table></div>';
    }
}
