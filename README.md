# toc-generator
Generate table of contents from h1-h6 tags.

## Installation

Run composer.

```bash
comopser require kunoichi/toc-generator
```

And include autoloader.

```php
require __DIR__ . '/vendor/autoload.php';
```

## Usage

### PHP

W.I.P

### WordPress

In your `functions.php`

```php
// Register TOC.
// If you have theme option for it,
// detect conditions.
add_action( 'init', function() {
   	$parser = new Kunoichi\TocGenerator\WpParser();
	$parser->set_title( __( 'Table of Contents', 'your-theme' ) );
} );
```

And render TOC where you want(e.g. In your `singular.php`).
You need `$post_id` to render TOC.
In this case, `get_queried_object_id()` is used.

```php
Kunoichi\TocGenerator\WpParser::render( get_queried_object_id() );
```

