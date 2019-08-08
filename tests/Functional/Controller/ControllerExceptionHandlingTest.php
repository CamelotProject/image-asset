<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Functional\Controller;

use Camelot\ImageAsset\Response\Response;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use Camelot\ImageAsset\Tests\Fixtures\Routing\MountPathMockBuilder;
use Camelot\ImageAsset\Tests\Functional\WebTestCase;
use Camelot\ImageAsset\Tests\ThumbnailAssertTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group  functional
 * @covers \Camelot\ImageAsset\Controller\ImageController
 * @covers \Camelot\ImageAsset\Controller\ImageAliasController
 */
final class ControllerExceptionHandlingTest extends WebTestCase
{
    use ThumbnailAssertTrait;

    public function testDebugOff404Handling(): void
    {
        $expected = FilesystemMockBuilder::create()->createImages()->read('400x300/crop/default.png');
        $client = self::createClient(['debug' => false]);
        $client->request(Request::METHOD_GET, MountPathMockBuilder::buildPath('400x300/crop/never-here.jpg'));

        $response = $client->getResponse();
        $content = $response->getContent();

        $this->assertHttpStatusCode(Response::HTTP_NOT_FOUND, $response);
        $this->assertThumbnailsSame($expected, $content);
    }
}
