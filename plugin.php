<?php
/*
 * Plugin Name:       CARDET Learndash plugin
 * Description:       Keeps all the custom functions/filters/actions/shortcodes
 * Version:           1.0
 * Author:            CARDET
 * Author URI:        https://cardet.org
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 */
 
 
 if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
 
 // Disable LearnDash assets from loading
 // Latest stylesheets as of LearnDash 3.5

function dm_remove_wp_block_library_css(){
wp_dequeue_style( 'wp-block-library' );
wp_dequeue_style( 'learndash-front' );
}
add_action( 'wp_enqueue_scripts', 'dm_remove_wp_block_library_css' );



/* Shortcodes */

//Custom Breadcrumbs shortcode

add_shortcode('cardet_custom_breadcrumbs','cardet_custom_breadcrumbs');
function cardet_custom_breadcrumbs(){
   ob_start();
   
   $site_url = get_site_url();
   $site_home_text = cardet_home_breadcrumbs_string();
    if ( 'sfwd-courses' == get_post_type()) {
    $course_title = get_the_title();
       echo "<a href='$site_url'>$site_home_text</a><span class='cardet-breadcrumbs-divider'>/</span><span class=cardet-span-text-breadcrumbs>$course_title</span>";
    }

    else if ( 'sfwd-lessons'== get_post_type()) {
    $course_id = learndash_get_course_id();
    $course_title = get_the_title($course_id);
    $course_link = get_the_permalink($course_id);
    
    $lesson_title = get_the_title();
    
       echo "<a href='$site_url'>$site_home_text </a><span class='cardet-breadcrumbs-divider'>/</span><a href='$course_link'>$course_title </a><span class='cardet-breadcrumbs-divider'>/</span><span class=cardet-span-text-breadcrumbs>$lesson_title </span>";
    }



    
    else if ( 'sfwd-topic'== get_post_type()) {
    $course_id = learndash_get_course_id();
    $course_title = get_the_title($course_id);
    $course_link = get_the_permalink($course_id);
    $lesson_id = learndash_get_lesson_id(get_the_ID());
     
    $lesson_title = get_the_title($lesson_id);
    $lesson_link = get_the_permalink($lesson_id);

    $topic_title = get_the_title();
    
       echo "<a href='$site_url'>$site_home_text </a>
       <span class='cardet-breadcrumbs-divider'>/</span>
       <a href='$course_link'>$course_title </a>
       <span class='cardet-breadcrumbs-divider'>/</span>


       <a href='$lesson_link'>$lesson_title </a>
       <span class='cardet-breadcrumbs-divider'>/</span>

       <span class=cardet-span-text-breadcrumbs>$topic_title </span>";
    }
   
   return ob_get_clean();
}


//Return text

add_shortcode('cardet_return_shortcode','cardet_return_shortcode');
function cardet_return_shortcode(){
    ob_start();
        _e('Return');
    return ob_get_clean();
}
// True/false shortcode

add_shortcode('cardet_true_shortcode','cardet_true_shortcode');
function cardet_true_shortcode(){
    ob_start();
        _e('True');
    return ob_get_clean();
}

add_shortcode('cardet_false_shortcode','cardet_false_shortcode');
function cardet_false_shortcode(){
    ob_start();
        _e('false');
    return ob_get_clean();
}

function cardet_home_breadcrumbs_string(){
    ob_start();
        _e('Home');
    return ob_get_clean();
}

// COURSES STRING
add_shortcode('cardet_courses_string','cardet_courses_string');
function cardet_courses_string(){
    ob_start();
        _e('Courses');
    return ob_get_clean();
}

