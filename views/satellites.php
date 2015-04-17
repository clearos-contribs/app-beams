<?php

/**
 * Satellites summary view.
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

if (isset($modem_connect_failed))
    echo infobox_critical(lang('beams_modem_communication_failure'), $modem_connect_failed);

///////////////////////////////////////////////////////////////////////////////
// Anchors
///////////////////////////////////////////////////////////////////////////////

$anchors = NULL;
if ($show_admin)
    $anchors = array(anchor_custom('/app/beams/satellites/admin', lang('beams_admin')));

///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////

$headers = array(
    lang('beams_provider'),
    lang('beams_satellite_name'),
    lang('base_description'),
    lang('beams_position'),
    lang('beams_number')
);

///////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////

foreach ($satellites as $id => $satellite) {
    $button = anchor_custom('/app/beams/satellites/switch_beam/' . $id, lang('beams_switch_beam'), 'high');
    if ($autoswitch == TRUE && $satellite['selected'])
        $button = anchor_custom('/app/beams/satellites/reset_beam/' . $id, lang('base_reset'), 'high');
    else if ($autoswitch != TRUE && $satellite['selected'])
        $button = anchor_custom('/app/beams/satellites/reset_beam/' . $id, lang('base_reset'), 'high');
    
    $detail_buttons = button_set(array($button));

    ///////////////////////////////////////////////////////////////////////////
    // Item details
    ///////////////////////////////////////////////////////////////////////////

    $item['title'] = $satellite['provider'] . "-" . $satellite['name'];
    $item['action'] = '/app/satellite/edit/';
    $item['anchors'] = $detail_buttons;
    $item['details'] = array(
        $satellite['provider'],
        $satellite['name'],
        $satellite['description'],
        $satellite['position'],
        $satellite['number']
    );

    $items[] = $item;
}

///////////////////////////////////////////////////////////////////////////////
// Summary table
///////////////////////////////////////////////////////////////////////////////

$options = array(
    'id' => 'satellite_list',
    'default_rows' => 100,
    'responsive' => array(3 => 'none', 4 => 'none')
);

echo summary_table(
    lang('beams_beams'),
    $anchors,
    $headers,
    $items,
    $options
);
