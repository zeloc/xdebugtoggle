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
        $xdebugIniFile = '/etc/php/7.1/mods-available/xdebug.ini';
        if(!is_writable($xdebugIniFile)){
            $output->writeln('<fg=red>Can\'t update xdebug.ini >>> Update permissions to make xdebug.ini writeable</>');
        }
        $output->writeln('');
        $output->writeln('<question>########    Toggle Xdebug on/off    #########</question>');
        $output->writeln('');
        $xdebugIni = file_get_contents('/etc/php/7.1/mods-available/xdebug.ini');
        preg_match('/^;z/', $xdebugIni, $matches);
        $result = $matches[0] ?? false;

        if ($result) {
            $out = preg_replace('/^;z/', 'z', $xdebugIni);
            $resultOutput = '<info>Xdebug Status: </info><fg=blue>ON</>';
        } else {
            $out = preg_replace('/^z/', ';z', $xdebugIni);
            $resultOutput = '<info>Xdebug Status: </info><fg=blue>OFF</>';
        }
        $output->writeln('Updating xdebug.ini file....');
        file_put_contents($xdebugIniFile, $out);
        $output->writeln('Restarting php fpm service....');
        shell_exec('sudo service php7.1-fpm restart');

        $output->writeln($resultOutput);
        $output->writeln('');
        $output->writeln('<question>#############################################</question>');
        $output->writeln('');
    }
}
