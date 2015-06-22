<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'beams';
$app['version'] = '1.0.0';
$app['release'] = '1';
$app['vendor'] = 'Marine VSAT';
$app['packager'] = 'ClearFoundation';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['description'] = lang('beams_app_description');

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('beams_app_name');
$app['category'] = lang('base_category_network');
$app['subcategory'] = lang('base_subcategory_settings');

/////////////////////////////////////////////////////////////////////////////
// Controllers
/////////////////////////////////////////////////////////////////////////////

$app['controllers']['beams']['title'] = $app['name'];
$app['controllers']['settings']['title'] = lang('base_settings');
$app['controllers']['notification']['title'] = lang('mail_smtp_notification_settings');

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

$app['core_requires'] = array(
    'app-firewall-custom-core'
);

$app['core_file_manifest'] = array(
   'app-beams.cron' => array('target' => '/etc/cron.d/app-beams'),
   'beams.conf' => array(
        'target' => '/etc/clearos/beams.conf',
        'mode' => '0644',
        'owner' => 'webconfig',
        'group' => 'webconfig',
        'config' => TRUE,
        'config_params' => 'noreplace',
    )
);

$app['core_directory_manifest'] = array(
   '/var/clearos/beams.d' => array('mode' => '755', 'owner' => 'root', 'group' => 'root')
);

$app['delete_dependency'] = array(
    'app-beams-core'
);