// Display certificates page - grid
add_shortcode('cardet_display_certificates_grid','cardet_display_certificates_grid');
function cardet_display_certificates_grid(){
    ob_start();
    
         $args = array(  
        'post_type' => 'sfwd-courses',
        'post_status' => 'publish',
        'posts_per_page' => -1, 
        );

        $loop = new WP_Query( $args ); 
        
        echo '<div class="uk-child-width-1-1@m uk-child-width-1-1@s uk-flex uk-flex-center" uk-grid uk-scrollspy="target: > a; cls: uk-animation-fade; delay: 300">';
            
        while ( $loop->have_posts() ) : $loop->the_post(); 
        if (!is_user_logged_in()) return;
        if (learndash_course_completed( get_current_user_id(),  get_the_ID() )){
            $certificate_link = learndash_get_course_certificate_link( get_the_ID(),  get_current_user_id());
            $output = '<div class="unlocked-certificate"><a class="uk-margin-small-bottom uk-padding-small uk-button uk-button-danger uk-button-certificate" target="_blank" href="'.$certificate_link.'"><span uk-icon="unlock" class="uk-text-default"></span> <span class="uk-text-default">' . _x('Download the Certificate!', 'cardet-ld-translations') . '</span></a></div>';
        }
        else {
            $output = '<button class="uk-margin-small-bottom uk-padding-small uk-button uk-button-primary locked-certificate" disabled> <span uk-icon="lock" class="uk-text-default"></span> <span class="uk-text-default">' . _x('Certificate locked', 'cardet-ld-translations') . '</span></button>';
        }
        echo "<div class='cardet-course-grid'>
            <div class='DDcardet-course-grid-image uk-text-center'><img
                    class='uk-width-small uk-display-block uk-margin-auto' src='". get_the_post_thumbnail_url() ."'>
            </div>
            <div class='uk-text-center cardet-course-header uk-hidden'>
                <h2 class='uk-h2'>" . get_the_title(). "</h2>
            </div>
            <div class='uk-text-center uk-margin-top'>" . $output . "</div>
        </div>";
        endwhile;

        echo '</div>';

        wp_reset_postdata(); 
    
    return ob_get_clean();
}

// Display completed courses counter 
add_shortcode('cardet_display_certificate_counter','cardet_display_certificate_counter');
function cardet_display_certificate_counter(){
    ob_start();
    $counter = 0;
    $total_courses = 0;
    
         $args = array(  
        'post_type' => 'sfwd-courses',
        'post_status' => 'publish',
        'posts_per_page' => -1, 
        );

        $loop = new WP_Query( $args ); 
        
    
        while ( $loop->have_posts() ) : $loop->the_post(); 
        $total_courses++;
        if (!is_user_logged_in()) return;
        if (learndash_course_completed( get_current_user_id(),  get_the_ID() )){
           $counter++; 
        }
        endwhile;

        echo "<div class='cert-counter'>$counter/$total_courses</div>";

        wp_reset_postdata(); 
    
    return ob_get_clean();
}

// Display courses 
add_shortcode('cardet_display_courses','cardet_display_courses');
function cardet_display_courses(){
    ob_start();
    
         $args = array(  
        'post_type' => 'sfwd-courses',
        'post_status' => 'publish',
        'posts_per_page' => -1, 
        );

        $loop = new WP_Query( $args ); 
        
        echo '<div class="uk-child-width-1-1@m uk-child-width-1-1@s uk-flex uk-flex-center" uk-grid uk-scrollspy="target: > a; cls: uk-animation-fade; delay: 300">';
            
        while ( $loop->have_posts() ) : $loop->the_post(); 
        if (is_user_logged_in()) {
          $course_access = get_field('limit_by_target_group', get_the_ID());
          $course_access_array = explode(",", $course_access);
          if ($course_access != 'All' &&  !in_array(get_user_meta(get_current_user_id(), 'role', true),$course_access_array)) continue;
          $course_link = get_the_permalink();
        }
        else $course_link = "#login";
        
        
            echo "<div><a class='cardet-course-grid' href='" . $course_link . "'><div class='cardet-course-grid-image uk-text-center'><img src='". get_the_post_thumbnail_url() ."'></div><div class='uk-text-center cardet-course-header'><h3 class='uk-heading-small uk-font-secondary uk-margin-top uk-margin-remove-bottom'>" . get_the_title(). "</h2></div></a></div>";
        endwhile;

        echo '</div>';

        wp_reset_postdata(); 
    
    return ob_get_clean();
}

