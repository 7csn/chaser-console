<?php

namespace chaser\console\input;

use chaser\console\argument\Option;

/**
 * 输入接口
 *
 * @package chaser\console\input
 */
interface InputInterface
{
    /**
     * 获取参数组
     *
     * @return string[]
     */
    public function getParameters(): array;

    /**
     * 获取长选项数组
     *
     * @return string[][]
     */
    public function getLongOptions(): array;

    /**
     * 获取短选项数组
     *
     * @return string[][]
     */
    public function getShortOptions(): array;

    /**
     * 判断是否含指定位置参数
     *
     * @param int $position
     * @return bool
     */
    public function hasParameter(int $position): bool;

    /**
     * 获取位置参数值
     *
     * @param int $position
     * @param bool $complex
     * @return array|string|null
     */
    public function getParameterValue(int $position, bool $complex = false): array|string|null;

    /**
     * 判断是否含有指定选项
     *
     * @param Option $option
     * @return bool
     */
    public function hasOption(Option $option): bool;

    /**
     * 获取选项值列表
     *
     * @param Option $option
     * @return string[]|null
     */
    public function getOptionValues(Option $option): ?array;

    /**
     * 生成新的输入对象
     *
     * @param int $start
     * @return Input
     */
    public function clone(int $start = 0): self;
}
