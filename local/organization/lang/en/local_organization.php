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
 * Language strings
 *
 * @package    local
 * @subpackage organization
 * @copyright  2022 Revanth kumar Grandhi <revanth.g@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['iconstyle'] = 'Icon Style';
$string['missingtheme'] = 'Select Theme';
$string['theme'] = 'Theme Name';
$string['msg_del_reg_schl'] = 'Hi {$a->username}<br> You are un assigned from organization {$a->organizationname}.';
$string['msg_add_reg_schl'] = 'Hi {$a->username}<br> You are assigned to client {$a->organizationname}.';
$string['assignrole_help'] = 'Assign a role to the user in the selected client.';
$string['assignedorganization'] = 'Assigned Clients';
$string['assignorganization_help'] = 'Assign this user to a client.';
$string['anyorganization'] = 'Any client';
$string['campus'] = 'Campus';
$string['university'] = 'University';
$string['location'] = 'Location';
$string['organizationlevel'] = 'clientLevel';
$string['assignedtoorganizations'] = 'Assigned to client';
$string['assignorganization'] = 'Assign clients';
$string['notassignedorganization'] = 'Sorry you are not assigned to any organization.';
$string['organizationscolleges'] = 'Clients';
$string['organizationid'] = 'Clients';
$string['organizationrequired'] = 'client field is mandatory';
$string['missingorganization'] = 'Please select the client';
$string['select'] = 'Select client';
$string['selectsuborganization']='Sub Department';
$string['organizationname'] = 'Organization name (English)';
$string['universitysettings'] = 'University Settings';
$string['cobaltLMSentitysettings'] = 'Entity Settings';
$string['organizationsettings'] = 'client Settings';
$string['GPA/CGPAsettings'] = 'GPA/CGPA Settings';
$string['PrefixandSuffix'] = 'Prefix and Suffix';
$string['assignmanager_title'] = 'client : Assign Managers';
$string['pluginname'] = 'Organization';
$string['orgStructure'] = 'Organization Structure';
$string['department_structure'] = 'Department Structure';
$string['orgmanage'] = 'Manage Organization';
$string['manageorganizations'] = 'Manage Departments';
$string['allowframembedding'] = 'This page allows you to manage (delete/edit) the organizations that are defined under this institution.';
$string['description'] = 'Description';
$string['deleteorgconfirm'] = 'Do you want to delete "<b>{$a}</b>" ?';
$string['deleteorganization'] = 'Delete Organization';
$string['delconfirm'] = 'Do you really want to delete this Course?';
$string['editorganization'] = 'Edit Organization';
$string['missingorganizationname'] = 'Organization name in english can\'t  be empty';
$string['vieworganization'] = 'Organization Info';
$string['viewsubdepartments'] = 'View Sub-Departments';
$string['top'] = 'Top';
$string['parent'] = 'Parent';
$string['parent_help'] = "To create a New client at Parent Level, please select 'Parent' ";
$string['organization'] = 'Organization';
$string['assignusers'] = 'Assign Managers';
$string['viewusers'] = 'View Users';
$string['unassign'] = 'Un assign';
$string['username'] = 'Managers';
$string['noprogram'] = 'No program is assigned';
$string['noorganization'] = 'No client is assigned';
$string['selectorganization'] = 'TOP Level';
$string['createsuccess'] = 'client with name "{$a->organization}" created successfully';
$string['updatesuccess'] = 'client with name "{$a->organization}" updated successfully';
$string['deletesuccess'] = 'Deleted Successfully';
$string['deletesuccessorganization'] = 'Client "<b>{$a}</b>" deleted Successfully';
$string['type'] = 'Type';
$string['type_help'] = 'Please select your client Type. If it is "University" please select University as Type. If it is "Campus"  select Campus as Type.';
$string['chilepermissions'] = 'Do we need to allow the manager to see child courses of this organization.';
$string['create'] = 'Create Departments';
$string['update_organization'] = 'Update Department';
$string['update_subdept'] = 'Update Sub Department';
$string['view'] = 'View Departments';
$string['assignmanager'] = 'Assign Managers';
$string['info'] = 'Help';
$string['reports'] = 'Reports';
$string['alreadyassigned'] = 'Already user is assigned to selected client "{$a->organization}"';
$string['assignedsuccess'] = 'Successfully assigned manager to Department.';
$string['permissions'] = 'Permissions';
$string['permissions_help'] = 'Do we need to allow the manager to see child courses of this client.';
$string['programname'] = 'Program Name';
$string['unassignmanager'] = "Are you sure, you want to unassign Manager?";
$string['unassingheading'] = 'Unassign Manager';
$string['unassignedsuccess'] = 'Successfully Unassigned Manager from client';
$string['problemunassignedsuccess'] = 'There is a problem in Unassigning manager from client';
$string['assignedfailed'] = 'Error in assigning a user';
$string['cannotdeleteorganization'] = 'As the client "{$a->scname}" has sub client, you cannot delete it. Please delete the assigned Departments or programs first and come back here. ';
$string['nousersyet'] = 'No User is having Manager Role';
$string['saction'] = 'Action';
$string['assignmanagertxt'] = "Assign the manager to a Departments by selecting the respective manager, next selecting the respective organizations and then clicking on 'Assign Manager' ";
$string['organization:manage'] = 'organization:manage';
$string['organization:view'] = 'organization:view';
$string['organization:manage_owndepartments'] = 'organization:manage_owndepartments';
$string['organization:manage_ownorganizations'] = 'organization:manage_ownorganizations';
$string['organization:assignusers'] = 'organization:assignusers';
$string['organization:manage_multidepartments'] = 'organization:manage_multidepartments';

