<?php

/**

 * Twenty Twelve functions and definitions

 *

 * Sets up the theme and provides some helper functions, which are used

 * in the theme as custom template tags. Others are attached to action and

 * filter hooks in WordPress to change core functionality.

 *

 * When using a child theme (see http://codex.wordpress.org/Theme_Development and

 * http://codex.wordpress.org/Child_Themes), you can override certain functions

 * (those wrapped in a function_exists() call) by defining them first in your child theme's

 * functions.php file. The child theme's functions.php file is included before the parent

 * theme's file, so the child theme functions would be used.

 *

 * Functions that are not pluggable (not wrapped in function_exists()) are instead attached

 * to a filter or action hook.

 *

 * For more information on hooks, actions, and filters, @link http://codex.wordpress.org/Plugin_API

 *

 * @package WordPress

 * @subpackage Twenty_Twelve

 * @since Twenty Twelve 1.0

 */





if ( ! function_exists( 'twentytwelve_content_nav' ) ) :

/**

 * Displays navigation to next/previous pages when applicable.

 *

 * @since Twenty Twelve 1.0

 */

function twentytwelve_content_nav( $html_id ) {

    global $wp_query;



    $html_id = esc_attr( $html_id );



    if ( $wp_query->max_num_pages > 1 ) : ?>

        <nav id="<?php echo $html_id; ?>" class="navigation" role="navigation">

            <h3 class="assistive-text"><?php _e( 'Post navigation', 'twentytwelve' ); ?></h3>

            <!--<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'twentytwelve' ) ); ?></div>

            <div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'twentytwelve' ) ); ?></div>-->

            <?php wp_pagenavi(); ?>

        </nav><!-- #<?php echo $html_id; ?> .navigation -->

    <?php endif;

}

endif;





function favicon_link() {

    echo '<link rel="shortcut icon" type="image/x-icon" href="/favicon48.ico" />' . "\n";
    echo '<link rel="icon" href="/favicon48.ico" type="image/x-icon">' . "\n";
    echo '<link rel="apple-touch-icon-precomposed" href="/apple-touch-icon.png" />' . "\n";

}

add_action( 'wp_head', 'favicon_link' );


//add pre button
add_action('admin_print_footer_scripts','eg_quicktags');
function eg_quicktags() {
?>
<script type="text/javascript" charset="utf-8">
QTags.addButton( 'eg_pre', 'pre','<pre>', '</pre>', 'q' );
</script>
<?php
}


//add highlightjs
function add_highlightjs(){
    echo '<link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/highlight.js/8.1/styles/default.min.css">' . "\n";
    echo '<script src="http://cdnjs.cloudflare.com/ajax/libs/highlight.js/8.1/highlight.min.js"></script>' . "\n";
    echo '<script>hljs.initHighlightingOnLoad();</script>' . "\n";
}
add_action("wp_footer","add_highlightjs");


function cfxy_widgets_init() {
    if ( !is_blog_installed() )
        return;
  
    register_widget('CFXY_Widget_Blog_Stat');  
  
    do_action('widgets_init');
}
  
add_action('init', 'cfxy_widgets_init', 1);
  
function insert_visitors() {
    global $wpdb ;
    static $fOk = FALSE;
      
    $data_array = array(
        'meta_id' => 110000,
        'post_id' => 110000,
        'meta_key' => 'Visitors',
        'meta_value' => '0'
    );
      
    $format_array = array(
        '%d', '%d', '%s', '%s'
    );
  
    $wpdb->insert($wpdb->postmeta, $data_array, $format_array) ;
}
  
function update_visitors() {
    global $wpdb ;
      
    $visitors = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE meta_id = 110000"));
      
    $visitors++;
  
    $wpdb->update($wpdb->postmeta, array('meta_value' => $visitors), array('meta_id' => 110000), array('%s'), array('%d')) ;
}
  
function get_visitors() {
    global $wpdb;
      
    insert_visitors();
    update_visitors();
    $visitors = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE meta_id = 110000"));
  
    echo $visitors;
}
  
function get_totalcomments() {
    global $wpdb;
    $comments = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments");
    echo $comments ;
}
  
function get_totallinks() {
    global $wpdb;
    $links = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->links WHERE link_visible = 'Y'");
    echo $links ;
}
  
