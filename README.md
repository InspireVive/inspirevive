InspireVive
===========

[![Build Status](https://travis-ci.org/InspireVive/inspirevive.svg?branch=master&style=flat)](https://travis-ci.org/InspireVive/inspirevive)
[![Coverage Status](https://coveralls.io/repos/InspireVive/inspirevive/badge.svg?style=flat)](https://coveralls.io/r/InspireVive/inspirevive)
[![Latest Stable Version](https://poser.pugx.org/InspireVive/inspirevive/v/stable.svg?style=flat)](https://packagist.org/packages/InspireVive/inspirevive)

Volunteer management platform to help organizations volunteer more effectively

## Requirements

- PHP 5.6+
- [Composer](https://getcomposer.org/)
- MySQL/MariaDB
- Redis
- wkhtmltopdf

Third party APIs used:

- [Mandrill](http://mandrill.com) - email
- Twitter
- Facebook
- Instagram

## Installation

### Composer Dependencies

Composer manages all PHP dependencies:

	composer install

### config.php

A config.php file needs to be created in the project's root directory. See config-exmaple.php for an example setup. *Never commit config.php to version control, for security.*

### Database Migrations

The database migrations can be ran with:

	php infuse migrate

### Compile front-end assets

The front-end assets can be compiled with grunt:

	grunt

## Managing InspireVive

### Administration Panel

The administration panel can be found at [example.com/admin](http://example.com/admin). There is also a link at the top of the page when logged in as an admin user. Admin users are members of the `admin` group (in the `GroupMembers` table).

### Tasks

#### Creating an organization

1. Sign in with an admin user
2. Click the green **Administration Dashboard** bar at the top of the page
3. Click the **Organizations** tab
4. Cilck **+ Organization** to open the new organization screen
5. Fill in the **Name**, **Email**, and **Address** fields. Note: the email address an be the general contact for the organization and not necessarily a specific person. Right now, this field is only for our records. Also, the address can be multiple lines by pressing ENTER.
6. Click the **Create** button at the bottom
7. You will be taken back to the listing of organizations. Search for the name of the organization you just created. After you find the correct organization, take note of the ID number (ie. **# 18**)
8. Click the **Volunteers** tab (not the one in the black bar but below that)
9. Click **+ Volunteer**
10. Set the **Uid** to your user ID, the **Organization** to the ID of the organization you created earlier, the **Role** to `admin`, and check the **Application Shared** field
11. Click the **Create** button at the bottom
12. Click the home icon in the top right corner to go back to InspireVive
13. In the profile you page you should see the organization you just created listed. Click the **Manage** link below its name. You will be taken to the management interface
14. Click the **Add Volunteers** button
15. Type in the email address of the volunteer coordinator(s) you wish to invite.
16. Click the **Add Volunteers** button at the bottom. They will then receive an invite email prompting them to create an InspireVive account if they have not already and inviting them as a volunteer of the organization
17. You will be taken to the **Volunteers** page. For each of the volunteers that you just invited, click the **Details** button next to their email/name and then click **Promote to Volunteer Coordinator**
18. Now each of the volunteer coordinators will have the new organization listed in their profile when they sign in to InspireVive along with the **Manage** link

#### Deleting a User Account

1. Sign in with an admin user
2. Click the green **Administration Dashboard** bar at the top of the page
3. Go to the **Users** section
4. Search for the user you are looking for. Once you find them click the red **X** button.
5. Confirm you want to delete the user (since this is a permanent action) and you are done.

## Contributing

Please feel free to contribute by participating in the issues or by submitting a pull request. :-)

### Tests

The included tests can be ran with:

	phpunit

## License

Copyright (c) 2015 Jared King

InspireVive is licensed under the GNU GPL v3 license in the `LICENSE` file. The InspireVive brand and logo are copyrights of InspireVive.