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
 * Strings for component 'WebEx Meeting', language 'en'
 *
 * @package    mod_webexactvity
 * @author     Eric Merrill <merrill@oakland.edu>
 * @copyright  2014 Oakland University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'اجتماع WebEx';
$string['pluginnamepural'] = 'اجتماعات WebEx';
$string['modulename'] = 'اجتماعات WebEx';
$string['modulename_help'] = 'يسمح نشاط WebEx Meeting للمدربين بجدولة الاجتماعات في نظام مؤتمرات الويب WebEx *.عند إضافة نشاط WebEx Meeting ، فإنك تحدد تاريخ الاجتماع ووقته ، بالإضافة إلى عدد من العوامل الاختيارية الأخرى (مثل المدة المتوقعة والوصف وما إلى ذلك). عندئذٍ يمكن للمشاركين (المتدربين المسجلين) الدخول إلى اجتماع WebEx من خلال النقر على رابط "الانضمام إلى الاجتماع" ضمن النشاط في Moodle (سيرى المدرسون رابطًا يقول "الاجتماع المضيف"). إذا تم تسجيل الاجتماع ، فسيتمكن المتدربين من عرض التسجيل بعد انتهاء الاجتماع.* WebEx هو نظام مؤتمرات عبر الويب يسمح للطلاب والمعلمين بالتعاون بشكل متزامن. ينقل الصوت والفيديو في الوقت الفعلي ، ويتضمن أدوات مثل السبورة البيضاء والدردشة ومشاركة سطح المكتب.';
$string['modulenameplural'] = 'اجتماعات WebEx';
$string['webexactivityname'] = 'اسم الاجتماع';
$string['pluginadministration'] = 'إدارة اجتماع WebEx';
$string['additionalsettings'] = 'إعدادات الاجتماع الإضافية';
$string['allchat'] = 'يمكن للمشاركين الدردشة مع المشاركين الآخرين ';
$string['apipassword'] = 'كلمة مرور مسؤول WebEx ';
$string['apipassword_help'] = 'كلمة المرور لحساب المسؤول على موقعك.';
$string['apisettings'] = 'إعدادات API';
$string['apiusername'] = 'اسم المستخدم لمسؤول WebEx  ';
$string['apiusername_help'] = 'اسم المستخدم لحساب المسؤول على موقعك. يجب أن يكون هذا حسابًا مخصصًا لـ Moodle لأسباب أمنية. ';
$string['availabilityendtime'] = 'تمديد وقت انتهاء الإتاحة ';
$string['badpasswordexception'] = 'كلمة مرور WebEx الخاصة بك غير صحيحة ولا يمكن تحديثها. ';
$string['badpassword'] = 'اسم مستخدم WebEx وكلمة المرور غير متطابقين. ';
$string['calpublish'] = 'نشر الاجتماع في التقويم ';
$string['calpublish_help'] = 'قم بنشر الاجتماع على تقويم الدورات التدريبية Moodle وقم بعرضه على صفحات لوحة تحكم المستخدم. لن يتم نشر اجتماعات الإتاحة الممتدة. ';
$string['confirmrecordingdelete'] = 'هل تريد بالتأكيد حذف التسجيل <b> {$a->name} </b> ، بمدة {a->time$}؟ هذا لا يمكن التراجع عنها.';
$string['confirmrecordingsdelete'] = 'هل أنت متأكد أنك تريد حذف التسجيلات المختارة؟ هذا لا يمكن التراجع عنها.';
$string['selectnone'] = 'الغاء تحديد الكل';
$string['connectionexception'] = 'حدث خطأ أثناء محاولة الاتصال: {$a->error} ';
$string['curlsetupexception'] = 'حدث خطأ أثناء إعداد curl. ';
$string['defaultmeetingtype'] = 'نوع الاجتماع الافتراضي ';
$string['defaultmeetingtype_help'] = 'نوع الاجتماع الذي سيتم تحديده مسبقًا عند إنشاء اجتماع جديد. ';
$string['deletelink'] = '<a href"{$a->url} "> حذف </a> ';
$string['deletionin'] = '<div> {$a->time} حتى الحذف. </ div> ';
$string['deletionsoon'] = '<div> سيتم حذفه قريباً. </ div> ';
$string['deletetime'] = 'وقت الحذف ';
$string['description'] = 'الوصف';
$string['directlinks'] = 'روابط مباشرة';
$string['directlinkstext'] = '<p> توفر هذه الروابط وصولاً مباشرًا إلى التسجيلات الموجودة على خادم Moodle. الوصول إليها لا يتطلب تسجيل الدخول ، ولم يتم تسجيلهم. </p> <br/>
Streaming: <a target_blank href="{$a->streamurl}" alt = "رابط البث"> {$a->streamurl} </a> <br />
Download: <a target_blank href="{$a->fileurl}" alt = "رابط البث"> {$a->fileurl} </a> <br />
';
$string['duration'] = 'المدة المتوقعة';
$string['duration_help'] = 'المدة المتوقعة للاجتماع للأغراض الإعلامية فقط ، ولا يؤثر على المدة التي يمكن أن يستمر الاجتماع خلالها. ';
$string['enablecallin'] = 'تمكين الاتصال الهاتفي';
$string['enablecallin_help'] = 'تفعيل دعم الاتصال الهاتفي للاجتماعات المنشأة حديثًا. لا تقم بالتمكين ما لم يكن لديك دعم الاتصالات الهاتفية CALLIN. ';
$string['entermeeting'] = 'الدخول إلى الاجتماع ';
$string['errordeletingrecording'] = 'خطأ في حذف التسجيل ';
$string['error_HM_AccessDenied'] = 'تم رفض الوصول لاستضافة هذا الاجتماع. ';
$string['error_JM_InvalidMeetingKey'] = 'حدث خطأ في مفتاح الاجتماع في WebEx ولا يمكنك الانضمام إلى هذا الاجتماع. ';
$string['error_JM_InvalidMeetingKeyOrPassword'] = 'حدث خطأ في مفتاح الاجتماع أو كلمة المرور في WebEx ولا يمكنك الانضمام إلى هذا الاجتماع. ';
$string['error_JM_MeetingLocked'] = 'هذا الاجتماع مغلق ولا يمكنك الانضمام إليه. ';
$string['error_JM_MeetingNotInProgress'] = 'الاجتماع غير نشط حاليا. ربما لم يبدأ بعد أو أنهى بالفعل. ';
$string['error_LI_AccessDenied'] = 'تعذر على المستخدم تسجيل الدخول إلى WebEx. ';
$string['error_LI_AccountLocked'] = 'حساب مستخدم WebEx مقفل. ';
$string['error_LI_AutoLoginDisabled'] = 'تم تعطيل عمليات تسجيل الدخول التلقائية لهذا المستخدم ';
$string['error_LI_InvalidSessionTicket'] = 'بطاقة الجلسة غير صالحة. حاول مرة اخرى.';
$string['error_LI_InvalidTicket'] = 'بطاقة تسجيل الدخول غير صالحة. حاول مرة اخرى.';
$string['error_unknown'] = 'حدث خطأ غير معروف.';
$string['error_'] = '';
$string['event_meeting_ended'] = 'انتهى الاجتماع ';
$string['event_meeting_hosted'] = 'تم استضافة الاجتماع ';
$string['event_meeting_joined'] = 'تم الانضمام للاجتماع ';
$string['event_meeting_started'] = 'بدأ الاجتماع ';
$string['event_recording_created'] = 'تم إنشاء التسجيل ';
$string['event_recording_deleted'] = 'تم حذف التسجيل ';
$string['event_recording_undeleted'] = 'تسجيل غير محذوف';
$string['event_recording_downloaded'] = 'تم تنزيل التسجيل ';
$string['event_recording_viewed'] = 'تم مشاهدة التسجيل ';
$string['externallinktext'] = '<p> هذا الرابط مخصص للمشاركين غير المسجلين في هذه الدورة التدريبية. لن يحتاج الطلاب في الدورة إلى إرسال هذا الرابط عبر البريد الإلكتروني ، حيث يمكنهم فقط النقر فوق الارتباط الانضمام إلى الاجتماع في الصفحة السابقة. يجب توزيع هذا الرابط بعناية - سيتمكن أي شخص لديه هذا الرابط من الوصول إلى هذا الاجتماع. لدعوة الآخرين إلى الاجتماع ، انسخ عنوان URL أدناه وأرسله إليهم. إذا كان هذا اجتماعًا عامًا ، فيمكن أيضًا وضع هذا الارتباط على موقع ويب. </ p> ';
$string['externalpassword'] = 'سيحتاج المشاركون أيضًا إلى معرفة كلمة مرور الاجتماع: <b> {$a} </b> ';
$string['getexternallink'] = '<a href"{$a->url} "> احصل على رابط مشارك خارجي </a> ';
$string['host'] = 'مضيف';
//$string['hostmeetinglink'] = '<a href"{$a-> url} "> الاجتماع المضيف </a> ';
$string['hostmeetinglink'] = '<a href="{$a->url}"><button class ="btn btn-primary">بدء الاجتماع</button></a>';
$string['hostschedulingexception'] = 'لا يمكن للمستخدم جدولة اجتماع لهذا المضيف. ';
$string['inprogress'] = 'في تَقَدم';
$string['invalidtype'] = 'نوع غير صالح ';
$string['joinmeetinglink'] = '<a href="{$a->url}">بدء البرنامج التدريبي</a>';
$string['longavailability'] = 'التوفر الممتد ';
$string['longavailability_help'] = 'سيؤدي تعيين هذا الخيار إلى ترك الاجتماع متاحًا للاستضافة حتى وقت انتهاء الإتاحة الموسعة. يسمح باجتماعات قابلة لإعادة الاستخدام لأشياء مثل ساعات العمل. ';
$string['manageallrecordings'] = 'إدارة جميع تسجيلات WebEx ';
$string['manageallrecordings_help'] = 'إدارة جميع التسجيلات من خادم WebEx ، وليس فقط التسجيلات التي تحتوي على نشاط Moodle. ';
$string['meetingclosegrace'] = 'فترة سماح الاجتماع ';
$string['meetingclosegrace_help'] = 'عدد الدقائق بعد وقت البدء بالإضافة إلى المدة التي سيُعتبر الاجتماع بعدها مكتملاً. ';
$string['meetingpassword'] = 'كلمة مرور الاجتماع ';
$string['meetingpast'] = 'هذا الاجتماع انتهى. ';
$string['meetingsettings'] = 'إعدادات الاجتماع ';
$string['meetingtemplate'] = 'قالب WebEx ';
$string['meetingtemplate_help'] = 'اسم النموذج الذي قمت بإعداده على حساب WebEx الخاص بك لنوع الاجتماع هذا. اتركه فارغا لاستخدام الإعدادات الافتراضية. ملاحظة: الإشارة إلى اسم نموذج غير موجود سيمنع إنشاء جلسات من نوع الاجتماع هذا. ';
$string['meetingtype'] = 'نوع الاجتماع ';
$string['meetingtypes'] = 'أنواع الاجتماعات';
$string['meetingtypes_desc'] = 'هذه هي أنواع اجتماعات WebEx التي تدعمها هذه الوحدة. لكل نوع ، يمكنك تحديد ما إذا كان "متاحًا" (لديك ترخيص له في WebEx ، وتريد أن يكون قادرًا على استخدامه من Moodle) ، وإذا كنت تريد أن يكون "متاحًا لجميع المستخدمين" . الأنواع "المتوفرة" ، ولكن ليست "متاحة لجميع المستخدمين" سيتم تحديدها فقط بواسطة الأشخاص الذين لديهم إذن mod / webexactivity: allavailabletypes. "كلمة مرور الاجتماع مطلوبة" هي إخبار المكون الإضافي إذا كان WebEx يتطلب كلمة مرور. استخدم "إنشاء كلمات المرور المطلوبة" أدناه للسماح للمستخدم بعدم تقديم كلمة مرور. ';
$string['meetingupcoming'] = 'هذا الاجتماع غير متاح بعد للانضمام. ';
$string['page_managerecordings'] = 'إدارة التسجيلات ';
$string['page_manageusers'] = 'ادارة المستخدمين';
$string['prefix'] = 'بادئة اسم المستخدم ';
$string['prefix_help'] = 'ستكون هذه السلسلة مسبوقة لجميع أسماء المستخدمين التي تم إنشاؤها بواسطة هذه الوحدة. ';
$string['recordingfileurl'] = 'تحميل';
$string['recordinglength'] = '({$a->time}, {$a->size})';
$string['recordingname'] = 'اسم التسجيل ';
$string['recordings'] = 'التسجيلات';
$string['recordingsettings'] = 'إعدادات التسجيلات ';
$string['recordingstreamurl'] = 'تشغيل';
$string['recordingtrashtime'] = 'تسجيل الوقت المُهمل';
$string['recordingtrashtime_help'] = 'عدد الساعات التي سيتم خلالها تعليق التسجيل قبل حذفه نهائيًا. ';
$string['requiremeetingpassword'] = 'طلب كلمات مرور الاجتماع ';
$string['requiremeetingpassword_help'] = 'مطالبة المستخدم بإدخال كلمة مرور الاجتماع. إذا لم يتم تحديده ، وتم تحديد نوع الاجتماع على أنه يتطلب كلمة مرور أعلاه ، فسيتم إنشاء كلمة مرور بشكل عشوائي. ';
$string['search:activity'] = 'اجتماع WebEx - معلومات النشاط ';
$string['settings'] = 'إعدادات اجتماع WebEx ';
$string['sitename'] = 'اسم الموقع';
$string['sitename_help'] = 'جزء عنوان url الموجود قبل .webex.com. إذا كان عنوان url لموقعك هو "https://example.webex.com" ، يمكنك إدخال "example" ';
$string['startssoon'] = 'سيبدأ قريبًا ';
$string['starttime'] = 'وقت البدء';
$string['stream'] = 'البث';
$string['studentdownload'] = 'السماح للمتدربين بتنزيل التسجيلات ';
$string['studentdownload_help'] = 'السماح للمتدربين بالوصول إلى رابط التنزيل للتسجيلات. ';
$string['studentvisible'] = 'مرئي للمتدربين ';
$string['task_deleterecordings'] = 'التخلص التسجيلات المحذوفة ';
$string['task_updateallrecordings'] = 'تحديث كافة التسجيلات ';
$string['task_updatemediumrecordings'] = 'تحديث متوسط التسجيلات السابقة ';
$string['task_updateopensessions'] = 'تحديث الجلسات المفتوحة ';
$string['task_updaterecentrecordings'] = 'تحديث التسجيلات السابقة الأخيرة ';
$string['typeinstalled'] = 'متوفر';
$string['typeforall'] = 'متاح لجميع المستخدمين ';
$string['typemeetingcenter'] = 'مركز الاجتماعات ';
$string['typemeetingcenter_desc'] = '';
$string['typepwreq'] = 'كلمة مرور الاجتماع مطلوبة ';
$string['typetrainingcenter'] = 'مركز تدريب';
$string['typetrainingcenter_desc'] = '';
$string['undeletelink'] = '<a href"{$a->url} "> إلغاء الحذف </a> ';
$string['unknownhostwebexidexception'] = 'معرف مضيف WebEx غير موجود ';
$string['user_create_exception'] = 'لمزيد من المعلومات حول هذا الخطأ ، راجع <a href= "https://github.com/merrill-oakland/moodle-mod_webexactivity/wiki/Manual#webex-exception-xxxxxxx-when-creating-new-user" target ="_blank" > هذه الصفحة </a>. ';
$string['usereditauto'] = 'مستخدم WebEx مُدار داخليًا ، ولا يمكن تحريره. ';
$string['usereditbad'] = 'لا يمكنك الوصول إلى هذه الصفحة. ';
$string['usereditunabletoload'] = 'تعذر تحميل المستخدم الخاص بك من WebEx. ';
$string['userexistsexplanation'] = 'عنوان بريدك الإلكتروني ({$a->email}) مستخدم بالفعل بواسطة مستخدم WebEx <b> {$a->username} </b>. الرجاء إدخال كلمة مرور WebEx أدناه. ';
$string['webexactivity:addinstance'] = 'إضافة اجتماع WebEx جديد ';
$string['webexactivity:allavailabletypes'] = 'إنشاء اجتماعات من جميع الأنواع المثبتة ';
$string['webexactivity:hostmeeting'] = 'استضافة وإدارة اجتماع WebEx ';
$string['webexactivity:reports'] = 'استخدام تقارير WebEx ';
$string['webexactivity:view'] = 'عرض اجتماع WebEx ';
$string['webexid'] = 'معرف WebEx ';
$string['webexrecordings'] = 'تسجيلات WebEx ';
$string['webexusercollision'] = 'هناك تعارض مع مستخدم WebEx الحالي. ';
$string['webexxmlexception'] = 'حدث خطأ في WebEx أثناء معالجة XML: {$a->errorcode} {$a->error} ';


