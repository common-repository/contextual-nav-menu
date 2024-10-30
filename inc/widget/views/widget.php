<?php

// Block direct requests
defined( 'ABSPATH' ) or die( '-1' );

$title = apply_filters( 'widget_title', $widget_title ); 
?>
<?php print $before_widget; ?>

<?php 
if ( ! empty( $title ) ) :

    print $before_title . $title . $after_title;
    
endif;
?>

<?php print $nav_menu; ?>

<?php print $after_widget;