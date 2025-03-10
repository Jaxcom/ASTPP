<?php

// ##############################################################################
// ASTPP - Open Source VoIP Billing Solution
//
// Copyright (C) 2016 iNextrix Technologies Pvt. Ltd.
// Samir Doshi <samir.doshi@inextrix.com>
// ASTPP Version 3.0 and above
// License https://www.gnu.org/licenses/agpl-3.0.html
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.
// ##############################################################################
class Summary extends MX_Controller
{

    function __construct()
    {
        parent::__construct();

        $this->load->helper('template_inheritance');

        $this->load->library('session');
        $this->load->library('astpp/form', 'summary_form');
        $this->load->library("summary_form");
        $this->load->model('summary_model');
        $this->load->library('ASTPP_Sms');

        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . '/astpp/login');
    }

    function customer()
    {
        $data['page_title'] = gettext('Customer Summary Report');
        $data['search_flag'] = true;
        $session_info = $this->session->userdata('customersummary_reports_search');
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
        $accountlist = $this->db_model->build_dropdown_deleted('id,IF(`deleted`=1,concat( first_name, " ", last_name, " ", "(", number, ")^" ),concat( first_name, " ", last_name, " ", "(", number, ")" )) as number', 'accounts', 'where_arr', array(
            'reseller_id' => $reseller_id,
            "type" => "GLOBAL"
        ));
        $data['cdrs_year'] = $this->common->set_year_dropdown('cdrs');
        $data['accountlist'] = $accountlist;
        $data['session_info'] = $session_info;
        $data['cdrs_year_val'] = $this->session->userdata('customer_cdrs_year');
        $data['search_string_type'] = $this->common->search_string_type();
        $data['search_report'] = $this->common->search_report_in();
        $new_column_arr = $this->summary_column_arr('customer');
        $data['grid_fields'] = $this->summary_form->build_customersummary($new_column_arr);
        $data["grid_buttons"] = $this->summary_form->build_grid_buttons_customersummary();
        $data['groupby_field'] = $this->common->set_summarycustomer_groupby();
        $data['groupby_time'] = $this->common->group_by_time();
        $data['accountinfo'] = $accountinfo;
        $this->load->view('view_customersummary_report', $data);
    }

    function customer_json()
    {
        $search_arr = $this->summary_search_info('customer');
        $count_all = $this->summary_model->get_customersummary_report_list(false, 0, 0, $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->summary_model->get_customersummary_report_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"], $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], false);
        if ($query->num_rows() > 0) {
            $json_data['rows'] = $this->summary_report_grid($search_arr, $query, 'customer', 'grid');
        }
        $this->session->set_userdata('customersummary_reports_export', $search_arr);
        echo json_encode($json_data);
    }

    function summary_column_arr($entity)
    {
        $new_column_arr = array();
        $total_width = '322';
        $column_name = 'accountid';
        if ($this->session->userdata('advance_search') == '1') {
            $search_array = $this->session->userdata($entity . 'summary_reports_search');
            if (isset($search_array['time']) && ! empty($search_array['time'])) {
                $entity_order = $entity == 'product' ? 'order_date' : 'callstart';
                $new_column_arr[] = array(
                    ucfirst(strtolower($search_array['time'])),
                    "58",
                    $search_array['time'] . "(" . $entity_order . ")",
                    "",
                    "",
                    ""
                );
            }
            if (isset($search_array['groupby_1']) && ! empty($search_array['groupby_1'])) {
                $first_column_groupby = $search_array['groupby_1'];

                if ($first_column_groupby == 'accountid' || $first_column_groupby == 'order_items.accountid') {
                    $new_column_arr[] = array(
                        "Account",
                        "322",
                        'accountid',
                        "first_name,last_name,number",
                        "accounts",
                        "build_concat_string"
                    );
                } elseif ($first_column_groupby == 'sip_user') {
                    $new_column_arr[] = array(
                        "SIP User",
                        "105",
                        "",
                        "",
                        "",
                        ""
                    );
                } elseif ($first_column_groupby == 'call_direction') {
                    $new_column_arr[] = array(
                        "Direction",
                        "105",
                        "",
                        "",
                        "",
                        ""
                    );
                } elseif ($first_column_groupby == 'calltype') {
                    $new_column_arr[] = array(
                        "Call Type",
                        "105",
                        "",
                        "",
                        "",
                        ""
                    );
                } elseif ($first_column_groupby == 'pattern') {
                    $new_column_arr[] = array(
                        "Code",
                        "45",
                        "pattern",
                        "pattern",
                        "",
                        "get_only_numeric_val"
                    );
                    $new_column_arr[] = array(
                        "Destination",
                        "59",
                        "notes",
                        "",
                        "",
                        ""
                    );
                } elseif ($first_column_groupby == 'package_id') {
                    $new_column_arr[] = array(
                        "Package",
                        "105",
                        'product_id',
                        "name",
                        "products",
                        "get_field_name"
                    );
                } elseif ($first_column_groupby == 'order_items.product_id' || $first_column_groupby == 'product_id') {
                    $new_column_arr[] = array(
                        "Products",
                        "105",
                        'product_id',
                        "name",
                        "products",
                        "get_field_name"
                    );
                } elseif ($first_column_groupby == 'order_items.product_category' || $first_column_groupby == 'product_category') {
                    $new_column_arr[] = array(
                        "Category",
                        "105",
                        'product_category',
                        "name",
                        "category",
                        "get_field_name"
                    );
                }
            }
            if (isset($search_array['groupby_2']) && ! empty($search_array['groupby_2'])) {
                $third_column_groupby = $search_array['groupby_2'];
                if ($third_column_groupby == 'accountid' || $third_column_groupby == 'order_items.accountid') {
                    $new_column_arr[] = array(
                        "Account",
                        "105",
                        'accountid',
                        "first_name,last_name,number",
                        "accounts",
                        "build_concat_string"
                    );
                } elseif ($third_column_groupby == 'sip_user') {
                    $new_column_arr[] = array(
                        "SIP User",
                        "105",
                        "",
                        "",
                        "",
                        ""
                    );
                } elseif ($third_column_groupby == 'call_direction') {
                    $new_column_arr[] = array(
                        "Direction",
                        "105",
                        "",
                        "",
                        "",
                        ""
                    );
                } elseif ($third_column_groupby == 'calltype') {
                    $new_column_arr[] = array(
                        "Call Type",
                        "105",
                        "",
                        "",
                        "",
                        ""
                    );
                } elseif ($third_column_groupby == 'pattern') {
                    $new_column_arr[] = array(
                        "Code",
                        "45",
                        "pattern",
                        "pattern",
                        "",
                        "get_only_numeric_val"
                    );
                    $new_column_arr[] = array(
                        "Destination",
                        "59",
                        "notes",
                        "",
                        "",
                        ""
                    );
                } elseif ($third_column_groupby == 'package_id') {
                    $new_column_arr[] = array(
                        "Package",
                        "105",
                        'product_id',
                        "name",
                        "products",
                        "get_field_name"
                    );
                } elseif ($third_column_groupby == 'order_items.product_id' || $third_column_groupby == 'product_id') {
                    $new_column_arr[] = array(
                        "Products",
                        "105",
                        'product_id',
                        "name",
                        "products",
                        "get_field_name"
                    );
                } elseif ($third_column_groupby == 'order_items.product_category' || $third_column_groupby == 'product_category') {
                    $new_column_arr[] = array(
                        "Category",
                        "105",
                        'product_category',
                        "name",
                        "category",
                        "get_field_name"
                    );
                }
            }
            if (isset($search_array['groupby_3']) && ! empty($search_array['groupby_3'])) {
                $fifth_column_groupby = $search_array['groupby_3'];
                if ($fifth_column_groupby == 'accountid' || $fifth_column_groupby == 'order_items.accountid') {
                    $new_column_arr[] = array(
                        "Account",
                        "105",
                        'accountid',
                        "first_name,last_name,number",
                        "accounts",
                        "build_concat_string"
                    );
                } elseif ($fifth_column_groupby == 'sip_user') {
                    $new_column_arr[] = array(
                        "SIP User",
                        "105",
                        "",
                        "",
                        "",
                        ""
                    );
                } elseif ($fifth_column_groupby == 'call_direction') {
                    $new_column_arr[] = array(
                        "Direction",
                        "105",
                        "",
                        "",
                        "",
                        ""
                    );
                } elseif ($fifth_column_groupby == 'calltype') {
                    $new_column_arr[] = array(
                        "Call Type",
                        "105",
                        "",
                        "",
                        "",
                        ""
                    );
                } elseif ($fifth_column_groupby == 'pattern') {
                    $new_column_arr[] = array(
                        "Code",
                        "45",
                        "pattern",
                        "pattern",
                        "",
                        "get_only_numeric_val"
                    );
                    $new_column_arr[] = array(
                        "Destination",
                        "59",
                        "notes",
                        "",
                        "",
                        ""
                    );
                } elseif ($fifth_column_groupby == 'package_id') {
                    $new_column_arr[] = array(
                        "Package",
                        "105",
                        'product_id',
                        "name",
                        "products",
                        "get_field_name"
                    );
                } elseif ($fifth_column_groupby == 'order_items.product_id' || $fifth_column_groupby == 'product_id') {
                    $new_column_arr[] = array(
                        "Products",
                        "105",
                        'product_id',
                        "name",
                        "products",
                        "get_field_name"
                    );
                } elseif ($fifth_column_groupby == 'order_items.product_category' || $fifth_column_groupby == 'product_category') {
                    $new_column_arr[] = array(
                        "Category",
                        "105",
                        'product_category',
                        "name",
                        "category",
                        "get_field_name"
                    );
                }
            }
            if (empty($new_column_arr)) {
                $new_column_arr[] = array(
                    "Account",
                    '322',
                    'accountid',
                    "first_name,last_name,number",
                    "accounts",
                    "build_concat_string"
                );
            }
        } else {
            $new_column_arr[] = array(
                "Account",
                '322',
                'accountid',
                "first_name,last_name,number",
                "accounts",
                "build_concat_string"
            );
        }

        return $new_column_arr;
    }

    function summary_report_grid($search_arr, $query, $entity, $purpose)
    {
        $export_arr = array();
        $db_field_name = $entity == 'provider' ? 'provider_id' : 'accountid';
        $show_seconds = (! empty($search_arr['search_in'])) ? $search_arr['search_in'] : 'minutes';
        $currency_info = $this->common->get_currency_info();
        foreach ($query->result_array() as $row1) {
            $atmpt = $row1['attempts'];
            $cmplt = ($row1['completed'] != 0) ? $row1['completed'] : 0;
            $acd = ($row1['completed'] > 0) ? round($row1['duration'] / $row1['completed']) : 0;
            $mcd = $row1['mcd'];
            if ($show_seconds == 'minutes') {
                $avgsec = $acd > 0 ? sprintf('%02d', $acd / 60) . ":" . sprintf('%02d', ($acd % 60)) : "00:00";
                $maxsec = $mcd > 0 ? sprintf('%02d', $mcd / 60) . ":" . sprintf('%02d', ($mcd % 60)) : "00:00";
                $duration = ($row1['duration'] > 0) ? sprintf('%02d', $row1['duration'] / 60) . ":" . sprintf('%02d', ($row1['duration'] % 60)) : "00:00";
                $billsec = ($row1['billable'] > 0) ? sprintf('%02d', $row1['billable'] / 60) . ":" . sprintf('%02d', ($row1['billable'] % 60)) : "00:00";
            } else {
                $duration = sprintf('%02d', $row1['duration']);
                $avgsec = $acd;
                $maxsec = $mcd;
                $billsec = sprintf('%02d', $row1['billable']);
            }
            if ($entity != 'provider') {
                $profit = $this->common->calculate_currency_manually($currency_info, $row1['debit'] - $row1['cost'], false);
                $debit = $this->common->calculate_currency_manually($currency_info, $row1['debit'], false);
            }
            $cost = $this->common->calculate_currency_manually($currency_info, $row1['cost'], false);
            $asr = ($atmpt > 0) ? (round(($cmplt / $atmpt) * 100, 2)) : '0.00';
            $new_arr = array();

            if ($this->session->userdata('advance_search') == 1) {
                if (! empty($search_arr['groupby_time'])) {
                    $time = $row1[$search_arr['groupby_time']];

                    if ($search_arr['groupby_time'] == "HOUR" || $search_arr['groupby_time'] == "DAY") {
                        $time = sprintf('%02d', $time);
                    }
                    if ($search_arr['groupby_time'] == "MONTH") {
                        $dateObj = DateTime::createFromFormat('!m', $time);
                        $time = $dateObj->format('F');
                    }
                    $new_arr[] = $time;
                }
                if ($search_arr['groupby_1'] == $db_field_name) {
                    $new_arr[] = $this->common->build_concat_string("first_name,last_name,number", "accounts", $row1[$db_field_name]);
                } elseif ($search_arr['groupby_1'] == 'pattern') {
                    $new_arr[] = filter_var($row1['pattern'], FILTER_SANITIZE_NUMBER_INT);
                    $new_arr[] = $row1['notes'];
                } elseif ($search_arr['groupby_1'] == 'trunk_id') {
                    $new_arr[] = $this->common->get_field_name('name', 'trunks', $row1['trunk_id']);
                } elseif ($search_arr['groupby_1'] == 'package_id') {
                    $new_arr[] = $this->common->get_field_name('name', 'products', $row1['package_id']);
                } elseif ($search_arr['groupby_1'] == 'sip_user') {
                    $new_arr[] = $row1['sip_user'];
                } elseif ($search_arr['groupby_1'] == 'call_direction') {
                    $new_arr[] = $row1['call_direction'];
                } elseif ($search_arr['groupby_1'] == 'calltype') {
                    $new_arr[] = $row1['calltype'];
                } elseif ($search_arr['groupby_1'] == 'product_id') {
                    $new_arr[] = $this->common->get_field_name('name', 'products', $row1['product_id']);
                } elseif ($search_arr['groupby_1'] == "product_category") {
                    $new_arr[] = $this->common->get_field_name('name', 'category', $row1['product_category']);
                }
                if ($search_arr['groupby_2'] == $db_field_name) {
                    $new_arr[] = $this->common->build_concat_string("first_name,last_name,number", "accounts", $row1[$db_field_name]);
                } elseif ($search_arr['groupby_2'] == 'pattern') {
                    $new_arr[] = filter_var($row1['pattern'], FILTER_SANITIZE_NUMBER_INT);
                    $new_arr[] = $row1['notes'];
                } elseif ($search_arr['groupby_2'] == 'trunk_id') {
                    $new_arr[] = $this->common->get_field_name('name', 'trunks', $row1['trunk_id']);
                } elseif ($search_arr['groupby_2'] == 'package_id') {
                    $new_arr[] = $this->common->get_field_name('name', 'products', $row1['package_id']);
                } elseif ($search_arr['groupby_2'] == 'sip_user') {
                    $new_arr[] = $row1['sip_user'];
                } elseif ($search_arr['groupby_2'] == 'call_direction') {
                    $new_arr[] = $row1['call_direction'];
                } elseif ($search_arr['groupby_2'] == 'calltype') {
                    $new_arr[] = $row1['calltype'];
                } elseif ($search_arr['groupby_2'] == 'product_id') {
                    $new_arr[] = $this->common->get_field_name('name', 'products', $row1['product_id']);
                } elseif ($search_arr['groupby_2'] == "product_category") {
                    $new_arr[] = $this->common->get_field_name('name', 'category', $row1['product_category']);
                }

                if ($search_arr['groupby_3'] == $db_field_name) {
                    $new_arr[] = $this->common->build_concat_string("first_name,last_name,number", "accounts", $row1[$db_field_name]);
                } elseif ($search_arr['groupby_3'] == 'pattern') {
                    $new_arr[] = filter_var($row1['pattern'], FILTER_SANITIZE_NUMBER_INT);
                    $new_arr[] = $row1['notes'];
                } elseif ($search_arr['groupby_3'] == 'trunk_id') {
                    $new_arr[] = $this->common->get_field_name('name', 'trunks', $row1['trunk_id']);
                } elseif ($search_arr['groupby_3'] == 'package_id') {
                    $new_arr[] = $this->common->get_field_name('name', 'products', $row1['package_id']);
                } elseif ($search_arr['groupby_3'] == 'sip_user') {
                    $new_arr[] = $row1['sip_user'];
                } elseif ($search_arr['groupby_3'] == 'call_direction') {
                    $new_arr[] = $row1['call_direction'];
                } elseif ($search_arr['groupby_3'] == 'calltype') {
                    $new_arr[] = $row1['calltype'];
                } elseif ($search_arr['groupby_3'] == 'product_id') {
                    $new_arr[] = $this->common->get_field_name('name', 'products', $row1['product_id']);
                } elseif ($search_arr['groupby_3'] == "product_category") {
                    $new_arr[] = $this->common->get_field_name('name', 'category', $row1['product_category']);
                }

                if (empty($new_arr)) {
                    $new_arr[] = $this->common->build_concat_string("first_name,last_name,number", "accounts", $row1[$db_field_name]);
                }
            } else {
                $new_arr[] = $this->common->build_concat_string("first_name,last_name,number", "accounts", $row1[$db_field_name]);
            }
            if ($entity != 'provider') {
                $custom_array = array(
                    $atmpt,
                    $cmplt,
                    $duration,
                    round($asr, 2),
                    $avgsec,
                    $maxsec,
                    $billsec,
                    $debit,
                    $cost,
                    $profit
                );
            } else {
                $custom_array = array(
                    $atmpt,
                    $cmplt,
                    $duration,
                    round($asr, 2),
                    $avgsec,
                    $maxsec,
                    $billsec,
                    $cost
                );
            }
            $final_array = array_merge($new_arr, $custom_array);
            $json_data[] = array(
                'cell' => $final_array
            );
            $export_arr[] = $final_array;
        }
        $function_name = 'get_' . $entity . 'summary_report_list';
        $total_info = $this->summary_model->$function_name(true, '', '', '', $search_arr['select_str'], $search_arr['order_str'], true);
        $total_info = $total_info->result_array();
        $total_info = $total_info[0];
        $total_asr = ($total_info['attempts'] > 0) ? round(($total_info['completed'] / $total_info['attempts']) * 100, 2) : 0;
        $total_acd = ($total_info['completed'] > 0) ? round($total_info['duration'] / $total_info['completed']) : 0;
        if ($show_seconds == 'minutes') {
            $total_info['duration'] = $total_info['duration'] > 0 ? sprintf('%02d', $total_info['duration'] / 60) . ":" . sprintf('%02d', ($total_info['duration'] % 60)) : "00:00";
            $total_info['billable'] = $total_info['billable'] > 0 ? sprintf('%02d', $total_info['billable'] / 60) . ":" . sprintf('%02d', ($total_info['billable'] % 60)) : "00:00";
            $total_acd = $total_acd > 0 ? sprintf('%02d', $total_acd / 60) . ":" . sprintf('%02d', ($total_acd % 60)) : "00:00";
            $total_info['mcd'] = $total_info['mcd'] > 0 ? sprintf('%02d', $total_info['mcd'] / 60) . ":" . sprintf('%02d', ($total_info['mcd'] % 60)) : "00:00";
        }
        if ($entity != 'provider') {
            $total_profit = $this->common->calculate_currency_manually($currency_info, $total_info['debit'] - $total_info['cost'], false);
            $total_debit = $this->common->calculate_currency_manually($currency_info, $total_info['debit'], false);
        }
        $total_cost = $this->common->calculate_currency_manually($currency_info, $total_info['cost'], false);
        if ($entity != 'provider') {
            $last_array = array(
                "<b>" . $total_info['attempts'] . "</b>",
                "<b>" . $total_info['completed'] . "</b>",
                "<b>" . $total_info['duration'] . "</b>",
                "<b>" . $total_asr . "</b>",
                "<b>" . $total_acd . "</b>",
                "<b>" . $total_info['mcd'] . "</b>",
                "<b>" . $total_info['billable'] . "</b>",
                "<b>" . $total_debit . "</b>",
                "<b>" . $total_cost . "</b>",
                "<b>" . $total_profit . "</b>"
            );
        } else {
            $last_array = array(
                "<b>" . $total_info['attempts'] . "</b>",
                "<b>" . $total_info['completed'] . "</b>",
                "<b>" . $total_info['duration'] . "</b>",
                "<b>" . $total_asr . "</b>",
                "<b>" . $total_acd . "</b>",
                "<b>" . $total_info['mcd'] . "</b>",
                "<b>" . $total_info['billable'] . "</b>",
                "<b>" . $total_cost . "</b>"
            );
        }
        if ($purpose == 'export') {
            $search_arr['custom_total_array'][0] = "Grand Total";
        }
        $new_export_array = array();
        foreach ($last_array as $key => $value) {
            $value = str_replace("<b>", "", $value);
            $value = str_replace("</b>", '', $value);
            if ($key == 7 || $key == 8 || $key == 9) {
                $value = sprintf("%." . $currency_info['decimalpoints'] . "f", floatval($value));
            }
            $new_export_array[$key] = $value;
        }
        $total_array = array_merge($search_arr['custom_total_array'], $last_array);
        $custom_export_arr = array_merge($search_arr['custom_total_array'], $new_export_array);
        $export_arr[] = $custom_export_arr;
        $json_data[] = array(
            'cell' => $total_array
        );
        return $purpose == 'grid' ? $json_data : $export_arr;
    }

    function customer_export_csv()
    {
        $account_info = $accountinfo = $this->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
        $search_arr = $this->session->userdata('customersummary_reports_export');
        $data_arr = array();
        $query = $this->summary_model->get_customersummary_report_list(true, '', '', $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], true);
        $search_header = explode(",", $search_arr['export_str']);

        ob_clean();
        $fixed_header = array(
            'Attempted Calls',
            'Completed Calls',
            'Duration',
            'ASR',
            'ACD',
            'MCD',
            'Billable',
            'Debit(' . $currency . ')',
            'Cost(' . $currency . ')',
            'Profit'
        );
        $header_arr[] = array_merge($search_header, $fixed_header);
        if ($query->num_rows() > 0) {
            $data_arr = $this->summary_report_grid($search_arr, $query, 'customer', 'export');
        }
        $customer_array = array_merge($header_arr, $data_arr);

        $this->load->helper('csv');
        array_to_csv($customer_array, 'Customer_Summary_Report_' . date("Y-m-d") . '.csv');
    }

    function customer_search()
    {
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $this->session->set_userdata('customer_cdrs_year', $this->input->post('cdrs_year'));
            unset($action['cdrs_year']);
            unset($_POST['action']);
            unset($_POST['advance_search']);
            $this->session->set_userdata('customersummary_reports_search', $this->input->post());
        }
        redirect(base_url() . 'summary/customer/');
    }

    function customer_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('customersummary_reports_search', "");
        $this->session->set_userdata('customersummary_reports_export', "");
        $this->session->unset_userdata('customer_cdrs_year');
        redirect(base_url() . 'summary/customer/');
    }

    function summary_search_info($entity)
    {
        $accountinfo = $this->session->userdata('accountinfo');
        $this->db->select('gmttime,gmtoffset');
        $timezone_info = (array) $this->db->get_where('timezone', array(
            "id" => $accountinfo['timezone_id']
        ))->first_row();
        if (! empty($timezone_info['gmttime']) && $timezone_info['gmtoffset'] != 0) {
            $user_timezone = $timezone_info['gmttime'];
        } else {
            $user_timezone = "GMT+00:00";
        }
        $user_timezone_arr = explode("GMT", $user_timezone);
        $user_timezone_gmttime = $user_timezone_arr[1];
        $group_by_str = null;
        $select_str = null;
        $group_by_time = null;
        $group_by_1 = null;
        $group_by_2 = null;
        $group_by_3 = null;
        $order_str = null;
        $custom_total_array = array();
        $custom_search = array();
        $export_select_str = null;
        $new_arr['search_in'] = 'minutes';
        $i = 0;

        $db_field_name = $entity == 'provider' ? 'provider_id' : 'accountid';

        if ($this->session->userdata('advance_search') == 1) {
            $custom_search = $this->session->userdata($entity . 'summary_reports_search');
            if (isset($custom_search['time']) && ! empty($custom_search['time'])) {
                if ($entity != "product") {
                    $group_by_str .= $custom_search['time'] . "(convert_tz(callstart,'+00:00','$user_timezone_gmttime')),";
                    $select_str .= $custom_search['time'] . "(convert_tz(callstart,'+00:00','$user_timezone_gmttime')) as " . $order_str .= $custom_search['time'] . ",";
                } else {
                    $group_by_str .= $custom_search['time'] . "(convert_tz(orders.order_date,'+00:00','$user_timezone_gmttime')),";
                    $select_str .= $custom_search['time'] . "(convert_tz(orders.order_date,'+00:00','$user_timezone_gmttime')) as " . $order_str .= $custom_search['time'] . ",";
                }
                $group_by_time = $custom_search['time'];
                $export_select_str .= $custom_search['time'] . ",";
                $custom_total_array[$i] = null;
                $i ++;
            }

            if (isset($custom_search['groupby_1']) && ! empty($custom_search['groupby_1'])) {
                $custom_group_by = $entity == 'product' ? "order_items." . $custom_search['groupby_1'] : $custom_search['groupby_1'];
                $select_str .= $custom_group_by . ",";
                $group_by_str .= $custom_group_by . ",";
                $order_str .= $custom_group_by . ",";
                $group_by_1 = $custom_group_by;
                if ($custom_search['groupby_1'] == $db_field_name) {
                    $export_select_str .= 'Account,';
                } elseif ($custom_search['groupby_1'] == 'trunk_id') {
                    $export_select_str .= 'Trunk,';
                } elseif ($custom_search['groupby_1'] == 'pattern') {
                    $select_str .= 'notes,';
                    $order_str .= 'notes,';
                    $export_select_str .= "Code,Destination,";
                    $custom_total_array[$i] = null;
                    $i ++;
                } elseif ($custom_search['groupby_1'] == 'package_id') {
                    $export_select_str .= 'Package,';
                } elseif ($custom_search['groupby_1'] == 'sip_user') {
                    $export_select_str .= 'SIP User,';
                } elseif ($custom_search['groupby_1'] == 'product_id') {
                    $export_select_str .= 'Product,';
                } elseif ($custom_search['groupby_1'] == 'call_direction') {
                    $export_select_str .= 'Direction,';
                } elseif ($custom_search['groupby_1'] == 'product_category') {
                    $export_select_str .= 'Category,';
                }
                $custom_total_array[$i] = null;
                $i ++;
            }

            if (isset($custom_search['groupby_2']) && ! empty($custom_search['groupby_2'])) {
                $custom_group_by = $entity == 'product' ? "order_items." . $custom_search['groupby_2'] : $custom_search['groupby_2'];
                $group_by_str .= $custom_group_by . ",";
                $select_str .= $custom_group_by . ",";
                $order_str .= $custom_group_by . ",";
                $group_by_2 = $custom_group_by;
                if ($custom_search['groupby_2'] == $db_field_name) {
                    $export_select_str .= 'Account,';
                } elseif ($custom_search['groupby_2'] == 'trunk_id') {
                    $export_select_str .= 'Trunk,';
                } elseif ($custom_search['groupby_2'] == 'pattern') {
                    $select_str .= 'notes,';
                    $order_str .= 'notes,';
                    $export_select_str .= "Code,Destination,";
                    $custom_total_array[$i] = null;
                    $i ++;
                } elseif ($custom_search['groupby_2'] == 'sip_user') {
                    $export_select_str .= 'SIP User,';
                } elseif ($custom_search['groupby_2'] == 'call_direction') {
                    $export_select_str .= 'Direction,';
                } elseif ($custom_search['groupby_2'] == 'package_id') {
                    $export_select_str .= 'Package,';
                } elseif ($custom_search['groupby_2'] == 'product_id') {
                    $export_select_str .= 'Product,';
                } elseif ($custom_search['groupby_2'] == 'product_category') {
                    $export_select_str .= 'Category,';
                }
                $custom_total_array[$i] = null;
                $i ++;
            }

            if (isset($custom_search['groupby_3']) && ! empty($custom_search['groupby_3'])) {
                $custom_group_by = $entity == 'product' ? "order_items." . $custom_search['groupby_3'] : $custom_search['groupby_3'];
                $select_str .= $custom_group_by . ",";
                $order_str .= $custom_group_by . ",";
                $group_by_3 = $custom_group_by;
                if ($custom_search['groupby_3'] == 'accountid' || $custom_search['groupby_3'] == 'provider_id') {
                    $export_select_str .= 'Account,';
                } elseif ($custom_search['groupby_3'] == 'trunk_id') {
                    $export_select_str .= 'Trunk,';
                } elseif ($custom_search['groupby_3'] == 'pattern') {
                    $select_str .= 'notes,';
                    $order_str .= 'notes,';
                    $export_select_str .= "Code,Destination,";
                    $custom_total_array[$i] = null;
                    $i ++;
                } elseif ($custom_search['groupby_3'] == 'sip_user') {
                    $export_select_str .= 'SIP User,';
                } elseif ($custom_search['groupby_3'] == 'call_direction') {
                    $export_select_str .= 'Direction,';
                } elseif ($custom_search['groupby_3'] == 'package_id') {
                    $export_select_str .= 'Package,';
                } elseif ($custom_search['groupby_2'] == 'product_id') {
                    $export_select_str .= 'Product,';
                } elseif ($custom_search['groupby_2'] == 'product_category') {
                    $export_select_str .= 'Category,';
                }
                $custom_total_array[$i] = null;
                $i ++;
            }
            $new_arr['search_in'] = (isset($custom_search['search_in']) && ! empty($custom_search['search_in'])) ? $custom_search['search_in'] : 'minutes';
            unset($custom_search['groupby_1'], $custom_search['groupby_2'], $custom_search['groupby_3'], $custom_search['search_in']);
            $this->session->set_userdata('summary_' . $entity . '_search', $custom_search);
        }

        if (! empty($group_by_str)) {
            $group_by_str = rtrim($group_by_str, ",");
            $select_str = rtrim($select_str, ",");
            $order_str = rtrim($order_str, ",");
            $export_select_str = rtrim($export_select_str, ",");
        } else {
            if ($entity != "product") {
                $select_str = $db_field_name;
                $order_str = $db_field_name;
                $group_by_str = $db_field_name;
            } else {
                $select_str = "order_items." . $db_field_name;
                $order_str = "order_items." . $db_field_name;
                $group_by_str = "order_items." . $db_field_name;
            }
            $export_select_str = "Account";
        }

        array_pop($custom_total_array);
        array_unshift($custom_total_array, '<b>Grand Total</b>');
        $new_arr['export_str'] = $export_select_str;
        $new_arr['select_str'] = $select_str;
        $new_arr['order_str'] = $order_str;
        $new_arr['group_by_str'] = $group_by_str;
        $new_arr['groupby_1'] = $group_by_1;
        $new_arr['groupby_2'] = $group_by_2;
        $new_arr['groupby_3'] = $group_by_3;
        $new_arr['groupby_time'] = $group_by_time;
        $new_arr['custom_total_array'] = $custom_total_array;
        return $new_arr;
    }

    function provider()
    {
        $data['page_title'] = gettext('Provider Summary Report');
        $data['search_flag'] = true;
        $session_info = $this->session->userdata('providersummary_reports_search');
        $accountlist = $this->db_model->build_dropdown_deleted('id,IF(`deleted`=1,concat( first_name, " ", last_name, " ", "(", number, ")^" ),concat( first_name, " ", last_name, " ", "(", number, ")" )) as number', 'accounts', 'where_arr', array(
            "type" => "3"
        ));
        $trunklist = $this->db_model->build_dropdown('id,name', 'trunks', '', array());
        $data['cdrs_year'] = $this->common->set_year_dropdown('cdrs');
        $data['trunklist'] = $trunklist;
        $data['accountlist'] = $accountlist;
        $data['session_info'] = $session_info;
        $data['cdrs_year_val'] = $this->session->userdata('provider_cdrs_year');
        $data['seconds'] = $this->session->userdata('provider_seconds');
        $data['search_report'] = $this->common->search_report_in();
        $data['grid_fields'] = $this->summary_form->build_providersummary();
        $data["grid_buttons"] = $this->summary_form->build_grid_buttons_providersummary();
        $data['search_string_type'] = $this->common->search_string_type();
        $data['groupby_field'] = $this->common->set_summaryprovider_groupby();
        $data['groupby_time'] = $this->common->group_by_time();
        $this->load->view('view_providersummary_report', $data);
    }

    function provider_json()
    {
        $search_arr = $this->summary_search_info('provider');
        $count_all = $this->summary_model->get_providersummary_report_list(false, '', '', $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->summary_model->get_providersummary_report_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"], $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], false);
        if ($query->num_rows() > 0) {
            $json_data['rows'] = $this->summary_report_grid($search_arr, $query, 'provider', 'grid');
        }
        $this->session->set_userdata('providersummary_reports_export', $search_arr);
        echo json_encode($json_data);
    }

    function provider_export_csv()
    {
        $account_info = $accountinfo = $this->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
        $search_arr = $this->session->userdata('providersummary_reports_export');
        $data_arr = array();
        $query = $this->summary_model->get_providersummary_report_list(true, '', '', $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], true);
        $search_header = explode(",", $search_arr['export_str']);
        ob_clean();
        $fixed_header = array(
            "Attempted Calls",
            "Completed Calls",
            "Duration",
            "ASR",
            "ACD",
            "MCD",
            "Billable",
            "Cost($currency)"
        );
        $header_arr[] = array_merge($search_header, $fixed_header);
        if ($query->num_rows() > 0) {
            $data_arr = $this->summary_report_grid($search_arr, $query, 'provider', 'export');
        }
        $provider_array = array_merge($header_arr, $data_arr);

        $this->load->helper('csv');
        array_to_csv($provider_array, 'Provider_Summary_Report_' . date("Y-m-d") . '.csv');
    }

    function provider_search()
    {
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $this->session->set_userdata('provider_cdrs_year', $this->input->post('cdrs_year'));
            unset($_POST['action'], $_POST['advance_search'], $_POST['cdrs_year']);
            unset($_POST['action'], $_POST['advance_search']);
            $this->session->set_userdata('providersummary_reports_search', $this->input->post());
        }
        redirect(base_url() . 'summary/provider/');
    }

    function provider_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('providersummary_reports_search', "");
        $this->session->set_userdata('providersummary_reports_export', "");
        $this->session->unset_userdata('provider_cdrs_year', "");
        redirect(base_url() . "summary/provider/");
    }

    function reseller()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Reseller Summary Report');
        $data['search_flag'] = true;
        $session_info = $this->session->userdata('resellersummary_reports_search');
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
        $accountlist = $this->db_model->build_dropdown_deleted('id,IF(`deleted`=1,concat( first_name, " ", last_name, " ", "(", number, ")^" ),concat( first_name, " ", last_name, " ", "(", number, ")" )) as number', 'accounts', 'where_arr', array(
            'reseller_id' => $reseller_id,
            "type" => "1"
        ));
        $data['cdrs_year'] = $this->common->set_year_dropdown('reseller_cdrs');
        $data['accountlist'] = $accountlist;
        $data['seconds'] = $this->session->userdata('reseller_seconds');
        $data['session_info'] = $session_info;
        $data['cdrs_year_val'] = $this->session->userdata('reseller_cdrs_year');
        $data['search_report'] = $this->common->search_report_in();
        $data['search_string_type'] = $this->common->search_string_type();
        $new_column_arr = $this->summary_column_arr('reseller');
        $data['grid_fields'] = $this->summary_form->build_resellersummary($new_column_arr);
        $data["grid_buttons"] = $this->summary_form->build_grid_buttons_resellersummary();
        $data['groupby_field'] = $this->common->set_summarycustomer_groupby();
        unset($data['groupby_field']['sip_user']);
        $data['groupby_time'] = $this->common->group_by_time();
        $this->load->view('view_resellersummary_report', $data);
    }

    function reseller_json()
    {
        $search_arr = $this->summary_search_info('reseller');
        $count_all = $this->summary_model->get_resellersummary_report_list(false, 0, 0, $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->summary_model->get_resellersummary_report_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"], $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], false);
        if ($query->num_rows() > 0) {
            $json_data['rows'] = $this->summary_report_grid($search_arr, $query, 'reseller', 'grid');
        }
        $this->session->set_userdata('resellersummary_reports_export', $search_arr);
        echo json_encode($json_data);
    }

    function reseller_search()
    {
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $this->session->set_userdata('reseller_cdrs_year', $this->input->post('cdrs_year'));
            unset($_POST['action'], $_POST['advance_search'], $_POST['cdrs_year']);
            unset($_POST['action'], $_POST['advance_search']);
            $this->session->set_userdata('resellersummary_reports_search', $this->input->post());
        }
        redirect(base_url() . "summary/reseller/");
    }

    function reseller_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('resellersummary_reports_search', "");
        $this->session->set_userdata('resellersummary_reports_export', "");
        $this->session->unset_userdata('reseller_cdrs_year', "");
        redirect(base_url() . "summary/reseller/");
    }

    function reseller_export_csv()
    {
        $account_info = $accountinfo = $this->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->common->get_field_name('currency', 'currency', $currency_id);

        $search_arr = $this->session->userdata('resellersummary_reports_export');
        $data_arr = array();
        $query = $this->summary_model->get_resellersummary_report_list(true, '', '', $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], true);
        $search_header = explode(",", $search_arr['export_str']);
        ob_clean();
        $fixed_header = array(
            "Attempted Calls",
            "Completed Calls",
            "Duration",
            "ASR",
            "ACD",
            "MCD",
            "Billable",
            "Debit($currency)",
            "Cost($currency)",
            "Profit"
        );
        $header_arr[] = array_merge($search_header, $fixed_header);
        if ($query->num_rows() > 0) {
            $data_arr = $this->summary_report_grid($search_arr, $query, 'reseller', 'export');
        }
        $reseller_array = array_merge($header_arr, $data_arr);
        $this->load->helper('csv');
        array_to_csv($reseller_array, 'Reseller_Summary_Report_' . date("Y-m-d") . '.csv');
    }

    function product()
    {
        $data['page_title'] = gettext('Product Summary Report');
        $data['search_flag'] = true;
        $session_info = $this->session->userdata('productsummary_reports_search');
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
        $accountlist = $this->db_model->build_dropdown_deleted('id,IF(`deleted`=1,concat( first_name, " ", last_name, " ", "(", number, ")^" ),concat( first_name, " ", last_name, " ", "(", number, ")" )) as number', 'accounts', 'where_arr', array(
            'reseller_id' => $reseller_id,
            "type" => "GLOBAL"
        ));
        $product_list = $this->db_model->build_dropdown('id,name', 'products', '', array(
            'status<>' => 1
        ));
        $data['productlist'] = $product_list;
        $category_list = $this->db_model->build_dropdown('id,name', 'category', '', array(
            'status<>' => 1
        ));
        $data['categorylist'] = $category_list;
        $data['order_items_year'] = $this->common->set_year_dropdown('order_items');
        $data['accountlist'] = $accountlist;
        $data['session_info'] = $session_info;
        $data['products_year_val'] = $this->session->userdata('product_year');
        $data['search_string_type'] = $this->common->search_string_type();
        $data['search_report'] = $this->common->search_report_in();
        $new_column_arr = $this->summary_column_arr('product');
        $data['grid_fields'] = $this->summary_form->build_product_summary($new_column_arr);
        $data["grid_buttons"] = $this->summary_form->build_grid_buttons_products_summary();
        $data['groupby_field'] = $this->common->set_product_groupby();
        $data['groupby_time'] = $this->common->group_by_time();
        $this->load->view('view_productsummary_report', $data);
    }

    function product_json()
    {
        $search_arr = $this->summary_search_info('product');

        $count_all = $this->summary_model->get_productsummary_report_list(false, 0, 0, $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], false);

        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->summary_model->get_productsummary_report_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"], $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], false);
        if ($query->num_rows() > 0) {
            $json_data['rows'] = $this->product_summary_report_grid($search_arr, $query, 'product', 'grid');
        }
        $this->session->set_userdata('productsummary_reports_export', $search_arr);

        echo json_encode($json_data);
    }

    function product_search()
    {
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($_POST['action']);
            unset($_POST['advance_search']);
            $_POST['order_items.accountid'] = $_POST['order_items#accountid'];
            unset($_POST['order_items#accountid']);
            $this->session->set_userdata('productsummary_reports_search', $this->input->post());
        }
        redirect(base_url() . 'summary/product/');
    }

    function product_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->unset_userdata('productsummary_reports_search', "");
        $this->session->set_userdata('productsummary_reports_export', "");
        $this->session->unset_userdata('product_cdrs_year');
        redirect(base_url() . 'summary/product/');
    }

    function product_summary_report_grid($search_arr, $query, $entity, $purpose)
    {
        $export_arr = array();
        $show_seconds = (! empty($search_arr['search_in'])) ? $search_arr['search_in'] : 'minutes';
        $currency_info = $this->common->get_currency_info();
        foreach ($query->result_array() as $row1) {

            $new_arr = array();
            $free_minutes = $row1['free_minutes'];
            if ($show_seconds == 'minutes') {
                $free_minutes_result = $free_minutes > 0 ? sprintf('%02d', $free_minutes / 60) . ":" . sprintf('%02d', ($free_minutes % 60)) : "00:00";
            } else {
                $free_minutes_result = sprintf('%02d', $row1['free_minutes']);
            }
            if ($this->session->userdata('advance_search') == 1) {
                if (! empty($search_arr['groupby_time'])) {
                    $time = $row1[$search_arr['groupby_time']];
                    if ($search_arr['groupby_time'] == "HOUR" || $search_arr['groupby_time'] == "DAY") {
                        $time = sprintf('%02d', $time);
                    }
                    if ($search_arr['groupby_time'] == "MONTH") {
                        $dateObj = DateTime::createFromFormat('!m', $time);
                        $time = $dateObj->format('F');
                    }
                    $new_arr[] = $time;
                }
                if ($search_arr['groupby_1'] == "order_items.accountid") {
                    $new_arr[] = $this->common->build_concat_string("first_name,last_name,number", "accounts", $row1["accountid"]);
                } elseif ($search_arr['groupby_1'] == 'order_items.product_id') {
                    $new_arr[] = $this->common->get_field_name('name', 'products', $row1['product_id']);
                } elseif ($search_arr['groupby_1'] == 'order_items.product_category') {
                    $new_arr[] = $this->common->get_field_name('name', 'category', $row1['product_category']);
                }

                if ($search_arr['groupby_2'] == "order_items.accountid") {
                    $new_arr[] = $this->common->build_concat_string("first_name,last_name,number", "accounts", $row1["accountid"]);
                } elseif ($search_arr['groupby_2'] == 'order_items.product_id') {
                    $new_arr[] = $this->common->get_field_name('name', 'products', $row1['product_id']);
                } elseif ($search_arr['groupby_2'] == 'order_items.product_category') {
                    $new_arr[] = $this->common->get_field_name('name', 'category', $row1['product_category']);
                }

                if ($search_arr['groupby_3'] == "order_items.accountid") {
                    $new_arr[] = $this->common->build_concat_string("first_name,last_name,number", "accounts", $row1["accountid"]);
                } elseif ($search_arr['groupby_3'] == 'order_items.product_id') {
                    $new_arr[] = $this->common->get_field_name('name', 'products', $row1['product_id']);
                } elseif ($search_arr['groupby_3'] == 'order_items.product_category') {
                    $new_arr[] = $this->common->get_field_name('name', 'category', $row1['product_category']);
                }

                if (empty($new_arr)) {
                    $new_arr[] = $this->common->build_concat_string("first_name,last_name,number", "accounts", $row1["accountid"]);
                }
            } else {
                $new_arr[] = $this->common->build_concat_string("first_name,last_name,number", "accounts", $row1["accountid"]);
            }
            $custom_array = array(
                $order_date = $row1['order_date'],
                $row1['quantity'],
                $row1['price'],
                $free_minutes_result,
                $this->common->get_renewal_type_category_list("", "", $row1['billing_type']),
                $row1['billing_days']
            );
            $final_array = array_merge($new_arr, $custom_array);
            $json_data[] = array(
                'cell' => $final_array
            );
            $export_arr[] = $final_array;
        }
        $free_minutes = '';
        $function_name = 'get_' . $entity . 'summary_report_list';

        $total_info = $this->summary_model->$function_name(true, '', '', $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], true);
        $total_info = $total_info->result_array();

        $quantity = 0;
        $price = 0;
        $minutes = 0;
        $billing_type = 0;
        $billing_days = 0;
        foreach ($total_info as $key => $val) {
            $quantity += $val['quantity'];
            $price += $val['price'];
            $minutes += $val['free_minutes'];
            $billing_type += $val['billing_type'];
            $billing_days += $val['billing_days'];
        }
        if ($show_seconds == 'minutes') {
            $free_minutes = $minutes > 0 ? sprintf('%02d', $minutes / 60) . ":" . sprintf('%02d', ($minutes % 60)) : "00:00";
        } else {
            $free_minutes = $minutes;
        }
        $last_array = array(
            "",
            "<b>" . $quantity . "</b>",
            "<b>" . $price . "</b>",
            "<b>" . $free_minutes . "</b>",
            "",
            "<b>" . $billing_days . "</b>"
        );

        if ($purpose == 'export') {
            $search_arr['custom_total_array'][0] = "Grand Total";
        }
        $new_export_array = array();
        foreach ($last_array as $key => $value) {
            $value = str_replace("<b>", "", $value);
            $value = str_replace("</b>", '', $value);
            if ($key == 7 || $key == 8 || $key == 9) {
                $value = sprintf("%." . $currency_info['decimalpoints'] . "f", floatval($value));
            }
            $new_export_array[$key] = $value;
        }
        $total_array = array_merge($search_arr['custom_total_array'], $last_array);
        $custom_export_arr = array_merge($search_arr['custom_total_array'], $new_export_array);
        $export_arr[] = $custom_export_arr;
        $json_data[] = array(
            'cell' => $total_array
        );

        return $purpose == 'grid' ? $json_data : $export_arr;
    }

    function product_export_csv()
    {
        $search_arr = $this->session->userdata('productsummary_reports_export');
        $data_arr = array();
        $query = $this->summary_model->get_productsummary_report_list(true, '', '', $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], true);
        $search_header = explode(",", $search_arr['export_str']);
        ob_clean();
        $fixed_header = array(
            'Quantity',
            'Price',
            'Free Minutes',
            'Billing Type',
            'Billing Days'
        );
        $header_arr[] = array_merge($search_header, $fixed_header);
        if ($query->num_rows() > 0) {
            $data_arr = $this->product_summary_report_grid($search_arr, $query, 'product', 'export');
        }
        $customer_array = array_merge($header_arr, $data_arr);
        $this->load->helper('csv');
        array_to_csv($customer_array, 'Product_Summary_Report_' . date("Y-m-d") . '.csv');
    }
}
?>

