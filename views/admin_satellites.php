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
// Anchors
///////////////////////////////////////////////////////////////////////////////

$anchors = array(anchor_cancel('/app/beams'));

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
    if ($satellite['available']) {
        $detail_buttons = button_set(
            array(
                anchor_custom('/app/beams/satellites/disable/' . $id, lang('base_disable'), 'high'),
                anchor_edit('/app/beams/satellites/edit/' . $id, 'low')
            )
        );
    } else {
        $detail_buttons = button_set(
            array(
                anchor_custom('/app/beams/satellites/enable/' . $id, lang('base_enable'), 'high'),
                anchor_edit('/app/beams/satellites/edit/' . $id, 'low')
            )
        );
    }

    ///////////////////////////////////////////////////////////////////////////
    // Item details
    ///////////////////////////////////////////////////////////////////////////

    $item['title'] = $satellite['provider'] . "-" . $satellite['name'];
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
    'id' => 'satellite_admin_list',
    'responsive' => array(0 => 'none', 4 => 'none', 5 => 'none')
);

echo summary_table(
    lang('beams_beams'),
    $anchors,
    $headers,
    $items,
    $options
);
