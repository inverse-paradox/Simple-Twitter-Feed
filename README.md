Simple-Twitter-Feed
===================

WordPress Plugin: Simple Twitter Feed by Inverse Paradox; results are cached in wordpress options to avoid excessive queries.

Simple useage
-------------------------
    [ip_twitter user="InverseParadox"]

Advanced usage
-------------------------
	[ip_twitter user="InverseParadox" num="5" before="<li>" after="</li>" widget_before="<ul>" widget_after="</ul>"]

Change log
-------------------------
version 1.0.2

	- added support for Twitter API 1.0
	- requires php 5.3 or greater (closures make callbacks easier and more dynamic)
	- settings page for API credentials accessable in plugin settings page 
	- look in plugins table where you activate/deactivate for only settings link
