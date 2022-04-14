<?php
class qiwicontroller
{

    function __construct()
    {


        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_enqueue_style', [$this, 'enqueue_style']);
    }
    function enqueue_style()
    {




        wp_enqueue_style('qiwi-chart-style-calendar', 'https://npmcdn.com/flatpickr/dist/themes/confetti.css', '', rand(0, 100));
        wp_enqueue_style('qiwi-chart-style-calendar', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', '', rand(0, 100));
        wp_enqueue_style('qiwi-chart-style', plugins_url('css/style.css',  dirname(__FILE__)), '', rand(0, 100));
    }

    function enqueue_scripts()
    {
        // Script Registration 
        wp_register_script('qiwi-chart', plugins_url('js/chart.min.js', dirname(__FILE__)), array('jquery'), '1.1', false);
        wp_enqueue_script('qiwi-chart-calendar', 'https://cdn.jsdelivr.net/npm/flatpickr', '', '1.1', false);
        wp_enqueue_script('qiwi-chart');
        wp_localize_script('qiwi-chart', 'myAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
        //      wp_enqueue_script('qiwi-chart-helper', plugins_url('js/helpers.esm.js',  dirname(__FILE__)),'','1.1', false);
        // Script Enqueue
    }


    function nav_list()
    {
        global $wpdb;
        wp_enqueue_style('qiwi-chart-style-datatable', '//cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css', '', rand(0, 100));
        wp_enqueue_script('qiwi-chart-datatable', '//cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js', '', '1.1', false);
        // Get Nav data for last 50 weeks

        if (!isset($_REQUEST['id'])) {



            $begin = new DateTime('2021-01-01');
            $end = new DateTime(date('Y-m-d'));

            $interval = DateInterval::createFromDateString('1 day');
            $period = new DatePeriod($begin, $interval, $end);
            $date_list['relation'] = 'OR';
            foreach ($period as $dt) {
                if ($dt->format("l") == 'Friday') {
                    $date_list_opt[] =  ['year' => $dt->format("Y"), 'month' => $dt->format("m"), 'day' => $dt->format("d"), 'compare' => 'IN', 'column' => 'post_date'];
                }
            }
            $date_list = array_merge($date_list, $date_list_opt);

            $query_string =  [
                'post_type' => 'stock_instrument',
                'posts_per_page' => -1,
                'orderby' => 'post_date',
                'order' => 'desc',
            ];
            $query_string['date_query'] = $date_list;

            $query = new WP_Query($query_string);
            echo "<h1>NAV Data Listing</h1> <div style='width:98%; margin:0 auto;background:#fff; padding:10px;'>";
            $row = 1;
            echo "<table id='table_id' class='table display'  ><thead><tr><th width='50'  style='text-align:left'>Week #</th><th style='text-align:left'>Title</th><th style='text-align:center'>NAV</th><th style='text-align:right'>Date</th></tr></thead><tbody>";
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                echo "<tr>";
                echo "<td>" . date("W", strtotime(get_post_meta($post_id, 'Date', true))) . "</td>";
                echo "<td ><a href='edit.php?post_type=stock_instrument&page=qwi_nav_list&id=$post_id'>" . get_the_title($post_id) . "</a></td>";
                if (get_post_meta($post_id, 'NAV', true)) {
                    echo "<td style='text-align:center'>" . str_replace("$", "", get_post_meta($post_id, 'NAV', true)) . "</td>";
                } else {
                    echo "<td>N/A</td>";
                }
                echo "<td style='text-align:right'>" . date("M d, Y ", strtotime(get_post_meta($post_id, 'Date', true))) . "</td>";

                $row++;
                echo "</tr>";
            }
            echo "</tbody></table></div>
                                    <script>
                                    jQuery(document).ready( function () {
                                        jQuery('#table_id').DataTable({
                                            autoWidth: false,    
                                            pageLength: 30,
                                            sorting:false
                                        });
                                    } );
                                    </script>
                                    ";

            wp_reset_postdata();
        } else {
            $post_id = $_REQUEST['id'];
            if (isset($_POST['submit'])) {
                // update NAV info
                update_post_meta($post_id, 'NAV', $_POST['NAV']);
                $msg = "<div class='success'>NAV value updated successfully</div>";
            }

            $NAV = str_replace("$", "", get_post_meta($post_id, 'NAV', true));

            echo "<h1>NAV Update: " . get_the_title($post_id) . "</h1>";

            include(plugin_dir_path(dirname(__FILE__)) . 'views/admin/nav_form.php');
        }
    }
    function postType_stock_instrument()
    {

        /**
         * Post Type: Stock instrument.
         */

        $labels = [
            "name" => __("Stock Instrument"),
            "singular_name" => __("Stock Instruments"),
        ];

        $args = [
            "label" => __("Stock Instrument"),
            "labels" => $labels,
            "description" => "",
            "public" => true,
            "publicly_queryable" => true,
            "show_ui" => true,
            "show_in_rest" => true,
            "rest_base" => "",
            "rest_controller_class" => "WP_REST_Posts_Controller",
            "has_archive" => false,
            "show_in_menu" => true,
            "show_in_nav_menus" => true,
            "delete_with_user" => false,
            "exclude_from_search" => false,
            "capability_type" => "post",
            "capabilities" => array("edit_posts", "manage_options"),
            "map_meta_cap" => true,
            "hierarchical" => false,
            "rewrite" => ["slug" => "stock_instrument", "with_front" => true],
            "query_var" => true,
            "supports" => ["title", "custom-fields"],
            "show_in_graphql" => false,
        ];

        register_post_type("stock_instrument", $args);
    }
    function add_import_submenu()
    {
        add_submenu_page('edit.php?post_type=stock_instrument', 'NAV Update', 'NAV Update', 'edit_posts', 'qwi_nav_list', [$this, 'nav_list']);
        add_submenu_page('edit.php?post_type=stock_instrument', 'Qwi Historical CSV Import', 'Historical Import', 'edit_posts', 'qwi_historical', [$this, 'csv_import_form']);
    }

    function import_from_csv($csv)
    {



        $row = 1;
        if (($handle = fopen($csv, 'r')) !== FALSE) {
            $row = 1;

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $num = count($data);
                if ($row == 1) {
                    //Get Key Name
                    $keyname = $data;
                    $row++;
                    continue;
                }


                $row++;

                for ($c = 0; $c < $num; $c++) {
                    if ($c == 0) {
                        $post_title = wp_strip_all_tags($data[0] . " on " . date('l jS F Y', strtotime($data[1])));
                        // Add Post and get ID 
                        // Create post object
                        $my_post = array(
                            'post_title'    =>  $post_title,
                            'post_status'   => 'publish',
                            'post_type' => 'stock_instrument',
                            'post_date' => date('Y-m-d H:i:s', strtotime($data[1])),
                            'post_author'   => 1,

                        );
                        //print_r($post_title);

                        $check_post_exist = post_exists($post_title);
                        // Insert the post into the database
                        if (!$check_post_exist) {
                            $post_id = wp_insert_post($my_post);
                        } else {
                            $post_id = $check_post_exist;
                        }
                    }
                    // add meta to the post data 
                    if (!$check_post_exist) {
                        add_post_meta($post_id,  $keyname[$c], $data[$c]);
                    } else {
                        update_post_meta($post_id,  $keyname[$c], $data[$c]);
                    }
                }
            }
            fclose($handle);
        }
    }

