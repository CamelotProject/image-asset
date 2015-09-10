<?php

namespace Bolt\Thumbs;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for the thumbnail route.
 * Passes the parsed request to the service.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class Controller implements ControllerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function connect(Application $app)
    {
        /** @var ControllerCollection $ctr */
        $ctr = $app['controllers_factory'];

        $toInt = function ($value) {
            return intval($value);
        };
        $toAction = function($value) {
            $actions = [
                'c' => Action::CROP,
                'r' => Action::RESIZE,
                'b' => Action::BORDER,
                'f' => Action::FIT,
            ];
            return isset($actions[$value]) ? $actions[$value] : Action::CROP;
        };
        $ctr->get('/{width}x{height}{action}/{file}', 'controller.thumbnails:thumbnail')
            ->assert('width', '\d+')
            ->convert('width', $toInt)
            ->assert('height', '\d+')
            ->convert('width', $toInt)
            ->assert('action', '[a-z]?')
            ->convert('action', $toAction)
            ->assert('file', '.+')
            ->bind('thumb');

        return $ctr;
    }

    /**
     * Returns a thumbnail response.
     *
     * @param Application $app
     * @param Request     $request
     * @param string      $file
     * @param string      $action
     * @param int         $width
     * @param int         $height
     *
     * @return Response
     */
    public function thumbnail(Application $app, Request $request, $file, $action, $width, $height)
    {
        if (strpos($file, '@2x') !== false) {
            $file = str_replace('@2x', '', $file);
            $width *= 2;
            $height *= 2;
        }

        $requestPath = urldecode($request->getPathInfo());
        $transaction = new Transaction($file, $action, new Dimensions($width, $height), $requestPath);

        $thumbnail = $app['thumbnails']->respond($transaction);

        return new Response($thumbnail);
    }
}
