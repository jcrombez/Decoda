<?php
/**
 * @author      Miles Johnson - http://milesj.me
 * @copyright   Copyright 2006-2012, Miles Johnson, Inc.
 * @license     http://opensource.org/licenses/mit-license.php - Licensed under The MIT License
 * @link        http://milesj.me/code/php/decoda
 */

namespace mjohnson\decoda\tests;

use mjohnson\decoda\filters\DefaultFilter;
use mjohnson\decoda\hooks\CensorHook;
use \Exception;

class DecodaTest extends TestCase {

	/**
	 * Set up Decoda.
	 */
	protected function setUp() {
		parent::setUp();

		$this->object->addFilter(new TestFilter());
	}

	/**
	 * Test that adding, getting and resetting filters work.
	 */
	public function testFilters() {
		// Empty, Test
		$this->assertTrue(count($this->object->getFilters()) == 2);

		try {
			$this->object->getFilter('Default');
			$this->assertTrue(false);
		} catch (Exception $e) {
			$this->assertTrue(true);
		}

		try {
			$this->object->getFilterByTag('b');
			$this->assertTrue(false);
		} catch (Exception $e) {
			$this->assertTrue(true);
		}

		$this->object->addFilter(new DefaultFilter());

		// Empty, Test, Default
		$this->assertTrue(count($this->object->getFilters()) == 3);

		$this->assertInstanceOf('\mjohnson\decoda\filters\Filter', $this->object->getFilter('Default'));
		$this->assertInstanceOf('\mjohnson\decoda\filters\Filter', $this->object->getFilterByTag('b'));

		$this->object->resetFilters();

		// Empty
		$this->assertTrue(count($this->object->getFilters()) == 1);
	}

	/**
	 * Test that adding, getting and resetting hooks work.
	 */
	public function testHooks() {
		// Empty
		$this->assertTrue(count($this->object->getHooks()) == 1);

		try {
			$this->object->getHook('Censor');
			$this->assertTrue(false);
		} catch (Exception $e) {
			$this->assertTrue(true);
		}

		$this->object->addHook(new CensorHook());

		// Empty, Censor
		$this->assertTrue(count($this->object->getHooks()) == 2);

		$this->assertInstanceOf('\mjohnson\decoda\hooks\Hook', $this->object->getHook('Censor'));

		$this->object->resetFilters();

		// Empty
		$this->assertTrue(count($this->object->getHooks()) == 1);
	}

	public function testEngines() {

	}

	public function testMessage() {

	}

	public function testBrackets() {

	}

	public function testEscaping() {

	}

	public function testLocale() {

	}

	public function testShorthand() {

	}

	public function testStrict() {

	}

	public function testXhtml() {

	}

	public function testWhitelist() {

	}

	/**
	 * Test that nesting of inline and block elements.
	 */
	public function testDisplayAndAllowedTypes() {
		// Inline with inline children
		$string = '[inlineAllowInline][inline]Inline[/inline][block]Block[/block][/inlineAllowInline]';
		$this->assertEquals('<inlineAllowInline><inline>Inline</inline>Block</inlineAllowInline>', $this->object->reset($string)->parse());

		// Inline with block children (block are never allowed)
		$string = '[inlineAllowBlock][inline]Inline[/inline][block]Block[/block][/inlineAllowBlock]';
		$this->assertEquals('<inlineAllowBlock>InlineBlock</inlineAllowBlock>', $this->object->reset($string)->parse());

		// Inline with both children (block are never allowed)
		$string = '[inlineAllowBoth][inline]Inline[/inline][block]Block[/block][/inlineAllowBoth]';
		$this->assertEquals('<inlineAllowBoth><inline>Inline</inline>Block</inlineAllowBoth>', $this->object->reset($string)->parse());

		// Block with inline children
		$string = '[blockAllowInline][inline]Inline[/inline][block]Block[/block][/blockAllowInline]';
		$this->assertEquals('<blockAllowInline><inline>Inline</inline>Block</blockAllowInline>', $this->object->reset($string)->parse());

		// Block with block children (inline are allowed always)
		$string = '[blockAllowBlock][inline]Inline[/inline][block]Block[/block][/blockAllowBlock]';
		$this->assertEquals('<blockAllowBlock>Inline<block>Block</block></blockAllowBlock>', $this->object->reset($string)->parse());

		// Block with both children
		$string = '[blockAllowBoth][inline]Inline[/inline][block]Block[/block][/blockAllowBoth]';
		$this->assertEquals('<blockAllowBoth><inline>Inline</inline><block>Block</block></blockAllowBoth>', $this->object->reset($string)->parse());
	}

