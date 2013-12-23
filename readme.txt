=== NBA Standings ===
Contributors: David Goldstein
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3396118
Tags: basketball, standings, sports, national basketball association, nba
Requires at least: 3.0
Tested up to: 3.8
Stable tag: 1.0

Display the standings for a conference of the <a href="http://nba.com" target="_blank">National Basketball Association (NBA)</a> on your blog.

== Description ==
NBA Standings adds the ability to display the standings for a conference of the <a href="http://nba.com" target="_blank">National Basketball Association (NBA)</a> on your blog via a sidebar widget or template tag. This is ideal for team fan blogs who want to show how their team is doing in the standings. You can also highlight your team in the standings.

Standings are derived from an XML file published daily at erikberg.com. The XML is saved to your Wordpress settings and parsed from there to display on your blog.

== Installation ==
1. Download the .zip archive (link below) and extract.
2. Upload the 'nba-standings' folder to your plugin directory. You should now have a '/wp-content/plugins/nba-standings/' directory holding the contents of the .zip file.
3. Activate the plugin through the 'Plugins' page in Wordpress.
4. Go to 'Settings->NBA Standings' in your admin interface to select the conference you'd like to display and the team you'd like to highlight.

= Sidebar Widget =

To display via sidebar widget:

1. Go to 'Appearance->Widgets' in your admin interface.
2. Under 'Available Widgets' look for 'NBA Standings'.
3. Drag 'NBA Standings' to the sidebar.
4. Enter a title for the widget and click the 'Save' button on the bottom of the widget.

= Template Tag =

To display via template tag add the following line of code to the place you'd like the standings to be displayed:

`<?php if(function_exists(ShowNBAStandings)) : ShowNBAStandings(); endif; ?>`

== Changelog ==
= 1.0 =
* Initial release.

== Screenshots ==

1. The setting page.
2. The front end.
