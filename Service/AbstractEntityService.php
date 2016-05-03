<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Service;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use MobileCart\CoreBundle\Constants\EntityConstants;

abstract class AbstractEntityService implements UserProviderInterface
{
    /**
     * @var array repos
     */
    protected $repos = [];

    /**
     * @var array
     */
    protected $productTypes = [];

    /**
     * @param $email
     * @return mixed
     */
    public function loadUserByUsername($email)
    {
        $customer = $this->findOneBy(EntityConstants::CUSTOMER, [
            'email' => $email,
        ]);

        if ($customer) {
            return $customer;
        }

        return $this->findOneBy(EntityConstants::ADMIN_USER, [
            'email' => $email,
        ]);
    }

    /**
     * @param UserInterface $user
     * @return mixed|UserInterface
     * @throws \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    $class
                )
            );
        }

        if ($class === $this->getRepository(EntityConstants::CUSTOMER)->getClassName()) {
            return $this->find(EntityConstants::CUSTOMER, $user->getId());
        }

        return $this->find(EntityConstants::ADMIN_USER, $user->getId());
    }

    /**
     * @param $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return in_array($class, [
            $this->getRepository(EntityConstants::CUSTOMER)->getClassName(),
            $this->getRepository(EntityConstants::ADMIN_USER)->getClassName()
        ]);
    }

    /**
     * @param $key
     * @return mixed
     */
    abstract public function getRepository($key);

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function addObjectRepository($key, $value)
    {
        $this->repos[$key] = $value;
        return $this;
    }

    /**
     * @param $key
     * @return string
     */
    public function getObjectRepository($key)
    {
        return isset($this->repos[$key])
            ? $this->repos[$key]
            : '';
    }

    /**
     * @return array
     */
    public function getObjectRepositories()
    {
        return $this->repos;
    }

    /**
     * @param array $repos
     * @return $this
     */
    public function setObjectRepositories(array $repos)
    {
        $this->repos = $repos;
        return $this;
    }

    /**
     * @param $key
     * @param $label
     * @return $this
     */
    public function addProductType($key, $label)
    {
        $this->productTypes[$key] = $label;
        return $this;
    }

    /**
     * @return array
     */
    public function getProductTypes()
    {
        return $this->productTypes;
    }

    /**
     * @param $str
     * @return mixed
     */
    public function slugify($str)
    {
        return str_replace('--', '-', strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $str))));
    }

    /**
     * @param $objectType
     * @return array
     */
    abstract public function getObjectTypeItemVars($objectType);

    /**
     * Create an Instance
     *
     * @param $objectType
     * @return mixed
     */
    abstract public function getInstance($objectType);

    /**
     * @param string $objectType
     * @param int $id
     * @return mixed
     */
    abstract public function find($objectType, $id);

    /**
     * @param $objectType
     * @param array $params
     * @param array $orderBy
     * @param null $limit
     * @param null $offset
     * @return mixed
     */
    abstract public function findBy($objectType, $params = [], array $orderBy = null, $limit = null, $offset = null);

    /**
     * @param $objectType
     * @param array $params
     * @param array $orderBy
     * @return mixed
     */
    abstract public function findOneBy($objectType, array $params, array $orderBy = null);

    /**
     * @param $objectType
     * @return array
     */
    abstract public function findAll($objectType);

    /**
     * @param $entity
     * @param string $objectType
     * @return mixed
     */
    abstract public function remove($entity, $objectType = '');

    /**
     * @param $entity
     * @param string $objectType
     * @return mixed
     */
    abstract public function persist($entity, $objectType = '');
}
