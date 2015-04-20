<?php

/**
 * Iface view.
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
use \clearos\apps\network\Role as Role;

$this->load->language('beams');
$this->load->language('base');
$this->load->language('network');

///////////////////////////////////////////////////////////////////////////////
// Form modes
///////////////////////////////////////////////////////////////////////////////

$read_only = FALSE;
if ($form_type == 'add') {
    $form_path = '/beams/iface/add';
    $buttons = array(
        form_submit_add('submit'),
        anchor_cancel('/app/beams/network/summary')
    );
} else {
    $form_path = '/beams/iface/edit/' . $interface;
    $buttons = array(
        form_submit_update('submit'),
        anchor_cancel('/app/beams/network/summary')
    );
}

///////////////////////////////////////////////////////////////////////////////
// Form open
///////////////////////////////////////////////////////////////////////////////

echo form_open($form_path);
echo form_header(lang('network_interface'));

echo fieldset_header(lang('base_information'));

echo field_input('nickname', $nickname, lang('beams_nickname'), ($form_type == 'add' ? $read_only : TRUE));
echo field_input('description', $description, lang('base_description'), $read_only);
echo field_dropdown('bootproto', $bootprotos, $bootproto, lang('network_connection_type'), $read_only);

echo fieldset_header(lang('base_settings'));

// Static
//-------

echo field_input('ipaddr', $ipaddr, lang('network_ip'), $read_only);
echo field_input('netmask', $netmask, lang('network_netmask'), $read_only);
echo field_input('gateway', $gateway, lang('network_gateway'), $read_only);

// DHCP
//-----

echo field_input('hostname', $dhcp_hostname, lang('network_hostname'), $read_only);
echo field_checkbox('dhcp_dns', $dhcp_dns, lang('network_automatic_dns_servers'), $read_only);

// PPPoE
//------

echo field_input('username', $username, lang('base_username'), $read_only);
echo field_input('password', $password, lang('base_password'), $read_only);
echo field_input('mtu', $mtu, lang('network_mtu'), $read_only);
echo field_checkbox('pppoe_dns', $pppoe_dns, lang('network_automatic_dns_servers'), $read_only);

echo field_button_set($buttons);

///////////////////////////////////////////////////////////////////////////////
// Form close
///////////////////////////////////////////////////////////////////////////////

echo form_footer();
echo form_close();
