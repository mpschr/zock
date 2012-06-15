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

include_once('src/classes/class.match.php');
include_once('src/classes/class.question.php');
class BetsContainer {

    
    /////////////////////////////////////////////////
    // PROPERTIES
    /////////////////////////////////////////////////

    /**
    * Id for the event
    * @var string
    */
    private $id          = null;

    /**
     * @var Event
     */
    private $event       = null;

    private $bets   = array();


    /////////////////////////////////////////////////
    // CONSTRUCTOR
    /////////////////////////////////////////////////

    /**
     * @param Event $event
     */
    public function __construct($event) {
        if ($event != NULL) {
            $this->id      = $event->getId();
            $this->event   = $event;
        }
    }

    /////////////////////////////////////////////////
    // METHODS
    /////////////////////////////////////////////////


    function betSort(&$objArray,$orderby,$sort_flag=SORT_ASC) {
        $indeces = array();


        if ($orderby == 'dueDate')
        {
            foreach($objArray as $obj) {
                $indeces[] = $obj->getDueDate();
            }

            array_multisort($indeces,SORT_NUMERIC,$sort_flag,$objArray);
        }

        elseif ($orderby == 'matchDay')
        {
            $this->betSort($objArray,'dueDate',$sort_flag);

            foreach($objArray as $obj) {
                /* @var $obj Bet */
                $indeces[] = $obj->getMatchdayId();
            }
            array_multisort($indeces,SORT_NUMERIC,$sort_flag,$objArray);

        } elseif ($orderby == 'home') {
            foreach($objArray as $key => $obj) {
                if ($obj instanceof Match) {
                    /* @var $obj Match */
                    $indeces[] = $obj->getHome();
                } else {
                    unset($objArray[$key]);
                }
            }
            return array_multisort($indeces,SORT_STRING,$sort_flag,$objArray);
        }
        elseif ($orderby == 'visitor') {
            foreach($objArray as $key => $obj) {
                if ($obj instanceof Match) {
                    /* @var $obj Match */
                    $indeces[] = $obj->getHome();
                } else {
                    unset($objArray[$key]);
                }
            }
            return array_multisort($indeces,SORT_STRING,$sort_flag,$objArray);
        }
    }

    /**
     * @param string $filter
     * @return array
     */
    private function getMatches($filter='') {

        //filtering
        $filterQuery ='';
        if ($filter!=''){
            $filterQuery = " WHERE ";
            $f = preg_split('/:/', $filter);
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
        $matches = null;
        $query = "SELECT *
				FROM ".PFIX."_event_".$_REQUEST['ev']
            .$filterQuery;

        $db = new bDb();
        $output = $db->query($query);
        //$matches = array();
        $counter = 0;
        foreach ($output as $match) {
            $m = new Match($match,$this->event);
            $matches[] = $m;
        }

        return $matches;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getQuestions() {
        $db = new bDb();
        $query = " SELECT *
            FROM ".PFIX."_qa_questions
            WHERE `event_id`  = ".$_REQUEST['ev'];
        $output = $db->query($query);
        $questions = null;
        foreach ($output as $question) {
            /* @var $question array */
            $q = new Question($question,$this->event);

            $betsQuery = " SELECT *
            FROM ".PFIX."_qa_bets
            WHERE `question_id`  = ".$q->getDbId();
            $betsOutput = $db->query($betsQuery);
            foreach ($betsOutput as $bo) {
                $q->assignBet($bo['user_id'],$bo['answer']);
            }
            $questions[] = $q;
        }
        return $questions;
    }

    /**
     * @param string $filter
     * @param string $orderby
     * @return array(Bet)
     */
    public function getBets($filter='',$orderby='dueDate') {
        $this->bets = array();
        $this->bets = array_merge($this->bets, $this->getMatches($filter));
        $questions = $this->getQuestions();
        if (sizeof($questions) > 0)
            $this->bets = array_merge($this->bets, $questions);

        $orderby = preg_split('/:/',$orderby);
        if ($orderby[1]!='') $orderby[1] = constant($orderby[1]);
        $this->betSort($this->bets,$orderby[0],$orderby[1]);

        return $this->bets;
    }
}
