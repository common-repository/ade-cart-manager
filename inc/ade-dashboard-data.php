<?php
// add basic plugin security.
defined('ABSPATH') || exit;
class ADEDASHBOARD
{
    public static function ade_cart_manager_page()
    {
        ob_start();
?>
        <?php
        //enqueue stylesheet instead
        wp_enqueue_style('ade-cart-manager-datatable-css', plugin_dir_url(ADE_CART_PLGUN_FILE) . 'assets/css/datatable.css');
        ?>
        <style>
            .ade-cart-manager {
                border: 1px solid #ccc;
                border-collapse: collapse;
                margin: 0;
                padding: 0;
                width: 100%;
                table-layout: fixed;
                font-size: 16px;
            }

            .ade-cart-manager caption {
                font-size: 1.5em;
                margin: .5em 0 .75em;
            }

            .ade-cart-manager tr {
                background-color: #f8f8f8;
                border: 1px solid #ddd;
                padding: .35em;
            }

            .ade-cart-manager th,
            .ade-cart-manager td {
                padding: .625em;
                text-align: center;
            }

            .ade-cart-manager th {
                font-size: .85em;
                letter-spacing: .1em;
                text-transform: uppercase;
            }

            @media screen and (max-width: 600px) {
                .ade-cart-manager {
                    border: 0;
                }

                .ade-cart-manager caption {
                    font-size: 1.3em;
                }

                .ade-cart-manager thead {
                    border: none;
                    clip: rect(0 0 0 0);
                    height: 1px;
                    margin: -1px;
                    overflow: hidden;
                    padding: 0;
                    position: absolute;
                    width: 1px;
                }

                .ade-cart-manager tr {
                    border-bottom: 3px solid #ddd;
                    display: block;
                    margin-bottom: .625em;
                }

                .ade-cart-manager td {
                    border-bottom: 1px solid #ddd;
                    display: block;
                    font-size: .8em;
                    text-align: right;
                }

                .ade-cart-manager td::before {
                    /*
    * aria-label has no advantage, it won't be read inside a .ade-cart-manager
    content: attr(aria-label);
    */
                    content: attr(data-label);
                    float: left;
                    font-weight: bold;
                    text-transform: uppercase;
                }

                .ade-cart-manager td:last-child {
                    border-bottom: 0;
                }
            }
        </style>
        <div class="wrap">
            <h1>Cart Manager</h1>
            <?php
            if (isset($_POST["save_popup_settings"])) {
                $enablepopup = sanitize_text_field($_POST["enablepopup"]);
                if (get_option('ade_cart_manager_enable_popup')) {
                    if (get_option('ade_cart_manager_enable_popup') == '') {
                        update_option('ade_cart_manager_enable_popup', $enablepopup);
                    } else {
                        update_option('ade_cart_manager_enable_popup', $enablepopup);
                    }
                } else {
                    add_option('ade_cart_manager_enable_popup', $enablepopup);
                }

                //show alert
                echo '<div class="notice notice-success is-dismissible" style="display:block;">
            <p>Cart Manager Popup Settings Updated</p>
            </div>';
            }
            ?>
            <form action="" method="post">
                <label for="">
                    <select name="enablepopup" id="" style="width: fit-content;">
                        <option value="no" <?php if (get_option('ade_cart_manager_enable_popup') == 'no') {
                                                echo 'selected';
                                            } ?>>
                            Disable Popup</option>
                        <option value="yes" <?php if (get_option('ade_cart_manager_enable_popup') == 'yes') {
                                                echo 'selected';
                                            } ?>>Enable Popup
                        </option>
                    </select>
                </label> <br>
                <input type="submit" style="    margin-top: 5px;" value="Save" class="button" name="save_popup_settings">
            </form>
            <p>
            <div style="    width: fit-content;
    border: 1px solid lightgray;
    background: white;
    padding: 0px 12px 0px 12px;
    margin-bottom: 10px;">
                <p style="margin: 0px;
    margin-top: 4px;
    font-weight: bold;
    border-bottom: 1px solid black;">Recent Searches</p>
                <ul style="margin-top: 5px;list-style-type: none;" id="recentsearches">

                </ul>
            </div>
            <table class="widefat fixed ade-cart-manager" cellspacing="0" id="ade-cart-manager-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Cart Data</th>
                        <th>Searches</th>
                        <th>Page Visited</th>
                        <th>Date</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>No data found</td>
                    </tr>
                </tbody>
            </table>
            </p>
        </div>
        <?php
        //enqueue script instead
        wp_enqueue_script('ade-cart-manager-script', plugin_dir_url(ADE_CART_PLGUN_FILE) . 'assets/js/datatable.js', array('jquery'), '1.0.0', true);
        ?>
        <style>
            /* The Modal (background) */
            .xpdmodal {
                display: none;
                position: fixed;
                z-index: 99999999999999999999999999999999999999999999999999;
                /* padding-top: 100px; */
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgb(0, 0, 0);
                background-color: rgb(0 0 0 / 50%);
                backdrop-filter: blur(2px);
            }

            /* Modal Content */
            .xpdmodal-content {
                background-color: #fefefe;
                margin: auto;
                overflow: scroll;
                padding: 20px;
                border: 1px solid #888;
                width: 23%;
                animation-name: animatetop;
                animation-duration: 0.4s;
                height: auto;
                margin-top: 100px;
            }

            /* The Close Button */
            .close {
                color: #aaaaaa;
                float: right;
                font-size: 28px;
                font-weight: bold;
            }

            .close:hover,
            .close:focus {
                color: #000;
                text-decoration: none;
                cursor: pointer;
            }

            /* Add Animation */
            @keyframes animatetop {
                from {
                    top: -300px;
                    opacity: 0
                }

                to {
                    top: 0;
                    opacity: 1
                }
            }

            /* Extra small devices (phones, 600px and down) */
            @media only screen and (max-width: 600px) {
                .xpdmodal-content {
                    width: 100%;
                    height: 500px;
                }

                .xpdmodal {
                    top: 0;
                }
            }

            /* Small devices (portrait tablets and large phones, 600px and up) */
            @media only screen and (min-width: 600px) {
                .xpdmodal-content {
                    width: 100%;
                    height: 500px;
                }

                .xpdmodal {
                    top: 0;
                }
            }

            /* Medium devices (landscape tablets, 768px and up) */
            @media only screen and (min-width: 768px) {
                .xpdmodal-content {
                    width: 100%;
                    height: 500px;
                }

                .xpdmodal {
                    top: 0;
                }
            }

            /* Large devices (laptops/desktops, 992px and up) */
            @media only screen and (min-width: 992px) {
                .xpdmodal-content {
                    width: 650px;
                    height: 500px;
                }

                .xpdmodal {
                    top: 0;
                }
            }

            /* Extra large devices (large laptops and desktops, 1200px and up) */
            @media only screen and (min-width: 1200px) {
                .xpdmodal-content {
                    width: 700px;
                    height: 500px;
                }

                .xpdmodal {
                    top: 0;
                }
            }

            .ptitle {
                font-size: 20px;
                margin-top: 0px;
            }

            .notice {
                display: none !important;
            }
        </style>
        <div id="myModal" class="xpdmodal">

            <!-- Modal content -->
            <div class="xpdmodal-content">
                <div style="width:100%">
                    <h4 style="float:left;" class="ptitle" id="welltitle">User Cart
                        Manager</h4>
                    <span class="close">&times;</span>
                </div>
                <div class="content" style="clear: both;">
                    <center><img style="    height: 72px;top: 70%;margin-top: 80px;" src="<?php echo plugin_dir_url(ADE_CART_PLGUN_FILE) . '/assets/Spinner-1s-200px.gif'; ?>" alt="">
                    </center>
                </div>
            </div>

        </div>
        <script>
            jQuery(document).ready(function($) {
                setTimeout(() => {
                    $("div.wcfm_customers_filter_wrap.wcfm_products_filter_wrap.wcfm_filters_wrap").hide();
                }, 2000);
            });
            // Get the modal
            var modal = document.getElementById("myModal");

            // Get the button that opens the modal
            var btn = document.getElementById("myBtn");

            // Get the <span> element that closes the modal
            var span = document.getElementsByClassName("close")[0];

            // When the user clicks the button, open the modal 
            let openmodal = function() {
                modal.style.display = "block";
            }

            // When the user clicks on <span> (x), close the modal
            span.onclick = function() {
                modal.style.display = "none";
            }

            // When the user clicks anywhere outside of the modal, close it
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }

