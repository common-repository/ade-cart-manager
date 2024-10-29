<?php
// add basic plugin security.
defined('ABSPATH') || exit;
class ADECARTMANAGER
{
    public function init()
    {
        // add_filter( 'wp', array( $this, 'process' ), 10 );	
        //add wp ajax
        add_action('wp_ajax_ade_cart_manager_ajax_process', array($this, 'ade_cart_manager_ajax_process'));
        add_action('wp_ajax_nopriv_ade_cart_manager_ajax_process', array($this, 'ade_cart_manager_ajax_process'));
        //Data API
        add_action('wp_ajax_ade_cart_manager_ajax_dataAPI', array($this, 'dataAPI'));
        add_action('wp_ajax_nopriv_ade_cart_manager_ajax_dataAPI', array($this, 'dataAPI'));
    }

    public function createDatabase()
    {
        //create database
        global $wpdb;
        $table_name = $wpdb->prefix . 'ade_user_cart_data';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_cart_data text NOT NULL,
            userip text NOT NULL,
            page_visited text NOT NULL,
            wc_session text NOT NULL,
            user_is_logged_in tinyint(1) NOT NULL,
            user_role text NOT NULL,
            user text NOT NULL,
            currenttimestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY id (id)
        ) $charset_collate;";
        require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function updateDatabaseColumn($column, $wc_session)
    {
        //check if column exists
        global $wpdb;
        $table_name = $wpdb->prefix . 'ade_user_cart_data';
        $column_exists = $wpdb->get_var("SHOW COLUMNS FROM $table_name LIKE '$column'");
        if (!$column_exists) {
            //add column with default value json_encode(array())
            $defaultdata = json_encode([]);
            $wpdb->query("ALTER TABLE $table_name ADD $column text NOT NULL");
            //update column with default value
            $wpdb->query("UPDATE $table_name SET $column = '$defaultdata'");
        }

        //get the coloumn data where wc_session
        $data = $wpdb->get_results("SELECT * FROM $table_name WHERE wc_session = '$wc_session'");
        return $data[0]->search_text;
    }

    public function ade_cart_manager_ajax_process()
    {
        //verify nounce
        $page_id = sanitize_text_field($_GET['current_page']);
        $search_text = sanitize_text_field($_GET['search_text']);
        $verify = check_ajax_referer('ade_cart_manager_ajax_process', 'ade_nounce');

        if ($verify == false) {
            echo json_encode(array('code' => '403', 'message' => 'Forbidden'));
            wp_die();
        }
        //get data
        //   if(is_user_logged_in()){
        $session = $this->getWcSession();
        $user_cart_data = $this->getCartData();
        $userip = $this->getUserIpAddr();
        $details = $userip;
        $userrole = "";
        $user = "";
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $userrole = $user->roles[0];
        } else {
            $userrole = "guest";
            $user = 'guest';
        }

