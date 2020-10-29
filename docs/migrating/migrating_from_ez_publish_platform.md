# Migrating from eZ Publish Platform

Migration overview:
- [eZ Publish 4.x to eZ Publish Platform 5.x](migrating_from_ez_publish.md)
- eZ Publish Platform 5.4/2014.11 to 2.5 _(this page)_
- [eZ Platform 2.5 to 3.x](../updating/upgrading_to_v3.md)

## Differences between eZ Publish Platform (5.x) and eZ Platform

eZ Publish Platform (5.x) was a transitional version of the eZ CMS, bridging the gap between the earlier generation called eZ Publish (sometimes referred to as *legacy*), and the current eZ Platform and eZ Platform Enterprise Edition for developers.

eZ Platform on the other hand contains a newer, far more mature version of the Symfony-based technology stack first introduced in eZ Publish Platform (5.x). And while it no longer bundles [Legacy bridge](legacy_bridge.md) to connect with eZ Publish, this still possible and supported with newer legacy packages.

## Upgrade process

An upgrade from eZ Publish Platform 5.4.x (Enterprise edition) or 2014.11 (Community edition) to newer versions of eZ Platform must be performed in stages.

This page will take you true the following steps:
- Setup clean eZ Platform 2.5 install using the latest available version
- Move over your project specific code, config and packages, adapt for changes in newer versions.
- _Optional; Add legacy bridge to continue to run legacy for certain parts and be able to gradual perform the migrations below later_
- Migrate eZ Flow to eZ Landing page _(1.x)_, and eZ Landing page to eZ Page Builder _(2.2 and higher)_
- Migrate XmlText content to RichText
- Once these steps are done; _All legacy, migration, older fieldtypes (ezflow, landingpage and ezxmltext) packages can be uninstalled._

