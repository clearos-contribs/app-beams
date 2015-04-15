<?php

/**
 * Beams class.
 *
 * @category   apps
 * @package    beams
 * @subpackage scripts
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2015 Marine VSAT
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/beams/
 */

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('base');
clearos_load_language('beams');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// Classes
//--------

use \clearos\apps\base\Script as Script;
use \clearos\apps\beams\Beams as Beams;

clearos_load_library('base/Script');
clearos_load_library('beams/Beams');

// Exceptions
//-----------

use \Exception as Exception;

///////////////////////////////////////////////////////////////////////////////
// M A I N
///////////////////////////////////////////////////////////////////////////////

$beams = new Beams();
$script = new Script();

if ($script->lock() !== TRUE) {
    echo "Beam notification in progress.\n";
    exit(0);
}

try {
	$beams->update_modem_lock();
} catch (Exception $e) {
    echo "Error updating modem lock: " . clearos_exception_message($e) . "\n";
    clearos_log('beam_notification', "Error setting networking: " . clearos_exception_message($e));
}
try {
	$beams->SendLatLong();
} catch (Exception $e) {
    echo "Error sending lat/long: " . clearos_exception_message($e) . "\n";
    clearos_log('beam_notification', "Error sending lat/long: " . clearos_exception_message($e));
}

$script->unlock();

// vim: syntax=php ts=4
