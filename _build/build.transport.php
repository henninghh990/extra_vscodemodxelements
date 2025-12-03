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
 * Template build script
 * 
 */

/* Set package info be sure to set all of these */


/******************************************
 * Work begins here
 * ****************************************/

/* set start time */
$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);


$configFile = __DIR__ . '/config.json';
if (!file_exists($configFile)) {
    exit("Could not load config file: $configFile");
}

$config = json_decode(file_get_contents($configFile), true);

if (!$config || !is_array($config)) {
    exit('Could not load config file.');
}

define('PKG_NAME', $config['displayName']);
define('PKG_NAME_LOWER', $config['lcaseName']);
define('PKG_VERSION', $config['version']);
define('PKG_RELEASE', $config['release']);
define('PKG_CATEGORY', $config['displayName']);

/* define sources */
$root = dirname(dirname(__FILE__)) . '/';
$sources = [
    'root' => $root,
    'build' => $root . '_build/',
    'data' => $root . '_build/data/',
    'resolvers' => $root . '_build/resolvers/',
    'chunks' => $root . 'core/components/' . PKG_NAME_LOWER . '/elements/chunks/',
    'snippets' => $root . 'core/components/' . PKG_NAME_LOWER . '/elements/snippets/',
    'plugins' => $root . 'core/components/' . PKG_NAME_LOWER . '/elements/plugins/',
    'lexicon' => $root . 'core/components/' . PKG_NAME_LOWER . '/lexicon/',
    'docs' => $root . 'core/components/' . PKG_NAME_LOWER . '/docs/',
    'pages' => $root . 'core/components/' . PKG_NAME_LOWER . '/elements/pages/',
    'source_assets' => $root . 'assets/components/' . PKG_NAME_LOWER,
    'source_core' => $root . 'core/components/' . PKG_NAME_LOWER,
];

unset($root);

/* Load MODX */
require_once $sources['build'] . '/build.config.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';


/* Set package options - you can turn these on one-by-one
 * as you create the transport package
 * */
$hasAssets = file_exists($sources['source_assets']); /* Transfer the files in the assets dir. */
$hasCore = file_exists($sources['source_core']);   /* Transfer the files in the core dir. */
$hasSnippets = isset($config['config']['elements']['snippets']) && is_array($config['config']['elements']['snippets']) && count($config['config']['elements']['snippets']) >= 1;
$hasChunks = isset($config['config']['elements']['chunks']) && is_array($config['config']['elements']['chunks']) && count($config['config']['elements']['chunks']) >= 1;
$hasTemplates = isset($config['config']['elements']['templates']) && is_array($config['config']['elements']['templates']) && count($config['config']['elements']['templates']) >= 1;
$hasTemplateVariables = isset($config['config']['elements']['tvs']) && is_array($config['config']['elements']['tvs']) && count($config['config']['elements']['tvs']) >= 1;
$hasPlugins = isset($config['config']['elements']['plugins']) && is_array($config['config']['elements']['plugins']) && count($config['config']['elements']['plugins']) >= 1;
$hasValidator = false; /* Run a validator before installing anything */
$hasResolver = true; /* Run a resolver after installing everything */
$hasSetupOptions = true; /* HTML/PHP script to interact with user */
$hasMenu = false; /* Add items to the MODx Top Menu */
$hasSettings = isset($config['config']['elements']['settings']) && is_array($config['config']['elements']['settings']) && count($config['config']['elements']['settings']) >= 1; /* Add new MODx System Settings */

$modx = new modX();
$modx->initialize('mgr');
echo '<pre>'; /* used for nice formatting of log messages */
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');

/* Create package */
$builder = new MODX\Revolution\Transport\modPackageBuilder($modx);
$builder->directory = dirname(dirname(dirname(dirname(__FILE__)))).'/extras/packages/';
$builder->createPackage(PKG_NAME_LOWER, PKG_VERSION, PKG_RELEASE);
$builder->registerNamespace(PKG_NAME_LOWER, false, true, '{core_path}components/' . PKG_NAME_LOWER . '/', '{assets_path}components/' . PKG_NAME_LOWER . '/');
$modx->log(modX::LOG_LEVEL_INFO, 'Created Transport Package and Namespace.');

/* create category */
$category = $modx->newObject('modCategory');
$category->set('id', 1);
$category->set('category', PKG_CATEGORY);

