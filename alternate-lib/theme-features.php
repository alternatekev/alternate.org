<?php
class ThemeFeatures {
	function __construct() {

		// add options page
		$this->add_options_page();

		// add support for the customizer
		$this->add_customizer_support();

		// add sidebars
		$this->add_sidebar_support();

		// add menus
		$this->add_menu_support();

		//add thumbnails
		$this->add_thumbnail_support();

		//add slideshow
		$this->add_slideshow_support();

		//add crossdomain support to json
		$this->add_crossdomain_support();
	}

	function add_crossdomain_support() {
		add_filter('http_origin', function() { return "https://alternate-react-tvevkvirtg.now.sh";});
	}

	function add_options_page() {
		if( function_exists('acf_add_options_page') ) {
			acf_add_options_page(array(
				'page_title' 	=> 'Theme General Settings',
				'menu_title'	=> 'Theme Settings',
				'menu_slug' 	=> 'theme-general-settings',
				'capability'	=> 'edit_posts',
				'redirect'		=> false
			));
		}
	}

	function add_customizer_support() {
		// register support for custom background
		add_theme_support( 'custom-background' );

		// register support for custom colors
		add_action( 'customize_register', array( $this, 'customizer' ) );

	}

	function customizer( $wp_customize ) {
		$wp_customize->add_setting( 'text_color', array(
		  'default' => '#FFFFFF',
		  'sanitize_callback' => 'sanitize_hex_color',
		) );
		$wp_customize->add_control( 'text_color', array(
		'type' 			=> 'color',
		'priority' 			=> 10, // Within the section.
		'section' 			=> 'colors', // Required, core or custom.
		'label' 			=> __( 'Text Color' ),
		'input_attrs' 		=> array(
			'class' 		=> 'text-color',
			'placeholder' 	=> __( '#FFFFFF' ),
			),
		'active_callback' 	=> 'is_front_page',
		) );

		$wp_customize->add_setting( 'accent_color', array(
			'default' 			=> '#FF00A6',
			'sanitize_callback' 	=> 'sanitize_hex_color',
		) );
		$wp_customize->add_control( 'accent_color', array(
		'type' 			=> 'color',
		'priority' 			=> 11, // Within the section.
		'section' 			=> 'colors', // Required, core or custom.
		'label' 			=> __( 'Accent Color' ),
		'input_attrs' 		=> array(
			'class' 		=> 'accent-color',
			'placeholder' 	=> __( '#FF00A6' ),
			),
		'active_callback' 	=> 'is_front_page',
		) );
	}

	function add_menu_support() {
		register_nav_menu( 'home-page-projects', __( 'Home Page Projects' ) );
		register_nav_menu( 'home-page-links', __( 'Home Page Links' ) );
		add_filter( 'timber_context', array( $this, 'add_to_context' ) );

	}

	function add_to_context($data) {
		$options = get_fields( 'options' );
		/* Now, in similar fashion, you add a Timber menu and send it along to the context. */
		$data[ 'projects' ] 	= 	new TimberMenu( 'home-page-projects' ); // This is where you can also send a WordPress menu slug or ID
		$data[ 'home_links' ] 	= 	new TimberMenu( 'home-page-links' ); // This is where you can also send a WordPress menu slug or ID
		$data[ 'options' ] 	= 	$options;
		$data[ 'collections' ] 	= 	$this->reformatCollectionImages( $options[ 'collections' ] );
		$data[ 'images' ] 	= 	$this->reformatPageImages( $data[ 'home_links' ], $options[ 'collections' ] );

		return $data;
	}

	function add_sidebar_support() {
		add_action( 'widgets_init', array( $this, 'register_sidebars' ) );
	}

	function register_sidebars() {
		register_sidebar( array(
			'name'          => 'Primary Home Modules',
			'id'            => 'home_modules',
			'description'   => '',
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		) );

		register_sidebar( array(
			'name'          => 'Secondary Home Modules',
			'id'            => 'home_secondary',
			'description'   => '',
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		) );
	}

	function reformatCollectionImages( $collections ){
		$reformatted = array();

		for($i = 0; $i < count( $collections ); $i++ ) {
			$reformatted[ $collections[ $i ][ 'collection' ] ] = $collections[ $i ][ 'image' ];
		}

		return $reformatted;
	}

	function reformatPageImages( $pages, $collections ){
		$reformatted = array();

		for( $i = 0; $i < count( $pages->items ); $i++ ) {
			$menu_image = @$pages->items[ $i ]->custom[ 'menu-item-image' ];
			$object_id = $pages->items[ $i ]->object_id;

			if( $menu_image ) {

				$reformatted[ $object_id ] = $menu_image;

			} else if( ! $menu_image ) {

				if( has_post_thumbnail( $object_id ) ){
					$urls = wp_get_attachment_image_src( get_post_thumbnail_id( $object_id ), 'large' );
					$reformatted[ $object_id ] = $urls[ 0 ];
				}

			} else if ( @$collections[ $object_id ][ 'collection' ] ){
				$reformatted[ $object_id ] = @$collections[ $object_id ] ['image' ];
			}
		}

		return $reformatted;
	}

	function add_thumbnail_support(){
		add_theme_support( 'post-thumbnails', array( 'page', 'post' ) );
	}

	function add_slideshow_support(){
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_portfolio_js' ) );
	}

	function enqueue_portfolio_js(){
		wp_enqueue_script( 'portfolio', get_template_directory_uri() . '/assets/js/portfolio.js', array( 'jquery' ) );
	}

} ?>
