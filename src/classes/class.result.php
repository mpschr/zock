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


class Result {

    
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

    /**
     * @param string $filter
     * @param string $orderby
     * @return array
     */
    private function getBetsAndResults($filter='',$orderby='') {
        $orderby = (orderby!='') ? explode(':', $orderby) : explode(':', 'time:ASC');

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


        //get the info by applying the insight of $orderby
        $orderplus = "";
        if ($orderby[0] == 'matchday_id') $orderplus = ", time ASC";
        $query = "SELECT *
				FROM ".PFIX."_event_".$_REQUEST['ev']
            .$filterQuery.
            " ORDER BY ".$orderby[0]." ".$orderby[1].$orderplus.";";

        $db = new bDb();
        $output = $db->query($query);

        return null;
    }

    /**
     * @return null
     * @throws Exception
     */
    private function getQuestions() {
        throw new Exception("Not yet implemented");
        return null;
    }

    /**
     * @param string $filter
     * @param string $orderby
     * @return array
     */
    public function getBets($filter='',$orderby='') {
        $this->bets = array();
        array_push($this->bets,$this->getQuestions());
        array_push($this->bets,$this->getBetsAndResults($filter,$orderby));
        return $this->bets;
    }

}