/* add snippets */
if ($hasSnippets) {
    $modx->log(modX::LOG_LEVEL_INFO, 'Adding in snippets.');
    $snippets = include $sources['data'] . 'transport.snippets.php';
    /* note: Snippets' default properties are set in transport.snippets.php */
    if (is_array($snippets)) {
        $category->addMany($snippets, 'Snippets');
    } else {
        $modx->log(modX::LOG_LEVEL_FATAL, 'Adding snippets failed.');
    }
}

/* add chunks  */
if ($hasChunks) {
    $modx->log(modX::LOG_LEVEL_INFO, 'Adding in chunks.');
    /* note: Chunks' default properties are set in transport.chunks.php */
    $chunks = include $sources['data'] . 'transport.chunks.php';
    if (is_array($chunks)) {
        $category->addMany($chunks, 'Chunks');
    } else {
        $modx->log(modX::LOG_LEVEL_FATAL, 'Adding chunks failed.');
    }
}

/* add templates  */
if ($hasTemplates) {
    $modx->log(modX::LOG_LEVEL_INFO, 'Adding in templates.');
    /* note: Templates' default properties are set in transport.templates.php */
    $templates = include $sources['data'] . 'transport.templates.php';
    if (is_array($templates)) {
        if (!$category->addMany($templates, 'Templates')) {
            $modx->log(modX::LOG_LEVEL_INFO, 'addMany failed with templates.');
        }
        ;
    } else {
        $modx->log(modX::LOG_LEVEL_FATAL, 'Adding templates failed.');
    }
}

/* add templatevariables  */
if ($hasTemplateVariables) {
    $modx->log(modX::LOG_LEVEL_INFO, 'Adding in Template Variables.');
    /* note: Template Variables' default properties are set in transport.tvs.php */
    $templatevariables = include $sources['data'] . 'transport.tvs.php';
    if (is_array($templatevariables)) {
        $category->addMany($templatevariables, 'TemplateVars');
    } else {
        $modx->log(modX::LOG_LEVEL_FATAL, 'Adding templatevariables failed.');
    }
}

/* add plugins  */
if ($hasPlugins) {
    $modx->log(modX::LOG_LEVEL_INFO, 'Adding in Plugins.');
    $plugins = include $sources['data'] . 'transport.plugins.php';
    if (is_array($plugins)) {
        $category->addMany($plugins);
    }
}

/* Create Category attributes array dynamically
 * based on which elements are present
 */

$attr = array(
    xPDOTransport::UNIQUE_KEY => 'category',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true,
);

if ($hasValidator) {
    $attr[xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL] = true;
}

if ($hasSnippets) {
    $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Snippets'] = array(
        xPDOTransport::PRESERVE_KEYS => false,
        xPDOTransport::UPDATE_OBJECT => true,
        xPDOTransport::UNIQUE_KEY => 'name',
    );
}

if ($hasChunks) {
    $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Chunks'] = array(
        xPDOTransport::PRESERVE_KEYS => false,
        xPDOTransport::UPDATE_OBJECT => true,
        xPDOTransport::UNIQUE_KEY => 'name',
    );
}

if ($hasPlugins) {
    $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Plugins'] = array(
        xPDOTransport::PRESERVE_KEYS => false,
        xPDOTransport::UPDATE_OBJECT => true,
        xPDOTransport::UNIQUE_KEY => 'name',
        xPDOTransport::RELATED_OBJECTS => true,
        xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
            'PluginEvents' => array(
                xPDOTransport::PRESERVE_KEYS => true,
                xPDOTransport::UPDATE_OBJECT => false,
                xPDOTransport::UNIQUE_KEY => array('pluginid', 'event'),
            ),
        ),
    );
}

if ($hasTemplates) {
    $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Templates'] = array(
        xPDOTransport::PRESERVE_KEYS => false,
        xPDOTransport::UPDATE_OBJECT => true,
        xPDOTransport::UNIQUE_KEY => 'templatename',
    );
}

if ($hasTemplateVariables) {
    $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['TemplateVars'] = array(
        xPDOTransport::PRESERVE_KEYS => false,
        xPDOTransport::UPDATE_OBJECT => true,
        xPDOTransport::UNIQUE_KEY => 'name',
    );
}

/* create a vehicle for the category and all the things
 * we've added to it.
 */
