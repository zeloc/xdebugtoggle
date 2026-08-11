# Zeloc Xdebug Toggle (Magento 2)

Magento 2 module that adds a CLI command to toggle Xdebug between debug and coverage modes by updating the Xdebug INI file and restarting PHP-FPM.

## Requirements

- PHP `~8.4.0` (from `composer.json`)
- Magento 2 module environment
- Permission to write to `/etc/php/<version>/mods-available/xdebug.ini`
- Permission to run `sudo service php<version>-fpm restart`

## Install

```bash
composer require zeloc/xdebugtoggle
```

Then enable and upgrade in Magento as usual:

```bash
bin/magento module:enable Zeloc_XdebugToggle
bin/magento setup:upgrade
bin/magento cache:flush
```

## Command

```bash
bin/magento zeloc:xdebug:toggle --mode=d
bin/magento zeloc:xdebug:toggle --mode=c
```

- `--mode=d` toggles Debug mode (`xdebug.mode=debug`)
- `--mode=c` toggles Coverage mode (`xdebug.mode=coverage`)

If `--mode` is missing or invalid, the command returns a non-zero exit code.

## PHP Version Source

The command reads the PHP version from Magento config path:

`zeloc_xdebugtoggle/php/version`

Default in `etc/config.xml`:

`8.3`

The computed target INI path is:

`/etc/php/<version>/mods-available/xdebug.ini`

## Generated Xdebug Settings

When enabled in debug mode, the module writes:

- `zend_extension=xdebug.so`
- `xdebug.mode=debug`
- `xdebug.client_port=9003`
- `xdebug.ide_key=PHPSTORM`
- `xdebug.discover_client_host=0`
- `xdebug.client_host=localhost`
- `xdebug.log=/var/log/xdebug.log`

When enabled in coverage mode, the module writes:

- `zend_extension=xdebug.so`
- `xdebug.mode=coverage`

When disabled, `zend_extension` is omitted.

## Notes

- This module edits a system PHP config file; run the command with appropriate permissions.
- Restarting PHP-FPM is required for changes to take effect.
- For containerized environments, you may need to adjust `xdebug.client_host` and discovery behavior.
