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
 * Loads system settings
 * 
 */

$settings = array();

foreach($config['config']['elements']['settings'] as $el){
    $key = PKG_NAME_LOWER.".{$el['key']}";
    $obj = $modx->newObject('modSystemSetting');
    $obj->fromArray([
        'key' => $key,
        'value' => $el['value'] ?? '',
        'xtype' => $el['xtype'] ?? 'textfield',
        'namespace' => $config['lcaseName'],
        'area' => $el['area'] ?? 'Default',
        'editedon' => time(),
    ], '', true, true);

    $settings[$key] = $obj;
}

return $settings;