<?php

namespace Cravler\RemoteBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Cravler\RemoteBundle\DependencyInjection\CravlerRemoteExtension;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class UbuntuUpstartCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cravler:remote:ubuntu:upstart')
            ->setDescription('Ubuntu upstart config generator')
            ->setHelp('sudo bash -c "app/console ' . $this->getName() . ' [type] > /etc/init/cravler-remote-[type].conf"')
            ->addArgument('type', InputArgument::OPTIONAL, 'types: app, server', 'app')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getContainer()->getParameter(CravlerRemoteExtension::CONFIG_KEY);

        $templating = $this->getContainer()->get('templating');

        $type = $input->getArgument('type') == 'server' ? 'server' : 'app';
        $homeDir = dirname(__DIR__) . '/Resources/nodejs';
        if ('server' == $type) {
            $homeDir = dirname($this->getContainer()->get('kernel')->getRootdir());
        }

        echo $templating->render('CravlerRemoteBundle:UbuntuUpstart:' . $type . '.conf.twig', array(
            'HOME_DIR'    => $homeDir,
            'SECRET'      => $config['secret'],
            'APP_PORT'    => $config['app_port'],
            'REMOTE_PORT' => $config['remote_port'],
            'SERVER_PORT' => $config['server_port'],
            'SERVER_HOST' => $config['server_host'],
        ));
    }
}
