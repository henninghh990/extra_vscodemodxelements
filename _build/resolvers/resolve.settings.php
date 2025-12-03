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
 */
/**
 * Resolver for saving setup options into system settings
 *
 * @package template
 * @subpackage build
 */
$package = 'vscodemodxelements';
$settings = ['api_token', 'api_url'];
$success  = false;

switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        foreach ($settings as $key) {
            if (isset($options[$key])) {
                $settingObject = $transport->xpdo->getObject(modSystemSetting::class, ['key' => strtolower($package) . '.' . $key]);
                if (!$settingObject) {
                    $settingObject = $transport->xpdo->newObject(modSystemSetting::class);
                    $settingObject->set('key', strtolower($package) . '.' . $key);
                    $settingObject->set('namespace', strtolower($package));
                }

                $settingObject->set('value', $options[$key]);
                $settingObject->save();
            }
        }

        $success = true;
        break;
    case xPDOTransport::ACTION_UNINSTALL:
        $success = true;

        break;
}

return $success;