    function csv_import_form()
    {
        if (isset($_POST['csvupload']) && isset($_FILES['csv_import']["tmp_name"])) {
            $target_dir = wp_upload_dir();
            $target_file = $target_dir['path'] . basename($_FILES["csv_import"]["name"]);
            if (move_uploaded_file($_FILES["csv_import"]["tmp_name"], $target_file)) {
                $this->import_from_csv($target_file);
                $msg = "<div class='success'> File was uploaded successfully</div>";
            } else {
                $msg = "<div class='fail'> File fail to upload</div>";
            }
        }

        include(plugin_dir_path(dirname(__FILE__)) . 'views/admin/csv_import.php');
        $this->getJSE();
    }

    function filter_where($where = '', $dateparam1 = '', $dateparam2 = '')
    {
        // posts for March 1 to March 15, 2010
        //  $where .= " AND post_date >= '2021-01-01' AND post_date < '2010-03-16'";
        return $where;
    }


    function chart_data($startdate = null, $enddate = null)
    {

        $query_string =  [
            'post_type' => 'stock_instrument',
            'posts_per_page' => -1,
            'orderby' => 'post_date',
            'order' => 'ASC',
        ];

        if (empty($startdate) && empty($enddate)) {
            // $startdate  = date('Y')."-". date('m')."-1";
            $startdate  =  date("Y-m-d", strtotime("-400 days"));

            // echo $startdate;
            $query_string['date_query'] = [
                'before' => date("Y-m-d 00:00", strtotime("+1 day")),
                'after' => $startdate,
                //'inclusive' => true    
            ];

            // $query_string['date_query']=[
            //     'column'=>'post_date',
            //     'after'=> '-30 days'    
            // ];
        } else {

            $query_string['date_query'] = [
                'before' => date("Y-m-d 00:00", strtotime("+1 day", strtotime($enddate))),
                'after' => date("Y-m-d 00:00", strtotime("-1 day", strtotime($startdate))),
                //'inclusive' => true    
            ];
            //die(print_r($query_string));
        }
        //die(print_r($query_string));
        $query = new WP_Query($query_string);

        $axisData = [];
        // print_r($query); 


        $row = 0;
        $nav = 0;
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            if (str_replace("$", "", get_post_meta($post_id, 'NAV', true))) {
                $nav = str_replace("$", "", get_post_meta($post_id, 'NAV', true));
            } else {
                $nav = null;
            }
            $axisData[$row]['x'] = @date("M d, Y ", strtotime(get_post_meta($post_id, 'Date', true)));
            $axisData[$row]['closedprice'] =  @number_format(get_post_meta($post_id, 'Close Price', true), 2);
            $axisData[$row]['lasttraded'] =   @number_format(str_replace("$", "", get_post_meta($post_id, 'Last Traded', true)), 2);
            $axisData[$row]['closingbid'] =   @number_format(get_post_meta($post_id, 'Closing Bid', true), 2);
            $axisData[$row]['volume'] =   (str_replace(",", "", get_post_meta($post_id, 'Volume (non block)', true)));
            $axisData[$row]['nav'] =  ($nav ? $nav : null);
            $row++;
        }