$vehicle = $builder->createVehicle($category, $attr);

if ($hasValidator) {
    $modx->log(modX::LOG_LEVEL_INFO, 'Adding in Script Validator.');
    $vehicle->validate('php', array(
        'source' => $sources['validators'] . 'validate.settings.php',
    ));
}

/* package in script resolver if any */
if ($hasResolver) {
    $modx->log(modX::LOG_LEVEL_INFO, 'Adding in Script Resolver.');
    $vehicle->resolve('php', array(
        'source' => $sources['resolvers'] . 'resolve.settings.php',
    ));
}

if ($hasCore) {
    $vehicle->resolve('file', array(
        'source' => $sources['source_core'],
        'target' => "return MODX_CORE_PATH . 'components/';",
    ));
}

if ($hasAssets) {
    $vehicle->resolve('file', array(
        'source' => $sources['source_assets'],
        'target' => "return MODX_ASSETS_PATH . 'components/';",
    ));
}

$builder->putVehicle($vehicle);

/* Transport Menus */
if ($hasMenu) {
    /* load menu */
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaging in menu...');
    $menu = include $sources['data'] . 'transport.menu.php';
    if (empty($menu)) {
        $modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in menu.');
    } else {
        $vehicle = $builder->createVehicle($menu, array(
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'text',
            xPDOTransport::RELATED_OBJECTS => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
                'Action' => array(
                    xPDOTransport::PRESERVE_KEYS => false,
                    xPDOTransport::UPDATE_OBJECT => true,
                    xPDOTransport::UNIQUE_KEY => array('namespace', 'controller'),
                ),
            ),
        ));
        $builder->putVehicle($vehicle);

        $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($menu) . ' menu items.');
        unset($vehicle, $menu);
    }
}

/* load system settings */
if ($hasSettings) {
    $settings = include $sources['data'] . 'transport.settings.php';
    if (!is_array($settings)) {
        $modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in settings.');
    } else {
        $attributes = array(
            xPDOTransport::UNIQUE_KEY => 'key',
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => false,
        );
        foreach ($settings as $setting) {
            $vehicle = $builder->createVehicle($setting, $attributes);
            $builder->putVehicle($vehicle);
        }
        $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($settings) . ' System Settings.');
        unset($settings, $setting, $attributes);
    }
}

/* Next-to-last step - pack in the license file, readme.txt, changelog,
 * and setup options
 */

$setupOptions = file_exists($sources['build'].'setup.options.php') ? ['source' => $sources['build'].'setup.options.php'] : [];
$builder->setPackageAttributes(array(
    'license' => file_get_contents($sources['docs'] . 'license.txt'),
    'readme' => file_get_contents($sources['docs'] . 'readme.txt'),
    'changelog' => file_get_contents($sources['docs'] . 'changelog.txt'),
    'setup-options' => ['source' => $sources['build'].'setup.options.php'],
));

/* Last step - zip up the package */
$builder->pack();

/* Copy existing config.json into _packages */
$packageName = PKG_NAME_LOWER . '-' . PKG_VERSION . '-' . PKG_RELEASE;
$targetFile = dirname(dirname($sources['root'])) . '/extras/packages/' . $packageName . '.json';
$sourceFile = dirname(__FILE__) . '/config.json';

if (!copy($sourceFile, $targetFile)) {
    $modx->log(modX::LOG_LEVEL_ERROR, "Failed to copy $sourceFile to $targetFile");
} else {
    $modx->log(modX::LOG_LEVEL_INFO, "Copied config.json to $targetFile");
}

// Save or update database record
$namespace = "Extras\\Model";
$packageRecord = $modx->getObject($namespace.'\\extrasPackage', ['signature' => $packageName]);
if(!$packageRecord) {
    $packageRecord = $modx->newObject($namespace.'\\extrasPackage', [
        'signature' => $packageName,
        'createdon' => date('Y-m-d H:i:s')
    ]);
}

$packageRecord->set('editedon', date('Y-m-d H:i:s'));
$packageRecord->save();

$modx->log(modX::LOG_LEVEL_INFO,"Database record saved.\n");

/* report how long it took */
$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

$modx->log(xPDO::LOG_LEVEL_INFO, "Package Built.");
$modx->log(xPDO::LOG_LEVEL_INFO, "Execution time: {$totalTime}");

exit();