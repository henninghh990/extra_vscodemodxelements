<?php

/**
 * @var \MODX\Revolution\modX $modx
 * @var array $namespace
 */

// Load the classes

use VSCodeApi\Controller\Api;

\MODX\Revolution\modX::getLoader()->addPsr4('VSCodeApi\\', $namespace['path'] .'src/');

$modx->vscode = new \VSCodeApi\Controller\Api($modx, []);
