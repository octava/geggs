# geggs
Git eggs. Proxy command of git

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

## Create build

1. Change version
2. Create and push tag
3. Create phar and manifest 
```
box build
manifest publish:gh-pages Octava/geggs -vvv
```
4. Go to github and upload new `geggs.phar`
