<?php
/**
 * ACL Manager
 * @author Matthieu YOURKEVITCH <matthieuy@gmail.com>
 * @package Acl
 */

namespace Acl;

class Role
{
    private $name;
    private $parents = array();
    private $access = array();

    /**
     * Constructor
     * @param string $name The name of the role
     * @param string[]|Role[] $parents List of parent role (name or Role instance)
     */
    public function __construct($name, array $parents = array())
    {
        $this->name = $name;
        foreach ($parents as $parent) {
            $this->parents[] = (string) $parent;
        }
    }

    /**
     * Get the role's name
     * @return string The role's name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the parents role
     * @return string[] List of parents
     */
    public function getParents()
    {
        return $this->parents;
    }

    /**
     * Add a parent
     * @param Role|string $parent The parent's role or his name
     * @throws \Exception The role can't be the parent of himself
     * @return Role This role
     */
    public function addParent($parent)
    {
        $parent = (string) $parent;
        if ($parent == $this->getName()) {
            throw new \Exception("The role can't be the parent of himself");
        } elseif (!in_array($parent, $this->parents)) {
            $this->parents[] = $parent;
        }

        return $this;
    }

    /**
     * Delete the parent (only the link)
     * @param Role|string $parent Parent (Role or his name)
     * @return Role This role
     */
    public function delParent($parent)
    {
        if (is_a($parent, 'Role')) {
            $parent = $parent->getName();
        }
        if (($index = array_search($parent, $this->parents)) !== false) {
            unset($this->parents[$index]);
        }

        return $this;
    }

    /**
     * Allow a resource to this role
     * @param Resource $resouce The resource
     * @return Role This role
     */
    public function allow(Resource $resource)
    {
        return $this->addAccess($resource, true);
    }

    /**
     * Deny a resource to this role
     * @param Resource $resource The resource
     * @return Role This role
     */
    public function deny($resource)
    {
        return $this->addAccess($resource, false);
    }

    /**
     * Check if this role can access to the resource
     * @param Resource|string $resource The resource or his name
     * @return boolean Allow or deny
     */
    public function isAllowed($resource)
    {
        $acl = Acl::getInstance();
        return $acl->isAllowed($this, $resource);
    }

    /**
     * Get all access<br>
     * @internal This method is only use by the Acl class don't use it
     * @access private
     * @return array The access
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * Get the role as string
     * @return string The role's name
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Get the role as array<br>
     * Use it for storage
     * @see fromArray()
     * @return array The role information (minimize)
     */
    public function toArray()
    {
        $return = array('name' => $this->name);
        if (!empty($this->parents)) {
            $return['parents'] = $this->parents;
        }
        if (!empty($this->access)) {
            $return['access'] = $this->access;
        }

        return $return;
    }

    /**
     * Set a role by array
     * @param array $array The role's info
     * @param string|null $name The role name (for overwrite the array info)
     * @throws \Exception Name must exist
     * @return Role The role
     */
    public static function fromArray(array $array, $name = null)
    {
        if ($name === null && !array_key_exists('name', $array)) {
            throw new \Exception("You must give a name for the role", E_USER_ERROR);
        }

        // Get info
        $name = ($name !== null) ? $name : $array['name'];
        $access = (array_key_exists('access', $array)) ? $array['access'] : array();
        $parents = (array_key_exists('parents', $array)) ? $array['parents'] : array();

        // Create role
        $role = new Role($name, $parents);
        $role->setAccess($access);

        return $role;
    }

    /**
     * Add access info
     * @param Resource $resource The resource
     * @param boolean $access Allow or deny
     * @return Role The role
     */
    private function addAccess(Resource $resource, $access)
    {
        $scope = $resource->getScope();
        $name = $resource->getName();
        $this->access[$scope][$name] = $access;
        return $this;
    }

    /**
     * Set the access array
     * @param array $access The access
     * @see fromArray()
     */
    private function setAccess(array $access)
    {
        $this->access = $access;
    }
}
