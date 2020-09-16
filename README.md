# TIG GLS Netherlands for Magento 2

![TIG GLS tested 2.2.x versions](https://img.shields.io/badge/Tested%20with-2.2.11-%23009f3e)
![TIG GLS tested 2.3.x versions](https://img.shields.io/badge/Tested%20with-2.3.4-%23009f3e)

We created this extension to **easily integrate GLS' Delivery Services into Magento 2**. 

## What does it do?
* Add GLS as a shipping method to Magento 2's checkout.
  * Show available Delivery Options in The Netherlands depending on zipcode and shipping date, such as:
    * Express Delivery (e.g. before 9.00 AM, 12.00 AM or 5.00 PM),
    * Saturday Delivery,
    * Delivery to a ParcelShop (sorted by distance from zipcode),
  * Ship outside The Netherlands using Standard Delivery.
  * Use table rates to set different shipping rates per country and order subtotal.
* Easily create, delete or print labels from within the Shipment-view:
  * Including ShopReturn-label (if enabled).
* Enable/disable Express Delivery Services separately,
* Offer discounts or calculate additional for specific Delivery Services.

## Installation using Composer
<pre>composer require tig/gls-magento2</pre>

## Installation without using Composer
_Clone_ or _download_ the contents of this repository into `app/code/TIG/GLS`.

### Development Mode
After installation, run `bin/magento setup:upgrade` to make the needed database changes and remove/empty Magento 2's generated files and folders.

### Production Mode
After installation, run:
1. `bin/magento setup:upgrade`
2. `bin/magento setup:di:compile`
3. `bin/magento setup:static-content:deploy [locale-codes, e.g. nl_NL en_US]`
4. `bin/magento cache:flush`

Done!

## Configuration

### API credentials
To use this module you need API credentials provided by GLS. These can be entered in _Stores / Configuration / Sales / GLS_.

### Shipping Method  
To configure the shipping method's handling fees, available services, etc. go to _Stores / Configuration / Sales / Shipping Methods / GLS_.

### Store Address and E-mail addresses
Because each label requires a valid Sender Address, it is mandatory to configure a store address at _Stores / Configuration / General / General / Store Information_. The House Number should be entered in _Street Address Line 2_.

GLS will notify your customers with emails about the delivery time. The sender name and sender email are from the Magento configuration located at _Stores / Configuration / Store Email Addresses / Customer Support_. 

### Table Rates (Price vs Destination)
To configure different handling fees for different countries, setup table rates in _Stores / Configuration / Sales / Shipping Methods / GLS_ using _Website_ as the Scope.

Start by using the _Export CSV_ button, which results in an empty CSV-file with the following columns:
* **Country**: (2 or 3 lettered) country codes according to ISO standards.
* **Region/State**: Specify a region or state to make the shipping applicable only to this region. Keep in mind that this field is optional in the checkout for some countries. Wildcard = *
* **Zip/Postal Code**: This should the the exact postal code. E.g. for The Netherlands and without postal code validation you should have 2 lines in your CSV-file per postal code, e.g. 1014BA and 1014 BA. Wildcard = *
* **Order Subtotal (and above)**: apply this shipping rate if the total cart value exceeds this amount (e.g. 100.) Defaults to 0. 
* **Shipping Price**: e.g. 9.95 (using a period as delimiter) is added to the amount specified as _Handling Fee_ in _Stores / Configuration / Sales / Shipping Methods / GLS_.  


