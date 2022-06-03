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


use Tygh\Addons\Organizations\Enum\ProfileTypes;
use Tygh\Addons\Organizations\Tools\Query;
use Tygh\Addons\Organizations\Tools\QueryFactory;

/**
 * Class OrganizationRepository
 *
 * @package Tygh\Addons\Organizations\Organization
 */
class OrganizationRepository
{
    const TABLE_NAME_ORGANIZATION = 'organization';
    const TABLE_NAME_PROFILE_FIELDS_DATA = 'profile_fields_data';

    /** @var \Tygh\Addons\Organizations\Tools\QueryFactory  */
    protected $query_factory;

    /** @var \Tygh\Addons\Organizations\Organization\OrganizationUserRepository  */
    protected $organization_user_repository;

    /**
     * OrganizationRepository constructor.
     *
     * @param \Tygh\Addons\Organizations\Tools\QueryFactory                    $query_factory
     * @param \Tygh\Addons\Organizations\Organization\OrganizationUserRepository $organization_user_repository
     */
    public function __construct(QueryFactory $query_factory, OrganizationUserRepository $organization_user_repository)
    {
        $this->query_factory = $query_factory;
        $this->organization_user_repository = $organization_user_repository;
    }

    /**
     * @param \Tygh\Addons\Organizations\Organization\Organization $organization
     *
     * @return \Tygh\Addons\Organizations\Organization\Organization
     * @throws \Exception
     */
    public function save(Organization $organization)
    {
        $query = $this->query_factory->createQuery(self::TABLE_NAME_ORGANIZATION);

        if ($organization->getOrganizationId()) {
            $organization->setUpdatedAt(new \DateTime('now'));

            $query->addConditions([
                'organization_id' => $organization->getOrganizationId()
            ]);
            $query->update($organization->toArray());
        } else {
            $organization->setCreatedAt(new \DateTime('now'));
            $organization->setUpdatedAt(new \DateTime('now'));

            $organization_id = $query->insert($organization->toArray());

            $organization->setOrganizationId($organization_id);
        }

        if ($organization->getOrganizationId()) {
            fn_store_profile_fields(
                ['fields' => $organization->getFields()],
                $organization->getOrganizationId(),
                ProfileTypes::CODE_ORGANIZATION
            );
        }

        return $organization;
    }

    /**
     * @param int $organization_id
     */
    public function deleteById($organization_id)
    {
        $query = $this->query_factory->createQuery(self::TABLE_NAME_ORGANIZATION, [
            'organization_id' => $organization_id
        ]);

        $query->delete();

        fn_delete_profile_fields_data(ProfileTypes::CODE_ORGANIZATION, $organization_id);

        $this->organization_user_repository->deleteByOrganizationId($organization_id);
    }

    /**
     * @param int  $organization_id
     * @param bool $load_fields_values
     * @param bool $load_owner_user
     *
     * @return \Tygh\Addons\Organizations\Organization\Organization|null
     */
    public function findById($organization_id, array $params = [])
    {
        $organization_id = (int) $organization_id;

        if (!$organization_id) {
            return null;
        }

        $organization = $this->findAll([
            'organization_id' => $organization_id
        ]);

        if (!$organization) {
            return null;
        }

        $organization = reset($organization);

        if (!empty($params['load_fields_values'])) {
            $this->loadFieldsValues($organization);
        }

        if (!empty($params['load_owner_user'])) {
            $this->loadOrganizationOwnerUser([$organization]);
        }

        return $organization;
    }

    /**
     * @param int[] $organization_ids
     *
     * @return \Tygh\Addons\Organizations\Organization\Organization[]
     */
    public function findAllByIds(array $organization_ids)
    {
        if (!$organization_ids) {
            return [];
        }

        return $this->findAll([
            'organization_id' => $organization_ids
        ]);
    }

    /**
     * @param array $conditions
     * @param null|int  $limit
     * @param null|int  $offset
     *
     * @return \Tygh\Addons\Organizations\Organization\Organization[]
     */
    public function findAll(array $conditions, array $params = [])
    {
        $result = [];
        $query = $this->query_factory->createQuery([self::TABLE_NAME_ORGANIZATION => 'organization']);
        $query->addField('*');

        $this->buildConditions($query, $conditions);

        if (isset($params['limit'])) {
            $query->setLimit($params['limit']);
        }

        if (isset($params['offset'])) {
            $query->setOffset($params['offset']);
        }

        if (isset($params['sort_by'])) {
            $query->setOrderBy($params['sort_by']);
        }

        foreach ($query->select() as $item) {
            $organization = $this->createOrganization($item);
            $result[$organization->getOrganizationId()] = $organization;
        }

        if (!empty($params['load_organization_user'])) {
            $this->loadOrganizationOwnerUser($result);
        }

        return $result;
    }

