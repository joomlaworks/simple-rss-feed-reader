Simple RSS Feed Reader
======================

Adding RSS/Atom syndicated content inside your Joomla website is now super-easy and simple with the 'Simple RSS Feed Reader' module from JoomlaWorks. All you have to do is add a few feeds to the module parameters, publish the module in some position and that's it!
You can even publish multiple feeds at the same time (meaning in the same module instance) and have them display combined!

The 'Simple RSS Feed Reader' module is based on the same feed parsing engine that powers JoomlaReader.com, the most popular Joomla news aggregator in the Joomla Community.

The feeds are stored inside your Joomla site's cache folder and refreshed in a specific time interval, which you set in the module's parameters. This feed cache is different to Joomla's cache, as you may need to have your site refreshed every 5 minutes, but have the feeds the module retrieves stored longer than 5 minutes.


## NOTABLE FEATURES
- one input box for feeds enables you to add unlimited feeds per module instance
- combine multiple feeds into one output list
- show or hide the first image inside each feed content
- extract and resize remote images for more layout control - we use Sencha's world class image resizing service for fast loading times and without stressing your server
- additional content options include pre-text, post-text and a custom link option at the bottom of the feeds block
- MVC templating is now standard, module comes pre-packed with 2 generic templates that will fit most sites - they also serve as a great starting point if you want to create your own
- One input box for feeds enables you to add unlimited sources to your module (previously limited to 20 feeds only per module instance).


## GETTING STARTED
After you install the module, add one or more feed sources in the related box under "Fetch Options" and simply adjust the "Feed Content Options" in the module parameters. Then publish into some module position.


## STYLING
The module installs with 2 generic templates (default and compact), which should be sufficient for most websites. If you want more control, you can simply override both the generated HTML and CSS, using MVC template overrides within your Joomla template. Or you can create new folders with new module templates inside your template's /html/mod_jw_srfr/ folder and just select the new ones in the module's parameters.

The compact template is inspired by https://joomlareader.com


## DEMO
Demo for "default" sub-template: https://demo.joomlaworks.net (right column)


## LEARN MORE
Visit the Simple RSS Feed Reader product page at: https://www.joomlaworks.net/extensions/free-premium/simple-rss-feed-reader
