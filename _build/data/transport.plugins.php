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

if (!function_exists('getPluginContent')) {
	function getpluginContent($filename)
	{
		$o = file_get_contents($filename);
		$o = str_replace('<?php', '', $o);
		$o = str_replace('?>', '', $o);
		$o = trim($o);
		return $o;
	}
}

$className = 'modPlugin';
$type = 'plugin';
$plural = $type . 's';
$extension = 'php';

$name = 'name';
$content = 'plugincode';

$objects = array();
foreach($config['config']['elements'][$plural] as $key => $el){
    $modx->log(xPDO::LOG_LEVEL_INFO, "Should add plugin {$el['name']}.");

    $path = $el['path'] ?? "/elements/$plural/";
    $obj = $modx->newObject($className);
    $obj->fromArray([
        'id' => $key,
        'name' => $el['name'],
        'description' => $template['description'] ?? '',
        $content => getPluginContent("{$sources['source_core']}$path" . ($el['filename'] ?? "$type.{$el['name']}.$extension")),
        'properties' => $el['properties'] ?? ''
    ], '', true, true);


    if(isset($el['events']) && is_array($el['events']) && !empty($el['events'])){
        $events = [];
        foreach($el['events'] as $e){
            $evObj = $modx->newObject('modPluginEvent');
            $evObj->fromArray(array(
                'event' => $e,
                'priority' => $e === 'OnHandleRequest' ? 1 : 0,
                'propertyset' => 0
            ),'',true,true);

            $events[] = $evObj;
        }

        if (is_array($events) && !empty($events)) {
            $obj->addMany($events);
            $modx->log(xPDO::LOG_LEVEL_INFO, "Packaged in ".count($events) ." Plugin Events for {$el['name']}.");
            flush();
        }

    }

    $objects[] = $obj;
}

$modx->log(xPDO::LOG_LEVEL_INFO, "Added ".count($objects)." plugins");
return $objects;