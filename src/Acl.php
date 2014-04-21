<?php
/**
 * ACL Manager
 * @author Matthieu YOURKEVITCH <matthieuy@gmail.com>
 * @package Acl
 */

namespace Acl;
 
use \Acl\Role       as Role;
use \Acl\Resource   as Resource;
use \Exception      as Exception;

class Acl
{
    private static $instance;
    private $roles = array();
    private $resources = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        self::$instance = $this;
    }

    /**
     * Get Acl instance
     * @return Acl\Acl Acl
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Acl();
        }
        return self::$instance;
    }

    /**
     * Add a role
     * @param Acl\Role|string $role The role to add or the role's name
     * @throws Exception Role's type is invalid
     * @return Acl\Role The role added
     */
    public function addRole($role)
    {
        if (is_string($role)) {

            if (!array_key_exists($role, $this->roles)) {
                $this->roles[$role] = new Role($role);
                return $this->roles[$role];
            }
        } elseif (is_a($role, 'Acl\Role')) {
            if (!array_key_exists($role->getName(), $this->roles)) {
                $this->roles[$role->getName()] = $role;
            }
        } else {
            throw new Exception("role must be a Role instance or a string");
        }
    }

    /**
     * Add many roles
     * @param Acl\Role[]|string[] Roles to add
     * @throws Exception Role's type is invalid
     * @return Acl\Acl ACL
     */
    public function addRoles(array $roles)
    {
        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * Delete a role
     * @param string $name Role's name
     * @return Acl\Acl ACL
     */
    public function delRole($name)
    {
        if (isset($this->roles[$name])) {
            unset($this->roles[$name]);
            foreach ($this->roles as $role) {
                $role->delParent($name);
            }
        }

        return $this;
    }

    /**
     * Get a role
     * @param string $name The role's name
     * @return Acl\Role|null The role or null if unset
     */
    public function getRole($name)
    {
        if (isset($this->roles[$name])) {
            return $this->roles[$name];
        }
        return null;
    }

    /**
     * Get all roles
     * @return Acl\Role[] Roles
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Add a resource
     * @param Acl\Resource|string Resource to add or this name
     * @throws Exception Resource's type is invalid
     * @return Acl\Resource resource added
     */
    public function addResource($resource)
    {
        if (is_string($resource)) {
            $scope = 'global';
            $name = $resource;
            $resource = new Resource($name, $scope);
        } elseif (is_a($resource, 'Acl\Resource')) {
            $scope = $resource->getScope();
            $name = $resource->getName();
        } else {
            throw new Exception("The resource must be a instance of Resource or a string");
        }

        // Add
        if (!array_key_exists($scope, $this->resources) || !array_key_exists($name, $this->resources[$scope])) {
            $this->resources[$scope][$name] = $resource;
        }
        
        return $resource;
    }

    /**
     * Add many resources
     * @param Acl\Resource[]|string[] Resources to add or their names
     * @throws Exception Resource's type is invalid
     * @return Acl\Acl ACL
     */
    public function addResources(array $resources)
    {
        foreach ($resources as $resource) {
            $this->addResource($resource);
        }

        return $this;
    }

    /**
     * Get a resource
     * @param string $name The resource's name
     * @param string $scope The scope
     * @return Acl\Resource|null Resource or null if unset
     */
    public function getResource($name, $scope = 'global')
    {
        if (isset($this->resources[$scope][$name])) {
            return $this->resources[$scope][$name];
        }
        return null;
    }

    /**
     * Get all resources
     * @return array Resources
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Allow a role to resource
     * @param Acl\Role $role The role
     * @param Resource $resource The resource
     * @return Acl\Acl ACL
     */
    public function allow(Role $role, Resource $resource)
    {
        $role->allow($resource);
        return $this;
    }

    /**
     * Deny a role to resource
     * @param Acl\Role $role The role
     * @param Acl\Resource $resource The resource
     * @throws Exception The role don't exist
     * @return Acl\Acl ACL
     */
    public function deny(Role $role, Resource $resource)
    {
        $role->deny($resource);
        return $this;
    }

    /**
     * Test if a role can acces to a resource
     * @param Acl\Role|string $role The role or his name
     * @param Acl\Resource|string $resource The resource or his name
     * @param string $scope The scope (need only if $resource is string)
     * @return boolean Allow or deny
     */
    public function isAllowed($role, $resource, $scope = 'global')
    {
        // Get Role and resource
        $role = (is_a($role, 'Acl\Role')) ? $role : $this->getRole($role);
        $resource = (is_a($resource, 'Acl\Resource')) ? $resource : $this->getResource($resource, $scope);

        // Role or resource don't exist : deny
        if ($role === null || $resource === null) {
            return false;
        }

        // Get vars
        $access = $role->getAccess();
        $scope = $resource->getScope();
        $name = $resource->getName();

        // Rule ACL exist ?
        if (isset($access[$scope][$name])) {
            return $access[$scope][$name];
        }

        // Maybe in parent role ?
        $parents = $role->getParents();
        foreach ($parents as $parent) {
            if ($this->isAllowed($parent, $resource, $scope)) {
                return true;
            }
        }

        // Nothing find : deny
        return false;
    }
}
