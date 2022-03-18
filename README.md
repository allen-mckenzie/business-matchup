### Business Matchup ###
**Contributors:** [allenmcnichols](https://profiles.wordpress.org/allenmcnichols/), [amethystanswers](https://profiles.wordpress.org/amethystanswers/)  
**Donate link:** https://github.com/sponsors/allen-mckenzie    
**Tags:** Yelp, Polls, Community Engagement    
**Requires at least:** 5.8    
**Tested up to:** 5.9    
**Requires PHP:** 7.0    
**Stable tag:** 0.1.3
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

Getting a Yelp API Key:

1.  If you don't already have a Yelp account go to: https://yelp.com/ and signup.

2.  After you login go to https://www.yelp.com/developers/v3/manage_app and create a new app.

    ![create-app-page](https://user-images.githubusercontent.com/43300142/158496476-2c1d5522-986d-41cb-9150-021cd0a491e8.png)

    ![enter-new-app-details](https://user-images.githubusercontent.com/43300142/158496510-46346d71-2222-4986-b670-a70f9bcd4ebd.png)

3.  If you see an error message when you create your new app because you didn't verify your email. Go do that and try again.

    ![create-app-error](https://user-images.githubusercontent.com/43300142/158496522-bbac1b97-f03b-4af2-917c-303af76bde6d.png)

4.  You should see a success messages that says, "Great your app has been created!"

    ![create-app-sucess](https://user-images.githubusercontent.com/43300142/158496541-f45bfa74-1ce9-4ae1-aff9-846da6612413.png)

5.  Copy your new API details and save them in a safe location

### What about my StrawPoll API Key ###

1.  If you don't already have a Straw Poll account go to: https://strawpoll.com/en/signup/ and signup.
2.  Once you are logged in go to: https://strawpoll.com/account/settings/
3.  Then click the Generate new key if you don't see a key there otherwise click the Show link and copy your API key.

### How do I create a Business Matchup Poll ###

1.  Click on Business Matchups

2.  Next, click on Add New

    ![add-new-business-matchup-poll](https://user-images.githubusercontent.com/43300142/158496712-29ef2661-0658-4412-a2c9-63634905b8d1.png)

3.  Give your page a title just like you would for any other page or post. This will create the permalink for your new poll.

    ![enter-business-matchup-poll-details](https://user-images.githubusercontent.com/43300142/158496755-db941097-a63b-429b-9970-890e89f9aa8a.png)

4.  Next, Enter the business location that your would like to base your matchup on. For example: San Francisco, CA.

5.  Then, Enter the type of businesses you want to matchup. For example: Pizza.

6.  Next, add a featured image that the social networks will use to create a thumbnail of your post when you share it.

7.  Finally, you can schedule this to post at a later date or publish now. This will gather the data we need and create the poll for you.

    ![business-matchup-poll-example](https://user-images.githubusercontent.com/43300142/158496794-a6216dd6-dde4-4ba6-9469-cbc4c4749b84.png)

8.  If you have Jetpack installed and use Publicize these posts are supported so that they automatically post to your connected accounts. For more information on how to do this please check out: https://jetpack.com/support/publicize/

9.  Check out your new Business Matchup poll and share it on social media!

    ![publish-business-matchup-poll-and-view](https://user-images.githubusercontent.com/43300142/158496822-3919ceaa-5260-435f-a6f0-e849304e0cc9.png)

## Screenshots ##

![publish-business-matchup-poll-and-view](https://user-images.githubusercontent.com/43300142/158496822-3919ceaa-5260-435f-a6f0-e849304e0cc9.png)

## Changelog ##

Here is the Changelog

### 0.1.3 ###

Use realpath when including files

### 0.1.2 ###

Fixed declared default constants to have unique 4 letter prefixes.

### 0.1.1 ###

Fixed declared default constants to have unique prefixes. Instead of API_KEY this plugin uses the prefix BM_ to make this BM_API_KEY
Added assets for the plugin icon and banner images
Added documentation for plugin configuration and integration with Yelp and StrawPoll

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