	/**
	 * Test attribute parsing, mapping and escaping.
	 */
	public function testAttributeParsing() {
		// No attributes, has custom HTML attributes
		$string = '[attributes]Attributes[/attributes]';
		$this->assertEquals('<attributes id="custom-html">Attributes</attributes>', $this->object->reset($string)->parse());

		// Default attribute, uses wildcard pattern, is mapped and renamed to wildcard
		$string = '[attributes="1337"]Attributes[/attributes]';
		$this->assertEquals('<attributes id="custom-html" wildcard="1337">Attributes</attributes>', $this->object->reset($string)->parse());

		$string = '[attributes="Decoda"]Attributes[/attributes]';
		$this->assertEquals('<attributes id="custom-html" wildcard="Decoda">Attributes</attributes>', $this->object->reset($string)->parse());

		$string = '[attributes="02/26/1988!"]Attributes[/attributes]';
		$this->assertEquals('<attributes id="custom-html" wildcard="02/26/1988!">Attributes</attributes>', $this->object->reset($string)->parse());

		// Alpha attribute, uses alpha pattern
		$string = '[attributes alpha="1337"]Attributes[/attributes]';
		$this->assertEquals('<attributes id="custom-html">Attributes</attributes>', $this->object->reset($string)->parse());

		$string = '[attributes alpha="Decoda Parser"]Attributes[/attributes]';
		$this->assertEquals('<attributes id="custom-html" alpha="Decoda Parser">Attributes</attributes>', $this->object->reset($string)->parse());

		$string = '[attributes alpha="Spaces Dashes- Underscores_"]Attributes[/attributes]';
		$this->assertEquals('<attributes id="custom-html" alpha="Spaces Dashes- Underscores_">Attributes</attributes>', $this->object->reset($string)->parse());

		$string = '[attributes alpha="Other! Not* Allowed&"]Attributes[/attributes]';
		$this->assertEquals('<attributes id="custom-html">Attributes</attributes>', $this->object->reset($string)->parse());

		// Alnum attribute, uses alpha and numeric pattern
		$string = '[attributes alnum="1337"]Attributes[/attributes]';
		$this->assertEquals('<attributes id="custom-html" alnum="1337">Attributes</attributes>', $this->object->reset($string)->parse());

		$string = '[attributes alnum="Decoda Parser"]Attributes[/attributes]';
		$this->assertEquals('<attributes id="custom-html" alnum="Decoda Parser">Attributes</attributes>', $this->object->reset($string)->parse());

		$string = '[attributes alnum="Spaces Dashes- Underscores_"]Attributes[/attributes]';
		$this->assertEquals('<attributes id="custom-html" alnum="Spaces Dashes- Underscores_">Attributes</attributes>', $this->object->reset($string)->parse());

		// Numeric attribute, uses numeric pattern
		$string = '[attributes numeric="1337"]Attributes[/attributes]';
		$this->assertEquals('<attributes id="custom-html" numeric="1337">Attributes</attributes>', $this->object->reset($string)->parse());

		$string = '[attributes numeric="+1,337"]Attributes[/attributes]';
		$this->assertEquals('<attributes id="custom-html" numeric="+1,337">Attributes</attributes>', $this->object->reset($string)->parse());

		$string = '[attributes numeric="1,337.00"]Attributes[/attributes]';
		$this->assertEquals('<attributes id="custom-html" numeric="1,337.00">Attributes</attributes>', $this->object->reset($string)->parse());

		$string = '[attributes numeric="Decoda"]Attributes[/attributes]';
		$this->assertEquals('<attributes id="custom-html">Attributes</attributes>', $this->object->reset($string)->parse());

		// All attributes and escaping
		$string = '[attributes="Decoda & Escaping" alpha="Decoda" alnum="Version 1.2.3" numeric="1337"]Attributes[/attributes]';
		$this->assertEquals('<attributes id="custom-html" wildcard="Decoda &amp; Escaping" alpha="Decoda" alnum="Version 1.2.3" numeric="1337">Attributes</attributes>', $this->object->reset($string)->parse());
	}

