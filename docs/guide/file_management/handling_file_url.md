# File URL handling

### IO URL decoration

<<<<<<< HEAD
By default, images and binary files that are referenced by the content is served from the same server as the application, for example `/var/site/storage/images/3/6/4/6/6463-1-eng-GB/kidding.png`.
=======
By default, images and binary files that are referenced by the content is served from the same server as the application, for example `/var/site/storage/images/3/6/4/6/6463-1-eng-GB/kidding.png`.
>>>>>>> cbb79188... Updates on handling.md
This is the default semantic configuration:

``` yaml
ibexa:
    system:
        default:
            io:
                url_prefix: '$var_dir$/$storage_dir$'
```

<<<<<<< HEAD
`$var_dir$` and `$storage_dir$` are dynamic, [SiteAccess-aware settings](../configuration.md#dynamic-settings-injection), and is replaced by their values in the execution context.
=======
`$var_dir$` and `$storage_dir$` are dynamic, [SiteAccess-aware settings](../configuration.md#dynamic-settings-injection), and is replaced by their values in the execution context.
>>>>>>> cbb79188... Updates on handling.md

## Using a static server for images

One common use case is to use an optimized nginx to serve images in an optimized way. The example image
above could be made available as `http://static.example.com/var/site/storage/images/3/6/4/6/6463-1-eng-GB/kidding.png`
by setting up a separate server that maps the `/path/to/ibexa/public/var` directory.
The configuration would be as follows:

``` yaml
ibexa:
    system:
        default:
            io:
                url_prefix: 'http://static.example.com/$var_dir$/$storage_dir$'
```

!!! caution

    For security reasons, do not map `/path/to/ibexa/public/` as
    Document Root of the static server.
    Map the `/var/` directory directly to `/path/to/ibexa/public/var` instead.

## `io.url_prefix`

Any `BinaryFile` returned by the public API is prefixed with the value of this setting, internally stored as `ibexa.site_access.config.scope.io.url_prefix`.

### `io.url_prefix` dynamic service container setting

Default value: `$var_dir$/$storage_dir$`
Example: `/var/site/storage`

<<<<<<< HEAD
Used to configure the default URL decorator service (`ibexa.core.io.default_url_decorator`), used by all binary data handlers to generate the URI of loaded files. It is always interpreted as an absolute URI, meaning that unless it contains a scheme (`http://`, `ftp://`), is prepended with a `/`.
=======
Used to configure the default URL decorator service (`ibexa.core.io.default_url_decorator`), used by all binary data handlers to generate the URI of loaded files. It is always interpreted as an absolute URI, meaning that unless it contains a scheme (`http://`, `ftp://`), is prepended with a `/`.
>>>>>>> cbb79188... Updates on handling.md

This setting is SiteAccess-aware.

### Services

#### URL decorators

<<<<<<< HEAD
A UrlDecorator decorates and undecorates a specified string (URL). It has two mirror methods: `decorate` and `undecorate`.

Two implementations are provided: `Prefix`, and `AbsolutePrefix`. They both add a prefix to a URL, but `AbsolutePrefix` ensures that unless the prefix is an external URL, the result is prepended with `/`.
=======
A UrlDecorator decorates and undecorates a specified string (URL). It has two mirror methods: `decorate` and `undecorate`.

Two implementations are provided: `Prefix`, and `AbsolutePrefix`. They both add a prefix to a URL, but `AbsolutePrefix` ensures that unless the prefix is an external URL, the result is prepended with `/`.
>>>>>>> cbb79188... Updates on handling.md

Three UrlDecorator services are introduced:

- `Ibexa\Core\IO\UrlDecorator\AbsolutePrefix` used by the binary data handlers to decorate all URIs sent out by the API. Uses `AbsolutePrefix`.
<<<<<<< HEAD
- `Ibexa\Core\IO\UrlDecorator\Prefix` used through the UrlRedecorator by various legacy elements (Converter, Storage Gateway, etc.) to generate its internal storage format for URIs. Uses a `Prefix`, not an `AbsolutePrefix`, meaning that no leading `/` is added.

In addition, a UrlRedecorator service, `Ibexa\Core\IO\UrlDecorator\Prefix`, uses both previously mentioned decorators to convert URIs between what is used on the new stack, and what format legacy expects (relative urls from the ezpublish root).
=======
- `Ibexa\Core\IO\UrlDecorator\Prefix` used through the UrlRedecorator by various legacy elements (Converter, Storage Gateway, etc.) to generate its internal storage format for URIs. Uses a `Prefix`, not an `AbsolutePrefix`, meaning that no leading `/` is added.

In addition, a UrlRedecorator service, `Ibexa\Core\IO\UrlDecorator\Prefix`, uses both previously mentioned decorators to convert URIs between what is used on the new stack, and what format legacy expects (relative urls from the ezpublish root).
>>>>>>> cbb79188... Updates on handling.md