<?php

/**
 * Reset beam view.
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

if (isset($modem_connect_failed))
    echo infobox_critical(lang('beams_modem_communication_failure'), $modem_connect_failed);

echo dialogbox_confirm(sprintf(lang('beams_confirm_reset_beam'), $id), '/app/beams/satellites/reset_beam/' . $id . '/' . $confirm, '/app/beams');
