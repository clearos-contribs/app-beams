#!/usr/clearos/sandbox/usr/bin/php
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

use \clearos\apps\base\File as File;
use \clearos\apps\base\Script as Script;
use \clearos\apps\beams\Beams as Beams;

clearos_load_library('base/File');
clearos_load_library('base/Script');
clearos_load_library('beams/Beams');

// Exceptions
//-----------

use \Exception as Exception;

///////////////////////////////////////////////////////////////////////////////
// M A I N
///////////////////////////////////////////////////////////////////////////////

set_time_limit(0);

$beams = new Beams();
$script = new Script();

try {
    if ($script->lock() !== TRUE) {
        echo "Beam timer in progress.\n";
        exit(0);
    }

    $file = new File(Beams::FILE_TIMER, TRUE);
    if (!$file->exists()) {
        echo "Exiting...\n";
        exit(0);
    }

    $timestamp = shell_exec('cat ' . Beams::FILE_TIMER);

    if (time() - $timestamp > 60) {
        $file->delete();
        echo "Time to set power settings and lock status\n";
        $list = $beams->get_beam_selector_list(FALSE, TRUE);
		foreach ($list as $id => $info) {
            if ($info['selected'])
                break;
        }
        try {
            // Lock beam?
            $beams->update_modem_lock();
        } catch (Exception $e) {
            echo "Error updating modem lock: " . clearos_exception_message($e) . "\n";
            clearos_log('beam_timer', "Error setting networking: " . clearos_exception_message($e));
        }
        // See if a push of a default power setting is required
        try {
            // This will save the default power
            $beams->get_power(TRUE);
            $beams->set_power($info['power']);
        } catch (Exception $e) {
            echo "Error setting power: " . clearos_exception_message($e) . "\n";
            clearos_log('beam_timer', "Error setting power: " . clearos_exception_message($e));
        }
    } else {
        echo "Action in " . (60 - (time() - $timestamp)) . " seconds\n";
        $script->unlock();
        exit(0);
    }

    $script->unlock();
} catch (Exception $e) {
    $script->unlock();
}


