<?php

use Behat\Mink\Driver\CoreDriver;
use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Selector\SelectorsHandler;
use Behat\Mink\Session;
use Example\Articles;
use Example\Demo;
use Example\Footer;
use Example\Navigation;
use Goez\PageObjects\Factory;

class PageTest extends PHPUnit_Framework_TestCase
{
    private $driver;

    private $session;

    private $factory;

    public function setUp()
    {
        $html = file_get_contents(__DIR__ . '/Example/demo.html');
        $text = strip_tags($html);
        $this->driver = Mockery::mock(CoreDriver::class);
        $this->driver->shouldReceive('find')
            ->withAnyArgs()
            ->andReturnNull();
        $this->driver->shouldReceive('getText')
            ->withAnyArgs()
            ->andReturn($text);
        $this->driver->shouldReceive('getHtml')
            ->withAnyArgs()
            ->andReturn($html);
        $this->session = Mockery::mock(Session::class);
        $this->session->shouldReceive('getDriver')
            ->withNoArgs()
            ->andReturn($this->driver);
        $this->session->shouldReceive('getSelectorsHandler')
            ->withNoArgs()
            ->andReturn(new SelectorsHandler());
        $this->factory = new Factory('Example', 'http://localhost');
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function testOpenWithoutCallback()
    {
        $page = new Demo('http://localhost', $this->session);
        $this->session->shouldReceive('visit')
            ->once()
            ->andReturnNull();

        $page->open();
        $uri = $page->getUri();

        $this->assertEquals('http://localhost/', $uri);
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testMethodNotFound()
    {
        $page = new Demo('http://localhost', $this->session);
        $page->notExistMethod();
    }

    public function testOpenWithCallback()
    {
        $spy = Mockery::mock(function () {
        });
        $spy->shouldReceive('call')->withAnyArgs()->once();
        $page = new Demo('http://localhost', $this->session);
        $this->session->shouldReceive('visit')
            ->once()
            ->andReturnNull();

        $page->open(function () use ($spy) {
            $spy->call($this);
        });
        $uri = $page->getUri();

        $this->assertEquals('http://localhost/', $uri);
    }

    public function testGetPartialElementWithoutSelectorFromPage()
    {
        $page = new Demo('http://localhost', $this->session);
        $page->setFactory($this->factory);

        $part = $page->getPart(Navigation::class);

        $this->assertInstanceOf(Navigation::class, $part);
    }

    /**
     * @param $name
     * @param $expectedClass
     * @dataProvider selectorProvider
     */
    public function testGetPartialElementWithSelectorFromPage($name, $expectedClass)
    {
        $page = new Demo('http://localhost', $this->session);
        $page->setFactory($this->factory);

        $part = $page->getPart($name);

        $this->assertInstanceOf($expectedClass, $part);
    }

    /**
     * @expectedException Goez\PageObjects\Exception\ElementNotFoundException
     */
    public function testGetPartialElementButElementNotFound()
    {
        $page = new Demo('http://localhost', $this->session);
        $page->setFactory($this->factory);

        $page->getPart('ElementNotFound');
    }

    public function selectorProvider()
    {
        return [
            [Articles::class, Articles::class],
            [Footer::class, Footer::class],
        ];
    }

    public function testGetElementInPageObjectShouldBeADocumentElement()
    {
        $page = new Demo('http://localhost', $this->session);

        $documentElement = $page->getElement();

        $this->assertInstanceOf(DocumentElement::class, $documentElement);
    }

    public function testPageShouldContainText()
    {
        $page = new Demo('http://localhost', $this->session);

        $page->shouldContainText('Home');
    }

    public function testPageShouldContainHtml()
    {
        $page = new Demo('http://localhost', $this->session);

        $page->shouldContainHtml('<a href="#">Home</a>');
    }

    public function testPageShouldNotContainText()
    {
        $page = new Demo('http://localhost', $this->session);

        $page->shouldNotContainText('Who are you?');
    }

    public function testPageShouldNotContainHtml()
    {
        $page = new Demo('http://localhost', $this->session);

        $page->shouldNotContainHtml('<a href="#">Who are you?</a>');
    }

    public function testSeeTitleInPage()
    {
        $page = new Demo('http://localhost', $this->session);

        $page->seeTitle('Example');
    }

    public function testSeeHeadingInPage()
    {
        $page = new Demo('http://localhost', $this->session);

        $page->seeHeading('This is level 1 heading');
    }

    public function testSeeSubHeadingInPage()
    {
        $page = new Demo('http://localhost', $this->session);

        $page->seeSubHeading('This is level 2 heading');
    }
}
