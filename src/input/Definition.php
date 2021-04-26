<?php

namespace chaser\console\input;

use chaser\console\exception\InvalidArgumentException;
use chaser\console\exception\LogicException;
use chaser\console\exception\RuntimeException;
use chaser\console\argument\Option;
use chaser\console\argument\Parameter;

/**
 * 输入定义类
 *
 * @package chaser\console\input
 */
class Definition
{
    /**
     * 位置参数列表
     *
     * @var Parameter[] [$name => Parameter]
     */
    private array $parameters = [];

    /**
     * 选项列表
     *
     * @var Option[] [$name => Option]
     */
    private array $options = [];

    /**
     * 选项快捷名列表
     *
     * @var string[] [$shortcut => $name]
     */
    private array $optionShortcuts = [];

    /**
     * 必须位置参数个数
     *
     * @var int
     */
    private int $requiredParameterCount = 0;

    /**
     * 是否含多值位置参数
     *
     * @var bool
     */
    private bool $hasComplexParameter = false;

    /**
     * 是否含值可选位置参数
     *
     * @var bool
     */
    private bool $hasOptionalParameter = false;

    /**
     * 初始化定义信息
     *
     * @param array|null $arguments
     */
    public function __construct(array $arguments = null)
    {
        if ($arguments !== null) {
            $this->setArguments($arguments);
        }
    }

    /**
     * 重新设置参数
     *
     * @param array $arguments
     */
    public function setArguments(array $arguments): void
    {
        $parameters = [];
        $options = [];

        foreach ($arguments as $argument) {
            if ($argument instanceof Parameter) {
                $parameters[] = $argument;
            } elseif ($argument instanceof Option) {
                $options[] = $argument;
            } else {
                throw new InvalidArgumentException(sprintf('Argument must be of type %s or %s.', Parameter::class, Option::class));
            }
        }

        $this->setParameters($parameters);
        $this->setOptions($options);
    }

    /**
     * 获取位置参数列表
     *
     * @return Parameter[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * 重新设置参数
     *
     * @param array $parameters
     */
    public function setParameters(array $parameters = []): void
    {
        $this->parameters = [];
        $this->requiredParameterCount = 0;
        $this->hasComplexParameter = false;
        $this->hasOptionalParameter = false;
        $this->addParameters($parameters);
    }

    /**
     * 批量添加参数
     *
     * @param array $Parameters
     */
    public function addParameters(array $Parameters = []): void
    {
        foreach ($Parameters as $parameter) {
            $this->addParameter($parameter);
        }
    }

    /**
     * 添加参数
     *
     * @param Parameter $parameter
     */
    public function addParameter(Parameter $parameter): void
    {
        $name = $parameter->getName();

        if (isset($this->parameters[$name])) {
            throw new LogicException(sprintf('An parameter with name "%s" already exists.', $name));
        }

        if ($this->hasComplexParameter) {
            throw new LogicException('Cannot add an parameter after an array parameter.');
        }

        if ($parameter->isRequired() && $this->hasOptionalParameter) {
            throw new LogicException('Cannot add a required parameter after an optional one.');
        }

        if ($parameter->isComplex()) {
            $this->hasComplexParameter = true;
        }

        if ($parameter->isRequired()) {
            ++$this->requiredParameterCount;
        } else {
            $this->hasOptionalParameter = true;
        }

        $this->parameters[$name] = $parameter;
    }

    /**
     * 获取选项列表
     *
     * @return Option[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * 重新设置选项
     *
     * @param array $options
     */
    public function setOptions(array $options = []): void
    {
        $this->options = [];
        $this->optionShortcuts = [];
        $this->addOptions($options);
    }

    /**
     * 批量添加选项
     *
     * @param array $options
     */
    public function addOptions(array $options = []): void
    {
        foreach ($options as $option) {
            $this->addOption($option);
        }
    }

