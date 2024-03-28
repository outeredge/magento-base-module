outer/edge Magento Base Module
============================================

## Setup

Configuration for Magento/Setup (before Interceptors can be used).

To enable our classes this file needs to be copied into ~/setup/config/autoload/
which can be done like so composer.json:

```
    "scripts": {
        "pre-autoload-dump": [
            "mkdir -p setup/config/autoload && cp vendor/outeredge/magento-base-module/outeredge.local.php setup/config/autoload/",
            ...
```

### Console Commands

| Command | Description |
|---|---|
| `$ bin/magento outeredge:config` | Show recent `core_config_data` changes |
| `$ bin/magento outeredge:nuke`   | Remove generated + static files and flushes cache) |

### Cookiebot Declaration

To add the Cookiebot cookie declaration form into your cookie policy CMS page, add the following snippet into the CMS page in the Magento admin:

```
{{block class="Magento\Framework\View\Element\Template" template="OuterEdge_Base::cookiebot/declaration.phtml"}}
```

### Multistore projects

Add `multistore.php` to the `composer.json` autoload files section allowing separate config files (and thus databases) per store on the same instance/repository:

* Add to `composer.json` :
```
    "autoload": {
        "files": [
            "vendor/outeredge/magento-base-module/multistore.php",
            ...
```

* Then create individual env.php files for each, naming them `env.php.STORE_CODE` replacing `STORE_CODE` as applicable. 

## All Features

This module provides the following useful features for Magento 2:

### Admin

* Additional widget types
  - Image picker
  - Text field
* Enabled `anchor` button in WYSIWYG 
* Banner images for CMS pages - adds an option to upload a banner image to all page in `Content > Pages` which can be pulled through to the frontend.
* `Create` section in admin menu for quick actions:
  - Create new order
  - Create product
* Enforces media URL seen by the admin to be NULL always (i.e. so CDN isn't used)

### SEO

* Canonical URL improvements
  - Add canonical URL for all CMS pages
  - Include pagination in category canonical URLs as per [Google Recommendation](https://developers.google.com/search/docs/specialty/ecommerce/pagination-and-incremental-page-loading#use-urls-correctly)
* Set robots meta tag to `NOINDEX/NOFOLLOW` for:
  - [Common query strings](https://github.com/outeredge/magento-base-module/blob/master/Plugin/Robots.php#L10) such as list ordering, limit, store codes etc
  - Search results
  - Product review list
* Forces a 404 HTTP response for placeholder images
* Adds preconnect headers for google.com/gstatic.com

### Cookiebot

  - Don't move cookiebot tags to footer when move JS to footer is enabled
  - Block YouTube cookies unless Cookiebot has given consent (*Enabled by default*)
  - Cookie declaration template for use on cookie policy CMS page

### Dev

* Helper classes:
  - Asset helper - get assets
  - Image helper - get media images and resize, crop etc.
  - Config helper - get values from Magento config
* Console commands
  - `outeredge:nuke` for removing all cache and static files
  - `outeredge:config` lists all recent config changes
* Adds support for newer version of MariaDB
* Disables jQuery mutate console messages
* `multistore.php` allows separate config files (and thus databases) per store on the same instance.