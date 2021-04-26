<?php

namespace chaser\console\output;

/**
 * 输出格式器
 *
 * @package chaser\console\output
 */
class Formatter implements FormatterInterface
{
    /**
     * 样式库
     *
     * @var StyleInterface[]
     */
    private array $styles = [];

    /**
     * 初始化格式器
     *
     * @param bool $decorated 是否装饰
     * @param StyleInterface[] $styles 添加样式库
     */
    public function __construct(private bool $decorated = false, array $styles = [])
    {
        $this->initialize();

        foreach ($styles as $name => $style) {
            $this->setStyle($name, $style);
        }
    }

    /**
     * @inheritDoc
     */
    public function setDecorated(bool $decorated): void
    {
        $this->decorated = $decorated;
    }

    /**
     * @inheritDoc
     */
    public function isDecorated(): bool
    {
        return $this->decorated;
    }

    /**
     * @inheritDoc
     */
    public function setStyle(string $name, StyleInterface $style): void
    {
        $this->styles[$name] = $style;
    }

    /**
     * @inheritDoc
     */
    public function hasStyle(string $name): bool
    {
        return isset($this->styles[$name]);
    }

    /**
     * @inheritDoc
     */
    public function getStyle(string $name): ?StyleInterface
    {
        return $this->styles[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function format(string $message = ''): string
    {
        if (!$this->isDecorated()) {
            return $message;
        }

        return (string)preg_replace_callback('/<([a-z]\w*)(.*?)>(.*?)<\/\1>/s', function ($match) {

            $subMessage = $this->format($match[3]);

            if (!$this->hasStyle($match[1])) {
                return "<{$match[1]}{$match[2]}>{$subMessage}</{$match[1]}>";
            }

            $style = $this->getStyle($match[1]);

            if (preg_match_all('/\b(fg|bg|href|options)=([\'"]?)([^\2\s]+?)\2(?=\s|$)/s', $match[2], $_matches, PREG_SET_ORDER)) {
                foreach ($_matches as $_match) {
                    $_match3 = str_replace(' ', '', $_match[3]);
                    switch ($_match[1]) {
                        case 'fg':
                            $style->setFgColor($_match3);
                            break;
                        case 'bg':
                            $style->setBgColor($_match3);
                            break;
                        case 'href':
                            $style->setHref($_match3);
                            break;
                        case 'options':
                            $style->setOptions(...explode(',', $_match3));
                            break;
                    }
                }
            }

            return $style->apply($subMessage);

        }, $message);
    }

    /**
     * 初始化样式库
     */
    private function initialize(): void
    {
        $this->setStyle('css', new Style());
        $this->setStyle('error', new Style(StyleInterface::WHITE, StyleInterface::RED));
        $this->setStyle('info', new Style(StyleInterface::GREEN));
        $this->setStyle('comment', new Style(StyleInterface::YELLOW));
    }
}
