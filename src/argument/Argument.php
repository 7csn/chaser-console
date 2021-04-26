<?php

namespace chaser\console\argument;

use chaser\console\exception\InvalidArgumentException;
use chaser\console\exception\LogicException;

/**
 * 参数特征
 *
 * @package chaser\console\argument
 */
trait Argument
{
    /**
     * 默认值
     *
     * @var string[]|string|null
     */
    private array|string|null $default;

    /**
     * 返回名称
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * 返回描述文本
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * 返回默认值
     *
     * @return string[]|string|null
     */
    public function getDefault(): array|string|null
    {
        return $this->default;
    }

    /**
     * 返回是否必须提供值
     *
     * @return bool
     */
    public function isRequired(): bool
    {
        return self::REQUIRED === (self::REQUIRED & $this->mode);
    }

    /**
     * 返回是否支持多值
     *
     * @return bool
     */
    public function isComplex(): bool
    {
        return self::COMPLEX === (self::COMPLEX & $this->mode);
    }

    /**
     * 检测名称
     */
    private function checkName(): void
    {
        if ($this->name === '') {
            throw new InvalidArgumentException('The name cannot be empty.');
        }

        if (preg_match('/\s/', $this->name)) {
            throw new InvalidArgumentException('The name cannot contain empty characters.');
        }
    }

    /**
     * 检测模式
     */
    private function checkMode(): void
    {
        if ($this->mode > $this->getMaxMode() || $this->mode < 0) {
            throw new InvalidArgumentException(sprintf('The argument mode "%s" is not valid.', $this->mode));
        }
    }

    /**
     * 获取模式理论上的最大值
     *
     * @return int
     */
    protected function getMaxMode(): int
    {
        return ArgumentInterface::COMPLEX | ArgumentInterface::REQUIRED;
    }

    /**
     * 设置默认值
     *
     * @param string ...$defaults
     */
    private function setDefault(string ...$defaults): void
    {
        if ($this->isRequired() && !empty($defaults)) {
            throw new LogicException('Cannot set a default value when using REQUIRED mode.');
        }

        if (!$this->isComplex() && count($defaults) > 1) {
            throw new LogicException('Multiple default values can only be set when using COMPLEX mode.');
        }

        $this->default = $this->isComplex() ? $defaults ?: null : $defaults[0] ?? null;
    }
}
