<?php

if ($modx->context->get('key') == 'mgr') {
    return;
}

if (isset($_GET['q'])) {
    $request_url = explode('/', $_GET['q']);
}

if($request_url[0] !== $modx->getOption('vscodemodxelements.api_url')) return;
unset($request_url[0]);

$_GET['q'] = implode('/', $request_url);

if (!defined('MODX_API_MODE')) define('MODX_API_MODE', true);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
if($modx->vscode->prepare()){
    $modx->vscode->run();
}

exit;