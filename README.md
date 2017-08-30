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
 
## TODO: Testing spec :-(