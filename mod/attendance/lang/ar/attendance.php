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
 * Strings for component 'attendance', language 'en'
 *
 * @package   mod_attendance
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['Aacronym'] = 'غ';
$string['Afull'] = 'غائب';
$string['Eacronym'] = 'ع';
$string['Efull'] = 'عذر';
$string['Lacronym'] = 'م';
$string['Lfull'] = 'متأخر';
$string['Pacronym'] = 'ح';
$string['Pfull'] = 'حاضر';
$string['absenteereport'] = 'تقرير الغائب';
$string['acronym'] = 'اختصار';
$string['add'] = 'إضافة';
$string['addedrecip'] = 'تمت إضافة {a$} مستلم جديد  ';
$string['addedrecips'] = 'تمت إضافة {a$} من المستلمين الجدد';
$string['addmultiplesessions'] = 'جلسات متعددة';
$string['addsession'] = 'إضافة جلسة';
$string['adduser'] = 'إضافة مستخدم';
$string['addwarning'] = 'إضافة تحذير';
$string['all'] = 'الكل';
$string['allcourses'] = 'جميع الدورات';
$string['allpast'] = 'كل السابق';
$string['allsessions'] = 'جميع الجلسات';
$string['allsessionstotals'] = 'إجمالي الجلسات المختارة';
$string['attendance:addinstance'] = 'إضافة نشاط حضور جديد';
$string['attendance:canbelisted'] = 'يظهر في القائمة';
$string['attendance:changeattendances'] = 'تغيير الحضور';
$string['attendance:changepreferences'] = 'تغيير التفضيلات';
$string['attendance:export'] = 'تصدير التقارير';
$string['attendance:manageattendances'] = 'إدارة الحضور';
$string['attendance:managetemporaryusers'] = 'إدارة المستخدمين المؤقتين';
$string['attendance:takeattendances'] = 'أخذ الحضور';
$string['attendance:view'] = 'مشاهدة الحضور';
$string['attendance:viewreports'] = 'عرض التقارير';
$string['attendance:viewsummaryreports'] = 'عرض تقارير ملخص الدورة';
$string['attendance:warningemails'] = 'يمكن الاشتراك في رسائل البريد الإلكتروني مع المستخدمين الغائبين';
$string['attendance_already_submitted'] = 'تم تعيين حضورك بالفعل.';
$string['attendance_no_status'] = 'لا توجد حالة صالحة متاحة - قد تكون متأخرا جدا لتسجيل الحضور.';
$string['attendancedata'] = 'بيانات الحضور';
$string['attendancefile'] = 'ملف الحضور (تنسيق csv)';
$string['attendancefile_help'] = 'يجب أن يكون الملف عبارة عن ملف CSV به صف رئيسي وحقول لتحديد المستخدم وتسجيل وقت الحضور على سبيل المثال (البريد الإلكتروني ، وقت ضئيل) أو (اسم المستخدم ، الوقت)';
$string['attendanceforthecourse'] = 'الحضور في البرنامج';
$string['attendancegrade'] = 'درجة الحضور';
$string['attendancenotset'] = 'يجب أن تحدد حضورك';
$string['attendancenotstarted'] = 'لم يبدأ الحضور بعد لهذه الدورة';
$string['attendancepercent'] = 'نسبة الحضور';
$string['attendancereport'] = 'تقرير الحضور';
$string['attendanceslogged'] = 'تسجيل الحضور';
$string['attendancestaken'] = 'تم تسجيل الحضور';
$string['attendancesuccess'] = 'تم الحضور بنجاح';
$string['attendanceupdated'] = 'تم تحديث الحضور بنجاح';
$string['attforblockdirstillexists'] = 'لا يزال دليل mod / attforblock القديم موجودًا - يجب حذف هذا الدليل على الخادم الخاص بك قبل تشغيل هذه الترقية.';
$string['attrecords'] = 'سجلات الحضور';
$string['autoassignstatus'] = 'حدد تلقائيًا أعلى حالة متاحة';
$string['autoassignstatus_help'] = 'إذا تم تحديد ذلك ، فسيتم تلقائيًا تعيين أعلى تقدير متاح للطلاب.';
$string['automark'] = 'وضع العلامات التلقائية';
$string['automarkuseempty'] = 'التعامل مع توافر حالة العلامة التلقائية';
$string['automarkuseempty_desc'] = 'إذا تم تحديدها ، فسيتم السماح بعناصر الحالة التي تحتوي على إعداد "متاح لـ" فارغ / غير محدد أثناء وضع العلامات التلقائي';
$string['selectactivity'] = 'حدد النشاط';
$string['automark_help'] = 'Allows marking to be completed automatically.
If "Yes" students will be automatically marked depending on their first access to the course.
If "Set unmarked at end of session" any students who have not marked their attendance will be set to the unmarked status selected.';

