<?php

namespace Kunoichi\TocGenerator;


use Masterminds\HTML5;

/**
 * Class Parser
 *
 * @package Kunoichi\TocGenerator
 */
class Parser {
	
	/**
	 * @var Item[]
	 */
	protected $parsed = [];
	
	protected $counter = 0;
	
	protected $id_prefix = '';
	
	protected $max_depth = 3;
	
	protected $ignore_deeper = false;
	
	protected $title = '';
	
	/**
	 * Constructor
	 *
	 * @param int    $max_depth
	 * @param bool   $ignore_deeper
	 * @param string $id
	 */
	public function __construct( $max_depth = 3, $ignore_deeper = false, $id = 'content-section-' ) {
		$this->id_prefix     = $id;
		$this->max_depth     = $max_depth;
		$this->ignore_deeper = $ignore_deeper;
	}
	
	/**
	 * Add link id to html elements.
	 *
	 * @param string $html
	 *
	 * @return string
	 */
	public function add_link_html( $html ) {
		return preg_replace_callback( '/<(h[1-6])([^>]*?)>/u', [ $this, 'convert_link' ], $html );
	}
	
	/**
	 * Add link to title elements.
	 *
	 * @param string[] $matches
	 * @return string
	 */
	protected function convert_link( $matches ) {
		$this->counter++;
		$attributes = $matches[2];
		if ( preg_match( '/id=\'|"([\'"]*)(\'|")/u', $matches[2], $id_matches ) ) {
			$id = $id_matches[1];
		} else {
			$id = sprintf( '%s%d', $this->id_prefix, $this->counter );
			$attributes .= sprintf( ' id="%s"', $id ) . $matches[2];
		}
		return sprintf( '<%s%s>', $matches[1], $attributes );
	}
	
	/**
	 * Convert HTML to array of links.
	 *
	 * @param string $html
	 * @return Item[]
	 */
	public function parse_html( $html ) {
		$html  = sprintf( '<html>%s</html>', $html );
		$html5 = new HTML5();
		$dom   = $html5->loadHTML( $html );
		$xpath = new \DOMXPath( $dom );
		$items = [];
		$dom_nodes = $xpath->query( '//*' );
		if ( ! $dom_nodes ) {
			return $items;
		}
		foreach( $dom_nodes as $hn ) {
			/** @var \DOMNode $dom */
			if ( ! preg_match( '/h([1-6])/u', strtolower( $hn->nodeName ), $matches ) ) {
				continue;
			}
			list( $match, $level ) = $matches;
			if ( $this->ignore_deeper && ( (int) $level > $this->ignore_deeper ) ) {
				continue;
			}
			$items[] = new Item( $hn );
		}
		return $items;
	}
	
	/**
	 * Save html
	 *
	 * @param string $html
	 * @return void
	 */
	public function save_parsed_html( $html ) {
		$this->parsed = $this->parse_html( $html );
	}
	
	/**
	 * Get toc HTML
	 *
	 * @param Item[] $items
	 * @param string $class_name
	 * @return string
	 */
	public function get_toc( $items = [], $class_name = 'toc' ) {
		if ( ! $items ) {
			$items = $this->parsed;
		}
		if ( ! $items ) {
			return '';
		}
		$class_name = htmlspecialchars( $class_name, ENT_QUOTES );
		if ( $this->title ) {
			$title = sprintf( '<h2 class="toc-title">%s</h2>', htmlspecialchars( $this->title, ENT_QUOTES ) );
		} else {
			$title = '';
		}
		$out  = sprintf( '<nav class="%1$s">%2$s<ol class="%1$s-root">', $class_name, $title );
		$bench_mark = $this->benchmark( $items );
		$prev       = $bench_mark;
		$counter = 0;
		foreach ( $items as $item ) {
			$level = $item->level( $this->max_depth );
			$diff  = $prev - $level;
			if ( 0 < $diff  ) {
				// Smaller.
				for ( $i = 0; $i < $diff; $i++ ) {
					$out .= '</li></ol>';
				}
				if ( $counter ) {
					$out .= '</li>';
				}
			} elseif ( 0 > $diff) {
				// Larger.
				for ( $i = 0; $i > $diff; $i-- ) {
					if ( $i < 0 || ! $counter ) {
						$out .= sprintf( '<li data-level="%d">', $bench_mark - $prev + $i );
					}
					$out .= sprintf( sprintf( '<ol class="%s-child">', $class_name ) );
				}
			} else {
				// Same level.
				if ( $counter ) {
					$out .= '</li>';
				}
			}
			$out .= sprintf( '<li data-level="%d">', $bench_mark - $level );
			$out .= $item->get_markup();
			$prev = $level;
			$counter++;
		}
		$last_diff = $bench_mark - $prev;
		if ( 0 > $last_diff ) {
			for ( $i = 0; $i > $last_diff; $i-- ) {
				$out .= '</li></ol>';
			}
		}
		$out .= '</li></ol></nav>';
		return $out;
	}
	
	/**
	 * Get benchmark
	 *
	 * @param Item[] $items
	 * @return int
	 */
	public function benchmark( $items = [] ) {
		$benchmark = $this->max_depth;
		foreach ( $items as $item ) {
			$lv        = $item->level( $this->max_depth );
			$benchmark = min( $benchmark, $lv );
		}
		return $benchmark;
	}
	
	/**
	 * Set TOC title.
	 *
	 * @param string $title
	 */
	public function set_title( $title ) {
		$this->title = $title;
	}
}
