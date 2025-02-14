<?php

namespace Kunoichi\TocGenerator;


/**
 * Toc Generator for WordPress
 *
 * @package Kunoichi\TocGenerator
 */
class WpParser extends Parser {

	protected $post_id = 0;

	protected $done = false;

	protected $page_counter = 0;

	/**
	 * Constructor
	 *
	 * @param int $max_depth
	 * @param bool $ignore_deeper
	 * @param string $id
	 * @param string $item_class
	 */
	public function __construct( $max_depth = 3, $ignore_deeper = false, $id = 'content-section-', $item_class = WpItem::class ) {
		parent::__construct( $max_depth, $ignore_deeper, $id, $item_class );
		add_action( 'wp_head', [ $this, 'prepare' ] );
		add_filter( 'the_content', [ $this, 'post_content' ] );
		add_action( 'kunoichi_toc_generate', [ $this, 'generate' ], 10 );
	}

	/**
	 * Executed in wp_head
	 */
	public function prepare() {
		if ( ! is_singular() ) {
			// Do nothing.
			return;
		}
		$this->post_id = get_queried_object_id();
	}

	/**
	 * Parse HTML content
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function post_content( $content ) {
		if ( get_the_ID() !== $this->post_id ) {
			return $content;
		}
		$content       = $this->add_link_html( $content );
		$this->counter = 0;
		if ( ! $this->done ) {
			$this->save_parsed_html( $content );
			$this->done = true;
		}

		return $content;
	}

	/**
	 * Add link id to html elements.
	 *
	 * @param string $html
	 *
	 * @return string
	 */
	public function add_link_html( $html ) {
		$contents = preg_split( '/<!--nextpage-->/u', $html );
		if ( is_array( $contents ) ) {
			$replaced_html      = '';
			$this->page_counter = 0;
			foreach ( $contents as $content ) {
				$this->counter = 0;
				++$this->page_counter;
				$replaced_html .= preg_replace_callback( '/<(h[1-6])([^>]*?)>/u', [ $this, 'convert_link' ], $content );
			}

			return $replaced_html;
		} else {
			return preg_replace_callback( '/<(h[1-6])([^>]*?)>/u', [ $this, 'convert_link' ], $html );
		}
	}

	/**
	 * Add link to title elements.
	 *
	 * @param string[] $matches
	 *
	 * @return string
	 */
	protected function convert_link( $matches ) {
		++$this->counter;
		$attributes = $matches[2];
		if ( preg_match( '/id=\'|"([\'"]*)(\'|")/u', $matches[2], $id_matches ) ) {
			$id = $id_matches[1];
		} else {
			$id          = sprintf( '%s%d', $this->id_prefix, $this->counter );
			$attributes .= sprintf( ' id="%s"', $id ) . $matches[2];
		}
		// Add an attribute for the page number.
		$attributes .= sprintf( ' data-page="%d"', $this->page_counter );

		return sprintf( '<%s%s>', $matches[1], $attributes );
	}

	/**
	 * If post id is
	 *
	 * @param int $post_id
	 */
	public function generate( $post_id ) {
		if ( ! $this->done ) {
			$content = apply_filters( 'the_content', get_post( $post_id )->post_content );
		}
		if ( $this->post_id === $post_id ) {
			echo $this->get_toc();
		}
	}

	/**
	 * Render post id.
	 *
	 * @param int $post_id
	 */
	public static function render( $post_id ) {
		do_action( 'kunoichi_toc_generate', $post_id );
	}
}
