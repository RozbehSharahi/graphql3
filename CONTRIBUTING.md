## Contributing

Starting development environment

```shell
bin/docker up -d
```

`bin/docker` is basically a proxy for `docker-compose` so you can do the same things as you would do
with `docker-compose`.

```
bin/docker build
bin/docker logs -f
bin/docker down
```

## Testing

Running tests

```shell
bin/phpunit
```

Running php-cs-fixer

```shell
bin/php-cs-fixer
```

Running phpstan

```shell
bin/phpstan
```

Running all at once:

```shell
bin/test
```

### Testing build

In order to test your changes on an active TYPO3 installation you can use the `./Build` directory, which contains
a `composer.json` that should be able to set up your build.

In order to install the build do:

```
bin/cli
cd Build
composer install
```

This will create a fresh TYPO3 installation. Navigate to `http://localhost:8080/`, where it should ask you to create
a `FIRST_INSTALL` file.

Installation steps should be fast and easy if you use sqlite (hopefully :)).