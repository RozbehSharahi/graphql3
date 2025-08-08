## Contributing

Starting development environment

```shell
.docker/bin/start
```

`.docker/bin/compose` is basically a proxy for `docker-compose` so you can do the same things as you would do
with `docker-compose`.

```
.docker/bin/compose build
.docker/bin/compose logs -f
.docker/bin/compose down
```

## Testing

Running tests

```shell
.docker/bin/phpunit
```

Running php-cs-fixer

```shell
.docker/bin/php-cs-fixer
```

Running phpstan

```shell
.docker/bin/phpstan
```

Running all at once:

```shell
.docker/bin/test
```

### Testing application

```shell
bin/install-app-v12
bin/install-app-v11
```

Navigate to `http://localhost:8080/` and you should see the home.

Navigate to `http://localhost:8080/graphiql` and you should see your graphql interface.


> Known issue: On switching to v11 you might get an error 'Tried to instantiate a package object for package
> "graphql3test" with a non-existing package'. In that case just run the script again. Idk yet were this comes from.