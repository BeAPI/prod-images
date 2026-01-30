# Prod Images - WordPress plugin proxy for medias #

[![CodeFactor](https://www.codefactor.io/repository/github/beapi/prod-images/badge)](https://www.codefactor.io/repository/github/beapi/prod-images)
[![Ask DeepWiki](https://deepwiki.com/badge.svg)](https://deepwiki.com/BeAPI/prod-images)

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

## Debug mode ##

When developing or troubleshooting, you can append `?debug` to any proxied asset URL. The plugin will then:

- **Not send** any content-type or image headers (no binary output)
- **Display** a dump of:
  - The remote URL actually called (the `debug` parameter is never sent to production)
  - The request headers as built by WordPress (User-Agent, Accept, etc.)
  - The response headers returned by the production server

Example: opening  
`https://your-dev.local/wp-content/uploads/2024/01/photo.jpg?debug`  
in your browser will show the debug output instead of the image. Use this to verify the production URL, check that the browser User-Agent is forwarded, and inspect the remote response (status, cache headers, etc.).

## Changelog ##

See [CHANGELOG.md](CHANGELOG.md) for the full version history.
