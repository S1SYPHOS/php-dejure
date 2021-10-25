<?php

/**
 * Testing DejureOnline - Linking texts with dejure.org, the Class(y) way
 *
 * @link https://github.com/S1SYPHOS/php-dejure
 * @license MIT
 */

namespace S1SYPHOS\Tests;


/**
 * Class DejureOnlineTest
 *
 * @package php-dejure
 */
class DejureOnlineTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Properties
     */

    /**
     * @var string
     */
    private static $text;


    /**
     * Setup
     */

    public static function setUpBeforeClass(): void
    {
        # Setup
        # (1) Text
        # Enforce UTF-8 encoding
        $text = '<!DOCTYPE html><meta charset="UTF-8">';

        # Insert test string
        $text .= '<div>';
        $text .= 'This is a <strong>simple</strong> HTML text.';
        $text .= 'It contains legal norms, like Art. 12 GG.';
        $text .= '.. or § 433 BGB!';
        $text .= '</div>';

        self::$text = $text;
    }


    /**
     * Tests
     */

    public function testInit(): void
    {
        # Setup
        # TODO: Install PHP extensions
        # (1) Cache drivers
        $cacheDrivers = [
            'file',
            # 'redis',
            # 'mongo',
            # 'mysql',
            'sqlite',
            # 'apc',
            # 'apcu',
            # 'memcache',
            # 'memcached',
            # 'wincache',
        ];

        foreach ($cacheDrivers as $cacheDriver) {
            # Run function
            $result = new \S1SYPHOS\DejureOnline($cacheDriver);

            # Assert result
            # TODO: Migrate to `assertInstanceOf`
            $this->assertInstanceOf('\S1SYPHOS\DejureOnline', $result);
        }
    }


    public function testInitInvalidCacheDriver(): void
    {
        # Setup
        # (1) Cache drivers
        $cacheDrivers = [
            '',
            '?!#@=',
            'mariadb',
            'sqlite3',
            'windows',
        ];

        # Assert exception
        $this->expectException(\Exception::class);

        foreach ($cacheDrivers as $cacheDriver) {
            # Run function
            $result = new \S1SYPHOS\DejureOnline($cacheDriver);
        }
    }


    public function testDejurify(): void
    {
        # Setup
        # (1) Instance
        $object = new \S1SYPHOS\DejureOnline();

        # (2) HTML document
        $dom = new \DOMDocument;

        # Run function
        @$dom->loadHTML(self::$text);
        $result = $dom->getElementsByTagName('a');

        # Assert result
        $this->assertEquals(count($result), 0);

        # Run function
        @$dom->loadHTML($object->dejurify(self::$text));
        $result = $dom->getElementsByTagName('a');

        # Assert result
        $this->assertEquals(count($result), 2);
    }


    public function testSetClass(): void
    {
        # Setup
        # (1) Instance
        $object = new \S1SYPHOS\DejureOnline();

        # (2) HTML document
        $dom = new \DOMDocument;

        # (3) Classes
        $classes = [
            '',
            'class',
            'another-class',
            'yet/another/class'
        ];

        # Run function
        foreach ($classes as $class) {
            $object->setClass($class);
            @$dom->loadHTML($object->dejurify(self::$text));

            # Assert result
            foreach ($dom->getElementsByTagName('a') as $node) {
                $this->assertEquals($class, $node->getAttribute('class'));
            }

            $this->assertEquals($class, $object->getClass());
        }
    }


    public function testSetLinkStyle(): void
    {
        # Setup
        # (1) Instance
        $object = new \S1SYPHOS\DejureOnline();

        # (2) HTML document
        $dom = new \DOMDocument;

        # (3) Link styles
        $linkStyles = [
            'schmal' => ['12', '433'],
            'weit' => ['Art. 12 GG', '§ 433 BGB'],
        ];

        # Run function
        foreach ($linkStyles as $linkStyle => $results) {
            $object->setLinkStyle($linkStyle);
            @$dom->loadHTML($object->dejurify(self::$text));

            # Assert result
            foreach ($dom->getElementsByTagName('a') as $index => $node) {
                $this->assertEquals($node->textContent, $results[$index]);
            }

            $this->assertEquals($linkStyle, $object->getLinkStyle());
        }
    }


    public function testInvalidLinkStyle(): void
    {
        # Setup
        # (1) Instance
        $object = new \S1SYPHOS\DejureOnline();

        # (2) Link styles
        $linkStyles = [
            'not-weit',
            'not-schmal',
        ];

        # Assert exception
        $this->expectException(\Exception::class);

        foreach ($linkStyles as $linkStyle) {
            # Run function
            $object->setLinkStyle($linkStyle);
            $object->dejurify(self::$text);
        }
    }


    public function testSetTooltip(): void
    {
        # Setup
        # (1) Instance
        $object = new \S1SYPHOS\DejureOnline();

        # (2) HTML document
        $dom = new \DOMDocument;

        # (3) Tooltips
        $tooltips = [
            'ohne' => ['', ''],
            'neutral' => ['Gesetzestext über dejure.org', 'Gesetzestext über dejure.org'],
            'beschreibend' => ['Art. 12 GG', '§ 433 BGB: Vertragstypische Pflichten beim Kaufvertrag'],
            'Gesetze' => ['Art. 12 GG', '§ 433 BGB: Vertragstypische Pflichten beim Kaufvertrag'],
            'halb' => ['Art. 12 GG', '§ 433 BGB: Vertragstypische Pflichten beim Kaufvertrag'],
        ];

        # Run function
        foreach ($tooltips as $tooltip => $results) {
            $object->setTooltip($tooltip);
            @$dom->loadHTML($object->dejurify(self::$text));

            # Assert result
            foreach ($dom->getElementsByTagName('a') as $index => $node) {
                $this->assertEquals($node->getAttribute('title'), $results[$index]);
            }

            $this->assertEquals($tooltip, $object->getTooltip());
        }
    }


    public function testInvalidTooltip(): void
    {
        # Setup
        # (1) Instance
        $object = new \S1SYPHOS\DejureOnline();

        # (2) Tooltips
        $tooltips = [
            'not-ohne',
            'not-neutral',
            'not-beschreibend',
            'not-Gesetze',
            'not-halb',
        ];

        # Assert exception
        $this->expectException(\Exception::class);

        foreach ($tooltips as $tooltip) {
            # Run function
            $object->setTooltip($tooltip);
            $object->dejurify(self::$text);
        }
    }


    public function testSetLineBreak(): void {}


    public function testInvalidLineBreak(): void
    {
        # Setup
        # (1) Instance
        $object = new \S1SYPHOS\DejureOnline();

        # (2) Line breaks
        $lineBreaks = [
            'not-ohne',
            'not-mit',
            'not-auto',
        ];

        # Assert exception
        $this->expectException(\Exception::class);

        foreach ($lineBreaks as $lineBreak) {
            # Run function
            $object->setLineBreak($lineBreak);
            $object->dejurify(self::$text);
        }
    }


    public function testSetTarget(): void
    {
        # Setup
        # (1) Instance
        $object = new \S1SYPHOS\DejureOnline();

        # (2) HTML document
        $dom = new \DOMDocument;

        # (3) Targets
        $targets = [
            '',
            '_blank',
            '_self',
            '_parent',
            '_top'
        ];

        # Run function
        foreach ($targets as $target) {
            $object->setTarget($target);
            @$dom->loadHTML($object->dejurify(self::$text));

            # Assert result
            foreach ($dom->getElementsByTagName('a') as $node) {
                $this->assertEquals($target, $node->getAttribute('target'));
            }

            $this->assertEquals($target, $object->getTarget());
        }
    }
}