$string['nopermissions'] = 'Sorry, You dont have Permissions ';
$string['errormessage'] = 'Error Message';
$string['assign_organization'] = 'Assigned Departments ';
$string['programsandorganizations'] = "<h3>Programs and Departments Assigned to this organization</h3>";
$string['success'] = 'clients "{$a->organization}" successfully {$a->visible}.';
$string['failure'] = 'You can not inactivate Departments.';
/* * **strings for bulk upload*** */
$string['allowdeletes'] = 'Allow deletes';
$string['csvdelimiter'] = 'CSV delimiter';
$string['defaultvalues'] = 'Default values';
$string['deleteerrors'] = 'Delete errors';
$string['encoding'] = 'Encoding';
$string['errors'] = 'Errors';
$string['nochanges'] = 'No changes';
$string['rowpreviewnum'] = 'Preview rows';
$string['uploadorganizations'] = 'Upload Departments';
$string['uploadorganization_help'] = ' The format of the file should be as follows:
* Please download sample excelsheet through button provided .
* Enter the values based upon the information provided in Information/help tab';
$string['uploadorganizationspreview'] = 'Upload Departments preview';
$string['uploadorganizationsresult'] = 'Upload Departments results';
$string['organizationaccountupdated'] = 'Departments updated';
$string['organizationaccountuptodate'] = 'Departments up-to-date';
$string['organizationdeleted'] = 'Client deleted';
$string['organizationscreated'] = 'Client created';
$string['organizationsdeleted'] = 'Client deleted';
$string['organizationsskipped'] = 'Client skipped';
$string['organizationsupdated'] = 'Client updated';
$string['uubulk'] = 'Select for bulk organization actions';
$string['uubulkall'] = 'All Departments';
$string['uubulknew'] = 'New Departments';
$string['uubulkupdated'] = 'Updated Departments';
$string['uucsvline'] = 'CSV line';
$string['uuoptype'] = 'Upload type';
$string['uuoptype_addnew'] = 'Add new only, skip existing Departments';
$string['uuoptype_addupdate'] = 'Add new and update existing Departments';
$string['uuoptype_update'] = 'Update existing Departments only';
$string['uuupdateall'] = 'Override with file and defaults';
$string['uuupdatefromfile'] = 'Override with file';
$string['uuupdatemissing'] = 'Fill in missing from file and defaults';
$string['uuupdatetype'] = 'Existing organization details';
$string['uploadorganizations'] = 'Upload Departments';
$string['uploadorganization'] = 'Upload Departments';
$string['organizationnotaddedregistered'] = 'Departments not added, Already manager';
$string['neworganization'] = 'New program created';
$string['parentid'] = 'Parentid';
$string['uploadorganizationspreview'] = 'Uploaded Departments preview';
$string['visible'] = 'Visible';
$string['duration'] = 'Duration';
$string['timecreated'] = 'Time Created';
$string['timemodified'] = 'Time modofied';
$string['organizationmodified'] = 'organization modified';
$string['description'] = 'Description';
$string['uploadorganizationspreview'] = 'Uploaded Departments Preview';
$string['uploadorganizations'] = 'Upload Departments';
$string['organizations'] = 'Departments';
$string['no_user'] = "No user is assigned till now";
$string['information'] = 'A organization in Cobalt Learning Management System is defined as college/institution that offers program(s). The organization(s) is instructed/disciplined by Instructor(s). A organization has its own programs and clients. ';
$string['addorganizationtabdes'] = 'This page allows you to create/define a new organization.<br>
Fill in the following details and click on  create college to create a new college.';
$string['editorganizationtabdes'] = 'This page allows you to edit organization.<br>
Fill in the following details and click on  Update organization.';
$string['asignmanagertabdes'] = 'This page allows you to assign manager(s) to the respective organization(s). ';
$string['eventlevel_help'] = '<b style="color:red;">Note: </b>Global level is a default event level <br />
                                             We have four levels of events
                                            <ul><li><b>Global:</b> Site level events</li><li><b>organization:</b> Events for particular organization<li><b>program:</b>Events for particular program</li><li><b>Semester:</b> Events for particular semester</li></ul>';
$string['list'] = '
<p style="text-align:justify;">We are accepting online application for the program <i>{$a->pfn}</i>
under the organization <i>{$a->sfn}</i> from <i>{$a->sd}</i>. Last date for online submission is <i>{$a->ed}</i>. Please click on below <i>Apply Now </i> button to submit online application.  <a href="program.php?id={$a->pid}">Readmore</a> for details.</p>';
$string['lists'] = '
<p style="text-align:justify;">We are accepting online application for the program <i>{$a->pfn}</i>
under the organization <i>{$a->sfn}</i> from <i>{$a->sd}</i>. Please click on below <i>Apply Now </i> button to submit online application. Click <a href="program.php?id={$a->pid}">here</a> for more details.</p>';
$string['graduatelist'] = '
<p style="text-align:justify;">Online applications will be accepted from <i>{$a->sd}</i> under the organization <i>{$a->sfn}</i>.
Last date for online submissions is <i>{$a->ed} </i>.
<a href="program.php?id={$a->pid}">Readmore </a>for details.Click on <i>Apply Now</i> button to submit the online application.</p>';
$string['graduatelists'] = '
<p style="text-align:justify;">Online applications will be accepted from <i>{$a->sd}</i> under the organization <i>{$a->sfn}</i>. Click
<a href="program.php?id={$a->pid}">here </a>for more details.Click on <i>Apply Now</i> button to submit the online application.</p>';
$string['offlist'] = '
<p style="text-align:justify;">We are accepting applications for the program <i>{$a->pfn}</i>
under the organization <i>{$a->sfn}</i> from <i>{$a->sd}</i>. Last date for online submission is <i>{$a->ed}</i>. Please click on below <i>Download </i> button to download application.  <a href="program.php?id={$a->pid}">Readmore</a> for details.</p>';
$string['offlists'] = '
<p style="text-align:justify;">We are accepting applications for the program <i>{$a->pfn}</i>
under the organization <i>{$a->sfn}</i> from <i>{$a->sd}</i>. Please click on below <i>Download </i> button to download application.  <a href="program.php?id={$a->pid}">Readmore</a> for details.</p>';
$string['offgraduatelist'] = '
<p style="text-align:justify;">Applications will be accepted from <i>{$a->sd}</i> under the organization <i>{$a->sfn}</i>.
Last date for application submissions is <i>{$a->ed} </i>.
<a href="program.php?id={$a->pid}">Readmore </a>for details.Click on <i>Download </i> button to download the application.</p>';
$string['offgraduatelists'] = '
<p style="text-align:justify;">Applications will be accepted from <i>{$a->sd}</i> under the organization <i>{$a->sfn}</i>.
<a href="program.php?id={$a->pid}">Readmore </a>for details.Click on <i>Download</i> button to download the application.</p>';
$string['applydesc'] = 'Thank you for your interest!<br>
To be a part of this organization, please fill in the following details and complete the admission process.<br>
You are applying to-<br>
organization Name :<b style="margin-left:5px;font-size:15px;margin-top:5px;">{$a->organization}</b><br>
Program Name :<b style="margin-left:5px;font-size:15px;">{$a->pgm}</b><br/>
Date of Application :<b style="margin-left:5px;font-size:15px;">{$a->today}</b>';
$string['pgmheading'] = 'organization & Program Details';
$string['reportdes'] = 'The list of accepted applicants is given below along with the registered organization name, program name, admission type, student type, and the status of the application.
<br>Apply filters to customize the view of applicants based on the application type, program type, organization, program, student type, and status.';
$string['viewapplicantsdes'] = 'The list of registered applicants is given below so as to view their applications and confirm their admission. Applicants whose details furnished do not meet the requirement can be rejected based on the rules and regulations.
<br>Using the filters, customize the view of applicants based on the admission type, program type, organization, program and curriculum.
';
$string['help_des'] = '<h1>View Departments</h1>
<p>This page allows you to manage (delete/edit) the Departments that are defined under this institution.</b></p>

