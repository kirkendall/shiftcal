t# shiftcal

This is the source code for the Shift calendar.
===============================================

It has been sanitized of passwords from the copy running at http://shift2bikes.org/cal , but if you'd like to see it in action that's where to check it out.

Basic Architecture
------------------

The landing page index.php is very minimal - it loads site config from include/common.php and then loads the MAINPAGE.  By default currently, MAINPAGE = view3week.php.

view3week.php provides a 3-week calendar view with all 3 weeks of the event data listed at the bottom.  links from the calendar to event data are included and navigable without talking to the server, after the page loads.  It has links to a simple search form (viewsearch.php) and a full-month view (viewmonth.php), and allows you to move between months.

to add an event, you start at 'calform.php' to fill out one of three versions of the event info page.  it submits to calsubmit.php and uses the verification code in vfyaddress.php and vfyvenue.php (these being broken is non-fatal), but vfydates.php is needed as it parses and accepts date specifications like "first thursdays" or "first of every month except december".

pedalpalooza event viewing and editing has a fair # of files associated with it, which makes sense, since pedalpalooza usage is more than the rest of the year combined.  The most-used files are ppcurrent.php which redirects to whatever year of pedalpalooza is specified as current in include/common.php - eg viewpp2015.php.  The other functionality in the PP stuff: a counter for # of events which is embedded in the PP overview page, a calendar exporter, helpers to view single days/events, and make a personalized calendar.

*See the code_manifest.txt file for more info about each php file in the project*

How to setup & run locally
--------------------------

Minimally you'll need to do the following:

- be running PHP <5.7 >=5.4
- enable PHP and the mysql module (deprecated in PHP 5.5 but still works)
- checkout the source code and stick it in your document root, in a directory called shiftcal
- adjust the database settings in include/accout.php
- untar includes.tgz in your document root (shiftcal/../include)
- run mysql < init.sql to create an empty database
- profit! (aka load http://localhost/shiftcal)


How to propose changes
----------------------

Please fork the code, make a pull request, and we'll try to merge it!
