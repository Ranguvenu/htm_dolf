// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * AMD module to enable Competencies to manage their own data.
 *
 * @module     local_competency/competencyactions
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Notification from 'core/notification';
import Pending from 'core/pending';
import {get_string as getString} from 'core/str';
import {get_strings as getStrings} from 'core/str';
import Ajax from 'core/ajax';
import homepage from 'theme_academy/homepage';

const SELECTORS = {
    DELETE_COMPETENCY: '[data-action="deletecompetency"][data-competencyid][data-competencyname]',
    DELETE_COMPETENCYPC: '[data-action="deletecompetencypc"][data-competencyid][data-competencyname]',
    DELETE_COMPETENCYPCOBJECTIVE: '[data-action="deletecompetencyobjective"][data-competencypcid][data-competencypcobjectivetype][data-competencypcobjectiveid][data-competencypcobjectivename]',
};

/**
 * Initialize module
 */
let HomePage = new homepage();
export const init = () => {
    document.addEventListener('click', event => {
        const triggerElement = event.target.closest(SELECTORS.DELETE_COMPETENCY);
        if (triggerElement === null) {
            return;
        }
        event.stopImmediatePropagation();
        const requiredStrings = [
            {key: 'deletecompetency', component: 'local_competency'},
            {key: 'deletecompetencyconfirmation', component: 'local_competency',param: triggerElement.dataset.competencyname},
        ];
        getStrings(requiredStrings).then(([deleteCompetency, deleteConfirm]) => {
            return Notification.confirm(deleteCompetency, deleteConfirm, deleteCompetency, null, () => {
                var params = {};
                params.elementid = triggerElement.dataset.competencyid;
                params.elementtype = 'competency';
                var promise = Ajax.call([{
                    methodname: 'local_sector_candeleteelement',
                    args: params
                }]);
                promise[0].done(function(resp) {
                    if(resp.candelete == 1) {
                        HomePage.confirmbox(getString('remove_dependency', 'local_sector'));
                    } else {
                        const pendingPromise = new Pending();
                        const request = {
                            methodname: 'local_competency_delete_data_competency',
                            args: {competencyid: triggerElement.dataset.competencyid}
                        };
                        Ajax.call([request])[0].then(response => {
                            if (response.result) {
                                window.location.reload();
                            } else {
                                Notification.addNotification({
                                    type: 'error',
                                    message: response.warnings[0].message
                                });
                            }
                            return pendingPromise.resolve();
                        }).catch(Notification.exception);
                    }
                }).fail(function() {
                    console.log('exception');
                });  
            });
        }).catch();
    });
    document.addEventListener('click', event => {
        const triggerElement = event.target.closest(SELECTORS.DELETE_COMPETENCYPC);
        if (triggerElement === null) {
            return;
        }

        event.stopImmediatePropagation();

        const requiredStrings = [
            {key: 'deletecompetencypc', component: 'local_competency'},
            {key: 'deletecompetencypcconfirmation', component: 'local_competency',param: triggerElement.dataset.competencyname},
        ];

        getStrings(requiredStrings).then(([deleteCompetencypc, deleteConfirm]) => {
            return Notification.confirm(deleteCompetencypc, deleteConfirm, deleteCompetencypc, null, () => {
                const pendingPromise = new Pending();
                const request = {
                    methodname: 'local_competency_delete_data_competencypc',
                    args: {competencyid: triggerElement.dataset.competencyid,competencypcid: triggerElement.dataset.id}
                };

                Ajax.call([request])[0].then(response => {
                    if (response.result) {
                        window.location.reload();
                    } else {
                        Notification.addNotification({
                            type: 'error',
                            message: response.warnings[0].message
                        });
                    }
                    return pendingPromise.resolve();
                }).catch(Notification.exception);
            });
        }).catch();
    });
    document.addEventListener('click', event => {
        const triggerElement = event.target.closest(SELECTORS.DELETE_COMPETENCYPCOBJECTIVE);
        if (triggerElement === null) {
            return;
        }
        event.stopImmediatePropagation();

        const requiredStrings = [
            {key: 'deletecompetencypcobjective', component: 'local_competency', component: 'local_competency',param: triggerElement.dataset.competencypcobjectivetype},
            {key: 'deletecompetencypcobjectiveconfirmation', component: 'local_competency',param: triggerElement.dataset.competencypcobjectivename},
        ];

        getStrings(requiredStrings).then(([deleteCompetencypcobjective, deleteConfirm]) => {
            return Notification.confirm(deleteCompetencypcobjective, deleteConfirm, deleteCompetencypcobjective, null, () => {
                const pendingPromise = new Pending();
                const request = {
                    methodname: 'local_competency_delete_competencypcobjectives',
                    args: {competencypcid: triggerElement.dataset.competencypcid,competencypcobjectiveid: triggerElement.dataset.competencypcobjectiveid,competencypcobjectivetype: triggerElement.dataset.competencypcobjectivetype}
                };

                Ajax.call([request])[0].then(response => {
                    if (response.result) {
                        window.location.reload();
                    } else {
                        Notification.addNotification({
                            type: 'error',
                            message: response.warnings[0].message
                        });
                    }
                    return pendingPromise.resolve();
                }).catch(Notification.exception);
            });
        }).catch();
    });
};