function get_totaldays($date) {
    $days = floor((time()-strtotime($date))/86400);
    echo $days ;
}
  
function get_totalposts() {
    $posts = wp_count_posts()->publish;
    echo $posts ;
}
  
function get_totaltags() {
    $tags = wp_count_terms('post_tag');
    echo $tags ;
}
  
  
class CFXY_Widget_Blog_Stat extends WP_Widget
{
    function __construct()
    {
        $widget_ops = array('classname' => 'blog-stat', 'description' => __( "The blog statistics on your site") );
        parent::__construct('cfxy_blog_stat', __('网站统计'), $widget_ops);
        $this->alt_option_name = 'blog-stat';
  
        add_action( 'save_post', array($this, 'flush_widget_cache') );
        add_action( 'deleted_post', array($this, 'flush_widget_cache') );
        add_action( 'switch_theme', array($this, 'flush_widget_cache') );
    }
  
    function widget($args, $instance)
    {
        $cache = wp_cache_get('widget_blog_stat', 'widget');
  
        if ( !is_array($cache) )
            $cache = array();
  
        if ( ! isset( $args['widget_id'] ) )
            $args['widget_id'] = $this->id;
  
        if ( isset( $cache[ $args['widget_id'] ] ) ) {
            echo $cache[ $args['widget_id'] ];
            return;
        }
  
        ob_start();
        extract($args);
  
        $title = apply_filters('widget_title', empty($instance['title']) ? __('网站统计') : $instance['title'], $instance, $this->id_base);
        $time = apply_filters('Time', empty($instance['time']) ? __('2013-01-01') : $instance['time'], $instance, $this->id_base);
        $posts = apply_filters('posts', empty($instance['posts']) ? __('　文章总数：') : $instance['posts'], $instance, $this->id_base);
        $comments = apply_filters('comments', empty($instance['comments']) ? __('　评论总数：') : $instance['comments'], $instance, $this->id_base);
        $visitors = apply_filters('visitors', empty($instance['visitors']) ? __('　浏览总数：') : $instance['visitors'], $instance, $this->id_base);
        $tags = apply_filters('tags', empty($instance['tags']) ? __('　标签个数：') : $instance['tags'], $instance, $this->id_base);
        $links = apply_filters('links', empty($instance['links']) ? __('　友情链接：') : $instance['links'], $instance, $this->id_base);
        $date = apply_filters('date', empty($instance['date']) ? __('　建站日期：') : $instance['date'], $instance, $this->id_base);
        $days = apply_filters('days', empty($instance['days']) ? __('　运行天数：') : $instance['days'], $instance, $this->id_base);
  
        $show_posts = isset( $instance['show_posts'] ) ? $instance['show_posts'] : false;
        $show_comments = isset( $instance['show_comments'] ) ? $instance['show_comments'] : false;
        $show_visitors = isset( $instance['show_visitors'] ) ? $instance['show_visitors'] : false;
        $show_links = isset( $instance['show_links'] ) ? $instance['show_links'] : false;
        $show_tags = isset( $instance['show_tags'] ) ? $instance['show_tags'] : false;
        $show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : false;
        $show_days = isset( $instance['show_days'] ) ? $instance['show_days'] : false;
  
        ?>
            <?php echo $before_widget; ?>
            <?php if ( $title ) echo $before_title . $title . $after_title; ?>   
            <ul>
                <?php if ( $show_posts ) : ?>
                    <li><?php echo $posts; ?><?php get_totalposts(); ?></li>
                <?php endif; ?>
                                      
                <?php if ( $show_comments ) : ?>
                    <li><?php echo $comments; ?><?php get_totalcomments(); ?></li> 
                <?php endif; ?>
                                      
                <?php if ( $show_visitors ) : ?>
                    <li><?php echo $visitors; ?><?php  get_visitors();  ?></li>
                <?php endif; ?>  
                              
                <?php if ( $show_tags ) : ?>
                    <li><?php echo $tags; ?><?php get_totaltags(); ?></li>  
                <?php endif; ?>
                  
                <?php if ( $show_links ) : ?>
                    <li><?php echo $links; ?><?php get_totallinks(); ?></li>
                <?php endif; ?>
                  
                <?php if ( $show_date ) : ?>
                    <li><?php echo $date; ?><?php echo $time; ?></li>
                <?php endif; ?>  
                              
                <?php if ( $show_days ) : ?>
                    <li><?php echo $days; ?><?php get_totaldays($time); ?></li>
                <?php endif; ?>
            </ul>
            <?php echo $after_widget; ?>
        <?php
  
        $cache[$args['widget_id']] = ob_get_flush();
        wp_cache_set('widget_blog_stat', $cache, 'widget');
    }
      
