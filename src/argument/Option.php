<?php

namespace chaser\console\argument;

use chaser\console\exception\InvalidArgumentException;
use chaser\console\exception\LogicException;

/**
 * 选项类
 *
 * @package chaser\console\argument
 */
class Option implements ArgumentInterface
{
    use Argument {
        getMaxMode as baseGetMaxMode;
        setDefault as baseSetDefault;
    }

    /**
     * 模式：值可选择是否提供
     */
    public const OPTIONAL = 0b100;

    /**
     * 初始化选项信息
     *
     * @param string $name
     * @param string|null $shortcut
     * @param int $mode
     * @param string $description
     * @param string ...$defaults
     */
    public function __construct(private string $name, private ?string $shortcut = null, private int $mode = 0, private string $description = '', string ...$defaults)
    {
        $this->checkName();
        $this->checkMode();
        $this->checkShortcut();

        $this->setDefault(...$defaults);
    }

    /**
     * 返回快捷名
     *
     * @return string|null
     */
    public function getShortcut(): ?string
    {
        return $this->shortcut;
    }

    /**
     * 返回是否可选值
     *
     * @return bool
     */
    public function isOptional(): bool
    {
        return self::OPTIONAL === (self::OPTIONAL & $this->mode);
    }

    /**
     * 返回是否可接受值
     *
     * @return bool
     */
    public function acceptValue(): bool
    {
        return $this->isRequired() || $this->isOptional();
    }

    /**
     * 检测快捷名
     */
    private function checkShortcut(): void
    {
        if ($this->shortcut !== null) {
            if (!preg_match('/^[^\s-]$/', $this->shortcut)) {
                throw new InvalidArgumentException('The option shortcut must be a non empty character and cannot be "-".');
            }
        }
    }

    /**
     * 检测模式
     */
    private function checkMode(): void
    {
        $this->baseGetMaxMode();

        if ($this->isRequired() && $this->isOptional()) {
            throw new LogicException('Option modes REQUIRED and OPTIONAL cannot coexist.');
        }

        if ($this->isComplex() && !$this->acceptValue()) {
            throw new LogicException('Option mode COMPLEX cannot exist without REQUIRED or OPTIONAL.');
        }
    }

    /**
     * 获取模式理论上的最大值
     *
     * @return int
     */
    protected function getMaxMode(): int
    {
        return self::REQUIRED | self::COMPLEX | self::OPTIONAL;
    }

    /**
     * 设置默认值
     *
     * @param string ...$defaults
     */
    private function setDefault(string ...$defaults): void
    {
        if ($this->isOptional() xor !empty($defaults)) {
            throw new LogicException('If and only if the option mode contains OPTIONAL, the default value is not empty.');
        }

        $this->baseSetDefault(...$defaults);
    }
}