            let showUserCartData = (elem) => {
                //check if data-searches is set
                if (elem.getAttribute('data-searches')) {
                    let searches1 = decodeURIComponent(elem.getAttribute('data-searches'));
                    let searches = JSON.parse(searches1);
                    let user = JSON.parse(elem.getAttribute('data-user'));
                    let userip = JSON.parse(elem.getAttribute('data-user-ip'));
                    let wc_sessiond = elem.getAttribute('data-wc-session');
                    //explode wc_sessiond
                    let wc_sessionemail = wc_sessiond.split('-');
                    let finalemail = wc_sessionemail[2];
                    //replace . with empty
                    wc_session2 = wc_sessiond.replace(/\./g, '');
                    //replace - with empty
                    wc_sessione = wc_session2.replace(/-/g, '');
                    //remove @ from string
                    wc_session = wc_sessione.replace(/@/g, '');
                    //convert to array
                    let searchesitem = [];
                    for (let key in searches) {
                        searchesitem.push(searches[key]);
                    }
                    console.log(searchesitem);
                    jQuery(document).ready(function($) {
                        $("#myModal").find(".ptitle").text("User Searches Data");
                        $("#myModal").find(".content").html(`
        <hr>
            ${(user == "guest") ? `<h4>${finalemail}</h4>` : `<h4>User: ${user.data.user_email}</h4>`}
            <h4>Search Data</h4>
            <table class="table table-striped table-bordered ade-cart-manager ${wc_session}" style="width:100%">
                <thead>
                    <tr>
                        <th>Search</th>
                        <th>Datetime</th>
                    </tr>
                </thead>
                <tbody>
                    ${searchesitem.map(item => `
                    <tr>
                        <td><a href="<?php echo esc_html(site_url()) ?>?s=${item.search_text}&post_type=product" target="_blank">${(item.search_text.charAt(0).toUpperCase() + item.search_text.slice(1)).replace(/\+/g, ' ')}</a></td>
                        <td>${item.timestamp.date}</td>
                    </tr>
                    `).join('')}
                </tbody>
            </table>
        `);
                        $(`.${wc_session}`).DataTable({
                            responsive: true,
                            columnDefs: [{
                                type: 'date',
                                'targets': [1]
                            }],
                            order: [
                                [1, 'desc']
                            ],
                        });
                        openmodal();
                    });
                } else {
                    //clear modal 
                    let cart1 = decodeURIComponent(elem.getAttribute('data-cart'));
                    let cart = JSON.parse(cart1);
                    let user = JSON.parse(elem.getAttribute('data-user'));
                    let userip = JSON.parse(elem.getAttribute('data-user-ip'));
                    let wc_sessiond = elem.getAttribute('data-wc-session');
                    //explode wc_sessiond
                    let wc_sessionemail = wc_sessiond.split('-');
                    let finalemail = wc_sessionemail[2];
                    //replace . with empty
                    wc_session2 = wc_sessiond.replace(/\./g, '');
                    //replace - with empty
                    wc_sessione = wc_session2.replace(/-/g, '');
                    //remove @ from string
                    wc_session = wc_sessione.replace(/@/g, '');
                    //convert to array
                    let cartitems = [];
                    for (let key in cart) {
                        cartitems.push(cart[key]);
                    }
                    jQuery(document).ready(function($) {
                        $("#myModal").find(".ptitle").text("User Cart Data");
                        $("#myModal").find(".content").html(`
        <hr>
            ${(user == "guest") ? `<h4>${finalemail}</h4>` : `<h4>User: ${user.data.user_email}</h4>`}
            <h4>Cart Items</h4>
            <table class="table table-striped table-bordered ade-cart-manager ${wc_session}" style="width:100%">
                <thead>
                    <tr>
                        <th>Thumbnail</th>
                        <th>Product Name</th>
                        <th>Product Price</th>
                        <th>Product Quantity</th>
                        <th>Product Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${cartitems.map(item => `
                    <tr>
                        <td style="width: 10%;"><img src="${item.product_image}" style="height:30px;" /></td>
                        <td><a href="${item.product_url}" target="_blank">${item.product_name.replace(/\+/g, ' ')}</a></td>
                        <td>${item.product_price}</td>
                        <td>${item.product_quantity}</td>
                        <td><?php echo esc_html(get_woocommerce_currency_symbol()); ?>${item.product_total}</td>
                    </tr>
                    `).join('')}
                </tbody>
            </table>
        `);
                        $(`.${wc_session}`).DataTable({
                            responsive: true,
                            "order": [
                                [1, "desc"]
                            ]
                        });
                        openmodal();
                    });
                }
            }

