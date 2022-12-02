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

class csvStoreReader extends dcStoreReader
{
	# overwrite dcStoreReader to remove cache and use mvStoreParser
    public function parse($url)
    {
        $this->validators = [];

        if (!$this->getModulesXML($url) || $this->getStatus() != '200') {
            return false;
        }

        return new csvStoreParser($this->getContent());
    }

	# overwrite dcStoreReader to remove cache and use mvStoreParser
    public static function quickParse($url, $cache_dir = null, $force = true)
    {
        $parser = new self();

        return $parser->parse($url);
    }
}
