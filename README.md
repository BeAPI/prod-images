# Prod Images #

[![CodeFactor](https://www.codefactor.io/repository/github/beapi/prod-images/badge)](https://www.codefactor.io/repository/github/beapi/prod-images)

## Description ##

This plugin allow to build development environment without copy data from uploads folder. Manage an failback with PHP and production assets.

## Installation ##

Define constants :
```
define( 'UPLOADS_STRUCTURE_NAME', 'wp-content/uploads, wp-content/blogs.dir' );
define( 'PROD_UPLOADS_URL', 'http://myproddomain' );
```

#### If WordPress Multisite + Apache HTTPD

You need to add the following rule before this line **wp-(content|admin|includes).*)**
```
RewriteRule ^([_0-9a-zA-Z-]+/)?(wp-content/uploads.*) $1 [L]
RewriteRule ^([_0-9a-zA-Z-]+/)?(wp-(content|admin|includes).*) $2 [L]
```

If your Multisite installation is an old verison with the blog.dir folder, you have to use this rule
```
RewriteRule ^([_0-9a-zA-Z-]+/)?files/(.+) $2 [L]
```

#### If WordPress + Nginx

It is likely that the NGINX configuration looks like this:
```
location ~* ^.+\.(ogg|ogv|svg|svgz|mp4|rss|atom|jpg|jpeg|gif|png|ico|zip|tgz|gz|rar|bz2|doc|xls|exe|ppt|tar|mid|$
    # access_log off;                                                                                          
    # log_not_found off;
                                                                                    
    expires max;                                                                                                                                                              
} 
```

With this configuration, there is no possible failback with WordPress, you must add the following statement in the condition:

```                                                                                 
try_files $uri $uri/ /index.php?$args;                                                                    
```

Full example :
```
location ~* ^.+\.(ogg|ogv|svg|svgz|mp4|rss|atom|jpg|jpeg|gif|png|ico|zip|tgz|gz|rar|bz2|doc|xls|exe|ppt|tar|mid|$
    # access_log off;                                                                                          
    # log_not_found off;
                                                                                    
    expires max;                                                                                             
    try_files $uri $uri/ /index.php?$args;                                                                   
} 
```


## Extra config ##

And in the wp-config.php
```
define( 'UPLOADS_STRUCTURE_NAME', 'wp-content/blogs.dir' );
```

Optionally you can add 
```
define( 'PROD_SSL_VERIFY', false );// default is true
```

## Changelog ##


### 0.1.9
* 13 May 2019
* Refactoring mimes types check (add SVG support)
* Improve readme (nginx part)

### 0.1.8
* 18 December 2018
* Minor code refactoring (phpcs)
* Add filter prod_images/remote_get_url

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
