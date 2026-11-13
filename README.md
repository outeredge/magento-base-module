# outer/edge Magento Base Module

This module provides the following useful features for Magento 2:

## Admin

* Additional widget types
  - Image picker
  - Text field
* Enabled `anchor` button in WYSIWYG
* Banner images for CMS pages - adds an option to upload a banner image to all page in `Content > Pages` which can be pulled through to the frontend.
* `Create` section in admin menu for quick actions:
  - Create new order
  - Create product
* Enforces media URL seen by the admin to be NULL always (i.e. so CDN isn't used)
* Adds bulk actions to Cart Price Rules

## SEO

* Canonical URL improvements
  - Add canonical URL for all CMS pages
  - Include pagination in category canonical URLs as per [Google Recommendation](https://developers.google.com/search/docs/specialty/ecommerce/pagination-and-incremental-page-loading#use-urls-correctly)
* Set robots meta tag to `NOINDEX/FOLLOW` for:
  - [Common query strings](https://github.com/outeredge/magento-base-module/blob/master/Plugin/Robots.php#L10) such as list ordering, limit, store codes etc
  - Search results
  - Product review list
* Forces a 404 HTTP response for placeholder images
* Adds preconnect headers for google.com/gstatic.com

## Cookiebot, Termly & CookieYes

The CMP provider (Cookiebot / Termly / CookieYes) can be selected in `Stores > Configuration > outer/edge > Cookie Restriction Features > Cookie Restriction`. Configuration is website-scoped so different CMP accounts can be used per website.

  - Don't move CMP tags to footer when move JS to footer is enabled
  - Block YouTube cookies unless Cookiebot has given consent (*Enabled by default*)
  - Cookiebot declaration template for use on cookie policy CMS page
  - Import Termly generated Cookie Policy for use on cookie policy CMS page
  - Automatically injects the CookieYes banner script via the configured Website ID (relies on CookieYes auto-blocking for script/iframe consent)

## API

 - `/rest/V1/site_status/get` - To remotely obtain recent config changes and indexer statuses

## Developer Tools

* Helper classes:
  - Asset helper - get assets
  - Image helper - get media images and resize, crop etc.
  - Config helper - get values from Magento config
* Console commands
  - `outeredge:nuke` for removing all cache and static files
  - `outeredge:config` lists all recent config changes
* Disables jQuery mutate console messages
* Forces the first encryption key to always be used for media assets

### Console Commands

| Command | Description |
|---|---|
| `$ bin/magento outeredge:config` | Show recent `core_config_data` changes (use `--save` to save differences shown to config.php and `--force` to overwrite any existing config) |
| `$ bin/magento outeredge:nuke`   | Remove generated + static files and flushes cache) |
| `$ bin/magento outeredge:eav-clean` | Removes non-existent types from EAV attributes |
| `$ bin/magento outeredge:convert-quote` | Converts a quote into an order |

### Cookiebot Declaration

To add the Cookiebot cookie declaration form into your cookie policy CMS page, add the following snippet into the CMS page in the Magento admin:

```
{{block class="OuterEdge\\Base\\Block\\Cmp" template="OuterEdge_Base::cookiebot/declaration.phtml"}}
```

### Termly Cookie Policy

To add the Termly cookie policy into your cookie policy CMS page, add the following snippet into the CMS page in the Magento admin:

```
{{block class="OuterEdge\\Base\\Block\\Cmp" template="OuterEdge_Base::termly/cookie-policy.phtml"}}
```

### CookieYes Cookie Declaration

To add the CookieYes cookie declaration into your cookie policy CMS page, add the following snippet into the CMS page in the Magento admin:

```
{{block class="OuterEdge\\Base\\Block\\Cmp" template="OuterEdge_Base::cookieyes/declaration.phtml"}}
```

### Multistore projects

Add `multistore.php` to the top of the `composer.json` autoload/files section to allow separate config files (and thus databases) _per store_ on the same instance/repository:

* Add to `composer.json` :
```
    "autoload": {
        "files": [
            "vendor/outeredge/magento-base-module/multistore.php",
            ...
```

* Then create individual env.php files for each, naming them `env.php.STORE_CODE` replacing `STORE_CODE` as applicable.
