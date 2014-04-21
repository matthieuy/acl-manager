# ACL Manager

[![Latest Stable Version](https://poser.pugx.org/matthieuy/acl-manager/v/stable.png)](https://packagist.org/packages/matthieuy/acl-manager) [![Total Downloads](https://poser.pugx.org/matthieuy/acl-manager/downloads.png)](https://packagist.org/packages/matthieuy/acl-manager) [![Latest Unstable Version](https://poser.pugx.org/matthieuy/acl-manager/v/unstable.png)](https://packagist.org/packages/matthieuy/acl-manager) [![License](https://poser.pugx.org/matthieuy/acl-manager/license.png)](https://packagist.org/packages/matthieuy/acl-manager) [![Build Status](https://travis-ci.org/matthieuy/acl-manager.svg?branch=master)](https://travis-ci.org/matthieuy/acl-manager)

## Installation
To install add the following dependency to your composer.json

```js
"matthieuy/acl-manager": "dev-master"
```

and run `composer update`

## Usage

This library use 3 class :

 - Acl : The main class to instance
 - Resource : The object to which access is controlled (ex: action)
 - Role : The object that may request access to a Resource (ex: user)


#### Init ACL Manager
```php
<?php
// Create an instance
$acl = \Acl\Acl::getInstance();
```


#### Create roles
```php
// Create Role with object
$admin = new \Acl\Role('admin');
$acl->addRole($admin);
$acl->addRoles(array(
            new \Acl\Role('publisher'), 
            new \Acl\Role('validator')
        ));

// Or with string (same result)
$acl->addRole('admin');
$acl->addRoles(array('publisher', 'validator'));

// You can use inheritance
$publisher = $acl->getRole('publisher');
$publisher->addParent('admin');
```


#### Create Resource
```php
// Create Resource
$readNews = new \Acl\Resource('read', 'news');
$acl->addResource($readNews);
$acl->addResources(array(
                    new \Acl\Resource('edit', 'news'),
                    new \Acl\Resource('action', 'module')
                ));

// Or with string (the defaut module is "global")
$acl->addResource('login');
$acl->addResources('connect', 'contact', 'profil');
```


#### Define the rights
```php
// Define the right with the Acl object
$acl->allow($admin, $readNews);
$acl->deny($role, $resource);

// Or with the Role object
$admin->allow($readNews);
$role->deny($resource);

// Or with the Resource object
$readNews->allow($admin);
$resource->deny($role);
```


#### Check right
```php
// Now, you can check right
// With the Acl object
$result = $acl->isAllowed($admin, $readNews);
$result = $acl->isAllowed('admin', 'read', 'news');
$result = $acl->isAllowed('role', 'resource', 'module');
if ($result) {
    echo "Allow";
} else {
    echo "Deny";
}

// With the Role object
$admin->isAllowed($readNews);
$role->isAllowed($resource);
$role->isAllowed('resourceName');

// With the resource object
$readNews->isAllowed($admin);
$resource->isAllowed($role);
$resource->isAllowed('roleName');
```
