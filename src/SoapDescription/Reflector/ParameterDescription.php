<?php declare(strict_types=1);
namespace SoapMiddleware\SoapDescription\Reflector;

class ParameterDescription
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $description;

    /**
     * @var bool
     */
    private $optional;

    /**
     * @var string
     */
    private $defaultValue;

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string)$this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return (string)$this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return (string)$this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * @param null|bool $flag
     * @return bool
     */
    public function isOptional($flag = null): bool
    {
        if ($flag !== null) {
            $this->optional = (bool)$flag;
        }
        return $this->optional;
    }

    /**
     * @return string
     */
    public function getDefaultValue(): string
    {
        return (string)$this->defaultValue;
    }

    /**
     * @param string $defaultValue
     */
    public function setDefaultValue(string $defaultValue)
    {
        $this->defaultValue = $defaultValue;
    }
}
