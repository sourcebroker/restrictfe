Changelog
---------

8.0.0
~~~~~

a) [BUGFIX] Store BE_USER just after authorization because later in typo3/sysext/frontend/Classes/Http/RequestHandler.php
   BE_USER can be unset if he has no access to page tree, but we do not care about acceess to page tree for restrictfe.
   We only want to know if user logged sucessfully.

b) [CLEANUP] PSR-2 formatting.

c) [DOCS] Divide changlog from main README.rst into separate CHANGELOG.rst.

d) [BUGFIX] Disable php inspecion for $_params in restrictFrontend($_params, &$pObj) - PhpUnusedParameterInspection

e) [TASK] Cleanup up on detecting for wrong naming for "exeptions" or "exception"

f) [TASK][!!!BREAKING] Move config to external class and remove hook to set additional config params as all params can be
    overwritten by config from $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']

g) [TASK] Add separate class which hooks into BE login process and stores tx_restrictfe cookie after sucessful BE
   login. Additionally logout user if "tx_restrictfe_clearbesession" is set for user profile.

7.1.2
~~~~~

a) Update ext_emconf.php.

7.1.1
~~~~~

a) Documentation update.

7.1.0
~~~~~

a) Add "requestUri" condition and update documentation for "requestUri" usage.
a) Update documentation with info that restrictfe is diabled for local instances.


7.0.1
~~~~~

a) Update documentation with default settings.

7.0.0
~~~~~

a) Remove "enable" $GLOBALS['TYPO3\_CONF\_VARS']['EXTCONF']['restrictfe']['enable']
b) Set 127.0.0.1 as default IP that is allowed to see frontend without authorization.