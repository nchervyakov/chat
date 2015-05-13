<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 15.04.2015
 * Time: 17:24
  */



namespace AppBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateUsersOnlineStatusCommand
 * @package AppBundle\Command
 */
class UpdateUsersOnlineStatusCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('users:update-online-status')
            ->setDescription('Updates users online status based on their last request time.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start updating users\' online statuses...');

        $userManager = $this->getContainer()->get('app.user_manager');

        $beforeCount = $userManager->getOnlineUsersCount();
        $userManager->updateUsersOnlineStatus();
        $afterCount = $userManager->getOnlineUsersCount();

        $diff = $beforeCount - $afterCount;
        $output->writeln("$diff users went offline.");

        $output->writeln('Complete.');
    }
}