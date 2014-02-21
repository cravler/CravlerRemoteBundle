<?php

namespace Cravler\RemoteBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Cravler\RemoteBundle\Service\RemoteService;
use Cravler\RemoteBundle\Security\Authorization\Storage;
use Cravler\RemoteBundle\DependencyInjection\CravlerRemoteExtension;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class ServerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cravler:remote:server')
            ->setDescription('Start remote server')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getContainer()->getParameter(CravlerRemoteExtension::CONFIG_KEY);

        /* @var Storage $storage */
        $storage = $this->getContainer()->get('cravler_remote.security.authorization.storage');

        /* @var RemoteService $remoteService */
        $remoteService = $this->getContainer()->get('cravler_remote.service.remote_service');

        /* @var SecurityContextInterface $sc */
        $sc = $this->getContainer()->get('security.context');
        $sc->setToken(new AnonymousToken(uniqid(), 'anon.', array()));

        $output->writeln('<info>Server started:</info>');
        $output->writeln('<info>    server-port: ' . $config['server_port'] . '</info>');
        $output->writeln('<info>    secret: ' . $config['secret'] . '</info>');

        $remoteService->listen(function($remote, $connection) use ($storage, $config) {
            $remote->auth($connection->id, function($authToken) use ($connection, $storage, $config) {
                if ($authToken) {
                    $authToken = json_decode(json_encode($authToken), true);
                    if (isset($authToken['id']) == $connection->id) {
                        $hash = md5(implode(';', array($config['secret'], $connection->id)));
                        if (isset($authToken['hash']) && $authToken['hash'] == $hash) {
                            if (!$storage->tokenExists($authToken)) {
                                $storage->add($authToken);
                            }
                            $connection->on('end', function() use ($storage, $authToken) {
                                if ($storage->tokenExists($authToken)) {
                                    $storage->remove($authToken);
                                }
                            });
                        }
                    }
                }
            });
        });
    }
}
