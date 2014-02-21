CravlerRemoteBundle
======================

## Installation

### Step 1: update your vendors by running

``` bash
$ php composer.phar require cravler/remote-bundle:@dev
```

### Step2: Enable the bundle

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...

        new Cravler\RemoteBundle\CravlerRemoteBundle(),
    );
}
```

### Step3: Routing

``` yaml
// app/config/routing.yml

cravler_remote:
    resource: "@CravlerRemoteBundle/Resources/config/routing.xml"
```

### Step4: Install node_modules

``` bash
npm install --prefix vendor/cravler/remote-bundle/Cravler/RemoteBundle/Resources/nodejs
```

## Configuration

The default configuration for the bundle looks like this:

``` yaml
cravler_remote:
    user_provider: ~
    app_port: 8080
    remote_port: 8081
    server_port: 8082
    secret: ThisTokenIsNotSoSecretChangeIt
```

## Generate ubuntu upstart commands

``` bash
sudo bash -c "app/console cravler:remote:ubuntu:upstart server > /etc/init/cravler-remote-server.conf"
sudo bash -c "app/console cravler:remote:ubuntu:upstart app > /etc/init/cravler-remote-app.conf"
```

## Example of Usage

[CravlerChatBundle](/cravler/CravlerChatBundle)

## License

This bundle is under the MIT license. See the complete license in the bundle:

```
LICENSE
```