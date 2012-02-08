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

Changelog
---------

* 1.0.3 - reorganize files to follow HostBill directories and for future feature expansion
* 1.0.2 - original release to github

How to Install
--------------

Just extract zip of github project into the root of your HostBill install then activate the module from inside HostBill.

Future Ideas
------------

* add sync support (getSynchInfo)
* add client area section for grabbing integration code
* add custom order type to make accounts show up under a different account type (instead of shared accounts)