$string['automarkall'] = 'نعم';
$string['automarkclose'] = 'تعيين غير مميز في نهاية الجلسة';
$string['onactivitycompletion'] = 'عند اكتمال النشاط ';
$string['automarktask'] = 'تحقق من جلسات الحضور التي تتطلب وضع علامة تلقائية';
$string['autorecorded'] = 'نظام تسجيل تلقائي';
$string['averageattendance'] = 'متوسط ​​الحضور؛';
$string['averageattendancegraded'] = 'متوسط ​​الحضور';
$string['backtoparticipants'] = 'العودة إلى قائمة المشاركين';
$string['below'] = 'أقل من {a$}٪';
$string['calclose'] = 'إغلاق';
$string['calendarevent'] = 'إنشاء حدث في التقويم للجلسة';
$string['calendarevent_help'] = 'If enabled, a calendar event will be created for this session.
If disabled, any existing calendar event for this session will be deleted.';
$string['caleventcreated'] = 'تم إنشاء حدث التقويم للجلسة بنجاح';
$string['caleventdeleted'] = 'Calendar event for session successfully deleted';
$string['calmonths'] = 'يناير ، فبراير ، مارس ، أبريل ، مايو ، يونيو ، يوليو ، أغسطس ، سبتمبر ، أكتوبر ، نوفمبر ، ديسمبر';
$string['calshow'] = 'اختر موعدا';
$string['caltoday'] = 'اليوم';
$string['calweekdays'] = 'الأحد، الاثنين، الثلاثاء، الأربعاء، الخميس، الجمعة، السبت';
$string['cannottakeforgroup'] = 'لا يمكنك أخذ الحضور للمجموعة "{a$}"';
$string['cantaddstatus'] = 'يجب عليك تعيين اختصار ووصف عند إضافة حالة جديدة.';
$string['categoryreport'] = 'تقرير فئة البرنامج';
$string['changeattendance'] = 'تغيير الحضور';
$string['changeduration'] = 'تغيير المدة';
$string['changesession'] = 'تغيير الجلسة';
$string['checkweekdays'] = 'تحديد أيام الأسبوع التي تقع ضمن نطاق تاريخ الجلسة المحدد.';
$string['closed'] = 'هذه الجلسة غير متاحة حاليًا لوضع علامة ذاتية';
$string['column'] = 'العمود';
$string['columnmap'] = 'تعيين العمود';
$string['columnmap_help'] = 'لكل حقل من الحقول المعروضة ، حدد العمود المقابل في ملف csv.';
$string['columns'] = 'أعمدة';
$string['commonsession'] = 'كل المتدربين';
$string['commonsessions'] = 'كل المتدربين';
$string['confirm'] = 'تأكيد';
$string['confirmcolumnmappings'] = 'تأكيد تعيينات الأعمدة';
$string['confirmdeletehiddensessions'] = 'هل أنت متأكد أنك تريد حذف جلسات {a->count$} المجدولة قبل تاريخ بدء الدورة ({$a-> date})؟';
$string['confirmdeleteuser'] = "هل أنت متأكد أنك تريد حذف المستخدم \'{$a->fullname}\'' ({$a-> email})؟ <br/> سيتم حذف جميع سجلات الحضور الخاصة به بشكل دائم.";
$string['copyfrom'] = 'نسخ بيانات الحضور من';
$string['countofselected'] = 'عدد المحدد';
$string['course'] = 'برنامج';
$string['coursemessage'] = 'مراسلة مستخدمي البرنامج التدريبي';
$string['courseshortname'] = 'الاسم المختصر للبرنامج';
$string['coursesummary'] = 'تقرير ملخص البرنامج';
$string['createmultiplesessions'] = 'إنشاء جلسات متعددة';
$string['createmultiplesessions_help'] = 'تتيح لك هذه الوظيفة إنشاء جلسات متعددة في خطوة واحدة بسيطة. تبدأ الجلسات في تاريخ الجلسة الأساسية وتستمر حتى تاريخ \'التكرار حتى \'. التكرار حتى: حدد أيام الأسبوع الذي سيجتمع فيه فصلك الدراسي (على سبيل المثال ، الاثنين / الأربعاء / الجمعة). *التكرار كل : يسمح هذا بإعداد التكرار. إذا كان فصلك سيجتمع كل أسبوع ، فحدد 1 ؛ إذا كان سيجتمع مرة كل أسبوعين ، اختر 2 ؛ كل 3 أسبوع ، حدد 3 ، وما إلى ذلك. * كرر حتى : حدد آخر يوم في الفصل (آخر يوم تريد حضوره).';
$string['createonesession'] = 'إنشاء جلسة واحدة للبرنامج';
$string['csvdelimiter'] = 'محدد CSV';
$string['currentlyselectedusers'] = 'المستخدمين المختارين حاليا';
$string['customexportfields'] = 'تصدير حقول ملف تعريف المستخدم المخصص';
$string['customexportfields_help'] = 'حقول ملف تعريف المستخدم المخصصة الإضافية لعرضها في تقرير التصدير.';
$string['date'] = 'التاريخ';
$string['days'] = 'أيام';
$string['defaultdisplaymode'] = 'وضع العرض الافتراضي ';
$string['defaults'] = 'الإعدادات الافتراضية';
$string['defaultsessionsettings'] = 'الإعدادات الافتراضية للجلسة';
$string['defaultsessionsettings_help'] = 'تحدد هذه الإعدادات، الإعدادات الافتراضية لجميع الجلسات الجديدة';
$string['defaultsettings'] = 'إعدادات الحضور الافتراضية';
$string['defaultsettings_help'] = 'تحدد هذه الإعدادات، الإعدادات الافتراضية لجميع الحضور الجديد';
$string['defaultstatus'] = 'مجموعة الحالة الافتراضية';
$string['defaultsubnet'] = 'عنوان الشبكة الافتراضي';
$string['defaultsubnet_help'] = 'قد يقتصر تسجيل الحضور على شبكات فرعية معينة عن طريق تحديد قائمة مفصولة بفواصل لعناوين IP الجزئية أو الكاملة. هذه هي القيمة الافتراضية المستخدمة عند إنشاء جلسات جديدة.';
$string['defaultview'] = 'العرض الافتراضي عند تسجيل الدخول';
$string['defaultview_desc'] = 'هذا هو العرض الافتراضي الذي يظهر للمدربين عند تسجيل الدخول لأول مرة.';
$string['defaultwarnings'] = 'مجموعة التحذير الافتراضية';
$string['defaultwarningsettings'] = 'إعدادات التحذير الافتراضية';
$string['defaultwarningsettings_help'] = 'تحدد هذه الإعدادات، الإعدادات الافتراضية لجميع التحذيرات الجديدة ';
$string['delete'] = 'حذف';
$string['deletecheckfull'] = 'هل أنت متأكد تمامًا من رغبتك في حذف {a$} تمامًا ، بما في ذلك جميع بيانات المستخدم؟';
$string['deletedgroup'] = 'تم حذف المجموعة المرتبطة بهذه الجلسة';
$string['deletehiddensessions'] = 'حذف كافة الجلسات المخفية';
$string['deletelogs'] = 'حذف بيانات الحضور';
$string['deleteselected'] = 'حذف المختار';
$string['deletesession'] = 'حذف الجلسة';
$string['deletesessions'] = 'حذف كافة الجلسات';
$string['deleteuser'] = 'حذف المستخدم';
$string['deletewarningconfirm'] = 'هل أنت متأكد أنك تريد حذف هذا التحذير؟';
$string['deletingsession'] = 'حذف جلسة البرنامج';
$string['deletingstatus'] = 'حذف حالة البرنامج ';
$string['description'] = 'الوصف';
$string['display'] = 'عرض';
$string['displaymode'] = 'وضع العرض';
$string['donotusepaging'] = 'لا تستخدم المناداة';
$string['downloadexcel'] = 'تحميل بتنسيق Excel ';
$string['downloadooo'] = 'تحميل بتنسيق OpenOffice';
$string['downloadtext'] = 'تحميل بتنسيق نصي';
$string['duration'] = 'المدة';
$string['editsession'] = 'تحرير الجلسة';
$string['edituser'] = 'تحرير المستخدم';
$string['emailcontent'] = 'محتوى البريد الإلكتروني ';
$string['emailcontent_default'] = 'مرحبًا ٪userfirstname٪ ،
لقد انخفض حضورك في ٪coursename٪٪ attancename٪ إلى أقل من٪warningpercent٪ وهو حاليًا٪percent٪ - نأمل أن تكون بخير!

