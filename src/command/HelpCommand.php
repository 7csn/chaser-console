<?php

namespace chaser\console\command;

use chaser\console\argument\Parameter;
use chaser\console\descriptor\Descriptor;
use chaser\console\input\Definition;
use chaser\console\input\InputInterface;
use chaser\console\output\OutputInterface;

/**
 * 帮助命令类
 *
 * @package chaser\console\command
 */
class HelpCommand extends Command
{
    /**
     * @inheritDoc
     */
    public static function getDefaultName(): string
    {
        return 'help';
    }

    /**
     * @inheritDoc
     */
    public static function getDefaultDescription(): string
    {
        return 'Displays help for a command';
    }

    /**
     * @inheritDoc
     */
    public function getArguments(): array
    {
        return [
            new Parameter('command_name', 0, 'The command name', $this->getName())
        ];
    }

    /**
     * @inheritDoc
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $descriptor = new Descriptor($output);
        $concrete = $this->getConcrete($input);
        $command = $this->getApplication()->get($concrete->getParameter('command_name'));
        $descriptor->describeCommand($command);
        return 0;
    }
}
