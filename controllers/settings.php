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
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

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
        if ($this->session->userdata('username') === 'root') {
            $this->form_validation->set_policy('hostname', 'beams/Beams', 'validate_hostname', TRUE);
            $this->form_validation->set_policy('username', 'beams/Beams', 'validate_username', TRUE);
            $this->form_validation->set_policy('password', 'beams/Beams', 'validate_password', TRUE);
            $this->form_validation->set_policy('interface', 'beams/Beams', 'validate_interface', TRUE);
            $this->form_validation->set_policy('power', 'beams/Beams', 'validate_power', TRUE);
        }

        $form_ok = $this->form_validation->run();

        // Handle form submit
        //-------------------

        if ($this->input->post('submit') && ($form_ok === TRUE)) {

            try {
                $this->beams->set_vessel($this->input->post('vessel'));

                // Return to summary page with status message
                $this->page->set_status_added();
                if ($this->session->userdata('username') === 'root') {
                    $this->beams->set_hostname($this->input->post('hostname'));
                    $this->beams->set_username($this->input->post('username'));
                    $this->beams->set_password($this->input->post('password'));
                    $this->beams->set_interface($this->input->post('interface'));
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

        $data['show_admin'] = ($this->session->userdata('username') === 'root') ? TRUE : FALSE;
        $data['vessel'] = $this->beams->get_vessel();
        $data['hostname'] = $this->beams->get_hostname();
        $data['username'] = $this->beams->get_username();
        $data['password'] = $this->beams->get_password();
        $data['interface'] = $this->beams->get_interface();
        $data['power'] = $this->beams->get_power();
        $data['power_options'] = $this->beams->get_power_options();
        $data['form_type'] = $form_type;

        $this->page->view_form('beams/settings', $data, lang('beams_beams'));
	}
}