// Display lessons 
add_shortcode('cardet_display_lessons','cardet_display_lessons');
function cardet_display_lessons(){
    ob_start();
        $course_id = learndash_get_course_id();
        if (get_field('single_or_multi_module', $course_id) == 'single') single_lesson_function($course_id);
        else multi_lesson_function($course_id);
    return ob_get_clean();
}



// Display topics 
add_shortcode('cardet_display_topics_shortcode','cardet_display_topics_shortcode');
function cardet_display_topics_shortcode() {
    ob_start();
        $lesson_id = learndash_get_lesson_id();
        echo cardet_display_topics($lesson_id);
    return ob_get_clean();
}

//Display Modal Quizzes
add_shortcode('cardet_display_quizmodals_shortcode','cardet_display_quizmodals_shortcode');
function cardet_display_quizmodals_shortcode() {
    ob_start();
    $course_id = learndash_get_course_id();
    if (get_field('single_or_multi_module', $course_id) == 'single'){
      $lessons = learndash_get_lesson_list( $course_id );
      foreach ($lessons as $lesson) {
        $quizes = learndash_course_get_quizzes( $course_id,  $lesson->ID);
        foreach ($quizes as $quiz){ ?>
          <div id="quiz-modal<?php echo $quiz->ID; ?>" class="quiz-modals uk-flex-top" uk-modal>
              <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">
              <button class="uk-modal-close-default" type="button" uk-close></button>
              <?php 
           $short = '[ld_quiz quiz_id="'.$quiz->ID.'"]';
  
              echo do_shortcode($short);
       ?>
              </div>
          </div>
      <?php }
      }
    }
    else {
      $lesson_id = learndash_get_lesson_id();
      $quizes = learndash_course_get_quizzes( $course_id,  $lesson_id);
      foreach ($quizes as $quiz){ ?>
          <div id="quiz-modal<?php echo $quiz->ID; ?>" class="quiz-modals uk-flex-top" uk-modal>
              <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">
              <button class="uk-modal-close-default" type="button" uk-close></button>
              <?php 
           $short = '[ld_quiz quiz_id="'.$quiz->ID.'"]';
  
              echo do_shortcode($short);
       ?>
              </div>
          </div>
      <?php }
    }
    
    return ob_get_clean();
    
}

/////////////////////// FORMINATOR Form submission check and Shortcode ///////////////////////
    function form_submission_check(){
        // Ensure the user is logged in
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            global $wpdb;

            // Table name where Forminator submissions are stored
            $table_name = $wpdb->prefix . 'frmt_form_entry_meta';

            // Query to check if the user has any submission
            $query = $wpdb->prepare("SELECT entry_id FROM {$wpdb->prefix}frmt_form_entry_meta WHERE meta_value = %s AND meta_key = 'hidden-1'", $user_id);

            $form_submission_count = $wpdb->get_var($query);

            if ($form_submission_count > 0) {
                return 1;
            } else {
                echo '<p class="th--message">You need to answer the Pre-Questionnaire before accessing the modules: <a class="uk-button uk-button-primary uk-flex go-to-questionnaire" href="https://elearning.planningseeds.eu/pre-questionnaire/" target="_blank"><span>Go to the Pre-Questionnaire</span></a></p>';


            }
        } else {
            return '<p class="lead">You need to log in to access the Modules</p>';
        }
    }
    //add_shortcode('hide_form_to_user', 'form_submission_check');

/////////////////////// END FORMINATOR ///////////////////////

/* Functions */


