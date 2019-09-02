<?php

namespace Kunoichi\TocGenerator;


/**
 * Item for TOC
 *
 * @package Kunoichi\TocGenerator
 */
class Item {
	
	/**
	 * @var \DOMNode
	 */
	private $dom = null;
	
	/**
	 * Item constructor.
	 *
	 * @param \DOMNode $dom
	 */
	public function __construct( $dom ) {
		$this->dom = $dom;
	}
	
	/**
	 * Return id attributes
	 *
	 * @return string
	 */
	public function id() {
		return (string) $this->dom->getAttribute( 'id' );
	}
	
	/**
	 * Get text
	 *
	 * @return string
	 */
	public function text() {
		return trim( $this->dom->textContent );
	}
	
	/**
	 * Get href
	 *
	 * @return string
	 */
	public function href() {
		return sprintf( '#%s', $this->id() );
	}
	
	/**
	 * Get nest level
	 *
	 * @return int 1-6.
	 */
	public function level( $max_depth = 3 ) {
		$node_name = strtolower( $this->dom->nodeName );
		if ( preg_match( '/h([1-6])/u', $node_name, $match ) ) {
			$depth = min( $max_depth, $match[1] );
		} else {
			$depth = $max_depth;
		}
		return (int) $depth;
	}
	
	/**
	 * Get markup of contents.
	 *
	 * @return string
	 */
	public function get_markup() {
		return sprintf( '<a href="%s">%s</a>', htmlspecialchars( $this->href(), ENT_QUOTES ), htmlspecialchars( $this->text(), ENT_QUOTES ) );
	}
}
