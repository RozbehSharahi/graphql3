# GraphQL 3

Graphql3 is based on TYPO3 sites. Each site has its own graphql endpoint. In dev mode also a graphiql endpoint.

```
https://[HOST]:[PORT]/my-site/graphql
https://[HOST]:[PORT]/my-site/grapihql
```

## Contribution

When interested in contribution, you will need to setup your own test environment (php, xdebug, imagemagick,...). I
personally use my own repo https://github.com/RozbehSharahi/doka.

### Code sniffing

```
vendor/bin/php-cs-fixer fix

// dry run for pipeline
vendor/bin/php-cs-fixer fix --dry-run
```

### Testing

```
vendor/bin/phpunit
```

### Testing build

In order to test your changes on an active TYPO3 installation you can use the `.build` directory, which contains
a `composer.json` that should be able to setup your build.

In order to install the build do:

```
cd .build
composer install
```

It will create a fresh installation and you might navigation to `http://YOUR_LOCAL_DOMAIN:PORT/`. It should ask you to
create a `FIRST_INSTALL` file.

Installation steps should be fast and easy if you use sqlite (hopefully :)).

When using my `doka` package the path would be `http://localhost:8080/typo3/install.php`, according to configuration
in `.doka.env`.