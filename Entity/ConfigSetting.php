<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConfigSetting
 *
 * @ORM\Table(name="config_setting")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\ConfigSettingRepository")
 */
class ConfigSetting
    extends AbstractCartEntity
    implements CartEntityInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, unique=true)
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=255, nullable=true)
     */
    protected $label;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text")
     */
    protected $value;

    /**
     * @return string
     */
    public function getObjectTypeKey()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::CONFIG_SETTING;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return ConfigSetting
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return ConfigSetting
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return ConfigSetting
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return array
     */
    public function getBaseData()
    {
        return [
            'code' => $this->getCode(),
            'label' => $this->getLabel(),
            'value' => $this->getValue(),
        ];
    }
}

