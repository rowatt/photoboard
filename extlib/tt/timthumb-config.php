<?php
define ('MEMORY_LIMIT', '64M');
define ('ALLOW_EXTERNAL', TRUE);
define ('FILE_CACHE_DIRECTORY', $_SERVER['DOCUMENT_ROOT'] . '/cache' );
$ALLOWED_SITES = array (
	'photoboard.dev',
	'img.photoboard.dev',
	'photoboard.findhorn.cc',
	'img.photoboard.findhorn.cc',
	'dev.photoboard.findhorn.cc',
	'img.dev.photoboard.findhorn.cc'
);
/* EOF */