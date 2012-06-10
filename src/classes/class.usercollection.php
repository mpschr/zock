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

        for ($i = 0; $i<$userNb; $i++) {
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
     * @param $event Event
     * @return array
     */
    public function getEventUsers($event)
    {
        /* @var $event Event */
        $evid = $event->getId();
        if ($this->eventUsers[$evid] == null) {
            $this->eventUsers[$evid] = array();
            foreach($this->items as $user) {
                /* @var $user User */
                if ($event->userIsApproved($user->getId())) {
                    $this->eventUsers[$evid][] = $user;
                }
            }
        }
        return $this->eventUsers[$evid];
    }

    /**
     * @param int $id
     * @return \Event|null
     */
    public function getUserById($id) {
        foreach($this->items as $user) {
            /* @var $user Event */
            if ($user->getId()==$id)
                return $user;
        }
        return null;
    }

}
