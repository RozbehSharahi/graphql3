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

```shell
bin/install-app-v12
bin/install-app-v11
```

Navigate to `http://localhost:8080/` and you should see the home.

Navigate to `http://localhost:8080/graphiql` and you should see your graphql interface.


> Known issue: On switching to v11 you might get an error 'Tried to instantiate a package object for package
> "graphql3test" with a non-existing package'. In that case just run the script again. Idk yet were this comes from.