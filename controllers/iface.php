<?php

/**
 * Iface controller.
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
 * Iface Override controller.
 *
 * @category   apps
 * @package    beams
 * @subpackage controllers
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2015 Marine VSAT
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/beams/
 */

class Iface extends ClearOS_Controller
{
    /**
     * Default controller.
     *
     * @return view
     */
    function index()
    {
        // Load libraries
        //---------------

        $this->load->library('beams/Beams');
		$this->lang->load('beams');

        $data['interfaces'] = $this->beams->get_interface_status();
        $this->page->view_form('beams/network', $data, lang('beams_network_status'));
    }

    /**
     * Add view.
     *
     * @return view
     */

    function add()
    {
        $this->_add_edit('add');
    }

    /**
     * Delete an network definition.
     *
     * @param string $name    name of network configuration
     * @param string $confirm confirm intentions to delete
     *
     * @return view
     */

    function delete($name, $confirm = NULL)
    {
        // Load libraries
        //---------------

        $this->load->library('beams/Beams');

        // Load dependencies
        //------------------

        $this->lang->load('beams');
        $confirm_uri = '/app/beams/iface/delete/' . $name . "/1";
        $cancel_uri = '/app/beams/network';

        if ($confirm != NULL) {
            $this->squid->delete_time_acl($name);
            $this->squid->reset(TRUE);

            redirect('/beams/network');
        }

        $items = array($name);

        $this->page->view_confirm_delete($confirm_uri, $cancel_uri, $items);
    }

    /**
     * Edit view.
     *
     * @param string $name network override name
     *
     * @return view
     */

    function edit($name)
    {
        $this->_add_edit('edit', $name);
    }

    /**
     * Command add/edit view.
     *
     * @param string $form_type form type
     * @param string $name      ACL name
     *
     * @return view
     */

    function _add_edit($form_type, $name = NULL)
    {
        // Load libraries
        //---------------

        $this->load->library('beams/Beams');
        $this->load->library('network/Iface');
        $this->lang->load('beams');
        $this->lang->load('base');

        // Set validation rules
        //---------------------

        $this->form_validation->set_policy('name', 'beams/Beams', 'validate_name', TRUE);
        $this->form_validation->set_policy('time', 'beams/Beams', 'validate_description', TRUE);
        $form_ok = $this->form_validation->run();

        // Handle form submit
        //-------------------

        if (($this->input->post('update') && $form_ok)) {
            try {

                $this->page->set_status_updated();
                redirect('/beams/network');
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        $data['bootprotos'] = $this->iface->get_supported_bootprotos();

        // Load the views
        //---------------

        $this->page->view_form('beams/iface', $data, lang('base_add'));
    }
}
