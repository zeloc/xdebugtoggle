<?php

namespace Zeloc\XdebugToggle\Console\Command;

use Epicor\CacheWarmer\Model\Config\Source\PageType as PageType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Symfony\Component\Console\Input\InputOption;
use Zeloc\XdebugToggle\Model\Config\XdebugConfig;

class ToggleXdebugCommand extends Command
{
    const ENTITY_TYPE = 'mode';

    private $commandName = 'zeloc:xdebug:toggle';

    private $commandDescription = 'Toggles xdebug on or off use --mode=d (debug mode) or --mode=c (unit coverage)';

    private $phpVersion;

    private $xdebugFilePath;

    private $output;
    /**
     * @var ScopeConfig
     */
    private $scopeConfig;

    public function __construct(
        ScopeConfig $scopeConfig,
                    $name = null
    ) {
        parent::__construct($name);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                'mode',
                null,
                InputOption::VALUE_REQUIRED,
                'Mode'
            )
        ];
        $this->setName($this->commandName);
        $this->setDescription($this->commandDescription);
        $this->setDefinition($options);

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mode = $input->getOption('mode');
        if (!in_array($mode, ['d', 'c'])) {
            $output->writeln('<fg=red>Use: --mode=d or --mode=c</>');
        } else {
            $this->phpVersion = $this->getPhpVersion();
            $this->xdebugFilePath = $this->getXdebugIniPath();
            $this->output = $output;
            if ($mode === 'd') {
                $this->toggleDebug();
            }
            if ($mode === 'c') {
                $this->toggleCoverage();
            }
        }

        return 0;
    }

    /**
     * @return void
     */
    private function toggleCoverage()
    {
        if (!is_writable($this->xdebugFilePath)) {
            $this->output
                ->writeln('<fg=red>Can\'t update xdebug.ini >>> Update permissions to make xdebug.ini writeable</>');
        }

        $this->infoHeading();
        $currentState = XdebugConfig::getCurrentState($this->xdebugFilePath);
        if ($currentState === 'disabled') {
            //toggle then sets enabled
            $updateConfig = XdebugConfig::getXdebugCoverageConfigString('enabled');
            $resultOutput = 'enabled';
        } else {
            $updateConfig = XdebugConfig::getXdebugCoverageConfigString('disabled');
            $resultOutput = 'disabled';
        }
        $this->infoUpdating();
        $this->infoOutputStatus($resultOutput);
        $this->infoMode('coverage');
        $this->writeConfig($updateConfig);
        $this->infoShowChangeInfo($updateConfig);

        $this->restartFpmService();
        $this->infoWriteFooter();
    }

    private function toggleDebug()
    {
        if (!is_writable($this->xdebugFilePath)) {
            $this->output
                ->writeln('<fg=red>Can\'t update xdebug.ini >>> Update permissions to make xdebug.ini writeable</>');
        }

        $this->infoHeading();
        $currentState = XdebugConfig::getCurrentState($this->xdebugFilePath);
        if ($currentState === 'disabled') {
            //toggle then sets enabled
            $updateConfig = XdebugConfig::getXdebugConfigString('enabled');
            $resultOutput = 'enabled';
        } else {
            $updateConfig = XdebugConfig::getXdebugConfigString('disabled');
            $resultOutput = 'disabled';
        }
        $this->infoUpdating();
        $this->infoOutputStatus($resultOutput);
        $this->infoMode('debug');
        $this->writeConfig($updateConfig);
        $this->infoShowChangeInfo($updateConfig);

        $this->restartFpmService();
        $this->infoWriteFooter();
    }

    private function infoOutputStatus($status)
    {
        if ($status === 'enabled') {
            $state = 'ON';
        } else {
            $state = 'OFF';
        }
        $this->output->writeln('<info>Xdebug Status Now: </info><fg=blue>' . $state . '</>');
    }

    private function restartFpmService()
    {
        shell_exec("sudo service php$this->phpVersion-fpm restart");
    }

    private function infoMode($mode)
    {
        $this->output->writeln('<info>Xdebug Status Mode: </info><fg=blue>' . $mode . '</>');
    }

    private function infoHeading()
    {
        $this->output->writeln('');
        $this->output->writeln('<question>########    Toggle Xdebug on/off    #########</question>');
        $this->output->writeln('');
        $this->output->writeln('<info>Target php version: </info><fg=blue>'.$this->phpVersion.'</>');
        $this->output->writeln('');
    }

    private function infoShowChangeInfo($updateConfig)
    {
        $this->output->writeln('');
        $this->output->writeln('<fg=gray>Current config now:</>');
        $this->output->writeln('<fg=yellow>' . $updateConfig . '</>');
        $this->output->writeln('Restarting php fpm service....');
    }

    private function infoUpdating()
    {
        $this->output->writeln('Updating xdebug.ini file....');
    }

    public function getXdebugIniPath()
    {
        $phpVersion = $this->getPhpVersion();
        return "/etc/php/$phpVersion/mods-available/xdebug.ini";
    }

    public function getPhpVersion()
    {
        return $this->scopeConfig->getValue('zeloc_xdebugtoggle/php/version');
    }

    public function writeConfig($configText)
    {
        file_put_contents($this->xdebugFilePath, $configText);
    }

    private function infoWriteFooter()
    {
        $this->output->writeln('');
        $this->output->writeln('<question>#############################################</question>');
        $this->output->writeln('');
    }


}
