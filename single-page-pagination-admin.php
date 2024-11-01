<?php

class LeaferSettingsPage
{
    const LEAFER_TITLE = 'Single Page Pagination';
    const LEAFER_SHORT_TITLE = 'Single Page Pagination';
    const LEAFER_OPTION_NAME = 'leafer_options';

    const TAXONOMIES = [
        'category' => 'Category',
        'post_tag' => 'Tag',
        'product_type' => 'Product type',
        'product_cat' => 'Product category',
        'product_tag' => 'Product tag',
    ];

    const FORECOLORS = [
        'white' => 'White',
        'black' => 'Black',
    ];

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
        add_action( 'admin_enqueue_scripts', array($this, 'init_scripts') );
        add_action( 'plugins_loaded', array($this, 'leafer_init') );

        $this->options = get_option( self::LEAFER_OPTION_NAME );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        add_submenu_page(
            'options-general.php',
            self::LEAFER_SHORT_TITLE . ' Settings',
            self::LEAFER_SHORT_TITLE . ' Settings',
            'manage_options',
            self::LEAFER_OPTION_NAME,
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        ?>
        <div class="wrap">
            <h1><?php echo self::LEAFER_TITLE ?></h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'leafer_option_group' );
                do_settings_sections( 'leafer-setting-admin' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'leafer_option_group',
            self::LEAFER_OPTION_NAME,
            array( $this, 'sanitize' )
        );

        add_settings_section(
            'leafer_data_section_id',
            __('Pagination', 'leafer'),
            array( $this, 'print_data_section_info' ),
            'leafer-setting-admin'
        );  

        add_settings_field(
            'in_same_term',
            __('In Same Term', 'leafer'),
            array( $this, 'in_same_term_callback' ),
            'leafer-setting-admin',
            'leafer_data_section_id'
        );      

        add_settings_field(
            'taxonomy', 
            __('Taxonomy', 'leafer'), 
            array( $this, 'taxonomy_callback' ), 
            'leafer-setting-admin', 
            'leafer_data_section_id'
        );

        add_settings_section(
            'leafer_cpa_section_id',
            __('Colors', 'leafer'),
            array( $this, 'print_cpa_section_info' ),
            'leafer-setting-admin'
        );  

        add_settings_field(
            'background',
            __('Background Color', 'leafer'),
            array( $this, 'bg_settings_field' ),
            'leafer-setting-admin',
            'leafer_cpa_section_id'
        );

        add_settings_field(
            'color',
            __('Arrow Color', 'leafer'),
            array( $this, 'color_settings_field' ),
            'leafer-setting-admin',
            'leafer_cpa_section_id'
        );

        add_settings_field(
            'tooltip_background',
            __('Tooltip Background Color', 'leafer'),
            array( $this, 'tt_bg_settings_field' ),
            'leafer-setting-admin',
            'leafer_cpa_section_id'
        );

        add_settings_field(
            'tooltip_color',
            __('Tooltip Text Color', 'leafer'),
            array( $this, 'tt_color_settings_field' ),
            'leafer-setting-admin',
            'leafer_cpa_section_id'
        );

    }

    public function print_data_section_info()
    {
        print '<p>' 
            . __('A simple plugin that displays next and previous links on single post pages.', 'leafer') . ' '
            . '<br/>'
            . __('Posts are ordered chronologically.', 'leafer')
            . '</p>';
    }

    public function print_cpa_section_info()
    {}

    public function in_same_term_callback()
    {
        printf(
            '<input type="checkbox" id="in_same_term" name="' . self::LEAFER_OPTION_NAME . '[in_same_term]" value="1" %s />',
            isset( $this->options['in_same_term'] ) && $this->options['in_same_term'] ? 'checked' : ''
        );
    }

    public function taxonomy_callback()
    {
        $taxonomies = array_keys(get_taxonomies());
        $our_tax = array_intersect($taxonomies, ['category', 'post_tag', 'product_type', 'product_cat', 'product_tag']);
        $value = isset( $this->options['taxonomy'] ) ? esc_attr( $this->options['taxonomy']) : 'category';

        printf('<select id="taxonomy" name="' . self::LEAFER_OPTION_NAME . '[taxonomy]">');
        foreach ($our_tax as $tax) {
            $title = __(self::TAXONOMIES[$tax]);
            printf(
                '<option value="%s" %s>%s</option>',
                $tax,
                $value == $tax ? 'selected' : '',
                $title
            );
        }
        printf('</select>');
    }

    public function bg_settings_field()
    {
        $color = ( isset( $this->options['background'] ) ) ? $this->options['background'] : '#ffffff';
        print('<input type="text" name="' . self::LEAFER_OPTION_NAME . '[background]" value="' . $color . '" class="leafer-color-picker" data-default-color="#ffffff">');
    }

    public function tt_bg_settings_field()
    {
        $color = ( isset( $this->options['tooltip_background'] ) ) ? $this->options['tooltip_background'] : '#000000';
        print('<input type="text" name="' . self::LEAFER_OPTION_NAME . '[tooltip_background]" value="' . $color . '" class="leafer-color-picker" data-default-color="#000000">');
    }

    public function tt_color_settings_field()
    {
        $color = ( isset( $this->options['tooltip_color'] ) ) ? $this->options['tooltip_color'] : '#ffffff';
        print('<input type="text" name="' . self::LEAFER_OPTION_NAME . '[tooltip_color]" value="' . $color . '" class="leafer-color-picker" data-default-color="#ffffff">');
    }

    public function color_settings_field()
    {
        $color = isset( $this->options['color'] ) ? esc_attr( $this->options['color']) : 'black';
        printf('<select id="color" name="' . self::LEAFER_OPTION_NAME . '[color]">');
        foreach (self::FORECOLORS as $value => $title) {
            printf(
                '<option value="%s" %s>%s</option>',
                $value,
                $color == $value ? 'selected' : '',
                __($title)
            );
        }
        printf('</select>');
    }

    public function init_scripts() {
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'leafer-admin', plugins_url( 'js/leafer-admin.min.js', __FILE__ ), array( 'wp-color-picker', 'jquery' ), false, true  );
    }

    public function sanitize($input) {
        $bg_color = $input['background'];
        $tt_bg_color = $input['tooltip_background'];
        $tt_color = $input['tooltip_color'];

        $vars = file_get_contents(plugin_dir_path(__FILE__) . 'css/leafer-front-variables.min.css');
        $vars = str_replace('__BUTTON_BG_COLOR__', $bg_color, $vars);
        $vars = str_replace('__TOOLTIP_BG_COLOR__', $tt_bg_color, $vars);
        $vars = str_replace('__TOOLTIP_COLOR__', $tt_color, $vars);

        $hash = md5($vars);
        $input['hash'] = $hash;

        $data = file_get_contents(plugin_dir_path(__FILE__) . 'css/leafer-front-data.min.css');

        $this->clear_build();
        file_put_contents(plugin_dir_path(__FILE__) . 'build/leafer-front.' . $hash . '.css', $vars . $data);

        return $input;
    }

    private function clear_build() {
        $path = plugin_dir_path(__FILE__) . 'build';
        if (file_exists($path)) {
            array_map('unlink', glob($path . '/*.*'));
        } else {
            mkdir($path, 0755);
        }
    }

    public function leafer_init() {
        load_plugin_textdomain( 'leafer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
    }
}
