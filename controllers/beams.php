<?php

/**
 * Beams controller.
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
 * Beams controller.
 *
 * @category   apps
 * @package    beams
 * @subpackage controllers
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2015 Marine VSAT
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/beams/
 */

class Beams extends ClearOS_Controller
{
	/**
	 * Beams server overview.
	 */

	function index()
	{
		// Load libraries
		//---------------

		$this->lang->load('beams');

		// Load views
		//-----------

        $controllers = array('beams/settings', 'beams/satellites');

        $options['breadcrumb_links'] = array(
            'settings' => array('url' => '/app/beams/network', 'tag' => lang('beams_network_status')),
            'terminal' => array('url' => '/app/beams/modem/terminal', 'tag' => lang('beams_terminal')),
            'power' => array('url' => '/app/beams/modem/reboot', 'tag' => lang('beams_reboot_modem'))
        );
        $this->page->view_controllers($controllers, lang('beams_app_name'), $options);
	}
}
