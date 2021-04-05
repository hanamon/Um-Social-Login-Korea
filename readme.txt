um-social-login 2.1.0 플러그인을 커스텀하였습니다.
정상 작동 됩니다.
1. 네이버, 카카오 소셜로그인 추가
2. CSS 수정 (네이버, 카카오 소셜로그인 버튼 아이콘)

=== Ultimate Member - Social Login ===
Author URI: https://ultimatemember.com/
Plugin URI: https://ultimatemember.com/extensions/social-login/
Contributors: ultimatemember, champsupertramp, nsinelnikov
Donate link:
Tags: social login, networks, community, discussion
Requires at least: 5.0
Tested up to: 5.7
Stable tag: 2.3.9
License: GNU Version 2 or Any Later Version
License URI: http://www.gnu.org/licenses/gpl-3.0.txt
Requires UM core at least: 2.1.0

The social login extension allows users to easily register/login to your site using their social network accounts (Facebook, Twitter, Google+, LinkedIn, Instagram, VK / VKontakte).

== Description ==

The social login extension allows users to easily register/login to your site using their social network accounts (Facebook, Twitter, Google+, LinkedIn, Instagram, VK / VKontakte).

= Key Features: =

* Add social login buttons to registration and login forms.
* Decide which social login buttons to use on your site (e.g you can activate Facebook and Twitter but keep Google+ and LinkedIn deactivated).
* 2-step process for users registering via social network (this ensures all required data is collected before registration is complete).
* Users can instantly login to your site without entering any details after they have registered on site.
* Users can connect/disconnect from social networks via their account page.
* Show social login buttons anywhere on site using shortcodes.
* Easily edit the shortcodes to customize what is shown on your site.
* Profile photos syncing (e.g if a user connects via Facebook their Facebook profile photo will show on your site).

= Supported Social Networks: =

* Facebook
* Twitter
* Google
* LinkedIn
* Instagram
* VKontakte

= Important info: =

* Due to social network policies and to avoid app reviews this extension only imports basic user information from the networks.
* This extension will not allow you to post on a user’s network after they register/login.
* Use of this extension requries you to setup an application on each of the social network’s websites.

Read about all of the plugin's features at [Ultimate Member - Social Login](https://ultimatemember.com/extensions/social-login/)

= Documentation & Support =

