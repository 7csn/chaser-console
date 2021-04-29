<?php

namespace chaser\console\input;

use chaser\console\argument\Option;
use chaser\console\exception\RuntimeException;

/**
 * 输入类
 *
 * @package chaser\console\input
 */
class Input implements InputInterface
{
    /**
     * 指令数组
     *
     * @var string[]
     */
    private array $tokens;

    /**
     * 指令解析数组
     *
     * @var string[]
     */
    private array $parsing;

    /**
     * 位置参数组
     *
     * @var string[]
     */
    private array $parameters = [];

    /**
     * 长选项数组
     *
     * @var string[][] [名称 => 值列表]
     */
    private array $longOptions = [];

    /**
     * 短选项数组
     *
     * @var string[][] [名称 => 值列表]
     */
    private array $shortOptions = [];

    /**
     * 获取默认输入指令数组
     *
     * @return string[]
     */
    public static function getDefaultTokens(): array
    {
        return isset($_SERVER['argv']) ? array_slice($_SERVER['argv'], 1) : [];
    }

    /**
     * 初始化输入信息
     *
     * @param array|null $tokens
     */
    public function __construct(array $tokens = null)
    {
        $this->tokens = $tokens ?? static::getDefaultTokens();

        $this->parse();
    }

    /**
     * @inheritDoc
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @inheritDoc
     */
    public function getLongOptions(): array
    {
        return $this->longOptions;
    }

    /**
     * @inheritDoc
     */
    public function getShortOptions(): array
    {
        return $this->shortOptions;
    }

    /**
     * @inheritDoc
     */
    public function hasParameter(int $position): bool
    {
        return isset($this->parameters[$position]);
    }

    /**
     * @inheritDoc
     */
    public function getParameterValue(int $position, bool $complex = false): array|string|null
    {
        return $complex ? array_slice($this->parameters, $position) ?: null : $this->parameters[$position] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function hasOption(Option $option): bool
    {
        if (isset($this->longOptions[$option->getName()])) {
            return true;
        }

        if (null === $shortcut = $option->getShortcut()) {
            return false;
        }

        return isset($this->shortOptions[$shortcut]);
    }

    /**
     * @inheritDoc
     */
    public function getOptionValues(Option $option): ?array
    {
        $name = $option->getName();

        if (isset($this->longOptions[$name])) {
            return $this->longOptions[$name];
        }

        if (null === $shortcut = $option->getShortcut()) {
            return null;
        }

        return $this->shortOptions[$shortcut] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function clone(int $start = 0): self
    {
        $tokens = $start === 0 ? $this->tokens : array_slice($this->tokens, $start);

        return new self($tokens);
    }

    /**
     * 解析参数
     */
    private function parse(): void
    {
        $this->parsing = $this->tokens;
        while (null !== $token = array_shift($this->parsing)) {
            str_starts_with($token, '-') ? $this->parseNotArgument($token) : $this->parseParameter($token);
        }
    }

    /**
     * 解析非常规参数部分
     *
     * @param string $token
     */
    private function parseNotArgument(string $token): void
    {
        str_starts_with($token, '--')
            ? $token === '--' ? $this->parseParameters() : $this->parseLongOption(substr($token, 2))
            : $this->parseShortOption(substr($token, 1));
    }

    /**
     * 解析长选项
     *
     * @param string $name
     */
    private function parseLongOption(string $name): void
    {
        if (str_contains($name, '=')) {
            [$name, $value] = explode('=', $name, 2);
            if (isset($options[$name])) {
                $options[$name][] = [];
            } else {
                $options[$name] = [$value];
            }
        }
        $this->parseOption($name, $this->longOptions);
    }

    /**
     * 解析短选项
     *
     * @param string $name
     */
    private function parseShortOption(string $name): void
    {
        if ($name === '') {
            throw new RuntimeException("The option shortcut cannot be empty.");
        }

        $end = strlen($name) - 1;

        if ($end > 0) {
            for ($i = $end - 1; $i >= 0; $i--) {
                if (!isset($this->shortOptions[$name[$i]])) {
                    $this->shortOptions[$name[$i]] = [];
                }
            }

            $name = $name[$end];
        }

        $this->parseOption($name, $this->shortOptions);
    }

    /**
     * 解析选项
     *
     * @param string $name
     * @param array $options
     */
    private function parseOption(string $name, array &$options): void
    {
        if (!isset($options[$name])) {
            $options[$name] = [];
        }

        while (null !== $token = array_shift($this->parsing)) {
            if (str_starts_with($token, '-')) {
                $this->parseNotArgument($token);
            } else {
                $options[$name][] = $token;
            }
        }
    }

    /**
     * 解析位置参数组
     */
    private function parseParameters(): void
    {
        while (null !== $token = array_shift($this->parsing)) {
            $this->parseParameter($token);
        }
    }

    /**
     * 解析位置参数
     *
     * @param string $value
     */
    private function parseParameter(string $value): void
    {
        $this->parameters[] = $value;
    }
}
