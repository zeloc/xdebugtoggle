<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Zeloc\XdebugToggle\Model\Config;

class XdebugConfig
{
    public static function getDebugModeConfigList(bool $isActive = true): array
    {
        $active = ['zend_extension' => 'xdebug.so'];
        $config =  [
            'xdebug.mode' => 'debug',
            'xdebug.client_port' => 9003,
            'xdebug.ide_key' => 'PHPSTORM',
            'xdebug.discover_client_host' => 0,
            'xdebug.client_host' => 'localhost',
            'xdebug.log' => '/var/log/xdebug.log'
        ];
        if ($isActive === true) {
            $config = array_merge($active, $config);
        }

        return $config;
    }

    public static function getCoverageModeConfigList(bool $isActive = true): array
    {
        $active = ['zend_extension' => 'xdebug.so'];
        $config =  [
            'xdebug.mode' => 'coverage'
        ];
        if ($isActive === true) {
            $config = array_merge($active, $config);
        }

        return $config;
    }

    public static function getConfigText(): string
    {
        $config = self::getDebugModeConfigList();
        $out = '';
        foreach ($config as $key => $value) {
            if ($key === 'xdebug.ide_key') {
                $out .= $key . "='" . $value . "'\n";
            } else {
                $out .= $key . "=" . $value . "\n";
            }
        }
        return $out;
    }

    public static function getXdebugConfigArray(string $path): array
    {
        $config = file($path, FILE_IGNORE_NEW_LINES);

        return $config === false ? [] : $config;
    }

    public static function getCurrentConfigArray(string $path): array
    {
        $xdebugIni = self::getXdebugConfigArray($path);
        $configArray = [];
        foreach ($xdebugIni as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, ';') || str_starts_with($line, '#')) {
                continue;
            }

            $lineData = explode('=', $line, 2);
            if (count($lineData) !== 2) {
                continue;
            }

            $param = trim($lineData[0]);
            $value = trim($lineData[1]);
            if ($param && $value) {
                $configArray[$param] = $value;
            }
        }

        return $configArray;
    }


    public static function getCurrentState(string $path): string
    {
        $result = self::getCurrentConfigArray($path);

        return array_key_exists('zend_extension', $result) ? 'enabled' : 'disabled';
    }

    public static function getXdebugConfigString(string $status): string
    {
        return self::getConfigString(self::getDebugModeConfigList($status === 'enabled'));
    }

    public static function getXdebugCoverageConfigString(string $status): string
    {
        return self::getConfigString(self::getCoverageModeConfigList($status === 'enabled'));
    }

    public static function getConfigString(array $arrayConfig): string
    {
        $configString = '';
        foreach ($arrayConfig as $index => $value) {
            $configString .= $index . '=' . $value . PHP_EOL;
        }

        return $configString;
    }
}