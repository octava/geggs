# geggs
Git eggs. Proxy command of git

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/8ca9cd4a-6298-446f-b1c5-6081115c3fc4/big.png)](https://insight.sensiolabs.com/projects/8ca9cd4a-6298-446f-b1c5-6081115c3fc4)

## Create build

1. Change version
2. Create and push tag
3. Create phar and manifest 
```
box build
manifest publish:gh-pages Octava/geggs -vvv
```
4. Go to github and upload new `geggs.phar`
