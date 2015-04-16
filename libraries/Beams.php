<?php

/**
 * Beams class.
 *
 * @category   apps
 * @package    beams
 * @subpackage libraries
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2015 Marine VSAT
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/beams/
 */

///////////////////////////////////////////////////////////////////////////////
// N A M E S P A C E
///////////////////////////////////////////////////////////////////////////////

namespace clearos\apps\beams;

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('beams');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// Classes
//--------

use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\Configuration_File as Configuration_File;
use \clearos\apps\base\File as File;
use \clearos\apps\base\Folder as Folder;
use \clearos\apps\base\Shell as Shell;
use \clearos\apps\date\NTP_Time as NTP_Time;
use \clearos\apps\firewall_custom\Firewall_Custom as Firewall_Custom;
use \clearos\apps\network\Hosts as Hosts;
use \clearos\apps\network\Iface_Manager as Iface_Manager;
use \clearos\apps\tasks\Cron as Cron;

clearos_load_library('base/Engine');
clearos_load_library('base/Configuration_File');
clearos_load_library('base/File');
clearos_load_library('base/Folder');
clearos_load_library('base/Shell');
clearos_load_library('date/NTP_Time');
clearos_load_library('firewall_custom/Firewall_Custom');
clearos_load_library('network/Hosts');
clearos_load_library('network/Iface_Manager');
clearos_load_library('tasks/Cron');

// Exceptions
//-----------

use \clearos\apps\base\Engine_Exception as Engine_Exception;
use \clearos\apps\base\Validation_Exception as Validation_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/Validation_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Beams class.
 *
 * @category   apps
 * @package    beams
 * @subpackage libraries
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2015 Marine VSAT
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/beams/
 */

