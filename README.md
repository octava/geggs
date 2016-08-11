# geggs
Git eggs. Proxy command of git.

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/8ca9cd4a-6298-446f-b1c5-6081115c3fc4/big.png)](https://insight.sensiolabs.com/projects/8ca9cd4a-6298-446f-b1c5-6081115c3fc4)

## Installation

Download last release version from https://github.com/octava/geggs/releases and move to bin directory.

Now just run geggs in order to run geggs

Example
```
curl -O https://github.com/octava/geggs/releases/download/3.1.0/geggs.phar
chmod +x geggs.phar
sudo mv geggs.phar /usr/local/bin/geggs
```

## Update

Run `sudo geggs self-update` for update geggs

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
