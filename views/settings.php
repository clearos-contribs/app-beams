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

if (isset($modem_connect_failed))
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
    echo field_input('modem_hostname', $modem_hostname, lang('beams_modem_hostname'), $read_only);
    echo field_input('modem_username', $modem_username, lang('beams_modem_username'), $read_only);
    echo field_password('modem_password', ($read_only ? preg_replace('/./', '*', $modem_password) : $modem_password), lang('beams_modem_password'), $read_only);
    echo field_dropdown('interface', $interfaces, $interface, lang('network_interface'), $read_only);
}
echo field_dropdown('power', $power_options, $power, lang('beams_tx_power'), (!$show_admin || $read_only ? TRUE : FALSE));
echo field_checkbox('auto_switch', $auto_switch, lang('beams_auto_switch'), $read_only);

echo field_dropdown('position_report', $position_report_options, $position_report, lang('beams_send_position_report'), $read_only);

if ($show_admin)
    echo field_textarea('email_latlong', implode("\n", $email_latlong), lang('beams_email_latlong'), $read_only);

echo field_button_set($buttons);

///////////////////////////////////////////////////////////////////////////////
// Form close
///////////////////////////////////////////////////////////////////////////////

echo form_footer();
echo form_close();
