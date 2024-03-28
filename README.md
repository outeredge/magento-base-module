outer/edge Magento Base Module
============================================

### Show recent `core_config_data` changes

`$ bin/magento outeredge:config`

### Nuke (removes generated + static files and flushes cache)

`$ bin/magento outeredge:nuke`

## Features

This module provides the following useful features for Magento 2:

### Admin

* `Block/Adminthtml/Widget` ??
* Banner images for CMS pages - adds an option to upload a banner image to all page in `Content > Pages` which can be pulled through to the frontend.
* `Create` section in admin menu for quick actions:
  - Create new order
  - Create product

### SEO

* Canonical URL improvements
 - Add canonical URL for all CMS pages
 - Include pagination in category canonical URLs as per Google recommendation ([View Google Recommendation](https://developers.google.com/search/docs/specialty/ecommerce/pagination-and-incremental-page-loading#use-urls-correctly))
* Set robots meta tag to `NOINDEX/NOFOLLOW` for:
  - Common query strings such as list ordering, limit, store codes etc
  - Search results
  - Product review list

### Dev

* Cookiebot features:
  - Don't move cookiebot tags to footer when move JS to footer is enabled
  - Block YouTube cookies unless Cookiebot has given consent
* Helper classes:
  - Asset helper - get assets
  - Image helper - get media images and resize, crop etc.
  - Config helper - get values from Magento config
* Console commands
  - `outeredge:nuke` for removing all cache and static files
  - `outeredge:config` lists all recent config changes
