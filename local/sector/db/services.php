<?php
 defined('MOODLE_INTERNAL') || die();
$functions = array(

	 'local_sector_deletesector' => array( // local_PLUGINNAME_FUNCTIONNAME is the name of the web service function that the client will call.
                'classname'   => 'local_sector_external', // create this class in componentdir/classes/external
                'classpath'   => 'local/sector/classes/external.php',
                'methodname'  => 'deletesector', // implement this function into the above class
                'description' => 'This documentation will be displayed in the generated API documentationAdministration > Plugins > Webservices > API documentation)',
                'type'        => 'write', // the value is 'write' if your function does any database change, otherwise it is 'read'.
                'ajax'        => true, // true/false if you allow this web service function to be callable via ajax

        ),
        'local_sectors_view' => array( // local_PLUGINNAME_FUNCTIONNAME is the name of the web service function that the client will call.
                'classname'   => 'local_sector_external', // create this class in componentdir/classes/external
                'classpath'   => 'local/sector/classes/external.php',
                'methodname'  => 'sectors_view', // implement this function into the above class
                'description' => 'This documentation will be displayed in the generated API documentationAdministration > Plugins > Webservices > API documentation)',
                'type'        => 'write', // the value is 'write' if your function does any database change, otherwise it is 'read'.
                'ajax'        => true, // true/false if you allow this web service function to be callable via ajax

        ),
        'local_getsegments' => array( // local_PLUGINNAME_FUNCTIONNAME is the name of the web service function that the client will call.
                'classname'   => 'local_sector_external', // create this class in componentdir/classes/external
                'classpath'   => 'local/sector/classes/external.php',
                'methodname'  => 'get_segments', // implement this function into the above class
                'description' => 'This documentation will be displayed in the generated API documentationAdministration > Plugins > Webservices > API documentation)',
                'type'        => 'write', // the value is 'write' if your function does any database change, otherwise it is 'read'.
                'ajax'        => true, // true/false if you allow this web service function to be callable via ajax

        ),
        'local_jobrole_level_view' => array( // local_PLUGINNAME_FUNCTIONNAME is the name of the web service function that the client will call.
                'classname'   => 'local_sector_external', // create this class in componentdir/classes/external
                'classpath'   => 'local/sector/classes/external.php',
                'methodname'  => 'jobrole_level_view', // implement this function into the above class
                'description' => 'This documentation will be displayed in the generated API documentationAdministration > Plugins > Webservices > API documentation)',
                'type'        => 'write', // the value is 'write' if your function does any database change, otherwise it is 'read'.
                'ajax'        => true, // true/false if you allow this web service function to be callable via ajax

        ),
        'local_sector_deletesegment' => array( // local_PLUGINNAME_FUNCTIONNAME is the name of the web service function that the client will call.
                'classname'   => 'local_sector_external', // create this class in componentdir/classes/external
                'classpath'   => 'local/sector/classes/external.php',
                'methodname'  => 'deletesegment', // implement this function into the above class
                'description' => 'This documentation will be displayed in the generated API documentationAdministration > Plugins > Webservices > API documentation)',
                'type'        => 'write', // the value is 'write' if your function does any database change, otherwise it is 'read'.
                'ajax'        => true, // true/false if you allow this web service function to be callable via ajax

        ),
        'local_sector_deletejobfamily' => array( // local_PLUGINNAME_FUNCTIONNAME is the name of the web service function that the client will call.
                'classname'   => 'local_sector_external', // create this class in componentdir/classes/external
                'classpath'   => 'local/sector/classes/external.php',
                'methodname'  => 'deletejobfamily', // implement this function into the above class
                'description' => 'This documentation will be displayed in the generated API documentationAdministration > Plugins > Webservices > API documentation)',
                'type'        => 'write', // the value is 'write' if your function does any database change, otherwise it is 'read'.
                'ajax'        => true, // true/false if you allow this web service function to be callable via ajax

        ),

        'local_sector_deletejobrole_level' => array( // local_PLUGINNAME_FUNCTIONNAME is the name of the web service function that the client will call.
                'classname'   => 'local_sector_external', // create this class in componentdir/classes/external
                'classpath'   => 'local/sector/classes/external.php',
                'methodname'  => 'deletejobrole_level', // implement this function into the above class
                'description' => 'This documentation will be displayed in the generated API documentationAdministration > Plugins > Webservices > API documentation)',
                'type'        => 'write', // the value is 'write' if your function does any database change, otherwise it is 'read'.
                'ajax'        => true, // true/false if you allow this web service function to be callable via ajax

        ),
        'display_responsibility' => array(
        'classname' => 'local_sector_external',
        'methodname' => 'responsibilitydisplay',
        'classpath'   => 'local/sector/classes/external.php',
        'description' => 'Responsibility view',
        'ajax' => true,
        'type' => 'read',
       ), 

        'local_sector_deleteresponsibility' => array(
        'classname' => 'local_sector_external',
        'methodname' => 'deleteresponsibility',
        'classpath'   => 'local/sector/classes/external.php',
        'description' => 'Responsibility view',
        'ajax' => true,
        'type' => 'read',
       ), 

       'local_sector_candeleteelement' => array(
        'classname' => 'local_sector_external',
        'methodname' => 'candeleteelement',
        'classpath'   => 'local/sector/classes/external.php',
        'description' => 'Responsibility view',
        'ajax' => true,
        'type' => 'read',
       ), 


);
