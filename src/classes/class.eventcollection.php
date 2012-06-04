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
include_once('src/classes/class.event.php');
class EventCollection extends Collection {


    /////////////////////////////////////////////////
    // PROPERTIES
    /////////////////////////////////////////////////


    /**
     * @var int
     */
    protected $userId = null;

    /**
     * @var array
     */
    protected $userEvents = null;

    /**
     * @var array
     */
    protected $inactiveEvents = null;

    /**
     * @var array
     */
    protected $activeEvents = null;

    /**
     * @var array
     */
    protected $finishedEvents = null;


    /////////////////////////////////////////////////
    // CONSTRUCTOR
    /////////////////////////////////////////////////

    /**
     * @param $id
     */
    public function __construct($id) {
        parent::__construct();
        $this->userId = $id;
        $db = new bDb();
        $output = $db->query("SELECT DISTINCT id FROM ".PFIX."_events");
        $eventNb = sizeof($output);

        for ($i = 1; $i<=$eventNb; $i++) {
            $e = new Event($i);
            parent::add($e);
        }
    }

    /**
     * @return array
     */
    public function getAllEvents()
    {
        return $this->items;
    }

    /**
     * @return array
     */
    public function getActiveEvents()
    {

        if ($this->activeEvents == null) {
            $this->activeEvents = array();
            foreach($this->items as $event) {
                /* @var $event Event */
                if ($event->getActive()==1 && !$event->getFinished()) {
                    array_push($this->activeEvents,$event);
                }
            }
        }
        return $this->activeEvents;
    }

    /**
     * @return array
     */
    public function getInactiveEvents()
    {
        if ($this->inactiveEvents == null) {
            $this->inactiveEvents = array();
            foreach($this->items as $event) {
                /* @var $event Event */
                if ($event->getActive()!=1 && !$event->getFinished()) {
                    array_push($this->inactiveEvents,$event);
                }
            }
        }
        return $this->inactiveEvents;
    }

    /**
     * @return array
     */
    public function getFinishedEvents()
    {
        if ($this->finishedEvents == null) {
            $this->finishedEvents = array();
            foreach($this->items as $event) {
                /* @var $event Event */
                if ($event->getFinished()) {
                    array_push($this->inactiveEvents,$event);
                }
            }
        }
        return $this->finishedEvents;
    }

    /**
     * @param $events array(Event)
     * @return array
     */
    public function onlyPublicEvents($events) {
        $publicEvents = array();
        foreach ($events as $e) {
            /* @var $e Event */
            if($e->getPublic() == 1) {
                $publicEvents[] = $e;
            }
        }
        return $publicEvents;
    }

    /**
     * @param $events array(Event)
     * @return array
     */
    public function onlyPrivateEvents($events) {
        $privateEvents = array();
        foreach ($events as $e) {
            /* @var $e Event */
            if($e->getPublic() != 1) {
                $privateEvents[] = $e;
            }
        }
        return $privateEvents;
    }

    /**
     * @return array
     */
    public function getUserEvents()
    {
        if ($this->userEvents == null) {
            $this->userEvents = array();
            foreach($this->items as $event) {
                /* @var $event Event */
                if ($event->userIsApproved($this->userId)) {
                    array_push($this->userEvents,$event);
                }
            }
        }
        return $this->userEvents;
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


    /////////////////////////////////////////////////
    // METHODS
    /////////////////////////////////////////////////



    /////////////////////////////////////////////////
    // GETTERS AND SETTERS
    /////////////////////////////////////////////////


}
