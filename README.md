# Instagram Feed Plugin

> IMPORTANT! Instagram changed there routes. so the `/media` route does not work anymore. So I had to change the Code to fetch the data via `?__a=1` (by the way thanks to @Bussmeyer for giving the advise). So if you will update to this version, please check your custom templates, the data structure changed.

The **Instagram Feed** Plugin is for [Grav CMS](http://github.com/getgrav/grav). Get your Instagram Feed on your Grav Application.

> NOTE: To use this plugin, you should pass a ***public*** Instagram feed.

**All what you need is a valid Instagram user. NO access_token is needed.**

## Installation

Installing the Instagram Feed plugin can be done in one of two ways. The GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.

### GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm) through your system's terminal (also called the command line).  From the root of your Grav install type:

    bin/gpm install instagram-feed

This will install the Instagram Feed plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/instagram-feed`.

### Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `instagram-feed`. You can find these files on [GitHub](https://github.com/danny-stey/grav-plugin-instagram-feed) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/instagram-feed
	
> NOTE: This plugin is a modular component for Grav which requires [Grav](http://github.com/getgrav/grav) and the [Error](https://github.com/getgrav/grav-plugin-error) and [Problems](https://github.com/getgrav/grav-plugin-problems) to operate.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/instagram-feed/instagram-feed.yaml` to `user/config/plugins/instagram-feed.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
route: /
```

## Usage

You could place the following code into your twig template:

    {{ instagram_feed() }}
    
And it will render the ` user/plugins/instagram-feed/templates/partials/instagram-feed.html.twig`, that looks like this:

```twig
{% if feed %}
<ul class="instagram-feed">
    {% for item in feed|slice(0, count) %}
        <li>
            <a href="{{ item.link }}" target="_blank">
                <img src="{{ item.images.thumbnail.url }}" alt="">
            </a>
        </li>
    {% endfor %}
</ul>
{% else %}
<p>
    Could not load the Instagram feed!
</p>
{% endif %}
```

### Customize template
if you want to modify the template please copy that template file into your `user/themes/your-theme/templates/partials` folder and name it `instagram-feed.html.twig`. It will automatically take your file instead of the default one.

### Define a special template
if you want to specify a special template for a special page or something in that way. you could pass the template path to the feed function in twig. Like this:

    {{ instagram_feed('path/to/template.html.twig') }}

### Dynamic User
Sometimes you would generate the feed with a dynamic username. Therefore I developed another twig function like this:
    
     {{ instagram_feed_of('username' [,'path/to/template.html.twig']) }}
     
> NOTE: you could also pass an optional individual template path.     

    
### Behind the scenes
The plugin is requesting the Instagram feed url `https://www.instagram.com/username/?__a=1`. The data that is passed to your template are exactly the same like the response of Instagram. If you want to know, what dataset do you get. Comment the `dump($feed)` in and you will get the feed dataset.

### Settings in the Admin Panel
if you are using the Admin Plugin of Grav, you have the following options:

- set the amount of images, which should be shown
- set the username of your Instagram Account
- set the cache expiring time

> NOTE: Your Instagram Account has to be public not private! It does not make any sense, when you wish your account should be private and you will display your feed on your public website. 


