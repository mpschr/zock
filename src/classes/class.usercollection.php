<?php
/*
===================================
zock!

Developed by
------------
* Michael Schroeder:
   michael.p.schroeder@gmail.com 
*
*

http://zock.sf.net

zock! is a free software licensed under GPL (General public license) v3
      more information look in the root folder for "LICENSE".
===================================
*/


include_once('src/opensource/class.collection.php');
include_once('src/classes/class.user.php');
class UserCollection extends Collection {


    /////////////////////////////////////////////////
    // PROPERTIES
    /////////////////////////////////////////////////


    /**
     * @var int
     */
    protected $eventId = null;

    /**
     * @var array
     */
    protected $eventUsers = null;



    /////////////////////////////////////////////////
    // CONSTRUCTOR
    /////////////////////////////////////////////////

    /**
     * @param $id
     */
    public function __construct($id=null) {
        parent::__construct();
        $this->eventId = $id;
        $db = new bDb();
        $output = $db->query("SELECT * FROM ".PFIX."_users");
        $userNb = sizeof($output);

        for ($i = 1; $i<=$userNb; $i++) {
            $u = new User($output[$i]);
            parent::add($u);
        }
    }



    /**
     * @return array
     */
    public function getAllUsers()
    {
        return $this->items;
    }

    /**
     * @return array
     */
    public function getEventUsers()
    {
        if ($this->eventUsers == null) {
            $this->eventUsers = array();
            foreach($this->items as $event) {
                /* @var $event Event */
                if ($event->userIsApproved($this->userId)) {
                    array_push($this->eventUsers,$event);
                }
            }
        }
        return $this->eventUsers;
    }

    /**
     * @param int $id
     * @return \Event|null
     */
    public function getEventById($id) {
        foreach($this->items as $event) {
            /* @var $event Event */
            if ($event->getId()==$id)
                return $event;
        }
        return null;
    }

}
