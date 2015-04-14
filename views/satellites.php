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

///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////

$headers = array(
    lang('beams_provider'),
    lang('beams_satellite_name'),
    lang('base_description'),
    lang('beams_number'),
    lang('beams_tx_power'),
    lang('beams_network_config')
);

///////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////

foreach ($satellites as $id => $satellite) {
    $detail_buttons = button_set(
        array(
            anchor_edit('/app/beams/satellite/edit/' . $id, 'high'),
        )
    );

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
        $satellite['number'],
        $satellite['tx_power'],
        $satellite['network'],
    );

    $items[] = $item;
}

///////////////////////////////////////////////////////////////////////////////
// Summary table
///////////////////////////////////////////////////////////////////////////////

$options = array(
    'id' => 'satellite_list',
    'responsive' => array(4 => 'none', 5 => 'none')
);

echo summary_table(
    lang('beams_beams'),
    NULL,
    $headers,
    $items,
    $options
);