function multi_lesson_function($course_id) {
    //Display lessons function
        if (!is_user_logged_in()) {
            echo '<p class="uk-text-lead">You need to <a href="#login">log in</a> to access the modules</p>';
            //$lesson_completion_class = 'cardet-lesson-not-completed';
        }
    
        $user_id = get_current_user_id();
        $lessons = learndash_get_lesson_list( $course_id );
        $grid_classes = get_field('multiple_lessons_grid_classes', $course_id);
        echo '<div class="'.$grid_classes.'" uk-grid uk-scrollspy="target: > a; cls: uk-animation-fade; delay: 300">';
        $isFirstLesson = $lessons[0]->ID; // Gets the first element of the array
        $prevLessonId = null;
     //ADDED
        $form_result = form_submission_check();
        $lesson_link = "#0";
    
        foreach ($lessons as $index => $lesson){
            $lesson_image = get_the_post_thumbnail($lesson->ID);
            $lesson_title = $lesson->post_title;
            $isComplete = learndash_is_lesson_complete( $user_id,  $lesson->ID );

            // LESSON COMPLETION PERCENTAGE
            //$course_id = learndash_get_course_id();
            // $lesson_progress = learndash_get_lesson_progress( $user_id, $course_id, $lesson->ID );
            // print_r($lesson_progress);
            //     //Returns: Array ( [percentage] => 0 [completed] => 0 [total] => 0 )

            // if (isset($lesson_progress['percentage'])) {
            //     $completion_percentage = $lesson_progress['percentage'];
            // } else {
            //     $completion_percentage = 0;
            // }

        //ADDED        
            if ($isFirstLesson == $lesson->ID && $form_result == 1) {
                $lesson_link = get_post_permalink($lesson->ID);
            }
            else {
                    $prevLessonId = $lessons[$index - 1]->ID; 
    
                    $isPrevCompleted = learndash_is_lesson_complete($user_id, $prevLessonId);
    
                    if ($isPrevCompleted){
                        $lesson_link = get_post_permalink($lesson->ID);
                    }
                    else {
                        $lesson_link = "#0";
                        $lesson_not_available = 'cardet-lesson-not-available';
                    }    
            }
     
            if ($isComplete) {
                $lesson_completion_class = 'cardet-lesson-completed'; 
            }
            else { $lesson_completion_class = 'cardet-lesson-not-completed'; }
    

        // Output the lesson with the progress bar
        echo "<div>
              
                <a class='cardet-lesson-grid uk-position-relative uk-animation-fade " . $lesson_completion_class . ' ' . $lesson_not_available . "' href='" . $lesson_link . "'>
                    <span class='$lesson_completion_class' uk-icon='check'></span>
                    <div class='cardet-lesson-grid-image uk-text-center uk-padding-small'>
                        <img src='" . get_the_post_thumbnail_url($lesson->ID) . "'>
                    </div>
                    <div class='DDuk-text-center uk-text-left cardet-lesson-header'>
                        <h2 class='uk-heading-medium uk-margin-remove'>" . $lesson_title . "</h2>
                        <div class='uk-hidden uk-margin-top cardet-lesson-bar'>
                            <div class='uk-hidden cardet-lesson-bar-inner'></div>
                            <div class='uk-hidden'> (". $completion_percentage . "% complete)</div>
                        </div>
                    </div>
                </a>
              </div>";
    }

    echo '</div>';
    
    }



