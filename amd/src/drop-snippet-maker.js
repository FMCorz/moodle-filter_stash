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
 * Drop snippet maker module.
 *
 * @package    block_stash
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    'jquery',
    'block_stash/drop-snippet-maker',
], function($, MakerBase) {

    var ILLEGALCHARS = /]/g;
    var START_TAG = '[drop:';
    var END_TAG = ']';

    /**
     * Drop snippet maker class.
     *
     * @class
     * @extends {module:block_stash/drop-snippet-maker}
     */
    function Maker() {
        MakerBase.prototype.constructor.apply(this, arguments);
    }
    Maker.prototype = Object.create(MakerBase.prototype);

    Maker.prototype._getActionText = function() {
        var txt = MakerBase.prototype._getActionText.apply(this, arguments);
        return txt.replace(ILLEGALCHARS, '');
    };

    Maker.prototype._getLabel = function() {
        var txt = MakerBase.prototype._getLabel.apply(this, arguments);
        return txt.replace(ILLEGALCHARS, '');
    };

    Maker.prototype.getSnippet = function() {
        var snippet = START_TAG,
            drop = this.getDrop();

        snippet += drop.get('id') + ':' + drop.get('hashcode').substring(0, 3);

        if (this._displayType == this.IMAGEANDBUTTON) {
            snippet += ':i:' + this._getActionText();
        } else if (this._displayType == this.TEXT) {
            snippet += ':t:' + this._getLabel();
        }

        snippet += END_TAG;
        return snippet;
    };

    return /** @alias module:filter_stash/drop-snippet-maker */ Maker;

});
