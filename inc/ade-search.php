<?php
// add basic plugin security.
defined('ABSPATH') || exit;
class AdeSearch
{
    public function init()
    {
        add_filter('wp', [$this, 'checkready'], 10);
    }

    public function checkready()
    {
        if (!session_id()) {
            session_start();
        }
        if (is_user_logged_in()) {
            if (isset($_SESSION['ade_user_cart_data'])) {
                add_action("wp_head", [$this, "getsearchText"]);
            }
        }
    }

    public function getsearchText()
    {
?>
        <script>
            jQuery(function($) {
                //use document instead
                $("input[name='s']").blur(function(e) {
                    e.preventDefault();
                    var search_text = $(this).val();
                    if (search_text != '') {
                        $.ajax({
                            type: "GET",
                            url: "<?php echo admin_url('admin-ajax.php'); ?>",
                            data: {
                                action: 'ade_cart_manager_ajax_process',
                                current_page: '<?php echo get_the_ID(); ?>',
                                time: new Date().getTime(),
                                ade_nounce: '<?php echo wp_create_nonce('ade_cart_manager_ajax_process'); ?>',
                                search_text: search_text
                            },
                            success: function(response) {}
                        });
                    }
                });

                <?php
                if (isset($_GET["s"]) && !empty($_GET["s"])) {
                    $search_text = sanitize_text_field($_GET["s"]);
                ?>
                    $.ajax({
                        type: "GET",
                        url: "<?php echo admin_url('admin-ajax.php'); ?>",
                        data: {
                            action: 'ade_cart_manager_ajax_process',
                            current_page: '<?php echo get_the_ID(); ?>',
                            time: new Date().getTime(),
                            ade_nounce: '<?php echo wp_create_nonce('ade_cart_manager_ajax_process'); ?>',
                            search_text: '<?php echo esc_html($search_text); ?>'
                        },
                        success: function(response) {}
                    });
                <?php
                }
                ?>
            });
        </script>
<?php
    }
}

//init
$ade_search = new AdeSearch();
$ade_search->init();
