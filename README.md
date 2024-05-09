Version 6.0.2

### **Now supports use changing the mode to coverage when using unit testing**

The php version can now be specified in the config.xml file currently defaulting to 8.1
```
composer require zeloc/xdebugtoggle
```
The above represents the name that is defined in the modules composer.json

Runs with n98 mage run or bin/magento
```
zeloc:xdebug:toggle  or short z:x:t
Modes now used:

Debug mode:
zeloc:xdebug:toggle --mode=d

Coverage mode
zeloc:xdebug:toggle --mode=c


```

```
