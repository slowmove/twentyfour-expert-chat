<?php
global $blog_id;
$pluginRoot = plugins_url('', __FILE__);
$expertchat = new ExpertChat($blog_id);
$activechat = $expertchat->get_active_chat();
$expert_name = get_the_author_meta("first_name", $activechat->user);
$date = new DateTime($activechat->createDate);


//echo "is active: " . $expertchat->is_active_chat() . "<br/>";
//echo "archived chats: " . count($expertchat->get_archived_chats());

if ( $expertchat->is_active_chat() || count($expertchat->get_archived_chats()) != 0 )
{
    $urlid = $_GET["chatid"];
    if($urlid != "")
    {
        $headline = "Arkiverad";
        $chatid = $_GET["chatid"];
        if( intval($chatid) > 0 )
            $activechat = $expertchat->get_chat(intval($chatid));
        
        $offlinetext = "OBS! Denna chat är avslutad!";
    }
    else
    {
        $active = $expertchat->is_active_chat();
        if ( $active )
        {
            $headline = "Pågående";
            $byline = $activechat->text;
        }
        else
        {
            $headline = "Arkiverad";
            $chatid = $_GET["chatid"];
            if( intval($chatid) > 0 )
                $activechat = $expertchat->get_chat(intval($chatid));
            else
                $activechat = $expertchat->get_latest_chat();
            
            $offlinetext = "OBS! Denna chat är avslutad!";
        }
    }
    $latest = "";
    echo '
        <div id="chatbox">                
            <div class="chatHead">
                <h2>'.$headline.' expertchatt - '. $activechat->title .'</h2>
                <span>'. $offlinetext .'</span>
                <p>'.$byline.'</p>
            </div>
            <div id="chat">
                <div class="message expert">
                    <div class="time">
                        <p>'. $date->format('H:i') .'</p>
                        <p class="name">Administrator</p>
                    </div>
                    <p class="message">'.get_the_author_meta("first_name", $activechat->user).' gör sig beredd på att starta dagens diskussion</p>
                </div>        
            ';
            
            $questions = $expertchat->get_answered_questions($activechat->id );        
            foreach( $questions as $question):
                $time = new DateTime($question->createDate);
                $name = $question->name;
                $q = $question->question;
                $a = $question->answer;
                echo '
                <div class="message">
                    <div class="time">
                        <p>'. $time->format('H:i') .'</p>
                        <p class="name">'.$name.'</p>
                    </div>
                    <p class="message">'.$q.'</p>
                </div>
                <div class="message expert">
                    <div class="time">
                        <p>'. $time->format('H:i') .'</p>
                        <p class="name">'.get_the_author_meta("first_name", $activechat->user).'</p>
                    </div>
                    <p class="message">'.$a.'</p>
                </div>            
                ';
                $latest = $question->createDate;
            endforeach;
    
        echo '
            </div>
        </div>
        <input type="hidden" id="chatid" value="'. $activechat->id .'" />
        <input type="hidden" id="latest" value="'. $latest .'" />
        ';
        if ( $active ):
            echo '
            <div class="form commentForm expertChatForm">
                <input type="text" id="qname" class="text" value="" placeholder="Ditt namn (eller nickname)" />
                <textarea class="comment" id="question" placeholder="Skriv till experten här:"></textarea>
                <input type="submit" class="button" value="Skicka" />
                
                <img src="'. $pluginRoot .'/img/load.gif" id="loadImg" style="display:none;" />                
            </div>
            <p class="messageInfo">Din kommentar eller fråga kommer modereras och därefter publiceras tillsammans med svar.</p>
            ';
        ?>
        <script type="text/javascript">
            $(document).ready(function() {
                document.getElementById('chat').scrollTop = 9999999;
                getData();
            });    
            $(".expertChatForm input:submit").click(
                        function()
                        {
                            $("input.button").hide();
                            $("#loadImg").show();
                            
                            var chatid =  $("#chatid").val();
                            var qname = $("#qname").val();
                            var question = $("#question").val();
                            if ( qname != "" && question != "")
                                $.post("<?php echo $pluginRoot ?>/api/post-question.php", {chat_id: chatid, qname: qname, question:question}, response).error(answerError);
                            else
                                alert("Du måste fylla i namn och meddelande för att kunna ställa en fråga.");
                            
                        }
            );
            
            function response(response){
                alert("Din fråga har mottagits och besvaras så fort det går.");
                $("#question").val("");
                $("input.button").show();
                $("#loadImg").hide();                
            }
            function answerError(error){
                alert(error);
            }
            
            function getData() {
                var chatid =  $("#chatid").val();
                var latest = $("#latest").val();
                $.ajax({
                    type: "POST",
                    url: "<?php echo $pluginRoot ?>/api/get-new-answers.php",
                    async: true,
                    timeout: 50000,
                    data: {chatid: chatid, latest:latest },
                    success: function(data) {
                        if( data.error )
                        {
                            var myDate = new Date();
                            var time = (myDate.getHours() < 10 ? "0" + myDate.getHours() : myDate.getHours()) + ":" + (myDate.getMinutes() < 10 ? "0" + myDate.getMinutes() : myDate.getMinutes());
                            $(".messageInfo").hide();
                            $(".expertChatForm").hide();
                            $("#chat").append('<div class="message expert"><div class="time"><p>' + time +'</p><p class="name"><?php echo get_the_author_meta("first_name", $activechat->user); ?> </p></div><p class="message">'+data.message+'</p></div>');
                            document.getElementById('chat').scrollTop = 9999999;
                        }
                        else
                        {
                            for(i=0; i<data.length; i++)
                            {
                                $('#latest').val(data[i].createDate);
                                var rawDate = data[i].createDate.replace(/-/g, " ");
                                var myDate = new Date( rawDate );
                                var time =   (myDate.getHours() < 10 ? "0" + myDate.getHours() : myDate.getHours()) + ":" + (myDate.getMinutes() < 10 ? "0" + myDate.getMinutes() : myDate.getMinutes());
                                var name = data[i].name;
                                var q = data[i].question;
                                var a = data[i].answer;
                                var expert = $('.name').html();                        
                                $("#chat").append('<div class="message"><div class="time"><p>'+time+'</p><p class="name">'+name+'</p></div><p class="message">'+q+'</p></div><div class="message expert"><div class="time"><p>' + time +'</p><p class="name"><p class="name"><?php echo get_the_author_meta("display_name", $activechat->user); ?> </p></div><p class="message">'+a+'</p></div>');
                                
                                document.getElementById('chat').scrollTop = 9999999;
                            }
                            setTimeout("getData()", 5000);
                        }
                    }
                });
            }    
        </script>
        <?php
        endif;
}
else
{
    echo "<p>Det finns tyvärr inga chattar att visa. </p>";
}
?>