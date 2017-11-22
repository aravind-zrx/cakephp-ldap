# Cakephp-ldap plugin for CakePHP

## Requirements

* CakePHP 3.1+

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require aravind-zrx/Cakephp-ldap
```

## Usage

In your app's `config/bootstrap.php` add:

```php
// In config/bootstrap.php
Plugin::load('LdapUtility');
```

or using cake's console:

```sh
./bin/cake plugin load LdapUtility
```

## Configuration:

Basic configuration for creating ldap handler instance

```php
	$config = [
		'host' => 'ldap.example.com',
        'port' => 389,
        'baseDn' => 'dc=example,dc=com',
        'startTLS' => true,
        'hideErrors' => true,
        'commonBindDn' => 'cn=readonly.user,ou=people,dc=example,dc=com',
        'commonBindPassword' => 'secret'
	]
	$ldapHandler = new LdapUtility\Ldap($config);
```

Setup Ldap authentication config in Controller

```php
    // In your controller, for e.g. src/Api/AppController.php
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('Auth', [
            'storage' => 'Memory',
            'authenticate', [
                LdapUtility/Ldap => [
					'host' => 'ldap.example.com',
			        'port' => 389,
			        'baseDn' => 'dc=example,dc=com',
			        'startTLS' => true,
			        'hideErrors' => true,
			        'commonBindDn' => 'cn=readonly.user,ou=people,dc=example,dc=com',
			        'commonBindPassword' => 'secret',
			        'fields' => ['username' => 'email', 'password' => 'password'],
                    'userModel' => 'Users',
                    'queryDatasource' => true
				]
            ],

            'unauthorizedRedirect' => false,
            'checkAuthIn' => 'Controller.initialize',
        ]);
    }
```

## Example:

Search for entry with cn starting with test
```php
	$ldapHandler->find('search', [
		'baseDn' => 'ou=people,dc=example,dc=com',
		'filter' => 'cn=test*',
		'attributes' => ['cn', 'sn', 'mail']
	]);
```

Read a particular entry with cn=test.user
```php
	$ldapHandler->find('read', [
		'baseDn' => 'ou=people,dc=example,dc=com',
		'filter' => 'cn=test.user',
		'attributes' => ['cn', 'sn', 'mail']
	]);
```

## TLS connections in development environment
	
	To connect an LDAP server over TLS connection, check ldap.conf file
		* For mac, conf file is located in /etc/openldap/ldap.conf
		* For unix, conf file is located in /etc/ldap/ldap.conf 
	To disable certificate verification change TLS_REQCERT to 'never' in ldap.conf file
