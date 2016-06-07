# Prod Images #

## Description ##

This plugin allow to build development environment without copy data from uploads folder. Manage an failback with PHP and production assets.

## Important to know ##

Define constants :
```
define( 'UPLOADS_STRUCTURE_NAME', 'wp-content/uploads' );
define( 'PROD_UPLOADS_URL', 'http://myproddomain' );
```

If installation WP Multisite :
You need to add the following rule before this line 
```
RewriteRule ^([_0-9a-zA-Z-]+/)?(wp-(content|admin|includes).*) $2 [L]
RewriteRule ^([_0-9a-zA-Z-]+/)?(wp-content/uploads.*) $1 [L]
```

## Changelog ##

### 0.1
* 18 Feb 2016
* initial

### 0.1.1
* 06 April 2016
* fix for wp_debug
