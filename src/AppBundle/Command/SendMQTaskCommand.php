<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 05.05.2015
 * Time: 18:54
  */



namespace AppBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendMQTaskCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('mq:send-task')
            ->setDescription('Sends a new task in the queue.')
            ->addArgument('message', InputArgument::REQUIRED, "message to send");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('old_sound_rabbit_mq.notifications_producer')->publish($input->getArgument('message'));
    }
}