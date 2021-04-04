<?php
/***  Enqueue child & parent theme stylesheets ***/
function wp_stephenz_child_enqueue_style() {
        wp_register_style( 'childstyle', get_stylesheet_directory_uri() . '/style.css'  );
        wp_enqueue_style( 'childstyle' );
        wp_register_style( 'Custom-Styles', get_stylesheet_directory_uri() . '/custom-styles.css?v=2020'  );
        wp_enqueue_style( 'Custom-Styles');
	}
add_action( 'wp_enqueue_scripts', 'wp_stephenz_child_enqueue_style', 11);


add_action( 'init', 'add_slug_body_class' );
// Page Slug -> Body Class
    function add_slug_body_class( $classes ) {
        global $post;
        if ( isset( $post ) ) {
            $classes[] = $post->post_type . '-' . $post->post_name;
        }
        return $classes;
    }
add_filter( 'body_class', 'add_slug_body_class' );


add_action( 'init', 'add_category_body class' );
function add_category_to_single($classes) {
if (is_single() ) {
    global $post;
    foreach((get_the_category($post->ID)) as $category) {
    // add category slug to the $classes array
        $classes[] = $category->category_nicename;
    }
}
// return the $classes array
return $classes;
}
add_filter('body_class','add_category_body');


// Custom Widget Sidebar
function stephenz_widgets_init() {
    register_sidebar( array(
        'name'          => __( 'Extra Sidebar 1', 'CompanyName' ),
        'id'            => 'extra-sidebar-1',
        'description'   => __( 'Extra Sidebar Area 1', 'companyname' ),
	    'before_widget' => '<div id="%1$s" class="widget widget-container %2$s">',
	    'after_widget' => "</div>",
        'before_title'  => '<h3 class="title widget-title">',
        'after_title'   => '</h3>',
    ) );
}
add_action( 'widgets_init', 'stephenz_widgets_init', 11 );

/**
 * Sort by custom fields.
 * mt1 refers to meta_1, mt2 to meta_2 and mt3 to meta_3
 *
 * @param $orderby original order by string
 * @return custom order by string 
 */
function customorderby($orderby) {
    return 'mt1.meta_value, mt2.meta_value, mt3.meta_value ASC';
}

// Custom post type for TEAM Section
function create_team_posttype() {
// Set UI labels for Custom Post Type
    $labels = array(
        'name'                => _x( 'Team', 'Post Type General Name' ),
        'singular_name'       => _x( 'Team', 'Post Type Singular Name' ),
        'menu_name'           => __( 'Team Members' ),
        'parent_item_colon'   => __( 'Parent Team Member' ),
        'all_items'           => __( 'All Team Members' ),
        'view_item'           => __( 'View Team Member' ),
        'add_new_item'        => __( 'Add New Team Member' ),
        'add_new'             => __( 'Add New Team Member' ),
        'edit_item'           => __( 'Edit Team Member' ),
        'update_item'         => __( 'Update Team Member' ),
        'search_items'        => __( 'Search Team Members' ),
        'not_found'           => __( 'Not Found' ),
        'not_found_in_trash'  => __( 'Not found in Trash' ),
    );
     
// Set other options for Custom Post Type
    $args = array(
        'label'               => __( 'Team'),
        'description'         => __( 'Team Members at Company' ),
        'labels'              => $labels,
        // Features this CPT supports in Post Editor
//        'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
        'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields', 'page-attributes' ),
        // You can associate this CPT with a taxonomy or custom taxonomy. 
        'taxonomies'          => array( 'category' ),
        /* A hierarchical CPT is like Pages and can have
        * Parent and child items. A non-hierarchical CPT
        * is like Posts.
        */ 
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 5,
        'can_export'          => true,
		'rewrite' => array('slug' => 'team'),
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'page',
    );
    // Register your Custom Post Type
    register_post_type( 'team', $args );
}
// Hooking up our function to theme setup
add_action( 'init', 'create_team_posttype', 1 );

function custom_meta_box_markup($object)
{
    wp_nonce_field(basename(__FILE__), "meta-box-nonce");
    ?>
        <div>
            <label for="meta-box-job-title">Title</label>
            <input name="meta-box-job-title" type="text" value="<?php echo get_post_meta($object->ID, "meta-box-job-title", true); ?>" style="margin-bottom:20px;width: 84%;">
            <br>
            <label for="meta-box-dropdown">Priority</label>
            <select name="meta-box-dropdown">
                <?php 
                    $option_values = array(1, 2, 3);
                    foreach($option_values as $key => $value) 
                    {
                        if($value == get_post_meta($object->ID, "meta-box-dropdown", true))
                        {
                            ?>
                                <option selected><?php echo $value; ?></option>
                            <?php    
                        }
                        else
                        {
                            ?>
                                <option><?php echo $value; ?></option>
                            <?php
                        }
                    }
                ?>
            </select>
            <br>
            <label for="meta-box-checkbox">Check Box</label>
            <?php
                $checkbox_value = get_post_meta($object->ID, "meta-box-checkbox", true);
                if($checkbox_value == "")
                {
                    ?>
                        <input name="meta-box-checkbox" type="checkbox" value="true">
                    <?php
                }
                else if($checkbox_value == "true")
                {
                    ?>  
                        <input name="meta-box-checkbox" type="checkbox" value="true" checked>
                    <?php
                }
            ?>
        </div>
    <?php  
}

function add_custom_meta_box()
{

    add_meta_box("demo-meta-box", "Job Title ", "custom_meta_box_markup", "team", "normal", "high", null);
}

add_action("add_meta_boxes", "add_custom_meta_box");

function save_custom_meta_box($post_id, $post, $update)
{
    if (!isset($_POST["meta-box-nonce"]) || !wp_verify_nonce($_POST["meta-box-nonce"], basename(__FILE__)))
        return $post_id;

    if(!current_user_can("edit_post", $post_id))
        return $post_id;

    if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
        return $post_id;

    $slug = "team";
    if($slug != $post->post_type)
        return $post_id;

    $meta_box_text_value = "";
    $meta_box_dropdown_value = "";
    $meta_box_checkbox_value = "";

    if(isset($_POST["meta-box-job-title"]))
    {
        $meta_box_text_value = $_POST["meta-box-job-title"];
    }   
    update_post_meta($post_id, "meta-box-job-title", $meta_box_text_value);

    if(isset($_POST["meta-box-dropdown"]))
    {
        $meta_box_dropdown_value = $_POST["meta-box-dropdown"];
    }   
    update_post_meta($post_id, "meta-box-dropdown", $meta_box_dropdown_value);

    if(isset($_POST["meta-box-checkbox"]))
    {
        $meta_box_checkbox_value = $_POST["meta-box-checkbox"];
    }   
    update_post_meta($post_id, "meta-box-checkbox", $meta_box_checkbox_value);
}

add_action("save_post", "save_custom_meta_box", 10, 3);


	