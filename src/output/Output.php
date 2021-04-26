<?php

namespace chaser\console\output;

use chaser\console\exception\InvalidArgumentException;

/**
 * 输出类
 *
 * @package chaser\console\output
 */
class Output implements OutputInterface
{
    /**
     * 消息输出模式
     *
     * @var int
     */
    private int $mode;

    /**
     * 输出流
     *
     * @var resource
     */
    private $stream;

    /**
     * 初始化输出信息
     *
     * @param FormatterInterface $formatter
     * @param int $mode
     */
    public function __construct(private FormatterInterface $formatter, int $mode = self::OUTPUT_DECORATE)
    {
        $this->stream = $this->getOutputStream();
        $this->setMode($mode);
    }

    /**
     * @inheritDoc
     */
    public function write(string $message = ''): void
    {
        if ($this->isQuiet()) {
            return;
        }

        switch ($this->mode) {
            case OutputInterface::OUTPUT_DECORATE:
                $message = $this->formatter->format($message);
                break;
            case OutputInterface::OUTPUT_RAW:
                break;
            case OutputInterface::OUTPUT_PLAIN:
                $message = strip_tags($message);
                break;
        }

        @fwrite($this->stream, $message);
        fflush($this->stream);
    }

    /**
     * @inheritDoc
     */
    public function writeln(string $message = '', int $lines = 1): void
    {
        $this->write($message . str_repeat(PHP_EOL, $lines));
    }

    /**
     * @inheritDoc
     */
    public function setMode(int $mode): void
    {
        if (!in_array($mode, [self::OUTPUT_DECORATE, self::OUTPUT_PLAIN, self::OUTPUT_RAW, self::OUTPUT_QUIET])) {
            throw new InvalidArgumentException(sprintf('The output mode "%s" is not valid.', $mode));
        }

        $this->mode = $mode;
        $this->formatter->setDecorated($mode === self::OUTPUT_DECORATE);
    }

    /**
     * @inheritDoc
     */
    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * @inheritDoc
     */
    public function isDecorate(): bool
    {
        return $this->formatter->isDecorated();
    }

    /**
     * @inheritDoc
     */
    public function isPlain(): bool
    {
        return $this->mode === self::OUTPUT_PLAIN;
    }

    /**
     * @inheritDoc
     */
    public function isRaw(): bool
    {
        return $this->mode === self::OUTPUT_RAW;
    }

    /**
     * @inheritDoc
     */
    public function isQuiet(): bool
    {
        return $this->mode === self::OUTPUT_QUIET;
    }

    /**
     * @inheritDoc
     */
    public function setFormatter(FormatterInterface $formatter): void
    {
        $this->formatter = $formatter;
    }

    /**
     * @inheritDoc
     */
    public function getFormatter(): FormatterInterface
    {
        return $this->formatter;
    }

    /**
     * 获取输出流
     *
     * @return resource
     */
    private function getOutputStream()
    {
        $filename = str_contains(PHP_OS, 'OS400') ? 'php://stdout' : 'php://output';
        return fopen($filename, 'w');
    }
}
