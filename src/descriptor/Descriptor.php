<?php

namespace chaser\console\descriptor;

use chaser\console\Application;
use chaser\console\argument\Option;
use chaser\console\argument\Parameter;
use chaser\console\command\CommandInterface;
use chaser\console\input\Definition;
use chaser\console\output\OutputInterface;

/**
 * 描述器基类
 *
 * @package chaser\console\descriptor
 */
class Descriptor implements DescriptorInterface
{
    /**
     * 初始化输出对象
     *
     * @param OutputInterface $output
     */
    public function __construct(private OutputInterface $output)
    {
    }

    /**
     * @inheritDoc
     */
    public function describeApplication(Application $application): void
    {
        $this->output->writeln();
        $this->output->writeln($application->getName());

        $this->output->writeln();
        $this->output->writeln('<comment>Usages:</comment>');
        $this->output->writeln(' command [parameters] [options] [--] [raw parameters]');

        $this->describeDefinition($application->getDefinition(false));

        $this->listBaseCommands($application);
    }

    /**
     * @inheritDoc
     */
    public function listBaseCommands(Application $application): void
    {
        $this->output->writeln();
        $this->output->writeln('<comment>Commands:</comment>');

        $commands = $application->getBaseCommands();
        $this->displayCommands($commands);
        $this->output->writeln('  <info>...</info>');
    }

    /**
     * @inheritDoc
     */
    public function listCommands(Application $application, string $prefix = null): void
    {
        $this->output->writeln();

        $commands = $application->getCommands($prefix);

        if (empty($commands)) {
            $this->output->writeln('<comment>No commands</comment>');
        } else {
            $this->output->writeln('<comment>Commands:</comment>');
            $this->displayCommands($commands);
        }
    }

    /**
     * @inheritDoc
     */
    public function describeCommand(CommandInterface $command): void
    {
        if ('' !== $description = $command->getDescription()) {
            $this->output->writeln();
            $this->output->writeln('<comment>Description:</comment>');
            $this->output->writeln('  ' . $description);
        }

        $this->output->writeln();
        $this->output->writeln('<comment>Usages:</comment>');
        $this->output->writeln('  ' . $command->getSynopsis(true));
        foreach ($command->getUsages() as $usage) {
            $this->output->writeln('  ' . $usage);
        }

        $definition = $command->getDefinition();
        if ($definition->getOptions() || $definition->getParameters()) {
            $this->describeDefinition($definition);
        }
    }

    /**
     * @inheritDoc
     */
    public function describeDefinition(Definition $definition): void
    {
        $parameters = $definition->getParameters();
        $parameterWidth = self::calculateAlignWidthForParameters($parameters);

        $options = $definition->getOptions();
        $optionWidth = self::calculateAlignWidthForOptions($options);

        $alignWidth = max($parameterWidth, $optionWidth);

        if ($parameterWidth > 0) {
            $this->output->writeln();
            $this->output->writeln('<comment>Parameters:</comment>');
            foreach ($parameters as $parameter) {
                $this->describeParameter($parameter, $alignWidth);
            }
        }

        if ($optionWidth > 0) {
            $this->output->writeln();
            $this->output->writeln('<comment>Options:</comment>');
            foreach ($options as $option) {
                $this->describeOption($option, $alignWidth);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function describeParameter(Parameter $parameter, int $alignWidth = null): void
    {
        if (null === $default = $parameter->getDefault()) {
            $default = '';
        } else {
            $default = $parameter->isComplex() ? join(' ', $default) : $default;
            $default = sprintf('<comment> [default: %s]</comment>', $default);
        }

        $width = self::normalizeParameterNameSpacing($parameter);
        if ($alignWidth === null) {
            $spacing = 0;
            $alignWidth = $width;
        } else {
            $spacing = $alignWidth - $width;
        }

        $space = $spacing > 0 ? str_repeat(' ', $spacing) : '';

        $this->output->writeln(sprintf('  <info>%s</info>  %s%s%s',
            $parameter->getName(),
            $space,
            preg_replace('/\s*[\r\n]/', "\n" . str_repeat(' ', $alignWidth + 4), $parameter->getDescription()),
            $default
        ));
    }

    /**
     * @inheritDoc
     */
    public function describeOption(Option $option, int $alignWidth = null): void
    {
        if (null === $default = $option->getDefault()) {
            $default = '';
        } else {
            $default = $option->isComplex() ? join(' ', $default) : $default;
            $default = sprintf('<comment> [default: %s]</comment>', $default);
        }

        $width = self::normalizeOptionNameSpacing($option);
        if ($alignWidth === null) {
            $spacing = 0;
            $alignWidth = $width;
        } else {
            $spacing = $alignWidth - $width;
        }

        if ($option->acceptValue()) {
            $format = $option->isRequired() ? '=%s' : '[=%s]';
            $value = sprintf($format, strtoupper($option->getName()));
        } else {
            $value = '';
        }

        $synopsis = sprintf('%s%s',
            $option->getShortcut() ? sprintf('-%s, ', $option->getShortcut()) : '    ',
            sprintf('--%s%s', $option->getName(), $value)
        );

        $space = $spacing > 0 ? str_repeat(' ', $spacing) : '';

        $this->output->writeln(sprintf('  <info>%s</info>  %s%s%s%s',
            $synopsis,
            $space,
            preg_replace('/\s*[\r\n]/', "\n" . str_repeat(' ', $alignWidth + 4), $option->getDescription()),
            $default,
            $option->isComplex() ? ' <comment>(multiple values allowed)</comment>' : ''
        ));
    }

    /**
     * 展示指令
     *
     * @param array $commands
     */
    private function displayCommands(array $commands): void
    {
        $alignWidth = self::calculateAlignWidthForCommands($commands);
        foreach ($commands as $name => $command) {
            $space = str_repeat(' ', $alignWidth - strlen($name));
            $this->output->writeln(sprintf('  <info>%s</info>  %s%s', $name, $space, $command->getDescription()));
        }
    }

    /**
     * 计算命令的名称对齐宽度
     *
     * @param array $commands
     * @return int
     */
    private static function calculateAlignWidthForCommands(array $commands): int
    {
        return array_reduce($commands, function ($alignWidth, CommandInterface $command) {
            $width = strlen($command->getName());
            return max($alignWidth, $width);
        }, 0);
    }

    /**
     * 计算位置参数的名称对齐宽度
     *
     * @param array $parameters
     * @return int
     */
    private static function calculateAlignWidthForParameters(array $parameters): int
    {
        return array_reduce($parameters, fn($alignWidth, Parameter $parameter) => max($alignWidth, self::normalizeParameterNameSpacing($parameter)), 0);
    }

    /**
     * 计算选项的名称对齐宽度
     *
     * @param Option[] $options
     * @return int
     */
    private static function calculateAlignWidthForOptions(array $options): int
    {
        return array_reduce($options, fn($alignWidth, Option $option) => max($alignWidth, self::normalizeOptionNameSpacing($option)), 0);
    }

    /**
     * 获取位置参数名标准占位
     *
     * @param Parameter $parameter
     * @return int
     */
    private static function normalizeParameterNameSpacing(Parameter $parameter): int
    {
        return strlen($parameter->getName());
    }

    /**
     * 获取选项标准占位
     *
     * @param Option $option
     * @return int
     */
    private static function normalizeOptionNameSpacing(Option $option): int
    {
        $width = strlen($option->getName());
        if ($option->acceptValue()) {
            $width = $width * 2 + 1;
            if ($option->isOptional()) {
                $width += 2;
            }
        }
        return $width + 6;
    }
}