    /**
     * 添加选项
     *
     * @param Option $option
     */
    public function addOption(Option $option): void
    {
        $name = $option->getName();

        if (isset($this->options[$name])) {
            throw new LogicException(sprintf('An option named "%s" already exists.', $name));
        }

        $shortcut = $option->getShortcut();

        if ($shortcut !== null) {
            if (isset($this->optionShortcuts[$shortcut])) {
                throw new LogicException(sprintf('An option with shortcut "%s" already exists.', $shortcut));
            }
            $this->optionShortcuts[$shortcut] = $name;
        }

        $this->options[$name] = $option;
    }

    /**
     * 获取摘要
     *
     * @param bool $short
     * @return string
     */
    public function getSynopsis(bool $short = false): string
    {
        $optionSynopses = $this->getSynopsisOfOptions($short);
        $parameterSynopses = $this->getSynopsisOfParameters();

        return $optionSynopses === '' || $parameterSynopses === ''
            ? $optionSynopses . $parameterSynopses
            : $optionSynopses . ' [--] ' . $parameterSynopses;
    }

    /**
     * 获取输入实体
     *
     * @param InputInterface $input
     * @return Concrete
     */
    public function resolve(InputInterface $input): Concrete
    {
        $givenParameterCount = count($input->getParameters());

        if ($givenParameterCount < $this->requiredParameterCount) {
            throw new RuntimeException(sprintf(
                'At least %d parameters are required and only %d are provided.',
                $this->requiredParameterCount,
                $givenParameterCount
            ));
        }

        $position = 0;
        $parameters = [];
        foreach ($this->parameters as $name => $parameter) {
            if ($position < $givenParameterCount) {
                $parameters[$name] = $input->getParameterValue($position++, $parameter->isComplex());
            } elseif (null !== $default = $parameter->getDefault()) {
                $parameters[$name] = $default;
            }
        }

        $options = [];
        foreach ($this->options as $name => $option) {
            if (null === $values = $input->getOptionValues($option)) {
                if (null !== $default = $option->getDefault()) {
                    $options[$name] = $default;
                }
            } elseif (empty($values)) {
                if ($option->isRequired()) {
                    throw new RuntimeException(sprintf('Option "%s" must provide a value.', $name));
                }
                $options[$name] = $option->getDefault();
            } else {
                $options[$name] = $option->acceptValue()
                    ? $option->isComplex() ? $values : $values[0]
                    : null;
            }
        }

        return new Concrete($parameters, $options);
    }

    /**
     * 获取位置参数摘要
     *
     * @return string
     */
    private function getSynopsisOfParameters(): string
    {
        $parameters = $this->getParameters();

        if (empty($parameters)) {
            return '';
        }

        $synopses = [];

        $tail = '';
        foreach ($this->parameters as $name => $parameter) {
            $synopsis = '<' . $name . '>';
            if ($parameter->isComplex()) {
                $synopsis .= '...';
            }

            if (!$parameter->isRequired()) {
                $synopsis = '[' . $synopsis;
                $tail .= ']';
            }

            $synopses[] = $synopsis;
        }

        return join(' ', $synopses) . $tail;
    }

    /**
     * 获取选项摘要
     *
     * @param bool $short
     * @return string
     */
    private function getSynopsisOfOptions(bool $short): string
    {
        $options = $this->getOptions();

        if (empty($options)) {
            return '';
        }

        if ($short) {
            return '[options]';
        }

        $synopses = [];

        foreach ($options as $name => $option) {
            $value = $option->acceptValue()
                ? $option->isOptional()
                    ? sprintf(' %s%s%s', '[', strtoupper($name), ']')
                    : sprintf(' %s', strtoupper($name))
                : '';

            $shortcut = $option->getShortcut() ? sprintf('-%s|', $option->getShortcut()) : '';
            $synopses[] = sprintf('[%s--%s%s]', $shortcut, $name, $value);
        }

        return join(' ', $synopses);
    }
}
