<?php

namespace Shasoft\DbSchema\Command;

use Shasoft\DbSchema\State\StateCommands;

// Базовая команда
abstract class Base  implements ICommand
{
    protected ?StateCommands $parent = null;
};
