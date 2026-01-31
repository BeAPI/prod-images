# Changelog

All notable changes to this project will be documented in this file.

## [0.2.1] - 2025-01-31

### Changed

- Composer package name: `beapi/prod-images` → `bea/prod-images`.

## [0.2.0] - 2025-01-30

### Added

- **Debug mode**: append `?debug` to any proxied asset URL to display debugging information without sending content-type headers. Output includes:
  - The remote URL called (production URL without the `debug` parameter)
  - Request headers as merged by WordPress (including User-Agent and defaults)
  - Response headers from the production server
- **User-Agent forwarding**: the incoming request’s User-Agent is now sent to the production server when fetching assets, so the remote request emulates the browser.

### Changed

- The `debug` query parameter is stripped from the remote URL and is never sent to production.
- Debug mode captures request headers via the `http_request_args` filter to show all headers WordPress sends (not only those defined by the plugin).

## [0.1.9] - 2019-05-13

- Refactoring mimes types check (add SVG support)
- Improve readme (nginx part)
- Disable plugin feature if main constant is not defined

## [0.1.8] - 2018-12-18

- Minor code refactoring (phpcs)
- Add filter `prod_images/remote_get_url`

## [0.1.7] - 2018-03-14

- Add two HTTP headers for easier debugging

## [0.1.6] - 2018-03-12

- Add constant `PROD_SSL_VERIFY` and filter `prod_images/remote_get_args`

## [0.1.5] - 2018-01-08

- Allow multiple values for `UPLOADS_STRUCTURE_NAME`

## [0.1.4] - 2016-09-08

- Fix fatal error on single site due to `is_subdomain_install()` function

## [0.1.3] - 2016-08-23

- Fix loading of local images

## [0.1.2] - 2016-07-28

- Fix cached image with WP Rocket

## [0.1.1] - 2016-04-06

- Fix for wp_debug

## [0.1.0] - 2016-02-18

- Initial release
