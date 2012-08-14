<?php

/*
  Plugin Name: Twentyfour Expert Chat
  Plugin URI: http://www.24hr.se/
  Description: All-to-one chat
  Version: 1.0
  Author: Erik Johansson
  License: GPL2
 */

/*  Copyright 2011  Erik Johansson  (email : erik.johansson@24hr.se)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

class ExpertChat {

    // plugin db version
    public static $twentyfourExpertChatDBVersion = "0.05";
    public static $tables = array(
        "expertchats" => "expertchat_chats",
        "questions" => "expertchat_questions"
    );
    // singleton instance reference
    public static $singletonRef = NULL;
    public $tableNameChats;
    public $tableNameQuestions;
    private $wpdb;
    //Unique id for each blog. Is used to determine which blog we want to use. Is used with wp network feature.
    public $blog_id;

    // creates an instance of the class, if no isntance was created before (singleton implementation)
    public static function getInstance() {
        if (self::$singletonRef == NULL) {
            self::$singletonRef = new ExpertChat();
        }

        return self::$singletonRef;
    }

    public function __construct($blog_id = null) {
        //If parameter blog_id is set, set id as public variable. 
        if ($blog_id != null) {
            $this->blog_id = $blog_id;
        }

        global $wpdb;
        $this->wpdb = $wpdb;

        //Always use same tables, Use the first blogs (id: 1) table prefix.
        $wpdb->prefix = $wpdb->get_blog_prefix(1);


        $this->tableNameChats = $this->wpdb->prefix . ExpertChat::$tables['expertchats'];
        $this->tableNameQuestions = $this->wpdb->prefix . ExpertChat::$tables['questions'];
    }

    public function create_chat($startdate, $userid, $title, $text, $blog_id = null) {
        //If parameter site id isn't set, use public variable blog_id (initliazed in the constructor).
        if ($blog_id == null) {
            $blog_id = $this->blog_id;
        }


        // create an upcoming chat
        // set in db and show in active chat in admin area
        $result = $this->wpdb->insert($this->tableNameChats, array(
            'blog_id' => $blog_id,
            'createDate' => $startdate,
            'open' => 0,
            'user' => intval($userid),
            'title' => $title,
            'text' => $text
                )
        );

        if ($result) {
            $result = $this->wpdb->insert_id;
        }

        return $result;
    }

    public function delete_chat($chatid) {
        // delete a chat from the database
        $result = $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->tableNameChats WHERE id = %d", $chatid));
        return $result;
    }

    public function open_chat($chatid) {
        // close a ongoing chat
        $result = $this->wpdb->update($this->tableNameChats, array('open' => 1), array('id' => $chatid));
        return $result;
    }

    public function close_chat($chatid) {
        // close a ongoing chat
        date_default_timezone_set('Europe/Stockholm');
        $result = $this->wpdb->update($this->tableNameChats, array('open' => 0, "stopDate" => date("Y-m-d H:i:s", time())), array('id' => $chatid));
        return $result;
    }

    public function is_active_chat() {

        //check if there is a active chat available.
        if ($this->blog_id != null) {
            $active_count = $this->wpdb->get_var("SELECT COUNT(*) FROM $this->tableNameChats WHERE open = 1 AND blog_id = $this->blog_id");
        } else {
            $active_count = $this->wpdb->get_var("SELECT COUNT(*) FROM $this->tableNameChats WHERE open = 1 ");
        }

        if ($active_count > 0)
            return true;
        else
            return false;
    }

    public function get_active_chat() {
        // get all answered questions from db to show for the user    	
        //Conditional statement to check if blog_id is set. If so get data for that specific site.
        if ($this->blog_id != null) {
            $result = $this->wpdb->get_results("SELECT * FROM $this->tableNameChats WHERE open = 1 AND blog_id = $this->blog_id");
            return $result[0];
        } else {
            $result = $this->wpdb->get_results("SELECT * FROM $this->tableNameChats WHERE open = 1");
            return $result;
        }
    }

    public function get_latest_chat() {
        //Conditional statement to check if blog_id is set. If so get data for that specific site.
        if ($this->blog_id != null) {
            $result = $this->wpdb->get_results("SELECT * FROM $this->tableNameChats WHERE open = 0 AND stopDate < CURRENT_TIMESTAMP AND stopDate != '0000-00-00 00:00:00' AND blog_id = $this->blog_id ORDER BY createDate DESC");
        } else {
            $result = $this->wpdb->get_results("SELECT * FROM $this->tableNameChats WHERE open = 0 AND stopDate < CURRENT_TIMESTAMP AND stopDate != '0000-00-00 00:00:00' ORDER BY createDate DESC");
        }

        // TO BE DELETED!  
        //$result = $this->wpdb->get_results("SELECT * FROM $this->tableNameChats WHERE open = 0 AND stopDate < CURRENT_TIMESTAMP AND stopDate != '0000-00-00 00:00:00' ORDER BY createDate DESC");
        return $result[0];
    }

    public function get_archived_chats() {
        // get their id's from db
        //Conditional statement to check if blog_id is set. If it is then get data for that specific site.
        if (!is_null($this->blog_id)) {
            $result = $this->wpdb->get_results("SELECT * FROM $this->tableNameChats WHERE open = 0 AND stopDate < CURRENT_TIMESTAMP AND stopDate != '0000-00-00 00:00:00' AND blog_id = $this->blog_id ORDER BY createDate ASC");
        } else {
            $result = $this->wpdb->get_results("SELECT * FROM $this->tableNameChats WHERE open = 0 AND stopDate < CURRENT_TIMESTAMP AND stopDate != '0000-00-00 00:00:00' ORDER BY createDate ASC");
        }

        return $result;
    }

    //Get users that have access to a specific blog.
    public static function get_blog_user($blog_id) {

        $params = array(
            'blog_id' => $blog_id
        );


        return get_users($params);
    }

    public function get_future_chats() {
        // get chats that will appear in the future
        if ($this->blog_id != null) {
            $result = $this->wpdb->get_results("SELECT * FROM $this->tableNameChats WHERE blog_id = $this->blog_id AND open = 0 AND createDate > CURRENT_TIMESTAMP AND stopDate = '0000-00-00 00:00:00' ORDER BY createDate ASC");
        } else {
            $result = $this->wpdb->get_results("SELECT * FROM $this->tableNameChats WHERE open = 0 AND createDate > CURRENT_TIMESTAMP AND stopDate = '0000-00-00 00:00:00' ORDER BY createDate ASC");
        }

        return $result;
    }

    public function get_chats_by_user($name) {
        // get all the chats with this expert
        $result = $this->wpdb->get_results("SELECT * FROM $this->tableNameChats WHERE user = $name AND open = 0");
        return $result;
    }

    public function get_chat($id) {
        // get all answered questions from db to show for the user
        $result = $this->wpdb->get_results("SELECT * FROM $this->tableNameChats WHERE id = $id");
        return $result[0];
    }

    public function set_answer($question_id, $answer) {
        // set in db and show it in active chat
        $result = $this->wpdb->update($this->tableNameQuestions, array('answer' => $answer), array('id' => $question_id));
        return $result;
    }

    public function deny_question($id) {
        //delete it from the db
        $result = $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->tableNameQuestions WHERE id = %d", $id));
        return $result;
    }

    public function post_question($chat_id, $name, $question) {
        // set in db and show in active chat in admin area
        $result = $this->wpdb->insert($this->tableNameQuestions, array(
            'chat_id' => $chat_id,
            'name' => $name,
            'question' => $question,
            'answer' => ''
                )
        );

        if ($result) {
            $result = $this->wpdb->insert_id;
        }

        return $result;
    }

    public function get_unanswered_questions() {
        // get all unanswered questions for the active chat
        $result = $this->wpdb->get_results("SELECT * FROM $this->tableNameQuestions WHERE chatid = $chatid AND ßanswer = '' ");

        return $result;
    }

    public function get_answered_questions($chatid) {
        // get all answered questions for the active chat
        $result = $this->wpdb->get_results("SELECT * FROM $this->tableNameQuestions WHERE chat_id = $chatid AND answer != '' ");
        return $result;
    }

    public function get_new_unanswered_questions($chatid = null, $latest) {
        //get all new questions
        if ($chatid != null) {
            $result = $this->wpdb->get_results("SELECT * FROM $this->tableNameQuestions WHERE chat_id = $chatid AND  createDate > '" . $latest . "' AND answer = '' ");
        } else {
            $result = $this->wpdb->get_results("SELECT * FROM $this->tableNameQuestions WHERE createDate > '" . $latest . "' AND answer = '' ");
        }


        //$result = $this->wpdb->get_results("SELECT * FROM $this->tableNameQuestions INNER JOIN $this->tableNameChats ON $this->tableNameQuestions.chat_id=$this->tableNameChats.id WHERE $this->tableNameChats.open = 1 AND $this->tableNameQuestions.answer = ''  AND $this->tableNameChats.createDate > '".$latest."'");

        return $result;
    }

    public function get_new_answered_questions($chatid, $latest) {
        if ($this->is_active_chat() > 0):
            //get all new questions
            $result = $this->wpdb->get_results("SELECT * FROM $this->tableNameQuestions WHERE chat_id = $chatid AND  createDate > '" . $latest . "' AND answer != '' ");
            return $result;
        else:
            return array("error" => true, "message" => "Chatten är nu stängd.");
        endif;
    }

    public function render_chat_box() {
        //check if there is a active chat or not. Then render the active or archived chat.
        include('twentyfour-expert-chatbox.php');
    }

    public static function add_chat_box() {
        $expertchat = ExpertChat::getInstance();
        $expertchat->render_chat_box();
    }

    // install function, ie create or update the database
    // Version 0.05 changes: Added column blog_id to table _expertchat_chats
    public static function install() {

        global $wpdb;

        $installed_ver = get_option("twentyfourExpertChatDBVersion");
        if ($installed_ver != ExpertChat::$twentyfourExpertChatDBVersion) {


            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            // chat session table
            $table_name = $wpdb->prefix . ExpertChat::$tables['expertchats'];
            $sql = "CREATE TABLE " . $table_name . " (
    	        id mediumint(9) NOT NULL AUTO_INCREMENT,
                blog_id mediumint(9) NULL,
    	        createDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    	        stopDate datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
    	        open TINYINT NOT NULL,
    	        user VARCHAR(300) NOT NULL,
    	        title VARCHAR(300) NOT NULL,
    	        text VARCHAR (3000) NOT NULL,
				UNIQUE KEY id (id)
            );";
            dbDelta($sql);

            // chat messages table
            $table_name = $wpdb->prefix . ExpertChat::$tables['questions'];
            $sql = "CREATE TABLE " . $table_name . " (
    	        id mediumint(9) NOT NULL AUTO_INCREMENT,
				chat_id mediumint(9) NOT NULL,    
				name VARCHAR(300) NOT NULL,	            	        
				question VARCHAR(3000) NOT NULL,
    	        answer VARCHAR(3000) NOT NULL,
				createDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				UNIQUE KEY id (id)
            );";
            dbDelta($sql);

            //echo $sql;
            update_option("twentyfourExpertChatDBVersion", ExpertChat::$twentyfourExpertChatDBVersion);

            //Permission to chat but not to administrate (create, delete) chats.
            add_role('chat', 'Chat - Only chat', array(
                'read' => true,
                'expertchat_options' => true,
                'expertchat_options_handle' => false
            ));

            //Permission to chat and administrate (create, delete) chats.
            add_role('chat_admin', 'Chat Admin - Can administrate chats', array(
                'read' => true,
                'expertchat_options' => true,
                'expertchat_options_handle' => true
            ));

            $admin = get_role('administrator');
            $admin->add_cap('expertchat_options');
            $admin->add_cap('expertchat_options_handle');
        }
    }

    // checks if a database table update is needed
    public static function update() {
        $installed_ver = get_option("twentyfourExpertChatDBVersion");
        if ($installed_ver != ExpertChat::$twentyfourExpertChatDBVersion) {
            ExpertChat::install();
        }
    }

    public static function setRequiredReferences() {
        // css
        wp_register_style('ExpertChatAdminCss', plugins_url('css/style.css', __FILE__));
        wp_register_script('yoohoo', 'http://code.jquery.com/jquery-1.7.2.min.js');
        // load script
        wp_register_script('ExpertChatModal', plugins_url('/js/jquery.simplemodal.1.4.1.min.js', __FILE__));
        wp_register_script('Placeholder', plugins_url('/js/jquery.placeholder.js', __FILE__));
     
    }

    public static function setRequiredChatboxReferences(){
        
        wp_deregister_style('ExpertChatChatboxCss');
        wp_register_style('ExpertChatChatboxCss', plugins_url('css/chatbox.css', __FILE__));
        wp_enqueue_style('ExpertChatChatboxCss');
    
    }
    
}

// hooks for install and update
register_activation_hook(__FILE__, 'ExpertChat::install');
add_action('plugins_loaded', 'ExpertChat::update');
add_action('admin_menu', 'ExpertChat::setRequiredReferences');
//Add js and css to network admin dashboard.
add_action('network_admin_menu', 'ExpertChat::setRequiredReferences');
add_action('wp_enqueue_scripts', 'ExpertChat::setRequiredChatboxReferences');

// load admin page
require_once('twentyfour-expert-chat-admin.php');