<?php
// add basic plugin security.
defined('ABSPATH') || exit;
function ade_cart_manager_page_link($links)
{
    $links[] = '<a href="' .
        admin_url('admin.php?page=ade-cart-manager') .
        '">' . __('Manage Cart Data') . '</a>';
    return $links;
}
// Menu
add_filter('plugin_action_links_' . plugin_basename(ADE_CART_PLGUN_FILE), 'ade_cart_manager_page_link');
add_action('admin_menu', [ADEDASHBOARD::class, 'adecartmanagermenu']);

//add wp ajax
add_action('wp_ajax_ade_cart_manager_ajax', [ADEDASHBOARD::class, 'ade_cart_manager_ajax']);
add_action('wp_ajax_nopriv_ade_cart_manager_ajax', [ADEDASHBOARD::class, 'ade_cart_manager_ajax']);

function ade_cart_pop_up_manager()
{
    //drop your email
?>
    <style>
        .adedonebutton {
            background: #9d9d9d;
        }

        .adedonebutton:hover {
            background-color: black;
        }
    </style>
    <div id="adecartmanagerid" style="    width: 220px;
        display: none;
    z-index: 260;
    position: fixed;
        border-radius: 20px;
      bottom: 5%;
    left: 3%;">
        <div style="    width: 100%;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: rgb(0 0 0 / 35%) 0 6px 100px 0;    padding: 10px;
    text-align: center;">
            <img src="<?php echo plugin_dir_url(ADE_CART_PLGUN_FILE); ?>/assets/img/cart.gif" style="height: 50px;    margin-top: -63px;" alt="">
            <div style="width: fit-content;
    margin-top: -48px;
    margin-left: -21px;
    font-size: 13px;
    background: gray;
    color: white;
    border-radius: 46px;
    padding: 1px;
    padding-left: 10px;
    padding-right: 10px;cursor: pointer;" id="adeclosepopup">X</div>
            <div style="    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif !important;
    -webkit-font-smoothing: auto;
    -moz-osx-font-smoothing: auto;margin-top: 10px;">
                <p style="    font-size: 19px;
    font-weight: normal;
    padding: 10px;
    padding-top: 0px;
    padding-bottom: 0px;;">Let help you arround the cart</p>
                <input type="email" name="useremail" id="useremail" placeholder="Kindly input your email" style="border: 1px solid lightgrey;
    width: 100%;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif !important;text-align: center;background: transparent;
    border-top: none;
    border-right: none;
    border-left: none;
    height: 20px;">
                <p id="ade_error_log" style="text-align: center;    text-align: center;
    padding: 0px;
    margin: 0px;
    color: red;display:none;">

                </p>
                <button style="    margin-top: 5px;
    width: 180px;
    padding: 4px;
    color: white;
    cursor: pointer;
    border-color: unset;
    display: initial;
    text-align: center;
    white-space: nowrap;
    font-size: inherit;
    height: auto;
    line-height: inherit;
    letter-spacing: 0px;
    border: none;
    border-radius: 2px;
    font-weight: normal;
    font-size: 12px;
    text-transform: uppercase;" class="adedonebutton" id="adecartsubmit">Done</button>

            </div>
        </div>
    </div>
    <script>
        function playAudio(type) {
            var audio = new Audio('<?php echo plugin_dir_url(ADE_CART_PLGUN_FILE); ?>/assets/sound/' + type + '.mp3');
            audio.play();
        }

        jQuery(function($) {
            setTimeout(() => {
                $("#adecartmanagerid").fadeIn();
            }, 2000);

            $("#adeclosepopup").click(function(e) {
                e.preventDefault();
                playAudio("out");
                $("#adecartmanagerid").fadeOut();
            });

            $("#adecartsubmit").click(function(e) {
                e.preventDefault();
                var error = $("#adecartmanagerid").find("#ade_error_log");
                var email = $("#adecartmanagerid").find("#useremail").val();
                if (email == "") {
                    //slide in error message
                    error.html("Kindly input your email").fadeOut().fadeIn();
                    return false;
                } else {
                    //validate email
                    var re =
                        /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                    //check if email not valid
                    if (!re.test(String(email).toLowerCase())) {
                        //slide in error message
                        error.html("Invalid email").fadeOut().fadeIn();
                        return false;
                    }

                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'post',
                        data: {
                            action: 'ade_cart_manager_ajax',
                            email: email,
                            time: new Date().getTime()
                        },
                        beforeSend: () => {
                            error.fadeOut();
                            $("#adecartmanagerid").find("#adecartsubmit").css("background-color",
                                "gray !important");
                            $("#adecartmanagerid").find("#adecartsubmit").css("cursor",
                                "not-allowed");
                            $("#adecartmanagerid").find("#adecartsubmit").html("Sending...");
                        },
                        success: function(response) {
                            $("#adecartmanagerid").find("#adecartsubmit").css("cursor",
                                "pointer");
                            if (response.code == 200) {
                                playAudio("sent");
                                $("#adecartmanagerid").find("#adecartsubmit").css(
                                    "background-color",
                                    "green !important");
                                $("#adecartmanagerid").find("#adecartsubmit").html("Success");
                                $("#adecartmanagerid").fadeOut();
                            } else {
                                playAudio("out");
                                $("#adecartmanagerid").find("#adecartsubmit").css(
                                    "background-color",
                                    "red !important");
                                $("#adecartmanagerid").find("#adecartsubmit").html("Error");
                                setTimeout(() => {
                                    $("#adecartmanagerid").find("#adecartsubmit").css(
                                        "background-color",
                                        "black !important");
                                    $("#adecartmanagerid").find("#adecartsubmit").html(
                                        "Try Again");
                                }, 2000);
                            }
                        }
                    });
                }
            });
        });
    </script>
    <?php
}

function ade_cart_footer_script()
{
    function ade_cart_pop_up_manager_logged_in()
    {
    ?>
        <script>
            jQuery(function($) {
                $.ajax({
                    type: "GET",
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    data: {
                        action: 'ade_cart_manager_ajax_process',
                        current_page: '<?php echo get_the_ID(); ?>',
                        time: new Date().getTime(),
                        ade_nounce: '<?php echo wp_create_nonce('ade_cart_manager_ajax_process'); ?>'
                    },
                    success: function(response) {}
                });
            });
        </script>
<?php
    }

    //add wordpress cookies
    if (!session_id()) {
        session_start();
    }
    if (!is_user_logged_in()) {
        if (!isset($_SESSION['ade_user_cart_data'])) {
            if (get_option('ade_cart_manager_enable_popup') == "yes") {
                add_action('wp_footer', 'ade_cart_pop_up_manager');
            }
        } else {
            add_action('wp_footer', 'ade_cart_pop_up_manager_logged_in');
        }
    } else {
        add_action('wp_footer', 'ade_cart_pop_up_manager_logged_in');
    }
}

add_filter('wp', 'ade_cart_footer_script', 10);

function ade_cart_clear_current_session()
{
    if (!session_id()) {
        session_start();
    }
    unset($_SESSION['ade_user_cart_data']);
}
add_action('wp_logout', 'ade_cart_clear_current_session');
