<?php

namespace Kunoichi\TocGenerator;

/**
 * Item for WordPress
 *
 * @package Kunoichi\TocGenerator
 */
class WpItem extends Item {

	/**
	 * Item constructor.
	 *
	 * @param \DOMNode $dom
	 */
	public function __construct( $dom ) {
		parent::__construct( $dom );
	}

	/**
	 * Get markup of contents.
	 *
	 * @return string
	 */
	public function get_markup() {
		global $page;
		$item_page = $this->page();

		if ( $page !== $item_page ) {
			// @see <a href="https://developer.wordpress.org/reference/functions/_wp_link_page/">_wp_link_page()</a>
			global $wp_rewrite;
			$post       = get_post();
			$query_args = array();
			$item_page  = $this->page();

			if ( 1 === $item_page ) {
				$url = get_permalink();
			} else {
				if ( ! get_option( 'permalink_structure' ) || in_array( get_post_status(), array(
					'draft',
					'pending',
				), true ) ) {
					$url = add_query_arg( 'page', $item_page, get_permalink() );
				} elseif ( 'page' === get_option( 'show_on_front' ) && (int) get_option( 'page_on_front' ) === get_the_ID() ) {
					$url = trailingslashit( get_permalink() ) . user_trailingslashit( "$wp_rewrite->pagination_base/" . $item_page, 'single_paged' );
				} else {
					$url = trailingslashit( get_permalink() ) . user_trailingslashit( $item_page, 'single_paged' );
				}
			}

			if ( is_preview() ) {
				if ( ( 'draft' !== $post->post_status ) && isset( $_GET['preview_id'], $_GET['preview_nonce'] ) ) {
					$query_args['preview_id']    = wp_unslash( $_GET['preview_id'] );
					$query_args['preview_nonce'] = wp_unslash( $_GET['preview_nonce'] );
				}
				$url = get_preview_post_link( $post, $query_args, $url );
			}
			$link = sprintf( '%s%s', $url, $this->href() );

		} else {
			$link = $this->href();
		}

		return sprintf( '<a href="%s" data-page="%s">%s</a>', esc_url( $link ), esc_attr( $item_page ), esc_html( $this->text() ) );
	}

	/**
	 * Return page attributes
	 *
	 * @return int
	 */
	public function page() {
		return (int) $this->dom->getAttribute( 'data-page' );
	}
}
