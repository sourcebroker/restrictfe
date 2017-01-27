# TYPO3 Extension ``restrictfe``

## What does it do?

This extension allows you to prevent non logged backend users to see the frontend.

That basicly means that you can use this extension as a replacement for htaccess passwords in cases
when you for example have some development version of a page and you don't want non authorized users
to see that page or google spiders to index it.

Additionally it allows you to keep the same .htaccess file for all stages because you do not have to
add any htaccess password directives to .htaccess file.

Some more feature:

- You can add IP or headers that will be allowed to see frontend without authorization.
- For backend user you can check “Clear BE session after login”. This will unlog BE user from backend and he will be still able to see the frontend. Usefull if you do not want to give any access to BE but user need to see the frontend.
- In version 3.0.0. a cookie is set after successful authorization. If the cookie is present then no additional login is required to see frontend.


## Users manual

Install extension. Choose option you like in EM config:

The alternatives there are:

- **Relative path to template**
  Relative path to template files folder (with slash at the end eg: typo3conf/ext/myext/Resource/Private/Templates/)
- **IPs allowed**
  Comma separated IPs allowed to see frontend without authorisation. Supports mask like 192.168.1.\*
- **Headers allowed**
  Comma separaetd list of headers allowed to see frontend without authorisation. (in the client you
  need to put header like "Tx-RestrictFe: value")

**IMPORTANT STEP!**

**You need to put this line so the extension can start work:**

**$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['enable'] = 1;**

Where to put this line?

The best is that you have some file that is out of GIT and is read by TYPO3 instance. One of the way
is to create file: **typo3conf/localconf_local.php**  and include it in **typo3conf/localconf.php**

Note! In TYPO3 6.x the additional configuration is kept in typo3conf/AdditionalConfiguration.php
file.


## Known problems

None.


## To-Do list

None.


## FAQ

- **Extension do not work. The frontend is not blocked at all. What is wrong?**
   You must declare somewhere  $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['enable'] = 1;
   Read the “Users Manual” section above for more info.
- **I have $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['enable'] = 1; but still frontend is
   not blocked?**
   Logout from BE. Logged BE users are not blocked.
- **I am logged out and have $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['enable'] = 1; but
   still frontend is not blocked?**
   From 3.0.0. version after first successful login a cookie is set (name tx_restrictfe). If that
   cookie is present then user do not have to authorize again. So delete that cookie and then you
   should not see frontend.