<h1>Add New</h1>
<p>This page allows you to create/define a new organization. </b></p>
<p>Fill in the following details and click on save changes to create a new organization.</p>
<ul>
<li style="display:block"><h4>Parent</h4>
<p>Parent denotes the main institution that can be categorized into different Departments, campus, universities etc. It can have one or multiple (child) sub-institutions.</b></p>
<p>Select the top level or the parent organization under which the new organization has to be created. </p>
<p><b>Note*:</b> Select \'Top Level\', if the new organization will be the parent organization or the highest level under this institution.</p></li>
<li style="display:block"><h4>Type</h4>
<p>Defines the type of institution or the naming convention you would like to apply for the above mentioned institution.</b></p>
<p><b>Campus -</b> A designation given to an educational institution that covers a large area including library, lecture halls, residence halls, student centers, parking etc.</p>
<p><b>University -</b> A designation given to an educational institution that grants graduation degrees, doctoral degrees or research certifications along with the undergraduate degrees. <Need to check/confirm></p>
<p><b>organization -</b> An educational institution or a part of collegiate university offering higher or vocational education. It may be interchangeable with University. It may also refer to a secondary or high organization or a constituent part of university.</p></li></ul>
<h1>Assign Manager</h1>
<p>This page allows you to assign manager(s) to the respective organization(s). </b></p>
<p>To assign manager(s), select the manager(s) by clicking on the checkbox, then select the organization from the given list and finally click on \'Assign Manager\'.</p>
';
$string['organization:create'] = 'organization:Create';
$string['organization:update'] = 'organization:Update';
$string['organization:visible'] = 'organization:Visible';
$string['organization:delete'] = 'organization:delete';
$string['organization:assignmanager'] = 'organization:Assign Manager to organization';
$string['permissions_error'] = 'Sorry! You dont have permission to access';
$string['notassignedorganization_ra'] = 'Sorry! You are not assigned to any organization/organization, Please click continue button to Assign.';
$string['notassignedorganization_otherrole'] = 'Sorry! You are not assigned to any organization/organization, Please inform authorized user(Admin or Manager) to Assign.';
$string['organizationnotfound_admin'] = 'Sorry! organization not created yet, Please click continue button to create.';
$string['organizationnotfound_otherrole'] = 'Sorry! organization not created yet, Please inform authorized user(Admin or Manager) to Crete organization';
$string['organizationnotcreated'] = 'Sorry! organization not created yet, Please click continue button to create or go to create organization/organization tab.';
$string['navigation_info'] = 'Presently no data is available, Click here to ';
$string['positions'] = 'Position';
$string['skillset'] = 'Skill set';
$string['subskillset'] = 'Sub skill set';
$string['batch'] = 'Batch';
$string['department'] = 'Department';
$string['organization'] = 'Organization';
$string['organization/department'] = 'Organization / Department';
$string['shortname'] = 'Code';
$string['shortnametakenlp'] = 'Code <b>"{$a}"</b> already taken ';
$string['assignemployee'] = 'Assign student';
$string['globalcourse']='Is this Global Course?';
$string['addnewcourse']='Add New Course';
$string['subdepartment'] = 'Sub Department';
$string['subsubdepartment'] = 'Sub Sub Department';
$string['createnewcourse'] = 'Create New +';
$string['assignroles'] = 'Assign Roles';
$string['search'] = 'Search';
$string['upload_users'] = 'Manage Users';
$string['uploadusers'] = 'Upload Users';
$string['uploadusersresult'] = 'Uploaded Users Result';
$string['uploaduserspreview'] = 'Upload Users Preview';
$string['organization_name'] = 'organization';
$string['adneworganization'] = '<i class="fa fa-sitemap popupstringicon" aria-hidden="true"></i> Add New Organization <div class= "popupstring"></div>';

