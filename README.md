OpenSRS Publishing Module for Hostbill
======================================

Allows Hostbill to automate the management of OpenSRS goMobi accounts via XML-API.

Hostbill Commands
-----------------

* create -- runs `create` against the API
* suspend -- runs `disable` against the API
* unsuspend -- runs `enable` against the API
* terminate -- runs `delete` against the API
* expire -- custom command that runs `let_expire` against the API.
* unexpire -- custom command that runs `enable` against the API.

Hostbill Integration
--------------------

* client area login details with resellers domainadmin.com url
* client area login button that submits form directly to resellers domainadmin.com url
* test configuration (it doesn't seem to like the current check version command but it will tell us if logged in)

How to Install
--------------

Just drop `class.opensrs_publishing.php` and the `opensrs_publishing` folder into `includes/modules/Hosting/` folder of your HostBill install then activate the module from inside HostBill.

Future Ideas
------------

* add sync support (getSynchInfo)
* add client area section for grabbing integration code