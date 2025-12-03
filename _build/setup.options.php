<?php
/**
 * @var modX $modx
 * @var array $options
 */

$package = 'vscodemodxelements';
$settings = [
    [
        'key' => 'api_token',
        'name' => 'VSCode API Token',
    ],
    [
        'key' => 'api_url',
        'name' => 'VSCode API URL',
        'value' => 'modx-elements'
    ]
    
];
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:

        
        $html = '';
        foreach($settings as $key => $value){
            $key = $value['key'];
            $setting = $modx->getObject('modSystemSetting', [
                'key' => strtolower($package) . '.' . $key
            ]);
            
            $selectedValue = $setting ? $setting->get('value') : $value['value'];
              
            $html .= <<<HTML
                <label for="{$key}">{$value['name']}</label> 
                <input type="text" name="{$key}" id="{$key}" width="300" value="{$selectedValue}" /> 
                <br /><br />
            HTML;
            
        }

        return $html;
}

return '';