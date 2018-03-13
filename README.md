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

## Usage
Add your test credentials in Sylius admin as new payment method. Complete couple of orders with different states and send email to GoPay authorities. 
After the review you will get production credentials, so just change it in Sylius admin and you are ready to go. 