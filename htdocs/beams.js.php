<?php

/**
 * Javascript helper for Beams.
 *
 * @category   apps
 * @package    beams
 * @subpackage javascript
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2015 Marine VSAT
 * @license    http://www.clearcenter.com/Company/terms.html ClearSDN license
 * @link       http://www.clearcenter.com/support/documentation/clearos/beams/
 */

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

clearos_load_language('beams');
clearos_load_language('base');

header('Content-Type: application/x-javascript');

?>

var lang_warning = '<?php echo lang('base_warning'); ?>';
var lang_status = '<?php echo lang('beams_modem_status'); ?>';
var lang_network = '<?php echo lang('base_network'); ?>';
var lang_transmit = '<?php echo lang('beams_transmit'); ?>';
var lang_receive = '<?php echo lang('beams_receive'); ?>';

$(document).ready(function() {
    $('tbody', $('#sidebar_summary_table')).append(
        '<tr>' +
        '  <td><b>' + lang_receive + '</b></td>' +
        '  <td><i id="beams_receive" class="fa fa-circle"></i></td>' +
        '</tr>' +
        '<tr>' +
        '  <td><b>' + lang_transmit + '</b></td>' +
        '  <td><i id="beams_transmit" class="fa fa-circle"></i></td>' +
        '</tr>' +
        '<tr>' +
        '  <td><b>' + lang_network + '</b></td>' +
        '  <td><i id="beams_network" class="fa fa-circle"></i></td>' +
        '</tr>' +
        '<tr>' +
        '  <td><b>' + lang_status + '</b></td>' +
        '  <td><i id="beams_status" class="fa fa-circle"></i></td>' +
        '</tr>'
    );

    set_interface_fields();
    if ($('#net_name').length > 0) {
        toggle_network_type();
    }
    $('#command').on('click', function(e) {
        e.preventDefault();
        if ($('#command').val() == 0)
            $('#terminal_out').html('');
        else
            execute_command($('#command').val());
    });
    $('#bootproto').change(function() {
        set_interface_fields();
    });
    get_modem_status();
});

function execute_command(command) {
    $('#terminal_out').html('<div class="theme-loading-small" id="terminal_wait"></div>');
    $.ajax({
        dataType: 'json',
        url: '/app/beams/modem/execute',
        data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&command=' + command,
        type: 'POST',
        success: function(json) {
            $('#terminal_out').html('');
            $.each(json, function (id, line) {
                $('#terminal_out').append('<span>' + line + '</span>');
            });
        },
        error: function(xhr, text, err) {
            $('#terminal_out').html('');
            clearos_dialog_box('error', lang_warning, xhr.responseText.toString());
        }
    });
}

/**
 * Sets visibility of network interface fields.
 */

function set_interface_fields() {
    // Static
    $('#ipaddr_field').hide();
    $('#netmask_field').hide();
    $('#gateway_field').hide();
    $('#enable_dhcp_field').hide();

    // DHCP
    $('#hostname_field').hide();
    $('#dhcp_dns_field').hide();

    // PPPoE
    $('#username_field').hide();
    $('#password_field').hide();
    $('#mtu_field').hide();
    $('#pppoe_dns_field').hide();

    type = $('#bootproto').val();

    if (type == 'static') {
        $('#ipaddr_field').show();
        $('#netmask_field').show();
        $('#gateway_field').show();
        $('#enable_dhcp_field').show();
    } else if (type == 'dhcp') {
        $('#hostname_field').show();
        $('#dhcp_dns_field').show();
    } else if (type == 'pppoe') {
        $('#username_field').show();
        $('#password_field').show();
        $('#mtu_field').show();
        $('#pppoe_dns_field').show();
    }
}

function get_modem_status() {
    $.ajax({
        type: 'GET',
        dataType: 'json',
        url: '/app/beams/modem/status',
        success: function(json) {
            console.log(json);
            if (json != undefined) {
                // Status
                if (json.state == 1)
                    $('#beams_status').addClass('beams-status-green');
                else if (json.state == 3)
                    $('#beams_status').addClass('beams-status-red');
                // Network
                if (json.network == 1)
                    $('#beams_network').addClass('beams-status-green');
                else if (json.network == 3)
                    $('#beams_network').addClass('beams-status-red');
            }
            window.setTimeout(get_modem_status, 1000);
        }
    });
}

// vim: syntax=javascript ts=4
