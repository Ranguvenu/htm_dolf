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

import TPDynamicForm from 'local_trainingprogram/dynamicform';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Templates from 'core/templates';
import Ajax from 'core/ajax';
import {get_string as get_string} from 'core/str';
import {get_strings as get_strings} from 'core/str';

import $ from 'jquery';

/**
 *
 * @module     local_competency/competency
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const Selectors = {
    actions: {
        editCompetency: '[data-action="editcompetency"]',
        editcompetencyPerformance: '[data-action="editcompetencyperformance"]',
        editcompetencyObjective: '[data-action="editcompetencyobjective"]',
        viewcompetencyLearningcontent: '[data-action="viewcompetencylearningcontent"]',
        viewquestionCompetencies: '[data-action="viewquestioncompetencies"]',
        editlevelDescription: '[data-action="editleveldescription"]',
    },
};

export const init = () => {
    document.addEventListener('click', function(e) {
        let element = e.target.closest(Selectors.actions.editCompetency);
        if (element) {
            e.preventDefault();
            const title = element.getAttribute('data-id') ?
                get_string('editcompetency', 'local_competency', element.getAttribute('data-name')) :
                get_string('createcompetency', 'local_competency');
            const form = new TPDynamicForm({
                formClass: 'local_competency\\form\\competency_form',
                args: {id: element.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: element,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }

        let competencyelement = e.target.closest(Selectors.actions.editcompetencyPerformance);
        if (competencyelement) {
            e.preventDefault();
            const title = competencyelement.getAttribute('data-name');
            const form = new TPDynamicForm({
                formClass: 'local_competency\\form\\competencyperformance_form',
                args: {competency: competencyelement.getAttribute('data-competencyid'),id: competencyelement.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: competencyelement,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }

        let competencyobjelement = e.target.closest(Selectors.actions.editcompetencyObjective);
        if (competencyobjelement) {
            e.preventDefault();
            const title = competencyobjelement.getAttribute('data-name');
            const form = new TPDynamicForm({
                formClass: 'local_competency\\form\\competencyobjective_form',
                args: {competency: competencyobjelement.getAttribute('data-competencyid'),competencypc: competencyobjelement.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: competencyobjelement,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }

        let learningcontent = e.target.closest(Selectors.actions.viewcompetencyLearningcontent);
        if (learningcontent) {


            var modalPromise = ModalFactory.create({
                type: ModalFactory.types.DEFAULT
            });

            var stringsPromise =get_strings([
                    {
                        key: 'learningcontenttrainingprogram',
                        component: 'local_competency',
                    },
                    {
                        key: 'learningcontentexam',
                        component: 'local_competency',
                    },
                    {
                        key: 'viewlearningcontent',
                        component: 'local_competency',
                        param:learningcontent.getAttribute('data-name')
                    },
                    {
                        key: 'learningcontentquestion',
                        component: 'local_competency',
                    },
                    {
                        key: 'learningcontentlevels',
                        component: 'local_competency',
                    }

            ]);
            $.when(stringsPromise, modalPromise).then(function(strings, modal) {

                var tabone = {'active' :'active','type':'exams','name':strings[1]};

                var tabtwo = {'active' :'','type':'trainingprograms','name':strings[0]};


                var options = {};
                options.competencyid = learningcontent.getAttribute('data-id');
                options.tabs = [tabone,tabtwo];
                options.contenttype = learningcontent.getAttribute('data-contenttype');

                if(options.contenttype == 'competencypc'){

                    var tabthree = {'active' :'','type':'questions','name':strings[3]};

                    options.tabs = [tabone,tabtwo,tabthree];

                }

                this.modal = modal;
                this.modal.setTitle(strings[2]);

                this.modal.setBody(Templates.render('local_competency/learningcontent_display',options));

                this.modal.getRoot().on(ModalEvents.hidden, function() {
                    this.modal.destroy();
                }.bind(this));

                this.modal.setLarge();

                this.modal.show();

            }.bind(this));
        }

        let questioncompetencies = e.target.closest(Selectors.actions.viewquestionCompetencies);
        if (questioncompetencies) {
             e.preventDefault();
             e.stopImmediatePropagation();

            var modalPromise = ModalFactory.create({
                type: ModalFactory.types.DEFAULT
            });

            var stringsPromise =get_strings([
                    {
                        key: 'viewquestioncompetencies',
                        component: 'local_competency',
                        param:questioncompetencies.getAttribute('data-name')
                    }

            ]);
            $.when(stringsPromise, modalPromise).then(function(strings, modal) {


                var options = {};
                options.questionid = questioncompetencies.getAttribute('data-id');

                this.modal = modal;
                this.modal.setTitle(strings[0]);

                this.modal.setBody(Templates.render('local_competency/questioncompetencies_display',options));

                this.modal.getRoot().on(ModalEvents.hidden, function() {
                    this.modal.destroy();
                }.bind(this));

                 this.modal.getRoot().addClass('questioncmtncTransition');

                this.modal.setLarge();

                this.modal.show();

            }.bind(this));
        }

        let levelelement = e.target.closest(Selectors.actions.editlevelDescription);
        if (levelelement) {
            e.preventDefault();
            const title = levelelement.getAttribute('data-levelid') ?
                get_string('editleveldescription', 'local_competency', levelelement.getAttribute('data-name')) :
                get_string('addleveldescription', 'local_competency');
            const form = new TPDynamicForm({
                formClass: 'local_competency\\form\\competencylevel_form',
                args: {competencyid: levelelement.getAttribute('data-competencyid'),levelid: levelelement.getAttribute('data-levelid')},
                modalConfig: {title},
                returnFocus: levelelement,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
    });
};
