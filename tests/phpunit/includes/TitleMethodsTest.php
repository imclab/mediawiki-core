<?php

class TitleMethodsTest extends MediaWikiTestCase {

	public function dataEquals() {
		return array(
			array( 'Main Page', 'Main Page', true ),
			array( 'Main Page', 'Not The Main Page', false ),
			array( 'Main Page', 'Project:Main Page', false ),
			array( 'File:Example.png', 'Image:Example.png', true ),
			array( 'Special:Version', 'Special:Version', true ),
			array( 'Special:Version', 'Special:Recentchanges', false ),
			array( 'Special:Version', 'Main Page', false ),
		);
	}

	/**
	 * @dataProvider dataEquals
	 */
	public function testEquals( $titleA, $titleB, $expectedBool ) {
		$titleA = Title::newFromText( $titleA );
		$titleB = Title::newFromText( $titleB );

		$this->assertEquals( $expectedBool, $titleA->equals( $titleB ) );
		$this->assertEquals( $expectedBool, $titleB->equals( $titleA ) );
	}

	public function dataInNamespace() {
		return array(
			array( 'Main Page', NS_MAIN, true ),
			array( 'Main Page', NS_TALK, false ),
			array( 'Main Page', NS_USER, false ),
			array( 'User:Foo', NS_USER, true ),
			array( 'User:Foo', NS_USER_TALK, false ),
			array( 'User:Foo', NS_TEMPLATE, false ),
			array( 'User_talk:Foo', NS_USER_TALK, true ),
			array( 'User_talk:Foo', NS_USER, false ),
		);
	}

	/**
	 * @dataProvider dataInNamespace
	 */
	public function testInNamespace( $title, $ns, $expectedBool ) {
		$title = Title::newFromText( $title );
		$this->assertEquals( $expectedBool, $title->inNamespace( $ns ) );
	}

	public function testInNamespaces() {
		$mainpage = Title::newFromText( 'Main Page' );
		$this->assertTrue( $mainpage->inNamespaces( NS_MAIN, NS_USER ) );
		$this->assertTrue( $mainpage->inNamespaces( array( NS_MAIN, NS_USER ) ) );
		$this->assertTrue( $mainpage->inNamespaces( array( NS_USER, NS_MAIN ) ) );
		$this->assertFalse( $mainpage->inNamespaces( array( NS_PROJECT, NS_TEMPLATE ) ) );
	}

	public function dataHasSubjectNamespace() {
		return array(
			array( 'Main Page', NS_MAIN, true ),
			array( 'Main Page', NS_TALK, true ),
			array( 'Main Page', NS_USER, false ),
			array( 'User:Foo', NS_USER, true ),
			array( 'User:Foo', NS_USER_TALK, true ),
			array( 'User:Foo', NS_TEMPLATE, false ),
			array( 'User_talk:Foo', NS_USER_TALK, true ),
			array( 'User_talk:Foo', NS_USER, true ),
		);
	}

	/**
	 * @dataProvider dataHasSubjectNamespace
	 */
	public function testHasSubjectNamespace( $title, $ns, $expectedBool ) {
		$title = Title::newFromText( $title );
		$this->assertEquals( $expectedBool, $title->hasSubjectNamespace( $ns ) );
	}


    public function dataGetContentModelName() {
        return array(
            array( 'Foo', CONTENT_MODEL_WIKITEXT ),
            array( 'Foo.js', CONTENT_MODEL_WIKITEXT ),
            array( 'Foo/bar.js', CONTENT_MODEL_WIKITEXT ),
            array( 'User:Foo', CONTENT_MODEL_WIKITEXT ),
            array( 'User:Foo.js', CONTENT_MODEL_WIKITEXT ),
            array( 'User:Foo/bar.js', CONTENT_MODEL_JAVASCRIPT ),
            array( 'User:Foo/bar.css', CONTENT_MODEL_CSS ),
            array( 'User talk:Foo/bar.css', CONTENT_MODEL_WIKITEXT ),
            array( 'User:Foo/bar.js.xxx', CONTENT_MODEL_WIKITEXT ),
            array( 'User:Foo/bar.xxx', CONTENT_MODEL_WIKITEXT ),
            array( 'MediaWiki:Foo.js', CONTENT_MODEL_JAVASCRIPT ),
            array( 'MediaWiki:Foo.css', CONTENT_MODEL_CSS ),
            array( 'MediaWiki:Foo.JS', CONTENT_MODEL_WIKITEXT ),
            array( 'MediaWiki:Foo.CSS', CONTENT_MODEL_WIKITEXT ),
            array( 'MediaWiki:Foo.css.xxx', CONTENT_MODEL_WIKITEXT ),
        );
    }

    /**
     * @dataProvider dataGetContentModelName
     */
    public function testGetContentModelName( $title, $expectedModelName ) {
        $title = Title::newFromText( $title );
        $this->assertEquals( $expectedModelName, $title->getContentModelName() );
    }

    /**
     * @dataProvider dataGetContentModelName
     */
    public function testHasContentModel( $title, $expectedModelName ) {
        $title = Title::newFromText( $title );
        $this->assertTrue( $title->hasContentModel( $expectedModelName ) );
    }

    public function dataIsCssOrJsPage() {
        return array(
            array( 'Foo', false ),
            array( 'Foo.js', false ),
            array( 'Foo/bar.js', false ),
            array( 'User:Foo', false ),
            array( 'User:Foo.js', false ),
            array( 'User:Foo/bar.js', false ),
            array( 'User:Foo/bar.css', false ),
            array( 'User talk:Foo/bar.css', false ),
            array( 'User:Foo/bar.js.xxx', false ),
            array( 'User:Foo/bar.xxx', false ),
            array( 'MediaWiki:Foo.js', true ),
            array( 'MediaWiki:Foo.css', true ),
            array( 'MediaWiki:Foo.JS', false ),
            array( 'MediaWiki:Foo.CSS', false ),
            array( 'MediaWiki:Foo.css.xxx', false ),
        );
    }

    /**
     * @dataProvider dataIsCssOrJsPage
     */
    public function testIsCssOrJsPage( $title, $expectedBool ) {
        $title = Title::newFromText( $title );
        $this->assertEquals( $expectedBool, $title->isCssOrJsPage() );
    }


    public function dataIsCssJsSubpage() {
        return array(
            array( 'Foo', false ),
            array( 'Foo.js', false ),
            array( 'Foo/bar.js', false ),
            array( 'User:Foo', false ),
            array( 'User:Foo.js', false ),
            array( 'User:Foo/bar.js', true ),
            array( 'User:Foo/bar.css', true ),
            array( 'User talk:Foo/bar.css', false ),
            array( 'User:Foo/bar.js.xxx', false ),
            array( 'User:Foo/bar.xxx', false ),
            array( 'MediaWiki:Foo.js', false ),
            array( 'User:Foo/bar.JS', false ),
            array( 'User:Foo/bar.CSS', false ),
        );
    }

    /**
     * @dataProvider dataIsCssJsSubpage
     */
    public function testIsCssJsSubpage( $title, $expectedBool ) {
        $title = Title::newFromText( $title );
        $this->assertEquals( $expectedBool, $title->isCssJsSubpage() );
    }

}
