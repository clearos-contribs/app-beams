<?php

/**
 * Report controller.
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
 * Report controller.
 *
 * @category   apps
 * @package    beams
 * @subpackage controllers
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2015 Marine VSAT
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/beams/
 */

class Report extends ClearOS_Controller
{
    /**
     * Beams controls sidebar.
     *
     * @return view
     */

    function sidebar()
    {
        // Load dependencies
        //------------------

        $this->lang->load('beams');

        // Load views
        //-----------

        $this->page->view_form('sidebar', array(), lang('beams_controls'));
    }
}
