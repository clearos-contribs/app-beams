<?php

/**
 * Settings controller.
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
 * Settings controller.
 *
 * @category   apps
 * @package    beams
 * @subpackage controllers
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2015 Marine VSAT
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/beams/
 */

class Settings extends ClearOS_Controller
{
    /**
     * Settings default controller.
     *
     * @return view
     */

    function index()
    {
        $this->view();
    }

    /**
     * Edit view.
     *
     * @return view
     */

    function edit()
    {
        $this->_item('edit');
    }

    /**
     * View view.
     *
     * @return view
     */

    function view()
    {
        $this->_item('view');
    }

    /**
     * Common view/edit view.
     *
     * @param string $form_type form type
     *
     * @return view
     */

    function _item($form_type)
    {
        // Load libraries
        //---------------

        $this->load->library('beams/Beams');
        $this->load->library('network/Iface_Manager');
		$this->lang->load('beams');

        $this->form_validation->set_policy('vessel', 'beams/Beams', 'validate_vessel', TRUE);
        $this->form_validation->set_policy('auto_switch', 'beams/Beams', 'validate_auto_switch', FALSE);
        $this->form_validation->set_policy('position_report', 'beams/Beams', 'validate_position_report', TRUE);
        if ($this->session->userdata('username') === 'root') {
            $this->form_validation->set_policy('modem_hostname', 'beams/Beams', 'validate_modem_hostname', TRUE);
            $this->form_validation->set_policy('modem_username', 'beams/Beams', 'validate_modem_username', TRUE);
            $this->form_validation->set_policy('modem_password', 'beams/Beams', 'validate_modem_password', TRUE);
            $this->form_validation->set_policy('interface', 'beams/Beams', 'validate_interface', TRUE);
            $this->form_validation->set_policy('power', 'beams/Beams', 'validate_power', TRUE);
            $this->form_validation->set_policy('email_latlong', 'beams/Beams', 'validate_email_latlong', FALSE);
        }

        $form_ok = $this->form_validation->run();

        // Handle form submit
        //-------------------

        if ($this->input->post('submit') && ($form_ok === TRUE)) {

            try {
                $this->beams->set_vessel($this->input->post('vessel'));
                $this->beams->set_auto_switch($this->input->post('auto_switch'));
                $this->beams->set_position_report($this->input->post('position_report'));

                // Return to summary page with status message
                $this->page->set_status_added();
                if ($this->session->userdata('username') === 'root') {
                    $this->beams->set_modem_hostname($this->input->post('modem_hostname'));
                    $this->beams->set_modem_username($this->input->post('modem_username'));
                    $this->beams->set_modem_password($this->input->post('modem_password'));
                    $this->beams->set_interface($this->input->post('interface'));
                    $this->beams->set_email_latlong($this->input->post('email_latlong'));
                }
                redirect('/beams');
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        $data = array();
        try {
            $ifaces = $this->iface_manager->get_external_interfaces();
            $interface = $this->beams->get_interface();

            foreach($ifaces as $iface)
                $data['interfaces'][$iface] = $iface; 
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        try {
            $data['form_type'] = $form_type;
            $data['show_admin'] = ($this->session->userdata('username') === 'root') ? TRUE : FALSE;
            $data['power_options'] = $this->beams->get_power_options();
            $data['position_report'] = $this->beams->get_position_report();
            $data['position_report_options'] = $this->beams->get_position_report_options();
            $data['vessel'] = $this->beams->get_vessel();
            $data['modem_hostname'] = $this->beams->get_modem_hostname();
            $data['modem_username'] = $this->beams->get_modem_username();
            $data['modem_password'] = $this->beams->get_modem_password();
            $data['interface'] = $this->beams->get_interface();
            $data['email_latlong'] = $this->beams->get_email_latlong();
            $data['auto_switch'] = $this->beams->get_auto_switch();
            $data['power'] = $this->beams->get_power();
        } catch (Exception $e) {
            $data['modem_connect_failed'] = clearos_exception_message($e); 
        }

        $this->page->view_form('beams/settings', $data, lang('base_settings'));
	}
}
