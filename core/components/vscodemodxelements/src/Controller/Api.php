<?php
declare(strict_types=1);

namespace VSCodeApi\Controller;
use Exception;
class Api {

    public $modx;
    private $method;
    private $data;
    private $request;
    private $first, $second, $third, $fourth, $fifth;

    private $allowedElements = ['modSnippet', 'modChunk', 'modPlugin', 'modTemplate'];
    private $expectedToken;


    public function __construct(&$modx){
        $this->modx = $modx;
    }

    public function prepare(){

        if (!defined('MODX_API_MODE')) {
            define('MODX_API_MODE', true);
        }
        if(!$this->getRequest()) return false;
        $this->getData();
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);

        $this->expectedToken = getenv('MODX_VSCODE_TOKEN');
        if (!$this->expectedToken && is_object($this->modx)) {
            $this->expectedToken = (string) $this->modx->getOption('vscodemodxelements.api_token', null, '');
        }

        return true;

    }

    public function run(){
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Filename');
        
        $this->validateToken();
        if(method_exists($this, $this->method)) $this->{$this->method}();
        $this->failure('Method does not exist');
    }

    public function get(){
        if($this->first === 'ping') $this->ping();
        if(in_array($this->first, $this->allowedElements)) $this->getElement();
    }

    private function getElement(){
        if($this->second) $this->getOne();
        $this->getList();
        
    }

    private function getOne(){
        $obj = $this->modx->getObject($this->first, $this->second);
        if(!$obj) $this->failure('Object not found', 404);
        $this->success($this->toData($obj));
    }

    private function getList(){
        $q = $this->modx->newQuery($this->first);
        
        $rows = [];
        foreach ($this->modx->getIterator($this->first, $q) as $element) {
            $rows[] = $this->toData($element);
        }
        $this->success($rows);
    }

    private function put(){
        // Update element
        if(!$this->first) $this->failure('No element specified', 400);
        if(!in_array($this->first, $this->allowedElements)) $this->failure("Updating {$this->first} not allowed");
        if(!$this->second) $this->failure('No ID specified', 400);

        //Fields
        $content = ((string) $this->data['content'] ?: null);
        $name = ((string) $this->data['name'] ?: null);
                
        $obj = $this->modx->getObject($this->first, $this->second);
        if (!$obj) $this->failure('Object not found', 404);

        if($content){
            $isStatic = method_exists($obj, 'isStatic') ? $obj->isStatic() : false;
            if ($isStatic && method_exists($obj, 'setFileContent')) {
                $obj->setFileContent($content);
            } else {
                if (method_exists($obj, 'setContent')) {
                    $obj->setContent($content);
                } elseif (property_exists($obj, 'snippet')) {
                    $obj->set('snippet', $content);
                } else {
                    $obj->set('content', $content);
                }
            }
        }

        if($name){
            $nameField = $this->first === 'modTemplate' ? 'templatename' : 'name';
            $obj->set($nameField, $name);
        }


        $this->success(['content' => $obj->toArray() ]);


        $saved = $obj->save(true);
        if(!$saved) $this->failure('Could not save');
        $this->success(['saved' => $saved]);
    }

    private function post(){
        if(!$this->first) $this->failure('No element specified', 400);
        if(!in_array($this->first, $this->allowedElements)) $this->failure("Creating {$this->first} not allowed");
        if(!isset($this->data['name'])) $this->failure('Missing name', 400);

        $obj = $this->modx->newObject($this->first);
        if (!$obj) $this->failure('Error creating object', 400);

        $nameField = $this->first === 'modTemplate' ? 'templatename' : 'name';
        $obj->set($nameField, $this->data['name']);


        if(!$obj->save()) $this->failure('Error saving object');

        $this->success($this->toData($obj));
    }

    private function ping(){
        $this->success([
            'pong'      => true,
            'time'      => date('c'),
            'site_name' => is_object($this->modx) ? (string)$this->modx->getOption('site_name') : null,
            'elements'  => $this->allowedElements
        ]);
    }

    private function getPhpInputBody(): void {
        $raw = file_get_contents('php://input');
        if ($raw === '' || $raw === false) $this->failure(['error' => 'Invalid JSON body'], 400);
        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->failure(['error' => 'Invalid JSON body'], 400);
        }
        $this->data = is_array($data) ? $data : [];
    }

    private function getData(): void {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST': $this->getPhpInputBody();
            case 'PUT': $this->getPhpInputBody();
        }
    }

    private function bearerToken(): ?string {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        if (stripos($auth, 'Bearer ') === 0) {
            return trim(substr($auth, 7));
        }
        if (isset($_GET['token'])) return (string)$_GET['token'];
        return null;
    }

    private function getRequest(): bool{
        if(isset($_GET['q'])){

            $request = explode('/', trim($_GET['q'], '/'));
            $this->request = $request;
            if(isset($request[0])) $this->first = $request[0];
            if(isset($request[1])) $this->second = $request[1];
            if(isset($request[2])) $this->third = $request[2];
            if(isset($request[3])) $this->fourth = $request[3];
            if(isset($request[4])) $this->fifth = $request[4];
        }

        return true;

    }

    private function validateToken(){
        $token = $this->bearerToken();
        if (!$this->expectedToken || !$token || !hash_equals($this->expectedToken, $token)) {
            header('WWW-Authenticate: Bearer realm="MODX VSCode API"');
            $this->failure(['error' => 'Unauthorized'], 401);
        }
    }

    private function toData($obj): array {

        $isPhp = in_array($obj->_alias, ['MODX\\Revolution\\modSnippet', 'MODX\\Revolution\\modPlugin']);
        $isTemplate = $obj->_alias === 'MODX\\Revolution\\modTemplate';
        return [
            'id'      => (int) $obj->id,
            'name'    => (string) $isTemplate ? $obj->templatename : $obj->name,
            'content' => ($isPhp ? "<?php\n" : '') . (string) $obj->content,
            'type'    => $obj->_alias,
            'debug'   => $obj->toArray()
        ];
    }

    private function success($data, $status = 200){
        
        $this->respond([
            'success' => true,
            'data' => $data
        ], $status);
    }

    private function failure($error, $status = 400){
        
        $this->respond([
            'success' => false,
            'error' => $error
        ], $status);
    }
    private function respond($data, $status = 200){
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        $backTrace = debug_backtrace();
        $data['debug'] = [
            'request' => $this->request,
            'method' => $this->method,
            'input' => $this->data,
            'function' => $backTrace[2]['function']
        ];

        if($this->modx->getOption('vscodemodxelements.debug_log')) $this->modx->log(1, print_r($data, true));
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}