// Privacy.

$string['privacy:metadata:username'] = 'اسم المستخدم الخاص بالمستخدم الذي يستضيف الاجتماع. ';
$string['privacy:metadata:webexpassword'] = 'كلمة مرور Webex المستخدمة للتفاوض نيابة عن المستخدم. يُستخدم فقط في المواقع التي تحتوي على تثبيتات قديمة جدًا لهذا المكون الإضافي. ';
$string['privacy:metadata:firstname'] = 'الاسم الأول للمستخدم الذي يقوم بالوصول إلى Webex ';
$string['privacy:metadata:lastname'] = 'الاسم الأخير للمستخدم الذي يصل إلى Webex ';
$string['privacy:metadata:email'] = 'عنوان البريد الإلكتروني للمستخدم الذي يدخل إلى Webex ';
$string['privacy:metadata:webexhost'] = 'معلومات المضيف WebEx ';
$string['privacy:metadata:webexparticipant'] = 'معلومات المضيف WebEx ';
$string['privacy:metadata:webexactivity'] = 'معلومات المشاركين في Webex ';
$string['privacy:metadata:webexactivity:hostwebexid'] = 'معرف مستخدم Webex للمستخدم المضيف ';
$string['privacy:metadata:webexactivity:password'] = 'كلمة مرور الاجتماع ';
$string['privacy:metadata:timemodified'] = 'الوقت الذي تم فيه تعديل السجل في قاعدة البيانات ';
$string['privacy:metadata:timecreated'] = 'الوقت الذي تم فيه إنشاء السجل ';
$string['privacy:metadata:webexactivity_user'] = 'مستخدمي استضافة Webex ';
$string['privacy:metadata:webexactivity_user:webexuserid'] = 'معرف مستخدم Webex لمستخدم Webex ';
$string['privacy:metadata:webexactivity_user:webexid'] = 'اسم مستخدم Webex لمستخدم Webex ';
$string['privacy:metadata:webexactivity_recording'] = 'تسجيل Webex ';
$string['privacy:metadata:webexactivity_recording:hostid'] = 'اسم مستخدم المضيف للتسجيل';
$string['minutes'] = 'دقائق';
$string['starttp'] = 'بدء البرنامج التدريبي';
$string['startevent'] = 'بدء الفعالية';
