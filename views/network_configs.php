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

use \clearos\apps\network\Iface as Iface;

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
        array(
            anchor_edit('/app/beams/iface/edit/' . $id, 'high'),
            anchor_delete('/app/beams/iface/delete/' . $id, 'low')
        )
    );

    ///////////////////////////////////////////////////////////////////////////
    // Item details
    ///////////////////////////////////////////////////////////////////////////

    $item['title'] = $config['id'];
    $item['anchors'] = $detail_buttons;
    $protocol = lang('base_unknown');
    if ($config['bootproto'] == Iface::BOOTPROTO_DHCP)
        $protocol = lang('network_bootproto_dhcp'); 
    elseif ($config['bootproto'] == Iface::BOOTPROTO_STATIC)
        $protocol = lang('network_bootproto_static'); 
    elseif ($config['bootproto'] == Iface::BOOTPROTO_PPPOE)
        $protocol = lang('network_bootproto_pppoe'); 
    $item['details'] = array(
        $id,
        $config['description'],
        $protocol
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
