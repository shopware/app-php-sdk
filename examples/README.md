# Simple Example App

This is a simple example app that shows how to use the `shopware/app-sdk`.

## Backend

In our simple Backend we react to `/register/authorize` and `/register/callback`. 
The first one is used to authorize the app, and the second one is used to handle the callback from the shopware instance.

In this example, we used a Simple [FileRepository](./FileRepository.php) to store the data,
but you should use any other storage by implementing the `\Shopware\App\SDK\Shop\ShopRepositoryInterface`

You can use also the PHP builtin server to run the backend:

```bash
php -S 0.0.0.0:6000 -t
```

## App manifest

```xml
<?xml version="1.0" encoding="UTF-8"?>
<manifest xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
          xsi:noNamespaceSchemaLocation="https://raw.githubusercontent.com/shopware/platform/trunk/src/Core/Framework/App/Manifest/Schema/manifest-2.0.xsd">
    <meta>
        <name>Foo</name>
        <label>My Label</label>
        <label lang="de-DE">My Label</label>
        <description></description>
        <description lang="de-DE"></description>
        <author>Your Company</author>
        <copyright>(c) by Your Company</copyright>
        <version>1.0.0</version>
        <icon>Resources/config/plugin.png</icon>
        <license>MIT</license>
    </meta>
    <setup>
        <registrationUrl>http://localhost:6000/register/authorize</registrationUrl>
        <secret>test</secret>
    </setup>
</manifest>
```

Make sure the `name` and `secret` inside `setup` matches the configuration with your AppConfiguration.



