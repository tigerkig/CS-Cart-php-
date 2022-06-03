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

/**
 * Class Organization
 *
 * @package Tygh\Addons\Organizations\Organization
 */
class Organization
{
    /** @var string  */
    const STATUS_ACTIVE = 'A';

    /** @var string  */
    const STATUS_DISABLED = 'D';

    /** @var int */
    protected $organization_id;

    /** @var string */
    protected $status;

    /** @var string */
    protected $name;

    /** @var string */
    protected $description;

    /** @var \DateTime */
    protected $created_at;

    /** @var \DateTime */
    protected $updated_at;

    /** @var array */
    protected $fields = [];

    /** @var null|\Tygh\Addons\Organizations\Organization\OrganizationUser */
    protected $owner_user;

    /**
     * Organization constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setCreatedAt(new \DateTime('now'));
        $this->setUpdatedAt(new \DateTime('now'));
        $this->setOwnerUser(new OrganizationUser());
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
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        if (!in_array($status, [self::STATUS_ACTIVE, self::STATUS_DISABLED], true)) {
            $status = self::STATUS_DISABLED;
        }

        $this->status = $status;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
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
        $this->name = trim((string) $name);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = trim((string) $description);
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param \DateTime $created_at
     */
    public function setCreatedAt($created_at)
    {
        if (is_numeric($created_at)) {
            $this->created_at = \DateTime::createFromFormat('U', $created_at);
        } elseif ($created_at instanceof \DateTime) {
            $this->created_at = $created_at;
        }
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @param \DateTime $updated_at
     */
    public function setUpdatedAt($updated_at)
    {
        if (is_numeric($updated_at)) {
            $this->updated_at = \DateTime::createFromFormat('U', $updated_at);
        } elseif ($updated_at instanceof \DateTime) {
            $this->updated_at = $updated_at;
        }
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return \Tygh\Addons\Organizations\Organization\OrganizationUser|null
     */
    public function getOwnerUser()
    {
        return $this->owner_user;
    }

    /**
     * @param \Tygh\Addons\Organizations\Organization\OrganizationUser|null $owner_user
     */
    public function setOwnerUser(OrganizationUser $owner_user)
    {
        $this->owner_user = $owner_user;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'organization_id' => $this->getOrganizationId(),
            'status'        => $this->getStatus(),
            'name'          => $this->getName(),
            'description'   => $this->getDescription(),
            'created_at'    => $this->getCreatedAt()->getTimestamp(),
            'updated_at'    => $this->getUpdatedAt()->getTimestamp(),
            'fields'        => $this->fields
        ];
    }

    /**
     * @param array $data
     */
    public function merge(array $data)
    {
        if (array_key_exists('status', $data)) {
            $this->setStatus($data['status']);
        }

        if (array_key_exists('name', $data)) {
            $this->setName($data['name']);
        }

        if (array_key_exists('description', $data)) {
            $this->setDescription($data['description']);
        }

        if (array_key_exists('fields', $data)) {
            $this->setFields($data['fields']);
        }
    }

    /**
     * @param array $data
     *
     * @return \Tygh\Addons\Organizations\Organization\Organization
     * @throws \Exception
     */
    public static function createFromArray(array $data)
    {
        $self = new Organization();

        if (array_key_exists('organization_id', $data)) {
            $self->setOrganizationId($data['organization_id']);
        }

        if (array_key_exists('status', $data)) {
            $self->setStatus($data['status']);
        }

        if (array_key_exists('name', $data)) {
            $self->setName($data['name']);
        }

        if (array_key_exists('description', $data)) {
            $self->setDescription($data['description']);
        }

        if (array_key_exists('created_at', $data)) {
            $self->setCreatedAt($data['created_at']);
        }

        if (array_key_exists('updated_at', $data)) {
            $self->setUpdatedAt($data['updated_at']);
        }

        if (array_key_exists('fields', $data)) {
            $self->setFields($data['fields']);
        }

        return $self;
    }

    /**
     * @return array
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_ACTIVE   => __('active'),
            self::STATUS_DISABLED => __('disabled'),
        ];
    }
}