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
 * Restore process decode rule for drop snippets.
 *
 * @package    filter_stash
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_stash;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/filter/stash/filter.php');

/**
 * Restore process decode rule for drop snippets class.
 *
 * @package    filter_stash
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class drop_snippet_restore_decode_rule extends \block_stash\drop_snippet_restore_decode_rule {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('FILTERSTASHDROPSNIPPET');
    }

    /**
     * Encodes the content.
     *
     * @param string $content The content.
     * @return string The content.
     */
    public static function encode_content($content) {

        $starttag = \filter_stash::START_FLAG;
        $endtag = \filter_stash::END_FLAG;

        // Replace a portion of the shortcode.
        $search = preg_quote($starttag) . '(([0-9]+):[a-z0-9]+)([^\]]*)' . preg_quote($endtag);
        $content = preg_replace('/' . $search . '/i', '[drop:$@FILTERSTASHDROPSNIPPET*$2@$$3]', $content);

        return $content;
    }

    /**
     * Get the replacement.
     *
     * @param drop|null $drop The drop if any.
     * @param int $oldid The old ID.
     * @return string The replacement string.
     */
    protected function get_replacement($drop, $oldid) {
        if ($drop) {
            return $drop->get_id() . ':' . substr($drop->get_hashcode(), 0, 3);
        }
        return '0:WHOOPS';
    }

}
