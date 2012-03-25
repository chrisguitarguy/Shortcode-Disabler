<?php
/**
 * Admin area functionality for Shortcode Disabler
 *
 * @author Christopher Davis <http://christopherdavis.me>
 * @package Shortcode Disabler
 * @since 0.1
 */
class CD_Shortcode_Disabler_Admin
{
    /**
     * Setting name
     * 
     * @since 0.1
     * @access protected
     */
    protected $setting = 'shortcode_disabler_options';

    /**
     * Menu page slug
     *
     * @since 0.1
     * @access protected
     */
    protected $page_slug = 'shortcode-disabler';
    
    /**
     * Nonce action
     * 
     * @since 0.1
     * @access protected
     */
    protected $nonce_action = 'shortcode_disabler_nonce';
    
    /**
     * Nonce action for delete URIs
     * 
     * @since 0.1
     * @access protected
     */
    protected $delete_nonce = 'shortcode_disabler_delete';
    
    /**
     * The URI for the admin page
     * 
     * @since 0.1
     * @access protected
     */
    protected $admin_uri;

    /**
     * Constructor
     *
     * @since 0.1
     * @uses add_action
     * @uses add_filter
     */
    function __construct()
    {
        add_action( 'admin_menu', array( &$this, 'menu_page' ) );
        add_action( 'admin_init', array( &$this, 'register_setting' ) );
        add_filter( 'plugin_action_links_' . CD_SCD_NAME, array( &$this, 'actions' ) );
        
        $this->admin_uri = add_query_arg( 'page', $this->page_slug, admin_url( 'options-general.php' ) );
    }

    /**
     * Adds the menu page for Shortcode Disabler
     *
     * @since 0.1
     * @uses add_options_page
     * @uses add_action
     */
    function menu_page()
    {
        $page = add_options_page(
            __( 'Shortcode Disabler', 'shortcode-disabler' ),
            __( 'Shortcode Disabler', 'shortcode-disabler' ),
            'manage_options',
            $this->page_slug,
            array( &$this, 'menu_page_cb' )
        );
        add_action( "load-{$page}", array( &$this, 'menu_page_load' ) );
    }

    /**
     * Menu page callback function (actually displays the page)
     *
     * @since 0.1
     */
    function menu_page_cb()
    {
        $opts = get_option( $this->setting, array() );
        require_once( CD_SCD_PATH . 'inc/list-table.php' );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2><?php _e( 'Shortcode Disabler', 'shortcode-disabler' ); ?></h2>
            <form action="<?php echo esc_url( $this->admin_uri ); ?>" method="POST">
                <?php wp_nonce_field( $this->nonce_action ); ?>
                <?php if( isset( $_GET['shortcode'] ) && $_GET['shortcode'] && isset( $opts[$_GET['shortcode']] ) ): ?>
                    <h4><?php _e( 'Edit Shortcode', 'shortcode-disabler' ); ?></h4>
                    <?php $this->shortcode_fields( $_GET['shortcode'], $opts[$_GET['shortcode']] ); ?>
                    <?php submit_button(); ?>
                <?php else: ?>
                    <h4><?php _e( 'Add Shortcode', 'shortcode-disabler' ); ?></h4>
                    <?php $this->shortcode_fields(); ?>
                    <?php submit_button( __( 'Add Shortcode', 'shortcode-disabler' ) ); ?>
                <?php endif; ?>
            </form>
            <?php
                $table = new CD_Shortcode_Disabler_List_Table();
                $table->prepare_items();
                $table->display();
            ?>
        </div>
        <?php
    }

    /**
     * Called when the menu page loads. Adds screen help and handles saving
     * shortcodes
     *
     * @since 0.1
     */
    function menu_page_load()
    {
        // help stuff here
        
        $opts = get_option( $this->setting, array() );
        
        if( isset( $_GET['delete'] ) && $_GET['delete'] )
        {
            $nonce = isset( $_GET['_wpnonce'] ) && $_GET['_wpnonce'] ? $_GET['_wpnonce'] : false;
            if( ! $nonce || ! wp_verify_nonce( $nonce, $this->delete_nonce ) )
            {
                wp_die(
                    __( "I wouldn't do that if I were you.", 'shortcode-diabler' ),
                    __( 'Busted', 'shortcode-disabler' )
                );
            }
            
            if( isset( $opts[$_GET['delete']] ) )
            {
                unset( $opts[$_GET['delete']] );
                update_option( $this->setting, $opt );
            }
            wp_redirect( $this->admin_uri );
        }
        
        if( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' == $_SERVER['REQUEST_METHOD'] && ! empty( $_POST ) )
        {
            check_admin_referer( $this->nonce_action );
            $shortcode = isset( $_POST['shortcode_name'] ) ? $_POST['shortcode_name'] : false;
            if( ! $shortcode ) return;
            $content = isset( $_POST['display_content'] ) && $_POST['display_content'] ? 'on' : 'off';
            $opts[esc_attr( $shortcode )] = $content;
            update_option( $this->setting, $opts );
            wp_redirect( $this->admin_uri );
        }
    }

    /**
     * Registers the the `shortcode_disabler_options` setting.
     *
     * @since 0.1
     * @uses register_setting
     */
    function register_setting()
    {
        // not really necessary for this plugin?
        register_setting(
            $this->setting,
            $this->setting
        );
    }

    /**
     * Filters plugin action links to display a "settings" link.
     *
     * @since 0.1
     */
    function actions( $actions )
    {
        $actions['setting'] = '<a href="' . esc_url( $this->admin_uri ) . '">' . __( 'Settings', 'shortcode-disabler' ) . '</a>';
        return $actions;
    }
    
    /**
     * Renders the shortcode fields for the shortcode edit/add form
     * 
     * @since 0.1
     * @access protected
     */
    protected function shortcode_fields( $shortcode='', $content='off' )
    {
        echo "<table class='form-table'><tr>";
        echo "<th scope='row'>" . __( 'Shortcode', 'shortcode-disabler' ) . "</th>";
        echo "<td><input type='text' name='shortcode_name' value='" . esc_attr( $shortcode ) . "' class='regular-text' /></td>";
        echo "</tr><tr>";
        echo "<th scope='row'>" . __( 'Display Content', 'shortcode-disabler' ) . "</th>";
        echo "<td><input type='checkbox' name='display_content' " . checked( 'on', $content, false ) . " /></td>";
        echo "</tr></table>";
    }
} // end class

new CD_Shortcode_Disabler_Admin();
