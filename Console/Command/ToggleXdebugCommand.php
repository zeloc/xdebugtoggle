<?php

namespace Zeloc\XdebugToggle\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ToggleXdebugCommand extends Command
{
    protected function configure()
    {
        $this->setName('zeloc:xdebug:toggle');
        $this->setDescription('Toggles xdebug on or off');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $xdebugIni = file_get_contents('/etc/php/7.0/mods-available/xdebug.ini');
        preg_match('/^;z/', $xdebugIni, $matches);
        $result = $matches[0] ?? false;

        if ($result) {
            $out = preg_replace('/^;z/', 'z', $xdebugIni);
            $output->writeln('Setting Xdebug ON');
        } else {
            $out = preg_replace('/^z/', ';z', $xdebugIni);
            $output->writeln('Setting Xdebug OFF');
        }
        file_put_contents('/etc/php/7.0/mods-available/xdebug.ini', $out);
        shell_exec('sudo service php7.0-fpm restart');
    }
}