لتحقيق أقصى استفادة من هذا البرنامج ، يجب تحسين حضورك ، يرجى الاتصال إذا كنت بحاجة إلى أي دعم إضافي.';
$string['emailcontent_help'] = 'عندما يتم إرسال تحذير إلى أحد المتدربين ، فإنه يأخذ محتوى البريد الإلكتروني من هذا الحقل. يمكن استخدام أحرف البدل التالية: <ul> <li>٪ coursename٪ </li> <li>٪ userfirstname٪ </li> <li>٪ userlastname٪ </li> <li>٪ userid٪ </li> <li>٪ warningpercent٪ </li>';
$string['emailsubject'] = 'موضوع البريد الإلكتروني';
$string['emailsubject_default'] = 'تحذير الحضور';
$string['emailsubject_help'] = 'عندما يتم إرسال تحذير للمتدرب ، فإنه يأخذ موضوع البريد الإلكتروني من هذا الحقل.';
$string['emailuser'] = 'مستخدم البريد الإلكتروني';
$string['emailuser_help'] = 'إذا تم تحديده ، فسيتم إرسال تحذير إلى المتدرب.';
$string['emptyacronym'] = 'غير مسموح بالاختصارات الفارغة. لم يتم تحديث سجل الحالة.';
$string['emptydescription'] = 'الوصف الفارغ غير مسموح به. لم يتم تحديث سجل الحالة.';
$string['enablecalendar'] = 'إنشاء أحداث التقويم';
$string['enablecalendar_desc'] = 'في حالة التمكين ، سيتم إنشاء حدث تقويم لكل جلسة حضور. بعد تغيير هذا الإعداد ، يجب عليك تشغيل تقرير إعادة تعيين التقويم.';
$string['enablewarnings'] = 'تمكين التحذيرات';
$string['enablewarnings_desc'] = 'يسمح هذا بتحديد مجموعة تحذير لإشعارات الحضور والبريد الإلكتروني للمستخدمين عندما ينخفض ​​الحضور إلى ما دون الحد الذي تم تكوينه. <br/> <strong> تحذير: هذه ميزة جديدة ولم يتم اختبارها على نطاق واسع. الرجاء الاستخدام على مسؤوليتك الخاصة وتقديم ملاحظات في منتديات موودل إذا وجدت أنها تعمل بشكل جيد. </ strong>';
$string['encoding'] = 'التشفير';
$string['encoding_help'] = 'يشير هذا إلى نوع تشفير الباركود المستخدم في بطاقة هوية المتدربين. تتضمن الأنواع النموذجية لأنظمة تشفير الباركود Code-39 و Code-128 و UPC-A.';
$string['endofperiod'] = 'نهاية الفترة';
$string['endtime'] = 'وقت انتهاء الجلسة';
$string['enrolmentend'] = 'ينتهي تسجيل المستخدم {a$}';
$string['enrolmentstart'] = 'يبدأ تسجيل المستخدم {a$}';
$string['enrolmentsuspended'] = 'تم تعليق التسجيل';
$string['enterpassword'] = 'أدخل كلمة المرور';
$string['error:coursehasnoattendance'] = 'الدورة ذات الاسم المختصر {a$} ليس بها أنشطة حضور.';
$string['error:coursenotfound'] = 'لا يمكن العثور على دورة بالاسم المختصر {a$}.';
$string['error:qrcode'] = 'يجب تمكين السماح للمتدربين بتسجيل الحضور الخاص بهم لاستخدام رمز الاستجابة السريعة! التخطي.';
$string['error:sessioncourseinvalid'] = 'دورة الجلسة غير صالحة! التخطي.';
$string['error:sessiondateinvalid'] = 'تاريخ الجلسة غير صالح! التخطي.';
$string['error:sessionendinvalid'] = 'وقت انتهاء الجلسة غير صالح! التخطي.';
$string['error:sessionstartinvalid'] = 'وقت بدء الجلسة غير صالح! التخطي.';
$string['error:statusnotfound'] = 'المستخدم: {a-> extuser} له قيمة حالة لا يمكن العثور عليها: {$ a-> status}';
$string['error:timenotreadable'] = 'المستخدم: {a-> extuser$} لديه وقت ضئيل لا يمكن تحويله بواسطة strtotime: {$a-> scantime}';
$string['error:userduplicate'] = 'تم العثور على المستخدم {a$} مرتين في الاستيراد. الرجاء تضمين سجل واحد فقط لكل مستخدم.';
$string['error:usernotfound'] = 'مستخدم مع {a-> userfield$} تم ضبطه على {a-> extuser$} لا يمكن العثور عليه';
$string['errorgroupsnotselected'] = 'حدد مجموعة واحدة أو أكثر';
$string['errorinaddingsession'] = 'خطأ في إضافة جلسة ';
$string['erroringeneratingsessions'] = 'خطأ في إنشاء الجلسات.';
$string['eventdurationupdated'] = 'تم تحديث مدة الجلسة ';
$string['eventreportviewed'] = 'عرض تقرير الحضور';
$string['eventscreated'] = 'أحداث التقويم التي تم إنشاؤها';
$string['eventsdeleted'] = 'أحداث التقويم محذوفة';
$string['eventsessionadded'] = 'تمت إضافة الجلسة';
$string['eventsessiondeleted'] = 'تم حذف الجلسة';
$string['eventsessionipshared'] = 'تعارض IP الخاص بالحضور';
$string['eventsessionsimported'] = 'تم استيراد الجلسات';
$string['eventsessionupdated'] = 'تم تحديث الجلسة';
$string['eventstatusadded'] = 'تمت إضافة الحالة';
$string['eventstatusupdated'] = 'تم تحديث الحالة';
$string['eventstudentattendancesessionsviewed'] = 'تم عرض تقرير الجلسة';
$string['eventstudentattendancesessionsupdated'] = 'تم تحديث تقرير الجلسة';
$string['eventtaken'] = 'تم أخذ الحضور';
$string['eventtakenbystudent'] = 'الحضور اتخذ من قبل المتدرب؛';
$string['export'] = 'تصدير';
$string['extrarestrictions'] = 'قيود اضافية';
$string['formattexttype'] = 'تنسيق';
$string['from'] = 'من:';
$string['gradebookexplanation'] = 'التقدير في دفتر التقديرات';
$string['gradebookexplanation_help'] = 'تعرض وحدة الحضور درجة الحضور الحالية الخاصة بك بناءً على عدد النقاط التي حصلت عليها حتى الآن وعدد النقاط التي كان من الممكن كسبها حتى الآن لا يشمل فترات الدراسة في المستقبل. في دفتر التقديرات ، يعتمد تقدير الحضور الخاص بك على نسبة الحضور الحالية وعدد النقاط التي يمكن الحصول عليها خلال مدة الدورة التدريبية بالكامل ، بما في ذلك فترات الفصل الدراسي المستقبلية. على هذا النحو ، قد لا تكون درجات الحضور الخاصة بك المعروضة في وحدة الحضور وفي دفتر التقديرات هي نفس عدد النقاط ولكنها نفس النسبة المئوية ، على سبيل المثال ، إذا كنت قد حصلت على 8 من 10 نقاط حتى الآن (80٪ حضور) والحضور بالنسبة لكامل الدورة التدريبية 50 نقطة ، ستعرض وحدة الحضور 8/10 وسيعرض دفتر العلامات 40/50. لم تكسب حتى الآن 40 نقطة ولكن 40 هي قيمة النقطة المكافئة لنسبة الحضور الحالية البالغة 80٪. لا يمكن أن تنخفض قيمة النقاط التي كسبتها في وحدة الحضور أبدًا ، لأنها تعتمد فقط على الحضور حتى الآن ومع ذلك ، فإن قيمة نقطة الحضور الموضحة في دفتر التقديرات قد تزيد أو تنقص اعتمادًا على حضورك المستقبلي ، حيث إنها تعتمد على الحضور في الدورة التدريبية بأكملها.';
$string['graded'] = 'الجلسات المصنفة ';
$string['gridcolumns'] = 'أعمدة الشبكة';
$string['group'] = 'مجموعة';
$string['groups'] = 'مجموعات';
$string['groupsession'] = 'مجموعة من المتدربين';
$string['groupsessionsby'] = 'تجميع الجلسات حسب';
$string['hiddensessions'] = 'الجلسات المخفية';
$string['hiddensessions_help'] = 'تكون الجلسات مخفية إذا تمت جدولتها قبل تاريخ بدء الدورة ، ويمكنك استخدام هذه الميزة لإخفاء الجلسات القديمة بدلاً من حذفها. ستظهر الجلسات المرئية فقط في دفتر التقديرات.';
$string['hiddensessionsdeleted'] = 'تم حذف "جميع الجلسات المخفية"';
$string['hideextrauserdetails'] = 'إخفاء تفاصيل المستخدم الإضافية';
$string['hidensessiondetails'] = 'إخفاء تفاصيل الجلسة';
$string['identifyby'] = 'تحديد الطالب من خلال';
$string['import'] = 'استيراد';
$string['importfile'] = 'استيراد ملف';
$string['importfile_help'] = 'استيراد ملف';
$string['importsessions'] = 'استيراد الجلسات';
$string['importstatus'] = 'حقل الحالة';
$string['importstatus_help'] = 'يسمح هذا بإدراج قيمة الحالة في الاستيراد - مثل قيم مثل ح أو م أو غ ';
$string['includeabsentee'] = 'تضمين جلسة عند حساب تقرير الغائب ';
$string['includeabsentee_help'] = 'إذا تم تحديد هذه الجلسة فسيتم تضمينها في حسابات تقرير الغائب.';
$string['includeall'] = 'حدد جميع الجلسات';
$string['includedescription'] = 'تضمين وصف الجلسة';
$string['includenottaken'] = 'تشمل الجلسات التي لم يتم أخذها';
$string['includeqrcode'] = 'تضمين رمز QR"';
$string['includeremarks'] = 'تضمين الملاحظات';
$string['incorrectpassword'] = 'لقد أدخلت كلمة مرور غير صحيحة ولم يتم تسجيل حضورك ، يرجى إدخال كلمة المرور الصحيحة.';
$string['incorrectpasswordshort'] = 'كلمة مرور غير صحيحة ، الحضور غير مسجل.';
$string['indetail'] = 'بالتفصيل...';
$string['indicator:cognitivedepth'] = 'الحضور المعرفي';
$string['indicator:cognitivedepth_help'] = 'يعتمد هذا المؤشر على العمق المعرفي الذي وصل إليه المتدرب في نشاط الحضور.';
$string['indicator:cognitivedepthdef'] = 'الحضور المعرفي';
$string['indicator:cognitivedepthdef_help'] = 'وصل المشارك إلى هذه النسبة المئوية من المشاركة المعرفية التي قدمها الحضور خلال فترة التحليل هذه (المستويات';
$string['indicator:cognitivedepthdef_link'] = 'Learning_analytics_indicators#Cognitive_depth';
$string['indicator:socialbreadth'] = 'الحضور الاجتماعي ';
$string['indicator:socialbreadth_help'] = 'يعتمد هذا المؤشر على النطاق الاجتماعي الذي وصل إليه الطالب في نشاط الحضور.';
$string['indicator:socialbreadthdef'] = 'الحضور الاجتماعي';
$string['indicator:socialbreadthdef_help'] = 'The participant has reached this percentage of the social engagement offered by the Attendance during this analysis interval (Levels = No participation, Participant alone)';
$string['indicator:socialbreadthdef_link'] = 'Learning_analytics_indicators#Social_breadth';
$string['invalidaction'] = 'يجب عليك تحديد إجراء ';
$string['invalidemails'] = 'يجب تحديد عناوين حسابات المستخدمين الموجودة ، لا يمكن العثور على: {$ a}';
$string['invalidimportfile'] = 'تنسيق الملف غير صالح.';
$string['invalidsessionenddate'] = 'لا يمكن أن يكون هذا التاريخ قبل تاريخ الجلسة';
$string['invalidsessionendtime'] = 'يجب أن يكون وقت الانتهاء أكبر من وقت البدء';
$string['invalidstatus'] = 'لقد اخترت حالة غير صالحة ، يرجى المحاولة مرة أخرى';
$string['iptimemissing'] = 'دقائق غير صالحة لتحرير';
$string['jumpto'] = 'الانتقال إلى';
$string['keepsearching'] = 'استمر في البحث ';
$string['marksessionimportcsvhelp'] = 'يتيح لك هذا النموذج تحميل ملف csv يحتوي على معرف مستخدم وحالة - يمكن أن يكون حقل الحالة هو اختصار الحالة أو الوقت الذي تم فيه تسجيل الحضور لهذا المستخدم. إذا تم تمرير قيمة الوقت ، فسيحاول تعيين قيمة الحالة مع أعلى درجة متاحة في ذلك الوقت. ';
$string['maxpossible'] = 'أقصى حد ممكن ';
$string['maxpossible_help'] = 'تعرض النتيجة التي يمكن لكل مستخدم الوصول إليها إذا حصل على الحد الأقصى من النقاط في كل جلسة لم يتم أخذها بعد (في الماضي والمستقبل): <ul>
    <li> <strong> النقاط </ strong>: الحد الأقصى من النقاط التي يمكن لكل مستخدم الوصول إليها في جميع الجلسات. </ li>
    <li> <strong> النسبة المئوية </ strong>: الحد الأقصى للنسبة المئوية التي يمكن لكل مستخدم الوصول إليها في جميع الجلسات. </ li>
    </ul> ';
