=== WP Googlefont Picker ===
Contributors: podpirate
//Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=CXETZUWYAAVC8
Tags: fonts,theme,googlefont,webfont,design,typography
Requires at least: 3.6
Tested up to: 4.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Choose some nice Google Webfont on your blog. 

== Description ==

- Works out of the box.

Latest files on [GitHub](https://github.com/mcguffin/wp-googlefont-picker).

== Installation ==

1. Upload the Zip file to the `/wp-content/plugins/` directory and unzip it.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Optional: Get a google API Key, go to Settings and stay tuned with the latest google webfonts.

== Frequently asked questions ==

= Do I need this infamous Google-API-Key badly? =

Nope, not really. The API-Key is needed to fetch the most recent list of available fonts. 
There is also a font-list included in the plugin, so it should work out of the box as well. 
I will update this list as well, when I publish a new version.

= Is it compatibe with my theme? =

It depends on your Theme. By now it has been tested with twentythirteen and travelify. 

In the frontend the plugin does three things:
- dequeue other googlefont css
- Load the font css from google using `wp_enqueue_style`
- inject it's own css.

If your Theme uses `wp_enqueue_style` to load it's own googlefonts, everything should be fine.
If not, your pageload may increase, as some obsolete fonts will still get loaded.

If the Text does not show up as expected, you might want to review the css selectors 
in the Settings section. A themes might put the font information into annoying selectors like
`#main article .article-head h1 a.head-link-href:hover {
	font-family:'BlaSerif';
}`
Use a DOM inspecting tool like Webkit, 

= Why can't I filter fonts by style? =

Short answer: Because it would be too much traffic.
Long answer: The Google-API offers two methods. The first one returns the fonts list. The second one returns 
font metadata, and must be called individually for each font. The style information (serif, sans, deco, ...)
is stored within these meta data. With currently some 600 fonts in their library (steeply rising), the plugin 
would have to send some 600 API-calls. With each one only taking half a second, the process of obaining all 
meta information would take 5 minutes, and cause a php timeout.
The result: Support requests, lots of one-star-ratings, sadness and despair plus global warming due to increased 
computing activity. Save the World. Don't make me implement style filtering.

= I found a bug. Where should I post it? =

I personally prefer GitHub. The plugin code is here: [GitHub](https://github.com/mcguffin/wp-googlefont-picker)
(See below as well.)

= I'd like to suggest a feature. Where should I post it? =

I personally prefer GitHub. The plugin code is here: [GitHub](https://github.com/mcguffin/wp-googlefont-picker)
(See above as well.)

= I want to use the latest files. How can I do this? =

Use the GitHub Repo rather than the WordPress Plugin. Do as follows:
1. If you haven't done so: [Install git](https://help.github.com/articles/set-up-git)
2. in the console cd into Your 'wp-content/plugins' directory
3. type 'git clone git@github.com:mcguffin/wp-revision-request.git'
4. If you want to update to the latest files (be careful, might be untested on Your WP-Version) type 'git pull'.

= I found a bug and fixed it. How can I let You know? =

Either post it on the [GitHub-repo](https://github.com/mcguffin/wp-googlefont-picker) or—if you cloned the repository—send me a pull request.

== Screenshots ==

1. Font customizer
2. Settings panel.


== Changelog ==

= 0.1.0 =
Initial Release

== Plugin API ==

The plugin offers some filters to allow themes and other plugins to hook in.

Visit the [GitHub-Repo](https://github.com/mcguffin/wp-googlefont-picker) for details.