            let showUserPageVisited = (elem) => {
                var pages = JSON.parse(elem.getAttribute('data-pages'));
                //convert to array
                let pagesitems = [];
                for (let key in pages) {
                    pagesitems.push(pages[key]);
                }
                jQuery(document).ready(function($) {
                    $("#myModal").find(".ptitle").text("User Page Data");
                    $("#myModal").find(".content").html(`
        <hr>
        <h4>User Page Visited</h4>
        <table class="table table-striped ade-cart-manager ade-cart-manager-2 table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>Page Title</th>
                    <th>Page Time</th>
                </tr>
            </thead>
            <tbody>
                ${pagesitems.map(item => `
                <tr>
                    <td><a href="${item.page_url}" target="_blank">${item.page_title.replace(/\+/g, ' ')}</a></td>
                    <td>${item.timestamp.date}</td>
                </tr>
                `).join('')}
            </tbody>
        </table>
    `);
                    $(".ade-cart-manager-2").DataTable({
                        responsive: true,
                        "order": [
                            [1, "desc"]
                        ]
                    });
                    openmodal();
                });
            }
        </script>
        <script>
            jQuery(document).ready(function($) {
                let loadadetabledata = () => {
                    var tabledata = $("#ade-cart-manager-table");
                    $.ajax({
                        type: "GET",
                        url: "<?php echo admin_url('admin-ajax.php'); ?>",
                        data: {
                            action: "ade_cart_manager_ajax_dataAPI",
                            time: new Date().getTime()
                        },
                        beforeSend: () => {
                            tabledata.find('tbody').html(`
            <tr>
                <td colspan="6" style="text-align: center;">
                    <img style="    height: 72px;top: 70%;"
                    src="<?php echo plugin_dir_url(ADE_CART_PLGUN_FILE) . '/assets/Spinner-1s-200px.gif'; ?>" alt="">
                </td>
            </tr>
            `);
                        },
                        success: function(response) {
                            //convert to array response.data
                            let cartitems = [];
                            for (let key in response.data) {
                                cartitems.push(response.data[key]);
                            }

                            tabledata.find('tbody').html(`
            ${cartitems.map(item => `
                    <tr>
                    <td>${item.user == "guest" ? item.wc_session.split('-')[2] : item.user}</td>
                    <td><a href="javascript:;" onclick="showUserCartData(this)"
                            data-cart='${item.cart}'
                            data-user='${item.user_api}'
                            data-user-ip='${item.user_ip}'
                            data-wc-session="${item.wc_session}">${item.user_cart_count}
                            View</a>
                    </td>
                    <td><a href="javascript:;" onclick="showUserCartData(this)"
                            data-searches='${item.searches}'
                            data-user='${item.user_api}'
                            data-user-ip='${item.user_ip}'
                            data-wc-session="${item.wc_session}">${item.searches_count}
                            View</a>
                    </td>
                    <td><a href="javascript:;" onclick="showUserPageVisited(this)"
                            data-pages='${item.page_visited}'>${item.page_count}
                            View</a>
                    </td>
                    <td>${item.timestamp}</td>
                    <td>${item.user_role}</td>
                </tr>
                `).join('')}
            `);
                            tabledata.DataTable({
                                responsive: true,
                                columnDefs: [{
                                    type: 'date',
                                    'targets': [4]
                                }],
                                order: [
                                    [4, 'desc']
                                ],
                            });
                        }
                    });

                    var recenthtml = $("#recentsearches");
                    $.ajax({
                        type: "GET",
                        url: "<?php echo admin_url('admin-ajax.php'); ?>",
                        data: {
                            action: "ade_cart_manager_ajax_dataAPI",
                            time: new Date().getTime(),
                            type: 'recent',
                            limit: 5,
                            starting: 0
                        },
                        beforeSend: () => {
                            recenthtml.html(`
                    <li style="text-align: center;">
                    <img style="    height: 35px;top: 70%;"
                    src="<?php echo plugin_dir_url(ADE_CART_PLGUN_FILE) . '/assets/Spinner-1s-200px.gif'; ?>" alt="">
                    </li>
                `)
                        },
                        success: function(response) {
                            //convert to array response.data
                            let htmldata = '';
                            $.each(response.data, function(indexInArray, valueOfElement) {
                                htmldata += `
                    <li>
                        <a href="<?php echo esc_html(site_url()) ?>?s=${indexInArray}&post_type=product" target="_blank">${indexInArray.charAt(0).toUpperCase() + indexInArray.slice(1)} <small>(${valueOfElement})</small></a>
                    </li>
                    `;
                            });
                            //add see more button
                            htmldata += `
                <li onclick="seemorerecent(this)">
                    <a href="javascript:;">See More</a>
                </li>
                `;
                            recenthtml.html(htmldata);
                        }
                    });
                }

                loadadetabledata();
            });

