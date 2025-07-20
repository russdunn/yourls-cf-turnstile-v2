Cloudflare Turnstile for YOURLS Admin v2 (Improved)
====================

Secure the admin login page with Turnstile by Cloudflare.

>This plugin was an initial fork of [SophiaAtkinson's](https://github.com/SophiaAtkinson/yourls-cf-turnstile) yourls-cf-turnstile but due to critical issues in the original, it was completely rewritten and released independently. Credit to Sophia for the initial inspiration.

Description
-----------
Adds Cloudflare Turnstile to the YOURLS Admin login.

Features
------------
- Adds a Cloudflare Turnstile widget to the login form
- Option to show or hide the widget (set with Cloudflare)
- Disables the login button until a successful validation
- Adds a 'not-allowed' cursor to the login button while disabled

Installation
------------
1. In `/user/plugins`, create a new folder named `yourls-cf-turnstile-v2`.
2. Drop these files in that directory.
3. Change `CF_TS_SITE_KEY` and `CF_TS_SECRET_KEY` to the keys found on the [Turnstile Page](https://dash.cloudflare.com/?to=/:account/turnstile)
4. Go to the Plugins administration page ( *eg* `http://sho.rt/admin/plugins.php` ) and activate the plugin.