Got a problem or need help with Ultimate Member? Head over to our [documentation](http://docs.ultimatemember.com/category/19-social-login) and perform a search of the knowledge base. If you can’t find a solution to your issue then you can create a topic on the [support forum](https://wordpress.org/support/plugin/ultimate-member).

== Installation ==

1. Activate the plugin
2. That's it. Go to Ultimate Member > Settings > Extensions > Social Login to customize plugin options
3. For more details, please visit the official [Documentation](http://docs.ultimatemember.com/category/19-social-login) page.

== Changelog ==

= 2.3.9: March 29, 2021 =

* Fixed: Cache & Session compatibility issue
* Fixed: Elementor compatibility issue
* Fixed: Duplicate overlay calls

= 2.3.8: December 22, 2020 =

* Fixed: Incompatibility issue with WooCommerce and Eventribe

= 2.3.7: December 16, 2020 =

* Fixed: A conflict with WooCommerce Ajax
* Fixed: Global redirection and compability with varnish cache
* Fixed: Redirection issues for Login/Register and custom shortcode
* Tweak: WordPress 5.6 compatibility (fixed issue with deprecated .load() function in JS)

= 2.3.6: October 26, 2020 =

* Added: Filter to allow automatic login with existing email address
* Fixed: Overflow forms in social register form
* Fixed: Duplicate welcome email
* Fixed: Session for custom role selection
* Fixed: Social authentication globally

= 2.3.5: August 24, 2020 =

* Fixed: WeChat Provider scopes
* Fixed: Minified scripts

= 2.3.4: August 11, 2020 =

* Added: WeChat Provider
* Added: Hooks for UM extensions integration
* Added: Allow form columns in Social Overlay
* Added: Hook to modify providers configuration
* Added: *.pot file for translations
* Changed: Google icon to match brand guidelines
* Fixed: Sanitation for role assignment with shortcode
* Fixed: Compatibility issue with plugins using OAuth
* Fixed: Redirection issue in child window

= 2.3.3: April 1, 2020 =

* Added: Synced social network avatar on sso connection
* Changed: Integration with myCRED and Notifications
* Fixed: Account activation process after Social Authentication on Registration
* Fixed: Login error notice
* Fixed: Spam registration issues
* Fixed: Account email activation on social registration
* Fixed: Fix SSO avatar size
* Fixed: An issue with disabled Login/Register fields outside the SSO Overlay
* Fixed: Bypass social login authentication

= 2.3.2: February 13, 2020 =

* Fixed: Allow SSO overlay fields disabled
* Fixed: Error message display & disabled fields

= 2.3.1: February 11, 2020 =

* Fixed: Script errors in social network graphs

= 2.3.0: December 18, 2019 =

* Added: hybridauth library for social connect
* Added: Sanitize functions for request variables
* Added: esc_attr functions to avoid XSS vulnerabilities
* Added: ability to change templates in theme via universal method UM()->get_template()

= 2.2.0: November 11, 2019 =

* Fixed: Instagram API
* Fixed: Integration with myCRED, changed API function

= 2.1.7: July 19, 2019 =

* Fixed: myCRED integration

= 2.1.6: June 25, 2019 =

* Fixed: G-Suite Gmail accounts

= 2.1.5: June 24, 2019 =

* Added: Filter to modify redirect URI for enterprise Gmail accounts
* Fixed: Google OAuthentication issue
* Fixed: Uninstall process

= 2.1.4: May 2, 2019 =

* Fixed: FB button styles & texts

= 2.1.3: April 24, 2019 =

* Important LinkedIn API breaking changes
   - All developers need to migrate to Version 2.0 of our APIs and OAuth 2.0 by May 1. Read more https://engineering.linkedin.com/blog/2018/12/developer-program-updates

 * In this update, the API is now using the `r_lite_profile` by default: https://docs.microsoft.com/en-us/linkedin/shared/references/v2/profile/lite-profile

 * If you need to use the `r_basic_profile` and `r_full_profile` access permissions, you need to extend the our Linkedin API with the following UM Social Login filters:
   + um_social_login_linked_scope( $scopes ) - adding or removing supported linkedin scope
   + um_social_login_linked_profile( $profile, $info ) - merge data from api response to match field keys in the 2nd step register process


= 2.1.2: April 10, 2019 =

* Tweak: LinkedIn integration with new app versions
* Fixed: escaped URLs to avoid XSS vulnerabilities

= 2.1.1: March 29, 2019 =

* New: Option "Keep user signed in?" added, it allows user to stay signed in
* Tweak: Update Facebook Graph to v.3.2
* Fixed: Logout before social login

= 2.1.0 October 14, 2018 =

* Optimized: JS/CSS includes

= 2.0.9 October 2, 2018 =

* Fixed: Text changes + new translation files

= 2.0.8 September 14, 2018 =

* Fixed: Image uploader on multisites
* Fixed: Google SSO

= 2.0.7 August 31, 2018 =

* Fixed: WP native AJAX handlers
* Fixed: Remove facebook user_link retrieval by default

= 2.0.6 August 9, 2018 =

* Fixed: styles at social content buttons

= 2.0.5 July 18, 2018 =

* Fixed: Social Login registration form overlay

= 2.0.4 July 3, 2018 =

* Fixed: Social Icons CSS
* Fixed: Social Disconnect

= 2.0.3 April 27, 2018 =

* Added: Loading translation from "wp-content/languages/plugins/" directory
* Fixed: Social Libraries Init

= 2.0.2 April 17, 2018 =

* Fixed: Fixed VK connect
* Fixed: Add all user meta from providers

= 2.0.1 February 8, 2018 =

* Fixed: Facebook autoload
* Tweak: UM2.0 compatibility

= 1.4.6 December 8, 2016 =

* Fixed: Redirection after authentication
* Fixed: Redirection on UM Message login modal
* Fixed: LinkedIn provider authentication
* Fixed: Specific roles and widget redirections
* Fixed: Google API library integration
* Fixed: Remove notices from Ajax Requests
* Fixed: Remove notices from WP CLI
* Fixed: Shortcode Login Redirection
* Fixed: After disconnection's redirection

= 1.4.5 June 27, 2016 =

* Fixed: Twitter authentication invalid oauth_verifier
* Fixed: Remove notices

= 1.4.4 June 20, 2016 =

* Tweak: Update linkedin to oauth2
* Tweak: Update EDD update class
* Added: Add filter hooks in facebook init
* Added: redirect url as button attributes
* Added: filter hooks and Fix profile photo URLs
* Added: an option to assign custom role in social button shortcodes
* Fixed: social login buttons and sessions
* Fixed: Remove notices
* Fixed: facebook authentication
* Fixed: license and updater
* Fixed: plugin updater
* Fixed: login and pending review users
* Fixed: Allow form without first name and last name
* Fixed: redirection
* Fixed: user email and notification
* Fixed: loading social buttons via Ajax
* Fixed: Allow authentication in any pages
* Fixed: translation string
* Fixed: duplicate class call

= 1.4.3 February 29, 2016 =

* Fixed: Fix callback URLs to avoid mismatch URI authorization
* Fixed: Fix twitter callback URL on active page refresh
* Tweak: Update Google API Library

= 1.4.2 February 24, 2016 =

* Fixed: Fix script enqueue
* Fixed: Fix social authentication
* Fixed: Add trim to remove whitespace from API keys and secrets

= 1.4.1 February 14, 2016 =

* Tweak: Update Facebook API library
* Tweak: Update twitter api library
* Tweak: Set current url as api callback for all providers and display overlay
* Fixed: Fix facebook api sessions
* Fixed: Fix redirect URIs in account page
* Fixed: Fix social overlay form option
* Fixed: Fix google authentication
* Fixed: Fix instagram api
* Fixed: Remove notice

= 1.4.0 January 29, 2016 =

* Tweak: Plugin updater updated to latest version
* Tweak: Add option to change social login/register form in the overlay

= 1.3.9 January 25, 2016 =

* New: added Dutch language support
* Fixed: Fix facebook cross site forgery issue in filezilla
* Fixed: Fixed duplicate recaptcha validation
* Fixed facebook cross site origin issue
* Fixed: Added email validation
* Fixed: Show overlay on social login form's current url
* Fixed: Fix fb auth processed when registering

= 1.3.8 January 6, 2016 =

* Tweak: Cleaner way to get callback url
* Fixed: Compatibility fix for showing overlay code in footer

= 1.3.7 December 13, 2015 =

* Fixed: Facebook mismatched csrf tokens
* Fixed: Twitter consumer validation and notices

= 1.3.6 December 11, 2015 =

* Tweak: compatibility with WP 4.4

= 1.3.5 December 8, 2015 =

* Initial release