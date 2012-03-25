<?php
if( ! class_exists( 'WP_List_Table' ) )
{
	require_once( trailingslashit( ABSPATH ) . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * List table for displaying shortcodes
 * 
 * @since 0.1
 */
class CD_Shortcode_Disabler_List_Table extends WP_List_table
{
    /**
     * Settings
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
     */
    function __construct()
    {
        // hijack column header setup
        $this->_column_headers = array( $this->get_columns(), array(), array() );
        $this->admin_uri = add_query_arg( 'page', $this->page_slug, admin_url( 'options-general.php' ) );
        parent::__construct();
    }
    
    /**
     * Get the column keys and their translations for column headers
     * 
     * @since 0.1
     */
    function get_columns()
    {
        return array(
            'shortcode'         => __( 'Shortcode', 'shortcode-disabler' ),
            'display_content'   => __( 'Display Content?' , 'shortcode-disabler' ),
            'actions'           => __( 'Actions', 'shortcode-disabler' )
        );
    }
    
    /**
     * Fetches and setups up the shortcodes for use in the list table
     * 
     * @since 0.1
     * @uses get_option
     */
    function prepare_items()
    {
        $codes = get_option( $this->setting );
        $this->items = array();
        if( ! $codes ) return;
        foreach( $codes as $code => $content )
        {
            $this->items[] = array(
                'code'    => $code,
                'content' => $content
            );
        }
    }
    
    /**
     * Display for no shortcodes
     * 
     * @since 0.1
     */
    function no_items()
    {
        echo __( 'Not shortcodes yet. Add some!', 'shortcode-disabler' );
    }
    
    /**
     * Display for the shortcode column
     * 
     * @since 0.1
     */
    function column_shortcode( $item )
    {
        return esc_html( $item['code'] );
    }
    
    /**
     * Display for the display_content column
     * 
     * @since 0.1
     */
    function column_display_content( $item )
    {
        $rv = __( 'No', 'shortcode-disabler' );
        if( 'on' == $item['content'] )
        {
            $rv = __( 'Yes', 'shortcode-disabler' );
        }
        return $rv;
    }
    
    /**
     * Display for the actions column
     * 
     * @since 0.1
     */
    function column_actions( $item )
    {
        $code = esc_attr( $item['code'] );
        $edit = add_query_arg( 'shortcode', $code, $this->admin_uri );
        $delete = add_query_arg( 'delete', $code, $this->admin_uri );
        $delete = wp_nonce_url( $delete, $this->delete_nonce );
        $rv = '<a href="' . esc_url( $edit ) . '">' . __( 'Edit', 'shortcode-disabler' ) . '</a>';
        $rv .= ' | ';
        $rv .= '<a href="' . esc_url( $delete ) . '">' . __( 'Delete' , 'shortcode-disabler' ) . '</a>';
        return $rv;
    }
}
