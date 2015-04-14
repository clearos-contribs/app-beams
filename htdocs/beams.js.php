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

var lang_exec = '<?php echo lang('beams_exec'); ?>';

$(document).ready(function() {
    if ($('#net_name').length > 0) {
        toggle_network_type();
    }
});

function cmd() {
    if ($('#command').val() == 0) {
        $('#whirly').hide();
        return;
    }
    var options = new Object();
    options.text = lang_exec + '<span id="exec_cmd"></span>';
    options.center = true;
    $('#exec_out').html(clearos_loading(options));
    $('#whirly').show();
    $('#exec_cmd').html($('#command').val());
    $.ajax({
        dataType: 'json',
        url: '/app/beams/exec',
        data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&exec=' + $('#command').val(),
        type: 'POST',
        success: function(html) {
            $('#exec_out').html(html);
        },
        error: function(xhr, text, err) {
            $('#whirly').html(xhr.responseText.toString());
        }
    });
}

// vim: syntax=javascript ts=4
