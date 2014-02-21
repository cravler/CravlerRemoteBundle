<?php

namespace Cravler\RemoteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Cravler\RemoteBundle\Security\TokenFactory;
use Cravler\RemoteBundle\Service\RemoteService;
use Cravler\RemoteBundle\DependencyInjection\CravlerRemoteExtension;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class RemoteController extends Controller
{
    /**
     * @param Request $request
     */
    protected function initSession(Request $request)
    {
        $session = $request->getSession();
        if ($session instanceof Session && !$session->getId()) {
            $session->start();
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function roomsAction(Request $request)
    {
        $this->initSession($request);

        $config = $this->container->getParameter(CravlerRemoteExtension::CONFIG_KEY);

        $roomsChain = $this->container->get('cravler_remote.service.rooms_chain');
        $rooms = array();
        foreach ($roomsChain->getRooms() as $room) {
            if ($room->isAllowed()) {
                $rooms[] = $room->getId();
            }
        }

        $hash = md5(implode(';', $rooms) . ';' . $config['secret']);

        return new JsonResponse(array(
            'ids'  => $rooms,
            'hash' => $hash
        ));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createTokenAction(Request $request)
    {
        $this->initSession($request);

        /* @var SecurityContextInterface $sc */
        $sc = $this->container->get('security.context');
        /* @var TokenFactory $factory */
        $factory = $this->container->get('cravler_remote.security.token_factory');

        $config = $this->container->getParameter(CravlerRemoteExtension::CONFIG_KEY);

        $user = null;
        $remoteKey = array();

        $session = $request->getSession();
        if ($session->getId() == $request->get('session')) {

            $hash = md5(implode(';', array(
                $request->get('id'), $config['secret'], $request->get('session')
            )));

            if ($hash === $request->get('hash')) {
                $remoteKey = array(
                    'id'      => $request->get('id'),
                    'session' => $session->getId(),
                    'hash'    => $hash
                );

                if ($sc->getToken()) {
                    if ($sc->getToken()->getUser() instanceof UserInterface) {
                        $user = $sc->getToken()->getUser();
                    }
                }
            }
        }

        return new JsonResponse($factory->createArrayToken($user, $remoteKey));
    }

    // DEMO

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function demoAction(Request $request)
    {
        $this->initSession($request);

        return $this->render('CravlerRemoteBundle:Remote:demo.html.twig');
    }

    /**
     * @return JsonResponse
     */
    public function demoMessageAction(Request $request)
    {
        $this->initSession($request);

        /* @var RemoteService $remoteService */
        $remoteService = $this->get('cravler_remote.service.remote_service');

        try {
            $msg = 'message: ' . date('Y-m-d H:i:s');

            $remoteProxy = $remoteService->createRemoteProxy();
            $remoteProxy->dispatch(array(
                'type' => 'cravler_remote.demo',
                'name' => 'message',
                'data' => array(
                    'response' => $msg
                ),
            ));

        } catch (\Exception $e) {
            $msg = $e->getMessage();
        }

        return new JsonResponse(array(
            $msg
        ));
    }
}
