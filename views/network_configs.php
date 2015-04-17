<?php

/**
 * Network configs view.
 *
 * @category   apps
 * @package    beams
 * @subpackage views
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2015 Marine VSAT
 * @license    http://www.clearcenter.com/Company/terms.html ClearSDN license
 * @link       http://www.clearcenter.com/support/documentation/clearos/beams/
 */

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('base');
$this->lang->load('beams');
$this->lang->load('network');

///////////////////////////////////////////////////////////////////////////////
// Anchors
///////////////////////////////////////////////////////////////////////////////

$anchors = array(
    anchor_custom('/app/beams/iface/add', lang('beams_add_network_config')),
    anchor_cancel('/app/beams/satellites/admin')
);

///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////

$headers = array(
    lang('beams_name'),
    lang('base_description'),
    lang('network_protocol')
);

///////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////

foreach ($configs as $id => $config) {
    if ($id == 'default')
        continue;
    
    $detail_buttons = button_set(
        anchor_edit('/app/beams/network/edit/' . $id, 'high'),
        anchor_delete('/app/beams/network/delete/' . $id, 'low')
    );

    ///////////////////////////////////////////////////////////////////////////
    // Item details
    ///////////////////////////////////////////////////////////////////////////

    $item['title'] = $config['name'];
    $item['anchors'] = $detail_buttons;
    $item['details'] = array(
        $config['name'],
        $config['description'],
        $config['protocol']
    );

    $items[] = $item;
}

///////////////////////////////////////////////////////////////////////////////
// Summary table
///////////////////////////////////////////////////////////////////////////////

$options = array(
    'id' => 'network_conf_list'
);

echo summary_table(
    lang('beams_network_options'),
    $anchors,
    $headers,
    $items,
    $options
);
