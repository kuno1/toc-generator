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

	/**
	 * Constructor
	 *
	 * @param int $max_depth
	 * @param bool $ignore_deeper
	 * @param string $id
	 */
	public function __construct( $max_depth = 3, $ignore_deeper = false, $id = 'content-section-' ) {
		parent::__construct( $max_depth, $ignore_deeper, $id );
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
