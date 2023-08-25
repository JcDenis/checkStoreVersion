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

use dcStore;
use dcUtils;
use Dotclear\Helper\Network\Http;
use Exception;

class CsvStore extends dcStore
{
    # overwrite dcStore::check to remove cache and use csvStoreReader and check disabled modules
    public function check(?bool $force = true): bool
    {
        if (!$this->xml_url) {
            return false;
        }

        try {
            $parser = DC_STORE_NOT_UPDATE ? false : CsvStoreReader::quickParse($this->xml_url, DC_TPL_CACHE, $force);
        } catch (Exception $e) {
            return false;
        }

        $raw_datas = !$parser ? [] : $parser->getModules();

        uasort($raw_datas, fn ($a, $b) => strtolower($a['id']) <=> strtolower($b['id']));

        $updates = [];
        $current = array_merge($this->modules->getModules(), $this->modules->getDisabledModules());
        foreach ($current as $p_id => $p_infos) {
            # non privileged user has no info
            if (!is_array($p_infos)) {
                continue;
            }
            # main repository
            if (isset($raw_datas[$p_id])) {
                if (dcUtils::versionsCompare($raw_datas[$p_id]['version'], $p_infos['version'], '>=')) {
                    $updates[$p_id]                    = $raw_datas[$p_id];
                    $updates[$p_id]['root']            = $p_infos['root'];
                    $updates[$p_id]['root_writable']   = $p_infos['root_writable'];
                    $updates[$p_id]['current_version'] = $p_infos['version'];
                }
                unset($raw_datas[$p_id]);
            }
            # per module third-party repository
            if (!empty($p_infos['repository']) && DC_ALLOW_REPOSITORIES) {
                try {
                    $dcs_url    = substr($p_infos['repository'], -12, 12) == '/dcstore.xml' ? $p_infos['repository'] : Http::concatURL($p_infos['repository'], 'dcstore.xml');
                    $dcs_parser = CsvStoreReader::quickParse($dcs_url, DC_TPL_CACHE, $force);
                    if ($dcs_parser !== false) {
                        $dcs_raw_datas = $dcs_parser->getModules();
                        if (isset($dcs_raw_datas[$p_id]) && dcUtils::versionsCompare($dcs_raw_datas[$p_id]['version'], $p_infos['version'], '>=')) {
                            if (!isset($updates[$p_id]) || dcUtils::versionsCompare($dcs_raw_datas[$p_id]['version'], $updates[$p_id]['version'], '>=')) {
                                $dcs_raw_datas[$p_id]['repository'] = true;
                                $updates[$p_id]                     = $dcs_raw_datas[$p_id];
                                $updates[$p_id]['root']             = $p_infos['root'];
                                $updates[$p_id]['root_writable']    = $p_infos['root_writable'];
                                $updates[$p_id]['current_version']  = $p_infos['version'];
                            }
                        }
                    }
                } catch (Exception $e) {
                }
            }
        }

        $this->data = [
            'new'    => [],
            'update' => $updates,
        ];

        return true;
    }
}
