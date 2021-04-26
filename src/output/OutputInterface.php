<?php

namespace chaser\console\output;

/**
 * 输出接口
 *
 * @package chaser\console\output
 */
interface OutputInterface
{
    /**
     * 消息输出模式：装饰
     */
    public const OUTPUT_DECORATE = 1;

    /**
     * 消息输出模式：文本
     */
    public const OUTPUT_PLAIN = 2;

    /**
     * 消息输出模式：原始
     */
    public const OUTPUT_RAW = 4;

    /**
     * 消息输出模式：静默
     */
    public const OUTPUT_QUIET = 8;

    /**
     * 输出消息
     *
     * @param string $message
     */
    public function write(string $message = ''): void;

    /**
     * 输出消息并换行
     *
     * @param string $message
     * @param int $lines
     */
    public function writeln(string $message = '', int $lines = 1): void;

    /**
     * 设置消息输出模式
     *
     * @param int $mode
     */
    public function setMode(int $mode): void;

    /**
     * 获取消息输出模式
     *
     * @return int
     */
    public function getMode(): int;

    /**
     * 设置格式器
     *
     * @param FormatterInterface $formatter
     */
    public function setFormatter(FormatterInterface $formatter): void;

    /**
     * 获取格式器
     *
     * @return FormatterInterface
     */
    public function getFormatter(): FormatterInterface;

    /**
     * 判断是否装饰模式
     *
     * @return bool
     */
    public function isDecorate(): bool;

    /**
     * 判断是否文本模式
     *
     * @return bool
     */
    public function isPlain(): bool;

    /**
     * 判断是否原始模式
     *
     * @return bool
     */
    public function isRaw(): bool;

    /**
     * 判断是否静默模式
     *
     * @return bool
     */
    public function isQuiet(): bool;
}
