<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay Chervyakov 
 * Date: 17.03.2015
 * Time: 16:10
 */


namespace AppBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class EmoticonsPrepareCommand extends ContainerAwareCommand
{
    protected $defaultPath;

    protected function configure()
    {
        $this->defaultPath = __DIR__ . '/../../../web/images/emoticons';

        $this->setName('emoticons:convert-qip')
            ->setDescription('Converts the QIP emoticon config into Symfony2 fixture file.')
            ->addArgument('path', InputArgument::OPTIONAL, "Path to the directory with emoticons and QIP config",
                    $this->defaultPath)
            ->addOption('target', 't', InputOption::VALUE_OPTIONAL, "Target fixture file with path",
                    __DIR__ . '/../Resources/fixtures/emoticons.yml');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        $filesystem = $this->getContainer()->get('filesystem');

        $output->writeln('Source path: ' . $path);

        if (!$filesystem->exists($path) || !is_dir($path)) {
            $output->writeln('<error>Source path does not exist!</error>');
            return;
        }

        $path = realpath($path);
        $configPath = realpath($path . '/_define.ini');
        $output->writeln('Source config: ' . $configPath);

        if (!$filesystem->exists($configPath)) {
            $output->writeln('<error>Source config does not exist!</error>');
            return;
        }

        $config = file($configPath) ?: [];

        $targetConfig = [];
        foreach ($config as $key => $configString) {
            $variants = preg_split('/\s*,\s*/', trim($configString), -1, PREG_SPLIT_NO_EMPTY);
            $emoticon = [
                'symbol' => array_shift($variants),
                'aliases' => $variants,
                'icon' => $this->convertKeyToAlphaBase($key) . '.gif'
            ];

            $targetConfig['emoticon' . ($key + 1)] = $emoticon;
        }
        $targetConfig = ['AppBundle\\Entity\\Emoticon' => $targetConfig];

        $targetPath = $input->getOption('target');
        $targetDir = dirname($targetPath);

        if (!file_exists($targetDir)) {
            $filesystem->mkdir($targetDir);
        }

        file_put_contents($targetPath, Yaml::dump($targetConfig));

        $output->writeln('<info>The config has been successfully created!</info>');
    }

    public function convertKeyToAlphaBase($number)
    {
        $converted = str_pad(base_convert(''.$number, 10, 26), 2, '0', STR_PAD_LEFT);
        $search = array_reverse(["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f",
                "g", "h", "i", "j", "k", "l", "m", "n", "o", "p"]);
        $replace = array_reverse(["a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p",
                "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"]);
        return str_replace($search, $replace, $converted);
    }
}