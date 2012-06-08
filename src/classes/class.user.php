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


class User {

    
    /////////////////////////////////////////////////
    // PROPERTIES
    /////////////////////////////////////////////////

    /**
    * Id for the language
    * @var int
    */
    private $id          = null;


    /**
    * Login of the user
    * @var string
    */
    private $login          = null;

    /**
     * @var string
     */
    private $pw  = null;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $famname;

    /**
     * @var string
     */
    private $event_admin;

    /**
     * @var string
     */
    private $lang;

    /**
     * @var string
     */
    private $style;

    /**
     * @var string
     */
    private $text;

    /**
     * @var string
     */
    private $picture;

    /**
     * @var string
     */
    private $account_type;

    /**
     * @var string
     */
    private $account_details;

    /**
     * @var string
     */
    private $account_holder;

    /**
     * @var string
     */
    private $home_comments;


    /////////////////////////////////////////////////
    // CONSTRUCTOR
    /////////////////////////////////////////////////

    function __construct($userdataarray) {
        if ($userdataarray != '')
            return null;

        foreach($userdataarray as $label => $content) {
            $this->$label = $content;
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



    /**
     * @param string $account_details
     */
    public function setAccountDetails($account_details)
    {
        $this->account_details = $account_details;
    }

    /**
     * @return string
     */
    public function getAccountDetails()
    {
        return $this->account_details;
    }

    /**
     * @param string $account_holder
     */
    public function setAccountHolder($account_holder)
    {
        $this->account_holder = $account_holder;
    }

    /**
     * @return string
     */
    public function getAccountHolder()
    {
        return $this->account_holder;
    }

    /**
     * @param string $account_type
     */
    public function setAccountType($account_type)
    {
        $this->account_type = $account_type;
    }

    /**
     * @return string
     */
    public function getAccountType()
    {
        return $this->account_type;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $event_admin
     */
    public function setEventAdmin($event_admin)
    {
        $this->event_admin = $event_admin;
    }

    /**
     * @return string
     */
    public function getEventAdmin()
    {
        return $this->event_admin;
    }

    /**
     * @param string $famname
     */
    public function setFamname($famname)
    {
        $this->famname = $famname;
    }

    /**
     * @return string
     */
    public function getFamname()
    {
        return $this->famname;
    }

    /**
     * @param string $home_comments
     */
    public function setHomeComments($home_comments)
    {
        $this->home_comments = $home_comments;
    }

    /**
     * @return string
     */
    public function getHomeComments()
    {
        return $this->home_comments;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    /**
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param string $login
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $picture
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
    }

    /**
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @param string $pw
     */
    public function setPw($pw)
    {
        $this->pw = $pw;
    }

    /**
     * @return string
     */
    public function getPw()
    {
        return $this->pw;
    }

    /**
     * @param string $style
     */
    public function setStyle($style)
    {
        $this->style = $style;
    }

    /**
     * @return string
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }
}
