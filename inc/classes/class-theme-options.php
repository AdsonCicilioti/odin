<?php
/**
 * Odin_Theme_Options class.
 *
 * Built settings page.
 *
 * @package  Odin
 * @category Options
 * @author   WPBrasil
 * @version  1.0
 */
class Odin_Theme_Options {

    /**
     * Settings tabs.
     *
     * @var array
     */
    private $_tabs = array();

    /**
     * Settings fields.
     *
     * @var array
     */
    private $_fields = array();

    /**
     * Settings construct.
     *
     * @param string $page_title Page title.
     * @param string $slug       Page slug.
     * @param string $capability User capability.
     */
    public function __construct(
        $page_title = 'Theme Settings',
        $slug       = 'odin-settings',
        $capability = 'manage_options'
    ) {
        $this->page_title = $page_title;
        $this->slug       = $slug;
        $this->capability = $capability;

        // Actions.
        add_action( 'admin_menu', array( &$this, 'add_page' ) );
        add_action( 'admin_init', array( &$this, 'create_settings' ) );

        if ( isset( $_GET['page'] ) && $_GET['page'] == $slug ) {
            add_action( 'admin_init', array( &$this, 'scripts' ) );
        }
    }

    /**
     * Add Settings Theme page.
     *
     * @return void.
     */
    public function add_page() {
        add_theme_page(
            $this->page_title,
            $this->page_title,
            $this->capability,
            $this->slug,
            array( &$this, 'settings_page' )
        );
    }

    /**
     * Load options scripts.
     *
     * @return void
     */
    function scripts() {
        wp_enqueue_script( 'jquery' );

        // Color Picker.
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );

        // Media Upload.
        wp_enqueue_script( 'media-upload' );
        wp_enqueue_script( 'thickbox' );
        wp_enqueue_style( 'thickbox' );
    }

    /**
     * Set settings tabs.
     *
     * @param array $tabs Settings tabs.
     */
    public function set_tabs( $tabs ) {
        $this->_tabs = $tabs;
    }

    /**
     * Set settings fields
     *
     * @param array $fields Settings fields.
     */
    public function set_fields( $fields ) {
        $this->_fields = $fields;
    }

    /**
     * Get current tab.
     *
     * @return string Current tab ID.
     */
    protected function get_current_tab() {
        if ( isset( $_GET['tab'] ) ) {
            $current_tab = $_GET['tab'];
        } else {
            $current_tab = $this->_tabs[0]['id'];
        }

        return $current_tab;
    }

    private function get_current_url() {
        $url = 'http';
        if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) {
            $url .= 's';
        }
        $url .= '://';

        if ( $_SERVER['SERVER_PORT'] != '80' ) {
            $url .= $_SERVER['SERVER_NAME'] . ' : ' . $_SERVER['SERVER_PORT'] . $_SERVER['PHP_SELF'];
        } else {
            $url .= $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
        }

        return esc_url( $url );
    }

    /**
     * Get tab navigation.
     *
     * @param  string $current_tab Current tab ID.
     *
     * @return string              Tab Navigation.
     */
    protected function get_navigation( $current_tab ) {

        $html = '<h2 class="nav-tab-wrapper">';

        foreach ( $this->_tabs as $tab ) {

            $current = ( $current_tab == $tab['id'] ) ? ' nav-tab-active' : '';

            $html .= sprintf( '<a href="%s?page=%s&amp;tab=%s" class="nav-tab%s">%s</a>', $this->get_current_url(), $this->slug, $tab['id'], $current, $tab['title'] );
        }

        $html .= '</h2>';

        echo $html;
    }

    /**
     * Built settings page.
     *
     * @return void.
     */
    public function settings_page() {
        ?>

        <div class="wrap">

            <?php
                // Display themes screen icon.
                screen_icon( 'themes' );

                // Get current tag.
                $current_tab = $this->get_current_tab();

                // Display the navigation menu.
                $this->get_navigation( $current_tab );

                // Display erros.
                settings_errors();
            ?>

            <form method="post" action="options.php">

                <?php
                    foreach ( $this->_tabs as $tabs ) {
                        if ( $current_tab == $tabs['id'] ) {

                            // Prints nonce, action and options_page fields.
                            settings_fields( $tabs['id'] );

                            // Prints settings sections and settings fields.
                            do_settings_sections( $tabs['id'] );
                        }
                    }

                    // Display submit button.
                    submit_button();
                ?>

            </form>

        </div>

        <?php
    }

    /**
     * Create settings.
     *
     * @return void.
     */
    public function create_settings() {

        // Register settings fields.
        foreach ( $this->_fields as $section => $items ) {

            // Register settings sections.
            add_settings_section(
                $section,
                $items['title'],
                '__return_false',
                $items['tab']
            );

            foreach ( $items['options'] as $option ) {

                $type = isset( $option['type'] ) ? $option['type'] : 'text';

                $args = array(
                    'id'          => $option['id'],
                    'tab'         => $items['tab'],
                    'description' => isset( $option['description'] ) ? $option['description'] : '',
                    'name'        => $option['label'],
                    'section'     => $section,
                    'size'        => isset( $option['size'] ) ? $option['size'] : null,
                    'options'     => isset( $option['options'] ) ? $option['options'] : '',
                    'default'     => isset( $option['default'] ) ? $option['default'] : ''
                );

                add_settings_field(
                    $option['id'],
                    $option['label'],
                    array( &$this, 'callback_' . $type ),
                    $items['tab'],
                    $section,
                    $args
                );
            }
        }

        // Register settings.
        foreach ( $this->_tabs as $tabs ) {
            if ( isset( $tabs['validate'] ) && $tabs['validate'] == false ) {
                register_setting( $tabs['id'], $tabs['id'] );
            } else {
                register_setting( $tabs['id'], $tabs['id'], array( &$this, 'validate_input' ) );
            }

        }
    }

    /**
     * Get Option.
     *
     * @param  string $tab     Tab that the option belongs
     * @param  string $id      Option ID.
     * @param  string $default Default option
     * @return array           Item options.
     */
    protected function get_option( $tab, $id, $default = '' ) {
        $options = get_option( $tab );

        if ( isset( $options[$id] ) ) {
            $default = $options[$id];
        }

        return $default;

    }

    /**
     * Text input callback.
     *
     * @param array $args Arguments from the option.
     */
    function callback_text( $args ) {
        $tab = $args['tab'];
        $id = $args['id'];

        // Sets current option.
        $current = esc_html( $this->get_option( $tab, $id, $args['default'] ) );

        // Sets input size.
        $size = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';

        $html = sprintf( '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" class="%4$s-text" />', $id, $tab, $current, $size );

        // Displays option description.
        if ( $args['description'] ) {
            $html .= sprintf( '<p class="description">%s</p>', $args['description'] );
        }

        echo $html;
    }

    /**
     * Textarea input callback.
     *
     * @param array $args Arguments from the option.
     */
    function callback_textarea( $args ) {
        $tab = $args['tab'];
        $id = $args['id'];

        // Sets current option.
        $current = esc_textarea( $this->get_option( $tab, $id, $args['default'] ) );

        $html = sprintf( '<textarea id="%1$s" name="%2$s[%1$s]" rows="5" cols="50">%3$s</textarea>', $id, $tab, $current );

        // Displays option description.
        if ( $args['description'] ) {
            $html .= sprintf( '<p class="description">%s</p>', $args['description'] );
        }

        echo $html;
    }

    /**
     * Editor callback.
     *
     * @param array $args Arguments from the option.
     */
    function callback_editor( $args ) {
        $tab = $args['tab'];
        $id = $args['id'];

        // Sets current option.
        $current = wpautop( $this->get_option( $tab, $id, $args['default'] ) );

        // Sets input size.
        $size = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : '600px';

        echo '<div style="width: ' . $size . ';">';

            wp_editor( $current, $tab . '[' . $id . ']', array( 'textarea_rows' => 10 ) );

        echo '</div>';

        // Displays option description.
        if ( $args['description'] ) {
            echo sprintf( '<p class="description">%s</p>', $args['description'] );
        }
    }

    /**
     * Checkbox input callback.
     *
     * @param array $args Arguments from the option.
     */
    function callback_checkbox( $args ) {
        $tab = $args['tab'];
        $id = $args['id'];

        // Sets current option.
        $current = $this->get_option( $tab, $id, $args['default'] );

        $html = sprintf( '<input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="1"%3$s />', $id, $tab, checked( 1, $current, false ) );

        // Displays option description.
        if ( $args['description'] ) {
            $html .= sprintf( '<label for="%s"> %s</label>', $id, $args['description'] );
        }

        echo $html;
    }

    /**
     * Multicheckbox input callback.
     *
     * @param array $args Arguments from the option.
     */
    function callback_multicheckbox( $args ) {
        $tab = $args['tab'];
        $id = $args['id'];

        $html = '';
        foreach( $args['options'] as $key => $label ) {
            $item_id = $id . '_' . $key;

            // Sets current option.
            $current = $this->get_option( $tab, $item_id, $args['default'] );

            $html .= sprintf( '<input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="1"%3$s />', $item_id, $tab, checked( $current, 1, false ) );
            $html .= sprintf( '<label for="%s"> %s</label><br />', $item_id, $label );
        }

        // Displays option description.
        if ( $args['description'] ) {
            $html .= sprintf( '<p class="description">%s</p>', $args['description'] );
        }

        echo $html;
    }

    /**
     * Radio input callback.
     *
     * @param array $args Arguments from the option.
     */
    function callback_radio( $args ) {
        $tab = $args['tab'];
        $id = $args['id'];

        // Sets current option.
        $current = $this->get_option( $tab, $id, $args['default'] );

        $html = '';
        foreach( $args['options'] as $key => $label ) {
            $item_id = $id . '_' . $key;
            $key = sanitize_title( $key );

            $html .= sprintf( '<input type="radio" id="%1$s_%3$s" name="%2$s[%1$s]" value="%3$s"%4$s />', $id, $tab, $key, checked( $current, $key, false ) );
            $html .= sprintf( '<label for="%s"> %s</label><br />', $item_id, $label );
        }

        // Displays option description.
        if ( $args['description'] ) {
            $html .= sprintf( '<p class="description">%s</p>', $args['description'] );
        }

        echo $html;
    }

    /**
     * Select input callback.
     *
     * @param array $args Arguments from the option.
     */
    function callback_select( $args ) {
        $tab = $args['tab'];
        $id = $args['id'];

        // Sets current option.
        $current = $this->get_option( $tab, $id, $args['default'] );

        $html = sprintf( '<select id="%1$s" name="%2$s[%1$s]">', $id, $tab );
        foreach( $args['options'] as $key => $label ) {
            $key = sanitize_title( $key );

            $html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $current, $key, false ), $label );
        }
        $html .= '</select>';

        // Displays option description.
        if ( $args['description'] ) {
            $html .= sprintf( '<p class="description">%s</p>', $args['description'] );
        }

        echo $html;
    }

    /**
     * Color input callback.
     *
     * @param array $args Arguments from the option.
     */
    function callback_color( $args ) {
        $tab = $args['tab'];
        $id = $args['id'];

        // Sets current option.
        $current = $this->get_option( $tab, $id, $args['default'] );

        $html = sprintf( '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" />', $id, $tab, $current );

        // Displays option description.
        if ( $args['description'] ) {
            $html .= sprintf( '<p class="description">%s</p>', $args['description'] );
        }

        $html .= '<script type="text/javascript">';
            $html .= 'jQuery(document).ready(function($) {';
                $html .= sprintf( '$("#%s").wpColorPicker();', $id );
            $html .= '});';
        $html .= '</script>';

        echo $html;
    }

    /**
     * Upload input callback.
     *
     * @param array $args Arguments from the option.
     */
    function callback_upload( $args ) {
        $tab = $args['tab'];
        $id = $args['id'];

        // Sets current option.
        $current = esc_url( $this->get_option( $tab, $id, $args['default'] ) );

        $html = sprintf( '<input type="text" id="color-%1$s" name="%2$s[%1$s]" value="%3$s" class="regular-text" />', $id, $tab, $current );

        $html .= sprintf( '<input class="button" id="%s_button" type="button" value="%s" />', $id, __( 'Selecionar arquivo', '_base' ) );

        // Displays option description.
        if ( $args['description'] ) {
            $html .= sprintf( '<p class="description">%s</p>', $args['description'] );
        }

        $html .= '<script type="text/javascript">';
            $html .= 'jQuery(document).ready(function($) {';
                $html .= sprintf( '$("#%s_button").click(function() {', $id );
                    $html .= 'uploadID = $(this).prev("input");';
                    $html .= sprintf( 'formfield = $("color-%s").attr("name");', $id );
                    $html .= 'tb_show("", "media-upload.php?post_id=&amp;type=image&amp;TB_iframe=true");';
                    $html .= 'return false;';
                $html .= '});';
                $html .= 'window.send_to_editor = function(html) {';
                    $html .= 'imgurl = $("img", html).attr("src");';
                    $html .= 'uploadID.val(imgurl);';
                    $html .= 'tb_remove();';
                $html .= '}';
            $html .= '});';
        $html .= '</script>';

        echo $html;
    }

    /**
     * HTML callback.
     *
     * @param array $args Arguments from the option.
     */
    function callback_html( $args ) {
        echo $args['description'];
    }

    /**
     * Sanitization fields callback.
     *
     * @param  string $input The unsanitized collection of options.
     * @return string        The collection of sanitized values.
     */
    public function validate_input( $input ) {
        // Create our array for storing the validated options
        $output = array();

        // Loop through each of the incoming options
        foreach ( $input as $key => $value ) {

            // Check to see if the current option has a value. If so, process it.
            if ( isset( $input[$key] ) ) {

                // Strip all HTML and PHP tags and properly handle quoted strings
                $output[$key] = strip_tags( stripslashes( $input[$key] ) );
            }
        }

        // Return the array processing any additional functions filtered by this action
        return apply_filters( 'cs_framework_settings_validate_input', $output, $input );
    }

}