class Beams extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const SAT_BEAM_NOTE = 'SAT_BEAM_NIC_TOGGLE-';
    const FILE_CONFIG = '/etc/clearos/beams.conf';
    const IPTABLES_BLOCK = 'iptables -I INPUT -i %s -j DROP';
    const FILE_LAT_LONG_CACHE = '/var/clearos/framework/cache/beams-latlong.cache';
    const FILE_CRONFILE = "app-beams";
    const FILE_TIMER = "/var/clearos/framework/tmp/beams.timer";
    const CMD_NOTIFY_SCRIPT = '/usr/clearos/app/beams/deploy/beam_notification.php';

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $config = null;
    protected $is_loaded = FALSE;
    private $_connection;
    private $_data;
    private $_timeout = 10;
    private $_prompt;
    private $_test = TRUE;
    private $_test_function = NULL;

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Beams constructor.
     */

    function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Run modem command.
     *
     * @param String $command command
     *
     * @return array
     * @throws Engine_Exception
     */

    function run_modem_command($command)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        Validation_Exception::is_valid($this->validate_command($command));

        $this->_connect();
        $this->_send($command);

        if ($this->_test)
            $this->_test_function = "run_modem_command: " . $command;

        $this->_read_to($this->_prompt);
        $this->_data = explode("\n", $this->_data);
        foreach ($this->_data as $mydata) {
            if (preg_match('/^\\[.*telnet.*\d/', $mydata))
                continue;
            if (preg_match('/^>$/', $mydata))
                continue;
            $filtered_data[] = $mydata;
        }
        unset($this->_data);
        $this->_data = $filtered_data;
        $this->_close();
        return $this->_data;
    }

    /**
     * Reboot modem.
     *
     * @return void
     * @throws Engine_Exception
     */

    function reboot_modem()
    {
        clearos_profile(__METHOD__, __LINE__);
        $this->_ssh('reboot');
    }

    /**
     * Returns Hostname.
     *
     * @return String
     * @throws Engine_Exception
     */

    function get_hostname()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        return $this->config['hostname'];
    }

    /**
     * Returns Username.
     *
     * @return String
     * @throws Engine_Exception
     */

    function get_username()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        return $this->config['username'];
    }

    /**
     * Returns interface.
     *
     * @return String
     * @throws EngineException
     */

    function get_interface()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        if (isset($this->config['interface']))
            return $this->config['interface'];
        $iface_manager = new Iface_Manager();
        $ifaces = $iface_manager->get_external_interfaces();
        $iface = reset($ifaces);
        return $iface;
    }

    /**
     * Returns Password.
     *
     * @return String
     * @throws Engine_Exception
     */

    function get_password()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        return $this->config['password'];
    }

    /**
     * Returns vessel.
     *
     * @return String
     * @throws Engine_Exception
     */

    function get_vessel()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        return $this->config['vessel'];
    }

    /**
     * Returns auto switch.
     *
     * @return boolean
     * @throws Engine_Exception
     */

    function get_auto_switch()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        return $this->config['auto_switch'];
    }

    /**
     * Returns email lat/long.
     *
     * @return array
     * @throws Engine_Exception
     */

    function get_email_latlong()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        return explode(',', $this->config['email_latlong']);
    }

    /**
     * Returns interface configuration options.
     *
     * @return array
     * @throws EngineException
     */

    function get_interface_configs()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        if (isset($this->config['interface_config']))
            return json_decode($this->config['interface_config'], TRUE);

        $default = array('default' => array('description' => lang('base_default')));
        return $default;
    }

    /**
     * Update/add a interface configuration options.
     *
     * @param array $data interface options
     *
     * @return void
     * @throws EngineException
     */

    function update_network_conf($data)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        if (isset($this->config['interface_config'])) {
            $definitions = json_decode($this->config['interface_config'], TRUE);
            $definitions[key($data)] = $data[key($data)];
            $this->_set_parameter('interface_config', json_encode($definitions));
        }
    }

    /**
     * Deletes a interface configuration options.
     *
     * @param string $id network interface ID
     *
     * @return void
     * @throws EngineException
     */

    function delete_network_config($id)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        if (isset($this->config['interface_config'])) {
            $definitions = json_decode($this->config['interface_config'], TRUE);
            unset($definitions[$id]);
            $this->_set_parameter('interface_config', json_encode($definitions));
        }
        // Find any references to override and delete

        $list = $this->get_beam_selector_list(false, false);
        foreach ($list as $beam_id => $info) {
            if (isset($this->config['ifconfig_' . $beam_id]) && $this->config['ifconfig_' . $beam_id] == $id)
                $this->_set_parameter('ifconfig_' . $beam_id, 'default');
        }
    }

    /**
     * Returns TX Power.
     *
     * @param boolean $default use modem default
     *
     * @return String
     * @throws EngineException
     */

    function get_power($default = FALSE)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        $power = 0;
        try {
            $output = $this->run_modem_command('tx power');
            foreach ($output as $line) {
                if (preg_match("/^Tx Power.*=\s* (-\s*\d+\.\d+)\s*dbm.*/", $line, $match)) {
                    $power = round(preg_replace('/\s/', '', $match[1]), 0);
                    break;
                }
            }
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
        if ($default)
            $this->_set_parameter('tx_power', (int)$power);
        else if ((int)$power == (int)$this->config['tx_power'])
            $power = 0;
        return (int)$power;
    }

    /**
     * Returns Position Report Interval.
     *
     * @return String
     * @throws Engine_Exception
     */

    function get_position_report()
    {
        clearos_profile(__METHOD__, __LINE__);

        $cronlines = "";
    
        try {
            $cron = new Cron();
            $cronrawdata = $cron->get_configlet(self::FILE_CRONFILE);
            $cronlines = explode("\n", $cronrawdata);
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }

        foreach ($cronlines as $line) {
            if ( (! preg_match("/^#/", $line)) && (! preg_match("/^\s*$/", $line))) {
                $rawline = $line;
                break;
            }
        }

        // Parse the cron info 
        //--------------------

        $cronentries = explode(" ", $rawline, 7);
        // If day is set, return 24 hours
        if (preg_match('/^(\d+)$/', $cronentries[1]))
            return 24;
        if (preg_match('/\*\/(\d+)/', $cronentries[1], $match))
            return $match[1];

        // Return default 6 hrs
        return 6;
    }

    /**
     * Set Hostname.
     *
     * @param $hostname hostname
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_hostname($hostname)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        // Validation
        // ----------

        Validation_Exception::is_valid($this->validate_hostname($hostname));

        $this->_set_parameter('hostname', $hostname);
    }

    /**
     * Set Position Report Interval.
     *
     * @param $interval interval
     *
     * @return void
     * @throws EngineException
     */

    function set_position_report($interval)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $cron = new Cron();

            if ($cron->ExistsCrondConfiglet(self::FILE_CRONFILE))
                $cron->DeleteCrondConfiglet(self::FILE_CRONFILE);

            $hour = 2;
            if ($interval < 24)
                $hour = "*/$interval";
    
            $cron->add_crond_configlet_by_parts(self::FILE_CRONFILE, 0, $hour, "*", "*", "*", "root", self::CMD_NOTIFY_SCRIPT . " >/dev/null 2>&1");
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Set Username.
     *
     * @param $username username
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_username($username)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        // Validation
        // ----------

        Validation_Exception::is_valid($this->validate_username($username));

        $this->_set_parameter('username', $username);
    }

    /**
     * Set Password.
     *
     * @param $password password
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_password($password)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        // Validation
        // ----------

        Validation_Exception::is_valid($this->validate_password($password));

        $this->_set_parameter('password', $password);
    }

    /**
     * Set email lat/long list.
     *
     * @param string $email_latlong a valid string of emails
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_email_latlong($email_latlong)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        // Validation
        // ----------

        Validation_Exception::is_valid($this->validate_email_latlong($email_latlong));

        $this->_set_parameter('email_latlong', preg_replace("/\n/",",", $email_latlong));
    }

    /**
     * Set Beam.
     *
     * @param $id beam ID
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_beam($id)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        list($provider, $number) = preg_split('/:/', $id);

        // Validation
        // ----------

        Validation_Exception::is_valid($this->validate_beam($number));

        $this->_connect();
        $this->_send("beamselector switch $number -f");
        if ($this->_test)
            $this->_test_function = "set_beam";

        $this->_read_to($this->_prompt);
        $this->_data = explode("\n", $this->_data);
        $this->_close();
        $ok = false;
        foreach ($this->_data as $mydata) {
            if (preg_match("/^Scheduling Service Restart.*/", $mydata)) {
                $ok = true;
                break;
            }
        }
        if (!$ok)
            throw new Engine_Exception(lang('beams_switch_failed'));

        $this->_set_parameter('beam_selected', $id);

        // Set power to default for now
        $this->_set_parameter('tx_power', 0);

        $file = new File(self::FILE_TIMER);
        if ($file->exists())
            $file->delete();
        $file->create("webconfig", "webconfig", "0644");
        $file->add_lines(time());
        $this->set_networking($id);

    }

    /**
     * Set Transmit Power.
     *
     * @param int $power power
     *
     * @return void
     * @throws EngineException
     */

    function set_power($power)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        // Validation
        // ----------

        Validation_Exception::is_valid($this->validate_power($power));

        $list = $this->get_beam_selector_list(false, true);
        foreach ($list as $id => $info) {
            if ($info['selected'])
                break;
        }
        $power_current = $beams->get_power();
        if ($power_current != $power) {
            $this->_connect();
            if ($power == 0)
                $this->_send('tx power ' . (int)$this->config['tx_power']);
            else
                $this->_send('tx power ' . $power);
        }

        $this->_set_parameter('power_' . $id, $power);
    }

    /**
     * Update lock.
     *
     * @return void
     * @throws Engine_Exception
     */

    function update_modem_lock()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        // If we have auto switching enabling, we're not forcing a lock
        if ($this->get_auto_switch())
            return;

        $this->_connect();
        $this->_send('beamselector lock');
    }

    /**
     * Set AutoSwitch.
     *
     * @param $auto switch auto switch
     *
     * @return boolean
     * @throws Engine_Exception
     */

    function set_auto_switch($auto_switch)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        if (isset($auto_switch) && ($auto_switch == 'on' || $auto_switch))
            $auto_switch = true;
        else
            $auto_switch = false;

        if ($this->_test) {
            $this->_set_parameter('auto_switch', $auto_switch);
            return $auto_switch;
        }
        if ($this->get_auto_switch() == $auto_switch)
            return false;

        if (!$auto_switch) {
            $this->_connect();
            $this->_send('beamselector lock');
            if ($this->_test)
                $this->_test_function = "set_auto_switch";
        } else {
            $this->_connect();
            $this->_send('beamselector list');
            if ($this->_test)
                $this->_test_function = "get_beam_selector_list";
            $this->_read_to($this->_prompt);
            $this->_data = explode("\n", $this->_data);
            foreach ($this->_data as $line) {
                if (preg_match("/^(\d+) is currently selected.*/", $line, $match)) {
                    $selected = $match[1];
                    break;
                }
            }
            $this->_send("beamselector switch $selected -f");
            if ($this->_test)
                $this->_test_function = "beamselector switch $selected -f expected reply";
        }
        
        $this->_read_to($this->_prompt);
        $this->_data = explode("\n", $this->_data);
        $ok = false;
        if ($auto_switch) {
            foreach ($this->_data as $mydata) {
                if (preg_match("/^Scheduling Service Restart.*/", $mydata)) {
                    $ok = true;
                    break;
                }
            }
        } else {
            // No feedback to confirm lock
            $ok = true;
        }
        $this->_close();
        if (!$ok)
            throw new Engine_Exception(lang('beams_auto_switch_failed'));
            
        $this->_set_parameter('auto_switch', $auto_switch);
        // Return true to indicate we changed something
        return true;
    }

    /**
     * Set Interface.
     *
     * @param $interface interface
     *
     * @return void
     * @throws EngineException
     */

    function set_interface($interface)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        // Validation
        // ----------

        Validation_Exception::is_valid($this->validate_interface($interface));

        $this->_set_parameter('interface', $interface);
    }

    /**
     * Set Vessel.
     *
     * @param $vessel vessel
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_vessel($vessel)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        // Validation
        // ----------

        Validation_Exception::is_valid($this->validate_vessel($vessel));

        $this->_set_parameter('vessel', $vessel);
    }

    /**
     * Set Beam ACL.
     *
     * @param string  $id Beam ID
     * @param boolean $enable enabled
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_acl($id, $enable)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        $settings = json_decode($this->config['acl'], TRUE);
        if ($settings == NULL || $settings === FALSE)
            $settings = array();
        $settings[$id] = $enable;

        $this->_set_parameter('acl', json_encode($settings));
    }

    /**
     * Sends a lat/long notification to admin.
     *
     * @return void
     * @throws Engine_Exception
     */

    function send_lat_long()
    {
        clearos_profile(__METHOD__, __LINE__);

        $email = $this->get_email_lat_long();

        if (empty($email) || !is_array($email))
            return;
        try {
            $output = $this->run_modem_command('latlong');
            $latlong_est = '';
            $latlong = '';
            foreach ($output as $line) {
                if (preg_match("/latlong = (\d+\.\d+) (\w+) (\d+\.\d+) (\w+).*/", $line, $match)) {
                    $latlong = $match[1] . ' ' . $match[2] . ' ' . $match[3] . ' ' . $match[4];
                    $latlong_est = number_format($match[1], 2) . ' ' . $match[2] . ' ' . number_format($match[3], 2) . ' ' . $match[4];
                }
            }
            $file = new File(self::FILE_LAT_LONG_CACHE);
            if ($file->exists()) {
                $content = $file->get_contents();
                if ($content == $latlong_est) {
                    clearos_profile(__METHOD__, __LINE__, "Lat/Long ($latlong_est) has not changed...");
                    return; 
                }
                $file->delete();
                $file->create("webconfig", "webconfig", "0644");
                $file->add_lines($latlong_est);
            } else {
                $file->create("webconfig", "webconfig", "0644");
                $file->add_lines($latlong_est);
            }
            $mailer = new Mailer();
            $hostname = new Hostname();
            $subject = lang('beams_email_notification') . ' - ' . $hostname->get();
            $body = "\n\n" . lang('beams_email_notification') . "\n";
            $body .= str_pad('', strlen(lang('beams_email_notification')), '=') . "\n\n";
            $ntptime = new NTP_Time();
            date_default_timezone_set($ntptime->get_time_zone());

            $thedate = strftime("%b %e %Y");
            $thetime = strftime("%T %Z");
            $body .= str_pad(lang('base_date') . ':', 12) . "\t" . $thedate . ' ' . $thetime . "\n";
            $body .= str_pad(lang('beams_vessel') . ':', 12) . "\t" . $this->get_vessel() . "\n";
            $body .= str_pad(lang('beams_lat_long') . ':', 12) . "\t" . $latlong . "\n";
            $body .= str_pad(lang('beams_google_earth') . ':', 12) . "\t" . "https://maps.google.com/maps?hl=en&q=" . str_replace(" ", "+", $latlong) . "\n";
            foreach($email as $recipient)
                $mailer->add_recipient($recipient);
            $mailer->set_subject($subject);
            $mailer->set_body($body);
            $mailer->set_sender($mailer->get_sender());
            $mailer->send();
            echo "Notification sent.\n";
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Get Power options.
     *
     *
     * @return array
     * @throws Engine_Exception
     */

    public function get_power_options() 
    {
        clearos_profile(__METHOD__, __LINE__);

        $power_levels = array(0 => lang('beams_modem_default'));
        for ($index = -1; $index >= -50; $index--) {
            $text = '';
            if ($index == -50)
                $text = ' (' . lang('beams_minimum') . ')';
            else if ($index == -1)
                $text = ' (' . lang('beams_maximum') . ')';
            $power_levels[$index] = $index . $text;
        }
        return $power_levels;
    }

    /**
     * Get beam list.
     *
     * @param boolean $display_all display all beams
     * @param boolean $get_selected poll modem to find selected beam
     *
     * @return array
     * @throws Engine_Exception
     */

    public function get_beam_selector_list($display_all = false, $get_selected = true) 
    {
        clearos_profile(__METHOD__, __LINE__);

        $info_file = clearos_app_base('beams') . '/deploy/base_list.php';

        if (file_exists($info_file))
            include $info_file;
        else
            $BEAMS = array();

        if (! $this->is_loaded)
            $this->_load_config();
        $result = array();
        if ($get_selected) {
            $this->_connect();
            $this->_send('beamselector list');
            if ($this->_test)
                $this->_test_function = "get_beam_selector_list";
            $this->_read_to($this->_prompt);
            $this->_data = explode("\n", $this->_data);
            $this->_close();
        }
        $selected = -1;
        foreach ($this->_data as $line) {
            if (preg_match("/^(\d+) is currently selected.*/", $line, $match)) {
                $selected = $match[1];
                break;
            }
        }
        $acl = json_decode($this->config['acl'], TRUE);
        foreach ($BEAMS as $beam) {
            $available = FALSE;
            if ($acl != NULL && $acl !== FALSE && array_key_exists($beam[0] . '_' . $beam[1], $acl) && $acl[$beam[0] . '_' . $beam[1]])
                $available = TRUE;
            if (!$display_all && !$available)
                continue;
            $result[$beam[0] . '_' . $beam[1]] = array(
                'provider' => $beam[0],
                'number' => $beam[1],
                'name' => $beam[2],
                'position' => $beam[3],
                'description' => $beam[4],
                'selected' => ($selected == $beam[1] ? TRUE : FALSE),
                'region' => $beam[5],
                'available' => $available
            );

        }

        return $result;
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E   M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * @access private
     */

    function __destruct()
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
    * Loads configuration files.
    *
    * @return void
    * @throws Engine_Exception
    */

    protected function _load_config()
    {
        clearos_profile(__METHOD__, __LINE__);

        $configfile = new Configuration_File(self::FILE_CONFIG);

        try {
            $this->config = $configfile->Load();
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }

        $this->is_loaded = true;
    }

    /**
     * Generic set routine.
     *
     * @private
     * @param  string  $key  key name
     * @param  string  $value  value for the key
     * @return  void
     * @throws Engine_Exception
     */

    function _set_parameter($key, $value)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::FILE_CONFIG, true);
            $match = $file->replace_lines("/^$key\s*=\s*/", "$key=$value\n");

            if (!$match)
                $file->add_lines("$key=$value\n");
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }

        $this->is_loaded = false;
    }

    /**
     * Validation routine for hostname.
     *
     * @param string $host  host
     * @return boolean  true if valid
     */

    function validate_hostname($hostname)
    {
        clearos_profile(__METHOD__, __LINE__);

        $hosts = new Hosts();

        if ($hostname !== 'localhost' && $hosts->validate_ip($hostname) != NULL && $hosts->validate_hostname($hostname) != NULL)
            return lang('beams_hostname') . " - " . lang('base_invalid');
    }

    /**
     * Validation routine for username.
     *
     * @param string $username username
     * @return boolean true if username is valid
     */

    function validate_username($username)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!preg_match("/^([a-zA-Z0-9_\-\.\$]+)$/", $username))
            return lang('beams_username') . " - " . lang('base_invalid');
    }

    /**
     * Validation routine for password.
     *
     * @param string $password password
     * @return boolean true if password is valid
     */

    function validate_password($password)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!preg_match("/^([a-zA-Z0-9_\-\.\$]+)$/", $password))
            return lang('beams_password') . " - " . lang('base_invalid');

    }

    /**
     * Validation routine for vessel.
     *
     * @param string $vessel vessel
     * @return boolean true if vessel is valid
     */

    function validate_vessel($vessel)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!preg_match("/^([a-zA-Z0-9_\-\. \@\!\(\)\&\$]+)$/", $vessel))
            return lang('beams_vessel') . " - " . lang('base_invalid');
    }

    /**
     * Validation routine for beam.
     *
     * @param int $beam beam
     * @return boolean true if beam is valid
     */

    function validate_beam($beam)
    {
        clearos_profile(__METHOD__, __LINE__);

        $list = $this->get_beam_selector_list(false, false);
        foreach ($list as $info) {
            if($beam == $info['number']) 
                return true;
        }

        return false;
    }

    /**
     * Validation routine for command list
     *
     * @param string $command a valid command
     * @return boolean true if command is valid
     */
    function validate_command($command)
    {
        clearos_profile(__METHOD__, __LINE__);

        $list = array(
            'beamselector list',
            'rx snr',
            'spoof dump',
            'latlong',
            'rmtstat',
            'version'
        );
        if (in_array($command, $list))
            return true;
        return false;
    }

    /**
     * Validation routine for an interface
     *
     * @param string $interface interface
     * @return boolean true if interface is valid
     */
    function validate_interface($interface)
    {
        clearos_profile(__METHOD__, __LINE__);

        $iface_manager = new Iface_Manager();

        $ifaces = $iface_manager->get_interfaces();

        if (! in_array($interface, $ifaces))
            return lang('network_network_interface_invalid');
    }

    /**
     * Validation routine for tx power
     *
     * @param int $power tx power
     * @return errmsg if power is valid
     */

    function validate_power($power)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($power < -50 || $power > 0)
            return lang('beams_tx_power') . ' - ' . lang('base_invalid');
    }

    /**
     * Validation routine for email for lat/long notifications.
     *
     * @param string $email_latlong email or string of emails
     *
     * @return mixed void if email is valid, errmsg otherwise
     */

    public function validate_email_latlong($email_latlong)
    {
        clearos_profile(__METHOD__, __LINE__);

        $emails = explode("\n", $email_latlong);
        foreach ($emails as $email) {
            if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email))
                return lang('base_email_address_invalid');
        }
    }

    /**
     * Validation routine for auto switch
     *
     * @param boolean $auto_switch auto switch beams
     * @return errmsg if auto_switch is valid
     */

    function validate_auto_switch($auto_switch)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!preg_match('/on|off/', strtolower($auto_switch)) && !is_bool($auto_switch))
            return lang('beams_auto_switch') . ' - ' . lang('base_invalid');
    }

    /**
     * Validation routine for NIC nickname.
     *
     * @param string $nickname nickname
     * @return boolean true if nickname is valid
     */

    function validate_nickname($nickname)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!preg_match("/^([a-zA-Z0-9_\-\. \@\!\(\)\&\$]+)$/", $nickname))
            return lang('beams_nickname') . " - " . lang('base_invalid');
    }

    /**
    * Establish a connection to the modem
    */

    private function _connect() 
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($this->_test)
            return;

        $this->_connection = fsockopen($this->config['hostname'], 23, $errno, $errstr, $this->_timeout);
        if ($this->_connection === false) {
            throw new Engine_Exception(lang('beams_connection_failed') . ": " . $this->config['hostname'], CLEAROS_ERROR);
        }
        stream_set_timeout($this->_connection, $this->_timeout);
        $this->_read_to(':');
        if (preg_match("/.*Username:.*/", $this->_data)) {
            $this->_send($this->config['username']);
            $this->_read_to(':');
        }
        $this->_send($this->config['password']);
        $this->_prompt = '>';
        $this->_read_to($this->_prompt);
        if (strpos($this->_data, $this->_prompt) === false) {
            fclose($this->_connection);
            throw new Engine_Exception(lang('beams_authentication_failed') . ": " . $this->config['hostname'], CLEAROS_ERROR);
        }
        $this->_read_to($this->_prompt);
    }

    /**
    * Close an active connection
    */
    private function _close() 
    {
        if ($this->_test)
            return;

        $this->_send('quit');
        fclose($this->_connection);
    }

    /**
    * Issue a command to the device
    */
    private function _send($command) 
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($this->_test) {
            clearos_profile(__METHOD__, __LINE__, "Testing enabled...sending modem cmd: " . $command);
            return;
        }

        fputs($this->_connection, $command . "\r\n");
    }

    /**
    * Read from socket until $char
    * @param string $char Single character (only the first character of the string is read)
    */
    private function _read_to($char) 
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($this->_test) {
            if ($this->_test_function == 'get_beam_selector_list')
                $this->_data = "12 is currently selected.\n";
            else if ($this->_test_function == 'set_beam')
                $this->_data = "\n";
            else if ($this->_test_function == 'set_auto_switch')
                $this->_data = "\n";
            else if ($this->_test_function == 'run_modem_command: latlong')
                $this->_data = "latlong = 18.020500 N 63.049500 W\n" .
                    "\n" .
                    "[RMT:64678] admin@telnet:::ffff:213.175.141.228;52935\n";
            else if (preg_match("/run_modem_command: (.*)/", $this->_test_function, $match))
                $this->_data = "Test mode enabled, " . $match[0] . "\n";
            else
                clearos_profile(__METHOD__, __LINE__, "Unknown function.");
            return;
        }

        // Reset $_data
        $this->_data = "";
        $index=0;
        while (($c = fgetc($this->_connection)) !== false) {
            $this->_data .= $c;
            if ($c == $char[0]) {
                if ($char[0] == '>' && substr($this->_data, -2, 1) != "\n") {
                    continue;
                } else {
                    break;
                }
            }
            $index++;
        }
        $this->_data = str_replace(chr(8), "", $this->_data);
        if (strpos($this->_data, '% Invalid input detected') !== false) $this->_data = false;
    }

    /**
     * Get network interface status
     *
     * @return array
     * @throws Engine_Exception
    */

    public function get_interface_status()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        $iface_manager = new Iface_Manager();
        $ifaces = $iface_manager->get_interface_details();
        $fw = new Firewall_Custom();
        $rules = $fw->get_rules();
        $status = array(); 
        foreach ($rules as $line => $rule) {
            if (preg_match('/\s*' . self::SAT_BEAM_NOTE .'\s*(.*)/', $rule['description'], $match))
                $status[$match[1]] = TRUE;
        }

        foreach ($ifaces as $name => $info) {
            if (!$info['configured'])
                continue;
            if (array_key_exists($name, $status))
                $ifaces[$name]['fw_disabled'] = TRUE;
            else
                $ifaces[$name]['fw_disabled'] = FALSE;
            if (isset($this->config["nickname.$name"]))
                $ifaces[$name]['nickname'] = $this->config["nickname.$name"];
            else
                $ifaces[$name]['nickname'] = $name;
        }
        return $ifaces;
    }

    /**
     * Set NIC nickname
     *
     * @param String $nic      NIC interface
     * @param String $nickname string Nickname
     *
     * @return void
     * @throws Validation_Exception
    */

    public function set_nickname($nic, $nickname)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        // Validation
        // ----------

        Validation_Exception::is_valid($this->validate_nickname($nickname));

        $this->_set_parameter('nickname.' . $nic, $nickname);
    }

    /**
     * Get modem commands
     *
     * @return array
     * @throws Engine_Exception
    */

    public function get_modem_commands()
    {
        clearos_profile(__METHOD__, __LINE__);
        $commands = array(
            '0' => lang('beams_select_command'),
            'beamselector list' => lang('beams_cmd_selector_list'),
            'rx snr' => lang('beams_cmd_rx_snr'),
            'spoof dump' => lang('beams_cmd_spoof_dump'),
            'latlong' => lang('beams_cmd_latlong'),
            'version' => lang('beams_cmd_version'),
            'rmtstat' => lang('beams_cmd_rmtstat')
        );
        return $commands;
    }

    /**
     * Reset network disable state
     *
     * @return void
     * @throws Engine_Exception
    */

    public function reset_network_disable_state()
    {
        clearos_profile(__METHOD__, __LINE__);

        $fw = new Firewall_Custom();
        $rules = $fw->get_rules();
        $found = FALSE; 
        foreach ($rules as $line => $rule) {
            if (preg_match('/\s*' . self::SAT_BEAM_NOTE . '.*/', $rule['description'])) {
                $fw->delete_rule($line);
                $found = TRUE;
            }
        }
        if ($found) { 
            $firewall = new Firewall();
            $firewall->Restart();
        }
    }

    /**
     * Toggle NIC status
     *
     * @param string $nic NIC
     *
     * @return void
     * @throws Engine_Exception
    */

    public function toggle_nic($nic)
    {
        clearos_profile(__METHOD__, __LINE__);

        $fw = new Firewall_Custom();
        $rules = $fw->get_rules();
        $found = FALSE;
        foreach ($rules as $line => $rule) {
            if (trim($rule['description']) == self::SAT_BEAM_NOTE . $nic) {
                $fw->delete_rule($line);
                $found = TRUE;
                break;
            }
        }
        if (!$found)
            $fw->add_rule(sprintf(self::IPTABLES_BLOCK, $nic), self::SAT_BEAM_NOTE . $nic, TRUE, 0);

        $firewall = new Firewall();
        $firewall->Restart();
    }

    /**
     * Set networking up 
     *
     * @param string $id Network ID
     *
     * @return void
     * @throws Engine_Exception
    */

    public function set_networking($id)
    {
        clearos_profile(__METHOD__, __LINE__);

        $eth = $this->get_interface();
        $interface = new Iface($eth);
    
        if (! $this->is_loaded)
            $this->_load_config();

        $configs = $this->get_interface_configs();
        if (!array_key_exists($this->config['ifconfig_' . $id], $configs))
            throw new Exception (lang('beams_network_definition_not_found') . ' (' . $this->config['ifconfig_' . $id] . ')');
    
        $bootproto = $configs[$this->config['ifconfig_' . $id]]['bootproto'];
        $options = $configs[$this->config['ifconfig_' . $id]]['options'];

        if (!isset($this->config['dyn_network_update']) || $this->config['dyn_network_update'] != 'yes') {
            clearos_profile(__METHOD__, __LINE__, "Debug mode - network update disabled.");
            clearos_profile(__METHOD__, __LINE__, "Eth " . $eth);
            clearos_profile(__METHOD__, __LINE__, "Boot Proto: " . $bootproto);
            clearos_profile(__METHOD__, __LINE__, "Options: " . json_encode($options));;
            return;
        }

        $routes = new Routes();
        $firewall = new Firewall();

        if ($bootproto == Iface::BOOTPROTO_PPPOE) {
            // PPPoE
            //------
            $firewall->remove_interface_role($eth);
            $eth = $interface->save_pppoe_config($eth, $options['username'], $options['password'], $options['mtu'], $options['pppoe_peerdns']);
        } else if ($bootproto == Iface::BOOTPROTO_DHCP) {
            // Ethernet
            //---------
            $interface->save_ethernet_config(true, "", "", "", $options['dhcp_hostname'], $options['peerdns']);
            $options['gateway'] = $routes->get_default();
        } else if ($bootproto == Iface::BOOTPROTO_STATIC) {
            // Static
            //-------
            $interface->save_ethernet_config(false, $options['ip'], $options['netmask'], $options['gateway'], "", false, true);
        }

        // Reset the routes
        //-----------------

        $role = Firewall::CONSTANT_EXTERNAL;
        $routes->set_gateway_device($eth);

        // Set firewall roles
        //-------------------

        $firewall->set_interface_role($eth, $role);

        // Enable interface 
        //-----------------

        // Response time can take too long on PPPoE and DHCP connections.

        if (($bootproto == Iface::BOOTPROTO_DHCP) || ($bootproto == Iface::BOOTPROTO_PPPOE))
            $interface->enable(true);
        else
            $interface->enable(false);

        // Restart syswatch
        //-----------------
    
        $syswatch = new Syswatch();
        $syswatch->reset();

        // Set Modem IP
        $this->set_hostname($options['gateway']);

    }

    /**
     * Establish an SSH connection to the modem
     *
     * @param string $command command
     *
     * @return void
     * @throws Validation_Exception
    */

    private function _ssh($command)
    {
        clearos_profile(__METHOD__, __LINE__);

        set_include_path(clearos_app_base('beams') . '/deploy/phpssl');

        if ($this->_test) {
            clearos_profile(__METHOD__, __LINE__, "Debug enabled - ssh command send: " . $command);
            return;
        }

        if (! $this->is_loaded)
            $this->_load_config();

        // TODO - This is a command ACL list...could be moved/expanded
        $list = array('reboot');
        if (!in_array($command, $list))
            throw new Validation_Exception(lang('beams_command_not_allowed'));

        $ssh = new Net_SSH2($this->config['hostname']);
        if (!$ssh->login('root', $this->config['password']))
            throw new Validation_Exception(lang('beams_ssh_failed'));

        $ssh->exec($command);
    }

}
