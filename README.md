Camelot Image Asset
===================

Installation
------------

### Applications that use Symfony Flex

Open a command console, enter your project directory and execute:

```console
$ composer require camelot/image-asset
```

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require camelot/image-asset
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Camelot\ImageAsset\Bridge\Symfony\CamelotImageAssetBundle::class => ['all' => true],
];
```

Configuration
-------------

### Default configuration

```yaml
# config/packages/camelot_image_asset.yaml
camelot_image_asset:
    image_dirs:
        - '%kernel.project_dir%/public/images'
    static_path: '%kernel.project_dir%/public/thumbs'
    routing:
        mount_point: /thumbs
        image:
            controller: Camelot\ImageAsset\Controller\ImageController
            path: '/{width}x{height}/{action}/{file}'
        image_alias:
            controller: Camelot\ImageAsset\Controller\ImageAliasController
            path: '/{alias}/{file}'
    default_image:
        path: image-default.png
        filesystem: camelot.image.filesystem.bundle
    default_image_size:
        width: 1024
        height: 768
    error_image:
        path: image-error.png
        filesystem: camelot.image.filesystem.bundle
    cache_time: null
    limit_upscaling: true
    only_aliases: false
    aliases: ~
```

### Aliases

```yaml
# config/packages/camelot_image_asset.yaml
camelot_image_asset:
    # ...

    aliases:
        my_alias:
            image_size:
                width: 1024
                height: 768
            action: ~ # One of "border"; "crop"; "fit"; "resize"
        other_alias:
            image_size:
                width:  1900
                height: 1200
            action: ~ # One of "border"; "crop"; "fit"; "resize"
```

### Routing

```yaml
# config/routes/camelot_image_asset.yaml
camelot_image_asset:
    resource: .
    type: image_asset
    prefix: /
```

Usage
-----

### Twig

```twig
    {% set uri = '/images/image.png' %}

    {# This will be cropped to the default width & height #}
    <img src="{{ thumbnail(uri, my_alias) }}">

    {# Same, but path resolved by Symfony's asset() Twig function #}
    {% set package_name = 'my_symfony_asset_package_name' %} # See config/packages/assets.yaml
    <img src="{{ thumbnail(asset(uri, package_name), my_alias) }}">
```

NGINX Optimisation
------------------

```nginx
location ~* /thumbs/(.*)$ {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~* ^.+\.(?:gif|jpe?g|jpeg|jpg|png|svg|svgz)$ {
    access_log          off;
    log_not_found       off;
    expires             max;
    add_header          Access-Control-Allow-Origin "*";
    add_header          Cache-Control "public, mustrevalidate, proxy-revalidate";
    add_header          Pragma public;
}
```
