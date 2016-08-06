# geggs
Git eggs. Proxy command of git.

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/8ca9cd4a-6298-446f-b1c5-6081115c3fc4/big.png)](https://insight.sensiolabs.com/projects/8ca9cd4a-6298-446f-b1c5-6081115c3fc4)

## Installation

Download geggs.phar from [latest release](https://github.com/octava/geggs/releases/latest).

## Init
Run `geggs init` for create configuration file and example plugin and command.
Add your vendors as in example in file *.geggs/geggs.yml*
```
octava_geggs:
    dir:
        vendors:
            - vendor/my-lib
            - vendor/my-lib2
```
After that you are ready to work with **geggs**

## Build new version of geggs

* Create and push tag
* Create phar 
```
ulimit -Sn 4096; box build --verbose
```
* Go to github and upload new `geggs.phar` into new release
* Publish new manifest
```
manifest publish:gh-pages octava/geggs -vvv
```
