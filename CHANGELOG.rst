Changelog
---------

10.0.0
~~~~~~

a) [TASK] Increase support to TYPO3 11 and drop support for TYPO3 8 and 9.
b) [BUGFIX] Add "extension-key" for TYPO3.
c) [TASK] Add ddev.
d) [TASK] Add backend redirect to front after authorisation.
e) [TASK] Refactor legacy code.

9.0.0
~~~~~

a) [TASK] Increase TYPO3 to 10 / drop support for 6.2 and 7.6
b) [TASK] Change location of ext icon.
c) [TASK] Refactor code.

8.2.1
~~~~~

a) [BUGFIX] Fix headers are case-insensitive.
b) [TASK] Extend supported TYPO3 verison in ext_emconf.php

8.2.0
~~~~~

a) [TASK] Change required TYPO3 from 'typo3/cms' to 'typo3/cms-core'.
b) [FEATURE] Add compatibility for TYPO3 9.0.0
c) [DOCS] Docs improvements.
d) [TASK] Change icon.

8.1.0
~~~~~

a) [FEATURE] Use TYPO3 settings for cookie HttpOnly and Secure flags.


8.0.3
~~~~~

a) [BUGFIX] Set restrictfe cookie only for backend user authorisation.


8.0.2
~~~~~

a) [TASK] Add more info to composer.json

b) [TASK] Add some badges to README.rst

c) [TASK] Add styleci / php_cs / scrutinizer configs

d) [BUGFIX] array_unique($conditionResults) cannot be passed to reset() as the parameter $array expects a reference.

e) [BUGFIX] The parameter $_params and $pObj is not used and could be removed.

f) [BUGFIX] Make strict comparision of values.

g) [TASK] Change exit() with die()

h) [DOCS] Mods for docs.

8.0.1
~~~~~

a) [DOCS] Docs update.

b) [BUGFIX] Bring back 'ip' => '127.0.0.1' as default config.

c) [CLENAUP] Remove not used use.

8.0.0
~~~~~

a) [BUGFIX] Store BE_USER just after authorization because later in typo3/sysext/frontend/Classes/Http/RequestHandler.php
   BE_USER can be unset if he has no access to page tree, but we do not care about acceess to page tree for restrictfe.
   We only want to know if user logged sucessfully.

b) [CLEANUP] PSR-2 formatting.

c) [DOCS] Divide changlog from main README.rst into separate CHANGELOG.rst.

d) [BUGFIX] Disable php inspecion for $_params in restrictFrontend($_params, &$pObj) - PhpUnusedParameterInspection.

e) [TASK] Cleanup up on detecting for wrong naming for "exeptions" or "exception".

f) [TASK][BREAKING] Move config to external class and remove hook to set additional config params as all params can be
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