	/**
	 * Test parent and child nesting hierarchy.
	 */
	public function testParentChildNesting() {
		// Whitelist will only allow white children
		$string = '[parentWhitelist][whiteChild]White[/whiteChild][blackChild]Black[/blackChild][/parentWhitelist]';
		$this->assertEquals('<parentWhitelist><whiteChild>White</whiteChild></parentWhitelist>', $this->object->reset($string)->parse());

		// Blacklist will not allow white children
		$string = '[parentBlacklist][whiteChild]White[/whiteChild][blackChild]Black[/blackChild][/parentBlacklist]';
		$this->assertEquals('<parentBlacklist><blackChild>Black</blackChild></parentBlacklist>', $this->object->reset($string)->parse());

		// No whitelist or blacklist
		$string = '[parent][whiteChild]White[/whiteChild][blackChild]Black[/blackChild][/parent]';
		$this->assertEquals('<parent><whiteChild>White</whiteChild><blackChild>Black</blackChild></parent>', $this->object->reset($string)->parse());

		// Children can only be nested in a parent
		$string = '[example][whiteChild]White[/whiteChild][blackChild]Black[/blackChild][/example]';
		$this->assertEquals('<example>WhiteBlack</example>', $this->object->reset($string)->parse());

		$string = '[whiteChild]White[/whiteChild][blackChild]Black[/blackChild]';
		$this->assertEquals('WhiteBlack', $this->object->reset($string)->parse());

		// Children can only be nested in a parent -- but do not persist the content
		$string = '[parentNoPersist][whiteChild]White[/whiteChild][blackChild]Black[/blackChild][/parentNoPersist]';
		$this->assertEquals('<parentNoPersist></parentNoPersist>', $this->object->reset($string)->parse());
	}

	/**
	 * Test max nesting depth.
	 */
	public function testMaxNestingDepth() {
		// No nested
		$string = '[depth]1[/depth]';
		$this->assertEquals('<depth>1</depth>', $this->object->reset($string)->parse());

		// 1 nested
		$string = '[depth]1 [depth]2[/depth][/depth]';
		$this->assertEquals('<depth>1 <depth>2</depth></depth>', $this->object->reset($string)->parse());

		// 2 nested
		$string = '[depth]1 [depth]2 [depth]3[/depth][/depth][/depth]';
		$this->assertEquals('<depth>1 <depth>2 <depth>3</depth></depth></depth>', $this->object->reset($string)->parse());

		// 3 nested - over the max so remove
		$string = '[depth]1 [depth]2 [depth]3 [depth]4[/depth][/depth][/depth][/depth]';
		$this->assertEquals('<depth>1 <depth>2 <depth>3 </depth></depth></depth>', $this->object->reset($string)->parse());
	}

	/**
	 * Test CRLF formatting.
	 */
	public function testNewlineFormatting() {
		// Remove CRLF
		$string = "[lineBreaksRemove]Line\nBreak\rTests[/lineBreaksRemove]";
		$this->assertEquals("<lineBreaksRemove>LineBreakTests</lineBreaksRemove>", $this->object->reset($string)->parse());

		// Preserve CRLF
		$string = "[lineBreaksPreserve]Line\nBreak\rTests[/lineBreaksPreserve]";
		$this->assertEquals("<lineBreaksPreserve>Line\nBreak\rTests</lineBreaksPreserve>", $this->object->reset($string)->parse());

		// Convert CRLF to <br>
		$string = "[lineBreaksConvert]Line\nBreak\rTests[/lineBreaksConvert]";
		$this->assertEquals("<lineBreaksConvert>Line<br>Break<br>Tests</lineBreaksConvert>", $this->object->reset($string)->parse());

		// Test nested
		$string = "[lineBreaksRemove]Line\nBreak\rTests[lineBreaksConvert]Line\nBreak\rTests[/lineBreaksConvert][/lineBreaksRemove]";
		$this->assertEquals("<lineBreaksRemove>LineBreakTests<lineBreaksConvert>Line<br>Break<br>Tests</lineBreaksConvert></lineBreaksRemove>", $this->object->reset($string)->parse());
	}

	/**
	 * Test that the content of the tag passes a regex pattern.
	 */
	public function testContentPatternMatching() {
		// Shouldn't pass
		$string = '[pattern]userpass[/pattern]';
		$this->assertEquals('(Invalid pattern)', $this->object->reset($string)->parse());

		// Should pass
		$string = '[pattern]user@pass[/pattern]';
		$this->assertEquals('<pattern>user@pass</pattern>', $this->object->reset($string)->parse());

		// Should pass with attributes
		$string = '[pattern="test"]user@pass[/pattern]';
		$this->assertEquals('<pattern attr="test">user@pass</pattern>', $this->object->reset($string)->parse());
	}

	/**
	 * Test that the content of the tag passes a regex pattern.
	 */
	public function testAutoClosingTags() {
		$this->object->setXhtml(true);

		// No content or attributes
		$string = '[autoClose]Content[/autoClose]';
		$this->assertEquals('<autoClose />', $this->object->reset($string)->parse());

		// No content with attributes
		$string = '[autoClose foo="1" bar="2"]Content[/autoClose]';
		$this->assertEquals('<autoClose foo="1" bar="2" />', $this->object->reset($string)->parse());
	}

}