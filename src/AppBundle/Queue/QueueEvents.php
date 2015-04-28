<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 27.04.2015
 * Time: 13:44
  */



namespace AppBundle\Queue;


class QueueEvents 
{
    const MESSAGE_ADDED = 'added.message';
    const MESSAGE_READ = 'read.message';
}