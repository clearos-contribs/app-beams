<?php

/**
 * Satellites controller.
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
 * Satellites controller.
 *
 * @category   apps
 * @package    beams
 * @subpackage controllers
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2015 Marine VSAT
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/beams/
 */

class Satellites extends ClearOS_Controller
{
    /**
     * Satellites controller.
     *
     * @return view
     */

    function index()
    {
        // Load libraries
        //---------------
        $this->load->library('beams/Beams');

        $this->lang->load('beams');

        try {
            $data['satellites'] = $this->beams->get_beam_selector_list();
        } catch (Exception $e) {
            $data['modem_connect_failed'] = clearos_exception_message($e); 
        }
        $data['autoswitch'] = $this->beams->get_auto_switch();
        $data['show_admin'] = ($this->session->userdata('username') === 'root') ? TRUE : FALSE;

        $this->page->view_form('beams/satellites', $data, lang('beams_beams'));
    }

    /**
     * Add satellite controller.
     *
     * @return view
     */

    function add()
    {
        // Load libraries
        //---------------
        $this->load->library('beams/Beams');

        $this->lang->load('beams');

        if ($this->session->userdata('username') != 'root') {
            $this->page->set_message(lang('beams_access_denied'));
            redirect('/beams');
            return;
        }

        $this->form_validation->set_policy('provider', 'beams/Beams', 'validate_provider', TRUE);
        $this->form_validation->set_policy('name', 'beams/Beams', 'validate_name', TRUE);
        $this->form_validation->set_policy('number', 'beams/Beams', 'validate_number', TRUE);
        $this->form_validation->set_policy('position', 'beams/Beams', 'validate_position', FALSE);
        $this->form_validation->set_policy('description', 'beams/Beams', 'validate_description', FALSE);

        $form_ok = $this->form_validation->run();

        // Handle form submit
        //-------------------

        if ($this->input->post('submit') && ($form_ok === TRUE)) {

            try {
                $this->beams->add_beam(
                    $this->input->post('provider'),
                    $this->input->post('name'),
                    $this->input->post('number'),
                    $this->input->post('position'),
                    $this->input->post('description'),
                    $this->input->post('network'),
                    $this->input->post('tx'),
                    $this->input->post('available')
                );
                redirect('/beams/satellites/admin');
            } catch (Exception $e) {
                $data['fatal_error'] = clearos_exception_message($e); 
            }
        }

        $data['power_options'] = $this->beams->get_power_options();
        $network_options = $this->beams->get_interface_configs();
        foreach ($network_options as $key => $value) {
            if (is_array($value))
                $data['network_options'][$key] = $key;
            else
                $data['network_options'][$key] = $value;
        }
        $this->page->view_form('beams/add_beam', $data, lang('beams_beams'));
    }

    /**
     * Edit satellite controller.
     *
     * @param string $id beam ID
     *
     * @return view
     */

