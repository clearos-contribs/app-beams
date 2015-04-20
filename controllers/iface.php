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
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

use \clearos\apps\network\Iface as Iface_Library;

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
            $this->beams->delete_network_config($name);
            redirect('/beams/network/summary');
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

        $this->form_validation->set_policy('nickname', 'beams/Beams', 'validate_nickname', TRUE);
        $this->form_validation->set_policy('description', 'beams/Beams', 'validate_description', TRUE);
        $this->form_validation->set_policy('bootproto', 'beams/Beams', 'validate_bootproto', TRUE);
        if ($this->input->post('bootproto') == Iface_Library::BOOTPROTO_DHCP) { 
           $this->form_validation->set_policy('hostname', 'beams/Beams', 'validate_hostname', TRUE);
        } else if ($this->input->post('bootproto') == Iface_Library::BOOTPROTO_STATIC) { 
           $this->form_validation->set_policy('ipaddr', 'beams/Beams', 'validate_ipaddr', TRUE);
           $this->form_validation->set_policy('netmask', 'beams/Beams', 'validate_netmask', TRUE);
           $this->form_validation->set_policy('gateway', 'beams/Beams', 'validate_gateway', TRUE);
        } else if ($this->input->post('bootproto') == Iface_Library::BOOTPROTO_PPPOE) { 
           $this->form_validation->set_policy('username', 'beams/Beams', 'validate_username', TRUE);
           $this->form_validation->set_policy('password', 'beams/Beams', 'validate_password', TRUE);
           $this->form_validation->set_policy('mtu', 'beams/Beams', 'validate_mtu', TRUE);
        }

        $form_ok = $this->form_validation->run();

        // Extra validation
        //-----------------

        if ($form_type == 'add' && $form_ok) {
            $options = $this->beams->get_interface_configs();
            if (array_key_exists($this->input->post('nickname'), $options)) {
                $this->form_validation->set_error('nickname', lang('beams_name_non_unique'));
                $form_ok = FALSE;
            }
        }


        // Handle form submit
        //-------------------

        if (($this->input->post('submit') && $form_ok)) {
            try {
                $setting[$this->input->post('nickname')] = array(
                    'description' => $this->input->post('description'),
                    'bootproto' => $this->input->post('bootproto')
                );

                if ($this->input->post('bootproto') == Iface_Library::BOOTPROTO_DHCP) { 
                    $setting[$this->input->post('nickname')]['dhcp_hostname'] = $this->input->post('hostname');
                    $setting[$this->input->post('nickname')]['dhcp_dns'] = $this->input->post('dhcp_dns');
                } else if ($this->input->post('bootproto') == Iface_Library::BOOTPROTO_STATIC) { 
                    $setting[$this->input->post('nickname')]['ipaddr'] = $this->input->post('ipaddr');
                    $setting[$this->input->post('nickname')]['netmask'] = $this->input->post('netmask');
                    $setting[$this->input->post('nickname')]['gateway'] = $this->input->post('gateway');
                } else if ($this->input->post('bootproto') == Iface_Library::BOOTPROTO_PPPOE) { 
                    $setting[$this->input->post('nickname')]['username'] = $this->input->post('username');
                    $setting[$this->input->post('nickname')]['password'] = $this->input->post('password');
                    $setting[$this->input->post('nickname')]['mtu'] = $this->input->post('mtu');
                    $setting[$this->input->post('nickname')]['pppoe_dns'] = $this->input->post('pppoe_dns');
                }
                
                $this->beams->set_network_conf($setting);
                $this->page->set_status_updated();
                redirect('/beams/network/summary');
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        $data['bootprotos'] = $this->iface->get_supported_bootprotos();
        $data['form_type'] = $form_type;
        if ($form_type == 'edit' && !$this->input->post('submit')) {
            $options = $this->beams->get_interface_configs();
            if (array_key_exists($name, $options)) {
                $data['nickname'] = $name;
                $data['description'] = $options[$name]['description'];
                $data['bootproto'] = $options[$name]['bootproto'];
                if ($data['bootproto'] == Iface_Library::BOOTPROTO_DHCP) { 
                    $data['dhcp_hostname'] = $options[$name]['dhcp_hostname'];
                    $data['dhcp_dns'] = $options[$name]['dhcp_dns'];
                } else if ($data['bootproto'] == Iface_Library::BOOTPROTO_STATIC) { 
                    $data['ipaddr'] = $options[$name]['ipaddr'];
                    $data['netmask'] = $options[$name]['netmask'];
                    $data['gateway'] = $options[$name]['gateway'];
                } else if ($data['bootproto'] == Iface_Library::BOOTPROTO_PPPOE) { 
                    $data['username'] = $options[$name]['username'];
                    $data['password'] = $options[$name]['password'];
                    $data['mtu'] = $options[$name]['mtu'];
                    $data['pppoe_dns'] = $options[$name]['pppoe_dns'];
                }
            } else {
                $this->page->set_message(lang('beams_config_not_found'), 'warning');
                redirect('/beams');
            }
        }

        // Load the views
        //---------------

        $this->page->view_form('beams/iface', $data, lang('base_add'));
    }
}
