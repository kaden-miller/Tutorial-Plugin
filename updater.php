<?php
class Pizza_Updater {
    protected $file;
    protected $plugin;
    protected $basename;
    protected $active;

    private $username;
    private $repository;
    private $authorize_token;
    private $github_response;

    public function __construct($file) {
        $this->file = $file;
        add_action('admin_init', array($this, 'set_plugin_properties'));
    }

    public function set_plugin_properties() {
        $this->plugin = get_plugin_data($this->file);
        $this->basename = plugin_basename($this->file);
        $this->active = is_plugin_active($this->basename);
    }

    public function set_username( $username ) {
        $this->username = $username;
    }
    public function set_repository( $repository ) {
        $this->repository = $repository;
    }
    public function authorize( $token ) {
        $this->authorize_token = $token;
    }

    private function get_repository_info() {
        if (is_null($this->github_response)){ // Do we have a response?
            $request_uri = sprintf(
                'https://api.github.com/repos/%s/%s/releases', $this->username, $this->repository
            ); // build URI
            if ( $this->authorize_token ) { // is there an access token?
                $request_uri = add_query_arg( 'access_token', $this->authorize_token, $request_uri ); // append it
            }
            $response = json_decode( wp_remote_retrieve_body(wp_remote_get($request_uri)), true);
            if (is_array($response)){ // if it is an array
                $response = current($response); // get the first item
            }
            if($this->authorize_token) { // is there an access token?
                $response['zipball_url'] = add_query_arg('access_token', $this->authorize_token, $response['zipball_url']); // update our zip url with token
            }
            $this->github_response = $response;

        }
    }

    public function initialize() {
        add_filter( 'pre_set_transient_update_plugins', array($this, 'modify_transient'), 10, 1);
        add_filter( 'plugins_api', array( $this, 'plugin_popup' ), 10, 3);
        add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
    }

    public function modify_transient( $transient ) {
        if (property_exists( $transient, 'checked') ) { // check if transient has a checked property
            if ($checked = $transient->checked) { // did worpress check for updates?
                $this->get_repository_info();
                $out_of_date = version_compare( $this->github_response['tag_name'], $checked[$this->basename], 'gt' ); // check if we're out of date
                if( $out_of_date ) {
                    $new_files = $this->github_response['zipball_url']; // get the zip
                    $slug = current( explode('/', $this->basename ) ); // create valid slug
                    $plugin = array( // setup plugin info
                        'url' => $this->plugin['PluginURI'],
                        'slug' => $slug,
                        'package' => $new_files,
                        'new_version' => $this->github_response['tag_name']
                    );
                    $transient->response[ $this->basename ] = (object)$plugin; // return it in response
                }
            }

        }
        return $transient;
    }

    public function plugin_popup( $result, $action, $args ) {
        if( ! empty( $args->slug ) ) { // If there is a slug
          if( $args->slug == current( explode( '/' , $this->basename ) ) ) { // And it's our slug
            $this->get_repository_info(); // Get our repo info
            // Set it to an array
            $plugin = array(
              'name'              => $this->plugin["Name"],
              'slug'              => $this->basename,
              'version'           => $this->github_response['tag_name'],
              'author'            => $this->plugin["AuthorName"],
              'author_profile'    => $this->plugin["AuthorURI"],
              'last_updated'      => $this->github_response['published_at'],
              'homepage'          => $this->plugin["PluginURI"],
              'short_description' => $this->plugin["Description"],
              'sections'          => array( 
                  'Description'   => $this->plugin["Description"],
                  'Updates'       => $this->github_response['body'],
              ),
              'download_link'     => $this->github_response['zipball_url']
            );
            return (object) $plugin; // Return the data
          }
        }   
        return $result; // Otherwise return default
      }

      public function after_install( $response, $hook_extra, $result ) {
        global $wp_filesystem;  // get global fs object

        $install_directory = plugin_dir_path( $this-> file ); // our plugin directory
        $wp_filesystem->move( $result['destination'], $install_directory ); // move files to the plugin dir
        $result['destination'] = $install_directory; // set the desination for the rest of the stack

        if ( $this->active ) { // check if it was active
            activate_plugin( $this->basename ); // reactive plugin
        }
        return $result;
      }


}
?>