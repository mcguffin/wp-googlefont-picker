WP Googlefont Picker
====================

Choose some nice Google Webfont on your blog. 

Description
-----------

- Works out of the box.

Frequently asked questions
--------------------------

### Do I need this infamous Google-API-Key badly? ###

Nope, the API-Key is optional. However it is is needed to fetch the most recent list of available fonts. 
There is also a font-list included in the plugin, so it should work out of the box as well. 
I will update this list as well, when I publish a new version.

### Is it compatibe with my theme? ###

It depends on your Theme. By now it has been tested with twentythirteen and travelify. 

In the frontend the plugin does three things:
 - dequeue other googlefont css
 - Load the font css from google using `wp_enqueue_style`
 - inject it's own css.

If your Theme uses `wp_enqueue_style` to load it's own googlefonts, everything should be fine.
If not, your pageload may increase, as some obsolete css will still be loaded.

If the Text does not show up as expected, you might want to review the css selectors 
in the Settings section. A themes might put the font information into annoying selectors like
`#main article .article-head h1 a.head-link-href:hover {
	font-family:'BlaSerif';
}`
Use a DOM inspecting tool like Webkit, or Firebug to detect from what seletor an element 
recieves its font-family property.

### Why can't I filter fonts by style like serif/sans/headline? ###

Short answer: Because it would be too much traffic.
Long answer: The Google-API offers two methods. The first one returns the fonts list. The second one returns 
font metadata, and must be called individually for each font. The style information (serif, sans, deco, ...)
is stored within these meta data. With currently (Feb. 2014) 632 fonts in the google library (steeply rising), 
the plugin would have to send some 600 API-calls. With each one only taking half a second, the process of 
obaining all meta information would take 5 minutes, and cause a php timeout.
The result: Support requests, lots of one-star-ratings, sadness and despair plus global warming due to 
increased computing activity. Save the World. Don't make me implement style filtering.

Plugin API
----------

The plugin offers some filters to allow themes and other plugins to hook in.

### Action googlefont_init ###