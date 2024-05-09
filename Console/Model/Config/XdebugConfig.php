<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Zeloc\XdebugToggle\Model\Config;

class XdebugConfig
{
    public static function getDebugModeConfigList($isActive = true)
    {
        $active = ['zend_extension' => 'xdebug.so'];
        $config =  [
            'xdebug.mode' => 'debug',
            'xdebug.client_port' => 9000,
            'xdebug.ide_key' => 'PHPSTORM',
            'xdebug.discover_client_host' => 0,
            'xdebug.client_host' => 'localhost',
            'xdebug.xdebug.log' => '/var/log/xdebug.log'
        ];
        if ($isActive === true) {
            $config = array_merge($active, $config);
        }

        return $config;
    }

    public static function getCoverageModeConfigList($isActive = true)
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

    public static function getConfigText()
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

    public static function getXdebugConfigArray($path)
    {
        return file($path);
    }

    public static function getCurrentConfigArray($path)
    {
        $xdebugIni = self::getXdebugConfigArray($path);
        $configArray = [];
        foreach ($xdebugIni as $line) {
            $lineData = explode('=', $line);
            $param = trim($lineData[0]) ?? false;
            $value = trim($lineData[1]) ?? false;
            if ($param && $value) {
                $configArray[$param] = $value;
            }
        }

        return $configArray;
    }


    public static function getCurrentState($path)
    {
        $result = self::getCurrentConfigArray($path);

        return array_key_exists('zend_extension', $result) ? 'enabled' : 'disabled';
    }

    public static function getXdebugConfigString($status)
    {
        if ($status === 'enabled') {
            $configArray = self::getDebugModeConfigList();
            return self::getConfigString($configArray);
        }
        if ($status === 'disabled') {
            $configArray = self::getDebugModeConfigList(false);
            return self::getConfigString($configArray);
        }
    }

    public static function getXdebugCoverageConfigString($status)
    {
        if ($status === 'enabled') {
            $configArray = self::getCoverageModeConfigList();
            return self::getConfigString($configArray);
        }
        if ($status === 'disabled') {
            $configArray = self::getCoverageModeConfigList(false);
            return self::getConfigString($configArray);
        }
    }

    public static function getConfigString($arrayConfig)
    {
        $configString = '';
        foreach ($arrayConfig as $index => $value) {
            $configString .= $index . '=' . $value . PHP_EOL;
        }

        return $configString;
    }
}