$string['maxpossiblepercentage'] = 'أقصى نسبة ممكنة ';
$string['maxpossiblepoints'] = 'الحد الأقصى من النقاط الممكنة ';
$string['maxwarn'] = 'العدد الأقصى من تحذيرات البريد الإلكتروني';
$string['maxwarn_help'] = 'الحد الأقصى لعدد مرات إرسال تحذير (يتم إرسال تحذير واحد فقط لكل جلسة) ';
$string['mergeuser'] = 'دمج المستخدم ';
$string['mobilesessionfrom'] = 'إظهار الجلسات من الأقدم من الأحدث ';
$string['mobilesessionfrom_help'] = 'يسمح بتقييد قائمة الجلسات عند وضع العلامات في التطبيق - يعرض فقط الجلسات التي بدأت منذ هذه القيمة ';
$string['mobilesessionto'] = 'إظهار الجلسات المستقبلية ';
$string['mobilesessionto_help'] = 'يسمح لقائمة الجلسات التي سيتم تقييدها لإظهار عدد صغير فقط من الجلسات المستقبلية. ';
$string['mobilesettings'] = 'إعدادات تطبيقات الجوال ';
$string['mobilesettings_help'] = 'تتحكم هذه الإعدادات في سلوك تطبيق الجوال Moodle ';
$string['modulename'] = 'حضور';
$string['modulename_help'] = 'تمكّن وحدة نشاط الحضور المدرب من أخذ الحضور أثناء الفصل والمتدربين لعرض سجل الحضور الخاص بهم. يمكن للمدرب إنشاء جلسات متعددة ويمكنه وضع علامة على حالة الحضور على أنها "حاضر" أو "غائب" أو "متأخر" أو "معذور" أو تعديل الحالات لتناسب احتياجاتهم. التقارير متاحة للفصل بأكمله أو متدرب بمفرده. ';
$string['modulenameplural'] = 'الحضور ';
$string['months'] = 'شهور';
$string['moreattendance'] = 'تم الحضور بنجاح لهذه الصفحة ';
$string['moveleft'] = 'تحرك يسارا';
$string['moveright'] = 'تحرك يمينا';
$string['multisessionexpanded'] = 'جلسات متعددة موسعة ';
$string['multisessionexpanded_desc'] = 'إظهار إعدادات "جلسات متعددة" موسعة بشكل افتراضي عند إنشاء جلسات جديدة. ';
$string['mustselectusers'] = 'يجب تحديد المستخدمين للتصدير ';
$string['newdate'] = 'موعد جديد';
$string['newduration'] = 'مدة جديدة ';
$string['newstatusset'] = 'مجموعة جديدة من الحالات ';
$string['noabsentstatusset'] = 'الحالة المعينة قيد الاستخدام ليس لها حالة لاستخدامها عند عدم تمييزها. ';
$string['noattendanceusers'] = 'لا يمكن تصدير أي بيانات لعدم وجود متدربين مسجلين في البرنامج. ';
$string['noattforuser'] = 'لا توجد سجلات حضور للمستخدم ';
$string['noautomark'] = 'Disabled';
$string['nocapabilitytotakethisattendance'] = 'لقد حاولت تغيير حضور جلسة باستخدام cmid: {$a} ليس لديك إذن بتعديله. ';
$string['nodescription'] = 'جلسة صف عادية ';
$string['noeventstoreset'] = 'لا توجد أحداث تقويم تتطلب تحديثًا. ';
$string['nogroups'] = 'تم تعيين هذا النشاط لاستخدام المجموعات ، ولكن لا توجد مجموعات في البرنامج. ';
$string['noguest'] = 'لا يمكن للضيف رؤية الحضور ';
$string['noofdaysabsent'] = 'عدد الأيام الغياب ';
$string['noofdaysexcused'] = 'عدد الأيام الاعتذار ';
$string['noofdayslate'] = 'عدد الأيام التأخير ';
$string['noofdayspresent'] = 'عدد الأيام الحضور ';
$string['nosessiondayselected'] = 'لم يتم تحديد يوم الجلسة ';
$string['nosessionexists'] = 'لا توجد جلسة لهذه الدورة ';
$string['nosessionsselected'] = 'لم يتم تحديد جلسات ';
$string['notfound'] = 'لم يتم العثور على نشاط حضور في هذه الدورة! ';
$string['notifytask'] = 'إرسال تحذيرات للمستخدمين ';
$string['notmember'] = 'ليس عضو ';
$string['notset'] = 'غير مضبوط';
$string['noupgradefromthisversion'] = 'لا يمكن ترقية وحدة الحضور من إصدار attforblock الذي قمت بتثبيته. - يرجى حذف attforblock أو ترقيته إلى أحدث إصدار قبل عدم استدعاء وحدة الحضور الجديدة ';
$string['numsessions'] = 'عدد الجلسات ';
$string['olddate'] = 'تاريخ قديم ';
$string['onlyselectedusers'] = 'تصدير مستخدمين محددين ';
$string['overallsessions'] = 'خلال جميع الجلسات ';
$string['overallsessions_help'] = 'عرض إحصائيات لجميع الجلسات بما في ذلك تلك التي لم تحضر بعد (الماضي والمستقبل): <ul>
    <li> <strong> الجلسات </ strong>: إجمالي عدد الجلسات. </ li>
    <li> <strong> النقاط </ strong>: النقاط الممنوحة بناءً على الجلسات التي تم إجراؤها. </ li>
    <li> <strong> النسبة المئوية </ strong>: النسبة المئوية للنقاط الممنوحة على الحد الأقصى للنقاط الممكنة لجميع الجلسات. </ li>
    </ul> ';
