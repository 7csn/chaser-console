<?php

namespace chaser\console\argument;

/**
 * 位置参数类
 *
 * @package chaser\console\argument
 */
class Parameter implements ArgumentInterface
{
    use Argument;

    /**
     * 初始化参数信息
     *
     * @param string $name
     * @param int $mode
     * @param string $description
     * @param string ...$defaults
     */
    public function __construct(private string $name, private int $mode = 0, private string $description = '', string ...$defaults)
    {
        $this->checkName();
        $this->checkMode();

        $this->setDefault(...$defaults);
    }
}
