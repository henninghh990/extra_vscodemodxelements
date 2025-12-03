<?php
$events['OnFileManagerUpload']= $modx->newObject('modPluginEvent');
$events['OnFileManagerUpload']->fromArray(array(
	'event' => 'ContentBlocks_RegisterInputs',
	'priority' => 0,
	'propertyset' => 0,
),'',true,true);

return $events;