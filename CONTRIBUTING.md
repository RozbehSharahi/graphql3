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

### Testing application

Navigate to `http://localhost:8080/`, where it should ask you to create a `FIRST_INSTALL` file.

Installation steps should be fast and easy if you use sqlite (hopefully :)).