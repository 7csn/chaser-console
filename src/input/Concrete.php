<?php

namespace chaser\console\input;

/**
 * 参数实体
 *
 * @package chaser\console\input
 */
class Concrete
{
    /**
     * 初始化实体信息
     *
     * @param string[][]|string[] $parameters 【名称 => 字符串值列表、字符串值、空】
     * @param <string[]|string|null>[] $options 【名称 => 字符串值列表、字符串值、空】
     */
    public function __construct(private array $parameters = [], private array $options = [])
    {
    }

    /**
     * 判断参数是否存在
     *
     * @param string $name
     * @return bool
     */
    public function hasParameter(string $name): bool
    {
        return key_exists($name, $this->parameters);
    }

    /**
     * 获取参数值
     *
     * @param string $name
     * @return string[]|string|null
     */
    public function getParameter(string $name): array|string|null
    {
        return $this->parameters[$name] ?? null;
    }

    /**
     * 判断参数是否存在
     *
     * @param string $name
     * @return bool
     */
    public function hasOption(string $name): bool
    {
        return key_exists($name, $this->options);
    }

    /**
     * 获取参数值
     *
     * @param string $name
     * @return string[]|string|null
     */
    public function getOption(string $name): array|string|null
    {
        return $this->options[$name] ?? null;
    }
}
