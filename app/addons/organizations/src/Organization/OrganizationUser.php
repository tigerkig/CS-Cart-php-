<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/


namespace Tygh\Addons\Organizations\Organization;

use Serializable;

/**
 * Class OrganizationUser
 *
 * @package Tygh\Addons\Organizations\Organization
 */
class OrganizationUser implements Serializable
{
    /** @var string  */
    const ROLE_OWNER = 'owner';

    /** @var string  */
    const ROLE_REPRESENTATIVE = 'representative';

    /** @var int */
    protected $user_id;

    /** @var int */
    protected $organization_id;

    /** @var string */
    protected $role;

    /** @var string */
    protected $name;

    /** @var null|\Tygh\Addons\Organizations\Organization\Organization */
    protected $organization;

    /**
     * OrganizationUser constructor.
     *
     * @param int|null    $organization_id
     * @param int|null    $user_id
     * @param string|null $role
     */
    public function __construct($organization_id = null, $user_id = null, $role = null)
    {
        if ($organization_id) {
            $this->setOrganizationId($organization_id);
        }

        if ($user_id) {
            $this->setUserId($user_id);
        }

        if ($role) {
            $this->setRole($role);
        }
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = (int) $user_id;
    }

    /**
     * @return int
     */
    public function getOrganizationId()
    {
        return $this->organization_id;
    }

    /**
     * @param int $organization_id
     */
    public function setOrganizationId($organization_id)
    {
        $this->organization_id = (int) $organization_id;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param string $role
     */
    public function setRole($role)
    {
        if (!in_array($role, [self::ROLE_OWNER, self::ROLE_REPRESENTATIVE], true)) {
            $role = self::ROLE_REPRESENTATIVE;
        }

        $this->role = $role;
    }

    /**
     * @return bool
     */
    public function isOwner()
    {
        return $this->role === self::ROLE_OWNER;
    }

    /**
     * @return \Tygh\Addons\Organizations\Organization\Organization|null
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param \Tygh\Addons\Organizations\Organization\Organization|null $organization
     */
    public function setOrganization(Organization $organization)
    {
        $this->organization = $organization;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'organization_id' => $this->getOrganizationId(),
            'user_id'       => $this->getUserId(),
            'role'          => $this->getRole()
        ];
    }

    /**
     * @param array $data
     *
     * @return \Tygh\Addons\Organizations\Organization\OrganizationUser
     */
    public static function createFromArray(array $data)
    {
        $self = new OrganizationUser();

        if (array_key_exists('organization_id', $data)) {
            $self->setOrganizationId($data['organization_id']);
        }

        if (array_key_exists('user_id', $data)) {
            $self->setUserId($data['user_id']);
        }

        if (array_key_exists('role', $data)) {
            $self->setRole($data['role']);
        }

        return $self;
    }

    /** @inheritdoc */
    public function serialize()
    {
        // Disabling serialization to avoid causing an error at session data deserialization phase.
        return null;
    }

    /** @inheritdoc */
    public function unserialize($serialized)
    {
    }
}