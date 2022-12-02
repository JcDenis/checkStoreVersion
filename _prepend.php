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

$__autoload['csvStore'] = dirname(__FILE__) . '/inc/class.csv.store.php';
$__autoload['csvStoreReader'] = dirname(__FILE__) . '/inc/class.csv.store.reader.php';
$__autoload['csvStoreParser'] = dirname(__FILE__) . '/inc/class.csv.store.parser.php';
