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

use dcStoreParser;

class CsvStoreParser extends dcStoreParser
{
    # overwrite dcStoreParser to bypasse current dotclear version
    protected function _parse(): void
    {
        if (empty($this->xml->module)) {
            return;
        }

        foreach ($this->xml->module as $i) {
            $attrs = $i->attributes();
            if (!isset($attrs['id'])) {
                continue;
            }

            $item = [];

            # DC/DA shared markers
            $item['id']      = (string) $attrs['id'];
            $item['file']    = (string) $i->file;
            $item['label']   = (string) $i->name; // deprecated
            $item['name']    = (string) $i->name;
            $item['version'] = (string) $i->version;
            $item['author']  = (string) $i->author;
            $item['desc']    = (string) $i->desc;

            # DA specific markers
            $item['dc_min']  = (string) $i->children(self::$bloc)->dcmin;
            $item['details'] = (string) $i->children(self::$bloc)->details;
            $item['section'] = (string) $i->children(self::$bloc)->section;
            $item['support'] = (string) $i->children(self::$bloc)->support;
            $item['sshot']   = (string) $i->children(self::$bloc)->sshot;

            $tags = [];
            foreach ($i->children(self::$bloc)->tags as $t) {
                $tags[] = (string) $t->tag;
            }
            $item['tags'] = implode(', ', $tags);

            # No more filters here, return all modules
            $this->items[$item['id']] = $item;
        }
    }
}
