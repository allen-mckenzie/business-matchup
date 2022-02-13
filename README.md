# Yelp Polls #
**Contributors:** [allenmcnichols](https://profiles.wordpress.org/allenmcnichols/)  
**Donate link:** https://github.com/sponsors/allen-mckenzie  
**Tags:** Yelp, Polls, Community Engagement  
**Requires at least:** 5.5  
**Tested up to:** 5.9  
**Requires PHP:** 7.4  
**Stable tag:** 0.0.2  
**License:** GPLv3 or later  
**License URI:** https://www.gnu.org/licenses/gpl-3.0.html  

This plugin will access the Yelp API and StrawPoll API to create polls based on a specific business category and city location that can then be shared to social media.

## Description ##

Yelp Polls helps you generate content by allowing you to specify a specific location and business category to pull information about the top 3 rated businesses in a given city. It then uses this information to create a StrawPoll and generates the necessary content to create a custom post that you can then share on Social Media.

## Installation ##

1.  Download the latest release version and unzip the contents in to the `/wp-contnet/plugins/` directory and make sure the name of the new folder is `yelp-polls`
2.  Activate the plugin through the 'Plugins' menu in WordPress

## Frequently Asked Questions ##

### Where do I enter my Yelp API Key ###

The Yelp API integration has not been added yet. This is a development release.

### What about my StrawPoll API Key ###

The StrawPoll integration has not been added yet. This is a development release.

## Screenshots ##

This development release is not yet fully documented.

## Changelog ##

### 0.0.1 ###

This is the initial shell for the plugin. It creates the custom post type and metaboxes that will be used by the API integrations later.

### 0.0.2 ###

Code audit to properly escape and filter outputs using best practices ( Issues flagged by Codacy )