    function update( $new_instance, $old_instance )
    {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['time'] = strip_tags($new_instance['time']);
          
        $instance['posts'] = strip_tags($new_instance['posts']);
        $instance['comments'] = strip_tags($new_instance['comments']);
        $instance['visitors'] = strip_tags($new_instance['visitors']);     
        $instance['tags'] = strip_tags($new_instance['tags']);
        $instance['links'] = strip_tags($new_instance['links']);
        $instance['date'] = strip_tags($new_instance['date']);
        $instance['days'] = strip_tags($new_instance['days']);
          
        $instance['show_posts'] = (bool) ($new_instance['show_posts']);
        $instance['show_comments'] = (bool) ($new_instance['show_comments']);      
        $instance['show_visitors'] = (bool) ($new_instance['show_visitors']);  
        $instance['show_links'] = (bool) ($new_instance['show_links']);
        $instance['show_tags'] = (bool) ($new_instance['show_tags']);
        $instance['show_date'] = (bool) $new_instance['show_date'];
        $instance['show_days'] = (bool) $new_instance['show_days'];
                  
        $this->flush_widget_cache();
  
        $alloptions = wp_cache_get( 'alloptions', 'options' );
        if ( isset($alloptions['blog-stat']) )
            delete_option('blog-stat');
  
        return $instance;
    }
  
    function flush_widget_cache()
    {
        wp_cache_delete('widget_blog_stat', 'widget');
    }
  
