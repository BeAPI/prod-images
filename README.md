# Prod Images #

## Description ##

This plugin allow to build development environment without copy data from uploads folder. Manage an failback with PHP and production assets.

## Important to know ##

Define constants :
```
define( 'UPLOADS_STRUCTURE_NAME', 'wp-content/uploads, wp-content/blogs.dir' );
define( 'PROD_UPLOADS_URL', 'http://myproddomain' );
```

If installation WP Multisite :
You need to add the following rule before this line **wp-(content|admin|includes).*)**
```
RewriteRule ^([_0-9a-zA-Z-]+/)?(wp-content/uploads.*) $1 [L]
RewriteRule ^([_0-9a-zA-Z-]+/)?(wp-(content|admin|includes).*) $2 [L]
```

If your Multisite installation is an old verison with the blog.dir folder, you have to use this rule
```
RewriteRule ^([_0-9a-zA-Z-]+/)?files/(.+) $2 [L]
```

And in the wp-config.php
```
define( 'UPLOADS_STRUCTURE_NAME', 'wp-content/blogs.dir' );
```

Optionally you can add 
```
define( 'PROD_SSL_VERIFY', false );// default is true
```

## Changelog ##

### 0.1.7
* 14 March 2018
* Add two HTTP headers for allow easier debugging

### 0.1.6
* 12 March 2018
* Add constant PROD_SSL_VERIFY and filter prod_images/remote_get_args

### 0.1.5
* 08 January 2018
* Allow multiples values for UPLOADS_STRUCTURE_NAME 

### 0.1.4
* 08 September 2016
* fix fatal error on single site due to is_subdomain_install() function

### 0.1.3
* 23 August 2016
* fix loading of local images

### 0.1.2
* 28 July 2016
* fix cached image withe WP Rocket

### 0.1.1
* 06 April 2016
* fix for wp_debug

### 0.1
* 18 Feb 2016
* initial