$string['cuplan'] = 'cuplan';
$string['deptconfig'] = 'Department Configuration';
$string['orgconfig'] = 'Organization Configuration';
$string['organisations'] = 'Organizations';
$string['noorganizationsavailable'] = 'No organizations available';
$string['adnewdept'] = '<i class="fa fa-sitemap popupstringicon" aria-hidden="true"></i> Add new Department <div class= "popupstring"></div>';
$string['adnewsubdept'] = '<i class="fa fa-sitemap popupstringicon" aria-hidden="true"></i> Add new subdepartment <div class= "popupstring"></div>';
$string['addnewdept'] = 'Create New Department';
$string['parentcannotbeempty'] = 'Parent cannot be empty';
$string['shortnamecannotbeempty'] = 'Code cannot be empty';
$string['fullnamecannotbeempty'] = 'Organization name in english can\'t  be empty';
$string['confirmationmsgfordel'] = 'Are you sure, you really want to delete this <b>{$a}</b>';
$string['editcostcen'] = '<i class="fa fa-sitemap popupstringicon" aria-hidden="true"></i> Update Organization <div class= "popupstring"></div>';
$string['createdepartment'] = 'Create Department';
$string['createsubdepartment'] = 'Create Subdepartment';

$string['confirmation_to_disable_0'] = 'Are you sure you want to active <b>{$a}</b> organization.';
$string['confirmation_to_disable_1'] = 'Are you sure you want to in-active <b>{$a}</b> organization.';
$string['confirmation_to_disable_department_0'] = 'Are you sure you want to active <b>{$a}</b> Department.';
$string['confirmation_to_disable_department_1'] = 'Are you sure you want to in-active <b>{$a}</b> Department.';

$string['organization_logo'] = 'Preferred Logo';
$string['preferredscheme'] = ' Select Preferred scheme';
$string['editdep'] = 'Edit Department';
$string['fieldlabel'] = 'Licence Key';
$string['notemptymsg'] = 'Licence Key should not be Empty';
$string['organisation'] = 'Organization';
$string['cannotcreatedept'] = 'You cannot create Department until atleast one Organization creation';
$string['addnewdept/subdept'] = 'Add New Department/Subdepartment';
$string['addnewsubdept'] = 'Create New Subdepartment';
$string['scheme_1'] = 'Greenish';
$string['scheme_2'] = 'Red Fox';
$string['scheme_3'] = 'Purple Amethyst';
$string['scheme_4'] = 'Silk Blue';
$string['scheme_5'] = 'Midnight Blue';
$string['scheme_6'] = 'Gunmetal';
$string['organization:assign_multiple_departments_manage'] = 'Assign multiple departments';
$string['organization:manage_multiorganizations'] = 'Manage multiple organizations';
$string['organization:manage_ownorganization'] = 'Manage ownorganization';
$string['organization:manage_subdepartments_manage'] = 'Manage_subdepartments';
$string['toomanyoptionstoshow'] = 'Too many options ({$a}) to show';
$string['onlinecourses'] = 'Online Courses';
$string['learningpaths'] = 'Learning Paths';
$string['instledcourses'] = 'Instructor-led Courses';
$string['scheme_6'] = 'Gunmetal';
$string['banner_description'] = 'Front Page Banner Description';
$string['headerleft_logo'] = 'Header Logo';
$string['banner_image'] = 'Banner Image';
$string['enrollusers'] = 'User enrolling';
$string['createorganization'] = 'Add Organization';
$string['enrolluserssuccess'] = '<b>{$a->changecount}</b> User successfully enrolled to this <b>"{$a->course}"</b> organization .';
$string['unenrolluserssuccess'] = '<b>{$a->changecount}</b> User successfully un-enrolled to this <b>"{$a->course}"</b> organization .';
$string['click_continue'] = 'Click on continue';
$string['orglist'] = 'Organization List';
$string['selectsector'] = 'Select Sector';
$string['selectsegment'] = 'Select Segment';
$string['segment'] = 'Segment';
$string['Fieldofwork'] = 'Field Of Work';
$string['ContactInformation'] = 'Contact Information';
$string['HRManager'] = 'HR Manager Information';
$string['AlternativeContact'] = 'Alternative Contact Information';
$string['hrfullname'] = 'HR';
$string['hrjobrole'] = 'HR Job Role';
$string['hremail'] = 'HR Email';
$string['hrmobile'] = 'HR Mobile';
$string['alfullname'] = 'fullname';
$string['aljobrole'] = 'Jobrole';
$string['alemail'] = 'Email';
$string['almobile'] = 'Mobile';
$string['approval_letter'] = 'Approval Letter';
$string['apply'] = 'Apply';
$string['reset'] = 'Reset';
$string['orgsector'] = 'Select Sector';
$string['selectsector'] = 'Select Sector';
$string['edit'] = 'Edit';
$string['orgposition'] = 'Select Position';
$string['noorgs'] = 'No Organization found';
$string['orgdepartment'] = 'Select Department';
$string['orgfieldofwork'] = 'Select Job Family';
$string['addorguser'] = 'Add Organization User';
$string['org'] = 'Organization';
$string['enroltoorgof'] = 'Assign Users To Organization Official Role';
$string['user'] = 'Users';
$string['users'] = 'Users';
$string['deleteallconfirm'] = 'Are you sure, you want to Un Assign ';
$string['deleteuser'] = 'Delete';
$string['sector'] = 'Sector';
$string['position'] = 'Position';
$string['jobfamily'] = 'Job family';
$string['userenrolments']  = 'User enrolments';
$string['email']  = 'Email';
$string['organization']  = 'Organization';
$string['apply']  = 'Apply';
$string['reset']  = 'Reset';
$string['un_enrollusers']  = 'Un enroll users';
$string['missinguser']  = 'Please select user';
$string['select_all']  = 'Select all';
$string['remove_all']  = 'Remove all';
$string['listoforganization']  = 'List Of Organization';
$string['delete']  = 'Delete';
$string['rejectorganization']  = 'Reject Organization';
$string['approveorganization']  = 'Approve Organization';
$string['reject']  = 'Reject';
$string['approve']  = 'Approve';
$string['nodataavailable']  = 'No Data Available';
$string['manage_orgaization']  = 'Manage Organization';
$string['rejectallconfirm']  = 'Are you sure, you want to Reject';
$string['approveallconfirm']  = 'Are you sure, you want to  Approve';
$string['adduserto']  = 'Add user to';
$string['not_enrolled_users'] = '<div>Not Enrolled Users ({$a})</div>';
$string['enrolled_users'] = 'Enrolled Users(<span id = "enrolledcount">{$a}</span>)';
$string['availablelist'] = 'Available users (<span id = "availablecount">{$a}</span>)';
$string['hrfullnamecannotbeempty'] = 'HR fullname cannot be empty';
$string['hrjobrolecannotbeempty'] = 'HR job role cannot be empty';
$string['hremailcannotbeempty'] = 'HR email cannot be empty';
$string['hrmobilecannotbeempty'] = 'HR mobile cannot be empty';
$string['alfullnamecannotbeempty'] = 'Alternative fullname cannot be empty';
$string['aljobrolecannotbeempty'] = 'Alternative job role cannot be empty';
$string['alemailcannotbeempty'] = 'Alternative email cannot be empty';
$string['almobilecannotbeempty'] = 'Alternative mobile cannot be empty';
$string['descriptioncannotbeempty'] = 'Description cannot be empty';
$string['emailexists'] = 'Email exists already.';
$string['numeric'] = 'Only numeric values';
$string['phonenumvalidate']='Please enter a 10 digit valid number';
$string['shortnametakenlp'] = 'Code <b>"{$a}"</b> already taken ';
$string['fullnametakenlp'] = 'Name <b>"{$a}"</b> already taken ';
$string['search'] = 'Search';
$string['remove_selected_users'] = 'Remove';
$string['manage_organization'] = 'Manage Organization';
$string['add_selected_users'] = 'Add';
$string['no_data_available'] = 'No Data Available';
$string['approved'] = 'Approved';
$string['pending'] = 'Pending';
$string['rejected'] = 'Rejected';
$string['orgsegment'] = 'Segment';
$string['orgsector'] = 'Sector';
$string['orgfieldofwork'] = 'Field of work';
$string['hrfullname'] = 'HR';
$string['hrjobrole'] = 'HR Job Role';
$string['hremail'] = 'HR Email';
$string['hrmobile'] = 'HR Mobile';
$string['alfullname'] = 'Alternative Name';
$string['aljobrole'] = 'Alternative Job Role';
$string['alemail'] = 'Alternative Email';
$string['almobile'] = 'Alternative Mobile';
$string['authusers'] = 'Enrolled users';
$string['userswithroles'] = 'Organization officials & Trainees';
$string['uploadusers'] = 'Upload users';
$string['sample_csv'] = 'Sample Csv';
$string['emptyemail'] = '<div class="alert alert-error" role="alert">Please enter email in line no. {$a} of uploaded sheet.</div>';
$string['validateemail'] = '<div class="alert alert-error" role="alert">Invalid email in line no. {$a} of uploaded sheet.</div>';
$string['notorgemail'] = '<div class="alert alert-error" role="alert">Email in line no {$a} is not registered with the current organization.</div>';
$string['deleteemail'] = '<div class="alert alert-error" role="alert">Email in line no {$a} is Deleted.</div>';
$string['manualenrol'] = 'Manual enrolments';
$string['bulkupload'] = 'Bulk upload';
$string['fillwithouterrors'] = '<h4> Please fill the sheet without any errors. Refer Help Manual for assistance.</h4>';
$string['recordsupdated'] = '<div class="alert alert-success" role="alert">{$a->count} record(s) successfully updated.</div>';'<h6 style= "color:red;"> ({$a->linenum}) Users are updated  </h6>';
$string['uploaderrors'] = 'Errors in uploaded sheet';
$string['noorganization'] = 'There is no organization';

