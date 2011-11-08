<?php
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
 * Defines the main subcourse configuration form
 */

require_once('moodleform_mod.php');

class mod_subcourse_mod_form extends moodleform_mod {

    public function definition() {

        $mform    =& $this->_form;

        // General settings -------------------------------------------------------------
        /// Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));
        /// Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('subcoursename', 'subcourse'),
                           array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        /// Adding the optional "intro" and "introformat" pair of fields
        $this->add_intro_editor(true, get_string('subcourseintro', 'subcourse'));

        // Subcourse information --------------------------------------------------------
        $mform->addElement('header', 'subcoursefieldset', get_string('refcourse', 'subcourse'));

        /// Referenced course selector
        $mycourses = subcourse_available_courses();
        $catlist = array();
        $catparents = array();
        make_categories_list($catlist, $catparents);
        $options = array();
        foreach ($mycourses as $mycourse) {
            if (empty($options[$catlist[$mycourse->category]])) {
                $options[$catlist[$mycourse->category]] = array();
            }
            $courselabel = $mycourse->fullname.' ('.$mycourse->shortname.')';
            $options[$catlist[$mycourse->category]][$mycourse->id] = $courselabel;
            if (empty($mycourse->visible)) {
                $hiddenlabel = ' '.get_string('hiddencourse', 'subcourse');
                $options[$catlist[$mycourse->category]][$mycourse->id] .= $hiddenlabel;
            }
        }
        unset($mycourse);
        /**
         * @var $refcourseelement HTML_QuickForm_input
         */
        $refcourseelement = $mform->addElement('selectgroups', 'refcourse',
                                               get_string('refcourselabel', 'subcourse'), $options);
        $mform->addHelpButton('refcourse', 'refcourse', 'subcourse');


        // Option to add a meta course enrolment to the other course.
        /**
         * @var $addmetaelement HTML_QuickForm_input
         */
        $addmetaelement = $mform->addElement('checkbox', 'addmeta',
                           get_string('addmeta', 'subcourse'));

        // Do we want the course to be one that students must take?
        $compulsoryelement = $mform->addElement('checkbox', 'compulsory',
                           get_string('compulsory', 'subcourse'));
        $mform->disabledIf('compulsory', 'addmeta');

        // If there is a meta enrolment already, we don't want to allow people to delete it as we
        // may cause problems. Force them to delete the whole subcourse if they want to do this
        if (!empty($this->current->id)) {
            $metaexists = subcourse_meta_exists($this->current->course, $this->current->refcourse);
            if ($metaexists) {
                $mform->setDefault('addmeta', 'checked');
                $compulsorydefault = $metaexists->customint2 ? 'checked' : '';
                $mform->setDefault('compulsory', $compulsorydefault);
                $refcourseelement->updateAttributes(array('disabled' => true));
                $addmetaelement->updateAttributes(array('disabled' => true));
            }
        }

        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        // add standard buttons, common to all modules
        $this->add_action_buttons();

    }
}

