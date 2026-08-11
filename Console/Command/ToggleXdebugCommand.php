<?php

namespace Zeloc\XdebugToggle\Console\Command;

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zeloc\XdebugToggle\Model\Config\XdebugConfig;

class ToggleXdebugCommand extends Command
{
    private const ENTITY_TYPE = 'mode';

    private string $commandName = 'zeloc:xdebug:toggle';

    private string $commandDescription = 'Toggles xdebug on or off use --mode=d (debug mode) or --mode=c (unit coverage)';

    private string $phpVersion = '';

    private string $xdebugFilePath = '';

    private ?OutputInterface $output = null;
    /**
     * @var ScopeConfig
     */
    private ScopeConfig $scopeConfig;

    public function __construct(
        ScopeConfig $scopeConfig,
        ?string $name = null
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
                self::ENTITY_TYPE,
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
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mode = $input->getOption(self::ENTITY_TYPE);
        if (!is_string($mode) || !in_array($mode, ['d', 'c'], true)) {
            $output->writeln('<fg=red>Use: --mode=d or --mode=c</>');

            return 1;
        }

        $this->phpVersion = $this->getPhpVersion();
        if ($this->phpVersion === '') {
            $output->writeln('<fg=red>Missing configured PHP version at zeloc_xdebugtoggle/php/version</>');

            return 1;
        }

        $this->xdebugFilePath = $this->getXdebugIniPath();
        $this->output = $output;
        if ($mode === 'd') {
            $this->toggleDebug();
        }
        if ($mode === 'c') {
            $this->toggleCoverage();
        }

        return 0;
    }

    /**
     * @return void
     */
    private function toggleCoverage(): void
    {
        if (!is_writable($this->xdebugFilePath)) {
            $this->output
                ->writeln('<fg=red>Can\'t update xdebug.ini >>> Update permissions to make xdebug.ini writeable</>');

            return;
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
        if (!$this->writeConfig($updateConfig)) {
            $this->output->writeln('<fg=red>Failed to write the updated xdebug.ini configuration</>');

            return;
        }
        $this->infoShowChangeInfo($updateConfig);

        $this->restartFpmService();
        $this->infoWriteFooter();
    }

    private function toggleDebug(): void
    {
        if (!is_writable($this->xdebugFilePath)) {
            $this->output
                ->writeln('<fg=red>Can\'t update xdebug.ini >>> Update permissions to make xdebug.ini writeable</>');

            return;
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
        if (!$this->writeConfig($updateConfig)) {
            $this->output->writeln('<fg=red>Failed to write the updated xdebug.ini configuration</>');

            return;
        }
        $this->infoShowChangeInfo($updateConfig);

        $this->restartFpmService();
        $this->infoWriteFooter();
    }

    private function infoOutputStatus(string $status): void
    {
        if ($status === 'enabled') {
            $state = 'ON';
        } else {
            $state = 'OFF';
        }
        $this->output->writeln('<info>Xdebug Status Now: </info><fg=blue>' . $state . '</>');
    }

    private function restartFpmService(): void
    {
        shell_exec("sudo service php$this->phpVersion-fpm restart");
    }

    private function infoMode(string $mode): void
    {
        $this->output->writeln('<info>Xdebug Status Mode: </info><fg=blue>' . $mode . '</>');
    }

    private function infoHeading(): void
    {
        $this->output->writeln('');
        $this->output->writeln('<question>########    Toggle Xdebug on/off    #########</question>');
        $this->output->writeln('');
        $this->output->writeln('<info>Target php version: </info><fg=blue>'.$this->phpVersion.'</>');
        $this->output->writeln('');
    }

    private function infoShowChangeInfo(string $updateConfig): void
    {
        $this->output->writeln('');
        $this->output->writeln('<fg=gray>Current config now:</>');
        $this->output->writeln('<fg=yellow>' . $updateConfig . '</>');
        $this->output->writeln('Restarting php fpm service....');
    }

    private function infoUpdating(): void
    {
        $this->output->writeln('Updating xdebug.ini file....');
    }

    public function getXdebugIniPath(): string
    {
        $phpVersion = $this->getPhpVersion();

        return "/etc/php/$phpVersion/mods-available/xdebug.ini";
    }

    public function getPhpVersion(): string
    {
        return trim((string)$this->scopeConfig->getValue('zeloc_xdebugtoggle/php/version'));
    }

    public function writeConfig(string $configText): bool
    {
        return file_put_contents($this->xdebugFilePath, $configText) !== false;
    }

    private function infoWriteFooter(): void
    {
        $this->output->writeln('');
        $this->output->writeln('<question>#############################################</question>');
        $this->output->writeln('');
    }


}