$string['select_sector'] = 'Select sector';
$string['jobrole'] = 'Job role';
$string['investmentbanking'] = 'Investment Banking';
$string['realestate'] = 'Realestate';
$string['insurance'] = 'Insurance';
$string['fieldworkother'] = 'Fieldwork Other';
$string['selectorgfieldofwork'] = 'Select Field Of Work';
$string['valorgsectorrequired'] = 'Sector field is mandatory';
$string['valorgsegmentrequired'] = 'Segment field is mandatory';
$string['valorgfieldofworkrequired'] = 'Fieldwork field is mandatory';
$string['orgsectorcannotbeempty'] = 'Sector field is mandatory';
$string['orgsegmentcannotbeempty'] = 'Segment field is mandatory';
$string['orgfieldofworkcannotbeempty'] = 'Fieldwork field is mandatory';
$string['choosesegment'] = 'Segment';
$string['selectfieldwork'] = 'Select Field Of Work';
$string['org_name'] = 'Organization Name';
$string['org_hr'] = 'HR Name';
$string['org_hr_email'] = 'HR Email';
$string['no_of_trainees'] = 'No of Trainees';
$string['org_status'] = 'Status';
$string['action'] = 'Action';
$string['organization_detailes'] = 'Oganization Info';
$string['role'] = 'Role';
$string['users_assigned'] = 'No of users assigned';
$string['action'] = 'Action';
$string['org_offcial'] = 'Organization Official';
$string['assign_offcial'] = 'Assign As Offcial';
$string['assign_trainee'] = 'Assign As Trainee';
$string['org_trainee'] = 'Trainee';
$string['name']='Name';
$string['email']='Email';
$string['identity_no']='Identity No';
$string['phone_no']='Phone No';
$string['role']='Role';
$string['action']='Action';
$string['action']='Action';
$string['un_assign']='Un Assign';
$string['search_enrolled_users']='Search Enrolled Users..';
$string['unassignconfirm'] = 'Un Assign Confirm!';
$string['orgunassignconfirm'] = 'Do you want to un assign "<b>{$a->username}</b>" who was assigned as a "<b>{$a->rolename}</b>" to "<b>{$a->orgname}</b>" organization?';
$string['unassigntext'] = 'Yes! Un Assign';
$string['select_sector'] = 'Select sector';
$string['jobfamily'] = 'Job family';
$string['segment'] = 'Segment';
$string['discount_percentage'] = 'Discount Percentage';
$string['numericonly'] = 'Percentage should be numeric.';
$string['selectorgofficial'] = 'Please select an user to assign as Organization Official';
$string['emailrequired'] = 'Email can not be empty';
$string['requiredvalidemail'] = 'Enter valid email';
$string['almobilemobilerequired'] = 'Alternative mobile number can not be empty';
$string['almobilerequirednumeric'] = 'Alternative mobile accepts numberic values only';
$string['almobileminimum10digitsallowed'] = 'Alternative mobile can not exceed 12 digits.';
$string['almobilestartswith5'] = 'Alternative mobile must starts with eaither 5/6/7/8/9';
$string['almobileminimum5digitsallowed'] = 'Alternative mobile must have minimum five digits and not more than 12 digits';

