# Sylius GoPay payment gateway plugin  
<div align="center">
    <a href="http://sylius.com" title="Sylius" target="_blank"><img src="https://demo.sylius.com/assets/shop/img/logo.png" width="300" /></a>
    <br>
    <a href="https://www.gopay.com" title="GoPay" target="_blank"><img src="https://dl.dropboxusercontent.com/s/af8fiebcqmk9wgm/GoPay-logo-varianta-A-PANTONE.png" width="300" /></a>
</div>

## Installation

```bash
$ composer require bratiask/gopay-plugin
```
    
Add plugin dependencies to your bundles.php file:

```php
Bratiask\GoPayPlugin\GoPayPlugin::class => ['all' => true]
```

## Usage
Add your test credentials in Sylius admin as new payment method. Complete couple of orders with different states and send email to GoPay authorities. 
After the review you will get production credentials, so just change it in Sylius admin and you are ready to go. 