    function edit($id)
    {
        // Load libraries
        //---------------
        $this->load->library('beams/Beams');

        $this->lang->load('beams');

        if ($this->session->userdata('username') != 'root') {
            $this->page->set_message(lang('beams_access_denied'));
            redirect('/beams');
            return;
        }

        $this->form_validation->set_policy('number', 'beams/Beams', 'validate_number', TRUE);
        $this->form_validation->set_policy('position', 'beams/Beams', 'validate_position', FALSE);
        $this->form_validation->set_policy('description', 'beams/Beams', 'validate_description', FALSE);

        $form_ok = $this->form_validation->run();

        // Handle form submit
        //-------------------

        if ($this->input->post('submit') && ($form_ok === TRUE)) {

            try {
                $this->beams->set_beam_defaults(
                    $id,
                    $this->input->post('power'),
                    $this->input->post('network'),
                    $this->input->post('number'),
                    $this->input->post('position'),
                    $this->input->post('description'),
                    $this->input->post('available')
                );
                redirect('/beams/satellites/admin');
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        try {
            $satellites = $this->beams->get_beam_selector_list(TRUE, FALSE);
        } catch (Exception $e) {
            $data['modem_connect_failed'] = clearos_exception_message($e); 
        }
        $data['id'] = $id;
        $data['beam'] = $satellites[$id];
        $data['power_options'] = $this->beams->get_power_options();
        $network_options = $this->beams->get_interface_configs();
        foreach ($network_options as $key => $value) {
            if (is_array($value))
                $data['network_options'][$key] = $key;
            else
                $data['network_options'][$key] = $value;
        }

        $this->page->view_form('beams/beam', $data, lang('beams_beams'));
    }

    /**
     * Administer satellites controller.
     *
     * @return view
     */

    function admin()
    {
        // Load libraries
        //---------------
        $this->load->library('beams/Beams');

        $this->lang->load('beams');

        if ($this->session->userdata('username') != 'root') {
            $this->page->set_message(lang('beams_access_denied'));
            redirect('/beams');
            return;
        }

        try {
            $data['satellites'] = $this->beams->get_beam_selector_list(TRUE, FALSE);
        } catch (Exception $e) {
            $data['modem_connect_failed'] = clearos_exception_message($e); 
        }
        $data['autoswitch'] = $this->beams->get_auto_switch();
        $data['show_admin'] = ($this->session->userdata('username') === 'root') ? TRUE : FALSE;

        $this->page->view_form('beams/admin_satellites', $data, lang('beams_beams'), array('type' => MY_Page::TYPE_WIDE_CONFIGURATION));
    }

    /**
     * Enable satellite controller.
     *
     * @param string $id satellite ID
     *
     * @return void
     */

    function enable($id)
    {
        // Load libraries
        //---------------
        $this->load->library('beams/Beams');

        $this->lang->load('beams');

        $data['satellites'] = $this->beams->set_acl($id, TRUE);

        redirect('/beams/satellites/admin');
    }

    /**
     * Delete satellite controller.
     *
     * @param string $id satellite ID
     * @param string $confirm confirm intentions to delete
     *
     * @return void
     */

    function delete($id, $confirm)
    {
        // Load libraries
        //---------------
        $this->load->library('beams/Beams');

        $this->lang->load('beams');

        if ($this->session->userdata('username') != 'root') {
            $this->page->set_message(lang('beams_access_denied'));
            redirect('/beams');
            return;
        }

        $this->lang->load('beams');
        $confirm_uri = '/app/beams/satellites/delete/' . $id . "/1";
        $cancel_uri = '/app/beams/satellites/admin';

        if ($confirm != NULL) {
            $this->beams->delete_satellite($id);
            redirect('/beams/satellites/admin');
            return;
        }

        $items = array($id);

        $this->page->view_confirm_delete($confirm_uri, $cancel_uri, $items);

    }

    /**
     * Disable satellite controller.
     *
     * @param string $id satellite ID
     *
     * @return void
     */

    function disable($id)
    {
        // Load libraries
        //---------------
        $this->load->library('beams/Beams');

        $this->lang->load('beams');

        if ($this->session->userdata('username') != 'root') {
            $this->page->set_message(lang('beams_access_denied'));
            redirect('/beams');
            return;
        }

        $data['satellites'] = $this->beams->set_acl($id, FALSE);

        redirect('/beams/satellites/admin');
    }

    /**
     * Switch beam.
     *
     * @param string $id      Beam ID
     * @param string $confirm confirmation ID
     *
     * @return view
     */
    function switch_beam($id, $confirm = NULL)
    {
        // Load libraries
        //---------------

        $this->load->library('session');
        $this->load->library('beams/Beams');
        $this->lang->load('beams');

        $data = array(
            'id' => $id,
            'confirm' => $this->session->userdata('switch_confirm') 
        );
        if ($confirm != NULL && $confirm == $this->session->userdata('switch_confirm')) {
            try {
                $this->beams->set_beam($id);
                $this->page->set_message(lang('beams_switch_in_progress'));
                redirect('/beams');
                return;
            } catch (Exception $e) {
                $data['modem_connect_failed'] = clearos_exception_message($e); 
            }
        } else if (!$this->session->userdata('switch_confirm')) {
            $this->session->set_userdata(array('switch_confirm' => rand(0, 10000)));
            redirect('/beams/satellites/switch_beam/' . $id);
            return;
        }
        $this->page->view_form('beams/switch_beam', $data, lang('beams_switch_beam'));
    }

    /**
     * Reset beam.
     *
     * @param string $id      Beam ID
     * @param string $confirm confirmation ID
     *
     * @return view
     */
    function reset_beam($id, $confirm = NULL)
    {
        // Load libraries
        //---------------

        $this->load->library('beams/Beams');
        $this->lang->load('beams');

        $data = array(
            'id' => $id,
            'confirm' => rand(0, 10000)
        );
        if ($confirm != NULL && $confirm == $this->session->userdata('reset_beam')) {
            try {
                $this->beams->reset_beam($id);
                $this->page->set_message(lang('beams_reset_in_progress'));
                redirect('/beams');
                return;
            } catch (Exception $e) {
                $data['modem_connect_failed'] = clearos_exception_message($e); 
            }
        }
        $this->session->set_userdata(array('reset_beam' => $data['confirm']));
        $this->page->view_form('beams/reset_beam', $data, lang('beams_reset_beam'));
    }

}