$string['hrmobilerequired'] = 'HR mobile number can not be empty';
$string['hrmobilerequirednumeric'] = 'HR mobile number accepts numberic values only';
$string['hrmobileminimum10digitsallowed'] = 'HR mobile number can not exceed 12 digits.';
$string['hrmobilestartswith5'] = 'HR mobile number must starts with eaither 5/6/7/8/9';
$string['hrmobileminimum5digitsallowed'] = 'HR mobile number must have minimum five digits and not more than 12 digits';

$string['messageprovider:organization_registration'] = 'Organization Registration';
$string['messageprovider:organization_assigning_official'] = 'Organization Assigning official';
$string['messageprovider:organization_assigning_trainee'] = 'Organization Assigning trainee';
$string['messageprovider:organization_enrollment'] = 'Organization enrollment';
$string['messageprovider:organization_wallet_update'] = 'Organization Wallet update';

$string['uploadorg'] = 'Upload Organizations';
$string['uploadafile'] = 'Upload File';
$string['youdonthabepermissiontouploaddata'] = 'You don\'t have permission to upload the sheet';
$string['uploadorgsheet'] = '{$a->count} records uploaded successfully.';
$string['help'] = "Help";
$string['sample']='Sample';
$string['help_1'] = 
'<div class="field_type font-weight-bold" style="text-align:left;">Mandatory Fields </div> <br>
<div class="helpmanual_table"><table class="generaltable" border="1">
<th>Field</th><th>Description</th><th>Sample Data</th>
<tr><td>OldID</td><td>The organization oldid.</td><td>2564ff#4453wqd444</td></tr>
<tr><td>License Key</td><td>Required Valid liceneckey.</td><td>156444534</td></tr>
<tr><td>Organization Name</td><td> The organization name.</td> <td> Classical Organization  or Regional Organization</td></tr>
<tr><td>Organization Arabic Name</td><td>The organization name in arabic.</td> <td> منظمة كلاسيكية  or منظمة إقليمية</td></tr>
<tr><td>Organization Code</td><td>Enter the organization code, avoid additional spaces.</td> <td> ORG123$ or ORG321&1$</td></tr>
<tr><td>Organization Description</td><td>Enter the organization description.</td><td><mlang en> Classical Organization <mlang> <mlang ar> منظمة كلاسيكية <mlang></td></tr>
<tr><td>Sector</td><td>The organization sectors  (Accepted shortcode and seperated by *).</td> <td> B*I or  B*I*F  </td> </tr>
<tr><td>Segment</td><td>The organization segments  (Accepted shortcode and seperated by *).</td> <td> BSA*IOS or  BAS*IOS*KLM  </td> </tr>
<tr><td>Field of work</td><td>Enter the field of work.</td><td> investmentbanking  or realestate or insurance</td></tr>
<tr><td>HR Name</td><td>The HR name.</td><td> Ibrahim  or Rehman</td></tr>
<tr><td>HR Jobrole</td><td>The HR jobrole name.</td><td>Consultant or Dietitian</td></tr>
<tr><td>HR Email</td><td>The HR email.</td><td>consultant@gmail.com or dietitian@gmail.com</td></tr>
<tr><td>HR Mobile</td><td>The HR mobile number.</td><td>9874563205 or 6598887989</td></tr>
<tr><td>Alternative Name</td><td>The alternative name.</td><td> Komal  or Fathima</td></tr>
<tr><td>Alternative Jobrole</td><td>The alternative jobrole name.</td><td>Doctor or Physician</td></tr>
<tr><td>Alternative Email</td><td>The alternative email.</td><td>komal@gmail.com or fathima@gmail.com</td></tr>
<tr><td>Alternative Mobile</td><td>The alternative mobile number.</td><td>9786545632 or 8697452558</td></tr>
<tr><td>Discount Percentage</td><td>Enter the discount percentage, avoid additional spaces.</td><td>2 or 5 or 10 or 100 </td></tr>
</table></div>
';
$string['organizationupload'] = 'OrganizationUpload';
$string['back_upload'] = "Back";
$string['validsheet'] = 'Please upload valid file. {$a} in uploaded sheet';
$string['org_emptymsg']='Please enter Organization Name in uploaded sheet at line {$a->excel_line_number}';
$string['error_orgcolumn_heading'] = 'OrganizationName is missing from headers';
$string['orgcode_emptymsg'] = 'Please enter Organization Code in uploaded sheet at line {$a->excel_line_number}';
$string['orgarabic_emptymsg'] = 'Please enter Organization Name in Arabic in uploaded sheet at line {$a->excel_line_number}';

$string['error_orgcodecolumn_heading'] = 'OrganizationCode is missing from headers';
$string['sector_emptymsg']='Please enter Sector Code in uploaded sheet at line {$a->excel_line_number}';
$string['error_sectorcolumn_heading'] = 'SectorCode is missing from headers';
$string['segment_emptymsg']='Please enter Segment Code in uploaded sheet at line no. {$a->excel_line_number}';
$string['error_segmentcolumn_heading'] = 'SegmentCode is missing from headers';

$string['fieldwork_emptymsg'] = 'Please enter Field OF Work in uploaded sheet at line {$a->excel_line_number}';
$string['error_fieldworkcolumn_heading'] = ' FieldOFWork is missing from headers';

$string['hr_emptymsg'] = 'Please enter HR Name in uploaded sheet at line {$a->excel_line_number}';
$string['error_hrcolumn_heading'] = 'HRName is missing from headers';