function single_lesson_function($course_id) {
    $lessons = learndash_get_lesson_list( $course_id );
    $layout = get_field('single_lesson_layout', $course_id);
    ?>
    <div class="uk-margin-bottom cardet-lesson-bar">
        <div class="cardet-lesson-bar-inner"></div>
    </div>
    <div class="topics_whole_div uk-position-relative">
    <span style="z-index:1;" class="uk-position-center cardet-modal-spinner" uk-spinner="ratio: 4.5">
        </span><div class="topics_shortcode">
          <?php 
        if ($layout == 'Accordion'){
          echo '<ul class="cardet-accordion-layout" uk-accordion>';
          foreach ($lessons as $lesson):
            $lesson_title = get_the_title($lesson->ID);
            echo '<li>';
              echo "<a class='uk-accordion-title cardet-accordion-title' href>$lesson_title</a>";
                echo "<div class='uk-accordion-content cardet-accordion-content'>".cardet_display_topics($lesson->ID)."</div>";
              echo "</li>";
          endforeach;
          echo "</ul>";
        }
        else {
          foreach ($lessons as $lesson):
            $lesson_title = get_the_title($lesson->ID);
            echo "<h3 class='cardet-list-lesson-title'>$lesson_title</h3>";
            echo cardet_display_topics($lesson->ID); 
          endforeach;
        }  
        
        

        ?>
        </div></div>

<div id="modal-generic-topics" class="uk-modal-full uk-flex-top" uk-modal="">
<div class="uk-modal-dialog uk-modal-body  uk-flex uk-flex-center uk-flex-middle" style="height:100%"><button class="uk-modal-close-full uk-close-large" type="button" uk-close=""></button>
<div class="cardet-generic-modal-content uk-position-relative" style="height:100%; width:100%"><div class="cardet-content-here" style="position:relative;z-index:2;width:100%; height:100%" ></div><a class="uk-button uk-button-primary return-rise-button">[cardet_return_shortcode]</a><span style="z-index:1;" class="uk-position-center cardet-modal-spinner" uk-spinner="ratio: 4.5"></span></div>
</div>
</div>

    <?php echo cardet_display_quizmodals_shortcode();   
}


//Display topics function

