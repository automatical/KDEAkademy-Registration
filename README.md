# KDE Event Registration

A simple web application to help organise and manage the KDE Annual Conference: Akademy. There is support for multiple events as KDE organises other conferences and sptrints throughout the year.

For each event, we normally require the same profile information from attendees, followed by a set of questions that may change from event to event. To aid this, a form definition is saved with a conference definition within the events table.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites

The decision was taken to built this application on a simple LAMP stack, making it easier to contribute features and bug fixes.

 * PHP Composer - https://getcomposer.org/
 * Bower - https://bower.io/
 * MySQL - https://www.mysql.com/
 * LDAP (For KDE Identity Integration)

To get up and running:

 * Configure a baseline LDAP server. An (unmaintained) example of this can be found here: https://github.com/GeekSoc/gas-vagrant
 * Create a database using the information in resources/schema.sql
 * Create a single conference using resources/conference.sql
 * Copy app/settings.php.dist to app/settings.php and configure appropriately.
 * Run the application: `php -S 0.0.0.0:8000 -t public`
 * Open a browser and start building: `open http://localhost:8000`
