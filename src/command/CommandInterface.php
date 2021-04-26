<?php

namespace chaser\console\command;

use chaser\console\Application;
use chaser\console\input\Concrete;
use chaser\console\input\Definition;
use chaser\console\input\InputInterface;
use chaser\console\output\OutputInterface;

/**
 * 命令接口
 *
 * @package chaser\console\command
 */
interface CommandInterface
{
    /**
     * 设置名称
     *
     * @param string $name
     */
    public function setName(string $name): void;

    /**
     * 获取名称
     *
     * @return string
     */
    public function getName(): string;

    /**
     * 设置说明
     *
     * @param string $description
     */
    public function setDescription(string $description): void;

    /**
     * 获取说明
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * 设置应用程序
     *
     * @param Application $application
     */
    public function setApplication(Application $application): void;

    /**
     * 获取应用程序
     *
     * @return Application|null
     */
    public function getApplication(): ?Application;

    /**
     * 添加使用实例
     *
     * @param string $usage
     */
    public function addUsage(string $usage): void;

    /**
     * 获取使用实例列表
     *
     * @return string[]
     */
    public function getUsages(): array;

    /**
     * 获取摘要
     *
     * @param bool $short
     * @return string
     */
    public function getSynopsis(bool $short = false): string;

    /**
     * 获取输入定义
     *
     * @return Definition
     */
    public function getDefinition(): Definition;

    /**
     * 获取完全（合并应用程序）输入定义
     *
     * @param bool $withParameters
     * @return Definition
     */
    public function getDefinitionWithApplication(bool $withParameters): Definition;

    /**
     * 获取输入实体
     *
     * @param InputInterface $input
     * @return Concrete
     */
    public function getConcrete(InputInterface $input): Concrete;

    /**
     * 运行命令
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output): int;
}
