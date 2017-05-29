<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Restore process decode rule for trade snippets.
 *
 * @package    filter_stash
 * @copyright  2017 Adrian Greeve <adriangreeve.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_stash;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/filter/stash/filter.php');

/**
 * Restore process decode rule for trade snippets class.
 *
 * @package    filter_stash
 * @copyright  2017 Adrian Greeve <adriangreeve.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class trade_snippet_restore_decode_rule extends \block_stash\restore_decode_rule {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('FILTERSTASHTRADESNIPPET', '', 'block_stash_trade');
    }

    /**
     * Nasty override to get things done.
     *
     * @param string $content The content.
     * @return string
     */
    public function decode($content) {
        if (preg_match_all($this->cregexp, $content, $matches) === 0) {
            return $content;
        }

        foreach ($matches[0] as $key => $tosearch) {
            foreach ($this->mappings as $mappingkey => $mappingsource) {
                $oldid = $matches[$mappingkey][$key];
                $trade = $this->get_trade($oldid);
            }

            $content = str_replace($tosearch, $this->get_replacement($trade, $oldid), $content);
        }
        return $content;
    }

    /**
     * Encodes the content.
     *
     * @param string $content The content.
     * @return string The content.
     */
    public static function encode_content($content) {

        $starttag = \filter_stash::TRADE_FLAG;
        $endtag = \filter_stash::END_FLAG;

        // Replace a portion of the shortcode.
        $search = preg_quote($starttag) . '(([0-9]+):[a-zA-Z0-9]+)[^\]]*' . preg_quote($endtag);
        $content = preg_replace('/' . $search . '/i', '[trade:$@FILTERSTASHTRADESNIPPET*$2@$]', $content);

        return $content;
    }

    /**
     * Get the replacement.
     *
     * @param trade|null $trade The trade if any.
     * @param int $oldid The old ID.
     * @return string The replacement string.
     */
    protected function get_replacement($trade, $oldid) {
        if ($trade) {
            return $trade->get_id() . ':' . substr($trade->get_hashcode(), 0, 3);
        }
        return '0:WHOOPS';
    }

    /**
     * Get the trade by mapping ID.
     * @param int $oldid The old trade ID.
     * @return trade|false
     */
    protected function get_trade($oldid) {
        if (!isset($this->cache[$oldid])) {
            $newid = $this->get_mapping('block_stash_trade', $oldid);
            if ($newid) {
                $this->cache[$oldid] = new \block_stash\trade($newid);
            } else {
                $this->cache[$oldid] = false;
            }
        }
        return $this->cache[$oldid];
    }

}
