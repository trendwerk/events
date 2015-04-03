Events
===========

Events for WordPress.

This plugin only delivers the most basic functionality for events. It doesn't contain any templates.

## Installation
If you're using Composer to manage WordPress, add this plugin to your project's dependencies. Run:
```sh
composer require trendwerk/events 1.0.2
```

Or manually add it to your `composer.json`:
```json
"require": {
	"trendwerk/events": "1.0.2"
},
```

## Templates

This plugin does not provide any templates. They have to be created in your theme. This works just like any other post type:

- `archive-events.php`
- `single-events.php`

#### Meta

There is some additional post meta available for use in your templates:

- `_start` Start date + time (UNIX timestamp)
- `_end` End date + time (UNIX timestamp)
- `_location`
- `_address`
- `_zipcode`
- `_city`
- `_cost`

## Past events

There's an endpoint available which contains past events. Default: `events/archive`.

#### Post type archive
Use [`get_post_type_archive_link`](https://developer.wordpress.org/reference/functions/get_post_type_archive_link/), like with any other post type.

#### Get events archive slug
```
apply_filters( 'events_archive_slug', '' );
```

## Hooks

#### Post type
```
apply_filters( 'events_post_type', $args );
```
`$args` contains all post type settings.

#### Archive slug
```
apply_filters( 'events_archive_slug', '' );
```
