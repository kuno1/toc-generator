<?php
/**
 * Test TOC generator
 */

use Kunoichi\TocGenerator\Parser;
use Masterminds\HTML5;

/**
 * Test TOC generator
 */
class TocTest extends WP_UnitTestCase {
	
	/**
	 * Test if id is properly set.
	 */
	public function test_id() {
		$parser = new Parser();
		$html = <<<HTML
<h1>Title</h1>
<p>TEST</p>
<h2>Heading 2</h2>
HTML;
		$expected = <<<HTML
<h1 id="content-section-1">Title</h1>
<p>TEST</p>
<h2 id="content-section-2">Heading 2</h2>
HTML;

		$this->assertEquals( $expected, $parser->add_link_html( $html ) );
		
		$links = $parser->parse_html( $expected );
		$this->assertEquals( 2, count( $links ) );
		
		
	}
	
	public function test_toc_generation() {
		$parser = new Parser( 4 );
		$html = <<<HTML
<h4>Heading4</h4>
<h2>Title1</h2>
<h3>Heading3</h3>
<h4>Heading4</h4>
<h4>Heading4</h4>
<h3>Heading3</h3>
<h4>Heading4</h4>
<h5>Heading5</h5>
<h2>Title2</h2>
<h3>Heading3</h3>
<h4>Heading4</h4>
<h2>Title3</h2>
<h3>Heading3</h3>
<h4>Heading4</h4>
<h2>Title4</h2>
<h3>Heading3</h3>
<h4>Heading4</h4>
HTML;

		$toc = $parser->get_toc( $parser->parse_html( $parser->add_link_html( $html ) ) );
		
		file_put_contents( __DIR__ . '/index.html', sprintf( "<!DOCTYPE html>\n<html>%s</html>" , $toc) );
	}
	
	
}
