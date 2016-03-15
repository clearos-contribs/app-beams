<?php

/**
 * Modem controller.
 *
 * @category   apps
 * @package    beams
 * @subpackage controllers
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2015 Marine VSAT
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/beams/
 */

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Modem controller.
 *
 * @category   apps
 * @package    beams
 * @subpackage controllers
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2015 Marine VSAT
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/beams/
 */

class Modem extends ClearOS_Controller
{
    /**
     * Default controller.
     *
     * @return view
     */
    function index()
    {
        redirect('/beams');
    }

    /**
     * Modem terminal.
     *
     * @return view
     */
    function terminal()
    {
        // Load libraries
        //---------------

        $this->load->library('beams/Beams');
		$this->lang->load('beams');

        $data['commands'] = $this->beams->get_modem_commands();
        $options['breadcrumb_links'] = array(
            'cancel' => array('url' => '/app/beams', 'tag' => lang('base_cancel'))
        );
        $this->page->view_form('beams/terminal', $data, lang('beams_terminal'), $options);
    }

    /**
     * Modem reboot.
     *
     * @param string $confirm confirmation ID
     *
     * @return view
     */
    function reboot($confirm = NULL)
    {
        // Load libraries
        //---------------

        $this->load->library('beams/Beams');
		$this->lang->load('beams');

        if ($confirm != NULL && $confirm == md5($this->session->userdata('session_id'))) {
            $this->beams->reboot_modem();
            $this->page->set_message(lang('beams_modem_reboot_started'));
            redirect('/beams');
            return;
        }
        $data = array('confirm' => md5($this->session->userdata('session_id')));
        $this->page->view_form('beams/reboot_modem', $data, lang('beams_reboot_modem'));

    }

    /**
     * Execute modem command.
     *
     * @return JSON
     */
    function execute()
    {
        clearos_profile(__METHOD__, __LINE__);

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        // Load libraries
        //---------------

        $this->load->library('beams/Beams');
        $output = $this->beams->run_modem_command($this->input->post('command'));

        echo json_encode($output);
    }

    /**
     * Fetch modem status.
     *
     * @return JSON
     */
    function status()
    {
        clearos_profile(__METHOD__, __LINE__);

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        // Load libraries
        //---------------

        $this->load->library('beams/Beams');
        $output = $this->beams->get_modem_status();

        echo json_encode($output);
    }

    /**
     * Lock beam.
     *
     * @return JSON
     */
    function lock_beam()
    {
        clearos_profile(__METHOD__, __LINE__);

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        // Load libraries
        //---------------

        $this->load->library('beams/Beams');
        $output = $this->beams->run_modem_command('beamselector lock');

        echo json_encode($output);
    }

}
