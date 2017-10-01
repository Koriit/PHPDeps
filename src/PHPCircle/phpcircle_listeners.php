<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

use Koriit\PHPCircle\CommandsLoader;
use Koriit\PHPCircle\Events\AppExecutedCommandEvent;
use Koriit\PHPCircle\Events\AppFinalizedEvent;
use Koriit\PHPCircle\Events\AppInitializedEvent;
use Koriit\PHPCircle\Events\AppLoadedCommandsEvent;

return [
      AppInitializedEvent::class => [],

      AppLoadedCommandsEvent::class => [
            10 => [
                  CommandsLoader::class,
            ],
      ],

      AppExecutedCommandEvent::class => [],

      AppFinalizedEvent::class => [],
];
