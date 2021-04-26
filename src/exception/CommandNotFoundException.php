<?php

namespace chaser\console\exception;

/**
 * 命令未找到异常类
 *
 * @package chaser\console\exception
 */
class CommandNotFoundException extends \InvalidArgumentException implements ExceptionInterface
{
}
