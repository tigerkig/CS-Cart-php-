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


use Tygh\Addons\Organizations\Tools\QueryFactory;

/**
 * Class OrganizationUserRepository
 *
 * @package Tygh\Addons\Organizations\Organization
 */
class OrganizationUserRepository
{
    /** @var string  */
    const TABLE_NAME_ORGANIZATION_USER = 'organization_user';

    /** @var string  */
    const TABLE_NAME_USERS = 'users';

    /** @var \Tygh\Addons\Organizations\Tools\QueryFactory  */
    protected $query_factory;

    /**
     * OrganizationUserRepository constructor.
     *
     * @param \Tygh\Addons\Organizations\Tools\QueryFactory $query_factory
     */
    public function __construct(QueryFactory $query_factory)
    {
        $this->query_factory = $query_factory;
    }

    /**
     * @param \Tygh\Addons\Organizations\Organization\OrganizationUser $organization_user
     *
     * @return \Tygh\Addons\Organizations\Organization\OrganizationUser
     */
    public function save(OrganizationUser $organization_user)
    {
        $query = $this->query_factory->createQuery(self::TABLE_NAME_ORGANIZATION_USER);
        $query->replace($organization_user->toArray());

        return $organization_user;
    }

    /**
     * @param int $user_id
     *
     * @return \Tygh\Addons\Organizations\Organization\OrganizationUser|null
     */
    public function findByUserId($user_id)
    {
        $user_id = (int) $user_id;

        if (!$user_id) {
            return null;
        }

        $query = $this->query_factory->createQuery(self::TABLE_NAME_ORGANIZATION_USER, [
            'user_id' => $user_id
        ]);
        $query->addField('*');

        $data = $query->row();

        if (!$data) {
            return null;
        }

        return $this->createOrganizationUser($data);
    }

    /**
     * @param int[] $user_ids
     *
     * @return \Tygh\Addons\Organizations\Organization\OrganizationUser[]
     */
    public function findByUserIds(array $user_ids)
    {
        /** @var \Tygh\Addons\Organizations\Organization\OrganizationUser[] $result */
        $result = [];
        $query = $this->query_factory->createQuery(self::TABLE_NAME_ORGANIZATION_USER, [
            'user_id' => $user_ids
        ]);
        $query->addField('*');

        foreach ($query->select() as $item) {
            $organization_user = $this->createOrganizationUser($item);
            $result[$organization_user->getUserId()] = $organization_user;
        }

        return $result;
    }

    /**
     * @param $organization_id
     *
     * @return \Tygh\Addons\Organizations\Organization\OrganizationUser[]
     */
    public function findUsersByORganizationId($organization_id)
    {
        $organization_id = (int) $organization_id;

        if (!$organization_id) {
            return [];
        }

        $result = [];
        $query = $this->query_factory->createQuery(self::TABLE_NAME_ORGANIZATION_USER, [
            'organization_id' => $organization_id
        ]);
        $query->addField('*');

        foreach ($query->select() as $item) {
            $organization_user = $this->createOrganizationUser($item);
            $result[$organization_user->getUserId()] = $organization_user;
        }

        return $result;
    }

    /**
     * @param int $organization_id
     *
     * @return \Tygh\Addons\Organizations\Organization\OrganizationUser|null
     */
    public function findOwnerByOrganizationId($organization_id)
    {
        if (!$organization_id) {
            return null;
        }

        $query = $this->query_factory->createQuery(self::TABLE_NAME_ORGANIZATION_USER, [
            'role'          => OrganizationUser::ROLE_OWNER,
            'organization_id' => $organization_id
        ]);
        $query->addField('*');

        $data = $query->row();

        if (!$data) {
            return null;
        }

        return $this->createOrganizationUser($data);
    }

    /**
     * @param int[] $organization_ids
     *
     * @return array
     */
    public function findOwnersByOrganizationIds(array $organization_ids)
    {
        if (!$organization_ids) {
            return [];
        }

        $result = [];
        $query = $this->query_factory->createQuery(self::TABLE_NAME_ORGANIZATION_USER, [
            'role'          => OrganizationUser::ROLE_OWNER,
            'organization_id' => $organization_ids
        ]);
        $query->addField('*');

        foreach ($query->select() as $item) {
            $organization_user = $this->createOrganizationUser($item);
            $result[$organization_user->getOrganizationId()] = $organization_user;
        }

        $this->loadUserName($result);

        return $result;
    }

    /**
     * @param \Tygh\Addons\Organizations\Organization\OrganizationUser[] $organization_users
     */
    public function loadUserName(array $organization_users)
    {
        $user_ids = array_map(function (OrganizationUser $user) {
            return $user->getUserId();
        }, $organization_users);

        $query = $this->query_factory->createQuery(self::TABLE_NAME_USERS, [
            'user_id' => $user_ids
        ]);
        $query->setFields(['user_id', 'firstname', 'lastname']);
        $users = $query->select('user_id');

        foreach ($organization_users as $organization_user) {
            if (!isset($users[$organization_user->getUserId()])) {
                $organization_user->setName('');
            } else {
                $organization_user->setName(trim(sprintf('%s %s',
                    $users[$organization_user->getUserId()]['firstname'],
                    $users[$organization_user->getUserId()]['lastname']
                )));
            }
        }
    }

    /**
     * @param int $organization_id
     */
    public function deleteByOrganizationId($organization_id)
    {
        $query = $this->query_factory->createQuery(self::TABLE_NAME_ORGANIZATION_USER, [
            'organization_id' => $organization_id
        ]);

        $query->delete();
    }

    /**
     * @param int $user_id
     */
    public function deleteByUserId($user_id)
    {
        $query = $this->query_factory->createQuery(self::TABLE_NAME_ORGANIZATION_USER, [
            'user_id' => $user_id
        ]);

        $query->delete();
    }

    /**
     * @param array $data
     *
     * @return \Tygh\Addons\Organizations\Organization\OrganizationUser
     */
    protected function createOrganizationUser(array $data)
    {
        return OrganizationUser::createFromArray($data);
    }
}