$string['oversessionstaken'] = 'الجلسات التي تم الإنتهاء منها ';
$string['oversessionstaken_help'] = 'عرض إحصائيات للجلسات التي تم فيها الحضور::
    <ul>

    <li> <strong> الجلسات </ strong>: عدد الجلسات التي تم إجراؤها بالفعل. </ li>

    <li> <strong> النقاط </ strong>: النقاط الممنوحة بناءً على الجلسات التي تم إجراؤها. </ li>

    <li> <strong> النسبة المئوية </ strong>: النسبة المئوية للنقاط الممنوحة على الحد الأقصى للنقاط الممكنة للجلسات التي تم أخذها. </ li>

    </ul> ';
$string['pageof'] = 'الصفحة {a-> page$} من {a-> numpages$} ';
$string['participant'] = 'مشارك';
$string['password'] = 'كلمة المرور';
$string['passwordgrp'] = 'كلمة مرور المتدرب ';
$string['passwordgrp_help'] = 'إذا تم تعيين المتدربين ، فسيُطلب منهم إدخال كلمة المرور هذه قبل أن يتمكنوا من تعيين حالة الحضور الخاصة بهم للجلسة. إذا كانت فارغة ، فلا يلزم إدخال كلمة مرور. ';
$string['passwordrequired'] = 'يجب عليك إدخال كلمة مرور الجلسة قبل أن تتمكن من تقديم حضورك ';
$string['percentage'] = 'النسبة';
$string['percentageallsessions'] = 'نسبة جميع الجلسات ';
$string['percentagesessionscompleted'] = 'نسبة الجلسات التي تم الإنتهاء منها ';
$string['pluginadministration'] = 'إدارة الحضور ';
$string['pluginname'] = 'حضور';
$string['points'] = 'نقاط';
$string['pointsallsessions'] = 'نقاط جميع الجلسات ';
$string['pointssessionscompleted'] = 'نقاط جميع الجلسات التي تم الإنتهاء منها ';
$string['preferences_desc'] = 'ستؤثر التغييرات التي يتم إجراؤها على مجموعات الحالة على جلسات الحضور الحالية وقد تؤثر على التقدير. ';
$string['preventsharederror'] = 'تم تعطيل التعليم الذاتي لجلسة ما لأنه يبدو أن هذا الجهاز قد تم استخدامه لتسجيل الحضور لمتدرب آخر. ';
$string['preventsharedip'] = 'منع المتدربين من مشاركة عنوان IP ';
$string['preventsharedip_help'] = 'منع المتدربين من استخدام نفس الجهاز (المحدد باستخدام عنوان IP) لأخذ الحضور للمتدربين الآخرين. ';
$string['preventsharediptime'] = 'الوقت للسماح بإعادة استخدام عنوان IP (بالدقائق) ';
$string['preventsharediptime_help'] = 'السماح بإعادة استخدام عنوان IP لأخذ الحضور في هذه الجلسة بعد انقضاء هذا الوقت. ';
$string['preview'] = 'معاينة ملف';
$string['previewhtml'] = 'معاينة تنسيق HTML ';
$string['priorto'] = 'تاريخ الجلسة يسبق تاريخ بدء الدورة ({$a}) بحيث يتم إخفاء الجلسات الجديدة المجدولة قبل هذا التاريخ (لا يمكن الوصول إليها). يمكنك تغيير تاريخ بدء الدورة في أي وقت (راجع إعدادات الدورة التدريبية) لتتمكن من الوصول إلى الجلسات السابقة. <br> <br> يرجى تغيير تاريخ الجلسة أو فقط النقر فوق الزر "إضافة جلسة" مرة أخرى للتأكيد؟ ';
$string['privacy:metadata:attendancelog'] = 'سجل حضور المستخدمين المسجل. ';
$string['privacy:metadata:attendancesessions'] = 'الجلسات التي سيُسجل حضورها. ';
$string['privacy:metadata:attendancewarningdone'] = 'سجل التحذيرات المرسلة إلى المستخدمين على سجل حضورهم. ';
$string['privacy:metadata:duration'] = 'مدة الجلسة بالثواني ';
$string['privacy:metadata:groupid'] = 'معرف المجموعة المرتبط بالجلسة. ';
$string['privacy:metadata:ipaddress'] = 'تم تعليم حضور عنوان IP من. ';
$string['privacy:metadata:lasttaken'] = 'الطابع الزمني لآخر مرة تم فيها حضور الجلسة. ';
$string['privacy:metadata:lasttakenby'] = 'معرف المستخدم الخاص بآخر مستخدم يقوم بالحضور في هذه الجلسة ';
$string['privacy:metadata:notifyid'] = 'معرف تحذير جلسة الحضور مقترن بـ. ';
$string['privacy:metadata:remarks'] = 'تعليقات حول حضور المستخدم. ';
$string['privacy:metadata:sessdate'] = 'الطابع الزمني لوقت بدء الجلسة. ';
$string['privacy:metadata:sessionid'] = 'معرف جلسة الحضور. ';
$string['privacy:metadata:statusid'] = 'معرف حالة حضور المتدرب. ';
$string['privacy:metadata:statusset'] = 'تم تعيين الحالة التي ينتمي إليها معرف الحالة. ';
$string['privacy:metadata:studentid'] = 'بطاقة هوية المتدرب الذي تم تسجيل حضوره. ';
$string['privacy:metadata:takenby'] = 'معرف المستخدم الخاص بالمستخدم الذي قام بالحضور للمتدرب. ';
$string['privacy:metadata:timemodified'] = 'الطابع الزمني لوقت آخر تعديل للجلسة ';
$string['privacy:metadata:timesent'] = 'الطابع الزمني عند إرسال التحذير. ';
$string['privacy:metadata:timetaken'] = 'الطابع الزمني لوقت أخذ الحضور للمتدرب. ';
$string['privacy:metadata:userid'] = 'معرّف المستخدم المراد إرسال تحذير إليه. ';
$string['processingfile'] = 'ملف المعالجة ';
$string['qr_cookie_error'] = 'انتهت صلاحية جلسة QR. ';
$string['qr_pass_wrong'] = 'كلمة مرور QR خاطئة أو انتهت صلاحيتها. ';
$string['qrcode'] = 'رمز QR';
$string['randompassword'] = 'كلمة مرور عشوائية ';
$string['remark'] = 'ملاحظة لـ: {a$} ';
$string['remarks'] = 'ملاحظات';
$string['repeatasfollows'] = 'كرر الجلسة أعلاه على النحو التالي ';
$string['repeatevery'] = 'تكرار كل';
$string['repeaton'] = 'كرر على ';
$string['repeatuntil'] = 'كرر حتى';
$string['report'] = 'تقرير';
$string['required'] = 'مطلوب*';
$string['requiredentries'] = 'تقوم السجلات المؤقتة بالكتابة فوق سجلات حضور المشاركين ';
$string['requiredentry'] = 'دليل تعليمات دمج المستخدم المؤقت ';
$string['requiredentry_help'] = '<p align ="center" > <b> الحضور </ b> </p>
<p align = "left"> <strong> دمج الحسابات </ strong> </p>
<p align = "left">
<table border = "2" cellpadding = "4">
<tr>
<th> مستخدم Moodle </th>
<th> مستخدم مؤقت </ th>
<th> الإجراء </th>
</tr>
<tr>
<td> بيانات الحضور </ td>
<td> بيانات الحضور </ td>
<td> سيتجاوز المستخدم المؤقت مستخدم Moodle </td>
</tr>
<tr>
<td> لا توجد بيانات حضور </ td>
<td> بيانات الحضور </ td>
<td> سيتم تحويل حضور المستخدم المؤقت إلى مستخدم Moodle </td>
</tr>
<tr>
<td> بيانات الحضور </ td>
<td> لا توجد بيانات حضور </ td>
<td> سيتم حذف المستخدم المؤقت </ td>
</tr>
<tr>
<td> لا توجد بيانات حضور </ td>
<td> لا توجد بيانات حضور </ td>
<td> سيتم حذف المستخدم المؤقت </ td>
</tr>
</table>