function cardet_display_topics($lesson_id){
    $total_topics = 0;
    $completed_topics=0;
    $course_id = learndash_get_course_id($lesson_id);
    $user_id = get_current_user_id();
    $topics = learndash_get_topic_list($lesson_id);
    $lesson_attr = '';
    $lesson_classes = 'uk-list cardet-topics-list';
    $lesson_layout = get_field('lesson_layout', $lesson_id);

    if (get_field('lesson_layout', $lesson_id) == 'Grid'){
      $lesson_attr = 'uk-grid';
      $lesson_classes = get_field('lesson_grid_classes_layout', $lesson_id) . ' grid-lesson-topics';
      $output_topics = "<div class='cardet-topics $lesson_classes'$lesson_attr>";
    }

    else {
      $output_topics = "<ul class='cardet-topics $lesson_classes'$lesson_attr>";
    }


    //topics
    foreach ($topics as $topic) {
        $total_topics++;
        $topic_title = $topic->post_title;
        $topic_type = get_field('module_type', $topic->ID);
        $isComplete = learndash_is_topic_complete( $user_id,  $topic->ID );

        if (get_the_post_thumbnail_url($topic->ID)) $featured_image_url = get_the_post_thumbnail_url($topic->ID);
        else $featured_image_url = get_stylesheet_directory_uri() .'/default-icons/'. $topic_type .'.png';
        
        
        if ($topic_type == 'PDF' || $topic_type == 'Word' || $topic_type == 'PowerPoint') $topic_link = get_field('file-u', $topic->ID);
        else if ($topic_type == 'Link' || $topic_type == 'Video' ) $topic_link = get_field('acf-link', $topic->ID);
        else if ($topic_type == 'SCORM' || $topic_type == 'Storyline' ) $topic_link = get_field('scorm_url', $topic->ID);

        //// FLIPBOOK
        else if ($topic_type == 'Flipbook'){
           $topic_link = get_permalink($topic->ID);
            $topicClass .= ' on-click-complete';
        }
    
        
        //////////////////////////////////////////////////        
        
        if ($isComplete){ $topic_completion_class = 'cardet-topic-completed'; $completed_topics++; }
        else $topic_completion_class = 'cardet-topic-not-completed';
        
        //output
        if (get_field('lesson_layout', $lesson_id) == 'Grid') $output_topics .= '<div><li class="grid-topic-inner ' . $topic_completion_class .'" data-topic-id="'.$topic->ID .'" data-topic-type="'.$topic_type .'" data-topic-link="'.$topic_link .'" data-lesson-id="'.$lesson_id .'"><a href="'.$topic_link .'"><img class="cardet-topic-icon" src="' . $featured_image_url. '"><span class="cardet-topic-completion-icon" uk-icon="check"></span><h3 class="topic-grid-title">' . $topic_title .'</h3></a></li></div>';
        else $output_topics .= '<li class="' . $topic_completion_class .'" data-topic-id="'.$topic->ID .'" data-topic-type="'.$topic_type .'" data-topic-link="'.$topic_link .'" data-lesson-id="'.$lesson_id .'"><a href="'.$topic_link .'"><img class="cardet-topic-icon" src="' . $featured_image_url. '"><span class="cardet-topic-completion-icon" uk-icon="check"></span>' . $topic_title .'</a></li>';
    }
    //topics end
    //quizzes buttons
    $quizes = learndash_course_get_quizzes( $course_id,  $lesson_id);
    foreach ($quizes as $quiz){
        $total_topics++;
        $topic_title = $quiz->post_title;
        //$topic_type = get_field('module_type', $topic->ID);

        if (get_the_post_thumbnail_url($quiz->ID)) $featured_image_url = get_the_post_thumbnail_url($quiz->ID);
        else $featured_image_url = get_stylesheet_directory_uri() .'/default-icons/'.'quiz.png';

        $isComplete = learndash_is_quiz_complete( $user_id,  $quiz->ID, $course_id );
        
        if ($isComplete){ $topic_completion_class = 'cardet-topic-completed'; $completed_topics++; }
        else $topic_completion_class = 'cardet-topic-not-completed';
        if (get_field('lesson_layout', $lesson_id) == 'Grid') $output_topics .= '<div><li class="grid-topic-inner ' . $topic_completion_class .'" data-topic-id="'.$quiz->ID .'" data-topic-type="quiz" data-topic-link="quiz-modal'. $quiz->ID .'" data-lesson-id="'.$lesson_id .'"><img class="cardet-topic-icon" src="'.$featured_image_url.'"><span class="cardet-topic-completion-icon" uk-icon="check"></span><h3 class="topic-grid-title">' . $topic_title .'</h3></li></div>';
        else $output_topics .= '<li class="' . $topic_completion_class .'" data-topic-id="'.$quiz->ID .'" data-topic-type="quiz" data-topic-link="quiz-modal'. $quiz->ID .'" data-lesson-id="'.$lesson_id .'"><img class="cardet-topic-icon" src="'.$featured_image_url.'"><span class="cardet-topic-completion-icon" uk-icon="check"></span>' . $topic_title .'</li>';
    } //quizzes end
    $output_topics .= '</ul>';
    
    $completed_topics_percentage = $completed_topics / $total_topics * 100;
    if (get_field('single_or_multi_module', $course_id) == 'single'){
      $completed_topics = 0;
      $total_topics = 0;
      $lessons = learndash_get_lesson_list( $course_id );
      foreach ($lessons as $lesson ){
        $topics = learndash_get_topic_list($lesson->ID);
        $quizes = learndash_course_get_quizzes( $course_id,  $lesson->ID);
        foreach ($topics as $topic){
          $total_topics++;
          if (learndash_is_topic_complete( $user_id,  $topic->ID )) $completed_topics++;
        }
         foreach ($quizes as $quiz){
           if (learndash_is_quiz_complete( $user_id,  $quiz->ID, $course_id )) $completed_topics++; 
         }
      }
      $completed_topics_percentage = $completed_topics / $total_topics * 100;
    }
     $output_topics .= '<style>.cardet-lesson-bar-inner{width:'. $completed_topics_percentage .'%}</style>';
    return $output_topics;
    //echo '<div id="modal-center" class="uk-flex-top" uk-modal><div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical"><button class="uk-modal-close-default" type="button" uk-close></button><div class="cardet-generic-modal-content"><span class="cardet-modal-spinner" uk-spinner="ratio: 4.5"></span></div></div></div>';
}