$string['hremail_emptymsg'] = 'Please enter HR Email in uploaded sheet at line {$a->excel_line_number}';
$string['error_hremailcolumn_heading'] = 'HREmail is missing from headers';

$string['hrjobrole_emptymsg'] = 'Please enter HR Jobrole in uploaded sheet at line {$a->excel_line_number}';
$string['error_hrjobrolecolumn_heading'] = 'HRJobrole is missing from headers';

$string['hrmobile_emptymsg'] = 'Please enter HR Mobile in uploaded sheet at line {$a->excel_line_number}';
$string['error_hrmobilecolumn_heading'] = 'HRMobile is missing from headers';

$string['altrname_emptymsg'] = 'Please enter Alternative Name in uploaded sheet at line {$a->excel_line_number}';
$string['error_altrnamecolumn_heading'] = 'AlternativeName is missing from headers';

$string['altremail_emptymsg'] = 'Please enter Alternative Email in uploaded sheet at line {$a->excel_line_number}';
$string['error_altremailcolumn_heading'] = 'Alternativeemail is missing from headers';

$string['altrjobrole_emptymsg'] = 'Please enter Alternative Jobrole in uploaded sheet at line {$a->excel_line_number}';
$string['error_altrjobrolecolumn_heading'] = 'AlternativeJobrole is missing from headers';

$string['altrmobile_emptymsg'] = 'Please enter Alternative Mobile in uploaded sheet at line {$a->excel_line_number}';

$string['sectorsnotmatchedwithrecords'] = 'Given sectors are not valid at line {$a->excel_line_number}.';

$string['segmentsnotmatchedwithrecords'] = 'Given segments are not valid at line {$a->excel_line_number}.';

$string['error_altrmobilecolumn_heading'] = 'AlternativeMobile is missing from headers';

$string['discountpercentage_heading'] = 'Discount percentage is missing from headers';

$string['requiredvalidhremail'] = 'Enter valid HR Email at line {$a->excel_line_number}';
$string['requiredvalidaltremail'] = 'Enter valid Alternative Email at line {$a->excel_line_number}';

$string['sector_codespaceerr']='Spaces are not allowed. Uploaded {$a->name_err} contains spaces at line {$a->excel_line_number}';

$string['sector_notvalid'] = 'Please enter vallid {$a->name_err} at line {$a->excel_line_number}';
$string['segment_notvalid'] = 'Please enter vallid Segment Code at line {$a->excel_line_number}';

$string['discount_neednumeric'] = 'discount percentage accepts numberic values only at line {$a->excel_line_number}.';
$string['discount_cant_exceed_100'] = 'discount percentage can not be more than 100% at line {$a->excel_line_number}.';

$string['mobile_requirednumeric'] = '{$a->name_err} number accepts numberic values only at line {$a->excel_line_number}';

$string['mobilestartswith5'] = '{$a->name_err} number must starts with eaither 5/6/7/8/9 at line {$a->excel_line_number}';

$string['mobileminimum5digitsallowed'] = '{$a->name_err} number must have minimum five digits and not more than 10 digits at line {$a->excel_line_number}';

$string['shortname_exist'] = 'Organization Code <b>"{$a->name_err}"</b> already taken at line {$a->excel_line_number}';
$string['organizationnamemissing'] = 'Organization Name is missing';
$string['organizationcodemissing'] = ' Organization Code is missing';
$string['descriptionmissing'] = ' Description is missing';
$string['sectorcodemissing'] = 'SectorCode is missing';
$string['segmentcodemissing'] = 'SegmentCode is missing';
$string['fieldofworkmissing'] = ' FieldOfWork is missing';
$string['hrnamemissing'] = 'HR Name is missing';
$string['hrjobrolemissing'] = 'HR Jobrole is missing';
$string['hremailmissing'] = 'HR Email is missing';
$string['hrmobilemissing'] = 'HR Mobile is missing';
$string['alternativenamemissing'] = 'Alternative Name is missing';
$string['alternativejobrolemissing'] = 'Alternative Jobrole is missing';
$string['alternativeemailmissing'] = 'Alternative Email is missing';
$string['alternativemobilemissing'] = 'Alternative Mobile is missing';
$string['discountpercentagemissing'] = 'Discount Percentage is missing';

$string['discount_percentage'] = 'Discount Percentage';
$string['search_organization'] = 'Search Organizations';
$string['uploadorg'] = 'Upload Organizations';
$string['fullnameinarabic'] = 'Organization name (Arabic)';
$string['missingorganizationfullnameinarabic'] = 'Organization name in arabic can\'t  be empty';

$string['fullnameinarabictakenlp'] = 'Name <b>"{$a}"</b> already taken ';
$string['organizationarabicnamemissing'] = 'Organization name in arabic is missing';

$string['arabicname_exist'] = 'Organization name in arabic <b>"{$a->name_err}"</b> already taken at line {$a->excel_line_number}';
$string['error_orgarabiccolumn_heading'] = 'OrganizationArabicName is missing from headers';
$string['uploadvalidsheet'] = 'Please upload valid file.';

$string['orgdescription_emptymsg'] = 'Please enter description in uploaded sheet at line {$a->excel_line_number}';
$string['notuploadorgsheet'] = '0 records updated';
$string['csv'] = '.CSV';
$string['organizationfilerequired'] = 'File can\'t be empty.';
$string['orgcreatedsuccessfully'] = 'Organization Created Successfully';
$string['invalidvalue'] = 'Missing {$a} value';
$string['orgshortnametakenlp'] = 'Organization shortname {$a} already taken';

