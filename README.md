# GraphQL 3

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

In order to test your changes on an active TYPO3 installation, you will need
call `http://your-local-domain/typo3/install.php`. The installation is the same as with fresh installation.

When using sqlite in install wizard, you will not need further configurations.

When using my `doka` package the path would be `http://localhost:8080/typo3/install.php`, according to configuration
in `.doka.env`.