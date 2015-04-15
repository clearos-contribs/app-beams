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

header('Content-Type: application/x-javascript');

?>

var lang_warning = '<?php echo lang('base_warning'); ?>';

$(document).ready(function() {
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
});

function execute_command(command) {
    var options = new Object;
    options.center = true;
    options.id = 'terminal_wait';

    $('#terminal_out').html(clearos_loading(options));
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

// vim: syntax=javascript ts=4
