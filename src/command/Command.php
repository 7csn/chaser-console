<?php

namespace chaser\console\command;

use chaser\console\Application;
use chaser\console\argument\Option;
use chaser\console\argument\Parameter;
use chaser\console\input\Concrete;
use chaser\console\input\Definition;
use chaser\console\input\InputInterface;

/**
 * 命令类
 *
 * @package chaser\console\command
 */
abstract class Command implements CommandInterface
{
    /**
     * 名称
     *
     * @var string
     */
    private string $name;

    /**
     * 说明
     *
     * @var string
     */
    private string $description;

    /**
     * 输入定义
     *
     * @var Definition|null
     */
    private ?Definition $definition;

    /**
     * 应用程序
     *
     * @var Application|null
     */
    private ?Application $application;

    /**
     * 示例列表
     *
     * @var string[]
     */
    private array $usages = [];

    /**
     * 摘要（简要 + 完整）
     *
     * @var array
     */
    private array $synopsis = [];

    /**
     * 获取默认名称
     *
     * @return string
     */
    abstract public static function getDefaultName(): string;

    /**
     * 获取默认描述
     *
     * @return string
     */
    abstract public static function getDefaultDescription(): string;

    /**
     * 获取输入参数
     *
     * @return <Parameter|Option>[]
     */
    abstract public function getArguments(): array;

    /**
     * 初始化命令
     *
     * @param string|null $name
     */
    public function __construct(string $name = null)
    {
        $name === null || $this->setName($name);

        $this->configure();
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name ??= static::getDefaultName();
    }

    /**
     * @inheritDoc
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return $this->description ??= static::getDefaultDescription();
    }

    /**
     * @inheritDoc
     */
    public function setApplication(Application $application): void
    {
        $this->application = $application;
    }

    /**
     * @inheritDoc
     */
    public function getApplication(): ?Application
    {
        return $this->application;
    }

    /**
     * @inheritDoc
     */
    public function addUsage(string $usage): void
    {
        if (!str_starts_with($usage, $this->getName())) {
            $usage = sprintf('%s %s', $this->getName(), $usage);
        }

        $this->usages[] = $usage;
    }

    /**
     * @inheritDoc
     */
    public function getUsages(): array
    {
        return $this->usages;
    }

    /**
     * @inheritDoc
     */
    public function getSynopsis(bool $short = false): string
    {
        return $this->synopsis[$short] ??= trim(sprintf('%s %s', $this->getName(), $this->getDefinition()->getSynopsis($short)));
    }

    /**
     * @inheritDoc
     */
    public function getDefinition(): Definition
    {
        return $this->definition ??= new Definition($this->getArguments());
    }

    /**
     * @inheritDoc
     */
    public function getDefinitionWithApplication(bool $withParameters): Definition
    {
        $definition = $this->getDefinition();

        if ($this->application === null) {
            return clone $definition;
        }

        $appliedDefinition = $this->application->getDefinition($withParameters);
        $newDefinition = new Definition();

        $newDefinition->setParameters($appliedDefinition->getParameters());
        $newDefinition->addParameters($definition->getParameters());

        $newDefinition->setOptions($definition->getOptions());
        $newDefinition->addOptions($appliedDefinition->getOptions());

        return $newDefinition;
    }

    /**
     * @inheritDoc
     */
    public function getConcrete(InputInterface $input): Concrete
    {
        return $this->getDefinitionWithApplication(true)->resolve($input);
    }

    /**
     * 初始化配置
     */
    protected function configure(): void
    {
    }
}