        //get old searches
        $old_searches = $this->updateDatabaseColumn("search_text", $session);
        //update old searches
        $old_searchesc = json_decode($old_searches);
        if ($search_text != "") {
            $old_searchesc[] = [
                'search_text' => $search_text,
                'timestamp' => new DateTime()
            ];
        }
        $old_searchesd = json_encode($old_searchesc);
        $data = [
            'user_cart_data' => json_encode($user_cart_data),
            'userip' => json_encode(["ip" => $userip, "details" => $details]),
            'wc_session' => $session,
            'user_is_logged_in' => is_user_logged_in(),
            'user_role' => $userrole,
            'user' => json_encode($user),
            'search_text' => $old_searchesd,
            'page_visited' => $this->pageVisited($page_id)
        ];
        //check if current page is page, post, product, shop, cart, checkout
        if (is_ajax()) {
            $this->saveCartData($data);
        }
        echo json_encode(array('code' => '200', 'message' => 'Success'));
        wp_die();
        // }
        die;
    }

    public function is_post_type($type)
    {
        global $wp_query;
        if ($type == get_post_type($wp_query->post->ID))
            return true;
        return false;
    }

    public function getCurrentpageUrl()
    {
        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

        $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        return $url; // Outputs: Full URL
    }

    public function pageVisited($page_id)
    {
        $page_visited = get_permalink($page_id);
        //get all from where wc_session
        global $wpdb;
        $table_name = $wpdb->prefix . 'ade_user_cart_data';
        $query = "SELECT * FROM $table_name WHERE wc_session = '" . $this->getWcSession() . "'";
        $results = $wpdb->get_results($query);
        if (count($results) > 0) {
            if (isset($results[0]->page_visited)) {
                $page_visitedold = json_decode($results[0]->page_visited, true);
                $current_page_url = $page_visited;
                $page_visitedold[$current_page_url] = [
                    'page_title' => get_the_title($page_id),
                    'page_url' => $current_page_url,
                    'timestamp' => new DateTime()
                ];
            } else {
                $page_visitedold = [
                    $page_visited => [
                        'page_title' => get_the_title($page_id),
                        'page_url' => $page_visited,
                        'timestamp' => new DateTime()
                    ]
                ];
            }
            return  $page_visitednew = json_encode($page_visitedold);
        } else {
            $page_visitednew = json_encode([
                $page_visited => [
                    'page_title' => get_the_title($page_id),
                    'page_url' => $page_visited,
                    'timestamp' => new DateTime()
                ]
            ]);
        }
        return $page_visitednew;
    }

    public function getWcSession()
    {
        //add wordpress cookies
        if (!session_id()) {
            session_start();
        }

        if (is_user_logged_in()) {
            if (isset($_SESSION["ade_user_cart_data"])) {
                //check if current user email is in string
                if (strpos($_SESSION["ade_user_cart_data"], wp_get_current_user()->user_email) !== false) {
                    return sanitize_text_field($_SESSION["ade_user_cart_data"]);
                } else {
                    //add current user email to string
                    $_SESSION["ade_user_cart_data"] = "wc-ade-" . wp_get_current_user()->user_email;
                    return sanitize_text_field($_SESSION["ade_user_cart_data"]);
                }
            } else {
                $_SESSION["ade_user_cart_data"] = "wc-ade-" . wp_get_current_user()->user_email;
                return sanitize_text_field($_SESSION["ade_user_cart_data"]);
            }
        } else {
            return sanitize_text_field($_SESSION["ade_user_cart_data"]);
        }
    }

    public function getCartDataFromBase()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ade_user_cart_data';
        $user_cart_data = $wpdb->get_results("SELECT * FROM $table_name ORDER BY currenttimestamp DESC");
        return $user_cart_data;
    }

    public function dataAPI()
    {
        header('Content-Type: application/json');
        $data = [];
        if (isset($_GET["type"]) && $_GET["type"] == "recent" && isset($_GET["limit"]) && isset($_GET["starting"])) {
            $limit = sanitize_text_field($_GET["limit"]);
            $starting = sanitize_text_field($_GET["starting"]);
            //get all recent searches
            $data = $this->getCartDataFromBase();
            $search_text_data = [];
            foreach ($data as $key => $value) {
                $search_text = json_decode($value->search_text);
                //push to array
                foreach ($search_text as $key => $value) {
                    $search_text_data[] = $value->search_text;
                }
            }
            //count how many times each search text is used
            $search_text_data_count = array_count_values($search_text_data);
            //sort array by count
            arsort($search_text_data_count);
            //get top 10
            $search_text_data_count = array_slice($search_text_data_count, (int)$starting, (int)$limit);
            echo json_encode(array('code' => '200', 'message' => 'Success', 'data' => $search_text_data_count));
            die;
        }
        //else continue here
        foreach ($this->getCartDataFromBase() as $user_cart_data) {
            $logged = $user_cart_data->user_is_logged_in == 1 ? "Yes" : "No";
            $user = "guest";
            $cartitems = json_decode($user_cart_data->user_cart_data, true);
            $pages = json_decode($user_cart_data->page_visited, true);
            if ($logged == "Yes") {
                $user = json_decode($user_cart_data->user);
                $user = $user->data->user_email;
            }
            $data[] = [
                'user_cart_count' => count($cartitems),
                'cart' => urlencode($user_cart_data->user_cart_data),
                'user_api' => $user_cart_data->user,
                'user_is_logged_in' => $logged,
                'user' => $user,
                'user_role' => $user_cart_data->user_role,
                'user_ip' => $user_cart_data->userip,
                'wc_session' => $user_cart_data->wc_session,
                'searches' => urlencode($user_cart_data->search_text),
                'searches_count' => count(json_decode($user_cart_data->search_text, true)),
                'page_visited' => $user_cart_data->page_visited,
                'page_count' => $pages ? count($pages) : 0,
                'timestamp' =>  $user_cart_data->currenttimestamp
            ];
        }
        echo json_encode(array('code' => '200', 'message' => 'Success', 'data' => $data));
        die;
    }

    public function saveCartData($data)
    {
        //check if database table exists
        global $wpdb;
        $table_name = $wpdb->prefix . 'ade_user_cart_data';
        if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
            $this->createDatabase();
        }
        //select * from table where wc_session = $session_id
        $session_id = $data['wc_session'];
        $this->updateDatabaseColumn("search_text", $session_id);
        $sql = "SELECT * FROM $table_name WHERE wc_session = '$session_id'";
        $results = $wpdb->get_results($sql);
        if (count($results) > 0) {
            $res = $wpdb->update(
                $table_name,
                array(
                    'user_cart_data' => $data['user_cart_data'],
                    'userip' => $data['userip'],
                    'wc_session' => $data['wc_session'],
                    'user_is_logged_in' => $data['user_is_logged_in'],
                    'user_role' => $data['user_role'],
                    'user' => $data['user'],
                    'search_text' => $data['search_text'],
                    'page_visited' => $data['page_visited']
                ),
                array(
                    'wc_session' => $session_id,
                )
            );
            //perform raw query
            $sql2 = "UPDATE $table_name SET currenttimestamp = now() WHERE wc_session = '$session_id'";
            $wpdb->query($sql2);
        } else {
            $res = $wpdb->insert(
                $table_name,
                array(
                    'user_cart_data' => $data['user_cart_data'],
                    'userip' => $data['userip'],
                    'wc_session' => $data['wc_session'],
                    'user_is_logged_in' => $data['user_is_logged_in'],
                    'user_role' => $data['user_role'],
                    'user' => $data['user'],
                    'search_text' => $data['search_text'],
                    'page_visited' => $data['page_visited']
                )
            );
        }
        //check for wpdb error
        if ($wpdb->last_error !== '') {
            return $wpdb->last_error;
        } else {
            return $res;
        }
    }

    //get user ip
    public static function getUserIpAddr()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            //ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            //ip pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    public function getCartData()
    {
        global $woocommerce;
        //list current cart items
        $cart = $woocommerce->cart->get_cart();
        $cart_items = array();
        foreach ($cart as $cart_item_key => $cart_item) {
            $product_id = $cart_item['product_id'];
            $product = new WC_Product($product_id);
            $product_name = $product->get_title();
            $product_price = $product->get_price();
            $product_quantity = $cart_item['quantity'];
            $product_total = $product_price * $product_quantity;
            $cart_items[$product_id] = array(
                'product_id' => $product_id,
                'product_name' => $product_name,
                'product_price' => $product_price,
                'product_quantity' => $product_quantity,
                'product_total' => $product_total,
                'product_url' => get_permalink($product_id),
                'product_image' => get_the_post_thumbnail_url($product_id)
            );
        }
        return $cart_items;
    }

    public function deactivation()
    {
        //Do nothing yet

        // global $wpdb;
        // $table_name = $wpdb->prefix . 'ade_user_cart_data';
        // //Query
        // $sql = "DROP TABLE IF EXISTS $table_name;";
        // $wpdb->query($sql);
    }

    public function activation()
    {
        //check if database table exists
        global $wpdb;
        $table_name = $wpdb->prefix . 'ade_user_cart_data';
        if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                user_cart_data text NOT NULL,
                userip text NOT NULL,
                page_visited text NOT NULL,
                wc_session text NOT NULL,
                user_is_logged_in tinyint(1) NOT NULL,
                user_role text NOT NULL,
                user text NOT NULL,
                currenttimestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY id (id)
            ) $charset_collate;";
            require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }

        //add pop up
        if (get_option('ade_cart_manager_enable_popup')) {
            if (get_option('ade_cart_manager_enable_popup') == '') {
                update_option('ade_cart_manager_enable_popup', 'yes');
            } else {
                update_option('ade_cart_manager_enable_popup', 'yes');
            }
        } else {
            add_option('ade_cart_manager_enable_popup', 'yes');
        }
    }
}
