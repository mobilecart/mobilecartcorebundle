<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;


/**
 * MobileCart\CoreBundle\Entity\AdminUser
 *
 * User Management for the Administration of the Store
 *
 * @ORM\Table(name="admin_user")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\AdminUserRepository")
 */
class AdminUser
    implements AdvancedUserInterface, CartEntityInterface, \Serializable
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $first_name
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     */
    private $first_name;

    /**
     * @var string $last_name
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     */
    private $last_name;

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @var string $hash
     *
     * @ORM\Column(name="hash", type="text", nullable=true)
     */
    private $hash;

    /**
     * @var string $confirm_hash
     *
     * @ORM\Column(name="confirm_hash", type="text", nullable=true)
     */
    private $confirm_hash;

    /**
     * @var int $failed_logins
     *
     * @ORM\Column(name="failed_logins", type="integer", nullable=true)
     */
    private $failed_logins;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="locked_at", type="datetime", nullable=true)
     */
    private $locked_at;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_login_at", type="datetime", nullable=true)
     */
    private $last_login_at;

    /**
     * @var string $api_key
     *
     * @ORM\Column(name="api_key", type="string", length=255, nullable=true)
     */
    private $api_key;

    /**
     * @var boolean $is_enabled
     *
     * @ORM\Column(name="is_enabled", type="boolean", nullable=true)
     */
    private $is_enabled;

    /**
     * @var boolean $is_expired
     *
     * @ORM\Column(name="is_expired", type="boolean", nullable=true)
     */
    private $is_expired;

    /**
     * @var boolean $is_locked
     *
     * @ORM\Column(name="is_locked", type="boolean", nullable=true)
     */
    private $is_locked;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="password_updated_at", type="datetime", nullable=true)
     */
    private $password_updated_at;

    /**
     * @var boolean $is_password_expired
     *
     * @ORM\Column(name="is_password_expired", type="boolean", nullable=true)
     */
    private $is_password_expired;

    public function __toString()
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    public function getObjectTypeKey()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::ADMIN_USER;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        $vars = get_object_vars($this);
        if (array_key_exists($key, $vars)) {
            $this->$key = $value;
        }

        return $this;
    }

    /**
     * @param $data
     * @return $this
     */
    public function fromArray($data)
    {
        if (!$data) {
            return $this;
        }

        foreach($data as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * Lazy-loading getter
     *  ideal for usage in the View layer
     *
     * @param $key
     * @return mixed|null
     */
    public function get($key)
    {
        if (isset($this->$key)) {
            return $this->$key;
        }

        $data = $this->getBaseData();
        if (isset($data[$key])) {
            return $data[$key];
        }

        $data = $this->getData();
        if (isset($data[$key])) {

            if (is_array($data[$key])) {
                return implode(',', $data[$key]);
            }

            return $data[$key];
        }

        return '';
    }

    /**
     * Getter , after fully loading
     *  use only if necessary, and avoid calling multiple times
     *
     * @param string $key
     * @return array|null
     */
    public function getData($key = '')
    {
        $data = $this->getBaseData();

        if (strlen($key) > 0) {

            return isset($data[$key])
                ? $data[$key]
                : null;
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getLuceneVarValuesData()
    {
        // Note:
        // be careful with adding foreign relationships here
        // since it will add 1 query every time an item is loaded

        return $this->getBaseData();
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize($this->getBaseData());
    }

    /**
     * @param string $str
     * @return $this|void
     */
    public function unserialize($str)
    {
        $baseData = $this->getBaseData();
        $data = unserialize($str);
        if (is_array($data) && $data) {
            foreach($data as $k => $v) {
                if (array_key_exists($k, $baseData)) {
                    $this->set($k, $v);
                }
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getBaseData()
    {
        return [
            'id'                  => $this->getId(),
            'email'               => $this->getEmail(),
            // 'hash'                => $this->getHash(),
            // 'confirm_hash'        => $this->getConfirmHash(),
            'name'                => $this->getName(),
            'first_name'          => $this->getFirstName(),
            'last_name'           => $this->getLastName(),
            'failed_logins'       => $this->getFailedLogins(),
            'locked_at'           => $this->getLockedAt(),
            'last_login_at'       => $this->getLastLoginAt(),
            'api_key'             => $this->getApiKey(),
            'is_enabled'          => $this->getIsEnabled(),
            'is_expired'          => $this->getIsExpired(),
            'is_locked'           => $this->getIsLocked(),
            'password_updated_at' => $this->getPasswordUpdatedAt(),
            'is_password_expired' => $this->getIsPasswordExpired(),
        ];
    }

    /**
     * Symfony Security
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * Symfony Security
     *
     * @return null|string
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Symfony Security
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->hash;
    }

    /**
     * Symfony Security
     *
     * @return $this
     */
    public function eraseCredentials()
    {
        //$this->hash = '';
        return $this;
    }

    /**
     * @return array|\Symfony\Component\Security\Core\Role\Role[]
     */
    public function getRoles()
    {
        return ['ROLE_ADMIN'];
    }

    /**
     * @return bool|void
     */
    public function isAccountNonExpired()
    {
        return !$this->getIsExpired();
    }

    /**
     * @return bool
     */
    public function isAccountNonLocked()
    {
        return !$this->getIsLocked();
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getIsEnabled();
    }

    /**
     * @return bool
     */
    public function isCredentialsNonExpired()
    {
        return !$this->getIsPasswordExpired();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    /**
     * Set first_name
     *
     * @param string $firstName
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;
        return $this;
    }

    /**
     * Get first_name
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set last_name
     *
     * @param string $lastName
     * @return $this
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;
        return $this;
    }

    /**
     * Get last_name
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set hash
     *
     * @param string $hash
     * @return $this
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * Get hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set confirm_hash
     *
     * @param string $confirmHash
     * @return $this
     */
    public function setConfirmHash($confirmHash)
    {
        $this->confirm_hash = $confirmHash;
        return $this;
    }

    /**
     * Get confirm_hash
     *
     * @return string
     */
    public function getConfirmHash()
    {
        return $this->confirm_hash;
    }

    /**
     * @param $failedLogins
     * @return $this
     */
    public function setFailedLogins($failedLogins)
    {
        $this->failed_logins = $failedLogins;
        return $this;
    }

    /**
     * @return int
     */
    public function getFailedLogins()
    {
        return $this->failed_logins;
    }

    /**
     * @param $lockedAt
     * @return $this
     */
    public function setLockedAt($lockedAt)
    {
        $this->locked_at = $lockedAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLockedAt()
    {
        return $this->locked_at;
    }

    /**
     * @param $lastLoginAt
     * @return $this
     */
    public function setLastLoginAt($lastLoginAt)
    {
        $this->last_login_at = $lastLoginAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastLoginAt()
    {
        return $this->last_login_at;
    }

    /**
     * @param $apiKey
     * @return $this
     */
    public function setApiKey($apiKey)
    {
        $this->api_key = $apiKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->api_key;
    }

    /**
     * @param $isEnabled
     * @return $this
     */
    public function setIsEnabled($isEnabled)
    {
        $this->is_enabled = $isEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsEnabled()
    {
        return $this->is_enabled;
    }

    /**
     * @param $isExpired
     * @return $this
     */
    public function setIsExpired($isExpired)
    {
        $this->is_expired = $isExpired;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsExpired()
    {
        return $this->is_expired;
    }

    /**
     * @param $isLocked
     * @return $this
     */
    public function setIsLocked($isLocked)
    {
        $this->is_locked = $isLocked;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsLocked()
    {
        return $this->is_locked;
    }

    /**
     * @param $passwordUpdatedAt
     * @return $this
     */
    public function setPasswordUpdatedAt($passwordUpdatedAt)
    {
        $this->password_updated_at = $passwordUpdatedAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getPasswordUpdatedAt()
    {
        return $this->password_updated_at;
    }

    /**
     * @param $isPasswordExpired
     * @return $this
     */
    public function setIsPasswordExpired($isPasswordExpired)
    {
        $this->is_password_expired = $isPasswordExpired;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsPasswordExpired()
    {
        return $this->is_password_expired;
    }
}