</p>
<p align = "left"> <strong> سيتم حذف المستخدم المؤقت في جميع الحالات بعد إجراء الدمج </ strong> </p> ';
$string['requiresubnet'] = 'يتطلب عنوان الشبكة ';
$string['requiresubnet_help'] = 'قد يقتصر تسجيل الحضور على شبكات فرعية معينة عن طريق تحديد قائمة مفصولة بفواصل لعناوين IP الجزئية أو الكاملة. ';
$string['resetcaledarcreate'] = 'تم تمكين أحداث التقويم ولكن عددًا من الجلسات الحالية لا يحتوي على أحداث. هل تريد إنشاء أحداث تقويم لجميع الجلسات الحالية؟ ';
$string['resetcaledardelete'] = 'تم تعطيل أحداث التقويم ولكن عددًا من الجلسات الحالية بها أحداث يجب حذفها. هل تريد حذف كافة الأحداث الموجودة؟ ';
$string['resetcalendar'] = 'إعادة تعيين التقويم ';
$string['resetdescription'] = 'تذكر أن حذف بيانات الحضور سيمحو المعلومات من قاعدة البيانات. يمكنك فقط إخفاء الجلسات القديمة بعد تغيير تاريخ البدء بالطبع! ';
$string['resetstatuses'] = 'إعادة تعيين الحالات إلى الافتراضي ';
$string['restoredefaults'] = 'استعادة الضبط الافتراضي';
$string['resultsperpage'] = 'النتائج لكل صفحة ';
$string['resultsperpage_desc'] = 'عدد المتدربين المعروضين على الصفحة ';
$string['rotateqrcode'] = 'تدوير رمز QR ';
$string['rotateqrcode_cleartemppass_task'] = 'مهمة لمسح كلمات المرور المؤقتة الناتجة عن تدوير وظيفة رمز QR. ';
$string['rotateqrcodeexpirymargin'] = 'قم بتدوير رمز QR / انتهاء صلاحية كلمة المرور (بالثواني) ';
$string['rotateqrcodeexpirymargin_desc'] = 'الفاصل الزمني (بالثواني) للسماح برمز QR / كلمة المرور منتهية الصلاحية بحلول. ';
$string['rotateqrcodeinterval'] = 'تدوير رمز QR / الفاصل الزمني لكلمة المرور (بالثواني) ';
$string['rotateqrcodeinterval_desc'] = 'الفاصل الزمني (بالثواني) لتدوير رمز QR / كلمة المرور. ';
$string['save'] = 'حفظ الحضور ';
$string['scantime'] = 'وقت الفحص';
$string['scantime_help'] = 'يسمح هذا بتضمين طابع زمني في ملف الاستيراد - سيحاول تحويل الطابع الزمني الذي تم تمريره باستخدام وظيفة PHP strtotime ثم استخدام إعدادات حالة الحضور لتحديد الحالة التي يجب تعيينها للمستخدم ';
$string['search:activity'] = 'الحضور - معلومات النشاط ';
$string['session'] = 'جلسة';
$string['session_help'] = 'جلسة';
$string['sessionadded'] = 'تمت إضافة الجلسة بنجاح ';
$string['sessionalreadyexists'] = 'الجلسة موجودة بالفعل لهذا التاريخ ';
$string['sessiondate'] = 'تاريخ';
$string['sessiondays'] = 'أيام الجلسة';
$string['sessiondeleted'] = 'تم حذف الجلسة بنجاح ';
$string['sessionduplicate'] = 'توجد جلسة مكررة للبرنامج: {a-> course$} في الحضور: {$a-> activity}، {$a-> date} ';
$string['sessionexist'] = 'لم يتم إضافة الجلسة لأنها (موجودة بالفعل)! ';
$string['sessiongenerated'] = 'تم إنشاء جلسة واحدة بنجاح ';
$string['sessions'] = 'الجلسات';
$string['sessionsallcourses'] = 'كل البرامج ';
$string['sessionsbyactivity'] = 'نسخة من الحضور ';
$string['sessionsbycourse'] = 'برنامج';
$string['sessionsbydate'] = 'أسبوع';
$string['sessionscompleted'] = 'الجلسات المأخوذة ';
$string['sessionscurrentcourses'] = 'البرامج الحالية ';
$string['sessionsgenerated'] = 'تم إنشاء {a$} جلسات بنجاح ';
$string['sessionsids'] = 'معرفات الجلسات: ';
$string['sessionsnotfound'] = 'لا توجد جلسات في النطاق الزمني المحدد';
$string['sessionstartdate'] = 'تاريخ بدء الجلسة ';
$string['sessionstotal'] = 'العدد الإجمالي للجلسات';
$string['sessionsupdated'] = 'تم تحديث الجلسات ';
$string['sessiontype'] = 'اكتب';
$string['sessiontype_help'] = 'يمكنك إضافة جلسات لجميع المتدربين أو لمجموعة من المتدربين. تعتمد القدرة على إضافة أنواع مختلفة على وضع مجموعة النشاط. * في وضع المجموعة "لا توجد مجموعات" ، يمكنك إضافة جلسات فقط لجميع المتدربين.
* في وضع المجموعة "مجموعات منفصلة" يمكنك إضافة جلسات فقط لمجموعة من الطلاب.
* في وضع المجموعة "المجموعات المرئية"، يمكنك إضافة كلا النوعين من الجلسات.';
$string['sessiontypeshort'] = 'اكتب';
$string['sessionunknowngroup'] = 'تحدد الجلسة مجموعة (مجموعات) غير معروفة: {a$} ';
$string['sessionupdated'] = 'تم تحديث الجلسة بنجاح ';
$string['set_by_student'] = 'مسجلة ذاتيا ';
$string['setallstatuses'] = 'تعيين حالة لـ ';
$string['setallstatusesto'] = 'اضبط الحالة على «{a$}» ';
$string['setperiod'] = 'الوقت المحدد بالدقائق لتحرير IP ';
$string['settings'] = 'الإعدادات';
$string['setunmarked'] = 'يتم ضبطه تلقائيًا عندما لا يتم وضع علامة عليه "؛';
$string['setunmarked_help'] = 'إذا تم تمكينه في الجلسة ، فقم بتعيين هذه الحالة إذا لم يقم المتدرب بوضع علامة على حضوره. ';
$string['showdefaults'] = 'إظهار الإعدادات الافتراضية ';
$string['showduration'] = 'عرض المدة ';
$string['showextrauserdetails'] = 'إظهار تفاصيل المستخدم الإضافية ';
$string['showqrcode'] = 'إظهار رمز QR ';
$string['showsessiondescriptiononreport'] = 'إظهار وصف الجلسة في التقرير ';
$string['showsessiondescriptiononreport_desc'] = 'إظهار وصف الجلسة في قائمة تقرير الحضور. ';
$string['showsessiondetails'] = 'إظهار تفاصيل الجلسة ';
$string['somedisabledstatus'] = '(تم حذف بعض الخيارات مع بدء الجلسة). ';
$string['sortedgrid'] = 'شبكة مرتبة ';
$string['sortedlist'] = 'قائمة مرتبة ';
$string['startofperiod'] = 'بداية الفترة ';
$string['starttime'] = 'وقت البدء';
$string['status'] = 'الحالة';
$string['statusall'] = 'الكل';
$string['statusdeleted'] = 'تم حذف الحالة ';
$string['statuses'] = 'الحالات';
$string['statusset'] = 'تعيين الحالة {a$} ';
$string['statussetsettings'] = 'مجموعة الحالة ';
$string['statusunselected'] = 'غير محدد "؛';
$string['strftimedm'] = '%b %d';
$string['strftimedmw'] = '<nobr>%a %b %d</nobr>';
$string['strftimedmy'] = '%d %b %Y';
$string['strftimedmyhm'] = '%d %b %Y %I.%M%p'; // Line added to allow multiple sessions in the same day.
$string['strftimedmyw'] = '<nobr>%a %d %b %Y</nobr>';
$string['strftimeh'] = '%I%p';
$string['strftimehm'] = '%I:%M%p';
$string['strftimeshortdate'] = '%d.%m.%Y';
$string['studentavailability'] = 'متاح للمتدربين (بالدقائق)';
$string['studentavailability_help'] = 'عندما يقوم المتدربين بوضع علامة على حضورهم ، عدد الدقائق بعد بدء الجلسة التي تكون هذه الحالة متاحة.';
$string['studentid'] = 'هوية المتدرب';
$string['studentmarked'] = 'هوية المتدرب';
$string['studentmarking'] = 'تسجيل المتدرب ';
$string['studentpassword'] = 'كلمة مرور المتدرب ';
$string['studentrecordingexpanded'] = 'تم توسيع تسجيل المتدرب ';
$string['studentrecordingexpanded_desc'] = 'إظهار إعدادات "تسجيل المتدرب" موسعة بشكل افتراضي عند إنشاء جلسات جديدة. ';
$string['studentscanmark'] = 'السماح للمتدربين بتسجيل الحضور الخاص بهم';
$string['studentscanmark_desc'] = 'If checked, teachers will be able to allow students to mark their own attendance.';
$string['studentscanmark_help'] = 'إذا تم تحديده ، فسيكون المدربين قادرين على السماح للمتدربين بوضع علامة على حضورهم. ';
$string['studentscanmarksessiontime'] = 'المتدربون يسجلون الحضور خلال وقت الجلسة ';
$string['studentscanmarksessiontime_desc'] = 'إذا تم تحديده ، يمكن للمتدربين تسجيل حضورهم أثناء الجلسة فقط. ';
$string['studentscanmarksessiontimeend'] = 'نهاية الجلسة (بالدقائق) ';
$string['studentscanmarksessiontimeend_desc'] = 'إذا لم يكن للجلسة وقت انتهاء ، فكم عدد الدقائق التي يجب أن تكون الجلسة متاحة للمتدربين لتسجيل حضورهم. ';
$string['submit'] = 'إرسال';
$string['submitattendance'] = 'إرسال الحضور ';
$string['submitpassword'] = 'إرسال كلمة المرور ';
$string['subnet'] = 'الشبكة الفرعية ';
$string['subnetactivitylevel'] = 'السماح بتكوين الشبكة الفرعية على مستوى النشاط ';
$string['subnetactivitylevel_desc'] = 'في حالة التمكين ، يمكن للمدربين تجاوز الشبكة الفرعية الافتراضية على مستوى النشاط عند إنشاء حضور. وإلا فسيتم استخدام الموقع الافتراضي عند إنشاء جلسة. ';
$string['subnetwrong'] = 'لا يمكن تسجيل الحضور إلا من مواقع معينة ، وهذا الكمبيوتر غير موجود في القائمة المسموح بها. ';
$string['summary'] = 'ملخص';
$string['tablerenamefailed'] = 'فشلت إعادة تسمية جدول attforblock القديم إلى الحضور ';
$string['tactions'] = 'إجراء';
$string['takeattendance'] = 'سجل الحضور';
$string['takensessions'] = 'الجلسات المأخوذة ';
$string['tcreated'] = 'تم إنشاءه';
$string['tempaddform'] = 'إضافة مستخدم مؤقت ';
$string['tempexists'] = 'يوجد بالفعل مستخدم مؤقت بعنوان البريد الإلكتروني هذا ';
$string['temptable'] = 'قائمة المستخدمين المؤقتين';
$string['tempuser'] = 'مستخدم مؤقت ';
$string['tempusermerge'] = 'دمج المستخدم المؤقت ';
$string['tempusers'] = 'المستخدمون المؤقتون';
$string['tempusersedit'] = 'تحرير المستخدم المؤقت ';
$string['tempuserslist'] = 'المستخدمون المؤقتون';
$string['thirdpartyemails'] = 'إخطار المستخدمين الآخرين';
$string['thirdpartyemails_help'] = 'قائمة المستخدمين الآخرين الذين سيتم إخطارهم. (يتطلب تعديل القدرة / الحضور: viewreports) ';
$string['thirdpartyemailsubject'] = 'تحذير الحضور ';
$string['thirdpartyemailtext'] = 'حضور {$a->firstname} {$a->lastname} لـ {$a->coursename} {$a->aname} أقل من {$a->warningpercent} ({$a->percent})';
$string['thirdpartyemailtextfooter'] = 'أنت تتلقى هذا لأن مدرب هذا البرنامج قد أضاف بريدك الإلكتروني إلى قائمة المستلمين ';
$string['thiscourse'] = 'هذا البرنامج';
$string['time'] = 'الوقت';
$string['timeahead'] = 'لا يمكن إنشاء جلسات متعددة تزيد مدتها عن عام واحد ، يرجى تعديل تاريخي البدء والانتهاء. ';
$string['to'] = 'إلى:';
$string['todate'] = 'حتي اليوم';
$string['triggered'] = 'تم إخطاره أولاً ';
$string['tuseremail'] = 'البريد الإلكتروني';
$string['tusername'] = 'الاسم الكامل';
$string['ungraded'] = 'الجلسات غير المصنفة "؛';
$string['unknowngroup'] = 'مجموعة غير معروفة ';
$string['unknownstatus'] = ' معرف الحالة غير معروف: {a$} ';
$string['update'] = 'تحديث';
$string['uploadattendance'] = 'رفع الحضور بواسطة CSV ';
$string['usedefaultsubnet'] = 'استخدم الاعدادات الافتراضية';
$string['usemessageform'] = 'أو استخدم النموذج أدناه لإرسال رسالة إلى المتدربين المختارين';
$string['userexists'] = 'يوجد بالفعل مستخدم حقيقي بعنوان البريد الإلكتروني هذا ';
$string['userid'] = 'معرف المستخدم';
$string['userimportfield'] = 'حقل المستخدم الخارجي ';
$string['userimportfield_help'] = 'الحقل من ملف CSV الذي تم تحميله والذي يحتوي على معرف المستخدم ';
$string['userimportto'] = 'مجال مستخدم Moodle ';
$string['userimportto_help'] = 'حقل Moodle الذي يطابق البيانات من تصدير CSV';
$string['users'] = ' تصدير المستخدمين';
$string['usestatusset'] = 'مجموعة الحالة ';
$string['variable'] = 'متغير';
$string['variablesupdated'] = 'تم تحديث المتغيرات بنجاح ';
$string['versionforprinting'] = 'نسخة للطباعة';
$string['viewmode'] = 'نمط العرض';
$string['warnafter'] = 'عدد الجلسات التي اتخذت قبل الإنذار ';
$string['warnafter_help'] = 'سيتم تشغيل التحذيرات فقط عندما يكون المستخدم قد سجل حضوره لهذا العدد من الجلسات على الأقل. ';
$string['warningdeleted'] = 'تم حذف التحذير ';
$string['warningdesc'] = 'ستتم إضافة هذه التحذيرات تلقائيًا إلى أي أنشطة حضور جديدة. إذا تم تشغيل أكثر من تحذير واحد في نفس الوقت بالضبط ، فسيتم إرسال التحذير ذو الحد الأدنى للتحذير فقط. ';
$string['warningdesc_course'] = 'تؤثر عتبات التحذيرات التي تم تعيينها هنا على تقرير الغائب وتسمح بإخطار الطلاب والأطراف الثالثة. إذا تم تشغيل أكثر من تحذير واحد في نفس الوقت بالضبط ، فسيتم إرسال التحذير ذو الحد الأدنى للتحذير فقط. ';
$string['warningfailed'] = 'لا يمكنك إنشاء تحذير يستخدم نفس النسبة المئوية وعدد الجلسات. ';
$string['warningpercent'] = 'تحذير إذا كانت النسبة المئوية أقل من "؛';
$string['warningpercent_help'] = 'سيتم إطلاق تحذير عندما تقل النسبة المئوية الإجمالية عن هذا الرقم. ';
$string['warnings'] = 'مجموعة التحذيرات ';
$string['warningthreshold'] = 'عتبة التحذير ';
$string['warningupdated'] = 'تحذيرات محدثة ';
$string['week'] = 'أسبوع (أسابيع) ';
$string['weekcommencing'] = 'يبدأ الأسبوع ';
$string['weeks'] = 'أسابيع';
$string['youcantdo'] = 'لا يمكنك فعل أي شيء';
$string['expired'] = 'مكتمل';
$string['notyetstarted'] = 'لم يبدأ بعد';
$string['setallstatusesto'] = '«{$a}» تحديد حالة الكل كـ';
$string['Present'] = 'حاضر';
$string['Late'] = 'متأخر';
$string['Absent'] = 'غائب';
$string['Excused'] = 'عذر';
$string['A'] = 'غ';
$string['E'] = 'ع';
$string['L'] = 'م';
$string['P'] = 'ح';