    /**
     * @param array $conditions
     *
     * @return int
     */
    public function count(array $conditions)
    {
        $query = $this->query_factory->createQuery([self::TABLE_NAME_ORGANIZATION => 'organization']);
        $query->addField('COUNT(*) AS cnt');

        $this->buildConditions($query, $conditions);

        return (int) $query->scalar();
    }

    /**
     * @param \Tygh\Addons\Organizations\Organization\Organization $organization
     */
    public function loadFieldsValues(Organization $organization)
    {
        $organization->setFields(
            fn_get_profile_fields_data(ProfileTypes::CODE_ORGANIZATION, $organization->getOrganizationId())
        );
    }

    /**
     * @param \Tygh\Addons\Organizations\Organization\Organization[] $organizations
     */
    public function loadOrganizationOwnerUser(array $organizations)
    {
        if (!$organizations) {
            return;
        }

        $organization_ids = array_map(function (Organization $organization) {
            return $organization->getOrganizationId();
        }, $organizations);

        $organization_users = $this->organization_user_repository->findOwnersByOrganizationIds($organization_ids);

        foreach ($organizations as $organization) {
            if (!isset($organization_users[$organization->getOrganizationId()])) {
                continue;
            }

            $organization->setOwnerUser($organization_users[$organization->getOrganizationId()]);
        }
    }

    /**
     * @param \Tygh\Addons\Organizations\Organization\OrganizationUser $user
     */
    public function deleteUser(OrganizationUser $user)
    {
        $this->organization_user_repository->deleteByUserId($user->getUserId());

        if ($user->isOwner()) {
            $organization_users = $this->organization_user_repository->findUsersByORganizationId($user->getOrganizationId());

            /** @var \Tygh\Addons\Organizations\Organization\OrganizationUser $old_organization_user */
            $organization_user = reset($organization_users);

            if ($organization_user) {
                $organization_user->setRole(OrganizationUser::ROLE_OWNER);
                $this->organization_user_repository->save($organization_user);
            }
        }
    }

    /**
     * @param \Tygh\Addons\Organizations\Organization\OrganizationUser $user
     */
    public function addUser(OrganizationUser $user)
    {
        $organization = $this->findById($user->getOrganizationId(), [
            'load_owner_user' => true
        ]);

        if (!$organization) {
            return;
        }

        if (!$organization->getOwnerUser()->getUserId()) {
            $user->setRole(OrganizationUser::ROLE_OWNER);
        } elseif ($user->isOwner()) {
            $user->setRole(OrganizationUser::ROLE_REPRESENTATIVE);
        }

        $this->organization_user_repository->save($user);
    }

    /**
     * @param \Tygh\Addons\Organizations\Organization\Organization     $organization
     * @param \Tygh\Addons\Organizations\Organization\OrganizationUser $new_owner_user
     */
    public function changeOwner(Organization $organization, OrganizationUser $new_owner_user)
    {
        $old_user = $this->organization_user_repository->findByUserId($new_owner_user->getUserId());

        if ($old_user) {
            $this->deleteUser($old_user);
        }

        if ($organization->getOwnerUser()->getUserId()) {
            $this->organization_user_repository->deleteByUserId($organization->getOwnerUser()->getUserId());
        }

        $new_owner_user->setOrganizationId($organization->getOrganizationId());
        $new_owner_user->setRole(OrganizationUser::ROLE_OWNER);
        $organization->setOwnerUser($new_owner_user);

        $this->organization_user_repository->deleteByUserId($organization->getOwnerUser()->getUserId());
        $this->organization_user_repository->save($new_owner_user);
    }

    /**
     * @param \Tygh\Addons\Organizations\Tools\Query $query
     * @param array                                  $conditions
     */
    protected function buildConditions(Query $query, array $conditions)
    {
        if (isset($conditions['organization_id'])) {
            $query->addConditions([
                'organization_id' => $conditions['organization_id']
            ]);
        }

        if (isset($conditions['status'])) {
            $query->addConditions([
                'status' => $conditions['status']
            ]);
        }

        if (isset($conditions['name'])) {
            $query->addConditions([
                'name' => $conditions['name']
            ]);
        }

        if (isset($conditions['owner'])) {
            $query->addInnerJoin(
                'owner',
                OrganizationUserRepository::TABLE_NAME_ORGANIZATION_USER,
                ['organization_id' => 'organization_id'],
                ['role' => OrganizationUser::ROLE_OWNER]
            );
            $query->addConditions(['user_id' => $conditions['owner']], 'owner');
        }

        if (isset($conditions['search'])) {
            $query->addCondition('organization.name LIKE ?l', [sprintf('%%%s%%', $conditions['search'])]);
        }
    }

    /**
     * @param array $data
     *
     * @return \Tygh\Addons\Organizations\Organization\Organization
     * @throws \Exception
     */
    protected function createOrganization(array $data)
    {
        return Organization::createFromArray($data);
    }
}