            let seemorerecent = (elem) => {
                jQuery(function($) {
                    let recenthtml = $("#recentsearches");
                    let limit = recenthtml.find('li').length;
                    let starting = recenthtml.find('li').length;
                    $.ajax({
                        type: "GET",
                        url: "<?php echo admin_url('admin-ajax.php'); ?>",
                        data: {
                            action: "ade_cart_manager_ajax_dataAPI",
                            time: new Date().getTime(),
                            type: 'recent',
                            limit: 5,
                            starting: starting - 1
                        },
                        beforeSend: () => {
                            //append before the last li
                            recenthtml.find('li:last-child').html(`
                    <img style="    height: 35px;top: 70%;"
                    src="<?php echo plugin_dir_url(ADE_CART_PLGUN_FILE) . '/assets/Spinner-1s-200px.gif'; ?>" alt="">
                `)
                        },
                        success: function(response) {
                            //convert to array response.data
                            let htmldata = '';
                            $.each(response.data, function(indexInArray, valueOfElement) {
                                htmldata += `
                <li>
                    <a href="<?php echo esc_html(site_url()) ?>?s=${indexInArray}&post_type=product" target="_blank">${indexInArray.charAt(0).toUpperCase() + indexInArray.slice(1)} <small>(${valueOfElement})</small></a>
                </li>
                `;
                            });
                            recenthtml.find('li:last-child').before(htmldata);
                            recenthtml.find('li:last-child').html(`
                <a href="javascript:;">See More</a>
                `)
                        }
                    });
                });
            }
        </script>
        <?php
        return ob_get_clean();
    }

    public static function dashboard_view()
    {
        echo self::ade_cart_manager_page();
    }

    public static function dashboard_view2()
    {
        if (isset($_GET["p"]) && $_GET["p"] == "cartmanager") {

        ?>
            <style>
                .mycontent h1 {
                    font-size: 20px;
                }

                #wwcfm_customers_expander {
                    display: none;
                }
            </style>
            <div class="wcfm-container">
                <div class="wcfm-content mycontent">
                    <?php
                    echo self::ade_cart_manager_page();
                    ?>
                </div>
            </div>
<?php
        }
    }

    /**
     * WCFM Articles Menu
     */
    function cart_manager_wcfm_menus($menus)
    {
        global $WCFM;

        $articles_menus = array('wcfm-ade-cart-manager-wcfm_menus' => array(
            'label'  => __('Cart Manager', 'wc-frontend-manager'),
            'url'       => get_wcfm_customers_url() . "?p=cartmanager",
            'icon'      => 'fa-shopping-cart',
            'priority'  => 4
        ));

        $menus = array_merge($menus, $articles_menus);
        return $menus;
    }

    // adding to the menu
    function adecartmanagermenu()
    {
        add_menu_page(
            'Cart Manager', // $page_title
            'Cart Manager', // $menu_title
            'manage_options', //  $capability
            'ade-cart-manager', // $menu_slug
            [ADEDASHBOARD::class, 'dashboard_view'], // $function
            'dashicons-cart', // $icon_url
            2 // Plugin $position
        );
    }

    function loaddata()
    {
        $pluginList = get_option('active_plugins');
        $plugin = 'wc-multivendor-marketplace/wc-multivendor-marketplace.php';
        if (in_array($plugin, $pluginList)) {
            if (is_user_logged_in()) {
                $user = wp_get_current_user();
                $allowed_roles = array('shop_manager', 'administrator', 'vendor_staff');
                if (array_intersect($allowed_roles, $user->roles)) {
                    add_action("before_wcfm_customers", [ADEDASHBOARD::class, 'dashboard_view2']);
                    add_filter('wcfm_menus', array(ADEDASHBOARD::class, 'cart_manager_wcfm_menus'), 20);
                }
            }
        }
    }

    public function ade_cart_manager_ajax()
    {
        if (!is_user_logged_in()) {
            $email = sanitize_email($_POST['email']);
            //add wordpress cookies
            if (!session_id()) {
                session_start();
            }

            if (!isset($_SESSION['ade_user_cart_data'])) {
                $session = $_SESSION["ade_user_cart_data"] = "wc-ade-" . $email;
            } else {
                $session = $_SESSION['ade_user_cart_data'];
            }
            echo wp_send_json(['code' => 200, 'message' => 'success']);
            die;
        } else {
            echo wp_send_json(['code' => 200, 'message' => 'success']);
            die;
        }
    }
}

add_filter('wp', [ADEDASHBOARD::class, 'loaddata'], 10);
