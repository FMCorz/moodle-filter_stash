Filter Stash
============

Simplify the usage of the block [Stash](https://moodle.org/plugins/block_stash) by providing shortcodes to place items.

Why use this?
---------------

With this filter the snippet to place the items becomes very simple:

    [drop:123:abc]

* Teachers do not have to switch to the HTML view of their editors
* The shortcode is always visible when editing, unlike the Javascript snippet
* Unlike the Javascript snippet, the shortcode will gracefully support newer versions
* The snippet will (safely) make it through some security limitations

The trading feature is only possible with the installation of this plugin. The snippet for trading is very similar to 
placing drops.

    [trade:123:abc]

The same benefits above apply.

Requirements
------------

Moodle 2.9 or greater.

Installation
------------

Simply install the plugin and enable the filter.

License
-------

Licensed under the [GNU GPL License](http://www.gnu.org/copyleft/gpl.html).
