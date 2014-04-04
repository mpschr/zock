<?php


class ZockQueries {

    //SELECT  FROM (from...) or FILTER WHERE (filter... or where ...) ORDER (order..)

    function fromEvent($id) {
        return (" FROM ".PFIX."_event_".$id);
    }


    /**
     * @param $arr array
     * @return string
     */
    function wherePaste($arr) {
        return (" WHERE ".join(" AND ",array_diff($arr, array(""," "))));
    }


    /**
     * @return string
     */

    function orderEvent($until="") {

        $interject = "";
        if($until!="") {
            $u = preg_split('/:/',$until);
            if($u[0] == 'matchday_id') {
                $interject = " matchday_id ASC, ";
            }
        }
        return " ORDER BY ".$interject." time ASC";
    }


    /**
     * @param $score_input_type
     * @return string
     */
    function filterMatchWithResult($score_input_type) {
        $score_field = ($score_input_type == 'results') ? 'score_h' : 'score';
        return (" ".$score_field." IS NOT NULL");
    }

    /**
     * @param $until string
     * @return string
     */
    function filterUntil($until) {
        $f = "";
        if($until!="") {
            $u = preg_split('/:/',$until);
            if($u[0] == 'matchday_id') {
                $f = " matchday_id <= '".$u[1]."' ";
            }
            elseif($u[0] == 'date') {
                $f =  " FROM_UNIXTIME(time, '%Y%m%d') <= ".$u[1];
            }
        }
        return $f;
    }

    function limitUntil($until) {
        $l = "";

        if($until!="") {
            $u = preg_split('/:/',$until);
            if ($u[0] == 'match') {
                $l = " LIMIT 0, ".$u[1];
            }
        }
        return $l;
    }

    public function selectEvent($select,$until="")
    {
        $interject = "";
        if($until!="") {
            $u = preg_split('/:/',$until);
            if ($u[0] == 'date') {
                $interject = ", FROM_UNIXTIME(time, '%Y%m%d') as vdate ";
            }
        }
        return ($select.$interject);
    }


} 