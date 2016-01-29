<?php

/**
 * Beam add view.
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

$this->lang->load('network');
$this->lang->load('beams');

if ($fatal_error)
    echo infobox_warning(lang('base_warning'), $fatal_error);

///////////////////////////////////////////////////////////////////////////////
// Form open
///////////////////////////////////////////////////////////////////////////////

echo form_open('beams/satellites/add');
echo form_header(lang('base_settings'));

///////////////////////////////////////////////////////////////////////////////
// Form fields and buttons
///////////////////////////////////////////////////////////////////////////////

$buttons = array(
    form_submit_add('submit'),
    anchor_custom('/app/beams/network/summary', lang('beams_network_options')),
    anchor_cancel('/app/beams/satellites/admin')
);

echo field_input('provider', $beam['provider'], lang('beams_provider'), FALSE);
echo field_input('name', $beam['name'], lang('beams_satellite_name'), FALSE);
echo field_input('number', $beam['number'], lang('beams_satellite_number'), FALSE);
echo field_input('position', $beam['position'], lang('beams_position'), FALSE);
echo field_input('description', $beam['description'], lang('base_description'), FALSE);
echo field_dropdown('network', $network_options, $beam['interface_config'], lang('beams_network_config'), FALSE);
echo field_dropdown('tx', $power_options, $beam['tx'], lang('beams_tx_power'), FALSE);
echo field_toggle_enable_disable('available', (isset($beam['available']) ? $beam['available'] : TRUE), lang('beams_available'), FALSE);
echo field_button_set($buttons);

///////////////////////////////////////////////////////////////////////////////
// Form close
///////////////////////////////////////////////////////////////////////////////

echo form_footer();
echo form_close();
