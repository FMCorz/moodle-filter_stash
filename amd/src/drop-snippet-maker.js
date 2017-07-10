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
        var preHash = START_TAG,
            postHash = '',
            drop = this.getDrop(),
            hashLength = 3;

        preHash += drop.get('id') + ':';

        if (this._displayType == this.IMAGEANDBUTTON) {
            postHash += ':i:' + this._getActionText();
        } else if (this._displayType == this.TEXT) {
            postHash += ':t:' + this._getLabel();
        }

        postHash += END_TAG;
        hashLength = Math.max(3, 32 - (preHash.length + postHash.length));

        // Backup will only encode 32 characters long texts, so we ensure
        // that the recommended snippet has the required length, in case it's
        // the only thing in the textarea.
        return preHash + drop.get('hashcode').substring(0, hashLength) + postHash;
    };

    return /** @alias module:filter_stash/drop-snippet-maker */ Maker;

});