// Flipbook shortcode - Return button
function flip_shortcode(){
    $topic_id = get_the_ID();
    $lesson_id = learndash_get_lesson_id($topic_id);
        $lesson_link = get_the_permalink($lesson_id);
        $topic_link = get_the_permalink($topic_id);

    ob_start();
        echo '<a class="uk-button uk-button-primary complete-activity-button" href="'. $lesson_link .'" data-topic-id="'.$topic_id .'" data-topic-type="Flipbook" data-topic-link="'. $topic_link .'" data-lesson-id="'.$lesson_id .'" data-lesson-link="'.$lesson_link .'">Return to Module</a>';
    return ob_get_clean();
}
add_shortcode('flip_shortcode', 'flip_shortcode');



/* AJAX */


/* AJAX PHP Function to complete lessons on click (applied for PDF/DOCX/PPT/LINK/VIDEO) */
function completeLearndashObj() {
	/* Parameted passed from $.Ajax call (This $ID = complete) */
	 $topic_id = $_GET['topic_id'];
	 $lesson_id = $_GET['lesson_id'];
     $course_id =  learndash_get_course_id($lesson_id);
   
    learndash_process_mark_complete(get_current_user_id(),$topic_id);
    if (get_field('single_or_multi_module', $course_id) == 'single') echo single_lesson_function($course_id);
    else echo cardet_display_topics($lesson_id);
	exit();
    
}
add_action( 'wp_ajax_completeLD', 'completeLearndashObj' );
add_action( 'wp_ajax_nopriv_completeLD', 'completeLearndashObj' );


/* Refresh topics - used for quiz modal close */


