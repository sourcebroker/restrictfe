TYPO3 Extension ``restrictfe``
==============================

.. image:: https://styleci.io/repos/80206848/shield?branch=master
    :target: https://styleci.io/repos/80206848

.. image:: https://poser.pugx.org/sourcebroker/restrictfe/d/monthly
    :target: https://packagist.org/packages/sourcebroker/restrictfe

.. image:: https://poser.pugx.org/sourcebroker/restrictfe/v/stable
    :target: https://packagist.org/packages/sourcebroker/restrictfe

.. image:: https://poser.pugx.org/sourcebroker/restrictfe/license
    :target: https://packagist.org/packages/sourcebroker/restrictfe

|

.. contents:: :local:

What does it do?
----------------

This extension blocks access to frontend and allows to show it
only to some defined exception's like if the request is from
an authorized backend user, has specific IP, header, domain, language
or GET/POST vars. Useful to protect your staging and production instances.

How this can be useful for me?
------------------------------

It will be useful whenever you want to protect whole or part of website
from being public. See following examples for staging and production
instances.

**For staging instances**

You will find restrictfe useful if you have staging instances and you want to
protect frontend content form public but at the same time:

* allow to show frontend to authorized backend users,
* allow to show frontend to IP of your VPN,
* allow to show frontend to your external spiders for crawling,
* allow some payment systems to send confirm link to your application endpoint,
* allow Google Page Speed to make tests,
* etc.

**For production instances**

You will find restrictfe useful if you have production instance which is
already live but access to some part of website must be yet hidden for
regular frontend users. At the same time is must be accessible in
frontend for logged backend users which must be able to edit content on
that hidden part.

The best example is multilanguage website. Lets assume there is
production with only one language - let it be English. After few months
website owner decided to have new language - Chines. The translation
will be done on live directly and will be long process - like few weeks.
During that process client must check content on frontend but at the
same time the translated website must be inaccessible for regular users.
The solution is to use restrictfe and set it to show all frontend except
sysLanguageUid=1 (the uid of new language). In such case even if some
frontend user will switch to new language by forcing L parameter in url
address then he will see warning "Login to see the content of this
page". The content of the warning can be change by setting path to Fluid
template so you can show whatever you like when frontend user is
requesting restricted content.

Installation
------------

Just use composer or download by Extension Manager.

::

    composer require sourcebroker/restrictfe

Be aware that after installation restrictfe blocks all traffic to
frontend by default. This is by design because if you will add new
staging instances they will be blocked by default so there is no risk
that you forgot to protect it and someone will see new staging instance
or google will index it. Of course you must remember to unblock
production instance with simple line:

::

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['exceptions'] = ['*' => true];

Put this config in the file that is included only on live instance!

**Notice!**
restrictfe protection is not working if $_SERVER['REMOTE_ADDR'] == 127.0.0.1 so if you
are working on your local instance restrictfe is disabled. If you want to to make testing
and enable it on your local instance insert following line in typo3conf/AdditionalConfiguration.php
or in some extension ext_localconf.php:
``$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['exceptions']['ip'] = '__UNSET';``


Documentation
-------------

Exceptions
~~~~~~~~~~

As stated earlier restrictfe blocks all traffic to frontend and we must
set exceptions that will allow to see the frontend. Those exceptions
conditions are written in
``$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['exceptions']``
array. By default on first level conditions are joined with logical OR
but you can join them with AND if you will make AND array key and
conditions inside. You can nest OR/AND conditions inside arrays. Values
of conditions can be string or array. If its array its OR'ed. Some
conditions can be negated. In such case the conditions inside are
AND'ed.

**The result of this condition checks is used to decide if frontend
should be blocked or not. If its true then frontend is not blocked.**

Conditions
~~~~~~~~~~

backendUser
+++++++++++

- | *Argument*
  | Activate (boolean)

- *Note*

  - If activated then frontend will be visible to authorized backend
    users. Only single authorization is needed and user can log out
    because special cookie will allow him to see frontend. That also
    means that BE user can unlog from backend and still see the
    frontend - its crucial for good testing of caching bugs.

  - For backend user you can check “Clear BE session after login” in
    backend user record. This will unlog BE user from backend just
    after authorization. This is useful if you want to create only
    kind of "preview" BE user. This user does not need to have access
    to any BE module and do not needs rights to read/write any table.

  - As stated in last points after backend user authorization special
    cookie is set that allows to access frontend even after backend
    user will be logged off. You can set each aspect of this cookie by
    setting ``$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['cookie']``
    array. For example you can set the cookie for multiple subdomains
    which means that user needs to authorize only once to have access
    to all protected subdomains. With htaccess password user would
    need to authorize to each subdomain independently. Example:
    ``$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['cookie']['domain'] = '.example.com';``

- *Example*

  ::

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['exceptions'] = [
       backendUser' => true
   ];

domain
++++++

- | *Argument*
  | Domain name (string)

- | *Note*
  | You can negate this condition with !domain.

- | *Example*
  | Allow frontend access to all except traffic to domain sub.example.com

  ::

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['exceptions'] = [
       '!domain' => ['sub.example.com']
    ];

get
+++

- | *Argument*
  | "getName=getValue" pairs (string)

- | *Note*
  | You can negate this condition with !get.

