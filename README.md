# TYPO3 Extension ``restrictfe``

## What does it do?

This extension allows you to show frontend only for BE logged users. Frontend can be 
blocked selectively, for example only for some language or only for some domains.

That basicly means that you can use this extension as a replacement for htaccess 
passwords in cases when you for example have some development version of a website 
and you don't want non authorized users to see this website or google spiders to index it.


Some more feature:

- You can build rules to deny / allow based on IP / domains / sysLanguageUid / HTTP headers.

- For backend user you can check “Clear BE session after login”. This will unlog BE 
user from backend but this BE user will be still able to see the frontend. Usefull if you do 
not want to give any access to BE but user need to see the frontend.

- In version 3.0+ a cookie is set after successful authorization. If the cookie 
is present then no additional login is required to see frontend.

- You can set the authorisation form multilpe subdomains which means that user needs 
to authorize only once to have access to all subdomains. With htaccess passwd user would
need to authorize to each subdomain independedly. 

## Documention

Restrictfe operates in two modes: "allow" and "deny".

### "allow" mode
This mode is useful on production instance which is already live but access to some part
of website must be yet hidden for regular frontend users but at the same time is must be 
accessible in frontend for logged BE users which must edit content on that hidden part . 

The best example is multilanguage website. Lets assume there is production with only one 
language - let it be English. Website owner decided to have new language - Chines. 
The translation will be done on live directly and will be long few weeks process. 
During that process client must do content check on frontend but on the same time the 
translated website must be unaccessible for regular users. The solution is to use restrictfe 
and put it into allow mode with exclusion for sysLanguageUid=1 (the uid of new langauge).

In such case even if some frontend user will switch to new language by forcing L parameter 
in url address then such frontend user will see "Login to see the content of this page" 
warning. 

If the mode is "allow" then if "rule" are true the access to frontend is blocked.

Configuration for above multilang example would be:
```
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['mode'] = 'allow';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['rules'] = [
        'sysLanguageUid' => 1,
];
```

The second example would be adding new website with new domain in already existing production
instance. The situation is the same as above. We do not want regular users to be able to accidentally 
find our new domain and see unfinished website. So we can deny access to this domain for regular
users but allow to see the content for BE logged users which will edit the content.

Configuration for above multi website example would be:
```
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['mode'] = 'allow';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['rules'] = [
        'domain' => '/^sub.example.com$/',
];
```

If you need some preg_matach on domain then use:
```
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['mode'] = 'allow';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['rules'] = [
        'domainPregmatch' => '/^sub.example.com$/',
];
```

### "deny" mode
This mode is useful for all staging instances where you want to deny all access to frontend 
except some IPs or HTTP headers etc. 

If the mode is "deny" then if "rule" are true the access to frontend is allowed.

Configuration array for "deny" mode:

```
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['mode'] = 'deny';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['rules'] = [
        'ip' => '12.12.2.2,123.12.2.*',
        'header' => 'HTTP_TX_RESTRICTFE=foo',
];
```

which would mean deny for all except:
- IP=12.12.2.2 OR IP mask 123.12.2.*
- OR http header HTTP_TX_RESTRICTFE exists with value equal to "foo"

### "rules"
Rules is array of conditions that are checked each by each. By default on first level 
conditions are joined with logical OR but you can make nested conditions array with
AND/OR operator. Values can be string or array.

The result of this check is then used to decide if frontend should be blocked or not. 

If you use mode "allow" then conditions are to turn on frontend blocking selectivly. 
If you use mode "deny" then conditions are to turn off frontend blocking selectivly. 

Allowed conditions:

- ip
- !ip
- domain
- !domain
- sysLanguageUid
- !sysLanguageUid
- domainPregmatch
- header


Examples:
 
1) Example for nesting:

```
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['mode'] = 'deny';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['rules'] = [
        'ip' => '166.149.64.0',
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
```

2) Examples for unblocking Google Page Speed Insights on staging instance to allow Google to make tests:

```
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['mode'] = 'deny';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['rules'] = [
        'AND' => [
             'ip' => '66.249.64.0/19',
             'header' => 'HTTP_USER_AGENT=Google Page Speed Insights',
        ]
];
```

## Important 
In version below 5.0 there were settings kept in Extension Manager with IP / header. 
You must move them manually to $GLOBALS[\'TYPO3_CONF_VARS\'][\'EXTCONF\'][\'restrictfe\'][\'rules\']


## Known problems
None.


## To-Do list
None.


## FAQ
- **Extension does not work. The frontend is not blocked at all. What is wrong?**
   Be sure you are logged from BE and the cookie "restrictfe" is deleted.
   
- **I am logged out from BE but still frontend is not blocked, why?**
   From 3.0.0. version after first successful login a cookie is set (name tx_restrictfe). If that
   cookie is present then user do not have to authorize again. So delete that cookie and then your
   frontend should be blocked again.
