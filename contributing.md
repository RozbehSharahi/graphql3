## Contributing

When interested in contribution, you will need to set up your own test environment (php, xdebug, imagemagick,...). I
personally use my own repo https://github.com/RozbehSharahi/doka, which you are also free to use ofc.

### Testing (and sniffing)

```
composer run tests
```

### Testing build

In order to test your changes on an active TYPO3 installation you can use the `.build` directory, which contains
a `composer.json` that should be able to setup your build.

In order to install the build do:

```
cd .build
composer install
```

This will create a fresh TYPO3 installation. Navigate to `http://[HOST]:[PORT]/`, where it should ask you to create
a `FIRST_INSTALL` file.

Installation steps should be fast and easy if you use sqlite (hopefully :)).

When using my `doka` package, the path would be `http://localhost:8080/typo3/install.php`, according to configuration
in `.doka.env`.