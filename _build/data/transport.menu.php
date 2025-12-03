<?php
/**
 * Template
 *
 * Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse justo 
 * eros, pellentesque nec tellus egestas, posuere egestas turpis. Nulla vitae 
 * ultrices ipsum. Aliquam lacus elit, dapibus rhoncus eros quis, scelerisque 
 * commodo lacus. Nulla turpis leo, varius sit amet venenatis vitae.
 *
 * @package template
 * @subpackage build
 * 
 * Adds modActions and modMenus into package
 * 
 */
$action = $modx->newObject('modAction');
$action->fromArray(array(
    'id' => 1,
    'namespace' => 'template',
    'parent' => 0,
    'controller' => 'index',
    'haslayout' => 1,
    'lang_topics' => 'template:default',
    'assets' => '',
), '', true, true);

/* load action into menu */
$menu = $modx->newObject('modMenu');
$menu->fromArray(array(
    'text' => 'template',
    'parent' => 'components',
    'description' => 'template.menu_desc',
    'icon' => 'images/icons/plugin.gif',
    'menuindex' => 0,
    'params' => '',
    'handler' => '',
), '', true, true);
$menu->addOne($action);
unset($action);

return $menu;