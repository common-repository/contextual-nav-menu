
<p>
    <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'cnm' ) ?></label>
    <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
</p>
<p>
    <label for="<?php echo $this->get_field_id( 'nav_menu' ); ?>"><?php _e( 'Select Menu:', 'cnm' ); ?></label>
    <select id="<?php echo $this->get_field_id( 'nav_menu' ); ?>" name="<?php echo $this->get_field_name( 'nav_menu' ); ?>">
    <?php foreach( $menus as $menu ) :?>
        <option <?php selected( $instance['nav_menu'], $menu->term_id, true ) ?> value="<?php print $menu->term_id; ?>"><?php print $menu->name; ?></option>
    <?php endforeach;?>
    </select>
</p>
<p>
    <label for="<?php echo $this->get_field_id( 'start_depth' ); ?>"><?php _e( 'Starting depth:', 'cnm' ); ?></label>
    <input id="<?php echo $this->get_field_id( 'start_depth' ); ?>" name="<?php echo $this->get_field_name( 'start_depth' ); ?>" type="text" value="<?php echo $instance['start_depth']; ?>" size="3" />
</p>
<p>
    <label for="<?php echo $this->get_field_id( 'depth' ); ?>"><?php _e( 'How many levels to display:', 'cnm' ); ?></label>
    <select name="<?php echo $this->get_field_name( 'depth' ); ?>" id="<?php echo $this->get_field_id( 'depth' ); ?>" class="widefat">
        <option value="0"<?php selected( $instance['depth'], 0 ); ?>><?php _e( 'Unlimited depth', 'cnm' ); ?></option>
        <option value="2"<?php selected( $instance['depth'], 2 ); ?>><?php _e( '2 levels deep', 'cnm' ); ?></option>
        <option value="3"<?php selected( $instance['depth'], 3 ); ?>><?php _e( '3 levels deep', 'cnm' ); ?></option>
        <option value="4"<?php selected( $instance['depth'], 4 ); ?>><?php _e( '4 levels deep', 'cnm' ); ?></option>
        <option value="5"<?php selected( $instance['depth'], 5 ); ?>><?php _e( '5 levels deep', 'cnm' ); ?></option>
    </select>
<p>
