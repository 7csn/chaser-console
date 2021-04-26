<?php

namespace chaser\console\command;

use chaser\console\argument\Parameter;
use chaser\console\descriptor\Descriptor;
use chaser\console\input\Definition;
use chaser\console\input\InputInterface;
use chaser\console\output\OutputInterface;

/**
 * 列表命令类
 *
 * @package chaser\console\command
 */
class ListCommand extends Command
{
    /**
     * @inheritDoc
     */
    public static function getDefaultName(): string
    {
        return 'list';
    }

    /**
     * @inheritDoc
     */
    public static function getDefaultDescription(): string
    {
        return 'Lists commands';
    }

    /**
     * @inheritDoc
     */
    public function getArguments(): array
    {
        return [
            new Parameter('namespace', 0, 'The namespace name')
        ];
    }

    /**
     * @inheritDoc
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $descriptor = new Descriptor($output);
        $descriptor->listCommands($this->getApplication(), $this->getConcrete($input)->getParameter('namespace'));
        return 0;
    }
}
