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

        $data['satellites'] = $this->beams->get_beam_selector_list(TRUE, FALSE);
        $data['autoswitch'] = $this->beams->get_auto_switch();
        $this->page->view_form('beams/satellites', $data, lang('beams_beams'));
	}
}