!!! caution "Things to be aware of when planning a migration"

    1. While the instructions below are fully supported, we are aware that the community, partners and customers come from a wide range of different versions of eZ Publish, some with issues that do not surface before attempting a migration. That's why we and the community are actively gathering feedback on Slack and/or support channels for Enterprise customers to gradually improve the migration scripts and instructions.

        - A good tip is to test out the content migration scripts early in the process to look for possible warnings given by them, and report these if it is considered as bugs in the migration scripts.

    1. "Hybrid setup" using Legacy Bridge is a supported option for 1.13LTS and 2.5LTS series. This means you can plan for a more gradual migration if you want, just like you could on eZ Publish Platform 5.x, with a more feature-mature version of eZ Platform, Symfony and even Legacy bridge itself. This is a great option for those who want acces to some of the latest features. The downside is a more complex setup to develop and maintian, given you continue run two systems in paralell, and the overall migration might take much longer when using an iterative/gradual approach.

    1. Additionally there are some other topics to be aware of for the code migration from eZ Publish to eZ Platform:

        - Symfony deprecations. The recommended version to migrate to is eZ Platform v2.5 LTS, which is using Symfony 3.4 LTS.
        - [Field Types reference](../api/field_type_reference.md) for overview of Field Types that do and don't exist in eZ Platform
        - API changes. While we have a strict backwards compatibility focus, some deprecated API features were removed and some changes were done to internal parts of the system. See [ezpublish-kernel:doc/bc/changes-6.0.md](https://github.com/ezsystems/ezpublish-kernel/blob/7.5/doc/bc/changes-6.0.md)

!!! caution "Take a Backup, and only perform on offline install"

    The migration script will operate on your current database, so only performe the upgrade on an offline installation.
    Make sure you have a working [backup](../guide/backup.md) of the site before you do the actual upgrade, in case of an unexpected error.


### Note on Paths

- `<old-ez-root>/`: The root directory where the 5.4/2014.11 installation is located in, for example: `/home/myuser/old_www/` or `/var/sites/ezp/`.
- `<new-ez-root>/`: The root directory where the installation is located in, for example: `/home/myuser/new_www/` or `/var/sites/[ezplatform|ezplatform-ee]/`.

## Check for requirements

- Information regarding system requirements can be found on the [Requirements documentation page](../getting_started/requirements.md); notable changes include:
    - PHP 7.1 or higher
    - MariaDB 10.0+ or MySQL 5.7+ or PostgreSQL 10+
    - Browser from 2018 or newer for use with eZ Platform Admin UI
- This page assumes you have composer installed on the machine and that it is a recent version.

## Upgrade steps

### Step 1: Extract latest eZ Platform/Enterprise v2.5.x

The easiest way to upgrade the distribution files is to extract a clean installation of eZ Platform / eZ Enterprise to a separate directory.

### Step 2: Move over code and config

##### 2.1. Code

If you have code in src folder, move that over:

`<old-ez-root>/src =>  <new-ez-root>/src`


!!! tip "Automate refactoring of your code for new Symfony and PHP versions"

    For using [Rector](https://github.com/rectorphp/rector), here is an example config for upgrading to PHP 7.1 and Symfony 3.4:
    ```yaml
    parameters:
        auto_import_names: true
        import_short_classes: false
        import_doc_blocks: true
        paths:
            - 'src'
        sets:
          - 'php70'
          - 'php71'
          - 'symfony30'
          - 'symfony31'
          - 'symfony32'
          - 'symfony33'
          - 'symfony34'
          - 'code-quality'
    # For more sets, see: https://github.com/rectorphp/rector/tree/master/config/set/
    ```

    Keep in mind that after finishing automatic refactoring there might be some code chunks that you need to fix manually.

##### 2.2. Composer

###### 2.2.1 Move over own packages

Assuming you have own composer packages *(libraries and bundles, but not eZ Publish legacy packages)*, execute commands like below to add them to new install in `<new-ez-root>`:

`composer require --no-update "vendor/package:~1.3.0"`

Adapt the command with your `vendor`, `package`, version number, and add `"–dev"` if a given package is for dev use only. Also check if there are other changes in `composer.json` you should move over.

!!! note

    For your own and thirdparty packages, make sure to pick  versions that work with eZ Platform 2.5 and Symfony 3.x.

###### 2.2.2 Install XmlText Field Type

While no longer bundled, the XmlText Field Type still exists and is needed to perform a migration from eZ Publish's XmlText to the new docbook-based format used by the RichText Field Type. If you plan to use Legacy Bridge for a while before migrating content, you'll also need this for rendering content with XMLText. From `<new-ez-root>` execute:

`composer require --no-update "ezsystems/ezplatform-xmltext-fieldtype:^1.9.2"`

!!! note

    Be aware this XmlText Field Type in eZ Platform uses the Content View system introduced in eZ Platform v1.0, so make sure you adapt custom templates and override rules if you plan to use this for rendering xmltext content _(in Legacy Bridge setup)_.


##### 2.3. Config

To move over your own custom configurations, follow the conventions below and manually move the settings over:

- `<old-ez-root>/ezpublish/config/parameters.yml => <new-ez-root>/app/config/parameters.yml`
    -  *For parameters like before, for new parameters you'll be prompted on later step.*
- `<old-ez-root>/ezpublish/config/config.yml =>  <new-ez-root>/app/config/config.yml`
    -  *For system/framework config, and for defining global db, cache, search settings.*
- `<old-ez-root>/ezpublish/config/ezpublish.yml => <new-ez-root>/app/config/ezplatform.yml`
    -  *For SiteAccess, site groups and repository settings.*

!!! note "Changes to repository configuration"

    When moving configuration over, be aware that as of 5.4.5 and higher, repository configuration has been enhanced to allow configuring storage engine and search engine independently, and this is now the norm in eZ Platform.

    ``` yaml
    # Default ezplatform.yml repositories configuration with comments
    ezpublish:
        # Repositories configuration, set up default repository to support solr if enabled
        repositories:
            default:
                # For storage engine use kernel default (current LegacyStorageEngine)
                storage: ~
                # For search engine, pick the one configured in parameters.yml, either "legacy" or "solr"
                search:
                    engine: '%search_engine%'
                    connection: default
    ```

!!! note "Make sure to adapt SiteAccess names"

    In the default configurations in **ezplatform.yml** you'll find existing SiteAccesses like `site`, and depending on installation perhaps a few others, all under a site group called `site\_group`. Make sure to change those to what you had in **ezpublish.yml** to avoid issues with having to log in to your website, given user/login policy rules will need to be updated if you change names of SiteAccess as part of the upgrade.

###### 2.3.1 Image aliases

Image aliases defined in legacy must also be defined for eZ Platform. Since image aliases in legacy may be scattered around
in different `image.ini` files in various extensions, you may find it easier to find all image alias definitions using
the legacy admin (**Setup** > **Ini settings**).

See [Image documentation page](../../guide/images/) for information about how to define image aliases.

For an example, see a legacy image alias defined as follows in `ezpublish_legacy/settings/siteaccess/ezdemo_site/image.ini.append.php`:

```
[articleimage]
Reference=
Filters[]
Filters[]=geometry/scalewidth=770

[articlethumbnail]
Reference=
Filters[]
Filters[]=geometry/scaledownonly=170;220
```

The corresponding image alias configuration for eZ Platform would be:

``` yaml
ezpublish:
    siteaccess:
        groups:
            # Define the siteaccesses where given image aliases are in use
            image_aliases_group: [ezdemo_site, eng, ezdemo_site_admin, admin]
    system:
        image_aliases_group:
            image_variations:
                articleimage:
                    reference: null
                    filters:
                        - { name: geometry/scalewidth, params: [770] }
                articlethumbnail:
                    reference: null
                    filters:
                        - { name: geometry/scaledownonly, params: [170, 220] }
```

##### 2.4. Bundles

Move over registration of _your_ bundles you have from src and from composer packages, from old to new kernel:

`<old-ez-root>/ezpublish/EzPublishKernel.php => <new-ez-root>/app/AppKernel.php`


##### 2.5. Optional: Install Legacy Bridge

If you don't plan to migrate content _(ezxmltext and ezflow)_ directly to newer eZ Platform Field Types, you can optionally install [Legacy bridge](legacy_bridge.md) and gradually handle code and subsequent content migration afterwards. For installation instructions see [here](https://github.com/ezsystems/LegacyBridge/blob/v2.1.5/INSTALL.md).

!!! note

    The Legacy Bridge integration does not have the same performance, scalability or integrated experience as a pure Platform setup. Like on eZ Publish Platform 5.x there are known edge cases where, for instance, cache or search index won't always be immediately updated across the two systems using the bridge. This is one of the many reasons why we recommend a pure Platform setup where that is possible.

###### 2.5.1 Manually set up symlinks for legacy storage folder

As eZ Publish legacy is installed via composer, we need to take care of placing some files outside its generated `<new-ez-root>/ezpublish_legacy/` folder, and for instance use symlink to place them inside during installation.

A lot of folders where automatically setup for you in step above, however one was notably not:
- `<new-ez-root>/ezpublish_legacy/var/[<site>/]storage`: This is typically not versioned in git, so there's no predefined convention for this. But to not loose it during composer update of `ezpublish-legacy`, you should strongly consider automaticaly symlinking this from a folder outside the project directory, and make sure to mark this folder as ignored by git once it and a corresponding `.keep` file have been added to your checkout. The example below will assume `<new-ez-root>/src/legacy_files/storage` was created for this purpose, if you opt for something else, just adjust the instructions.

###### 2.5.2 Upgrade the legacy distribution files

The easiest way to upgrade the distribution files is to copy the directories that contain site-specific files from the existing 5.4 installation into `/<ezplatform>/ezpublish_legacy`. Make sure you copy the following directories:

- Custom designs if any: `<old-ez-root>/ezpublish_legacy/design/<your_designs>` => `<new-ez-root>/src/legacy_files/design/<your_designs>`
    - *Do NOT include built-in designs: admin, base, standard or admin2*
- SiteAccess settings: `<old-ez-root>/ezpublish_legacy/settings/siteaccess/<your_siteaccesses>` => `<new-ez-root>/src/legacy_files/settings/siteaccess/<your_siteaccesses>`
- Override settings: `<old-ez-root>/ezpublish_legacy/settings/override/*` => `<new-ez-root>/src/legacy_files/settings/override/*`
- Custom extensions: `<old-ez-root>/ezpublish_legacy/extension/*` => `<new-ez-root>/src/AppBundle/ezpublish_legacy/` *(do NOT include the built-in / composer provided ones, like: ezflow, ezjscore, ezoe, ezodf, ezie, ezmultiupload, ezmbpaex, ez_network, ezprestapiprovider, ezscriptmonitor, ezsi, ezfind)*
- Other folders to move over *(or potentially set up symlinks for)* if applicable:
    - ezpublish_legacy/var/storage/packages
    - `ezpublish_legacy/config.php` and `ezpublish_legacy/config.cluster.php`

#####  2.6 Binary files

Binary files can simply be copied from the old to the new installation:

`<old-ez-root>/web/var[/<site_name>]/storage => <new-ez-root>/web/var[/<site_name>]/storage`

!!! note

    In the eZ Publish Platform 5.x installation `web/var` is a symlink to `ezpublish_legacy/var`, so if you can't find it in path above you can instead copy the storage files to the similar `ezpublish_legacy/var[/<site_name>]/storage` path.

#####  2.7 Re-apply permissions and update composer

Since writable directories and files have been replaced / copied, their permissions might have changed. You need to re-apply them.

When that is done, execute the following to update and install all packages from within `<new-ez-root>`:

`composer update --prefer-dist`

!!! note

    At the end of the process, you will be asked for values for parameters.yml not already moved from old installation, or new *(as defined in parameters.yml.dist)*.

##### 2.8 Register EzSystemsEzPlatformXmlTextFieldTypeBundle

Add the following new bundle to your new kernel file, `<new-ez-root>/app/AppKernel.php`:

`new EzSystems\EzPlatformXmlTextFieldTypeBundle\EzSystemsEzPlatformXmlTextFieldTypeBundle(),` 

### Step 3: Upgrade the database

##### 3.1. Execute update SQL

Import to your database the changes provided in one of the following files. It's also recommended reading inline comments as you might not need to run some queries *(if you for instance upgrade from 5.4.11)*:

Postgres:
- `<new-ez-root>/vendor/ezsystems/ezpublish-kernel/data/update/postgres/dbupdate-5.4.0-to-6.13.0.sql`
- `<new-ez-root>/vendor/ezsystems/ezpublish-kernel/data/update/postgres/dbupdate-6.13.0-to-7.5.0.sql`

MySQL/MariaDB:
- `<new-ez-root>/vendor/ezsystems/ezpublish-kernel/data/update/mysql/dbupdate-5.4.0-to-6.13.0.sql`
- `<new-ez-root>/vendor/ezsystems/ezpublish-kernel/data/update/mysql/dbupdate-6.13.0-to-7.5.0.sql`


!!! note  "Change to UTF8mb4 for MySQL/MariaDB"

    Since v2.2 the character set for MySQL/MariaDB database tables changed from `utf8` to `utf8mb4` to support 4-byte characters.

    Update database script above takes care about shortening indexes to make space for the longer strings, however you'll also need to:
    - Change character set and collate
    - Change Doctrine DBAL connection setting

    You can find instructions for that as part of [7.2 upgrade guide](https://github.com/ezsystems/ezpublish-kernel/blob/7.5/doc/upgrade/7.2.md#mysqlmariadb-database-tables-character-set-change) provided with kernel package.

##### 3.2. Once you are ready to migrate content to Platform Field Types

Steps here should only be done once you are ready to move away from legacy and Legacy Bridge, as the following Field Types are not supported by legacy. In other words, content you have migrated will not be editable in legacy admin interface anymore, but rather in the more modern eZ Platform back-end UI, allowing you to take full advantage of what the Platform has to offer.

###### 3.2.1 Migrate XmlText content to RichText

You should test the XmlText to RichText conversion before you apply it to a production database. RichText has a stricter validation compared to XmlText and you may have to fix some of your XmlText before you are able to convert it to RichText.
Run the conversion script on a copy of your production database as the script is rather resource-intensive.

`php -d memory_limit=1536M bin/console ezxmltext:convert-to-richtext --dry-run --export-dir=ezxmltext-export --export-dir-filter=notice,warning,error --concurrency 4 -v`

- `-d memory_limit=1536M` specifies that each conversion process gets 1536MB of memory. This should be more than sufficient for most databases. If you have small `ezxmltext` documents, you may decrease the limit. If you have huge `ezxmltext` documents, you may need to increase it. See PHP documentation for more information about the [memory_limit setting](http://php.net/manual/en/ini.core.php#ini.memory-limit).
- `--dry-run` prevents the conversion script from writing anything back to the database. It just tests if it is able to convert all the `ezxmltext` documents.
- `--export-dir` specifies a directory where it will dump the `ezxmltext` for content object attributes which the conversion script finds problems with
- `--export-dir-filter` specifies what severity the problems found needs to be before the script dumps the `ezxmltext`:
    - `notice`: `ezxmltext` contains problems which the conversion tool was able to fix automatically and likely do not need manual intervention
    - `warning`: the conversion tool was able to convert the `ezxmltext` to valid `richtext`, but data could have been altered/removed/added in the process. Manual supervision is strongly recommended.
    - `error`: the `ezxmltext` text cannot be converted and manual changes are required.
- `--concurrency 4` specifies that the conversion script will spawn four child processes which run the conversion. If you have dedicated hardware for running the conversion, you should use concurrency level that matches the number of logical CPUs on your system. If your system needs to do other tasks while running the conversion, you should run with a smaller number.
- `-v` specifies verbosity level. You may increase the verbosity level by supplying `-vv`, but `-v` will be sufficient in most cases.

The script also has an `--image-content-types` option which you should use if you have custom image classes. With this option, you specify the content class identifiers:

`php bin/console ezxmltext:convert-to-richtext --image-content-types=image,custom_image -v`

The script needs to know these identifiers in order to convert `<ezembed>` tags correctly. Failing to do so will prevent the editor from showing image thumbnails of embedded image objects. You may find the image Content Types in your installation by looking for these settings in `content.ini(.append.php)`:

```
[RelationGroupSettings]
ImagesClassList[]
ImagesClassList[]=image
```

If the `--image-content-types` option is not specified, the default setting `image` will be used.

!!! note

    There is no corresponding `ImagesClassList[]` setting in eZ Platform, images is rather an editorial choice. So even though you have custom image classes, you don't need to configure this in the eZ Platform configuration when migrating.


**Fixing wrong image type afterwards**

If you later realize that you provided the convert script with incorrect image Content Type identifiers, it is perfectly safe to re-execute the command as long as you use the `--fix-embedded-images-only`.

So, if you first ran the command:

`php bin/console ezxmltext:convert-to-richtext --image-content-types=image,custom_image -v`

But later realize the last identifier should be `profile`, not ``custom_image``, you may execute :

`php bin/console ezxmltext:convert-to-richtext --image-content-types=image,profile --fix-embedded-images-only -v`

The last command would then ensure embedded objects with Content Type identifier `custom_image` are no longer tagged as images, while embedded objects with Content Type identifier `profile` and `image` are.


**Example on how to deal with conversion warnings and errors**

Using the option `--export-dir`, the conversion will export problematic `ezxmltext` to files with the name pattern `[export-dir]/ezxmltext_[contentobject_id]_[contentobject_attribute_id]_[version]_[language].xml`. A corresponding `.log` file will also be created which includes information about why the conversion failed. Be aware that the reported location of the problem may not be accurate or may be misleading.

Below is an example of a xml dump, `ezxmltext_12_1234_2_eng-GB.xml`:

```xml
<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/">
  <paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/" ez-temporary="1">
    <table border="1">
      <tr>
        <td>
          <paragraph>col1</paragraph>
        </td>
        <td align="right">
          <paragraph>col2</paragraph>
        </td>
        <td align="middle" xhtml:width="73">
          <paragraph>col3</paragraph>
        </td>
        <td align="center" xhtml:width="73">
          <paragraph>col4</paragraph>
        </td>
      </tr>
    </table>
  </paragraph>
</section>
```

The corresponding log file, `ezxmltext_12_1234_2_eng-GB.log`:

```
notice: Found ez-temporary attribute in a ezxmltext paragraphs. Removing such attribute where contentobject_attribute.id=1234
error: Validation errors when converting ezxmltext for contentobject_attribute.id=1234
- context : Error in 2:0: Element section has extra content: informaltable
```

The first log message is a notice about the `ez-temporary=1` attribute which the conversion tool simply will remove during conversion.
The second log message is an error, but the cause described may be confusing. During the conversion, the `<table>` element will be converted to an `<informaltable>` tag, which is problematic.
The exact problem in this case is the value of the second align attribute: `<td align="middle"....>`. An align attribute may only have the following values: `left`, `right`, `center`, `justify`.

In order to fix the problem, open the .xml file in a text editor and correct the errors:

```xml
<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/">
  <paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">
    <table border="1">
      <tr>
        <td>
          <paragraph>col1</paragraph>
        </td>
        <td align="right">
          <paragraph>col2</paragraph>
        </td>
        <td align="center" xhtml:width="73">
          <paragraph>col3</paragraph>
        </td>
        <td align="center" xhtml:width="73">
          <paragraph>col4</paragraph>
        </td>
      </tr>
    </table>
  </paragraph>
</section>
```

Now, you may test if the modified `ezxmltext` may be converted using the `--dry-run` and `--content-object` options:

`php -d memory_limit=1536M bin/console ezxmltext:import-xml --dry-run  --export-dir=ezxmltext-export --content-object=56554 -v`

If the tool reports no errors, then the `ezxmltext` is fixed. You may rerun the command without the `--dry-run` option in order to actually update the database with the correct XmlText.

Once you have fixed all the dump files in `ezxmltext-export/`, you may skip the `--content-object` option and the script will import all the dump files located in the `export-dir`:

`php -d memory_limit=1536M bin/console ezxmltext:import-xml --export-dir=ezxmltext-export -v`

Other typical problems that needs manual fixing:

**Duplicate xhtml IDs**

Xhtml IDs needs to be unique. The following `ezxmltext` will result in a warning:

```
    <paragraph>
        <link target="_blank" xhtml:id="inv5" url_id="2309">link with id inv5</link>
    </paragraph>
    <paragraph>
        <link target="_blank" xhtml:id="inv5" url_id="2309">another link with id inv5</link>
    </paragraph>
```

The conversion tool will replace the duplicate id (`inv5`) with a random value. If you need the ID value to match your CSS, you need to change it manually.
The conversion tool will also complain about IDs which contain invalid characters.

**Links with non-existing `object_remote_id` or `node_remote_id`.**

In `ezxmltext` you may have links which refer to other objects by their remote ID. This is not supported in `richtext`, so the conversion tool must look up such remote IDs and replace them with the `object_id` or `node_id`. If the conversion tool cannot find the object by its remote id, it will issue a warning about it.

In older eZ Publish databases you may also have invalid links due to lack of reference to a target (no `href`, `url_id`, etc.):

```
    <link>some text</link>
```

When the conversion tool detects links with no reference it will issue a warning and rewrite the URL to point to current page (`href="#"`).

**Custom tags and attributes**

eZ Platform 2 supports custom tags, including inline custom tags, and limited use of custom tag attributes.
After migrating to RichText, you need to adapt your custom tag config for eZ Platform and rewrite the custom tags in Twig.
See [Custom tag documentation](../guide/extending/extending_online_editor.md#custom-tags) for more info.

If you configured custom attributes in legacy in OE using [ezoe_attributes.ini](https://github.com/ezsystems/ezpublish-legacy/blob/master/extension/ezoe/settings/ezoe_attributes.ini#L33-L48), note that not all types are supported.

Below is a table of the tags that are currently supported, and their corresponding names in eZ Platform.

| [XmlText](https://github.com/ezsystems/ezpublish-legacy/blob/2019.03/extension/ezoe/settings/ezoe_attributes.ini#L33-L48) | [RichText](https://github.com/ezsystems/ezplatform-richtext/blob/v1.1.5/src/bundle/DependencyInjection/Configuration.php#L17) | Note  |
| ------------- | ------------- | ----- |
| `link`        | [`link`](../guide/extending/extending_online_editor.md#example-link-tag) |  |
| `number`      | `number`      |  |
| `int`         | `number`      |  |
| `checkbox`    | `boolean`     |  |
| `select`      | `choice`      |  |
| `text`        | `string`      |  |
| `textarea`    | Not supported |   Use `string` as workaround |
| `email`       | Not supported |   Use `string` as workaround |
| `hidden`      | Not supported |   Use `string` as workaround |
| `color`       | Not supported |   Use `string` as workaround |
| `htmlsize`    | Not supported |   Use `string` as workaround |
| `csssize`     | Not supported |   Use `string` as workaround |
| `csssize4`    | Not supported |   Use `string` as workaround |
| `cssborder`   | Not supported |   Use `string` as workaround |


###### 3.2.2 Migrate eZ Matrix

**If** you use Matrix field (ezmatrix), you'll need to [migrate the storage format](../updating/4_update_2.5.md#changes-to-matrix-field-type)
as it has changed to json internally for new eZ Platform field type.

###### 3.2.3 Add other eZ Enterprise schemas (eZ Enterprise only)

In eZ Platform, the system ships with additional enterprise features that need to be installed.

1. First add all tables needed by eZ Platform Enterprise:
   - https://github.com/ezsystems/ezplatform-ee-installer/blob/2.4/Resources/sql/schema.sql

1. Apply [additional indexes added to Page Builder as of 2.5.3](../updating/4_update_2.5.md#page-builder).

###### 3.2.4 Migrate Page field to Page (eZ Enterprise only)

**If** you use Page field (ezflow) and an eZ Enterprise subscription, and are ready to migrate your eZ Publish Flow content to the eZ Enterprise Page Builder, you can use a script to migrate your old Page content to new Page, to start using a pure eZ Enterprise setup. See [Migrating legacy Page field (ezflow) to new Page (Enterprise)](#migrating-legacy-page-field-ezflow-to-new-page-enterprise) for more information.


### Step 4: Re-configure web server and proxy

#### Varnish *(optional)*

If you use Varnish, the recommended Varnish (4 or higher) VCL configuration can be found in the `doc/varnish` folder. See also the [Using Varnish](../guide/http_cache.md#using-varnish) page.

#### Web server configuration

The officially recommended virtual configuration is now shipped in the `doc` folder, for both apache2 (`doc/apache2`) and nginx (`doc/nginx`). Both are built to be easy to understand and use, but aren't meant as drop-in replacements for your existing configuration.

As was the case starting 5.4, one notable change is that `SetEnvIf` is now used to dynamically change rewrite rules depending on the Symfony environment. It is currently used for the assetic production rewrite rules.

### Step 5: Link assets

As of 2.5 WebPack Encore from Symfony is prefered for handling assets, while Assetic is deprecated. To dump assets is hence a bit different as shown in update guide:

- [Dump assets](../updating/6_dump_assets.md)


## Potential pitfalls

Some frequent migration issues are covered on [common issues page](common_issues.md), the following are specific to migration to 2.5.

##### Unstyled login screen after upgrade

It is possible that after the upgrade your admin screen will be unstyled. This may happen because the new SiteAccess will not be available in the database. You can fix it by editing the permissions for the Anonymous user. Go to Roles in the Admin Panel and edit the Limitations of the Anonymous user's user/login policy. Add all SiteAccesses to the Limitation, save, and clear the browser cache. The login screen should now show proper styling.

##### Translating URLs

eZ Platform by default uses transformation rule `urlalias` when generating URL aliases, same as was default in 5.x.
However, as of eZ Platform 2.x the configuration shipped in `ezplatform.yaml` is set to `urlalias_lowercase`, lower casing the URL for improved SEO.

Like in eZ Publish this is configurable, and can still also be set to `urlalias_compat` and `urlalias_iri` if you had that from before:

``` yaml
ezplatform:
    url_alias:
        slug_converter:
            transformation: 'urlalias_compat'
```

!!! note

    Make sure to [re-generate URL aliases](../guide/url_management.md#regenerating-url-aliases) if you choose to change this setting.
    Implicit as part of the upgrade, or if you change it in the future.

!!! tip

    eZ Platform also lets you define own [transformation groups](../guide/url_management.md#url-alias-patterns).

## Migrating legacy Page field (ezflow) to new Page (Enterprise)

To move your legacy Page field / eZ Flow configuration to eZ Platform Enterprise Edition you can use scripts that will aid in the migration process.

### First from eZ Flow (5.x) to Landing Pages (1.x)

The script will automatically migrate only data – to move custom views, layouts, blocks etc., you will have to provide their business logic again.

!!! caution

    Steps here use `ezsystems/landing-page-fieldtype-bundle:1.7.7-alpha1` to run the ezflow migration script only.
    Don't install this version to use landing-page field type on eZ Platform 2.5.

To use the script, do the following:

!!! note

    Make a note of the paths to .ini files which define your legacy blocks. You will need these paths later.

**1.** Add `ezflow-migration-toolkit` package to your clean Platform installation.

Run:
```bash
composer require "ezsystems/landing-page-fieldtype-bundle:1.7.7-alpha1" "ezsystems/ezflow-migration-toolkit:dev-migrate_on_2.5"
```

**2.** Add the new bundles to `AppKernel.php`.

``` php
// AppKernel.php
new EzSystems\EzFlowMigrationToolkitBundle\EzSystemsEzFlowMigrationToolkitBundle(),
new EzSystems\LandingPageFieldTypeBundle\EzSystemsLandingPageFieldTypeBundle(),
```

**3.** Clear cache.

``` bash
bin/console cache:clear
```

**4.** Run the script with the following parameters:

- absolute path of your legacy application
- list of .ini files which define your legacy blocks

**Script command**

``` bash
bin/console ezflow:migrate <legacy path> —ini=<block definitions> [—ini=<another block definition> ...]
```

**Example of the migration script command**

``` bash
bin/console ezflow:migrate /var/www/legacy.application.com/ —ini=extension/myapplication/settings/block.ini.append.php
```

**5.** You will be warned about the need to create a [backup](../guide/backup.md) of your database. **Proceed only if you are sure you have done it.**

A `MigrationBundle` will be generated in the `src/` folder.

You will see a report summarizing the results of the migration.

**6.** Add `MigrationBundle` to `AppKernel.php`.

``` php
// AppKernel.php
new MigrationBundle\MigrationBundle()
```

**7.** Clear cache again.

At this point you can already view the initial effects of the migration, but they will still be missing some of your custom content.

The `MigrationBundle` generates placeholders for layouts in the form of frames with a data dump.

For blocks that could not be mapped to existing Page blocks, it will also generate PHP file templates that you need to fill with your own business logic.


### Lastly from Landing Pages (1.x) to Page Builder (2.x)

For the last part of this migration, please follow the existing upgrade guide for [migrating to Page Builder](../updating/4_update_2.2.md#migrate-landing-pages).
