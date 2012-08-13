<div class="wrap tfmac">
    <h2>Skapa ny expertchatt</h2>
    <?php
    $startDateYear = $_POST["startDateYear"];
    $startDateMonth = $_POST["startDateMonth"];
    $startDateDay = $_POST["startDateDay"];
    $startDateHour = $_POST["startDateHour"];
    $startDateMinute = $_POST["startDateMinute"];

    $startDate = $startDateYear . "-" . $startDateMonth . "-" . $startDateDay . " " . $startDateHour . ":". $startDateMinute;

    $user = $_POST["user"];
    $chattitle = $_POST["chattitle"];
    $chattext = $_POST["chattext"];
    $blog = $_POST['blog'];

    global $wpdb;
    $wp_user_search = $wpdb->get_results("SELECT ID, display_name FROM $wpdb->users ORDER BY ID");

    $pluginRoot = plugins_url('', __FILE__);
    global $blog_id;
    $blogid = $is_network_admin ? null : $blog_id;
    $chatadmin = new ExpertChat($blogid);

    if( $startDate != "" && intval($user) > 0):
        $chatadmin->create_chat($startDate, $user, $chattitle, $chattext, $blog);
        echo "<h3>Din chatt är tillagd. Du kan fortsätta skapa ytterligare chattar.</h3>";
    endif;


    ?>



    <form method="post" action="" id="newChatForm">
        <input type="text" name="chattitle" id="chattitle" placeholder="Chattens titel" />
        <select type="text" name="startDateYear" class="startDate" placeholder="YYYY">
            <option value="2011">2011</option>
            <option value="2012" selected="selected">2012</option>
            <option value="2013" >2013</option>
            <option value="2014" >2014</option>
            <option value="2015" >2015</option>
            <option value="2016" >2016</option>
            <option value="2017" >2017</option>
            <option value="2018" >2018</option>
            <option value="2019" >2019</option>
            <option value="2020" >2020</option>
        </select>
        <select type="text" name="startDateMonth" class="startDate" placeholder="MM">
            <option value="01" selected="selected">Januari</option>
            <option value="02">februari</option>
            <option value="03">mars</option>
            <option value="04">april</option>
            <option value="05">maj</option>
            <option value="06">juni</option>
            <option value="07">juli</option>
            <option value="08">augusti</option>
            <option value="09">september</option>
            <option value="10">oktober</option>
            <option value="11">november</option>
            <option value="12">december</option>
        </select>
        <select type="text" name="startDateDay" class="startDate" placeholder="DD">
            <option value="01" selected="selected">01</option>
            <option value="02">02</option>
            <option value="03">03</option>
            <option value="04">04</option>
            <option value="05">05</option>
            <option value="06">06</option>
            <option value="07">07</option>
            <option value="08">08</option>
            <option value="09">09</option>
            <option value="10">10</option>
            <option value="11">11</option>
            <option value="12">12</option>
            <option value="13">13</option>
            <option value="14">14</option>
            <option value="15">15</option>
            <option value="16">16</option>
            <option value="17">17</option>
            <option value="18">18</option>
            <option value="19">19</option>
            <option value="20">20</option>
            <option value="21">21</option>
            <option value="22">22</option>
            <option value="23">23</option>
            <option value="24">24</option>
            <option value="25">25</option>
            <option value="26">26</option>
            <option value="27">27</option>
            <option value="28">28</option>
            <option value="29">29</option>
            <option value="30">30</option>
            <option value="31">31</option>
        </select>
        Klockan
        <select type="text" name="startDateHour" class="startDate" placeholder="HH">
            <option value="01" selected="selected">01</option>
            <option value="02">02</option>
            <option value="03">03</option>
            <option value="04">04</option>
            <option value="05">05</option>
            <option value="06">06</option>
            <option value="07">07</option>
            <option value="08">08</option>
            <option value="09">09</option>
            <option value="10">10</option>
            <option value="11">11</option>
            <option value="12">12</option>
            <option value="13">13</option>
            <option value="14">14</option>
            <option value="15">15</option>
            <option value="16">16</option>
            <option value="17">17</option>
            <option value="18">18</option>
            <option value="19">19</option>
            <option value="20">20</option>
            <option value="21">21</option>
            <option value="22">22</option>
            <option value="23">23</option>
            <option value="24">24</option>
        </select>
        <select type="text" name="startDateMinute" class="startDate" placeholder="MM">
            <option value="00" selected="selected">00</option>
            <option value="10">10</option>
            <option value="20">20</option>
            <option value="30">30</option>
            <option value="40">40</option>
            <option value="50">50</option>
        </select>



        <select type="text" name="blog" id="blog">
            <option selected="selected">Välj site</option>
            <?php

                if(is_network_admin()){
                    $blogs = get_blog_list(0, 'all');

                    foreach($blogs as $blog){
                        echo '<option value="'.$blog['blog_id'].'">'.$blog['path'].'</option>';
                    }

                }else{
                    global $blog_id;
                    echo '<option value="'.$blog_id.'">'.get_bloginfo().'</option>';
                }

            ?>


        </select>
        <select type="text" name="user" id="user" placeholder="user id">
            <option selected="selected">Välj användare</option>
            <?php foreach($wp_user_search as $wpuser): ?>
                <option value="<?php echo $wpuser->ID ?>"><?php echo $wpuser->display_name ?></option>
            <?php endforeach; ?>
        </select>
        <textarea name="chattext" id="chattext" placeholder="Text"></textarea>
        <input type="submit" value="Skapa" />
    </form>
    <?php
    $futurechats = $chatadmin->get_future_chats();
    ?>
    <ol id="futureChatList">
        <?php
        foreach($futurechats as $chat):
            $date = new DateTime($chat->createDate);
            echo "<li id=".$chat->id.">" . $date->format('Y-m-d H:i')  . " - " . $chat->title . " <a href='#' onclick='deleteChat(".$chat->id.")'>Ta bort</a></li>";
        endforeach;
        ?>
    </ol>
</div>

<script type="text/javascript">
    function deleteChat(id)
    {
        jQuery.ajax({
            type: "POST",
            url: "<?php echo $pluginRoot ?>/api/delete-chat.php",
            async: true,
            timeout: 50000,
            data: { chatid: id },
            success: function(data) {
                //location.reload();
                jQuery("#" + id).hide();
            }
        });
    }


    jQuery('#blog').change(function(){

        jQuery.ajax({
            type: "POST",
            url: "<?php echo $pluginRoot ?>/api/get_blog_users.php",
            data: {blogid: this.value},
            success: function(data){
                jQuery('#user').empty();

                for(u in data){
                    var value = jQuery('<option/>').attr('value', data[u].ID);

                    value.text(data[u].user_login);
                    value.appendTo('#user');
                }

            }
        });

    });

</script>
