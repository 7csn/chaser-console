<?php

namespace chaser\console;

use chaser\console\command\CommandInterface;
use chaser\console\command\HelpCommand;
use chaser\console\command\ListCommand;
use chaser\console\descriptor\Descriptor;
use chaser\console\exception\CommandNotFoundException;
use chaser\console\input\Concrete;
use chaser\console\input\Definition;
use chaser\console\input\Input;
use chaser\console\input\InputInterface;
use chaser\console\output\Formatter;
use chaser\console\output\Output;
use chaser\console\output\OutputInterface;
use chaser\console\argument\Option;
use Throwable;

/**
 * 应用程序类
 *
 * @package chaser\console
 */
class Application
{
    /**
     * 版本号
     */
    public const VERSION = '1.0';

    /**
     * 基础命令库
     *
     * @var CommandInterface[] [命令名 => 命令对象]
     */
    private array $baseCommands = [];

    /**
     * 命令库
     *
     * @var CommandInterface[] [命令名 => 命令对象]
     */
    private array $commands = [];

    /**
     * 默认命令名
     *
     * @var string
     */
    private string $defaultCommand;

    /**
     * 初始化应用程序
     */
    public function __construct()
    {
        $this->addBase(new HelpCommand());

        $list = new ListCommand();
        $this->addBase($list);
        $this->defaultCommand = $list->getName();
    }

    /**
     * 添加命令
     *
     * @param CommandInterface $command
     */
    public function add(CommandInterface $command): void
    {
        $command->setApplication($this);
        $this->commands[$command->getName()] = $command;
    }

    /**
     * 判断命令是否存在
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->baseCommands[$name]) || isset($this->commands[$name]);
    }

    /**
     * 获取指定命令
     *
     * @param string $name
     * @return CommandInterface
     */
    public function get(string $name): CommandInterface
    {
        $command = $this->baseCommands[$name] ?? $this->commands[$name] ?? null;

        if ($command === null) {
            throw new CommandNotFoundException(sprintf('Command "%s" is not defined.', $name));
        }

        return $command;
    }

    /**
     * 启动应用程序
     *
     * @param InputInterface|null $input
     * @param OutputInterface|null $output
     * @return int
     */
    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        if ($input === null) {
            $input = new Input();
        }

        $concrete = $this->getDefinition()->resolve($input);

        if ($output === null) {
            $output = new Output(new Formatter());
        }

        $this->outputSetting($concrete, $output);

        if ($input->hasParameter(0)) {
            try {
                $command = $this->get($input->getParameterValue(0));
                $code = $command->run($input->clone(1), $output);
                return self::normalizeCode($code);
            } catch (Throwable $exception) {
                return $this->exception($exception, $output);
            }
        }

        $descriptor = new Descriptor($output);
        $descriptor->describeApplication($this);
        return 0;
    }

    /**
     * 获取应用名称
     *
     * @return string
     */
    public function getName(): string
    {
        return sprintf('Chaser console <info>v%s</info>', self::VERSION);
    }

    /**
     * 获取基础指令列表
     *
     * @return CommandInterface[]
     */
    public function getBaseCommands(): array
    {
        return $this->baseCommands;
    }

    /**
     * 获取自定义指令列表
     *
     * @param string $prefix
     * @return CommandInterface[]
     */
    public function getCommands(string $prefix = ''): array
    {
        $commands = $prefix === ''
            ? $this->commands
            : array_filter($this->commands, fn($name) => str_starts_with($name, $prefix), ARRAY_FILTER_USE_KEY);
        ksort($commands);

        return $commands;
    }

    /**
     * 获取输入定义
     *
     * @param bool $withParameters
     * @return Definition
     */
    public function getDefinition(): Definition
    {
        return new Definition([
            new Option('output', 'O', Option::OPTIONAL, "Output setting: 0(decorate tags), 1(strip tags), 2(raw output), 3(no output)", '0')
        ]);
    }

    /**
     * 添加基础命令
     *
     * @param CommandInterface $command
     */
    private function addBase(CommandInterface $command): void
    {
        $command->setApplication($this);
        $this->baseCommands[$command->getName()] = $command;
    }

    /**
     * 输出设置
     *
     * @param Concrete $concrete
     * @param OutputInterface $output
     */
    private function outputSetting(Concrete $concrete, OutputInterface $output): void
    {
        switch ($concrete->getOption('output')) {
            case 0:
                $output->setMode(OutputInterface::OUTPUT_DECORATE);
                break;
            case 1:
                $output->setMode(OutputInterface::OUTPUT_PLAIN);
                break;
            case 2:
                $output->setMode(OutputInterface::OUTPUT_RAW);
                break;
            case 3:
                $output->setMode(OutputInterface::OUTPUT_QUIET);
                break;
        }
    }

    /**
     * 异常处理
     *
     * @param Throwable $exception
     * @param OutputInterface $output
     * @return int
     */
    private function exception(Throwable $exception, OutputInterface $output): int
    {
        $output->writeln(sprintf("<error>\nException: %s</error>", $exception->getMessage()));
        return self::normalizeCode($exception->getCode(), true);
    }

    /**
     * 标准化执行码
     *
     * @param mixed $code
     * @param bool $error
     * @return int
     */
    private static function normalizeCode(mixed $code, bool $error = false): int
    {
        $code = (int)$code;

        $min = $error ? 1 : 0;

        if ($code > 255) {
            $code = 255;
        } elseif ($code < $min) {
            $code = $min;
        }

        return $code;
    }
}
