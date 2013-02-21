<?php
/*
Plugin Name: Single Post Widget
Description: Display single post from url on sidebar widget.
Author: Takayuki Miyauchi
Version: 0.4.0
Author URI: http://firegoby.jp/
Plugin URI: http://firegoby.jp/wp/single-post-widget
Domain Path: /languages
Text Domain: single-post-widget
*/


class SinglePostWidget extends WP_Widget {

    private $num = 5;
    private $domain = "single-post-widget";

    function __construct() {
      $widget_ops = array(
        'description' => __('Display single selected Post or Page.', $this->domain)
      );
      $control_ops = array('width' => 400, 'height' => 350);
      parent::__construct(
        false,
        __('Single Post', $this->domain),
        $widget_ops,
        $control_ops
      );
    }

    public function form($instance) {

      // outputs the options form on admin
      $postid = (isset($instance['postid'])) ? $instance['postid'] : '';
      $pid = $this->get_field_id('postid');
      $pf = $this->get_field_name('postid');
      $all = get_posts('numberposts=-1');



      if ( isset( $instance[ 'title' ] ) ) {
        $title = $instance[ 'title' ];
      } else {
        $title = __( 'Featured Post', $this->domain );
      }
      ?>
      <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
      </p>
      <?php
      echo '<p>';
      echo __("Select the post or page", $this->domain);
      echo "<br />";
      echo '<select class="widefat" id="'.$pid.'" name="'.$pf.'">';
      foreach ( $all as $post ) {
        $selected = ( $post->ID == $postid )? 'selected' : '';
        echo '<option value="'.$post->ID.'" '.$selected.'>'.$post->post_title.'</option>';
      }
      echo '</select>';
      echo '</p>';

      $sizes = get_intermediate_image_sizes();
      $size = (isset($instance['size']) && $instance['size']) ? $instance['size'] : '';
      $sfield = $this->get_field_id('size');
      $sfname = $this->get_field_name('size');
      echo '<p>';
      echo __('Image size:', $this->domain);
      echo '<br />';
      $op = '<option value="%s"%s>%s</option>';
      echo "<select class=\"widefat\" id=\"{$sfield}\" name=\"{$sfname}\">";
      printf($op, '', '', '');
      foreach ($sizes as $s) {
          if ($s === $size) {
              printf($op, $s, ' selected="selected"', $s);
          } else {
              printf($op, $s, '', $s);
          }
      }
      echo "</select>";
      echo '</p>';

    }

    public function update($new_instance, $old_instance) {
      // processes widget options to be saved
      return $new_instance;
    }

    public function widget($args, $instance) {
      $pid = null;
      if (isset($instance['postid']) && preg_match("/^[0-9]+$/", $instance['postid'])) {
          $pid = $instance['postid'];
      } elseif (isset($instance['postid']) && $instance['postid']) {
          $pid = url_to_postid($instance['postid']);
      }
      if (!$pid) {
          return '';
      }


      if ( isset( $instance['title'] ) ) {
        $title = apply_filters( 'widget_title', $instance['title'] );
      } else {
        $title = 'Featured Post';
      }

      echo $args['before_widget'];
      echo $args['before_title'];
      echo esc_html($title);
      echo $args['after_title'];

      $the_post = new WP_Query(
        array(
          'p' => $pid,
          'post_type'=> 'any'
        )
      );
      while($the_post->have_posts()) :
        $the_post->the_post();
        get_template_part( 'content', 'featured' );
      endwhile;

      echo $args['after_widget'];
    }

    private function template()
    {
        $html = '<div class="%class%">';
        $html .= '<div class="post-thumb"><a href="%post_url%">%post_thumb%</a></div>';
        $html .= '<div class="post-excerpt">%post_excerpt% <a href="%post_url%">&raquo; '.__('Read More', $this->domain).'</a></div>';
        $html .= '</div>';
        return apply_filters("single-post-widget-template", $html);
    }
}
// register Foo_Widget widget
add_action( 'widgets_init', create_function( '', 'register_widget( "SinglePostWidget" );' ) );


?>