    function form( $instance )
    {
        $title  = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
        $time   = isset( $instance['time'] ) ? esc_attr( $instance['time'] ) : '';
          
        $posts  = isset( $instance['posts'] ) ? esc_attr( $instance['posts'] ) : '';
        $comments  = isset( $instance['comments'] ) ? esc_attr( $instance['comments'] ) : '';
        $visitors  = isset( $instance['visitors'] ) ? esc_attr( $instance['visitors'] ) : '';      
        $tags   = isset( $instance['tags'] ) ? esc_attr( $instance['tags'] ) : '';
        $links  = isset( $instance['links'] ) ? esc_attr( $instance['links'] ) : '';
        $date   = isset( $instance['date'] ) ? esc_attr( $instance['date'] ) : '';
        $days  = isset( $instance['days'] ) ? esc_attr( $instance['days'] ) : '';
          
        $show_posts = isset( $instance['show_posts'] ) ? esc_attr( $instance['show_posts'] ) : '';
        $show_comments = isset( $instance['show_comments'] ) ? esc_attr( $instance['show_comments'] ) : '';
        $show_visitors = isset( $instance['show_visitors'] ) ? esc_attr( $instance['show_visitors'] ) : '';
        $show_links = isset( $instance['show_links'] ) ? esc_attr( $instance['show_links'] ) : '';     
        $show_tags = isset( $instance['show_tags'] ) ? esc_attr( $instance['show_tags'] ) : '';
        $show_date = isset( $instance['show_date'] ) ? esc_attr( $instance['show_date'] ) : '';
        $show_days = isset( $instance['show_days'] ) ? esc_attr( $instance['show_days'] ) : '';
          
        ?>
            <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widgets 标题：（如 “博客统计”）' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>
              
            <p><label for="<?php echo $this->get_field_id( 'time' ); ?>"><?php _e( '建站日期：（格式 “2013-01-01”）' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'time' ); ?>" name="<?php echo $this->get_field_name( 'time' ); ?>" type="text" value="<?php echo $time; ?>" /></p> 
              
            <p>可自定义各统计项标题：（空则为默认）</p>
              
            <p>
                <input class="checkbox" type="checkbox" <?php checked( $show_posts ); ?> id="<?php echo $this->get_field_id( 'show_posts' ); ?>" name="<?php echo $this->get_field_name( 'show_posts' ); ?>" />
                <label for="<?php echo $this->get_field_id( 'posts' ); ?>"><?php _e('&nbsp;"文章总数："&nbsp;==＞   ' ); ?></label>
                <input id="<?php echo $this->get_field_id( 'posts' ); ?>" name="<?php echo $this->get_field_name( 'posts' ); ?>" type="text" value="<?php echo $posts; ?>"  size="10" />
            </p>
                          
            <p>
                <input class="checkbox" type="checkbox" <?php checked( $show_comments ); ?> id="<?php echo $this->get_field_id( 'show_comments' ); ?>" name="<?php echo $this->get_field_name( 'show_comments' ); ?>" />         
                <label for="<?php echo $this->get_field_id( 'comments' ); ?>"><?php _e( '&nbsp;"评论总数："&nbsp;==＞   ' ); ?></label>
                <input id="<?php echo $this->get_field_id( 'comments' ); ?>" name="<?php echo $this->get_field_name( 'comments' ); ?>" type="text" value="<?php echo $comments; ?>"  size="10" />
            </p>
                          
            <p>
                <input class="checkbox" type="checkbox" <?php checked( $show_visitors ); ?> id="<?php echo $this->get_field_id( 'show_visitors' ); ?>" name="<?php echo $this->get_field_name( 'show_visitors' ); ?>" />         
                <label for="<?php echo $this->get_field_id( 'visitors' ); ?>"><?php _e( '&nbsp;"浏览总数："&nbsp;==＞   ' ); ?></label>
                <input id="<?php echo $this->get_field_id( 'visitors' ); ?>" name="<?php echo $this->get_field_name( 'visitors' ); ?>" type="text" value="<?php echo $visitors; ?>"  size="10" />
            </p>
              
            <p>
                <input class="checkbox" type="checkbox" <?php checked( $show_tags ); ?> id="<?php echo $this->get_field_id( 'show_tags' ); ?>" name="<?php echo $this->get_field_name( 'show_tags' ); ?>" />         
                <label for="<?php echo $this->get_field_id( 'tags' ); ?>"><?php _e( '&nbsp;"标签个数："&nbsp;==＞   ' ); ?></label>
                <input id="<?php echo $this->get_field_id( 'tags' ); ?>" name="<?php echo $this->get_field_name( 'tags' ); ?>" type="text" value="<?php echo $tags; ?>"  size="10" />
            </p>
                          
            <p>
                <input class="checkbox" type="checkbox" <?php checked( $show_links ); ?> id="<?php echo $this->get_field_id( 'show_links' ); ?>" name="<?php echo $this->get_field_name( 'show_links' ); ?>" />
                <label for="<?php echo $this->get_field_id( 'links' ); ?>"><?php _e( '&nbsp;"友情链接："&nbsp;==＞   ' ); ?></label>
                <input id="<?php echo $this->get_field_id( 'links' ); ?>" name="<?php echo $this->get_field_name( 'links' ); ?>" type="text" value="<?php echo $links; ?>"  size="10" />
            </p>
                          
            <p>
                <input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />         
                <label for="<?php echo $this->get_field_id( 'date' ); ?>"><?php _e( '&nbsp;"建站日期："&nbsp;==＞   ' ); ?></label>
                <input id="<?php echo $this->get_field_id( 'date' ); ?>" name="<?php echo $this->get_field_name( 'date' ); ?>" type="text" value="<?php echo $date; ?>"  size="10" />
            </p>
                          
            <p>
                <input class="checkbox" type="checkbox" <?php checked( $show_days ); ?> id="<?php echo $this->get_field_id( 'show_days' ); ?>" name="<?php echo $this->get_field_name( 'show_days' ); ?>" />
                <label for="<?php echo $this->get_field_id( 'days' ); ?>"><?php _e( '&nbsp;"运行天数："&nbsp;==＞   ' ); ?></label>
                <input id="<?php echo $this->get_field_id( 'days' ); ?>" name="<?php echo $this->get_field_name( 'days' ); ?>" type="text" value="<?php echo $days; ?>"  size="10" />
            </p>
              
        <?php
    }
}