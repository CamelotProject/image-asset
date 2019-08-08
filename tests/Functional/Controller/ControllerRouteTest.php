<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Functional\Controller;

use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\ImageAsset;
use Camelot\ImageAsset\Response\Response;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use Camelot\ImageAsset\Tests\Fixtures\Routing\MountPathMockBuilder;
use Camelot\ImageAsset\Tests\Functional\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group  functional
 * @covers \Camelot\ImageAsset\Controller\ImageController
 * @covers \Camelot\ImageAsset\Controller\ImageAliasController
 */
final class ControllerRouteTest extends WebTestCase
{
    /** @var ?string */
    private static $mountPoint = null;
    /** @var ?string */
    private static $placeholderThumbPath = null;
    /** @var ?string */
    private static $placeholderRequestPath = null;

    public static function setUpBeforeClass(): void
    {
        self::$mountPoint = (string) new MountPathMockBuilder();
        self::$placeholderThumbPath = '800x600/{action}/placeholder.jpg';
        self::$placeholderRequestPath = self::$mountPoint . '/800x600/{action}/placeholder.jpg';
    }

    public function testIndexWithTwigFilterToGenerateThumbnail(): void
    {
        $client = self::createClient();
        $crawler = $client->request(Request::METHOD_GET, '');

        $this->assertIsSuccessful($client);

        static::assertStringContainsString('<img src="' . $this->getPlaceholderRequestPath(Action::FIT) . '">', $crawler->filter('body')->html());
    }

    public function providerActions(): iterable
    {
        yield Action::BORDER => [Action::BORDER];
        yield Action::CROP => [Action::CROP];
        yield Action::FIT => [Action::FIT];
        yield Action::RESIZE => [Action::RESIZE];

        //yield 'b' => ['b'];
        //yield 'c' => ['c'];
        //yield 'f' => ['f'];
        //yield 'r' => ['r'];
    }

    /**
     * @depends      testIndexWithTwigFilterToGenerateThumbnail
     * @dataProvider providerActions
     */
    public function testThumbnail(string $action): string
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, $this->getPlaceholderRequestPath($action));
        $this->assertIsSuccessful($client);

        return $action;
    }

    /**
     * @depends      testThumbnail
     * @dataProvider providerActions
     */
    public function testThumbnailRoute(string $action): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, $this->getPlaceholderRequestPath($action));

        $this->assertRouteSame(ImageAsset::THUMBNAIL_ROUTE, ['width' => 800, 'height' => 600, 'action' => $action, 'file' => 'placeholder.jpg']);
    }

    /**
     * @depends      testThumbnail
     * @dataProvider providerActions
     */
    public function testResponseInstance(string $action): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, $this->getPlaceholderRequestPath($action));

        static::assertInstanceOf(Response::class, $client->getResponse());
    }

    /**
     * @depends      testThumbnail
     * @dataProvider providerActions
     */
    public function testResponseContent(string $action): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, $this->getPlaceholderRequestPath($action));

        self::assertImagesSame($this->getPlaceholderThumbPath($action), $client->getResponse()->getContent());
    }

    /**
     * @depends      testThumbnail
     * @dataProvider providerActions
     *
     * @see          "cache_time: 42" in tests/Fixtures/App/config/packages/camelot_image_asset.yaml
     */
    public function testResponseHeaders(string $action): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, $this->getPlaceholderRequestPath($action));

        $this->assertResponseHeaderSame('Content-Type', 'image/jpeg');
        $this->assertResponseHeaderSame('Cache-Control', 'max-age=42, public, s-maxage=42');
    }

    /**
     * @depends testIndexWithTwigFilterToGenerateThumbnail
     */
    public function testThumbnailRetina(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, MountPathMockBuilder::buildPath('800x600/crop/placeholder@2x.jpg'));

        $this->assertIsSuccessful($client);
        $this->assertRouteSame(ImageAsset::THUMBNAIL_ROUTE, ['width' => 800, 'height' => 600, 'action' => 'crop', 'file' => 'placeholder@2x.jpg']);
    }

    /**
     * @depends testIndexWithTwigFilterToGenerateThumbnail
     */
    public function testThumbnailAlias(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, MountPathMockBuilder::buildPath('test_128x128/placeholder.jpg'));

        $this->assertIsSuccessful($client);
    }

    /**
     * @depends testThumbnailAlias
     */
    public function testThumbnailAliasRoute(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, MountPathMockBuilder::buildPath('test_128x128/placeholder.jpg'));

        $this->assertRouteSame(ImageAsset::THUMBNAIL_ALIAS_ROUTE, ['alias' => 'test_128x128', 'file' => 'placeholder.jpg']);
    }

    public function providerQueryStrings(): iterable
    {
        yield 'Empty query' => [''];
        yield 'Single parameter query' => ['v=1'];
        yield 'Two parameter query' => ['v=1&q=7fx23G'];
    }

    /**
     * @depends      testThumbnail
     * @dataProvider providerQueryStrings
     */
    public function testQueryStrings(string $queryString): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, $this->getPlaceholderRequestPath() . '?' . $queryString);

        self::assertImagesSame($this->getPlaceholderThumbPath(), $client->getResponse()->getContent());
    }

    private function assertImagesSame(string $targetFile, string $responseData): void
    {
        $file = FilesystemMockBuilder::create()->createImages()->get($targetFile);
        if ($file->read() !== $responseData) {
            static::fail(sprintf('Response does not match requested file %s', $targetFile));
        }
        $this->addToAssertionCount(1);
    }

    private function getPlaceholderRequestPath(string $action = Action::CROP): string
    {
        return str_replace('{action}', $action, self::$placeholderRequestPath);
    }

    private function getPlaceholderThumbPath(string $action = Action::CROP): string
    {
        return str_replace('{action}', $action, self::$placeholderThumbPath);
    }

    /** Spill less noise when exceptions occur. */
    private function assertIsSuccessful(KernelBrowser $client): void
    {
        $statusCode = $client->getResponse()->getStatusCode();
        static::assertSame(Response::HTTP_OK, $statusCode, 'Request was not successful and returned ' . $statusCode);
    }
}
