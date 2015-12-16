# geggs
Git eggs. Proxy command of git

## Create build

1. Change version
2. Create and push tag
3. Create phar and manifest 
```
box build
manifest publish:gh-pages Octava/geggs -vvv
```
4. Go to github and upload new `geggs.phar`
