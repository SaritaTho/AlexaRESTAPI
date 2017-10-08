# alexa-rest-api
It's not done. Go to [alexa.technoguyfication.com][1]

## How do I use it
**I ask very kindly that, instead of running your own production release of my software, please just use the [website][1] that's already there. It'll get constant updates and is overall easier to use. This code is here for people who want to study it, or run a private copy of it for personal use. Thanks.**

You'll need:
* A MySQL server capable of running InnoDB tables.
* Apache or Nginx or something equivalent.
* PHP version > 5.0 (this is a rough estimate, I haven't bothered to actually test it)
* Basic knowledge of what's going on

Assuming you have everything, let's get started!

1. Clone the repo somewhere and find the `src` folder.
2. Get your MySQL and Apache (or equivalent) server started.
3. Run [this script](database-setup.sql) in your MySQL database to get it all ready to go.
4. [Create a reCAPTCHA site](https://www.google.com/recaptcha/admin) and take note of your site key and secret.
5. [Configure everything](https://github.com/Technoguyfication/alexa-rest-api/wiki/Configuration) using the wiki page.
6. Adjust the `.htaccess` file in the document root to your liking.

#### Congrats! You're done.

[1]: https://alexa.technoguyfication.com