        wp_reset_postdata();

        // print_r($axisData);

        return  $axisData;
    }
    function generate_chart_data_json()
    {
        extract($_POST);

        if (isset($startdate)) {
            header('Content-Type: application/json');
            echo   json_encode($this->chart_data($startdate, $enddate));
            die();
        } else {
            return  json_encode($this->chart_data($startdate, $enddate));
        }
    }
    function generate_chart()
    {
        $data                   =  json_encode($this->chart_data());
        $page    =  $this->getPage();
        $rx_right_sidebar =  '(<div class="tw\-grid tw\-mt.+<div class="tw\-border)i';

        preg_match($rx_right_sidebar, $page, $right_sidebar);


        $rx_stock_stats = '(<div class="tw\-flex.+</div>)Ui';

        preg_match_all($rx_stock_stats, $right_sidebar[0], $stock_stats);

        $stock_statistics = array();
        foreach($stock_stats[0] as $idx=>$stock_stat) {

            $rx_stats = '(<span[^>]+>(.+)</span>[ ]+<span[^>]+>(.+)</span>)Ui';
            preg_match($rx_stats, $stock_stat, $stat);
            $stock_statistics[$idx]['key'] = trim($stat[1]);
            $stock_statistics[$idx]['value'] = trim($stat[2]);
        }

        //print_r($stock_statistics)  ;


        $rx_mid_section = '(tw-mt-2 tw-grid tw-gap-6 lg.+</div>[ ]+<h3>)Ui';

        preg_match($rx_mid_section, $page, $mid_section);

        $rx_stock_stats = '(<div class="tw\-flex.+</div>)Ui';

        preg_match_all($rx_stock_stats, $mid_section[0], $stock_stats);

        $stock_statistics2 = array();

        foreach($stock_stats[0] as $idx=>$stock_stat) {

            $rx_stats = '(<span[^>]+>(.+)</span>[ ]+<span[^>]+>(.+)</span>)Ui';
            preg_match($rx_stats, $stock_stat, $stat);
            $stock_statistics2[$idx]['key'] = trim($stat[1]);
            $stock_statistics2[$idx]['value'] = trim($stat[2]);
        }

        //print_r($stock_statistics2) ;
        //$data = str_replace(array("\"x\"","\"y\""),array("x","y"),$data);
        include(plugin_dir_path(dirname(__FILE__)) . 'views/test.php');
    }

    function getJSE()
    {
        require_once ABSPATH . '/wp-admin/includes/post.php';
        $curlSession = curl_init();
        curl_setopt($curlSession, CURLOPT_URL, 'https://www.jamstockex.com/wp-json/jse-api/v1/instruments/latest/qwi');
        curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);

        $jsonData = json_decode(curl_exec($curlSession));
        curl_close($curlSession);
        if (!empty($jsonData)) {


            $post_title = wp_strip_all_tags("QWI on " . date('l jS F Y'));
            // Add Post and get ID 
            // Create post object
            $my_post = array(
                'post_title'    =>  $post_title,
                'post_status'   => 'publish',
                'post_type' => 'stock_instrument',
                'post_date' => date('Y-m-d H:i:s'),
                'post_author'   => 1,

            );
            

            $check_post_exist = post_exists($post_title);
            // Insert the post into the database
            if (!$check_post_exist) {
                $post_id = wp_insert_post($my_post);
            } else {
                $post_id = $check_post_exist;
            }
            $data  = $jsonData->Instrument;

           // print_r( $data);

            $close_price = $data->ClosePrice;
            $closingAsk = $data->ClosingAsk;
            $lastTrade  = $data->LastTradePrice;
            $fiftytwo_week_high =  $data->High52Week;
            $fiftytow_week_low = $data->Low52Week;
            $volumeTraded  = $data->VolumeTradedToday;
            $totalPricechange = $data->TodayPriceChange;
            $todayPercentage = $data->TodayPercentageChange;
            $divCurrentYear  =  $data->DivCurrentYearAmt;
            $todayHigh =  $data->TodaysHigh;
            $todayLow = $data->TodaysLow;
            $lastTraded = $data->LastTradePrice;
            $Volume = $data->VolumeTradedToday;
            $PreviousYear = $data->DivPreviousYearAmt;
            $TodaysOpen = $data->TodaysOpen;
            $ClosingBid = $data->ClosingBid;


            // add meta to the post data 
            if (!$check_post_exist) {
                add_post_meta($post_id, '52 Week High', $fiftytwo_week_high);
                add_post_meta($post_id, '52 Week Low', $fiftytow_week_low);
                add_post_meta($post_id, 'Close Price', $close_price);
                add_post_meta($post_id, 'Closing Ask', $closingAsk);
                add_post_meta($post_id, 'Current Year Div', $divCurrentYear);
                add_post_meta($post_id, 'Last Traded', $lastTrade);
                add_post_meta($post_id, 'Previous Year Div', $PreviousYear);
                add_post_meta($post_id, 'Symbol', 'QWI');
                add_post_meta($post_id, 'Today High', $todayHigh);
                add_post_meta($post_id, 'Today Low', $todayLow);
                add_post_meta($post_id, 'Volume (non block)', $Volume);
                add_post_meta($post_id, 'Today Open', $TodaysOpen);
                add_post_meta($post_id, 'Closing Bid', $ClosingBid);
                add_post_meta($post_id, 'Price Change', $todayPriceChange);
                add_post_meta($post_id, 'Today Percentage', $todayPercentage);
                add_post_meta($post_id, 'Date', date('Y-m-d H:i:s'));
                add_post_meta($post_id, 'NAV', null);
            } else {
                update_post_meta($post_id, '52 Week High', $fiftytwo_week_high);
                update_post_meta($post_id, '52 Week Low', $fiftytow_week_low);
                update_post_meta($post_id, 'Close Price', $close_price);
                update_post_meta($post_id, 'Closing Ask', $closingAsk);
                update_post_meta($post_id, 'Current Year Div', $divCurrentYear);
                update_post_meta($post_id, 'Last Traded', $lastTrade);
                update_post_meta($post_id, 'Previous Year Div', $PreviousYear);
                update_post_meta($post_id, 'Symbol', 'QWI');
                update_post_meta($post_id, 'Today High', $todayHigh);
                update_post_meta($post_id, 'Today Low', $todayLow);
                update_post_meta($post_id, 'Volume (non block)', $Volume);
                update_post_meta($post_id, 'Today Open', $TodaysOpen);
                update_post_meta($post_id, 'Closing Bid', $ClosingBid);
                update_post_meta($post_id, 'Price Change', $todayPriceChange);
                update_post_meta($post_id, 'Today Percentage', $todayPercentage);
                update_post_meta($post_id, 'Date', date('Y-m-d H:i:s'));
                //update_post_meta($post_id,'NAV', null);
            }
        }
    }

    function getNetAssetValue()
    {
        $page = $this->getPage();
        require_once ABSPATH . '/wp-admin/includes/post.php';
        
        $rx_right_sidebar =  '(<div class="tw\-grid tw\-mt.+<div class="tw\-border)i';
       // echo $rx_right_sidebar; 
        preg_match($rx_right_sidebar, $page, $right_sidebar);


        $rx_stock_stats = '(<div class="tw\-flex.+</div>)Ui';

        preg_match_all($rx_stock_stats, $right_sidebar[0], $stock_stats);

        $stock_statistics = array();
        foreach($stock_stats[0] as $idx=>$stock_stat) {

            $rx_stats = '(<span[^>]+>(.+)</span>[ ]+<span[^>]+>(.+)</span>)Ui';
            preg_match($rx_stats, $stock_stat, $stat);
            $stock_statistics[$idx]['key'] = trim($stat[1]);
            $stock_statistics[$idx]['value'] = trim($stat[2]);
        }

        //print_r($stock_statistics)  ;


        $rx_mid_section = '(tw-mt-2 tw-grid tw-gap-6 lg.+</div>[ ]+<h3>)Ui';

        preg_match($rx_mid_section, $page, $mid_section);

        $rx_stock_stats = '(<div class="tw\-flex.+</div>)Ui';

        preg_match_all($rx_stock_stats, $mid_section[0], $stock_stats);

        $stock_statistics2 = array();

        foreach($stock_stats[0] as $idx=>$stock_stat) {

            $rx_stats = '(<span[^>]+>(.+)</span>[ ]+<span[^>]+>(.+)</span>)Ui';
            preg_match($rx_stats, $stock_stat, $stat);
            $stock_statistics2[$idx]['key'] = trim($stat[1]);
            $stock_statistics2[$idx]['value'] = trim($stat[2]);
        }


        if(!empty($stock_statistics2)) {
            foreach ($stock_statistics2 as $stock_statistic2) {
                // echo "<pre>";
                // print_r($stock_statistic2);
                if ($stock_statistic2['key'] == "Today's Range:") {
                    $today_range = $stock_statistic2['value']; 
                } 
                if ($stock_statistic2['key'] == "52 Week Range:") {
                    $fifty_two_week_range = $stock_statistic2['value']; 
                }
                if ($stock_statistic2['key'] == "52 Week Volume Range:") {
                    $fifty_two_week_volume_range = $stock_statistic2['value']; 
                }
                if ($stock_statistic2['key'] == "Market Value of Shares Outstanding:") {
                    $market_value_of_shares_outstanding = $stock_statistic2['value']; 
                }
                if ($stock_statistic2['key'] == "Shares Outstanding:") {
                    $shares_outstanding = $stock_statistic2['value']; 
                }
                if ($stock_statistic2['key'] == "Week to Date:") {
                    $week_to_date = $stock_statistic2['value']; 
                }
                if ($stock_statistic2['key'] == "Month to Date:") {
                    $month_to_date = $stock_statistic2['value']; 
                }
                if ($stock_statistic2['key'] == "Quarter to Date:") {
                    $quarter_to_date = $stock_statistic2['value']; 
                }
                if ($stock_statistic2['key'] == "Year to Date:") {
                    $year_to_date = $stock_statistic2['value']; 
                }                
            }
        }

        
        foreach ($stock_statistics as $stock_statistic) {
                                 
                $post_title = wp_strip_all_tags("QWI on " . date('l jS F Y'));
                // Add Post and get ID 
                // Create post object
                $my_post = array(
                    'post_title'    =>  $post_title,
                    'post_status'   => 'publish',
                    'post_type' => 'stock_instrument',
                    'post_date' => date('Y-m-d H:i:s'),
                    'post_author'   => 1,

                );         

                $check_post_exist = post_exists($post_title);
                // Insert the post into the database
                if (!$check_post_exist) {
                    $post_id = wp_insert_post($my_post);
                } else {
                    $post_id = $check_post_exist;
                }

                if($stock_statistic['key'] == 'Prev. Closing Price') {
                    $stock_close_price = $stock_statistic['value'];
                }
                if($stock_statistic['key'] == 'Open') {
                    $stock_open = $stock_statistic['value'];
                }
                if($stock_statistic['key'] == 'Bid') {
                    $stock_bid = $stock_statistic['value'];
                }
                if($stock_statistic['key'] == 'Volume Traded') {
                    $stock_volume_trade = $stock_statistic['value'];
                }
                if($stock_statistic['key'] == 'Last Traded') {
                    $last_traded = $stock_statistic['value'];
                }
                if($stock_statistic['key'] == 'Ask') {
                    $ask = $stock_statistic['value'];
                }
        }

        // add meta to the post data 
        if (!$check_post_exist) {
            add_post_meta($post_id, '52 Week High', $fifty_two_week_range);
            add_post_meta($post_id, '52 Week Low', null);
            add_post_meta($post_id, 'Close Price', $stock_close_price);
            add_post_meta($post_id, 'Closing Ask', $ask);
            add_post_meta($post_id, 'Current Year Div', null);
            add_post_meta($post_id, 'Last Traded', $last_traded);
            add_post_meta($post_id, 'Previous Year Div', null);
            add_post_meta($post_id, 'Symbol', 'QWI');
            add_post_meta($post_id, 'Today High', null);
            add_post_meta($post_id, 'Today Low', null);
            add_post_meta($post_id, 'Volume (non block)', $stock_volume_trade);
            add_post_meta($post_id, 'Today Open', $stock_open);
            add_post_meta($post_id, 'Closing Bid', $stock_bid);
            add_post_meta($post_id, 'Price Change', null);
            add_post_meta($post_id, 'Today Percentage', null);
            add_post_meta($post_id, 'Date', date('Y-m-d H:i:s'));
            add_post_meta($post_id, 'NAV', null);

        } else {
            update_post_meta($post_id, '52 Week High', $fifty_two_week_range);
            update_post_meta($post_id, '52 Week Low', null);
            update_post_meta($post_id, 'Close Price', $stock_close_price);
            update_post_meta($post_id, 'Closing Ask', $ask);
            update_post_meta($post_id, 'Current Year Div', null);
            update_post_meta($post_id, 'Last Traded', $last_traded);
            update_post_meta($post_id, 'Previous Year Div', null);
            update_post_meta($post_id, 'Symbol', 'QWI');
            update_post_meta($post_id, 'Today High', null);
            update_post_meta($post_id, 'Today Low', null);
            update_post_meta($post_id, 'Volume (non block)', $stock_volume_trade);
            update_post_meta($post_id, 'Today Open', $stock_open);
            update_post_meta($post_id, 'Closing Bid', $stock_bid);
            update_post_meta($post_id, 'Price Change', null);
            update_post_meta($post_id, 'Today Percentage', null);
            update_post_meta($post_id, 'Date', date('Y-m-d H:i:s'));
        } 
        wp_mail( "vinodsohagpure@gmail.com", "QWI JSE CRON was run at ".date("Y-m-d h:i A"), "Cron tab run successfully."); 
        return $post_id;                    

    }

    

   function getPage($add_headers = false) {
        $url = 'https://www.jamstockex.com/trading/instruments/?instrument=qwi-jmd';
        $options = array(

            CURLOPT_CUSTOMREQUEST  =>"GET",        //set request type post or get
            CURLOPT_POST           => false,        //set to GET
            CURLOPT_COOKIEFILE     => "cookie_wp.txt", //set cookie file
            CURLOPT_COOKIEJAR      => "cookie_wp.txt", //set cookie jar
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,

        );



        $ch      = curl_init( $url );

        curl_setopt_array( $ch, $options );

        $content = curl_exec( $ch );
        $err     = curl_errno( $ch );
        $errmsg  = curl_error( $ch );
        $header  = curl_getinfo( $ch );

        curl_close( $ch );

        $header['errno']   = $err;
        $header['errmsg']  = $errmsg;
        $header['content'] = $content;

        if ($header['http_code'] == 200) {
            $content = str_replace(array("\n", "\r") , ' ', $content);
            // $content = preg_replace('([ ]+)', ' ', $content);
            return $content;
        } else {
            return false;
        }
    }
}
