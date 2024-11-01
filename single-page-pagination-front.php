<?php

class LeaferFront {

    const LEAFER_OPTION_NAME = 'leafer_options';

    private $options;

    public function __construct() {
        $this->options = get_option( self::LEAFER_OPTION_NAME );

        if ( $this->is_woocommerce_installed() ) {
            add_action( 'woocommerce_after_single_product_summary', array( $this, 'leafer_after_single_product_summary' ) );
        } else {
            add_filter( 'the_content', array($this, 'leafer_the_content') );
        }
        add_action( 'wp_enqueue_scripts', array($this, 'leafer_front_scripts') );
    }

    public function leafer_the_content($content) {

        if ( ! $this->is_suitable()) return $content;

        $in_same_term = isset( $this->options['in_same_term'] ) && $this->options['in_same_term'];
        $excluded_terms = isset( $this->options['excluded_terms'] ) ? $this->options['excluded_terms'] : '';
        $taxonomy = isset( $this->options['taxonomy'] ) ? $this->options['taxonomy'] : 'category';
        $bgcolor = isset( $this->options['background'] ) ? $this->options['background'] : '#ffffff';
        $color = isset( $this->options['color'] ) ? $this->options['color'] : 'black';;
    
        $prev_post = get_previous_post($in_same_term, $excluded_terms, $taxonomy);
        $next_post = get_next_post($in_same_term, $excluded_terms, $taxonomy);
    
        if ($prev_post) {
            $content .=
                  '<div id="leafer-button-left" class="leafer-button leafer-button-left'
                . ' leafer-button-left-' . $color . '"'
                . ' data-tooltip="' . $prev_post->post_title . '"'
                . '>'
                . '<a href="' . get_permalink($prev_post) . '">&nbsp;</a>'
                . '</div>';
        }
    
        if ($next_post) {
            $content .=
                  '<div id="leafer-button-right" class="leafer-button leafer-button-right'
                . ' leafer-button-right-' . $color . '"'
                . ' data-tooltip="' . $next_post->post_title . '"'
                . '>'
                . '<a href="' . get_permalink($next_post) . '">&nbsp;</a>'
                . '</div>';
        }
    
        return $content;
    }
    
    public function leafer_after_single_product_summary() {
        echo $this->leafer_the_content('');
    }

    private function build($front_css)
    {
        $options = get_option( self::LEAFER_OPTION_NAME );

        $bg_color = $options['background'];
        $tt_bg_color = $options['tooltip_background'];
        $tt_color = $options['tooltip_color'];

        $vars = file_get_contents(plugin_dir_path(__FILE__) . 'css/leafer-front-variables.min.css');
        $vars = str_replace('__BUTTON_BG_COLOR__', $bg_color, $vars);
        $vars = str_replace('__TOOLTIP_BG_COLOR__', $tt_bg_color, $vars);
        $vars = str_replace('__TOOLTIP_COLOR__', $tt_color, $vars);

        $hash = md5($vars);
        $input['hash'] = $hash;

        $data = file_get_contents(plugin_dir_path(__FILE__) . 'css/leafer-front-data.min.css');

        file_put_contents(__DIR__ . '/' . $front_css, $vars . $data);
    }
    
    public function leafer_front_scripts() {
        $front_css = isset($this->options['hash'])
            ? 'build/leafer-front.' . $this->options['hash'] . '.css'
            : 'css/leafer-front.min.css';

        if ( ! file_exists(__DIR__ . '/' . $front_css)) {
            $this->build($front_css);
        }

        wp_enqueue_style( 'leafer-front', plugins_url($front_css, __FILE__) );
        wp_enqueue_script( 'popper', plugins_url( 'js/popper.min.js', __FILE__ ), array(), false, true );
        wp_enqueue_script( 'tooltip', plugins_url( 'js/tooltip.min.js', __FILE__ ), array('popper'), false, true );
        wp_enqueue_script( 'leafer-front', plugins_url( 'js/leafer-front.min.js', __FILE__ ), array('popper', 'tooltip'), false, true );
    }

    public function is_suitable() {
        $is_product = false;
        if ($this->is_woocommerce_installed()) {
            $is_product = is_product();
        }
        return $is_product || is_single();
    }

    public function is_woocommerce_installed() {
        return in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
    }
}