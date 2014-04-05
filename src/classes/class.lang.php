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


class Lang {

    
    /////////////////////////////////////////////////
    // PROPERTIES
    /////////////////////////////////////////////////

    /**
    * Id for the language
    * @var string
    */
    public $id          = "en";


    /**
    * Name of the language
    * @var string
    */
    public $name          = "English";


    private $contents   = array();

    /////////////////////////////////////////////////
    // CONSTRUCTOR
    /////////////////////////////////////////////////

    function __construct($langid) {
        if ($langid != NULL)
            $this->id      = $langid;

        $filename="data/langs/lang_$langid.xml";
        $xml = simplexml_load_file($filename);
        foreach($xml->entry as $entry) {
            $lab = (string)  $entry->attributes()->key;
            $this->contents[$lab] = (string) $entry;
        }
    }


    /////////////////////////////////////////////////
    // CONSTRUCTOR
    /////////////////////////////////////////////////


    /**
    * Gets the content for the label 
    * @param string $label
    * @return string
    */
    public function get($label) {
        if (!isset($this->contents[$label]))
            return "NO TRANS: <b>".$label."</b>";

            
        $content = $this->contents[$label];
        if ($content == "") {
            $content = "EMPTY: <b>".$label."</b>";
        }
        return $content;
    }
}
