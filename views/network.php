<?php

/**
 * Network summary view.
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
$this->lang->load('network');
$this->lang->load('beams');

///////////////////////////////////////////////////////////////////////////////
// Anchors
///////////////////////////////////////////////////////////////////////////////

$anchors = array(anchor_cancel('/app/beams'));

///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////

$headers = array(
    lang('network_interface'),
    lang('beams_nickname'),
    lang('network_role'),
    lang('network_ip'),
    lang('base_status')
);

///////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////

foreach ($interfaces as $name => $info) {

    ///////////////////////////////////////////////////////////////////////////
    // Item details
    ///////////////////////////////////////////////////////////////////////////

    if (!$info['configured'])
                continue;

    $ip = empty($info['address']) ? '' : $info['address'];
    $role = isset($info['role']) ? $info['role'] : "";
    $roletext = isset($info['roletext']) ? $info['roletext'] : "";
    $status = lang('beams_no_link');
    if (isset($info['link'])) {
        if ($info['link'] == 1) {
            if ($info['fw_disabled'])
                $status = lang('beams_disabled');
            else
                $status = lang('beams_active');
        }
    }

    $buttons = array(
        anchor_edit('/app/beams/network/edit/' . $name, 'high')
    );

        $buttons[] = anchor_custom('/app/beams/network/toggle/' . $name, lang('beams_toggle_status'), 'low');

    $item['title'] = $name;
    $item['anchors'] = button_set($buttons);
    $item['details'] = array(
        $name,
        $info['nickname'],
        $roletext,
        $ip,
        $status
    );

    $items[] = $item;
}

///////////////////////////////////////////////////////////////////////////////
// Summary table
///////////////////////////////////////////////////////////////////////////////

$options = array(
    'id' => 'network_list',
    'responsive' => array(2 => 'none', 3 => 'none')
);

echo summary_table(
    lang('beams_network_status'),
    $anchors,
    $headers,
    $items,
    $options
);
