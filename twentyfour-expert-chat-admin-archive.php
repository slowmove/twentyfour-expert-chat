<div class="wrap tfmac">    
    <h2>Arkiverade expertchattar</h2>
    <?php
    $chatid = $_GET["chatid"];

    $pluginRoot = plugins_url('', __FILE__);
    $chatadmin = new ExpertChat();
    
    if( intval($chatid) > 0):
        $questions = $chatadmin->get_answered_questions($chatid);
        $visibleChat = $chatadmin->get_chat($chatid);
        ?>
        Frågor för chatt: <strong>"<?php echo $visibleChat->title; ?>"</strong> som hölls <strong><?php echo $visibleChat->createDate; ?></strong>
        <br/>
        <a href="#" onclick="backToArchive();">Tillbaka till chattarkivet</a>
        <div class="innerWrapper">
            <table id="mailList">
                <thead>
                    <tr>
                        <th class="date">Tid</th>
                        <th>Namn</th>
                        <th>Fråga</th>
                        <th>Svar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($questions as $question): ?>
                    <tr class="item" id="mailItem-<?php echo $question->id; ?>">
                        <td class="date"><?php
                            $date = new DateTime($question->createDate);
                            echo $date->format('H:i'); 
                        ?></td>
                        <td class="name">
                            <span><?php echo $question->name; ?></span>
                        </td>
                        <td class="message">
                            <?php echo $question->question; ?>
                        </td>
                        <td class="message">
                            <?php echo $question->answer; ?>
                        </td>
                    </tr>
                    <?php
                        endforeach; 
                    ?>
                </tbody>
            </table>
        </div>
        <script type="text/javascript">
        function backToArchive() {
            var url = location.href.split("&chatid");
            location.href = url[0];
        }
        </script>
        <?php
    else:
        $archived_chats = $chatadmin->get_archived_chats();
        if( count($archived_chats) > 0 ):
        ?>
        <div class="innerWrapper">
            <table id="mailList">
                <thead>
                    <tr>
                        <th class="date">Datum</th>
                        <th>Namn</th>
                        <th>Expert</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($archived_chats as $chat): ?>
                    <tr class="item" id="mailItem-<?php echo $chat->id; ?>">
                        <td class="date">
                            <?php
                                $date = new DateTime($chat->createDate);
                                echo $date->format('Y-m-d'); 
                            ?>
                        </td>
                        <td class="name">
                            <span><?php echo $chat->title; ?></span>
                        </td>
                        <td class="message">
                            <?php the_author_meta("display_name", $chat->user) ?>
                        </td>
                        <td>
                            <input type="button" value="Visa chatt" onclick="showChat(<?php echo $chat->id ?>);" />
                            <input type="button" value="Ta bort" onclick="deleteChat('<?php echo $chat->id; ?>')" />                            
                        </td>
                    </tr>
                    <?php
                        endforeach; 
                    ?>
                </tbody>
            </table>
        </div>
        <script type="text/javascript">
            function showChat(chatid) {
                location.href = location.href + "&chatid=" + chatid;
            }

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
                        jQuery("#mailItem-" + id).hide();
                    }
                });	        
            }               
        </script>
        <?php
        endif;
    endif;
    ?>
</div>
