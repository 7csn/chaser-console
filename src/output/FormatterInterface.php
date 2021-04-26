<?php

namespace chaser\console\output;

/**
 * 格式器接口
 *
 * @package chaser\console\output
 */
interface FormatterInterface
{
    /**
     * 设置装饰是否启用
     *
     * @param bool $decorated
     */
    public function setDecorated(bool $decorated): void;

    /**
     * 返回装饰是否启用
     *
     * @return bool
     */
    public function isDecorated(): bool;

    /**
     * 设置样式
     *
     * @param string $name
     * @param StyleInterface $style
     */
    public function setStyle(string $name, StyleInterface $style): void;

    /**
     * 判断样式是否存在
     *
     * @param string $name
     * @return bool
     */
    public function hasStyle(string $name): bool;

    /**
     * 获取样式
     *
     * @param string $name
     * @return StyleInterface|null
     */
    public function getStyle(string $name): ?StyleInterface;

    /**
     * 格式化消息
     *
     * @param string $message
     * @return string
     */
    public function format(string $message = ''): string;
}
