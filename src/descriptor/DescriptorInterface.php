<?php

namespace chaser\console\descriptor;

use chaser\console\Application;
use chaser\console\argument\Option;
use chaser\console\argument\Parameter;
use chaser\console\command\CommandInterface;
use chaser\console\input\Definition;

/**
 * 描述器接口
 *
 * @package chaser\console\descriptor
 */
interface DescriptorInterface
{
    /**
     * 描述应用程序
     *
     * @param Application $application
     */
    public function describeApplication(Application $application): void;

    /**
     * 展示应用程序基本指令
     *
     * @param Application $application
     */
    public function listBaseCommands(Application $application): void;

    /**
     * 展示指定命名空间下的指令
     *
     * @param Application $application
     * @param string|null $namespace
     */
    public function listCommands(Application $application, string $namespace = null): void;

    /**
     * 描述命令
     *
     * @param CommandInterface $command
     */
    public function describeCommand(CommandInterface $command): void;

    /**
     * 描述输入定义
     *
     * @param Definition $definition
     */
    public function describeDefinition(Definition $definition): void;

    /**
     * 描述位置参数
     *
     * @param Parameter $parameter
     */
    public function describeParameter(Parameter $parameter): void;

    /**
     * 描述选项
     *
     * @param Option $option
     */
    public function describeOption(Option $option): void;
}
