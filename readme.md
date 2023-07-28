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

Define the Api Route in `routes_website.yaml`
```yaml
sulu_pawwwloff_article.api:
  type: rest
  resource: sulu_pawwwloff_article.api.controller
  prefix: /api
  name_prefix: pawww.
```

## Role Permissions
If this bundle is being added to a previous Sulu installation, you will need to manually add the permissions to your admin user role(s) under the `Settings > User roles` menu option.

## Article Template

### Template.xml

To link the article in the frontend there are two ways of integration, Smart Content and the article Selection.

### Smart Content
The smart content type is used to display a list of article items it loads the latest published article items by default.

The smart content can be configured in every `template.xml` file.

 ```xml
        <property name="article" type="smart_content">
            <meta>
                <title lang="en">Articles</title>
            </meta>

            <params>
                <param name="provider" value="pawwwloff_articles"/>
                <param name="max_per_page" value="5"/>
                <param name="page_parameter" value="p"/>
            </params>
        </property>

```

Follow the Official [Smart Content Documentation](https://docs.sulu.io/en/latest/cookbook/smart-content.html) to learn more about the smart content.

### Article Selection
The article selection is used to display a specific list of article items.

The smart content can be configured in every `template.xml` file.

 ```xml
        <property name="article" type="pawwwloff_article_selection">
            <meta>
                <title lang="en">Articles</title>
            </meta>
        </property>
```

### Twig Template

If the bundles default controller is used, a template must be created in `pawwwloff/article/index.html.twig`.

This is an example template, covering all currently available content block types in one article item.

 ```twig
{% block content %}
    {% set header = sulu_resolve_media(article.header.id, 'de') %}
    <img src="{{ header.thumbnails['600x'] }}" alt="{{ header.title }}" title="{{ header.title }}" />
    <h1>{{ article.title }}</h1>


    <p>{{ article.teaser }}</p>

    {% for contentItem in article.content  %}
        {% if contentItem.type == 'editor'  %}
            {{ contentItem.text | raw }}
        {% elseif contentItem.type == 'title'  %}
            <{{ contentItem.titleType }}>{{ contentItem.title }}</{{ contentItem.titleType }}>
        {% elseif contentItem.type == 'image'  %}
            {% set img = sulu_resolve_media(contentItem.image.id, 'de') %}
            <img src="{{ img.thumbnails['600x'] }}" alt="" />
        {% elseif contentItem.type == 'quote'  %}
            <figure>
                <blockquote><p>{{ contentItem.quote }}</p></blockquote>
                <figcaption>{{ contentItem.quoteReference }}</figcaption>
            </figure>
        {% endif %}
    {% endfor %}
{% endblock %}
 ```

## Twig-Extensions

### sulu_resolve_pawwwloff_article

Returns article for given id.

 ```php
{% set article = sulu_resolve_pawwwloff_article('1') %}
{{ article.title }}
 ```

Arguments:

    id: int - The id of requested article.

Returns:

    object - Object with all needed properties, like title

#### Filters
```php
By tags /path?tags=1,2,3
By categories /path?categories=1,2,3
```

Returns active articles tags.

 ```php
{% set tags = get_pawwwloff_articles_tags() %}
 ```

Returns active articles categories.

 ```php
{% set categories = get_pawwwloff_articles_categories() %}
 ```

