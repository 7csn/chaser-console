<?php

namespace chaser\console\output;

/**
 * 输出样式接口
 *
 * @package chaser\console\output
 */
interface StyleInterface
{
    /**
     * 颜色：黑
     */
    public const BLACK = 'black';

    /**
     * 颜色：红
     */
    public const RED = 'red';

    /**
     * 颜色：绿
     */
    public const GREEN = 'green';

    /**
     * 颜色：黄
     */
    public const YELLOW = 'yellow';

    /**
     * 颜色：蓝
     */
    public const BLUE = 'blue';

    /**
     * 颜色：品红
     */
    public const MAGENTA = 'magenta';

    /**
     * 颜色：青
     */
    public const CYAN = 'cyan';

    /**
     * 颜色：白
     */
    public const WHITE = 'white';

    /**
     * 配置：高亮
     */
    public const HIGHLIGHT = 'highlight';

    /**
     * 配置：下边线
     */
    public const UNDERLINE = 'underline';

    /**
     * 配置：闪烁
     */
    public const BLINK = 'blink';

    /**
     * 配置：反显（前后景倒置）
     */
    public const REVERSE = 'reverse';

    /**
     * 配置：消隐（前景空白）
     */
    public const CONCEAL = 'conceal';

    /**
     * 设置前景色
     *
     * @param string $color
     */
    public function setFgColor(string $color): void;

    /**
     * 设置背景色
     *
     * @param string $color
     */
    public function setBgColor(string $color): void;

    /**
     * 批量设置选项
     *
     * @param string ...$options
     */
    public function setOptions(string ...$options): void;

    /**
     * 设置选项
     *
     * @param string $option
     */
    public function setOption(string $option): void;

    /**
     * 选项批量设置
     *
     * @param string ...$options
     */
    public function unsetOptions(string ...$options): void;

    /**
     * 选项配置
     *
     * @param string $option
     */
    public function unsetOption(string $option): void;

    /**
     * 设置超链接
     *
     * @param string $href
     */
    public function setHref(string $href): void;

    /**
     * 返回文本样式应用内容
     *
     * @param string $text
     * @return string
     */
    public function apply(string $text): string;

    /**
     * 返回设置内容
     *
     * @return string
     */
    public function set(): string;

    /**
     * 返回复位内容
     *
     * @return string
     */
    public function unset(): string;
}
