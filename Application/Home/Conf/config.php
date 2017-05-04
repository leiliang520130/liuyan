<?php
return array(
        'ENCRYPT_PASSWORD'  =>  'cosense_password',
        'ENCRYPT_EMAIL' => 'cosense_email',

	// 默认模板主题名称
        'DEFAULT_THEME' => 'Default/',

	'TMPL_PARSE_STRING' => array (
                '__STATIC__' => __ROOT__ . '/Public/Static',
                '__IMG__' => __ROOT__ . '/Public/' . MODULE_NAME . '/images',
                '__CSS__' => __ROOT__ . '/Public/' . MODULE_NAME . '/css',
                '__ASSETS__' => __ROOT__ . '/Public/' . MODULE_NAME . '/assets',
                '__JS__' => __ROOT__ . '/Public/' . MODULE_NAME . '/js',
                '__FA__' => __ROOT__ . '/Public/' . MODULE_NAME . '/font-awesome',
                '__EXT__' => __ROOT__ . '/Public/' . MODULE_NAME . '/ext'
        )
);