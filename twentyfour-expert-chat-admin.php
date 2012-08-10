<?php

// create custom plugin settings menu
add_action('admin_menu', 'ExpertChat_create_menu');
//Add plugin menu to network admin.
add_action('network_admin_menu', 'ExpertChat_create_menu');


function ExpertChat_create_menu() 
{
    // create admin page
    add_menu_page('Expertchatt', 'Expertchatt', 'expertchat_options', __DIR__, 'expertchat_admin_page');
    add_submenu_page(__DIR__, 'Arkiverade chattar', 'Arkiverade chattar', 'expertchat_options', 'twentyfourExpertChat_archive', 'twentyfourExpertChat_archive_page');
    add_submenu_page(__DIR__, 'Hantera chattar', 'Hantera chattar', 'expertchat_options_handle', 'twentyfourExpertChat_new', 'twentyfourExpertChat_new_page');

    wp_enqueue_style('ExpertChatAdminCss');
    wp_enqueue_script('jquery'); 
    wp_enqueue_script('ExpertChatModal');
    wp_enqueue_script('Placeholder');  
}

function expertchat_admin_page() 
{
    // get the plugin base url
    $pluginRoot = plugins_url('', __FILE__);
    
    //Check if current admin is network admin. If network admin, don't specifie blog_id (get chat data from all blogs)
    if(is_network_admin()){
        $blog_id = null;
    }else{
      global $blog_id;
    }
    
    $expertchatAdmin = new ExpertChat($blog_id);
    $questions = $expertchatAdmin->get_unanswered_questions();
    $is_active = $expertchatAdmin->is_active_chat();
	?>
    <div class="wrap tfmac">
        <h2>Expertchatt-admin</h2>
		<?php
		if($is_active > 0):
                    $activechat = $expertchatAdmin->get_active_chat();
                    $date = new DateTime($activechat->stopDate);
                    
                    if(is_network_admin()):
                        foreach($activechat as $activechat):
                    
                ?>
		<h3 class="activeState">Chatten <?php echo $activechat->title; ?> är aktiv</h3>
		Du chattar nu som <?php the_author_meta("display_name",$activechat->user); ?>, chatten är satt att pågå till ungefär <strong><?php echo $date->format('H:i'); ?></strong> <br/>
		<input type="hidden" value="<?php echo $activechat->id ?>" id="currentchatid" />
		<input type="button" value="Avsluta chatten" onclick="stopCurrentChat();" />
                <?php
                       endforeach;
                   else:
                ?>
                <h3 class="activeState">Chatten <?php echo $activechat->title; ?> är aktiv</h3>
		Du chattar nu som <?php the_author_meta("display_name",$activechat->user); ?>, chatten är satt att pågå till ungefär <strong><?php echo $date->format('H:i'); ?></strong> <br/>
		<input type="hidden" value="<?php echo $activechat->id ?>" id="currentchatid" />
		<input type="button" value="Avsluta chatten" onclick="stopCurrentChat();" />
                <?php endif;  ?>
                
                
        <div class="innerWrapper">
            <table id="mailList">
                <thead>
                    <tr>
                        <th class="date">Tid</th>
                        <th>Namn</th>
                        <th>Fråga</th>
                    </tr>
                </thead>
                <tbody>
                    <?php                    
                        $counter = 0;
						$latest = "";
                    	date_default_timezone_set('Europe/Stockholm'); 
                    ?>
                    <?php foreach($questions as $question): ?>
                    <tr class="item<?php echo ($counter) % 2 == 0 ? " odd": ""; ?>" id="mailItem-<?php echo $question->id; ?>">
                        <td class="date"><?php
                            $date = new DateTime($question->createDate);
                            echo $date->format('H:i'); 
                        ?></td>
                        <td class="name">
                            <span><?php echo $question->name; ?></span>
                            <input type="hidden" name="message" class="messageField" value="<?php echo $question->question; ?>" />
                            <input type="hidden" name="mailId" class="mailId" value="<?php echo $question->id; ?>" />
                        </td>
                        <td class="message"><?php echo $question->question; ?></td>
                    </tr>
                    <?php
						$latest = $question->createDate;
                        $counter++;
                        endforeach; 
                    ?>
                </tbody>
            </table>
			<input type="hidden" id="chatid" value="<?php echo $activechat->id ?>" />
			<input type="hidden" id="latest" value="<?php echo $latest ?>" />
      
            <div id="mailModal">
                <div id="mailAnswer">
                    <div class="mailStatus">Frågan och Svaret har publicerats!</div>
                    <div class="mailTop">
                        <div class="mailDateRow"><span class="mailType"></span> / <span class="mailDate"></span></div>
                        <div class="mailFromRow">Från: <span class="mailFrom"></span></div>
                        <div class="mailSubjectRow">Ämne: <span class="mailSubject"></span></div>
                    </div>
                    <div class="mailMessage"></div>
                    <div class="answerTitle">Svar:</div>
                    <textarea id="answerMessage" name="content" rows="10" cols="100"></textarea>
                    <input type="hidden" class="mailId" name="mailId" value="" />
                    <input type="button" class="cancelButton" id="cancelButton" name="sendBtn" value="Avbryt"/>
                    <input type="button" class="deleteButton" id="deleteButton" name="deleteBtn" value="Ta bort"/>
                    <input type="submit" class="sendButton" id="sendButton" name="sendBtn" value="Skicka"/>
                    <div class="clear"></div>
                </div>  
            </div>
        </div>
		<?php
		else:
		?>
			<h3 >Det finns just nu ingen aktiv chatt.</h3>
			<p>
				Gå in under <strong>Expertchatt -> Hantera chattar</strong> för att skapa en ny expertchatt.
			</p>
                <?php
		endif;
		?>
                        
                
                <?php
                    $nextchats = $expertchatAdmin->get_future_chats();
                    if ( count($nextchats) > 0):
                ?>
                    <p>
                    <strong>Nästkommande chatt: </strong><br/>
                <?php
                        foreach($nextchats as $nextchat): ?>
                        <?php echo $nextchat->title; ?> - Ska starta <?php echo $nextchat->createDate; ?>
                        <input type="button" onclick="startChat(<?php echo $nextchat->id;?>);" value="Starta chatten" />
                        </p>
                <?php
                        endforeach;
                    endif;
                ?>
                        
	</div>
    <script type="text/javascript">
        jQuery(document).ready(function(){
            var $ = jQuery;
			getData();

            var timeoutRef = -1;    			
			
            function answerSent(result)
            {
                $("#mailModal .mailStatus").html("Frågan och Svaret har publicerats!").fadeIn(200);
                hideAndRemoveQuestion();                
            }
            
            function questionDeleted(result)
            {
                $("#mailModal .mailStatus").html("Frågan har tagits bort!").fadeIn(200);
                hideAndRemoveQuestion();
                
            }
            
            function hideAndRemoveQuestion()
            {
                var questionId = $("#mailModal .mailId").val();
                
                $("#mailItem-" + questionId).remove();
                
                $("#mailList tbody tr")
                    .removeClass("odd")
                    .each(
                        function(index, item)
                        {
                            if (index % 2 == 0)
                            {
                                $(item).addClass("odd");
                            }
                        }
                    );
                
                clearTimeout(timeoutRef);
                timeoutRef = setTimeout(
                    function()
                    {
                        $.modal.close();
                    }, 2000
                );
            }
            
            function answerError()
            {
                 $("#mailModal .mailStatus").html("Ett fel har inträffat!").fadeIn(200);
                 clearTimeout(timeoutRef);
                 timeoutRef = setTimeout(
                     function()
                     {
                         $("#mailModal .mailStatus").fadeOut(200);
                     }, 2000
                 );
            }
            
            $("#cancelButton").click(
                function()
                {
                    $.modal.close();
                }
            )
            
            $("#deleteButton").click(
                function()
                {
                    if (confirm("Är du säker på att du vill ta bort frågan?"))
                    {
                        $("#mailModal .mailStatus").html("").hide();
                        var questionId = $("#mailModal .mailId").val();
                        $("#mailModal .mailStatus").html("Tar bort...").fadeIn(200);
                        $.post('<?php echo $pluginRoot; ?>/api/delete-question.php', {questionId: questionId}, questionDeleted).error(answerError);
                    }
                }
            );
            
            $("#sendButton").click(
                function()
                {
                    $("#mailModal .mailStatus").html("").hide();
                    var questionId = $("#mailModal .mailId").val();
                    var answer = $("#answerMessage").val();
                    if (answer == "")
                    {
                        alert("Du måste fylla i något i meddelandet.");
                        return false;
                    }
                    
                    $("#mailModal .mailStatus").html("Skickar...").fadeIn(200);
                    
                    // if everything is ok
                    $.post('<?php echo $pluginRoot; ?>/api/post-answer.php', {questionId: questionId, answer: answer}, answerSent).error(answerError);
                    
                }
            )
            
            $("#mailList tbody tr").live('click', function(e) {
                
                e.preventDefault;
                
                var current = $(this);
                
                $("#mailModal .mailMessage").html(current.find(".messageField").val().replace(/\n/gi, "<br/>"));
                $("#mailModal .mailFrom").html(current.find(".name span").html());
                $("#mailModal .mailSubject").html(current.find(".subject").html());
                $("#mailModal .mailDate").html(current.find(".date").html());
                $("#mailModal .mailType").html(current.find(".type").html());
                $("#mailModal .mailId").val(current.find("input.mailId").val());
                
                $("#mailModal").modal({opacity: 80});
                
            });
            
            
        });
		
		function stopCurrentChat() {
			var chatid = jQuery("#currentchatid").val();
			jQuery.ajax({
				type: "POST",
				url: "<?php echo $pluginRoot ?>/api/close-chat.php",
				async: true,
				timeout: 50000,
				data: { chatid: chatid },
				success: function(data) {
					location.reload();
				}
			});			
		}
		
                //Chatid to determine which chat to start.
		function startChat(id) {
			jQuery.ajax({
				type: "POST",
				url: "<?php echo $pluginRoot ?>/api/open-chat.php",
                                data: {chatid: id},
				async: true,
				timeout: 50000,
				success: function(data) {
					location.reload();					
				}
			});					
		
                }
	
                
		function getData() {
			var chatid =  jQuery("#chatid").val();
			var latest = jQuery("#latest").val();
			jQuery.ajax({
				type: "POST",
				url: "<?php echo $pluginRoot ?>/api/get-new-questions.php",
				async: true,
				timeout: 50000,
				data: {
                                    <?php if(is_network_admin() == false){
                                    echo 'chatid: chatid,'; 
                                    } 
                                    ?>
                                    latest:latest},
                                    success: function(data) {
					for(i=0; i<data.length; i++)
					{
                                                jQuery('#latest').val(data[i].createDate);
						var rawDate = data[i].createDate.replace(/-/g, " ");
                                                var myDate = new Date(rawDate);
						var time =   (myDate.getHours() < 10 ? "0" + myDate.getHours() : myDate.getHours()) + ":" + (myDate.getMinutes() < 10 ? "0" + myDate.getMinutes() : myDate.getMinutes());
						var name = data[i].name;
						var q = data[i].question;
						var id = data[i].id;           
						jQuery("#mailList tbody").append('<tr class="item" id="mailItem-'+id+'"><td class="date">'+time+'</td><td class="name"><span>'+name+'</span><input type="hidden" name="message" class="messageField" value="'+q+'" /><input type="hidden" name="mailId" class="mailId" value="'+id+'" /></td><td class="message">'+q+'</td></tr>');
					
                                         }
					setTimeout("getData()", 1000);
				}
			});
                        
		}        

    </script>
<?php 
}

function twentyfourExpertChat_archive_page()
{
	include 'twentyfour-expert-chat-admin-archive.php';   
}
function twentyfourExpertChat_new_page()
{
	include 'twentyfour-expert-chat-admin-new.php';   
}
?>