- | *Example*
  | Allow only request with GET param secret=999 to access frontend.

  ::

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['exceptions'] = [
    'get' => 'secret=999'
   ];

header
++++++

- | *Argument*
  | "headerName=headerValue" pairs (string)

- | *Note*
  | You can negate this condition with !header.

- | *Example*
  | Allow only request with HTTP header MYHEADER=99 to access frontend.

  ::

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['exceptions'] = [
       'header' => 'MYHEADER=99'
    ];

ip
++

- | *Argument*
  | Single IP with mask (string), comma separated list of IPs with
    mask(string), array of IPs with mask (array string)

- | *Note*
  | In the background a ``GeneralUtility::cmpIP()`` is used so you can
    use \* and mask for IP like 12.12.45.\* or 13.55.0.0/16.
  | You can negate this condition with !ip.

- | *Example*
  | Allow frontend access only for IP 11.11.11.11 or 22.22.22.22 or 33.33.33.33

  ::

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['exceptions'] = [
      'ip' => [
             '11.11.11.11',  // ip of developers VPN
             '22.22.22.22'   // ip of client VPN
             '33.33.33.33'   // payment system confirm request
           ]
      ];


  Block frontend access to traffic from IP range 34.34.0.0/16

  ::

       $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['exceptions'] = [
           '!ip' => [
               '34.34.0.0/16', // some not trusted network
           ]
       ];

post
++++

-  | *Argument*
   | "getName=getValue" pairs (string)

-  | *Note*
   | You can negate this condition with !post.

-  | *Example*
   | Allow only request with POST param secret=999 to access frontend.

   ::

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['exceptions'] = [
       'post' => 'secret=999'
    ];

requestUri
++++++++++

-  | *Argument*
   |  uri part after domain without leading slash (string)

-  | *Note*
   | You can negate this condition with !requestUri. The argument is search for only on begining of text.

-  | *Example*
   | Allow only request starting with api/ to be processed.

   ::

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['exceptions'] = [
       'requestUri' => ['api/', 'api2/']
    ];


sysLanguageUid
++++++++++++++

-  | *Argument*
   | uid of language in TYPO3 (integer)

-  | *Note*
   | You can negate this condition with !sysLanguageUid.

-  | *Example*
   | Allow frontend access to all except traffic to language with uid 1.
     Useful on production instance when we want to add and translate new language.

   ::

     $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['exceptions'] = ['!sysLanguageUid' => 1];

Configuration examples
----------------------

Some most useful real live configuration examples:

Production instance that must have sysLanguageUid=1 not avaliable public
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['exceptions'] = [
            '!sysLanguageUid' => 1,
    ];

Production instance that must have domain "sub.example.com" not avaliable public
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['exceptions'] = [
            '!domain' => 'sub.example.com',
    ];

Staging instance that needs to unblock frontend for Google Page Speed Insights
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['exceptions'] = [
           'get' => 'secret=91009123',
    ];

Then of course the url you give google for testing is:
https://www.example.com/?secret=91009123

Staging instance that needs to unblock frontend for IP=11.11.11.11
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['exceptions'] = [
          'ip' => '11.11.11.11',
    ];

Example how the AND condition looks like
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

ip and header are AND'ed. array values inside ip and header are OR'ed.

::

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['exceptions'] = [
            'AND' => [
                 'ip' => [
                    '66.249.64.0/19'
                    '66.249.44.0/19'
                    ],
                 'header' => [
                    'HTTP_USER_AGENT=Google Page Speed Insights'
                    'HTTP_USER_AGENT=Google Page Speed'
                   ],
                 ]
            ]
    ];


Default Configuration
~~~~~~~~~~~~~~~~~~~~~

By default following configuration is applied. You can change every
element of this array using ``$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']``

  ::

        [
            'templatePath' => ExtensionManagementUtility::siteRelPath('restrictfe').'Resources/Private/Templates/Restricted.html',
            'cookie'       => [
                'expire'   => time() + 86400 * 30,
                'path'     => '/',
                'domain'   => null,
                'secure'   => false,
                'httponly' => true,
            ],
            'exceptions' => [
                'backendUser' => true,
                'ip'          => '127.0.0.1',
            ],
        ];


FAQ
---

-  |**Extension does not work. The frontend is not blocked at all. What is wrong?**
   |Be sure you are logged from BE and the cookie "restrictfe" is deleted. Remember also that
   restrictfe protection is not working if $_SERVER['REMOTE_ADDR'] == 127.0.0.1 so if you
   are working on your local instance restrictfe is disabled. To enable it on your local instance
   insert folowing line:
   ``$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['exceptions']['ip'] = '__UNSET';``

-  |**I am logged out from BE but still frontend is not blocked, why?**
   |From 3.0.0. version after first successful login a cookie is set
   (name tx\_restrictfe). If that cookie is present then user do not
   have to authorize again. So delete that cookie and then your frontend
   should be blocked again.


Known problems
--------------

None.

To-Do list
----------

1. Add userFunc for conditions
2. Add pregmatch for all conditions like '~domain'
3. Add support for detecting browser language to see proper lang on
   "you must log to see the website" warning screen.
4. Make unit tests for conditions array.


Changelog
---------

See https://github.com/sourcebroker/restrictfe/blob/master/CHANGELOG.rst
