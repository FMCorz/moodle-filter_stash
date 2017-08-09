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
 * The wild filter appears.
 *
 * The purpose of this filter is to provide teachers with a simpler snippet
 * to inject in their content. A Wordpress-like shortcode will be parsed and
 * converted to the appropriate item drop.
 *
 * @package    filter_stash
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use block_stash\drop;
use block_stash\item;
use block_stash\manager;
use block_stash\output\drop as droprenderable;
use block_stash\output\drop_image;
use block_stash\output\drop_text;
use block_stash\output\trade as traderenderable;

/**
 * Filter class.
 *
 * @package    filter_stash
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter_stash extends moodle_text_filter {

    protected $coursecontext;

    const START_FLAG = '[drop:';
    const TRADE_FLAG = '[trade:';
    const END_FLAG = ']';

    /**
     * The filtering occurs here.
     *
     * @param string $text HTML content.
     * @param array $options Options passed to the filter.
     * @return string The new content.
     */
    public function filter($text, array $options = []) {
        $this->coursecontext = $this->context->get_course_context(false);
        if (!$this->coursecontext) {
            return $text;
        }

        $newtext = '';

        while (($pos = strpos($text, self::START_FLAG)) !== false) {
            $newtext .= substr($text, 0, $pos);

            $text = substr($text, $pos);
            $endpos = strpos($text, self::END_FLAG);

            if ($endpos === false) {
                break;
            }

            // Extract the short code and remove it from the remaining content.
            $shortcode = substr($text, 0, $endpos + 1);
            $text = substr($text, $endpos + 1);

            // Compute the shortcode.
            $newtext .= $this->transform_shortcode($shortcode);
        }

        $newtext .= $text;
        // Before returning let's check it for trades.

        $text = $newtext;
        $newtext = '';

        while (($pos = strpos($text, self::TRADE_FLAG)) !== false) {
            $newtext .= substr($text, 0, $pos);

            $text = substr($text, $pos);
            $endpos = strpos($text, self::END_FLAG);

            if ($endpos === false) {
                break;
            }

            // Extract the short code and remove it from the remaining content.
            $shortcode = substr($text, 0, $endpos + 1);
            $text = substr($text, $endpos + 1);

            // Compute the shortcode.
            $newtext .= $this->transform_shortcode_for_trade($shortcode);
        }
        $newtext .= $text;

        return $newtext;
    }

    /**
     * Display as image.
     *
     * @param droprenderable $drop The drop renderable.
     * @param string $text The text, if any.
     * @param array $options Options for this display type.
     * @return string HTML fragment.
     */
    protected function make_image_display(droprenderable $drop, $text, array $options) {
        global $PAGE;
        $renderable = new drop_image($drop, $text);
        $output = $PAGE->get_renderer('block_stash');
        return $output->render($renderable);
    }

    /**
     * Display as text.
     *
     * @param droprenderable $drop The drop renderable.
     * @param string $text The text, if any.
     * @param array $options Options for this display type.
     * @return string HTML fragment.
     */
    protected function make_text_display(droprenderable $drop, $text, array $options) {
        global $PAGE;
        $renderable = new drop_text($drop, $text);
        $output = $PAGE->get_renderer('block_stash');
        return $output->render($renderable);
    }

    /**
     * Display trade.
     *
     * @param traderenderable $trade The trade renderable.
     * @return string HTML fragment.
     */
    protected function make_trade_display(traderenderable $trade) {
        global $PAGE;
        $output = $PAGE->get_renderer('block_stash');
        return $output->render($trade);
    }

    /**
     * Transform the shortcode.
     *
     * This will attempt to transform the shortcode for the current user.
     * If the code is suspected to be related to something else, it should
     * be returned as is.
     *
     * Supported formats:
     *
     * - [drop:ID:HASHPORTION]
     * - [drop:ID:HASHPORTION:X]
     * - [drop:ID:HASHPORTION:X[:...]:Some text]
     *
     * Where:
     * - ID is the database ID of the drop.
     * - HASHPORTION is at least the 3 first characters of the hash code.
     * - X is the display type and one of [i, t] for image, image and text. Default is i.
     * - Some text is the text to use with some display type.
     * - [:...] is reserved for specific options per display type.
     *
     * @param string $shortcode The full short code.
     * @return string
     */
    public function transform_shortcode($shortcode) {
        // Remove the start and end flags.
        $code = substr($shortcode, 6, -1);

        // Split on colons.
        $parts = explode(':', $code);

        // We do not have enough portions.
        if (count($parts) < 2) {
            return $shortcode;
        }

        $id = (int) array_shift($parts);
        $hashportion = array_shift($parts);
        if (!$id || strlen($hashportion) < 3) {
            // Invalid code, we leave.
            return $shortcode;
        }

        $displaytype = array_shift($parts);
        $text = array_pop($parts);
        $options = (array) $parts;

        $display = '';

        // Only process when the stash is enabled.
        $manager = manager::get($this->coursecontext->instanceid);
        if (!$manager->is_enabled()) {
            return $display;
        }

        // Attempt to find the drop.
        try {
            $drop = $manager->get_drop($id);
            $item = $manager->get_item($drop->get_itemid());

        } catch (dml_exception $e) {
            // Most likely the drop doesn't exist.
            return $display;

        } catch (coding_exception $e) {
            // Some error occured, who knows?
            return $display;
        }

        // Confirm the hash.
        if (strpos($drop->get_hashcode(), $hashportion) !== 0) {
            return $display;
        }

        // Can they see the drop?
        if (!$manager->is_drop_visible($drop)) {
            return $display;
        }

        // Seems that everything is good.
        $droprenderable = new droprenderable($drop, $item, $manager);
        switch ($displaytype) {

            case 't':
                $display = $this->make_text_display($droprenderable, $text, $options);
                break;

            case 'i':
            default:
                $display = $this->make_image_display($droprenderable, $text, $options);
                break;
        }

        return $display;
    }

    public function transform_shortcode_for_trade($shortcode) {
        // Remove the start and end flags.
        $code = substr($shortcode, 7, -1);
        // Split on colons.
        $parts = explode(':', $code);

        // We should only have two parts. Ignore if we have something different.
        if (count($parts) != 2) {
            return $shortcode;
        }

        $id = (int) array_shift($parts);
        $hashportion = array_shift($parts);
        if (!$id || strlen($hashportion) < 3) {
            // Invalid code, we leave.
            return $shortcode;
        }

        $display = '';
        // Only process when the stash is enabled.
        $manager = manager::get($this->coursecontext->instanceid);
        if (!$manager->is_enabled()) {
            return $display;
        }

        // Attempt to find the drop.
        try {
            $trade = $manager->get_trade($id);
        } catch (dml_exception $e) {
            // Most likely the drop doesn't exist.
            return $display;

        } catch (coding_exception $e) {
            // Some error occured, who knows?
            return $display;
        }

        // Confirm the hash.
        if (strpos($trade->get_hashcode(), $hashportion) !== 0) {
            return $display;
        }

        $tradeitems = $manager->get_trade_items($trade->get_id());
        $traderenderable = new traderenderable($trade, $manager, $tradeitems);
        // print_object($traderenderable);
        $display = $this->make_trade_display($traderenderable);

        return $display;
    }

}
