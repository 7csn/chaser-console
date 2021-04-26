<?php

namespace chaser\console\argument;

/**
 * 参数接口
 *
 * @package chaser\console\argument
 */
interface ArgumentInterface
{
    /**
     * 模式：复合值（可多值）
     */
    public const COMPLEX = 0b1;

    /**
     * 模式：必须提供值
     */
    public const REQUIRED = 0b10;

    /**
     * 返回名称
     *
     * @return string
     */
    public function getName(): string;

    /**
     * 返回描述文本
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * 返回默认值
     *
     * @return string[]|string|null
     */
    public function getDefault(): array|string|null;

    /**
     * 返回是否支持多值
     *
     * @return bool
     */
    public function isComplex(): bool;

    /**
     * 返回是否必须提供值
     *
     * @return bool
     */
    public function isRequired(): bool;
}
