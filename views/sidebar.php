<?php

/**
 * Beam Sidebar view.
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

$this->lang->load('beams');

echo sidebar_header(lang('beams_controls'), array('id' => 'beam-controls'));
echo sidebar_text(anchor_custom('/app/beams/network', lang('beams_network_status')));
echo sidebar_text(anchor_custom('/app/beams/modem/terminal', lang('beams_terminal')));
echo sidebar_text(anchor_custom('/app/beams/modem/reboot', lang('beams_reboot_modem')));
echo sidebar_footer();
