InspireVive
===========

[![Build Status](https://travis-ci.org/InspireVive/inspirevive.svg?branch=master&style=flat)](https://travis-ci.org/InspireVive/inspirevive)
[![Coverage Status](https://coveralls.io/repos/InspireVive/inspirevive/badge.svg?style=flat)](https://coveralls.io/r/InspireVive/inspirevive)
[![Latest Stable Version](https://poser.pugx.org/InspireVive/inspirevive/v/stable.svg?style=flat)](https://packagist.org/packages/InspireVive/inspirevive)

Volunteer management platform to help organizations volunteer more effectively

## Requirements

- PHP 7.1+
- [Composer](https://getcomposer.org/)
- MySQL/MariaDB
- Redis
- wkhtmltopdf

## Installation

### Composer Dependencies

Composer manages all PHP dependencies:

	composer install

### secrets.php

A secrets.php file needs to be created in the `config` directory. See secrets.php.example for an example setup. *Never commit secrets.php to version control, for security.*

### Database Migrations

The database migrations can be ran with:

	php infuse migrate

### Compile front-end assets

The front-end assets can be compiled with grunt:

	grunt

## Contributing

Please feel free to contribute by participating in the issues or by submitting a pull request. :-)

### Tests

The included tests can be ran with:

	phpunit

## License

Copyright (c) 2015 Jared King

InspireVive is licensed under the GNU GPL v3 license in the `LICENSE` file. The InspireVive brand and logo are copyrights of InspireVive.