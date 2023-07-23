<h1 align="center">SuluArticleBundle</h1>

## Requirements

* PHP 8.0
* Sulu ^2.5.*
* Symfony ^5.0 || ^6.0

## Features
* List view of Article
* Routing
* Preview
* SULU Media include
* Content Blocks (Title,Editor,Image,Quote)
* Activity Log
* SEO


## Installation

### Install the bundle 

Execute the following [composer](https://getcomposer.org/) command to add the bundle to the dependencies of your 
project:

```bash

composer require pawwwloff/sulu-article-bundle --with-all-dependencies

```

## Installation

### Install the bundle

Execute the following [composer](https://getcomposer.org/) command to add the bundle to the dependencies of your
project:

```bash

composer require thecadien/sulu-news-bundle

```

### Enable the bundle

Enable the bundle by adding it to the list of registered bundles in the `config/bundles.php` file of your project:

 ```php
 return [
     /* ... */
     Pawwwloff\Bundle\SuluArticleBundle\PawwwloffArticleBundle::class => ['all' => true],
 ];
 ```

### Update schema
```shell script
bin/console doctrine:schema:update --force
```

## Bundle Config

Define the Admin Api Route in `routes_admin.yaml`
```yaml
sulu_pawwwloff_article.admin:
  type: rest
  resource: sulu_pawwwloff_article.rest.controller
  prefix: /admin/api
  name_prefix: app.
```

## Role Permissions
If this bundle is being added to a previous Sulu installation, you will need to manually add the permissions to your admin user role(s) under the `Settings > User roles` menu option.

## Template
After the installation, a news [Template](template.md) must be set up for the frontend.
