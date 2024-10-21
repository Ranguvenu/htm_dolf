<?php
namespace local_trainingprogram\local;

class dataprovider {

    private static $instance = null;

    private $trainingid;

    public $training;

    public const LECTURE=0;
    public const CASE_STUDIES=1;
    public const DIALOGUE_TEAMS=2;
    public const EXERCISES_ASSIGNMENTS=3;
    public const PREEXAM=0;
    public const POSTEXAM=1;
    public const COUPON=0;
    public const EARLY_REGISTRATION=1;
    public const GROUPS=2;
    public const PUBLICPROGRAM=0;
    public const PRIVATEPROGRAM=1;
    public const DEDICATEDPROGRAM=2;
    public const ZOOM=1;
    public const WEBEX=2;
    public const TEAMS=3;

    public static $programmethods  = [self::LECTURE => 'Lecture', 
                                      self::CASE_STUDIES => 'Case studies', 
                                      self::DIALOGUE_TEAMS => 'Dialogue teams', 
                                      self::EXERCISES_ASSIGNMENTS => 'Exercises and assignments'];

    public static $languages = ['arabic' => 'Arabic', 'english' => 'English'];

    public static $evaluationmethods = [self::PREEXAM => 'Pre exam', 
                                        self::POSTEXAM => 'Post exam'];

    public static $trainingmethods = ['online' => 'Online', 
                                      'offline' => 'Offline'];

    public static $discount = [self::COUPON => 'Coupon', 
                               self::EARLY_REGISTRATION => 'Early registration', 
                               self::GROUPS => 'Groups'];

    public static $programtype = [self::PUBLICPROGRAM => 'Public',
                                  self::PRIVATEPROGRAM => 'Private',
                                  self::DEDICATEDPROGRAM => 'Dedicated'];

    public static $virtualplatforms = [self::ZOOM => 'Zoom',
                                       self::WEBEX => 'Webex',
                                       self::TEAMS => 'Teams'];

    protected function __construct($trainingid) {
        global $DB;
        $this->trainingid = $trainingid;

        $this->training = $DB->get_record('local_trainingprogram', ['id' => $this->trainingid]);
        
    }

    public static function getInstance($trainingid) {
        if (self::$instance == null)
        {
          self::$instance = new dataprovider($trainingid);
        }
     
        return self::$instance;
    }

    public function get_programdates() {
       global $DB;
       $dates = ['availablefrom' => $this->training->availablefrom, 
                 'availableto' => $this->training->availableto];
      
       return $dates;

    }

    public function get_programtype() {
        return $this->training->trainingmethods;
    }

    public static function get_timeselector() {

        for ($i = 7; $i <= 21; $i++) {
            $hours[$i] = sprintf("%02d", $i);
        }
        for ($i = 0; $i < 60; $i += 1) {
            $minutes[$i] = sprintf("%02d", $i);
        }

        return compact('hours', 'minutes');
    }

    public static function get_offering($offeringid) {
        global $DB;
       $sql = 'SELECT tpo.id,tpo.trainertype,tpo.trainerorg,tpo.startdate,tpo.enddate,tpo.time,tpo.duration,tpo.trainingmethod,tpo.trainingid,
                        tpo.availableseats, tpo.sellingprice,h.name AS hallname,
                        h.maplocation, h.seatingcapacity, h.city,tpo.time,tpo.duration,tpo.languages,tpo.financially_closed_status 

                  FROM  {tp_offerings} AS tpo 
             LEFT JOIN {hall} AS h ON tpo.halladdress=h.id WHERE tpo.id = :offeringid';
        return $DB->get_record_sql($sql, ['offeringid' => $offeringid]);
    }

}
