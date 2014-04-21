<?php
/**
 * ACL Manager
 * @author Matthieu YOURKEVITCH <matthieuy@gmail.com>
 * @package Acl
 */

namespace Acl;
use \Acl\Role       as Role;

class Resource
{
    private $name;
    private $scope;

    /**
     * Constructor
     * @param string $name The name of the resource
     * @param string $scope The scope
     */
    public function __construct($name, $scope = 'global')
    {
        $this->name = $name;
        $this->scope = $scope;
    }

    /**
     * Get the resource's name
     * @return string The name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the resource's scope
     * @return string The scope's name
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Allow a role to this resource
     * @param Role $role The role
     * @return Acl\Resource resource
     */
    public function allow(Role $role)
    {
        $role->allow($this);
        return $this;
    }

    /**
     * Deny a role to this resource
     * @param Role $role The role
     * @return Acl\Resource resource
     */
    public function deny(Role $role)
    {
        $role->deny($this);
        return $this;
    }

    /**
     * Check if role can access to this resource
     * @param Acl\Role $role The role to check
     * @return boolean Allow or deny
     */
    public function isAllowed(Role $role)
    {
        $acl = Acl::getInstance();
        return $acl->isAllowed($role, $this);
    }

    /**
     * Get the resource as string
     * @return string The resource's name
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Get the resource as array
     * @return array The resource's informations
     */
    public function toArray()
    {
        return array(
                'name' => $this->name,
                'scope' => $this->scope
            );
    }
}
