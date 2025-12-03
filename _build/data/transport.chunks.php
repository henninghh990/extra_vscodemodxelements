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
 */
$className = 'modChunk';
$type = 'chunk';
$plural = $type . 's';
$extension = 'tpl';

$name = 'name';
$content = 'snippet';

$objects = array();
foreach($config['config']['elements'][$plural] as $key => $el){

    $path = $el['path'] ?? "/elements/$plural/";
    $obj = $modx->newObject($className);
    $obj->fromArray([
        'id' => $key,
        $name => $el['name'],
        'description' => $template['description'] ?? '',
        $content => file_get_contents("{$sources['source_core']}$path" . ($el['filename'] ?? "$type.{$el['name']}.$extension")),
        'properties' => $el['properties'] ?? ''
    ], '', true, true);
    $objects[] = $obj;
}
return $objects;