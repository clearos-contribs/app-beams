<?php

/**
 * Beams settings view.
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

if ($modem_connect_failed)
    echo infobox_critical(lang('beams_modem_communication_failure'), $modem_connect_failed);

///////////////////////////////////////////////////////////////////////////////
// Form open
///////////////////////////////////////////////////////////////////////////////

echo form_open('beams/settings/edit');
echo form_header(lang('base_settings'));

///////////////////////////////////////////////////////////////////////////////
// Form fields and buttons
///////////////////////////////////////////////////////////////////////////////

if ($form_type === 'edit') {
    $read_only = FALSE;
    $buttons = array(
        form_submit_update('submit'),
        anchor_cancel('/app/beams')
    );
} else {
    $read_only = TRUE;
    $buttons = array(anchor_edit('/app/beams/settings/edit'));
}

echo field_input('vessel', $vessel, lang('beams_vessel'), $read_only);
if ($show_admin) {
    echo field_input('hostname', $hostname, lang('beams_hostname'), $read_only);
    echo field_input('username', $username, lang('beams_username'), $read_only);
    echo field_password('password', ($read_only ? preg_replace('/./', '*', $password) : $password), lang('beams_password'), $read_only);
    echo field_dropdown('interface', $interfaces, $interface, lang('network_interface'), $read_only);
}
echo field_dropdown('power', $power_options, $power, lang('beams_tx_power'), (!$show_admin || $read_only ? TRUE : FALSE));
echo field_checkbox('auto_switch', $auto_switch, lang('beams_auto_switch'), $read_only);

// Position options
$position_options = array (
    1 => lang('beams_every_1_hour'),
    2 => lang('beams_every_2_hours'),
    3 => lang('beams_every_3_hours'),
    6 => lang('beams_every_6_hours'),
    12 => lang('beams_every_12_hours'),
    24 => lang('beams_every_24_hours')
);
echo field_dropdown('position_report', $position_options, $position_report, lang('beams_send_position_report'), $read_only);

if ($show_admin)
    echo field_textarea('email_latlong', implode("\n", $email_latlong), lang('beams_email_latlong'), $read_only);

echo field_button_set($buttons);

///////////////////////////////////////////////////////////////////////////////
// Form close
///////////////////////////////////////////////////////////////////////////////

echo form_footer();
echo form_close();
