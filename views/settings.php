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

///////////////////////////////////////////////////////////////////////////////
// Form open
///////////////////////////////////////////////////////////////////////////////

echo form_open('beams/settings');
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
    if ($show_admin)
        $buttons[] = anchor_custom('/app/beams/satellites', lang('beams_configure_satellites'));
}

echo field_input('vessel', $vessel, lang('beams_vessel'), $read_only);
if ($show_admin) {
    echo field_input('hostname', $hostname, lang('beams_hostname'), $read_only);
    echo field_input('username', $username, lang('beams_username'), $read_only);
    echo field_input('password', $password, lang('beams_password'), $read_only);
    echo field_dropdown('interface', $interfaces, $interface, lang('network_interface'), $read_only);
}
echo field_checkbox('autoswitch', $autoswitch, lang('beams_autoswitch'), $read_only);
if ($show_admin) {
    echo field_textarea('email_latlong', implode("\n", $email_latlong), lang('beams_email_lat_long'));
}
echo field_button_set($buttons);

///////////////////////////////////////////////////////////////////////////////
// Form close
///////////////////////////////////////////////////////////////////////////////

echo form_footer();
echo form_close();
