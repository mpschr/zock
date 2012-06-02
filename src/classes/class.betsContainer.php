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


class BetsContainer {

    
    /////////////////////////////////////////////////
    // PROPERTIES
    /////////////////////////////////////////////////

    /**
    * Id for the event
    * @var string
    */
    private $id          = null;

    private $bets   = array();


    /////////////////////////////////////////////////
    // CONSTRUCTOR
    /////////////////////////////////////////////////

    public function __construct($eventid) {
        if ($eventid != NULL)
            $this->id      = $eventid;

    }

    /////////////////////////////////////////////////
    // METHODS
    /////////////////////////////////////////////////


    function objSort(&$objArray,$indexFunction,$sort_flags=0) {
        $indeces = array();
        foreach($objArray as $obj) {
            $indeces[] = $indexFunction($obj);
        }
        return array_multisort($indeces,$objArray,$sort_flags);
    }

    /**
     * @param string $filter
     * @return array
     */
    private function getMatches($filter='') {

        $filterQuery = " WHERE ";
        //filtering
        if ($filter!=''){
            $f = preg_split('/:/', $_REQUEST['filter']);
            switch ($f[0]){
                case 'team':
                    $filterQuery .= "`home` LIKE '%".$f[1]."%' OR `visitor` LIKE '%".$f[1]."%'";
                    break;
                case 'home';
                    $filterQuery .= "`home` LIKE '%".$f[1]."%'";
                    break;
                case 'visitor';
                    $filterQuery .= "`visitor` LIKE '%".$f[1]."%'";
                    break;
                case 'matchday';
                    $filterQuery .= "`matchday` LIKE '".$f[1]."'";
                    break;
            }
        }

        $orderplus = "";
        $query = "SELECT *
				FROM ".PFIX."_event_".$_REQUEST['ev']
            .$filterQuery;

        $db = new bDb();
        $output = $db->query($query);
        $matches = array();
        foreach ($output as $match); {
            /* @var $match Match */
            array_push($matches,new Match($match));
        }

        return $matches;
    }

    /**
     * @return null
     * @throws Exception
     */
    private function getQuestions() {
        $db = new bDb();
        $output = $db->query("");
        $questions = array();
        foreach ($output as $question); {
            /* @var $question Match */
            array_push($questions,new Match($question));
        }
        return $questions;
    }

    /**
     * @param string $filter
     * @param string $orderby
     * @return array
     */
    public function getBets($filter='',$orderby='') {
        $this->bets = array();
        //array_push($this->bets,$this->getQuestions());
        array_push($this->bets,$this->getMatches($filter,$orderby));
        objSort($this->bets,'getDueDate');
        return $this->bets;
    }
}