function cardet_ajax_topics() {
	
	 $lesson_id = $_GET['lesson_id'];

	echo cardet_display_topics($lesson_id);
	exit();
    
}
add_action( 'wp_ajax_ajaxtopics', 'cardet_ajax_topics' );
add_action( 'wp_ajax_nopriv_ajaxtopics', 'cardet_ajax_topics' );



  //CARDET BODY CLASS FOR COURSES AND MODULES
  if( function_exists('acf_add_local_field_group') ):

    acf_add_local_field_group(array(
        'key' => 'group_64fafbbfec521',
        'title' => 'Course/Lesson Body class',
        'fields' => array(
            array(
                'key' => 'field_64fafbc0bcb9e',
                'label' => 'Course/Module body class',
                'name' => 'course_body_class',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'maxlength' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'sfwd-courses',
                ),
            ),
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'sfwd-lessons',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
        'show_in_rest' => 0,
    ));
    
    endif;		


    //enter body class filter function

    function add_acf_body_class( $classes ) {
        global $post;
        if ( get_post_type() === 'sfwd-courses' || get_post_type() === 'sfwd-lessons' ) {  // Only apply to single posts
           $value = get_field( 'course_body_class' );
           if ( $value ) {
              $classes[] = $value; 
           }
        }
        return $classes;
     }
     add_filter( 'body_class', 'add_acf_body_class' );


     function custom_password_reset_form() {

        ob_start();
    
        if (isset($_POST['cprf_reset_password'])) {
            $user_login = sanitize_text_field($_POST['cprf_user_login']);
            $user = get_user_by('login', $user_login);
    
            if (!$user && is_email($user_login)) {
                $user = get_user_by('email', $user_login);
            }
    
            $user_meta = get_user_meta($user->ID);
    
            if ($user) {
    
                add_filter('allow_password_reset', function($allow, $user_id) use ($user) {
                  return $user->ID === $user_id ? true : $allow;
                }, 20, 2);
              
              $reset_key = get_password_reset_key($user);
    
              if (is_wp_error($reset_key)) {
                  return;
              }
    
              $site_name = get_bloginfo('name');
              $site_url = home_url();
              $reset_url = network_site_url("wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode($user->user_login) . "&wp_lang=en_US", 'login');
    
                $from_name = 'The ' . $site_name . ' Team';
                $from_email = 'wordpress@' . parse_url($site_url, PHP_URL_HOST);
              // Email headers
              $headers = array(
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . sanitize_text_field($from_name) . ' <' . sanitize_email($from_email) . '>',
            );
    
              $message = <<<EMAIL
                    Hello {$user->display_name},
                    <br><br>
                    You request password reset to the "{$site_name}". <br><br>
                    To reset your password click the link below or copy paste it in your browser.
                    <br><br>
                    {$reset_url}
                    <br><br>
                    If you did not request this, please ignore this email.
                    <br><br>
                    Best regards,
                    <br>
                    {$from_name}
                    EMAIL;
    
                    $subject = "Password reset request at {$site_name}";
              // Send the email
              if (wp_mail($user->user_email, $subject, $message, $headers)) {
                  echo '<div class="uk-alert-success"><p>A password reset link has been sent to your email address. Please check your <b>inbox</b> or <b>spam</b> folders.</p></div>';
              } else{
                  echo '<div class="uk-alert-alert"><p>Email not sent! Please contact the system administrator!</p></div>';
              }
          } else {
              echo '<div class="uk-alert-danger"><p>No user found with this username or email.</p></div>';
          }
          
        }
    
        ?>
        <form method="post">
            <label class="" for="cprf_user_login">Username or Email:</label>
            <input class="uk-input uk-form-width-medium"  type="text" name="cprf_user_login" id="cprf_user_login" required><br><br>
            <input class="uk-button uk-button-primary" type="submit" name="cprf_reset_password" value="Send Reset Link">
        </form>
        <?php
    
        return ob_get_clean();
    }
    add_shortcode('password_reset_form', 'custom_password_reset_form');


/**
 * LearnDash registration: Cloudflare Turnstile validation.
 *
 */

define('CF_TURNSTILE_SITE_KEY', '0x4AAAAAACpG_TE6H1GiZ0PM');
define('CF_TURNSTILE_SECRET_KEY', '0x4AAAAAACpG_XJJ5iAd2W5jKpOLWFGwR04');

add_action('plugins_loaded', function () {
    remove_action('registration_errors', 'cfturnstile_wp_register_check', 10, 3);
}, 20);

//  Validate the Turnstile token on form submission.
add_filter('registration_errors', function ($errors, $sanitized_user_login, $user_email) {

    static $checked = false;
    if ( $checked ) {
        return $errors;
    }
    $checked = true;

    $token = isset($_POST['cf-turnstile-response']) ? sanitize_text_field(wp_unslash($_POST['cf-turnstile-response'])) : '';

    if ( empty($token) ) {
        $errors->add('turnstile_missing', __('ERROR: Please verify that you are human.'));
        return $errors;
    }

    $secret = defined('CF_TURNSTILE_SECRET_KEY') ? CF_TURNSTILE_SECRET_KEY : get_option('cf_turnstile_secret_key');


    $resp = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
        'timeout' => 10,
        'body'    => [
            'secret'   => $secret,
            'response' => $token,
            'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '',
        ],
    ]);

    if ( is_wp_error($resp) ) {
        $errors->add('turnstile_http_error', __('ERROR: Human verification failed. Please try again.'));
        return $errors;
    }

    $body = wp_remote_retrieve_body($resp);
    $data = json_decode($body, true);


    if ( empty($data['success']) ) {
        $errors->add('turnstile_failed', __('ERROR: Please verify that you are human.'));
    }

    return $errors;
}, 10, 3);
