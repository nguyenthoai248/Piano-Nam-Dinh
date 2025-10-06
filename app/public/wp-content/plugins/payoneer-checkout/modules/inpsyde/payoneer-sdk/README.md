# Payoneer SDK
[![Continuous Integration](https://github.com/inpsyde/payoneer-sdk/actions/workflows/testing.yml/badge.svg)](https://github.com/inpsyde/payoneer-sdk/actions/workflows/testing.yml)

This package provides development tools (SDK) for server-side interaction with the [Payoneer Orchestration Platform](https://www.optile.io/).
It was created as a part of the [Payoneer for Woocommerce plugin][] project, and has serving its needs as a main goal.
This means that this package does not fully implement all possibilities of the Payoneer Orchestration Platform's API. 
Instead, it strives to implement tools needed API parts in the best way.

This package expects [PSR-7(HTTP Message)][], [PSR-17(HTTP Factory)][] and [PSR-18(HTTP Client)][] standards implementations will be provided by consuming code.

## Installation

The best way to install this package is through Composer:

```BASH
$ composer require inpsyde/payoneer-sdk.
```

## Usage

Extend these services from your application with real operable objects and data:
* `payoneer_sdk.remote_api_url.base_string`,
* `payoneer_sdk.uri_factory`,
* `payoneer_sdk.http_client`,
* `payoneer_sdk.api_username`,
* `payoneer_sdk.api_password`.

Check the [inpsyde/modularity][] documentation if you need to know more about services extending procedure.

Next, retrieve the API Client from the service `payoneer_sdk.api_client` and make API calls using it. Currently, it has
very limited abilities. Feel free to extend it in your application or contribute to this package to add features you 
need.


## Development

1. Run `make setup` to set up Docker and install dependencies.
2. Run `make lint test` to run linter and tests.

See [Makefile](/Makefile) for other useful commands.

The [.env](/.env.example) file contains some configuration of the Docker environment.
You may need to rebuild Docker for changes (like WP version) to take effect: `make destroy setup` (all WP data will be lost). 

For Windows users: `make` is not included out-of-the-box but you can simply copy the commands from [Makefile](/Makefile) to `cmd`,
e.g. `docker-compose run --rm test vendor/bin/phpunit` instead of `make test`.

## Copyright and License

This package is [free software](https://www.gnu.org/philosophy/free-sw.en.html) distributed under the terms of the GNU General Public License version 2 or (at your option) any later version. For the full license, see [LICENSE](./LICENSE).

## Contributing

All feedback / bug reports / pull requests are welcome.

[inpsyde/modularity]: https://github.com/inpsyde/modularity
[Payoneer for Woocommerce plugin]: https://github.com/inpsyde/payoneer-for-woocommerce
[PSR-7(HTTP Message)]: https://packagist.org/providers/psr/http-message-implementation
[PSR-17(HTTP Factory)]: https://packagist.org/providers/psr/http-factory-implementation
[PSR-18(HTTP Client)]: https://packagist.org/providers/psr/http-client-implementation
[Syde]: https://syde.com
