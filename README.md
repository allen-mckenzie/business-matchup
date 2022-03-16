### Business Matchup ###
**Contributors:** [allenmcnichols](https://profiles.wordpress.org/allenmcnichols/), [amethystanswers](https://profiles.wordpress.org/amethystanswers/)  
**Donate link:** https://github.com/sponsors/allen-mckenzie    
**Tags:** Yelp, Polls, Community Engagement    
**Requires at least:** 5.8    
**Tested up to:** 5.9    
**Requires PHP:** 7.0    
**Stable tag:** 0.1.0    
**License:** GPLv3 or later    
**License URI:** https://www.gnu.org/licenses/gpl-3.0.html    

This plugin will access the Yelp API and StrawPoll API to create polls based on a specific business category and city location that can then be shared to social media.

## Description ##

Business Matchup helps you generate content by allowing you to specify a specific location and business category to pull information about the top 3 rated businesses in a given city. It then uses this information to create a StrawPoll and generates the necessary content to create a custom post that you can then share on Social Media.

## Installation ##

1.  Download the latest release version and unzip the contents in to the `/wp-contnet/plugins/` directory and make sure the name of the new folder is `business-matchup-polls`
2.  Activate the plugin through the 'Plugins' menu in WordPress

## Frequently Asked Questions ##

Here are some frequently asked questions

### Where do I enter my Yelp API Key ###

The Yelp API integration has not been added yet. This is a development release.

### What about my StrawPoll API Key ###

The StrawPoll integration has not been added yet. This is a development release.

## Screenshots ##

1.  Here are some screenshots
2.  This development release is not yet fully documented.

## Upgrade Notice ##

This is an upgrade notice

## Changelog ##

Here is the Changelog

### 0.1.0 ###

Code cleanup and prep for the first release and review

### 0.0.3 ###

Added initial configuration hooks for Yelp API and StrawPoll API integration.
Fixed custom post type slug to be business-matchup-poll and business-matchup-polls respectively.
Bug: relocating settings section into the post type management section soon.

### 0.0.2 ###

Code audit to properly escape and filter outputs using best practices ( Issues flagged by Codacy )

### 0.0.1 ###

This is the initial shell for the plugin. It creates the custom post type and metaboxes that will be used by the API integrations later.
