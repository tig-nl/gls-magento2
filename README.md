# TIG GLS for Magento 2
We created this extension to **easily integrate GLS' Delivery Services into Magento 2**. 

## What does it do?
* Add GLS as a shipping method to Magento 2's checkout.
  * Show available Delivery Options depending on zipcode and shipping date, such as:
    * Express Delivery (e.g. before 9.00 AM, 12.00 AM or 5.00 PM),
    * Saturday Delivery,
    * Delivery to a ParcelShop (sorted by distance from zipcode).
* Easily create, delete or print labels from within the Shipment-view:
  * Including ShopReturn-label (if enabled).
* Enable/disable Express Delivery Services separately,
* Offer discounts or calculate additional for specific Delivery Services.

## Installation using Composer
<pre>composer require tig/gls-magento2</pre>

## Installation without using Composer
_Clone or download_ the contents of this repository into `app/code/TIG/GLS`.

### Development Mode
After installation, run `bin/magento setup:upgrade` to make the needed database changes and remove/empty Magento 2's generated files and folders.

### Production Mode
After installation, run:
1. `bin/magento setup:upgrade`
2. `bin/magento setup:di:compile`
3. `bin/magento setup:static-content:deploy [locale-codes, e.g. nl_NL en_US`
4. `bin/magento cache:fluche`

Done!

## Configuration

### API credentials
To use this module you need API credentials provided by GLS. These can be entered in _Stores / Configuration / Sales / GLS_.

### Shipping Method  
To configure the shipping method's handling fees, available services, etc. go to _Stores / Configuration / Sales / Shipping Methods / GLS_.

### Store Address and E-mail addresses
You need to configure your store address. This can be done by: _Stores / Configuration / General / General / Store Information_. Make sure you type your houseNo in the _Street Address Line 2_ field.

GLS will notify your customers with emails about the delivery time. The sender name and sender email are from the Magento configuration located at _Stores / Configuration / Store Email Addresses / Customer Support_. 

### Table Rates (price vs destination)
To configure different handling fees for different countries you setup table rates within the GLS extension. This can be done at configuration scope: website.

You can start with exporting the .csv-file to have a template with the following fields:
* Country = (2 or 3 lettered) country codes according to Magento standards.
* Region/State = You can specify the region or state. Keep in mind this field is optional in the checkout for some countries. Wildcard = *
* Zip/Postal Code = This should the the exact postal code. For example for the Netherlands and without postal code validation you should have 2 lines in your .csv-file per postal code: 1014BA and 1014 BA. Wildcard = *
* Shipping Price = 9.95 (period as delimiter) this price is added to the amount in the Base _Handling Fee_ field in the configuration.  


