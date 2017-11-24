# Sylius GoPay payment gateway plugin  

## Installation

```bash
$ composer require czende/gopay-plugin
```
    
Add plugin dependencies to your AppKernel.php file:

```php
public function registerBundles()
{
    return array_merge(parent::registerBundles(), [
        ...
        
        new \Czende\GoPayPlugin\GoPayPlugin(),
    ]);
}
```

## Good to know
Be aware of your default Symfony session storage and its permissions. This plugin saves gateway tokens into php session defined in your framework params. 
In case your application doesn't have proper permissions for `/tmp/session` you have to change the default settings, for example, to:
```
framework:
    ...
    session:
        handler_id: session.handler.native_file
        save_path: '%kernel.project_dir%/var/sessions/%kernel.environment%'
```