$string['hrrequiredvalidemail'] = 'Enter valid HR email';
$string['alrequiredvalidemail'] = 'Enter valid alternative email';
$string['view_org'] = 'View';
$string['remove_dependency'] = 'You cannot delete this as it is mapped somewhere';
$string['uploaddoc_help'] = 'Upload list of organizations';
$string['uploaddoc'] = 'Upload Document';
$string['organization:visible'] = 'Manage Organization';
$string['orgcreated'] = 'Organization Created At';
$string['status'] = 'Status';
$string['trainee'] = 'Trainee';
$string['organizationofficial'] = 'Organization Official';
$string['organization_items_detailes'] = 'Oganization Items Info';
$string['itemname'] = 'Item Name';
$string['itemscount'] = 'No of Items';
$string['send'] = 'Send';
$string['hremailconfirm'] = 'Do you want to send email to "<b>{$a}</b>" ?';
$string['sendemail'] = 'Send Email';
$string['oldidmissing'] = 'OldID is missing';
$string['licensekeymissing'] = 'License key is missing';
$string['oldid_emptymsg'] = 'Please enter oldid in uploaded sheet at line {$a->excel_line_number}';
$string['licensekey_emptymsg'] = 'Please enter license key in uploaded sheet at line {$a->excel_line_number}';
$string['licensekeycodemismatched'] = 'Given licensekey <b>"{$a->licensekey}"</b> has mapped with another organization having shortname<b>"{$a->licensekeymappedorganization}"</b> at line {$a->excel_line_number}.';
$string['error_oldidcolumn_heading'] = 'OldID is missing from headers';
$string['error_licensekeycolumn_heading'] = 'Licese Key is missing from headers';
$string['subject'] = 'Subject';
$string['message'] = 'Message';
$string['subjectcannotbeempty'] = 'Subject cannot be empty';
$string['messagecannotbeempty'] = 'Message cannot be empty';
$string['sender_email'] = 'Sender Email';
$string['missing_field']='Missing Field';
$string['invalid_field']='Invalid Field';
$string['field_exists']='Field Already Exists';
$string['org_inserted']='Orgnization Inserted';
$string['header_missing']='Column Header Missing';
$string['orgname']='Organization';
$string['delete_bulk_upload_log']='Delete Organization Bulk Upload Log';
$string['max_discount']='Maximum Discount';
$string['maxdiscounterror']='Discount can not be more than maximum discount ("<b>{$a}</b>%").';
$string['validpercentagerequired']='Please enter a valid number';
$string['assign_user_to_org_offcial']='Assign Organization Users As Officials';
$string['assign_user_to_org']='Assign Users To Organization';
$string['licensekey']='License Key';
$string['licensekeyexistserror'] = 'License Key alreday taken for <b>{$a}</b>.';
$string['logo'] = 'Logo';
$string['jpg_format'] = '.JPG';
$string['png_format'] = '.PNG';
$string['jpeg_format'] = '.JPEG';
$string['selecttype'] = 'Select Type';
$string['partnertype'] = 'Partner Type';
$string['partner'] = 'Strategic Partner ';
$string['listoftype'] = 'List of Partner Type';
$string['search_type'] = 'Search Type ';
$string['createtype'] = 'Create ';
$string['actions'] = 'Actions ';
$string['deletetypes'] = 'Delete ';
$string['deletetypesconfirm'] = 'Are you sure you want to delete? ';
$string['types'] = 'Partnertypes ';
$string['partnertypes'] = 'Partner Types';
$string['serchpartnertypes'] = 'Search Partner Types';
$string['newpartnertype'] = 'Add New Partner Type';
$string['createpartner'] = 'Create Partner Type';
$string['partnernamenotbeempty'] = 'Partnername is missing';
$string['partnerarabicnamenotbeempty'] = 'Partner Arabicname is missing';
$string['descriptionnotbeempty'] = 'Description is missing';

$string['viewtypes'] = 'View Partner type';
$string['edittypes'] = 'Edit Partner type';
$string['deletetype'] = 'Delete Partner type';
$string['arabicname'] = 'Arabic Name';
$string['deletepartnerconfirm'] = 'Are you sure, you want to Delete  "<b>{$a}</b>" Partner type';

$string['orgpartner'] = 'Partner';
$string['arabicname'] = 'Arabic Name';
$string['arabicname'] = 'Arabic Name';
$string['rank'] = 'Rank';
$string['rankshouldbenumeric'] = 'Rank should be Numeric';
$string['rankfound'] = 'Rank is available';
$string['englishname'] = 'Name';
$string['orglogo'] = 'Logo';
$string['tax_number'] = 'Tax Number';
$string['tax_certificate'] = 'Tax Certificate';
$string['numeric'] = 'This field can only have numbers.';
$string['pdf_format'] = '.pdf';
$string['autoapproval'] = 'Auto Approval';
$string['partnerimage'] = 'Partner Image';
$string['invoice_summary'] = 'Invoice Summary';
$string['invoicenumber'] = 'Invoice Number';
$string['invoicetype'] = 'Invoice Type';
$string['learningtype'] = 'Learning Type';
$string['learningitem'] = 'Learning Item';
$string['amount'] = 'Amount';
$string['seats'] = 'Seats';
$string['invoicestatus'] = 'Invoice Status';
$string['paymentstatus'] = 'Payment Status';
$string['trainingprogram'] = 'Training Program';
$string['exam'] = 'Exam';
$string['events'] ='Event';
$string['filterselect'] = 'Please  Select One';
$string['learningtype'] = 'Learning Type';
$string['orgofficial'] = 'Organization Official';
$string['organization:assessment_operator_view'] = 'View Organization [Assessment Operator]';
$string['nopartner'] = 'No Partner';
$string['trainingpartner'] = 'Training Partner';
$string['orgfieldofworken'] = 'Field of work (English)';
$string['orgfieldofworkab'] = 'Field of work (Arabic)';
$string['due'] = 'Due';
$string['orderstatus'] = 'Order Status';
$string['approvedin'] = 'Approved';

$string['orgfieldofworkenerror'] = 'Field of work in english can\'t  be empty';
$string['orgfieldofworkaberror'] = 'Field of work in arabic can\'t  be empty';
$string['paidin'] = 'Paid';
$string['org_partner'] = 'Partner';
$string['yes'] = 'Yes';
$string['no'] = 'No';
$string['missinglicensekey'] = 'Please enter license key';
$string['licensekey10digitsallowed'] = 'License key must have minimum 10 digits';
$string['active'] = 'Active';
$string['inactive'] = 'In-Active';
