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
use chaser\console\argument\Parameter;
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
     * 命令参数
     *
     * @var Parameter
     */
    private Parameter $commandParameter;

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

        $hasCommand = $input->hasParameter(0);
        $concrete = $this->getDefinition($hasCommand)->resolve($input);

        if ($output === null) {
            $output = new Output(new Formatter());
        }

        $this->outputSetting($concrete, $output);

        if ($hasCommand) {
            try {
                $commandParameterName = $this->getCommandParameter()->getName();
                $commandName = $concrete->getParameter($commandParameterName);
                $command = $this->get($commandName);
                $code = $command->run($input, $output);
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
     * 获取指令列表
     *
     * @param string|null $namespace
     * @return CommandInterface[]
     */
    public function getCommands(string $namespace = null): array
    {
        $command = $this->baseCommands + $this->commands;

        return $namespace === null || $namespace === ''
            ? $command
            : array_filter(
                $command,
                fn($name) => $name === $namespace || str_starts_with($name, $namespace . '.'),
                ARRAY_FILTER_USE_KEY
            );
    }

    /**
     * 获取命令参数
     *
     * @return Parameter
     */
    public function getCommandParameter(): Parameter
    {
        return $this->commandParameter ??= new Parameter('command', Parameter::REQUIRED, 'The command to execute');
    }

    /**
     * 获取输入定义
     *
     * @param bool $withParameters
     * @return Definition
     */
    public function getDefinition(bool $withParameters): Definition
    {
        $arguments = [
            new Option('output', 'o', Option::OPTIONAL, "Message output: 0(decorate tags), 1(strip tags), 2(raw output), 3(no output)", '0'),
        ];

        if ($withParameters) {
            array_unshift($arguments, $this->getCommandParameter());
        }

        return new Definition($arguments);
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
