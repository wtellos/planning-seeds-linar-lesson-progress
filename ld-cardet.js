jQuery(document).ready(function($){

    //On click activity
    $( "body" ).on( "click", ".return-rise-button",function(event){
        event.preventDefault();
        UIkit.modal('#modal-generic-topics').hide();
        $('.cardet-content-here').html('');
    })
    $( "body" ).on( "click", ".cardet-topics  li a", function(e){
        e.preventDefault();
    })


    //On click activity
    $( "body" ).on( "click", ".cardet-topics  li", function(){
        console.log('clicked');
        $topic_type = $(this).attr('data-topic-type');
        $topic_id = $(this).attr('data-topic-id');
        $lesson_id = $(this).attr('data-lesson-id');

        if ($topic_type == 'quiz') {
            $topic_link = $(this).attr('data-topic-link');
        }
        else {
            $topic_link = $('a', this).attr('href');
        }

        
        //Open link in new tab and complete activity.
        if ($topic_type == 'PDF' || $topic_type == 'Word'  || $topic_type == 'PowerPoint'  || $topic_type == 'Link' || $topic_type == 'Video' ) {
            window.open($topic_link, '_blank').focus();
            complete_activity($topic_id, $lesson_id);
        }
        else if ($topic_type == 'Flipbook') {
            window.location.href = $topic_link;            
        }
        else if ($topic_type == 'quiz') {
            UIkit.modal('#'+ $topic_link ).show();
        }
        //Open modal for other activities
        else {
            $('.return-rise-button').removeClass("now-active");
            UIkit.modal('#modal-generic-topics').show();
            $('.cardet-content-here').html('<iframe class="iframe-' + $topic_type +'" src="' + $topic_link +'" style="width:100%;height:100%;"></iframe>');

            //storyline completion
            if ($topic_type == 'Storyline') {
                //Storyline completion
                $('.cardet-modal-spinner').css('opacity','1');
                storyline_load_completion($topic_id, $lesson_id);
            }
            //Storyline end
            
            //rise completion
            if ($topic_type == 'SCORM') {
                //rise completion
                $('.cardet-modal-spinner').css('opacity','1');
                rise_load_completion($topic_id, $lesson_id);
            }
            //rise end
        }
    }) 

    //On load activity - Flipbook completion
    $(document).ready(function() {
        var $Flipbook_button = $("a.complete-activity-button");

        if ($Flipbook_button.length > 0) {
             $topic_type = $Flipbook_button.attr('data-topic-type');
             $topic_id = $Flipbook_button.attr('data-topic-id');
             $lesson_id = $Flipbook_button.attr('data-lesson-id');

            complete_activity($topic_id, $lesson_id);

        }
    });

    //Complete activity function
    function complete_activity($topic_id, $lesson_id) {
        $('.cardet-modal-spinner').css('opacity','1');
        $('.ajax_content').css('opacity','0');
        $lesson_link = $(this).attr('data-lesson-link');
        $.ajax(
                {
                    type: "get",
                    data: {
                        action: 'completeLD',
                        topic_id: $topic_id,
                        lesson_id: $lesson_id
                    },
                    dataType: "html",
                    url: my_ajax_object.ajax_url,
                    complete: function (msg) {
                        //UIkit.modal('#modal-generic-topics').hide();
                        //console.log(msg.responseText);
                        $('.ajax_content').html(msg.responseText);
                        //$('.cardet-content-here').html('');
                        $('.cardet-modal-spinner').css('opacity','0');
                        $('.ajax_content').css('opacity','1');
                        $('.return-rise-button').addClass("now-active");

                        $('.return-rise-button').addClass("now-active");
                    }
                });
    }
    
    //Storyline completion
    function storyline_load_completion($topic_id, $lesson_id) {
          $(".iframe-Storyline").on("load", function(){
                $('.cardet-modal-spinner').css('opacity','0');
                $(".iframe-Storyline").css('opacity','1');
                //storyline Load
                $(this).contents().on("click","div[data-acc-text*='EXIT'], div[data-acc-text*='exit'], div[data-acc-text*='complete'], div[data-acc-text*='COMPLETE']", function(event){
                event.preventDefault();
                $(".iframe-Storyline").css('opacity','0');
                complete_activity($topic_id, $lesson_id);
                UIkit.modal('#modal-generic-topics').hide();
                $('.cardet-content-here').html('');
                })
            }) 
    }
    
    //RISE completion
    function rise_load_completion($topic_id, $lesson_id) {
        $(".iframe-SCORM").on("load", function(){
        $('.cardet-modal-spinner').css('opacity','0');
        $(".iframe-SCORM").css('opacity','1');
        console.log('load');
        $loltracker = ".nav-sidebar-header";
        $(this).contents().on("DOMSubtreeModified",$loltracker, function(){
	        var a = $(this).html();
		    if (a.includes('100')) {
                $loltracker = "dad";
				 $(".iframe-Rise").css('opacity','0');
				 complete_activity($topic_id, $lesson_id);
		    }
        });
    })
    }
    
    
    //Refresh topics when modal quiz is closed
    // Variable with element that fire event
    var $slideItem = $('.quiz-modals');

    $slideItem.on('hide', function(){
        $('.ajax_content').css('opacity','0');
        $('.cardet-modal-spinner').css('opacity','1');
        $.ajax(
                {
                    type: "get",
                    data: {
                        action: 'ajaxtopics',
                        lesson_id: $lesson_id
                    },
                    dataType: "html",
                    url: my_ajax_object.ajax_url,
                    complete: function (msg) {
                        //UIkit.modal('#modal-generic-topics').hide();
                        //console.log(msg.responseText);
                        $('.ajax_content').html(msg.responseText);
                        //$('.cardet-content-here').html('');
                        $('.cardet-modal-spinner').css('opacity','0');
                        $('.ajax_content').css('opacity','1');

                    }
                });
    });
    
    // Certificate counter
    $('.cert-menu').append($('.cert-counter'));
})

