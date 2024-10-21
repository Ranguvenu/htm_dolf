<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Syncronisation data event.
 *
 * @package    auth_iam
 * @copyright  eabyas info solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_iam\event;

defined('MOODLE_INTERNAL') || die();
/**
 * The auth_iam data_synced event class.
 *
 * @property-read array $other {
 *      errormsg - Error Message generated.
 * }
 *
 * @copyright  2020 Readynez Ltd
 * @author     Benjamin Ellis - Mukudu Ltd <ben.ellis@mukudu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class data_synced extends \core\event\base {

    /**
     *
     * {@inheritDoc}
     * @see \core\event\base::init()
     */
    protected function init() {
        $this->context = \context_system::instance();
        $this->data['crud'] = 'r'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * {@inheritDoc}
     * @see \core\event\base::get_name()
     */
    public static function get_name() {
        return get_string('event_data_synced', 'auth_iam');
    }

    /**
     *
     * {@inheritDoc}
     * @see \core\event\base::get_description()
     */
    public function get_description() {
        return get_string('event_data_synced_desc', 'auth_iam', $this->data['other']['data']);
    }
}