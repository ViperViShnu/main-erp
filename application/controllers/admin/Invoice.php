<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once APPPATH . '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class Invoice extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('invoice_model');
        $this->load->library('gst');
    }

    public function invoice_state_report()
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/dashboard');
        }
        $data = array();
        $pathonor_jonno['invoice_state_report_div'] = $this->load->view("admin/invoice/invo_data_2", $data, true);
        echo json_encode($pathonor_jonno);
        exit;
    }

    public function invo_data()
    {
        // cheeck its ajax request then redirect to admin/dashboard
        if (!$this->input->is_ajax_request()) {
            redirect('admin/dashboard');
        }
        $client_outstanding = 0;
        $all_valid_invo_total = 0;
        $past_overdue = 0;
        $all_paid_amount = 0;
        $not_paid = 0;
        $fully_paid = 0;
        $draft = 0;
        $partially_paid = 0;
        $overdue = 0;

        $all_invo_res = $this->db->query("SELECT
        COALESCE(SUM(tbl_items.total_cost), 0) AS all_invoices_cost,
        COALESCE(SUM(tbl_invoices.discount_total), 0) AS all_invoices_discount,
        COALESCE(SUM(tbl_items.item_tax_total), 0) AS all_invoices_tax,
        COALESCE(SUM(tbl_invoices.adjustment), 0) AS all_invoices_adjustment
        FROM tbl_invoices
        LEFT JOIN tbl_items ON tbl_invoices.invoices_id = tbl_items.invoices_id
        WHERE tbl_invoices.status != 'cancelled' AND tbl_invoices.status != 'draft' AND tbl_invoices.inv_deleted = 'No'")->row();

        if (!empty($all_invo_res) && $all_invo_res->all_invoices_cost > 0) {
            $all_valid_invo_total = $all_invo_res->all_invoices_cost - $all_invo_res->all_invoices_discount + $all_invo_res->all_invoices_tax + $all_invo_res->all_invoices_adjustment;
        }


        $today_invo_res = $this->db->query("SELECT
        COALESCE(SUM(tbl_items.total_cost), 0) AS all_invoices_cost,
        COALESCE(SUM(tbl_invoices.discount_total), 0) AS all_invoices_discount,
        COALESCE(SUM(tbl_items.item_tax_total), 0) AS all_invoices_tax,
        COALESCE(SUM(tbl_invoices.adjustment), 0) AS all_invoices_adjustment
        FROM tbl_invoices
        LEFT JOIN tbl_items ON tbl_invoices.invoices_id = tbl_items.invoices_id
        WHERE tbl_invoices.status != 'cancelled' AND tbl_invoices.status != 'draft'  AND tbl_invoices.inv_deleted = 'No' AND  invoice_date = CURDATE()")->row();

        if (!empty($today_invo_res) && $today_invo_res->all_invoices_cost > 0) {
            $today_valid_invo_total = $today_invo_res->all_invoices_cost - $today_invo_res->all_invoices_discount + $today_invo_res->all_invoices_tax + $today_invo_res->all_invoices_adjustment;
        } else {
            $today_valid_invo_total = 0;
        }

        $all_paid_amount_res = $this->db->query("SELECT COALESCE(SUM(tbl_payments.amount), 0) as all_paid_amount
        FROM tbl_invoices
        LEFT JOIN tbl_payments ON tbl_invoices.invoices_id = tbl_payments.invoices_id
        WHERE tbl_invoices.status != 'cancelled' AND tbl_invoices.status != 'draft'")->row()->all_paid_amount;
        if (!empty($all_paid_amount_res)) {
            $all_paid_amount = $all_paid_amount_res;
        }

        $client_outstanding = $all_valid_invo_total - $all_paid_amount;


        $payment_today = $this->db->query("SELECT COALESCE(SUM(tbl_payments.amount), 0) as all_paid_amount
        FROM tbl_invoices
        LEFT JOIN tbl_payments ON tbl_invoices.invoices_id = tbl_payments.invoices_id
        WHERE tbl_invoices.status != 'cancelled' AND tbl_invoices.status != 'draft' AND  payment_date = CURDATE()")->row()->all_paid_amount;


        $due_date_expired_invo_res = $this->db->query("SELECT
        COALESCE(count(tbl_invoices.invoices_id), 0) as invoice_overdue_no,
        COALESCE(SUM(tbl_items.total_cost), 0) AS all_invoices_cost,
        COALESCE(SUM(tbl_invoices.discount_total), 0) AS all_invoices_discount,
        COALESCE(SUM(tbl_items.item_tax_total), 0) AS all_invoices_tax,
        COALESCE(SUM(tbl_invoices.adjustment), 0) AS all_invoices_adjustment,
        COALESCE(SUM(tbl_payments.amount), 0) AS unpaid_invoices_payment
        FROM tbl_invoices
        LEFT JOIN tbl_items ON tbl_invoices.invoices_id = tbl_items.invoices_id
        LEFT JOIN tbl_payments ON tbl_invoices.invoices_id = tbl_payments.invoices_id
        WHERE tbl_invoices.status != 'cancelled' AND tbl_invoices.status != 'draft' AND due_date < CURDATE() AND status != 'paid'")->row();

        if (!empty($due_date_expired_invo_res)) {
            $past_overdue = $due_date_expired_invo_res->all_invoices_cost - $due_date_expired_invo_res->all_invoices_discount
                + $due_date_expired_invo_res->all_invoices_tax + $due_date_expired_invo_res->all_invoices_adjustment
                - $due_date_expired_invo_res->unpaid_invoices_payment;
        }

        $res['client_outstanding'] = display_money($all_valid_invo_total - $all_paid_amount, default_currency()); // $client_outstanding
        $res['total_invo_amount'] = $res['total_invoice_amount'] = display_money($all_valid_invo_total, default_currency());
        $res['today_invo_amount'] = display_money($today_valid_invo_total, default_currency());
        $res['paid_invo_amount'] = $res['total_receipts'] = display_money($all_paid_amount, default_currency());
        //$res['total_receipts'] = display_money($all_paid_amount, default_currency());
        $res['payment_today'] = display_money($payment_today, default_currency());
        $res['past_overdue'] = display_money($past_overdue, default_currency());
        $res['invoice_overdue_no'] = $due_date_expired_invo_res->invoice_overdue_no;
        $data = array();
        //$res['state_report'] = $this->load->view('admin/invoice/invo_data', $data, TRUE);
        echo json_encode($res);
        exit();
    }

    public function createinvoice($action = NULL, $id = NULL, $item_id = NULL)
    {
        $data['page'] = lang('sales');

        if ($action == 'all_payments') {
            $data['sub_active'] = lang('payments_received');
        } else {
            $data['sub_active'] = lang('invoice');
        }
        if (!empty($item_id)) {
            $can_edit = $this->invoice_model->can_action('tbl_invoices', 'edit', array('invoices_id' => $id));

            if (!empty($can_edit)) {

                $data['item_info'] = $this->invoice_model->check_by(array('items_id' => $item_id), 'tbl_items');
            }
        }
        if (!empty($id) && $action != 'payments_details') {
            // get all invoice info by id
            $can_edit = $this->invoice_model->can_action('tbl_invoices', 'edit', array('invoices_id' => $id));

            if (!empty($can_edit)) {
                $data['invoice_info'] = $this->invoice_model->check_by(array('invoices_id' => $id), 'tbl_invoices');
                if (!empty($data['invoice_info']->client_id)) {
                    $data['invoices_to_merge'] = $this->invoice_model->check_for_merge_invoice($data['invoice_info']->client_id, $id);
                }
            }
        }
        if ($action == 'create_invoice') {

            $data['active'] = 2;
        } else {
            $data['active'] = 1;
        }
        // get all client
        $this->invoice_model->_table_name = 'tbl_client';
        $this->invoice_model->_order_by = 'client_id';
        $data['all_client'] = $this->invoice_model->get();

        // get permission user
        $data['permission_user'] = $this->invoice_model->all_permission_user('13');
        $type = $this->uri->segment(5);
        if (empty($type)) {
            $type = '_' . date('Y');
        }
        $filterBy = null;
        if (!empty($type) && !is_numeric($type)) {
            $ex = explode('_', $type);
            if ($ex[0] != 'c') {
                $filterBy = $type;
            }
        }

        if ($action == 'invoice_details') {
            $data['title'] = "Invoice Details"; //Page title
            $data['invoice_info'] = $this->invoice_model->check_by(array('invoices_id' => $id), 'tbl_invoices');
            if (!empty($data['invoice_info'])) {
                $data['client_info'] = $this->invoice_model->check_by(array('client_id' => $data['invoice_info']->client_id), 'tbl_client');
                $payment_status = $this->invoice_model->get_payment_status($id);
                if ($payment_status != lang('cancelled') && $payment_status != lang('fully_paid')) {
                    $this->load->model('credit_note_model');
                    $data['total_available_credit'] = $this->credit_note_model->get_available_credit_by_client($data['invoice_info']->client_id);
                }
                $lang = $this->invoice_model->all_files();
                foreach ($lang as $file => $altpath) {
                    $shortfile = str_replace("_lang.php", "", $file);
                    //CI will record your lang file is loaded, unset it and then you will able to load another
                    //unset the lang file to allow the loading of another file
                    if (isset($this->lang->is_loaded)) {
                        $loaded = sizeof($this->lang->is_loaded);
                        if ($loaded < 3) {
                            for ($i = 3; $i <= $loaded; $i++) {
                                unset($this->lang->is_loaded[$i]);
                            }
                        } else {
                            for ($i = 0; $i <= $loaded; $i++) {
                                unset($this->lang->is_loaded[$i]);
                            }
                        }
                    }
                    if (!empty($data['client_info']->language)) {
                        $language = $data['client_info']->language;
                    } else {
                        $language = 'english';
                    }
                    $data['language_info'] = $this->lang->load($shortfile, $language, TRUE, TRUE, $altpath);
                }
                $subview = 'invoice_details';
                // get payment info by id
                $this->invoice_model->_table_name = 'tbl_payments';
                $this->invoice_model->_order_by = 'payments_id';
                $data['all_payments_history'] = $this->invoice_model->get_by(array('invoices_id' => $id), FALSE);
            } else {
                set_message('error', 'No data Found');
                redirect('admin/invoice/manage_invoice');
            }
        } elseif ($action == 'payment' || $action == 'payment_history') {
            $data['title'] = lang($action); //Page title
            // get payment info by id
            $this->invoice_model->_table_name = 'tbl_payments';
            $this->invoice_model->_order_by = 'payments_id';
            $data['all_payments_history'] = $this->invoice_model->get_by(array('invoices_id' => $id), FALSE);

            $subview = $action;
        } elseif ($action == 'payments_details') {
            $data['title'] = "Payments Details"; //Page title
            $subview = 'payments_details';
            // get payment info by id
            $this->invoice_model->_table_name = 'tbl_payments';
            $this->invoice_model->_order_by = 'payments_id';
            $data['payments_info'] = $this->invoice_model->get_by(array('payments_id' => $id), TRUE);
        } elseif ($action == 'invoice_history') {
            $data['invoice_info'] = $this->invoice_model->check_by(array('invoices_id' => $id), 'tbl_invoices');
            $data['title'] = "Invoice History"; //Page title
            $subview = 'invoice_history';
        } elseif ($action == 'email_invoice') {
            $data['invoice_info'] = $this->invoice_model->check_by(array('invoices_id' => $id), 'tbl_invoices');
            $data['title'] = "Email Invoice"; //Page title
            $subview = 'email_invoice';
        } elseif ($action == 'send_reminder') {
            $data['invoice_info'] = $this->invoice_model->check_by(array('invoices_id' => $id), 'tbl_invoices');
            $data['title'] = "Send Remainder"; //Page title
            $subview = 'send_reminder';
        } elseif ($action == 'send_overdue') {
            $data['invoice_info'] = $this->invoice_model->check_by(array('invoices_id' => $id), 'tbl_invoices');
            $data['title'] = lang('send_invoice_overdue'); //Page title
            $subview = 'send_overdue';
        } elseif ($action == 'make_payment') {
            //$data['all_invoices'] = $this->invoice_model->get_permission('tbl_invoices');
            $data['title'] = lang('make_payment'); //Page title
            $subview = 'make_payment';
        } else {
            $data['title'] = "Create Invoice"; //Page title
            // $data['invoice_info'] = $this->invoice_model->check_by(array('invoices_id' => $id), 'tbl_invoices');
            // print('<pre>' . print_r($data['invoice_info'], true) . '</pre>');
            // exit;

            $subview = 'createinvoice';
        }
        $data['subview'] = $this->load->view('admin/invoice/' . $subview, $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function manage_invoice($action = NULL, $id = NULL, $item_id = NULL)
    {
        $data['page'] = lang('sales');

        if ($action == 'all_payments') {
            $data['sub_active'] = lang('payments_received');
        } else {
            $data['sub_active'] = lang('invoice');
        }
        if (!empty($item_id)) {
            $can_edit = $this->invoice_model->can_action('tbl_invoices', 'edit', array('invoices_id' => $id));

            if (!empty($can_edit)) {
                $data['item_info'] = $this->invoice_model->check_by(array('items_id' => $item_id), 'tbl_items');
            }
        }
        if (!empty($id) && $action != 'payments_details') {
            // get all invoice info by id
            $can_edit = $this->invoice_model->can_action('tbl_invoices', 'edit', array('invoices_id' => $id));

            if (!empty($can_edit)) {
                $data['invoice_info'] = join_data('tbl_invoices', '*', array('invoices_id' => $id), array('tbl_client' => 'tbl_client.client_id = tbl_invoices.client_id'));
                if (!empty($data['invoice_info']->client_id)) {
                    $data['invoices_to_merge'] = $this->invoice_model->check_for_merge_invoice($data['invoice_info']->client_id, $id);
                }
            }
        }
        if ($action == 'create_invoice') {
            $data['active'] = 2;
        } else {
            $data['active'] = 1;
        }
        // get all client
        $this->invoice_model->_table_name = 'tbl_client';
        $this->invoice_model->_order_by = 'client_id';
        $data['all_client'] = $this->invoice_model->get();

        // get permission user
        $data['permission_user'] = $this->invoice_model->all_permission_user('13');
        $type = $this->uri->segment(5);
        if (empty($type)) {
            $type = '_' . date('Y');
        }
        $filterBy = null;
        if (!empty($type) && !is_numeric($type)) {
            $ex = explode('_', $type);
            if ($ex[0] != 'c') {
                $filterBy = $type;
            }
        }

        if ($action == 'invoice_details') {
            redirect('admin/invoice/invoice_details/' . $id);
        } elseif ($action == 'payment' || $action == 'payment_history') {
            $data['title'] = lang($action); //Page title
            // get payment info by id
            $this->invoice_model->_table_name = 'tbl_payments';
            $this->invoice_model->_order_by = 'payments_id';
            $data['all_payments_history'] = $this->invoice_model->get_by(array('invoices_id' => $id), FALSE);
            $subview = $action;
        } elseif ($action == 'payments_details') {
            $data['title'] = "Payments Details"; //Page title
            $subview = 'payments_details';
            // get payment info by id
            $this->invoice_model->_table_name = 'tbl_payments';
            $this->invoice_model->_order_by = 'payments_id';
            $data['payments_info'] = $this->invoice_model->get_by(array('payments_id' => $id), TRUE);
        } elseif ($action == 'invoice_history') {
            $data['invoice_info'] = join_data('tbl_invoices', '*', array('invoices_id' => $id), array('tbl_client' => 'tbl_client.client_id = tbl_invoices.client_id'));
            $data['title'] = "Invoice History"; //Page title
            $subview = 'invoice_history';
        } elseif ($action == 'email_invoice') {
            $data['invoice_info'] = join_data('tbl_invoices', '*', array('invoices_id' => $id), array('tbl_client' => 'tbl_client.client_id = tbl_invoices.client_id'));
            $data['title'] = "Email Invoice"; //Page title
            $subview = 'email_invoice';
        } elseif ($action == 'send_reminder') {
            $data['invoice_info'] = join_data('tbl_invoices', '*', array('invoices_id' => $id), array('tbl_client' => 'tbl_client.client_id = tbl_invoices.client_id'));
            $data['title'] = "Send Remainder"; //Page title
            $subview = 'send_reminder';
        } elseif ($action == 'send_overdue') {
            $data['invoice_info'] = join_data('tbl_invoices', '*', array('invoices_id' => $id), array('tbl_client' => 'tbl_client.client_id = tbl_invoices.client_id'));
            $data['title'] = lang('send_invoice_overdue'); //Page title
            $subview = 'send_overdue';
        } elseif ($action == 'make_payment') {
            //$data['all_invoices'] = $this->invoice_model->get_permission('tbl_invoices');
            $data['title'] = lang('make_payment'); //Page title
            $subview = 'make_payment';
        } else {
            $data['title'] = "Manage Invoice"; //Page title
            $subview = 'manage_invoice';
        }
        $data['all_invoices_info'] = $this->invoice_model->get_permission('tbl_invoices');
        $data['subview'] = $this->load->view('admin/invoice/' . $subview, $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function invoice_details($id, $pdf = NULL)
    {
        $data['title'] = "Invoice"; //Page title
        $data['sales_info'] = join_data('tbl_invoices', '*', array('invoices_id' => $id), array('tbl_client' => 'tbl_client.client_id = tbl_invoices.client_id'));
        if (!empty($data['sales_info'])) {
            $url = base_url('frontend/view_invoice/') . url_encode($id);
            $data['qrcode'] = generate_qrcode($url);

            $payment_status = $this->invoice_model->get_payment_status($id);
            $data['sales_info']->ref_no = lang('invoice') . ' : ' . $data['sales_info']->reference_no;
            $data['sales_info']->start_date = lang('invoice_date') . ' : ' . display_date($data['sales_info']->invoice_date);
            // check overdue invoice
            if (strtotime($data['sales_info']->due_date) < strtotime(date('Y-m-d')) && $payment_status != lang('fully_paid') && $payment_status != lang('cancelled')) {
                // check overdue how many days from due_date
                $date1 = new DateTime($data['sales_info']->due_date);
                $date2 = new DateTime(date('Y-m-d'));
                $interval = $date1->diff($date2);
                $overdue_days = $interval->format('%a');
                $data['sales_info']->overdue_days = lang('invoice_overdue') . ' ' . lang('by') . ' ' . $overdue_days . ' ' . lang('days');
            }
            $data['sales_info']->end_date = lang('due_date') . ' : ' . display_date($data['sales_info']->due_date);
            if (!empty($data['sales_info']->user_id)) {
                $data['sales_info']->sales_agent = lang('sales') . ' ' . lang('agent') . ' : ' . fullname($data['sales_info']->user_id);
            }
            if ($payment_status == lang('fully_paid')) {
                $label = "success";
            } elseif ($payment_status == lang('draft')) {
                $label = "default";
                $text = lang('status_as_draft');
            } elseif ($payment_status == lang('cancelled')) {
                $label = "danger";
            } elseif ($payment_status == lang('partially_paid')) {
                $label = "warning";
            } elseif ($data['sales_info']->emailed == 'Yes') {
                $label = "info";
                $payment_status = lang('sent');
            } else {
                $label = "danger";
            }
            $data['sales_info']->status = lang('status') . ' :  <span class="label label-' . $label . '">' . lang($payment_status) . '</span>';
            if (!empty($text)) {
                $data['sales_info']->status .= '<br><p style="padding: 15px;margin-bottom: 20px;border: 1px solid transparent;border-radius: 4px;;background: color: #8a6d3b;background-color: #fcf8e3;border-color: #faebcc;">' . $text . '</p>';
            }
            $data['sales_info']->custom_field = '';
            $show_custom_fields = custom_form_label(9, $data['sales_info']->invoices_id);
            if (!empty($show_custom_fields)) {
                foreach ($show_custom_fields as $c_label => $v_fields) {
                    if (!empty($v_fields)) {
                        $data['sales_info']->custom_field .= $c_label . ' : ' . $v_fields . '<br>';
                    }
                }
            }
            $data['all_items'] = $this->invoice_model->ordered_items_by_id($data['sales_info']->invoices_id);
            $data['sales_info']->sub_total = ($this->invoice_model->calculate_to('invoice_cost', $data['sales_info']->invoices_id));
            $data['sales_info']->discount = ($this->invoice_model->calculate_to('discount', $data['sales_info']->invoices_id));
            $data['sales_info']->total = ($this->invoice_model->calculate_to('total', $data['sales_info']->invoices_id));
            $data['paid_amount'] = $this->invoice_model->calculate_to('paid_amount', $data['sales_info']->invoices_id);
            $data['invoice_due'] = $this->invoice_model->calculate_to('invoice_due', $data['sales_info']->invoices_id);
            $data['payment_status'] = $payment_status;
            if ($payment_status != lang('cancelled') && $payment_status != lang('fully_paid')) {
                $this->load->model('credit_note_model');
                $data['total_available_credit'] = $this->credit_note_model->get_available_credit_by_client($data['sales_info']->client_id);
            }
            // get payment info by id
            $data['all_payment_info'] = get_result('tbl_payments', array('invoices_id' => $id));
            $data['footer'] = config_item('invoice_footer');
        } else {
            set_message('error', 'No data Found');
            redirect('admin/invoice/manage_invoice');
        }
        if (!empty($pdf)) {
            $this->common_model->sales_pdf($data, $pdf);
        }
        $data['subview'] = $this->load->view('admin/invoice/invoice_details', $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function delivery_note($id, $pdf = true)
    {
        $data['title'] = "Invoice"; //Page title
        $data['sales_info'] = join_data('tbl_invoices', '*', array('invoices_id' => $id), array('tbl_client' => 'tbl_client.client_id = tbl_invoices.client_id'));
        if (!empty($data['sales_info'])) {
            $url = base_url('frontend/view_invoice/') . url_encode($id);
            $data['qrcode'] = generate_qrcode($url);

            $payment_status = $this->invoice_model->get_payment_status($id);
            $data['sales_info']->ref_no = lang('invoice') . ' : ' . $data['sales_info']->reference_no;
            $data['sales_info']->start_date = lang('invoice_date') . ' : ' . display_date($data['sales_info']->invoice_date);
            // check overdue invoice
            if (strtotime($data['sales_info']->due_date) < strtotime(date('Y-m-d')) && $payment_status != lang('fully_paid') && $payment_status != lang('cancelled')) {
                // check overdue how many days from due_date
                $date1 = new DateTime($data['sales_info']->due_date);
                $date2 = new DateTime(date('Y-m-d'));
                $interval = $date1->diff($date2);
                $overdue_days = $interval->format('%a');
                $data['sales_info']->overdue_days = lang('invoice_overdue') . ' ' . lang('by') . ' ' . $overdue_days . ' ' . lang('days');
            }
            $data['sales_info']->end_date = lang('due_date') . ' : ' . display_date($data['sales_info']->due_date);
            if (!empty($data['sales_info']->user_id)) {
                $data['sales_info']->sales_agent = lang('sales') . ' ' . lang('agent') . ' : ' . fullname($data['sales_info']->user_id);
            }
            if ($payment_status == lang('fully_paid')) {
                $label = "success";
            } elseif ($payment_status == lang('draft')) {
                $label = "default";
                $text = lang('status_as_draft');
            } elseif ($payment_status == lang('cancelled')) {
                $label = "danger";
            } elseif ($payment_status == lang('partially_paid')) {
                $label = "warning";
            } elseif ($data['sales_info']->emailed == 'Yes') {
                $label = "info";
                $payment_status = lang('sent');
            } else {
                $label = "danger";
            }
            $data['sales_info']->status = lang('status') . ' :  <span class="label label-' . $label . '">' . lang($payment_status) . '</span>';
            if (!empty($text)) {
                $data['sales_info']->status .= '<br><p style="padding: 15px;margin-bottom: 20px;border: 1px solid transparent;border-radius: 4px;;background: color: #8a6d3b;background-color: #fcf8e3;border-color: #faebcc;">' . $text . '</p>';
            }
            $data['sales_info']->custom_field = '';
            $show_custom_fields = custom_form_label(9, $data['sales_info']->invoices_id);
            if (!empty($show_custom_fields)) {
                foreach ($show_custom_fields as $c_label => $v_fields) {
                    if (!empty($v_fields)) {
                        $data['sales_info']->custom_field .= $c_label . ' : ' . $v_fields . '<br>';
                    }
                }
            }
            $data['all_items'] = $this->invoice_model->ordered_items_by_id($data['sales_info']->invoices_id);
            $data['sales_info']->sub_total = ($this->invoice_model->calculate_to('invoice_cost', $data['sales_info']->invoices_id));
            $data['sales_info']->discount = ($this->invoice_model->calculate_to('discount', $data['sales_info']->invoices_id));
            $data['sales_info']->total = ($this->invoice_model->calculate_to('total', $data['sales_info']->invoices_id));
            $data['paid_amount'] = $this->invoice_model->calculate_to('paid_amount', $data['sales_info']->invoices_id);
            $data['invoice_due'] = $this->invoice_model->calculate_to('invoice_due', $data['sales_info']->invoices_id);
            $data['payment_status'] = $payment_status;
            if ($payment_status != lang('cancelled') && $payment_status != lang('fully_paid')) {
                $this->load->model('credit_note_model');
                $data['total_available_credit'] = $this->credit_note_model->get_available_credit_by_client($data['sales_info']->client_id);
            }
            // get payment info by id
            $data['all_payment_info'] = get_result('tbl_payments', array('invoices_id' => $id));
            $data['footer'] = config_item('invoice_footer');
        } else {
            set_message('error', 'No data Found');
            redirect('admin/invoice/manage_invoice');
        }
        if (!empty($pdf)) {
            $this->common_model->sales_pdf($data, $pdf, true);
        }
    }


    public
    function invoices_credit($invoices_id)
    {
        $data['invoice_info'] = get_row('tbl_invoices', array('invoices_id' => $invoices_id));
        $payment_status = $this->invoice_model->get_payment_status($invoices_id);
        $this->load->model('credit_note_model');
        $total_available_credit = $this->credit_note_model->get_available_credit_by_client($data['invoice_info']->client_id);
        if ($payment_status != lang('cancelled') && $payment_status != lang('fully_paid') && !empty($total_available_credit)) {
            $data['all_open_credit'] = get_result('tbl_credit_note', array('status' => 'open', 'client_id' => $data['invoice_info']->client_id));
            $data['subview'] = $this->load->view('admin/credit_note/invoices_to_credits', $data, FALSE);
            $this->load->view('admin/_layout_modal', $data);
        } else {
            $type = "error";
            $message = "No Record Found";
            set_message($type, $message);
            redirect('admin/credit_note');
        }
    }


    public
    function apply_invoices_credit($invoices_id)
    {
        $invoice_amount = $this->input->post('amount', true);
        $added_into_payment = $this->input->post('added_into_payment', true);
        if ($invoice_amount) {
            foreach ($invoice_amount as $credit_note_id => $amount) {
                if (!empty($amount)) {
                    $this->load->model('credit_note_model');
                    $credit_remaining = $this->credit_note_model->credit_note_calculation('credit_remaining', $credit_note_id);
                    $credit_info = $this->invoice_model->check_by(array('credit_note_id' => $credit_note_id), 'tbl_credit_note');
                    if ($amount > $credit_remaining) {
                        // messages for user
                        $error[] = lang('overpaid_amount') . ' the ' . $credit_info->reference_no;
                    } else {
                        $this->apply_credits($invoices_id, ['amount' => $amount, 'credit_note_id' => $credit_note_id, 'added_into_payment' => $added_into_payment]);
                    }
                }
            }
        }
        if (!empty($error)) {
            foreach ($error as $show) {
                set_message('error', $show);
            }
        }
        set_message('success', lang('credit_applied_to_invoices'));
        redirect('admin/invoice/manage_invoice/invoice_details/' . $invoices_id);
    }

    public function apply_credits($invoices_id, $input_post)
    {
        $data = array(
            'invoices_id' => $invoices_id,
            'credit_note_id' => $input_post['credit_note_id'],
            'user_id' => my_id(),
            'date' => date('Y-m-d'),
            'date_applied' => date('Y-m-d H:i'),
            'amount' => $input_post['amount'],
        );
        $this->invoice_model->_table_name = 'tbl_credit_used';
        $this->invoice_model->_primary_key = 'credit_used_id';
        $credit_used_id = $this->invoice_model->save($data);
        if (!empty($credit_used_id)) {
            if ($input_post['added_into_payment'] == 'on') {
                $input_post['invoices_id'] = $invoices_id;
                $input_post['credit_used_id'] = $credit_used_id;
                $this->added_into_payment($input_post);
            }
        }

        return true;
    }

    private function added_into_payment($input_post)
    {
        $this->load->model('credit_note_model');
        $this->load->helper('string_helper');
        $invoices_id = $input_post['invoices_id'];
        $paid_amount = $input_post['amount'];
        $due = $this->invoice_model->calculate_to('invoice_due', $invoices_id);
        $credit_notes = $this->db->where('credit_note_id', $input_post['credit_note_id'])->get('tbl_credit_note')->row();
        if ($paid_amount != 0) {
            $trans_id = random_string('nozero', 6);
            $inv_info = $this->invoice_model->check_by(array('invoices_id' => $invoices_id), 'tbl_invoices');
            $data = array(
                'invoices_id' => $invoices_id,
                'paid_by' => $inv_info->client_id,
                'payment_method' => config_item('default_payment_method'),
                'currency' => client_currency($inv_info->client_id),
                'amount' => $paid_amount,
                'payment_date' => date('Y-m-d'),
                'trans_id' => $trans_id,
                'notes' => 'This Payment from Credit notes <a href="' . base_url('admin/credit_note/index/credit_note_details/' . $input_post['credit_note_id']) . '">' . $credit_notes->reference_no . '</a>',
                'month_paid' => date("m"),
                'year_paid' => date("Y"),
            );
            $this->invoice_model->_table_name = 'tbl_payments';
            $this->invoice_model->_primary_key = 'payments_id';
            $payments_id = $this->invoice_model->save($data);

            $this->invoice_model->_table_name = 'tbl_credit_used';
            $this->invoice_model->_primary_key = 'credit_used_id';
            $cu_data['payments_id'] = $payments_id;
            $this->invoice_model->save($cu_data, $input_post['credit_used_id']);


            if ($paid_amount < $due) {
                $status = 'partially_paid';
            }
            if ($paid_amount == $due) {
                $status = 'Paid';
            }
            $invoice_data['status'] = $status;
            update('tbl_invoices', array('invoices_id' => $invoices_id), $invoice_data);

            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'invoice',
                'module_field_id' => $invoices_id,
                'activity' => ('activity_new_payment'),
                'icon' => 'fa-shopping-cart',
                'link' => 'admin/invoice/manage_invoice/invoice_details/' . $invoices_id,
                'value1' => display_money($paid_amount, client_currency($inv_info->client_id)),
                'value2' => $inv_info->reference_no,
            );
            $this->invoice_model->_table_name = 'tbl_activities';
            $this->invoice_model->_primary_key = 'activities_id';
            $this->invoice_model->save($activity);

            if (!empty($inv_info->user_id)) {
                $notifiedUsers = array($inv_info->user_id);
                foreach ($notifiedUsers as $users) {
                    if ($users != $this->session->userdata('user_id')) {
                        add_notification(array(
                            'to_user_id' => $users,
                            'description' => 'not_new_invoice_payment',
                            'icon' => 'shopping-cart',
                            'link' => 'admin/invoice/manage_invoice/invoice_details/' . $invoices_id,
                            'value' => lang('invoice') . ' ' . $inv_info->reference_no . ' ' . lang('amount') . display_money($paid_amount, $currency->symbol),
                        ));
                    }
                }
                show_notification($notifiedUsers);
            }
            if ($this->input->post('save_into_account') == 'on') {
                $account_id = config_item('default_account');
                if (!empty($account_id)) {
                    $reference = lang('invoice') . ' ' . lang('reference_no') . ": <a href='" . base_url('admin/invoice/manage_invoice/invoice_details/' . $inv_info->invoices_id) . "' >" . $inv_info->reference_no . "</a> and " . lang('trans_id') . ": <a href='" . base_url('admin/invoice/manage_invoice/payments_details/' . $payments_id) . "'>" . $this->input->post('trans_id', true) . "</a>";
                    // save into tbl_transaction
                    $tr_data = array(
                        'name' => lang('invoice_payment', lang('trans_id') . '# ' . $trans_id),
                        'type' => 'Income',
                        'amount' => $paid_amount,
                        'credit' => $paid_amount,
                        'date' => date('Y-m-d'),
                        'paid_by' => $inv_info->client_id,
                        'payment_methods_id' => config_item('default_payment_method'),
                        'reference' => $trans_id,
                        'notes' => lang('this_deposit_from_invoice_payment', $reference) . ' ' . 'from credit notes',
                        'permission' => 'all',
                    );

                    $account_info = $this->invoice_model->check_by(array('account_id' => $account_id), 'tbl_accounts');
                    if (!empty($account_info)) {
                        $ac_data['balance'] = $account_info->balance + $tr_data['amount'];
                        $this->invoice_model->_table_name = "tbl_accounts"; //table name
                        $this->invoice_model->_primary_key = "account_id";
                        $this->invoice_model->save($ac_data, $account_info->account_id);

                        $aaccount_info = $this->invoice_model->check_by(array('account_id' => $account_id), 'tbl_accounts');

                        $tr_data['total_balance'] = $aaccount_info->balance;
                        $tr_data['account_id'] = $account_id;

                        // save into tbl_transaction
                        $this->invoice_model->_table_name = "tbl_transactions"; //table name
                        $this->invoice_model->_primary_key = "transactions_id";
                        $return_id = $this->invoice_model->save($tr_data);

                        $deduct_account['account_id'] = $account_id;
                        $this->invoice_model->_table_name = 'tbl_payments';
                        $this->invoice_model->_primary_key = 'payments_id';
                        $this->invoice_model->save($deduct_account, $payments_id);

                        // save into activities
                        $activities = array(
                            'user' => $this->session->userdata('user_id'),
                            'module' => 'transactions',
                            'module_field_id' => $return_id,
                            'activity' => 'activity_new_deposit',
                            'icon' => 'fa-building-o',
                            'link' => 'admin/transactions/view_details/' . $return_id,
                            'value1' => $account_info->account_name,
                            'value2' => $paid_amount,
                        );
                        // Update into tbl_project
                        $this->invoice_model->_table_name = "tbl_activities"; //table name
                        $this->invoice_model->_primary_key = "activities_id";
                        $this->invoice_model->save($activities);
                    }
                }
                if ($this->input->post('send_thank_you') == 'on') {
                    $this->send_payment_email($invoices_id, $paid_amount); //send thank you email
                }

                if ($this->input->post('send_sms') == 'on') {
                    $this->send_payment_sms($invoices_id, $payments_id); //send thank you email
                }
            }
        }
    }

    function send_payment_email($invoices_id, $paid_amount)
    {
        $inv_info = $this->invoice_model->check_by(array('invoices_id' => $invoices_id), 'tbl_invoices');
        $email_template = email_templates(array('email_group' => 'payment_email'), $inv_info->client_id);
        $message = $email_template->template_body;
        $subject = $email_template->subject;
        if (!empty($inv_info)) {
            $currency = $inv_info->currency;
            $reference = $inv_info->reference_no;

            $invoice_currency = str_replace("{INVOICE_CURRENCY}", $currency, $message);
            $reference = str_replace("{INVOICE_REF}", $reference, $invoice_currency);
            $amount = str_replace("{PAID_AMOUNT}", $paid_amount, $reference);
            $message = str_replace("{SITE_NAME}", config_item('company_name'), $amount);

            $data['message'] = $message;
            $message = $this->load->view('email_template', $data, TRUE);
            $client_info = $this->invoice_model->check_by(array('client_id' => $inv_info->client_id), 'tbl_client');

            // send notification to client
            if (!empty($client_info)) {
                $client_info = $this->invoice_model->check_by(array('client_id' => $client_info->client_id), 'tbl_client');
                if (!empty($client_info->primary_contact)) {
                    $notifyUser = array($client_info->primary_contact);
                } else {
                    $user_info = $this->invoice_model->check_by(array('company' => $client_info->client_id), 'tbl_account_details');
                    if (!empty($user_info)) {
                        $notifyUser = array($user_info->user_id);
                    }
                }
            }
            if (!empty($notifyUser)) {
                foreach ($notifyUser as $v_user) {
                    if ($v_user != $this->session->userdata('user_id')) {
                        add_notification(array(
                            'to_user_id' => $v_user,
                            'icon' => 'shopping-cart',
                            'description' => 'not_payment_received',
                            'link' => 'client/invoice/manage_invoice/invoice_details/' . $invoices_id,
                            'value' => lang('invoice') . ' ' . $inv_info->reference_no . ' ' . lang('amount') . display_money($paid_amount, $inv_info->currency),
                        ));
                    }
                }
                show_notification($notifyUser);
            }

            $address = $client_info->email;

            $params['recipient'] = $address;

            $params['subject'] = '[ ' . config_item('company_name') . ' ]' . ' ' . $subject;
            $params['message'] = $message;
            $params['resourceed_file'] = '';

            $activity = array(
                'user' => my_id(),
                'module' => 'invoice',
                'module_field_id' => $invoices_id,
                'activity' => ('activity_send_payment'),
                'icon' => 'fa-shopping-cart',
                'link' => 'admin/invoice/manage_invoice/invoice_details/' . $invoices_id,
                'value1' => $reference,
                'value2' => $currency . ' ' . $amount,
            );
            $this->invoice_model->_table_name = 'tbl_activities';
            $this->invoice_model->_primary_key = 'activities_id';
            $this->invoice_model->save($activity);
            $this->invoice_model->send_email($params);
        } else {
            return true;
        }
    }

    public function send_payment_sms($invoices_id, $payments_id)
    {
        $inv_info = $this->invoice_model->check_by(array('invoices_id' => $invoices_id), 'tbl_invoices');
        $mobile = client_can_received_sms($inv_info->client_id);

        if (!empty($mobile)) {
            $merge_fields = [];
            $merge_fields = array_merge($merge_fields, merge_invoice_template($invoices_id));
            $merge_fields = array_merge($merge_fields, merge_invoice_template($invoices_id, 'payment', $payments_id));
            $merge_fields = array_merge($merge_fields, merge_invoice_template($invoices_id, 'client', $inv_info->client_id));
            $this->sms->send(SMS_PAYMENT_RECORDED, $mobile, $merge_fields);
        }
        return true;
    }

    public function applied_credits($invoices_id)
    {
        $data['title'] = lang('applied_credits');
        $data['all_credit_used'] = get_result('tbl_credit_used', array('invoices_id' => $invoices_id));
        $data['subview'] = $this->load->view('admin/invoice/applied_credits', $data, FALSE);
        $this->load->view('admin/_layout_modal', $data);
    }

    public function invoiceList($filterBy = null, $search_by = null)
    {
        if ($this->input->is_ajax_request()) {
            $where = array();
            $this->load->model('datatables');
            $this->datatables->table = 'tbl_invoices';
            $this->datatables->join_table = array('tbl_client');
            $this->datatables->join_where = array('tbl_invoices.client_id=tbl_client.client_id');
            $this->datatables->select = 'tbl_invoices.*,tbl_client.name';
            $this->datatables->column_order = array('reference_no', 'tbl_client.name', 'invoice_date', 'due_date', 'status', 'tags');
            $this->datatables->column_search = array('reference_no', 'tbl_client.name', 'invoice_date', 'due_date', 'status', 'tags');
            $this->datatables->order = array('invoices_id' => 'desc');

            if (empty($filterBy)) {
                $filterBy = '_' . date('Y');
            }
            if (!empty($filterBy) && !is_numeric($filterBy)) {
                $ex = explode('_', $filterBy);
                if ($ex[0] != 'c') {
                    $filterBy = $filterBy;
                }
            }
            if (!empty($search_by)) {
                if ($search_by == 'by_project') {
                    $where = array('project_id' => $filterBy);
                }
                if ($search_by == 'by_agent') {
                    $where = array('user_id' => $filterBy);
                }
                if ($search_by == 'by_client') {
                    $where = array('tbl_invoices.client_id' => $filterBy);
                }
                if ($search_by == 'by_client_draft') {
                    $where = array('tbl_invoices.client_id' => $filterBy, 'status !=' => 'draft');
                }
                if ($filterBy == 'by_client_recurring') {
                    $where = array('tbl_invoices.client_id' => $filterBy, 'recurring' => 'Yes');
                }
            } else {
                if ($filterBy == 'recurring') {
                    $where = array('recurring' => 'Yes');
                }
                if ($filterBy == 'paid') {
                    $where = array('status' => 'Paid');
                } else if ($filterBy == 'not_paid') {
                    $where = array('status' => 'Unpaid');
                } else if ($filterBy == 'draft') {
                    $where = array('status' => 'draft');
                } else if ($filterBy == 'partially_paid') {
                    $where = array('status' => 'partially_paid');
                } else if ($filterBy == 'cancelled') {
                    $where = array('status' => 'Cancelled');
                } else if ($filterBy == 'overdue') {
                    $where = array('UNIX_TIMESTAMP(due_date) <' => strtotime(date('Y-m-d')), 'status !=' => 'Paid');
                } else if ($filterBy == 'last_month' || $filterBy == 'this_months') {
                    if ($filterBy == 'last_month') {
                        $month = date('Y-m', strtotime('-1 months'));
                    } else {
                        $month = date('Y-m');
                    }
                    $where = array('invoice_month' => $month);
                } else if (strstr($filterBy, '_')) {
                    $year = str_replace('_', '', $filterBy);
                    $where = array('invoice_year' => $year);
                }
            }
            // get all invoice
            $fetch_data = $this->datatables->get_datatable_permission($where);
            $data = array();

            $edited = can_action('13', 'edited');
            $deleted = can_action('13', 'deleted');
            foreach ($fetch_data as $_key => $v_invoices) {
                if (!empty($v_invoices)) {
                    $currency = client_currency($v_invoices->client_id);
                    $action = null;
                    $can_edit = $this->invoice_model->can_action('tbl_invoices', 'edit', array('invoices_id' => $v_invoices->invoices_id));
                    $can_delete = $this->invoice_model->can_action('tbl_invoices', 'delete', array('invoices_id' => $v_invoices->invoices_id));
                    if ($this->invoice_model->get_payment_status($v_invoices->invoices_id) == lang('fully_paid')) {
                        $invoice_status = lang('fully_paid');
                        $label = "success";
                    } elseif ($this->invoice_model->get_payment_status($v_invoices->invoices_id) == lang('draft')) {
                        $invoice_status = lang('draft');
                        $label = "default";
                    } elseif ($this->invoice_model->get_payment_status($v_invoices->invoices_id) == lang('partially_paid')) {
                        $invoice_status = lang('partially_paid');
                        $label = "warning";
                    } elseif ($v_invoices->emailed == 'Yes') {
                        $invoice_status = lang('sent');
                        $label = "info";
                    } else {
                        $invoice_status = $this->invoice_model->get_payment_status($v_invoices->invoices_id);
                        $label = "danger";
                    }

                    $sub_array = array();
                    $name = null;
                    $name .= '<a class="text-info" href="' . base_url() . 'admin/invoice/manage_invoice/invoice_details/' . $v_invoices->invoices_id . '">' . $v_invoices->reference_no . '</a>';
                    $sub_array[] = $name;
                    $sub_array[] = strftime(config_item('date_format'), strtotime($v_invoices->invoice_date));
                    $payment_status = $this->invoice_model->get_payment_status($v_invoices->invoices_id);
                    $overdue = null;
                    if (strtotime($v_invoices->due_date) < strtotime(date('Y-m-d')) && $payment_status != lang('fully_paid') && $payment_status != lang('cancelled')) {
                        $overdue .= '<span class="label label-danger ">' . lang("overdue") . '</span>';
                    }
                    $sub_array[] = strftime(config_item('date_format'), strtotime($v_invoices->due_date)) . ' ' . $overdue;
                    $sub_array[] = '<span class="tags">' . client_name($v_invoices->client_id) . '</span>';
                    $sub_array[] = display_money($this->invoice_model->calculate_to('invoice_due', $v_invoices->invoices_id), $currency);
                    $recurring = null;
                    if ($v_invoices->recurring == 'Yes') {
                        $recurring = '<span data-toggle="tooltip" data-placement="top"
                                                              title="' . lang("recurring") . '"
                                                              class="label label-primary"><i
                                                                class="fa fa-retweet"></i></span>';
                    }
                    $sub_array[] = "<span class='label label-" . $label . "'>" . $invoice_status . "</span>" . ' ' . $recurring;
                    $sub_array[] = get_tags($v_invoices->tags, true);

                    $custom_form_table = custom_form_table(9, $v_invoices->invoices_id);

                    if (!empty($custom_form_table)) {
                        foreach ($custom_form_table as $c_label => $v_fields) {
                            $sub_array[] = $v_fields;
                        }
                    }
                    if (!empty($can_edit) && !empty($edited)) {
                        $action .= '<a data-toggle="modal" data-target="#myModal"
                                                               title="' . lang('clone') . ' ' . lang('invoice') . '"
                                                               href="' . base_url() . 'admin/invoice/clone_invoice/' . $v_invoices->invoices_id . '"
                                                               class="btn btn-xs btn-purple">
                                                                <i class="fa fa-copy"></i></a>' . ' ';
                        $action .= btn_edit('admin/invoice/createinvoice/create_invoice/' . $v_invoices->invoices_id) . ' ';
                    }
                    if (!empty($can_delete) && !empty($deleted)) {
                        $action .= ajax_anchor(base_url("admin/invoice/delete/delete_invoice/$v_invoices->invoices_id"), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $_key)) . ' ';
                    }
                    $change_status = null;
                    if (!empty($can_edit) && !empty($edited)) {
                        $ch_url = base_url() . 'admin/invoice/';
                        $change_status .= '<div class="btn-group">
        <button class="btn btn-xs btn-default dropdown-toggle"
                data-toggle="dropdown">
                    ' . lang('change') . '
            <span class="caret"></span></button>
        <ul class="dropdown-menu animated zoomIn">';
                        $change_status .= '<li><a href="' . $ch_url . 'manage_invoice/invoice_details/' . $v_invoices->invoices_id . '">' . lang('preview_invoice') . '</a></li>';
                        $change_status .= '<li><a href="' . $ch_url . 'manage_invoice/payment/' . $v_invoices->invoices_id . '">' . lang('pay_invoice') . '</a></li>';
                        $change_status .= '<li><a href="' . $ch_url . 'manage_invoice/email_invoice/' . $v_invoices->invoices_id . '">' . lang('email_invoice') . '</a></li>';
                        $change_status .= '<li><a href="' . $ch_url . 'manage_invoice/send_reminder/' . $v_invoices->invoices_id . '">' . lang('send_reminder') . '</a></li>';
                        $change_status .= '<li><a href="' . $ch_url . 'manage_invoice/send_overdue/' . $v_invoices->invoices_id . '">' . lang('send_invoice_overdue') . '</a></li>';
                        $change_status .= '<li><a href="' . $ch_url . 'manage_invoice/invoice_history/' . $v_invoices->invoices_id . '">' . lang('invoice_history') . '</a></li>';
                        $change_status .= '<li><a href="' . $ch_url . 'pdf_invoice/' . $v_invoices->invoices_id . '">' . lang('pdf') . '</a></li>';
                        $change_status .= '</ul></div>';
                        $action .= $change_status;
                    }

                    $sub_array[] = $action;
                    $data[] = $sub_array;
                }
            }
            render_table($data, $where);
        } else {
            redirect('admin/dashboard');
        }
    }

    public function recurringinvoiceList()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->table = 'tbl_invoices';
            $this->datatables->join_table = array('tbl_client');
            $this->datatables->join_where = array('tbl_invoices.client_id=tbl_client.client_id');
            $this->datatables->select = 'tbl_invoices.*,tbl_client.name';
            $this->datatables->column_order = array('reference_no', 'tbl_client.name', 'invoice_date', 'due_date', 'status', 'tags');
            $this->datatables->column_search = array('reference_no', 'tbl_client.name', 'invoice_date', 'due_date', 'status', 'tags');
            $this->datatables->order = array('invoices_id' => 'desc');

            // get all invoice
            $fetch_data = $this->datatables->get_datatable_permission(array('recurring' => 'Yes'));

            $data = array();

            $edited = can_action('13', 'edited');
            $deleted = can_action('13', 'deleted');
            foreach ($fetch_data as $_key => $v_invoices) {
                if (!empty($v_invoices)) {
                    $action = null;

                    $can_edit = $this->invoice_model->can_action('tbl_invoices', 'edit', array('invoices_id' => $v_invoices->invoices_id));
                    $can_delete = $this->invoice_model->can_action('tbl_invoices', 'delete', array('invoices_id' => $v_invoices->invoices_id));
                    if ($this->invoice_model->get_payment_status($v_invoices->invoices_id) == lang('fully_paid')) {
                        $invoice_status = lang('fully_paid');
                        $label = "success";
                    } elseif ($this->invoice_model->get_payment_status($v_invoices->invoices_id) == lang('draft')) {
                        $invoice_status = lang('draft');
                        $label = "default";
                    } elseif ($this->invoice_model->get_payment_status($v_invoices->invoices_id) == lang('partially_paid')) {
                        $invoice_status = lang('partially_paid');
                        $label = "warning";
                    } elseif ($v_invoices->emailed == 'Yes') {
                        $invoice_status = lang('sent');
                        $label = "info";
                    } else {
                        $invoice_status = $this->invoice_model->get_payment_status($v_invoices->invoices_id);
                        $label = "danger";
                    }

                    $sub_array = array();
                    $name = null;
                    $name .= '<a class="text-info" href="' . base_url() . 'admin/invoice/manage_invoice/invoice_details/' . $v_invoices->invoices_id . '">' . $v_invoices->reference_no . '</a>';

                    $sub_array[] = $name;
                    $payment_status = $this->invoice_model->get_payment_status($v_invoices->invoices_id);
                    $overdue = null;
                    if (strtotime($v_invoices->due_date) < strtotime(date('Y-m-d')) && $payment_status != lang('fully_paid') && $payment_status != lang('cancelled')) {
                        $overdue .= '<span class="label label-danger ">' . lang("overdue") . '</span>';
                    }
                    $sub_array[] = strftime(config_item('date_format'), strtotime($v_invoices->due_date)) . ' ' . $overdue;
                    $sub_array[] = '<span class="tags">' . client_name($v_invoices->client_id) . '</span>';
                    $sub_array[] = display_money($this->invoice_model->calculate_to('invoice_due', $v_invoices->invoices_id), client_currency($v_invoices->client_id));
                    $recurring = null;
                    if ($v_invoices->recurring == 'Yes') {
                        $recurring = '<span data-toggle="tooltip" data-placement="top"
                                                              title="' . lang("recurring") . '"
                                                              class="label label-primary"><i
                                                                class="fa fa-retweet"></i></span>';
                    }
                    $sub_array[] = "<span class='label label-" . $label . "'>" . $invoice_status . "</span>" . ' ' . $recurring;
                    $sub_array[] = get_tags($v_invoices->tags, true);
                    $custom_form_table = custom_form_table(9, $v_invoices->invoices_id);

                    if (!empty($custom_form_table)) {
                        foreach ($custom_form_table as $c_label => $v_fields) {
                            $sub_array[] = $v_fields;
                        }
                    }
                    if (!empty($can_edit) && !empty($edited)) {
                        $action .= '<a data-toggle="modal" data-target="#myModal"
                                                               title="' . lang('clone') . ' ' . lang('invoice') . '"
                                                               href="' . base_url() . 'admin/invoice/clone_invoice/' . $v_invoices->invoices_id . '"
                                                               class="btn btn-xs btn-purple">
                                                                <i class="fa fa-copy"></i></a>' . ' ';
                        $action .= btn_edit('admin/invoice/createinvoice/create_invoice/' . $v_invoices->invoices_id) . ' ';
                    }
                    if (!empty($can_delete) && !empty($deleted)) {
                        $action .= ajax_anchor(base_url("admin/invoice/delete/delete_invoice/$v_invoices->invoices_id"), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $_key)) . ' ';
                    }
                    $change_status = null;
                    if (!empty($can_edit) && !empty($edited)) {
                        $ch_url = base_url() . 'admin/invoice/';
                        $change_status .= '<div class="btn-group">
        <button class="btn btn-xs btn-default dropdown-toggle"
                data-toggle="dropdown">
                    ' . lang('change') . '
            <span class="caret"></span></button>
        <ul class="dropdown-menu animated zoomIn">';
                        $change_status .= '<li><a href="' . $ch_url . 'manage_invoice/invoice_details/' . $v_invoices->invoices_id . '">' . lang('preview_invoice') . '</a></li>';
                        $change_status .= '<li><a href="' . $ch_url . 'manage_invoice/payment/' . $v_invoices->invoices_id . '">' . lang('pay_invoice') . '</a></li>';
                        $change_status .= '<li><a href="' . $ch_url . 'manage_invoice/email_invoice/' . $v_invoices->invoices_id . '">' . lang('email_invoice') . '</a></li>';
                        $change_status .= '<li><a href="' . $ch_url . 'manage_invoice/send_reminder/' . $v_invoices->invoices_id . '">' . lang('send_reminder') . '</a></li>';
                        $change_status .= '<li><a href="' . $ch_url . 'manage_invoice/send_overdue/' . $v_invoices->invoices_id . '">' . lang('send_invoice_overdue') . '</a></li>';
                        $change_status .= '<li><a href="' . $ch_url . 'manage_invoice/invoice_history/' . $v_invoices->invoices_id . '">' . lang('invoice_history') . '</a></li>';
                        $change_status .= '<li><a href="' . $ch_url . 'pdf_invoice/' . $v_invoices->invoices_id . '">' . lang('pdf') . '</a></li>';
                        $change_status .= '</ul></div>';
                        $action .= $change_status;
                    }

                    $sub_array[] = $action;
                    $data[] = $sub_array;
                }
            }
            render_table($data, array('recurring' => 'Yes'));
        } else {
            redirect('admin/dashboard');
        }
    }

    public function paymentList($filterBy = null, $search_by = null)
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->table = 'tbl_payments';
            $this->datatables->join_table = array('tbl_invoices', 'tbl_client', 'tbl_payment_methods');
            $this->datatables->join_where = array('tbl_payments.invoices_id=tbl_invoices.invoices_id', 'tbl_payments.paid_by=tbl_client.client_id', 'tbl_payment_methods.payment_methods_id=tbl_payments.payment_method');
            $this->datatables->column_order = array('tbl_payments.payment_date', 'tbl_invoices.invoice_date', 'tbl_invoices.reference_no', 'tbl_client.name', 'amount', 'tbl_invoices.date_saved', 'tbl_payment_methods.method_name');
            $this->datatables->column_search = array('tbl_payments.payment_date', 'tbl_invoices.invoice_date', 'tbl_invoices.reference_no', 'tbl_client.name', 'amount', 'tbl_invoices.date_saved', 'tbl_payment_methods.method_name');
            $this->datatables->order = array('payments_id' => 'desc');

            $where = array();
            if (!empty($search_by)) {
                if ($search_by == 'by_invoice') {
                    $where = array('tbl_payments.invoices_id' => $filterBy);
                }
                if ($search_by == 'by_account') {
                    $where = array('account_id' => $filterBy);
                }
                if ($search_by == 'by_client') {
                    $where = array('tbl_payments.paid_by' => $filterBy);
                }
            } else {
                if ($filterBy == 'last_month' || $filterBy == 'this_months') {
                    if ($filterBy == 'last_month') {
                        $month = date('m', strtotime('-1 months'));
                        $year = date('Y', strtotime('-1 months'));
                    } else {
                        $month = date('m');
                        $year = date('Y');
                    }
                    $where = array('year_paid' => $year, 'month_paid' => $month);
                } else if ($filterBy == 'today') {
                    $where = array('UNIX_TIMESTAMP(payment_date)' => strtotime(date('Y-m-d')));
                } else if (strstr($filterBy, '_')) {
                    $year = str_replace('_', '', $filterBy);
                    $where = array('year_paid' => $year);
                }
            }

            // get all invoice
            $fetch_data = $this->datatables->get_payment($filterBy, $search_by);
            $data = array();

            $edited = can_action('13', 'edited');
            $deleted = can_action('13', 'deleted');
            foreach ($fetch_data as $_key => $v_payments_info) {
                if (!empty($v_payments_info)) {
                    if (!empty($v_payments_info->system_currency)) {
                        $currency = $this->invoice_model->check_by(array('code' => $v_payments_info->system_currency), 'tbl_currencies');
                    } else {
                        $currency = $this->invoice_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');
                    }
                    $action = null;
                    $v_invoices = get_row('tbl_invoices', array('invoices_id' => $v_payments_info->invoices_id));
                    if (empty($v_invoices)) {
                        $v_invoices = new stdClass();
                        $v_invoices->client_id = 0;
                        $v_invoices->date_saved = 0;
                        $v_invoices->invoices_id = 0;
                        $v_invoices->reference_no = '-';
                    }
                    $can_edit = $this->invoice_model->can_action('tbl_invoices', 'edit', array('invoices_id' => $v_invoices->invoices_id));
                    $can_delete = $this->invoice_model->can_action('tbl_invoices', 'delete', array('invoices_id' => $v_invoices->invoices_id));
                    if (is_numeric($v_payments_info->payment_method)) {
                        $v_payments_info->method_name = get_any_field('tbl_payment_methods', array('payment_methods_id' => $v_payments_info->payment_method), 'method_name');
                    } else {
                        $v_payments_info->method_name = $v_payments_info->payment_method;
                    }
                    $sub_array = array();
                    $name = null;
                    $name .= '<a class="text-info" href="' . base_url() . 'admin/invoice/manage_invoice/payments_details/' . $v_payments_info->payments_id . '">' . strftime(config_item('date_format'), strtotime($v_payments_info->payment_date)) . '</a>';

                    $sub_array[] = $name;
                    $sub_array[] = strftime(config_item('date_format'), strtotime($v_invoices->invoice_date));
                    $sub_array[] = '<a class="text-info" href="' . base_url() . 'admin/invoice/manage_invoice/invoice_details/' . $v_invoices->invoices_id . '">' . $v_invoices->reference_no . '</a>';
                    $sub_array[] = client_name($v_invoices->client_id);
                    $sub_array[] = display_money($v_payments_info->amount, $currency->symbol);
                    $sub_array[] = !empty($v_payments_info->method_name) ? $v_payments_info->method_name : '-';

                    if (!empty($can_edit) && !empty($edited)) {
                        $action .= btn_edit('admin/invoice/all_payments/' . $v_payments_info->payments_id) . ' ';
                    }
                    if (!empty($can_delete) && !empty($deleted)) {
                        $action .= ajax_anchor(base_url("admin/invoice/delete/delete_payment/$v_payments_info->payments_id"), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $_key)) . ' ';
                    }
                    $action .= btn_view('admin/invoice/manage_invoice/payments_details/' . $v_payments_info->payments_id) . ' ';
                    $action .= '<a class="btn btn-xs btn-success" data-toggle="tooltip" data-placement="top" title="' . lang('send_email') . '" href="' . base_url() . 'admin/invoice/send_payment/' . $v_payments_info->payments_id . '/' . $v_payments_info->amount . '"><i class="fa fa-envelope"></i></a>' . ' ';

                    $sub_array[] = $action;
                    $data[] = $sub_array;
                }
            }

            render_table($data, $where);
        } else {
            redirect('admin/dashboard');
        }
    }

    public
    function make_payment()
    {
        $edited = can_action('13', 'edited');
        if (!empty($edited)) {
            $data['all_invoices'] = $this->invoice_model->get_client_wise_invoice();
            $data['modal_subview'] = $this->load->view('admin/invoice/make_payment', $data, FALSE);
            $this->load->view('admin/_layout_modal', $data);
        } else {
            set_message('error', lang('there_in_no_value'));
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/invoice/all_payments');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    public
    function client_change_data($customer_id, $current_invoice = 'undefined')
    {
        if ($this->input->is_ajax_request()) {
            $data = array();
            $data['client_currency'] = $this->invoice_model->client_currency_symbol($customer_id);
            $_data['invoices_to_merge'] = $this->invoice_model->check_for_merge_invoice($customer_id, $current_invoice);
            $data['merge_info'] = $this->load->view('admin/invoice/merge_invoice', $_data, true);
            echo json_encode($data);
            exit();
        }
    }

    public
    function get_merge_data($id)
    {
        $invoice_items = $this->invoice_model->ordered_items_by_id($id);
        $i = 0;
        foreach ($invoice_items as $item) {
            $invoice_items[$i]->taxname = $this->invoice_model->get_invoice_item_taxes($item->items_id);
            //            $invoice_items[$i]->new_itmes_id = $item->saved_items_id;
            $invoice_items[$i]->qty = $item->quantity;
            $invoice_items[$i]->rate = $item->unit_cost;
            $i++;
        }
        echo json_encode($invoice_items);
        exit();
    }

    public
    function payments_pdf($id)
    {
        $data['title'] = "Payments PDF"; //Page title
        // get payment info by id
        $this->invoice_model->_table_name = 'tbl_payments';
        $this->invoice_model->_order_by = 'payments_id';
        $data['payments_info'] = $this->invoice_model->get_by(array('payments_id' => $id), TRUE);
        $this->load->helper('dompdf');
        // $viewfile = $this->load->view('admin/invoice/payments_pdf', $data, TRUE);
        $viewfile = $this->load->view('admin/invoice/payments_pdfNew', $data, TRUE); // 2024-12-03
        pdf_create($viewfile, slug_it('Payment  # ' . $data['payments_info']->trans_id));
    }

    public
    function pdf_invoice($id)
    {
        $this->invoice_details($id, true);
    }

    public
    function project_invoice($id)
    {
        $data['title'] = lang('project') . ' ' . lang('invoice'); //Page title
        $data['active'] = 2;
        $data['project_id'] = $id;
        $data['project_info'] = $this->invoice_model->check_by(array('project_id' => $id), 'tbl_project');
        // get all client
        $this->invoice_model->_table_name = 'tbl_client';
        $this->invoice_model->_order_by = 'client_id';
        $data['all_client'] = $this->invoice_model->get();
        // get permission user
        $data['permission_user'] = $this->invoice_model->all_permission_user('13');
        // get all invoice
        $data['all_invoices_info'] = $this->invoice_model->get_permission('tbl_invoices');
        $data['subview'] = $this->load->view('admin/invoice/manage_invoice', $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public
    function all_payments($id = NULL)
    {
        if (!empty($id)) {
            $can_edit = $this->invoice_model->can_action('tbl_invoices', 'edit', array('invoices_id' => $id));
            if (!empty($can_edit)) {
                $payments_info = $this->invoice_model->check_by(array('payments_id' => $id), 'tbl_payments');
                $data['invoice_info'] = $this->invoice_model->check_by(array('invoices_id' => $payments_info->invoices_id), 'tbl_invoices');
            }
            $data['title'] = "Edit Payments"; //Page title
            $subview = 'edit_payments';
        } else {
            $data['title'] = "All Payments"; //Page title
            $subview = 'all_payments';
        }
        $data['all_invoice_info'] = $this->invoice_model->get_permission('tbl_invoices');

        // get payment info by id

        if (!empty($id)) {
            $can_edit = $this->invoice_model->can_action('tbl_payments', 'edit', array('payments_id' => $id));
            if (!empty($can_edit)) {
                $this->invoice_model->_table_name = 'tbl_payments';
                $this->invoice_model->_order_by = 'payments_id';
                $data['payments_info'] = $this->invoice_model->get_by(array('payments_id' => $id), TRUE);
            } else {
                set_message('error', lang('no_permission_to_access'));
                if (empty($_SERVER['HTTP_REFERER'])) {
                    redirect('admin/invoice/all_payments');
                } else {
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
        }
        $data['subview'] = $this->load->view('admin/invoice/' . $subview, $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function payments_state_report()
    {
        $data = array();
        $pathonor_jonno['payments_state_report_div'] = $this->load->view("admin/invoice/payments_state_report", $data, true);
        echo json_encode($pathonor_jonno);
        exit;
    }

    public
    function save_invoice($id = NULL)
    {
        $created = can_action('13', 'created');
        $edited = can_action('13', 'edited');
        if (!empty($created) || !empty($edited) && !empty($id)) {
            $data = $this->invoice_model->array_from_post(array('reference_no', 'client_id', 'project_id', 'warehouse_id', 'discount_type', 'tags', 'discount_percent', 'user_id', 'adjustment', 'discount_total', 'show_quantity_as'));
            if (empty($data['discount_type'])) {
                $data['discount_type'] = null;
            }
            if (empty($data['project_id'])) {
                $data['project_id'] = 0;
            }
            if (empty($data['warehouse_id'])) {
                $data['warehouse_id'] = 0;
            }

            $all_payment = get_result('tbl_online_payment');
            foreach ($all_payment as $payment) {
                $allow_gateway = 'allow_' . slug_it(strtolower($payment->gateway_name));
                $gateway_status = slug_it(strtolower($payment->gateway_name)) . '_status';
                if (config_item($gateway_status) == 'active') {
                    $data[$allow_gateway] = ($this->input->post($allow_gateway) == 'Yes') ? 'Yes' : 'No';
                }
            }

            $data['client_visible'] = ($this->input->post('client_visible') == 'Yes') ? 'Yes' : 'No';
            $data['invoice_date'] = date('Y-m-d', strtotime($this->input->post('invoice_date', TRUE)));
            if (empty($data['invoice_date'])) {
                $data['invoice_date'] = date('Y-m-d');
            }
            $data['invoice_year'] = date('Y', strtotime($this->input->post('invoice_date', TRUE)));
            $data['invoice_month'] = date('Y-m', strtotime($this->input->post('invoice_date', TRUE)));
            $data['due_date'] = date('Y-m-d', strtotime($this->input->post('due_date', TRUE)));
            $pos = $this->input->post('pos', true);
            if (!empty($pos)) {
                $permission = 'everyone';
                $notes = 'Directly from purchase';
            } else {
                $permission = $this->input->post('permission', true);
                $notes = $this->input->post('notes');
            }
            $data['notes'] = $notes;
            $tax['tax_name'] = $this->input->post('total_tax_name', TRUE);
            $tax['total_tax'] = $this->input->post('total_tax', TRUE);
            $data['total_tax'] = json_encode($tax);
            $i_tax = 0;
            if (!empty($tax['total_tax'])) {
                foreach ($tax['total_tax'] as $v_tax) {
                    $i_tax += $v_tax;
                }
            }
            $data['tax'] = $i_tax;

            $save_as_draft = $this->input->post('save_as_draft', TRUE);
            if (!empty($save_as_draft)) {
                $data['status'] = 'draft';
            }
            $Paid = $this->input->post('Paid', TRUE);
            if (!empty($Paid)) {
                $data['status'] = 'Paid';
            }
            $currency = $this->invoice_model->client_currency_symbol($data['client_id']);
            if (!empty($currency->code)) {
                $curren = $currency->code;
            } else {
                $curren = config_item('default_currency');
            }
            $data['currency'] = $curren;
            if (!empty($permission)) {
                if ($permission == 'everyone') {
                    $assigned = 'all';
                } else {
                    $assigned_to = $this->invoice_model->array_from_post(array('assigned_to'));
                    if (!empty($assigned_to['assigned_to'])) {
                        foreach ($assigned_to['assigned_to'] as $assign_user) {
                            $assigned[$assign_user] = $this->input->post('action_' . $assign_user, true);
                        }
                    }
                }
                if (!empty($assigned)) {
                    if ($assigned != 'all') {
                        $assigned = json_encode($assigned);
                    }
                } else {
                    $assigned = 'all';
                }
                $data['permission'] = $assigned;
            } else {
                set_message('error', lang('assigned_to') . ' Field is required');
                if (empty($_SERVER['HTTP_REFERER'])) {
                    redirect('admin/invoice/manage_invoice');
                } else {
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
            // get all client
            $this->invoice_model->_table_name = 'tbl_invoices';
            $this->invoice_model->_primary_key = 'invoices_id';
            if (!empty($id)) {
                $invoice_id = $id;
                $can_edit = $this->invoice_model->can_action('tbl_invoices', 'edit', array('invoices_id' => $id));
                if (!empty($can_edit)) {
                    $this->invoice_model->save($data, $id);
                } else {
                    set_message('error', lang('there_in_no_value'));
                    redirect('admin/invoice/manage_invoice');
                }
                $action = ('activity_invoice_updated');
                $description = 'not_invoice_updated';
                $msg = lang('invoice_updated');
            } else {
                $invoice_id = $this->invoice_model->save($data);
                $action = ('activity_invoice_created');
                $description = 'not_invoice_created';
                $msg = lang('invoice_created');
            }
            save_custom_field(9, $invoice_id);
            $recuring_frequency = $this->input->post('recuring_frequency', TRUE);
            if (!empty($recuring_frequency) && $recuring_frequency != 'none') {
                $recur_data = $this->invoice_model->array_from_post(array('recur_start_date', 'recur_end_date'));
                $recur_data['recuring_frequency'] = $recuring_frequency;
                $this->get_recuring_frequency($invoice_id, $recur_data); // set recurring
            } else {
                $update_recur = array(
                    'recurring' => 'No',
                    'recur_end_date' => date('Y-m-d'),
                    'recur_next_date' => '0000-00-00'
                );
                $this->invoice_model->_table_name = 'tbl_invoices';
                $this->invoice_model->_primary_key = 'invoices_id';
                $this->invoice_model->save($update_recur, $invoice_id);
            }
            $qty_calculation = config_item('qty_calculation_from_items');
            // save items
            $invoices_to_merge = $this->input->post('invoices_to_merge', TRUE);
            $cancel_merged_invoices = $this->input->post('cancel_merged_invoices', TRUE);

            if (!empty($invoices_to_merge)) {
                foreach ($invoices_to_merge as $inv_id) {
                    if (empty($cancel_merged_invoices)) {
                        if (!empty($qty_calculation) && $qty_calculation == 'Yes') {
                            $all_items_info = $this->db->where('invoices_id', $inv_id)->get('tbl_items')->result();
                            if (!empty($all_items_info)) {
                                foreach ($all_items_info as $v_items) {
                                    $this->invoice_model->return_items($v_items->saved_items_id, $v_items->quantity, $data['warehouse_id']);
                                }
                            }
                        }
                        $this->db->where('invoices_id', $inv_id);
                        $this->db->delete('tbl_invoices');

                        $this->db->where('invoices_id', $inv_id);
                        $this->db->delete('tbl_items');
                    } else {
                        $mdata = array('status' => 'Cancelled');
                        $this->invoice_model->_table_name = 'tbl_invoices';
                        $this->invoice_model->_primary_key = 'invoices_id';
                        $this->invoice_model->save($mdata, $inv_id);
                    }
                }
            }

            $removed_items = $this->input->post('removed_items', TRUE);
            if (!empty($removed_items)) {
                foreach ($removed_items as $r_id) {
                    $itemInfo = get_row('tbl_items', array('items_id' => $r_id));
                    if ($r_id != 'undefined') {
                        if (!empty($qty_calculation) && $qty_calculation == 'Yes' && !empty($itemInfo->saved_items_id)) {
                            $this->invoice_model->return_items($itemInfo->saved_items_id, $itemInfo->quantity, $data['warehouse_id']);
                        }
                        $this->db->where('items_id', $r_id);
                        $this->db->delete('tbl_items');
                    }
                }
            }


            $itemsid = $this->input->post('items_id', TRUE);
            $items_data = $this->input->post('items', true);
            if (!empty($items_data)) {
                $index = 0;
                $total_price = 0;
                foreach ($items_data as $items) {
                    unset($items['invoice_items_id']);
                    unset($items['total_qty']);
                    $items['invoices_id'] = $invoice_id;
                    $tax = 0;
                    if (!empty($items['taxname'])) {
                        foreach ($items['taxname'] as $tax_name) {
                            $tax_rate = explode("|", $tax_name);
                            $tax += $tax_rate[1];
                        }
                        $items['item_tax_name'] = $items['taxname'];
                        unset($items['taxname']);
                        $items['item_tax_name'] = json_encode($items['item_tax_name']);
                    }
                    if (empty($items['saved_items_id']) || $items['saved_items_id'] == 'undefined') {
                        $items['saved_items_id'] = 0;
                    }

                    $price = $items['quantity'] * $items['unit_cost'];
                    $items['item_tax_total'] = ($price / 100 * $tax);
                    $items['total_cost'] = $price;


                    // get all client
                    $this->invoice_model->_table_name = 'tbl_items';
                    $this->invoice_model->_primary_key = 'items_id';
                    $data['warehouse_id'] = $this->input->post('warehouse_id', true);

                    if (!empty($items['items_id'])) {
                        $items_id = $items['items_id'];
                        if (!empty($qty_calculation) && $qty_calculation == 'Yes') {
                            $this->invoice_model->check_existing_qty($items_id, $items['quantity'], $data['warehouse_id']);
                        }
                        $this->invoice_model->save($items, $items_id);
                    } else {
                        if (!empty($qty_calculation) && $qty_calculation == 'Yes') {
                            if (!empty($items['saved_items_id']) && $items['saved_items_id'] != 'undefined') {
                                $this->invoice_model->reduce_items($items['saved_items_id'], $items['quantity'], $data['warehouse_id']);
                            }
                        }
                        $items_id = $this->invoice_model->save($items);
                    }
                    $thecount = $items['total_cost'];
                    $total_price += $thecount;
                    $index++;
                }
            }
            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'invoice',
                'module_field_id' => $invoice_id,
                'activity' => $action,
                'icon' => 'fa-shopping-cart',
                'link' => 'admin/invoice/manage_invoice/invoice_details/' . $invoice_id,
                'value1' => $data['reference_no']
            );
            $this->invoice_model->_table_name = 'tbl_activities';
            $this->invoice_model->_primary_key = 'activities_id';
            $this->invoice_model->save($activity);

            if (!empty($Paid)) {
                $this->get_payment($invoice_id, true);
            }
            // send notification to client
            if (!empty($data['client_id'])) {
                $client_info = $this->invoice_model->check_by(array('client_id' => $data['client_id']), 'tbl_client');
                if (!empty($client_info->primary_contact)) {
                    $notifyUser = array($client_info->primary_contact);
                } else {
                    $user_info = $this->invoice_model->check_by(array('company' => $data['client_id']), 'tbl_account_details');
                    if (!empty($user_info)) {
                        $notifyUser = array($user_info->user_id);
                    }
                }
            }
            if (!empty($notifyUser)) {
                foreach ($notifyUser as $v_user) {
                    if ($v_user != $this->session->userdata('user_id')) {
                        add_notification(array(
                            'to_user_id' => $v_user,
                            'icon' => 'shopping-cart',
                            'description' => $description,
                            'link' => 'client/invoice/manage_invoice/invoice_details/' . $invoice_id,
                            'value' => $data['reference_no'],
                        ));
                    }
                }
                show_notification($notifyUser);
            }
            // messages for user
            $type = "success";
            $message = $msg;
            set_message($type, $message);
            redirect('admin/invoice/manage_invoice/invoice_details/' . $invoice_id);
        } else {
            redirect('admin/invoice/manage_invoice');
        }
    }

    function get_recuring_frequency($invoices_id, $recur_data)
    {
        $recur_days = $this->get_calculate_recurring_days($recur_data['recuring_frequency']);
        $due_date = $this->invoice_model->get_table_field('tbl_invoices', array('invoices_id' => $invoices_id), 'due_date');

        $next_date = date("Y-m-d", strtotime($due_date . "+ " . $recur_days . " days"));

        if ($recur_data['recur_end_date'] == '') {
            $recur_end_date = '0000-00-00';
        } else {
            $recur_end_date = date('Y-m-d', strtotime($recur_data['recur_end_date']));
        }
        $update_invoice = array(
            'recurring' => 'Yes',
            'recuring_frequency' => $recur_days,
            'recur_frequency' => $recur_data['recuring_frequency'],
            'recur_start_date' => date('Y-m-d', strtotime($recur_data['recur_start_date'])),
            'recur_end_date' => $recur_end_date,
            'recur_next_date' => $next_date
        );
        $this->invoice_model->_table_name = 'tbl_invoices';
        $this->invoice_model->_primary_key = 'invoices_id';
        $this->invoice_model->save($update_invoice, $invoices_id);
        return TRUE;
    }

    function get_calculate_recurring_days($recuring_frequency)
    {
        switch ($recuring_frequency) {
            case '7D':
                return 7;
                break;
            case '1M':
                return 31;
                break;
            case '3M':
                return 90;
                break;
            case '6M':
                return 182;
                break;
            case '1Y':
                return 365;
                break;
        }
    }


    public
    function recurring_invoice($id = NULL)
    {
        $data['title'] = lang('recurring_invoice');
        if (!empty($id)) {
            $data['invoice_info'] = join_data('tbl_invoices', '*', array('invoices_id' => $id), array('tbl_client' => 'tbl_client.client_id = tbl_invoices.client_id'));
            $data['active'] = 2;
        } else {
            $data['active'] = 1;
        }
        // get all client
        $this->invoice_model->_table_name = 'tbl_client';
        $this->invoice_model->_order_by = 'client_id';
        $data['all_client'] = $this->invoice_model->get();
        // get permission user
        $data['permission_user'] = $this->invoice_model->all_permission_user('51');

        // get all invoice
        $data['all_invoices_info'] = $this->invoice_model->get_permission('tbl_invoices');

        $data['subview'] = $this->load->view('admin/invoice/recurring_invoice', $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function createrecuringinvoice($id = null)
    {
        $data['title'] = lang('recurring_invoice');
        if (!empty($id)) {
            $data['invoice_info'] = join_data('tbl_invoices', '*', array('invoices_id' => $id), array('tbl_client' => 'tbl_client.client_id = tbl_invoices.client_id'));
            $data['active'] = 2;
        } else {
            $data['active'] = 1;
        }
        // get all client
        $this->invoice_model->_table_name = 'tbl_client';
        $this->invoice_model->_order_by = 'client_id';
        $data['all_client'] = $this->invoice_model->get();
        // get permission user
        $data['permission_user'] = $this->invoice_model->all_permission_user('51');

        // get all invoice
        $data['all_invoices_info'] = $this->invoice_model->get_permission('tbl_invoices');

        $data['subview'] = $this->load->view('admin/invoice/createrecuringinvoice', $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public
    function stop_recurring($invoices_id)
    {
        $update_recur = array(
            'recurring' => 'No',
            'recur_end_date' => date('Y-m-d'),
            'recur_next_date' => '0000-00-00'
        );
        $this->invoice_model->_table_name = 'tbl_invoices';
        $this->invoice_model->_primary_key = 'invoices_id';
        $this->invoice_model->save($update_recur, $invoices_id);
        // Log Activity
        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'invoice',
            'module_field_id' => $invoices_id,
            'activity' => 'activity_recurring_stopped',
            'icon' => 'fa-shopping-cart',
            'link' => 'admin/invoice/manage_invoice/invoice_details/' . $invoices_id,
        );
        $this->invoice_model->_table_name = 'tbl_activities';
        $this->invoice_model->_primary_key = 'activities_id';
        $this->invoice_model->save($activity);
        // messages for user
        $type = "success";
        $message = lang('recurring_invoice_stopped');
        set_message($type, $message);
        redirect('admin/invoice/manage_invoice');
    }

    public
    function insert_items($invoices_id)
    {
        $edited = can_action('13', 'edited');
        $can_edit = $this->invoice_model->can_action('tbl_invoices', 'edit', array('invoices_id' => $invoices_id));
        if (!empty($can_edit) && !empty($edited)) {
            $data['invoices_id'] = $invoices_id;
            $data['modal_subview'] = $this->load->view('admin/invoice/_modal_insert_items', $data, FALSE);
            $this->load->view('admin/_layout_modal', $data);
        } else {
            set_message('error', lang('there_in_no_value'));
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public
    function clone_invoice($invoices_id)
    {
        $edited = can_action('13', 'edited');
        $can_edit = $this->invoice_model->can_action('tbl_invoices', 'edit', array('invoices_id' => $invoices_id));
        if (!empty($can_edit) && !empty($edited)) {
            $data['invoice_info'] = $this->invoice_model->check_by(array('invoices_id' => $invoices_id), 'tbl_invoices');
            // get all client
            $this->invoice_model->_table_name = 'tbl_client';
            $this->invoice_model->_order_by = 'client_id';
            $data['all_client'] = $this->invoice_model->get();

            $data['modal_subview'] = $this->load->view('admin/invoice/_modal_clone_invoice', $data, FALSE);
            $this->load->view('admin/_layout_modal', $data);
        } else {
            set_message('error', lang('there_in_no_value'));
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/invoice/manage_invoice');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    public
    function cloned_invoice($id)
    {
        $edited = can_action('13', 'edited');
        $can_edit = $this->invoice_model->can_action('tbl_invoices', 'edit', array('invoices_id' => $id));
        if (!empty($can_edit) && !empty($edited)) {
            if (config_item('increment_invoice_number') == 'FALSE') {
                $this->load->helper('string');
                $reference_no = config_item('invoice_prefix') . ' ' . random_string('nozero', 6);
            } else {
                $reference_no = $this->invoice_model->generate_invoice_number();
            }
            $invoice_info = $this->invoice_model->check_by(array('invoices_id' => $id), 'tbl_invoices');

            // save into invoice table
            $new_invoice = array(
                'reference_no' => $reference_no,
                'recur_start_date' => $invoice_info->recur_start_date,
                'recur_end_date' => $invoice_info->recur_end_date,
                'client_id' => $this->input->post('client_id', true),
                'warehouse_id' => $invoice_info->warehouse_id,
                'project_id' => $invoice_info->project_id,
                'invoice_date' => $this->input->post('invoice_date', true),
                'invoice_year' => date('Y', strtotime($this->input->post('invoice_date', true))),
                'invoice_month' => date('Y-m', strtotime($this->input->post('invoice_date', true))),
                'due_date' => $this->input->post('due_date', true),
                'notes' => $invoice_info->notes,
                'tags' => $invoice_info->tags,
                'total_tax' => $invoice_info->total_tax,
                'tax' => $invoice_info->tax,
                'discount_type' => $invoice_info->discount_type,
                'discount_percent' => $invoice_info->discount_percent,
                'user_id' => $invoice_info->user_id,
                'adjustment' => $invoice_info->adjustment,
                'discount_total' => $invoice_info->discount_total,
                'show_quantity_as' => $invoice_info->show_quantity_as,
                'recurring' => $invoice_info->recurring,
                'recuring_frequency' => $invoice_info->recuring_frequency,
                'recur_frequency' => $invoice_info->recur_frequency,
                'recur_next_date' => $invoice_info->recur_next_date,
                'currency' => $invoice_info->currency,
                'status' => $invoice_info->status,
                'date_saved' => $invoice_info->date_saved,
                'emailed' => $invoice_info->emailed,
                'show_client' => $invoice_info->show_client,
                'viewed' => $invoice_info->viewed,
                'permission' => $invoice_info->permission,
            );
            $all_payment = get_result('tbl_online_payment');
            foreach ($all_payment as $payment) {
                $allow_gateway = 'allow_' . slug_it(strtolower($payment->gateway_name));
                $gateway_status = slug_it(strtolower($payment->gateway_name)) . '_status';
                if (config_item($gateway_status) == 'active') {
                    $new_invoice[$allow_gateway] = ($invoice_info->$allow_gateway == 'Yes') ? 'Yes' : 'No';
                }
            }

            $this->invoice_model->_table_name = "tbl_invoices"; //table name
            $this->invoice_model->_primary_key = "invoices_id";
            $new_invoice_id = $this->invoice_model->save($new_invoice);

            $invoice_items = $this->db->where('invoices_id', $id)->get('tbl_items')->result();
            if (!empty($invoice_items)) {
                foreach ($invoice_items as $new_item) {
                    $this->invoice_model->reduce_items($new_item->saved_items_id, $new_item->quantity, $invoice_info->warehouse_id);
                    $items = array(
                        'invoices_id' => $new_invoice_id,
                        'saved_items_id' => $new_item->saved_items_id,
                        'item_name' => $new_item->item_name,
                        'item_desc' => $new_item->item_desc,
                        'unit_cost' => $new_item->unit_cost,
                        'quantity' => $new_item->quantity,
                        'item_tax_rate' => $new_item->item_tax_rate,
                        'item_tax_name' => $new_item->item_tax_name,
                        'item_tax_total' => $new_item->item_tax_total,
                        'total_cost' => $new_item->total_cost,
                        'unit' => $new_item->unit,
                        'order' => $new_item->order,
                        'date_saved' => $new_item->date_saved,
                    );
                    $this->invoice_model->_table_name = "tbl_items"; //table name
                    $this->invoice_model->_primary_key = "items_id";
                    $this->invoice_model->save($items);
                }
            }
            // save into activities
            $activities = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'invoice',
                'module_field_id' => $new_invoice_id,
                'activity' => ('activity_cloned_invoice'),
                'icon' => 'fa-shopping-cart',
                'link' => 'admin/invoice/manage_invoice/invoice_details/' . $new_invoice_id,
                'value1' => ' from ' . $invoice_info->reference_no . ' to ' . $reference_no,
            );
            // Update into tbl_project
            $this->invoice_model->_table_name = "tbl_activities"; //table name
            $this->invoice_model->_primary_key = "activities_id";
            $this->invoice_model->save($activities);

            // messages for user
            $type = "success";
            $message = lang('invoice_created');
            set_message($type, $message);
            redirect('admin/invoice/manage_invoice/invoice_details/' . $new_invoice_id);
        } else {
            set_message('error', lang('there_in_no_value'));
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/invoice/manage_invoice');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    public
    function add_insert_items($invoices_id)
    {
        $edited = can_action('13', 'edited');
        $can_edit = $this->invoice_model->can_action('tbl_invoices', 'edit', array('invoices_id' => $invoices_id));
        if (!empty($can_edit) && !empty($edited)) {
            $saved_items_id = $this->input->post('saved_items_id', TRUE);
            if (!empty($saved_items_id)) {
                foreach ($saved_items_id as $v_items_id) {
                    $this->invoice_model->reduce_items($v_items_id, 1);
                    $items_info = $this->invoice_model->check_by(array('saved_items_id' => $v_items_id), 'tbl_saved_items');

                    $tax_info = json_decode($items_info->tax_rates_id);
                    $tax_name = array();
                    if (!empty($tax_info)) {
                        foreach ($tax_info as $v_tax) {
                            $all_tax = $this->db->where('tax_rates_id', $v_tax)->get('tbl_tax_rates')->row();
                            $tax_name[] = $all_tax->tax_rate_name . '|' . $all_tax->tax_rate_percent;
                        }
                    }
                    if (!empty($tax_name)) {
                        $tax_name = $tax_name;
                    } else {
                        $tax_name = array();
                    }
                    $data['quantity'] = 1;
                    $data['invoices_id'] = $invoices_id;
                    $data['item_name'] = $items_info->item_name;
                    $data['item_desc'] = $items_info->item_desc;
                    $data['hsn_code'] = $items_info->hsn_code;
                    $data['unit_cost'] = $items_info->unit_cost;
                    $data['item_tax_rate'] = '0.00';
                    $data['item_tax_name'] = json_encode($tax_name);
                    $data['item_tax_total'] = $items_info->item_tax_total;
                    $data['total_cost'] = $items_info->unit_cost;
                    // get all client
                    $this->invoice_model->_table_name = 'tbl_items';
                    $this->invoice_model->_primary_key = 'items_id';
                    $items_id = $this->invoice_model->save($data);

                    $action = ('activity_invoice_items_added');
                    $activity = array(
                        'user' => $this->session->userdata('user_id'),
                        'module' => 'invoice',
                        'module_field_id' => $items_id,
                        'activity' => $action,
                        'icon' => 'fa-circle-o',
                        'value1' => $items_info->item_name
                    );
                    $this->invoice_model->_table_name = 'tbl_activities';
                    $this->invoice_model->_primary_key = 'activities_id';
                    $this->invoice_model->save($activity);
                }

                $this->update_invoice_tax($saved_items_id, $invoices_id);

                $type = "success";
                $msg = lang('invoice_item_added');
            } else {
                $type = "error";
                $msg = 'please Select an items';
            }
            $message = $msg;
            set_message($type, $message);
            redirect('admin/invoice/manage_invoice/invoice_details/' . $invoices_id);
        } else {
            set_message('error', lang('there_in_no_value'));
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/invoice/manage_invoice');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    function update_invoice_tax($saved_items_id, $invoices_id)
    {

        $invoice_info = $this->invoice_model->check_by(array('invoices_id' => $invoices_id), 'tbl_invoices');
        $tax_info = json_decode($invoice_info->total_tax);

        $tax_name = $tax_info->tax_name;
        $total_tax = $tax_info->total_tax;
        $invoice_tax = array();
        if (!empty($tax_name)) {
            foreach ($tax_name as $t_key => $v_tax_info) {
                array_push($invoice_tax, array('tax_name' => $v_tax_info, 'total_tax' => $total_tax[$t_key]));
            }
        }
        $all_tax_info = array();
        if (!empty($saved_items_id)) {
            foreach ($saved_items_id as $v_items_id) {
                $items_info = $this->invoice_model->check_by(array('saved_items_id' => $v_items_id), 'tbl_saved_items');

                $tax_info = json_decode($items_info->tax_rates_id);
                if (!empty($tax_info)) {
                    foreach ($tax_info as $v_tax) {
                        $all_tax = $this->db->where('tax_rates_id', $v_tax)->get('tbl_tax_rates')->row();
                        array_push($all_tax_info, array('tax_name' => $all_tax->tax_rate_name . '|' . $all_tax->tax_rate_percent, 'total_tax' => $items_info->unit_cost / 100 * $all_tax->tax_rate_percent));
                    }
                }
            }
        }
        if (!empty($invoice_tax) && is_array($invoice_tax) && !empty($all_tax_info)) {
            $all_tax_info = array_merge($all_tax_info, $invoice_tax);
        }

        $results = array();
        foreach ($all_tax_info as $value) {
            if (!isset($results[$value['tax_name']])) {
                $results[$value['tax_name']] = 0;
            }
            $results[$value['tax_name']] += $value['total_tax'];
        }
        if (!empty($results)) {

            foreach ($results as $key => $value) {
                $structured_results['tax_name'][] = $key;
                $structured_results['total_tax'][] = $value;
            }
            $invoice_data['tax'] = array_sum($structured_results['total_tax']);
            $invoice_data['total_tax'] = json_encode($structured_results);

            $this->invoice_model->_table_name = 'tbl_invoices';
            $this->invoice_model->_primary_key = 'invoices_id';
            $this->invoice_model->save($invoice_data, $invoices_id);
        }
        return true;
    }

    public
    function add_item($id = NULL)
    {
        $edited = can_action('13', 'edited');
        $data = $this->invoice_model->array_from_post(array('invoices_id', 'item_order'));
        $can_edit = $this->invoice_model->can_action('tbl_invoices', 'edit', array('invoices_id' => $data['invoices_id']));
        if (!empty($can_edit) && !empty($edited)) {
            $quantity = $this->input->post('quantity', TRUE);
            $array_data = $this->invoice_model->array_from_post(array('item_name', 'item_desc', 'item_tax_rate', 'unit_cost'));
            if (!empty($quantity)) {
                foreach ($quantity as $key => $value) {
                    if (!empty($array_data['item_name'][$key])) {
                        $data['quantity'] = $value;
                        $data['item_name'] = $array_data['item_name'][$key];
                        $data['item_desc'] = $array_data['item_desc'][$key];
                        $data['unit_cost'] = $array_data['unit_cost'][$key];
                        $data['item_tax_rate'] = $array_data['item_tax_rate'][$key];
                        $sub_total = $data['unit_cost'] * $data['quantity'];

                        $data['item_tax_total'] = ($data['item_tax_rate'] / 100) * $sub_total;
                        $data['total_cost'] = $sub_total + $data['item_tax_total'];

                        // get all client
                        $this->invoice_model->_table_name = 'tbl_items';
                        $this->invoice_model->_primary_key = 'items_id';
                        if (!empty($id)) {
                            $items_id = $id;
                            $this->invoice_model->save($data, $id);
                            $action = ('activity_invoice_items_updated');
                            $msg = lang('invoice_item_updated');
                        } else {
                            $items_id = $this->invoice_model->save($data);
                            $action = ('activity_invoice_items_added');
                            $msg = lang('invoice_item_added');
                        }
                        $activity = array(
                            'user' => $this->session->userdata('user_id'),
                            'module' => 'invoice',
                            'module_field_id' => $items_id,
                            'activity' => $action,
                            'icon' => 'fa-circle-o',
                            'value1' => $data['item_name']
                        );
                        $this->invoice_model->_table_name = 'tbl_activities';
                        $this->invoice_model->_primary_key = 'activities_id';
                        $this->invoice_model->save($activity);
                    }
                }
            }
            $type = "success";
            $message = $msg;
            set_message($type, $message);
            redirect('admin/invoice/manage_invoice/invoice_details/' . $data['invoices_id']);
        } else {
            set_message('error', lang('there_in_no_value'));
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/invoice/manage_invoice');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    public
    function change_status($action, $id)
    {
        $edited = can_action('13', 'edited');
        $can_edit = $this->invoice_model->can_action('tbl_invoices', 'edit', array('invoices_id' => $id));
        if (!empty($can_edit) && !empty($edited)) {
            $where = array('invoices_id' => $id);
            if ($action == 'hide') {
                $data = array('show_client' => 'No');
            } else {
                $data = array('show_client' => 'Yes');
            }
            $this->invoice_model->set_action($where, $data, 'tbl_invoices');
            // messages for user
            $type = "success";
            $message = lang('invoice_status_changed', $action);
            set_message($type, $message);
            redirect('admin/invoice/manage_invoice/invoice_details/' . $id);
        } else {
            set_message('error', lang('there_in_no_value'));
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/invoice/manage_invoice');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    public
    function delete($action, $invoices_id, $item_id = NULL)
    {
        $deleted = can_action('13', 'deleted');
        $can_delete = $this->invoice_model->can_action('tbl_invoices', 'delete', array('invoices_id' => $invoices_id));
        if (!empty($can_delete) && !empty($deleted)) {
            $invoices_info = $this->invoice_model->check_by(array('invoices_id' => $invoices_id), 'tbl_invoices');
            if (!empty($invoices_info->reference_no)) {
                $val = $invoices_info->reference_no;
            } else {
                $val = NULL;
            }
            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'invoice',
                'module_field_id' => $invoices_id,
                'activity' => ('activity_invoice' . $action),
                'icon' => 'fa-shopping-cart',
                'value1' => $val,

            );
            $this->invoice_model->_table_name = 'tbl_activities';
            $this->invoice_model->_primary_key = 'activities_id';
            $this->invoice_model->save($activity);

            if ($action == 'delete_item') {
                $this->invoice_model->_table_name = 'tbl_items';
                $this->invoice_model->_primary_key = 'items_id';
                $this->invoice_model->delete($item_id);
            } elseif ($action == 'delete_invoice') {
                $this->invoice_model->_table_name = 'tbl_items';
                $this->invoice_model->delete_multiple(array('invoices_id' => $invoices_id));

                $this->invoice_model->_table_name = 'tbl_payments';
                $this->invoice_model->delete_multiple(array('invoices_id' => $invoices_id));

                $this->invoice_model->_table_name = 'tbl_reminders';
                $this->invoice_model->delete_multiple(array('module' => 'invoice', 'module_id' => $invoices_id));

                $this->invoice_model->_table_name = 'tbl_pinaction';
                $this->invoice_model->delete_multiple(array('module_name' => 'invoice', 'module_id' => $invoices_id));

                $this->invoice_model->_table_name = 'tbl_credit_used';
                $this->invoice_model->delete_multiple(array('invoices_id' => $invoices_id));

                $this->invoice_model->_table_name = 'tbl_invoices';
                $this->invoice_model->_primary_key = 'invoices_id';
                $this->invoice_model->delete($invoices_id);
            } elseif ($action == 'delete_payment') {
                $this->invoice_model->_table_name = 'tbl_payments';
                $this->invoice_model->_primary_key = 'payments_id';
                $this->invoice_model->delete($invoices_id);
            } elseif ($action == 'delete_applied_credits') {
                $credit_used = get_row('tbl_credit_used', array('credit_used_id' => $item_id));
                if (!empty($credit_used->payments_id) && $credit_used->payments_id != 0) {
                    $this->invoice_model->_table_name = 'tbl_payments';
                    $this->invoice_model->_primary_key = 'payments_id';
                    $this->invoice_model->delete($credit_used->payments_id);
                }
                $this->invoice_model->_table_name = 'tbl_credit_used';
                $this->invoice_model->_primary_key = 'credit_used_id';
                $this->invoice_model->delete($item_id);
            }
            $type = "success";


            if ($action == 'delete_item') {
                $text = lang('invoice_item_deleted');
                //                set_message($type, $text);
                //                redirect('admin/invoice/manage_invoice/invoice_details/' . $invoices_id);
            } elseif ($action == 'delete_payment') {
                $text = lang('payment_deleted');
                //                set_message($type, $text);
                //                redirect('admin/invoice/manage_invoice/all_payments');
            } else {
                $text = lang('deleted_invoice');
                //                set_message($type, $text);
                //                redirect('admin/invoice/manage_invoice');
            }
            echo json_encode(array("status" => $type, 'message' => $text));
            exit();
        } else {
            echo json_encode(array("status" => 'error', 'message' => lang('there_in_no_value')));
            exit();
            //            set_message('error', lang('there_in_no_value'));
            //            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public
    function get_payment($invoices_id, $pos = null)
    {
        $edited = can_action('13', 'edited');
        $can_edit = $this->invoice_model->can_action('tbl_invoices', 'edit', array('invoices_id' => $invoices_id));
        if (!empty($can_edit) && !empty($edited)) {
            $due = $this->invoice_model->calculate_to('invoice_due', $invoices_id);

            if (!empty($pos)) {
                $paid_amount = $due;
                $payment_date = date('Y-m-d H:i');
                $this->load->helper('string');
                $trans_id = random_string('nozero', 6);
                $notes = 'directly from POS';
                $save_into_account = 'on';
                // $send_thank_you = 'on';
                // $send_sms = 'on';
            } else {
                $paid_amount = $this->input->post('amount', TRUE);
                $payment_date = $this->input->post('payment_date', TRUE);
                $trans_id = $this->input->post('trans_id');
                $notes = $this->input->post('notes', true);
                $save_into_account = $this->input->post('save_into_account');
                $send_thank_you = $this->input->post('send_thank_you');
                $send_sms = $this->input->post('send_sms');
            }
            if ($paid_amount != 0) {
                if ($paid_amount > $due) {
                    // messages for user
                    $type = "error";
                    $message = lang('overpaid_amount');
                    set_message($type, $message);
                    redirect('admin/invoice/manage_invoice/payment/' . $invoices_id);
                } else {
                    $inv_info = $this->invoice_model->check_by(array('invoices_id' => $invoices_id), 'tbl_invoices');
                    $currency = $this->invoice_model->check_by(array('code' => $inv_info->currency), 'tbl_currencies');
                    $data = array(
                        'invoices_id' => $invoices_id,
                        'paid_by' => $inv_info->client_id,
                        'payment_method' => (!empty($pos) ? '-' : $this->input->post('payment_methods_id', TRUE)),
                        'currency' => (!empty($pos) ? $currency->symbol : $this->input->post('currency', TRUE)),
                        'amount' => $paid_amount,
                        'payment_date' => $payment_date,
                        'trans_id' => $trans_id,
                        'notes' => $notes,
                        'month_paid' => date("m", strtotime($payment_date)),
                        'year_paid' => date("Y", strtotime($payment_date)),
                    );

                    $this->invoice_model->_table_name = 'tbl_payments';
                    $this->invoice_model->_primary_key = 'payments_id';
                    $payments_id = $this->invoice_model->save($data);

                    if ($paid_amount < $due) {
                        $status = 'partially_paid';
                    }
                    if ($paid_amount == $due) {
                        $status = 'Paid';
                    }
                    $invoice_data['status'] = $status;
                    update('tbl_invoices', array('invoices_id' => $invoices_id), $invoice_data);


                    $this->invoice_model->_table_name = 'tbl_award_points';
                    $this->invoice_model->_primary_key = 'tbl_award_points_id ';

                    $client_award_point = (!empty(config_item('client_award_ponint')) ? config_item('client_award_ponint') : 0);
                    $client_purchese_price = (!empty(config_item('client_spent_amount')) ? config_item('client_spent_amount') : 0);
                    $user_sales_price = (!empty(config_item('staff_spent_amount')) ? config_item('staff_spent_amount') : 0);
                    $user_award_point = (!empty(config_item('staff_award_ponint')) ? config_item('staff_award_ponint') : 0);

                    $client_point = 0;
                    if ($client_award_point != 0 || $client_purchese_price != 0) {
                        if ($paid_amount >= $client_purchese_price) {
                            $client_point = floor(($paid_amount / $client_purchese_price) * $client_award_point);
                        }
                    }

                    $award_p['client_award_point'] = $client_point;
                    $staff_point = 0;
                    if ($client_award_point != 0 || $user_sales_price != 0) {
                        if ($paid_amount >= $user_sales_price) {
                            $staff_point = floor(($paid_amount / $user_sales_price) * $user_award_point);
                        }
                    }
                    $award_p['user_award_point'] = $staff_point;
                    $award_p['client_id'] = $inv_info->client_id;
                    $award_p['user_id'] = my_id();
                    $award_p['invoices_id'] = $invoices_id;
                    $award_p['payment_status'] = $status;
                    $award_p['date'] = date('Y-m-d');
                    $this->invoice_model->save($award_p);

                    $activity = array(
                        'user' => $this->session->userdata('user_id'),
                        'module' => 'invoice',
                        'module_field_id' => $invoices_id,
                        'activity' => ('activity_new_payment'),
                        'icon' => 'fa-shopping-cart',
                        'link' => 'admin/invoice/manage_invoice/invoice_details/' . $invoices_id,
                        'value1' => display_money($paid_amount, client_currency($inv_info->client_id)),
                        'value2' => $inv_info->reference_no,
                    );
                    $this->invoice_model->_table_name = 'tbl_activities';
                    $this->invoice_model->_primary_key = 'activities_id';
                    $this->invoice_model->save($activity);

                    if (!empty($inv_info->user_id)) {
                        $notifiedUsers = array($inv_info->user_id);
                        foreach ($notifiedUsers as $users) {
                            if ($users != $this->session->userdata('user_id')) {
                                add_notification(array(
                                    'to_user_id' => $users,
                                    'description' => 'not_new_invoice_payment',
                                    'icon' => 'shopping-cart',
                                    'link' => 'admin/invoice/manage_invoice/invoice_details/' . $invoices_id,
                                    'value' => lang('invoice') . ' ' . $inv_info->reference_no . ' ' . lang('amount') . display_money($paid_amount, $currency->symbol),
                                ));
                            }
                        }
                        show_notification($notifiedUsers);
                    }

                    if ($save_into_account == 'on') {
                        $account_id = $this->input->post('account_id', true);
                        if (empty($account_id)) {
                            $account_id = config_item('default_account');
                        }
                        if (!empty($account_id)) {
                            $reference = lang('invoice') . ' ' . lang('reference_no') . ": <a href='" . base_url('admin/invoice/manage_invoice/invoice_details/' . $inv_info->invoices_id) . "' >" . $inv_info->reference_no . "</a> and " . lang('trans_id') . ": <a href='" . base_url('admin/invoice/manage_invoice/payments_details/' . $payments_id) . "'>" . $this->input->post('trans_id', true) . "</a>";
                            $trans_id = $this->input->post('trans_id', true);
                            // save into tbl_transaction
                            $tr_data = array(
                                'name' => lang('invoice_payment', lang('trans_id') . '# ' . $trans_id),
                                'type' => 'Income',
                                'amount' => $paid_amount,
                                'credit' => $paid_amount,
                                'date' => date('Y-m-d', strtotime($this->input->post('payment_date', TRUE))),
                                'paid_by' => $inv_info->client_id,
                                'payment_methods_id' => $this->input->post('payment_methods_id', TRUE),
                                'reference' => $trans_id,
                                'notes' => lang('this_deposit_from_invoice_payment', $reference),
                                'permission' => 'all',
                            );


                            $account_info = $this->invoice_model->check_by(array('account_id' => $account_id), 'tbl_accounts');
                            if (!empty($account_info)) {
                                $ac_data['balance'] = $account_info->balance + $tr_data['amount'];
                                $this->invoice_model->_table_name = "tbl_accounts"; //table name
                                $this->invoice_model->_primary_key = "account_id";
                                $this->invoice_model->save($ac_data, $account_info->account_id);

                                $aaccount_info = $this->invoice_model->check_by(array('account_id' => $account_id), 'tbl_accounts');

                                $tr_data['total_balance'] = $aaccount_info->balance;
                                $tr_data['account_id'] = $account_id;

                                // save into tbl_transaction
                                $this->invoice_model->_table_name = "tbl_transactions"; //table name
                                $this->invoice_model->_primary_key = "transactions_id";
                                $return_id = $this->invoice_model->save($tr_data);

                                $deduct_account['account_id'] = $account_id;
                                $this->invoice_model->_table_name = 'tbl_payments';
                                $this->invoice_model->_primary_key = 'payments_id';
                                $this->invoice_model->save($deduct_account, $payments_id);

                                // save into activities
                                $activities = array(
                                    'user' => $this->session->userdata('user_id'),
                                    'module' => 'transactions',
                                    'module_field_id' => $return_id,
                                    'activity' => 'activity_new_deposit',
                                    'icon' => 'fa-building-o',
                                    'link' => 'admin/transactions/view_details/' . $return_id,
                                    'value1' => $account_info->account_name,
                                    'value2' => $paid_amount,
                                );
                                // Update into tbl_project
                                $this->invoice_model->_table_name = "tbl_activities"; //table name
                                $this->invoice_model->_primary_key = "activities_id";
                                $this->invoice_model->save($activities);
                            }
                        }
                    }

                    //award points calculation


                    if ($send_thank_you == 'on') {
                        $this->send_payment_email($invoices_id, $paid_amount); //send thank you email
                    }

                    if ($send_sms == 'on') {
                        $this->send_payment_sms($invoices_id, $payments_id); //send thank you email
                    }
                }
            }
            // messages for user
            $type = "success";
            $message = lang('generate_payment');
            set_message($type, $message);
            redirect('admin/invoice/manage_invoice/invoice_details/' . $invoices_id);
        } else {
            set_message('error', lang('there_in_no_value'));
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/invoice/all_payments');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    public
    function update_payemnt($payments_id)
    {
        $data = array(
            'amount' => $this->input->post('amount', TRUE),
            'payment_method' => $this->input->post('payment_methods_id', TRUE),
            'payment_date' => date('Y-m-d', strtotime($this->input->post('payment_date', TRUE))),
            'notes' => $this->input->post('notes', TRUE),
            'month_paid' => date("m", strtotime($this->input->post('payment_date', TRUE))),
            'year_paid' => date("Y", strtotime($this->input->post('payment_date', TRUE))),
        );
        $payments_info = $this->invoice_model->check_by(array('payments_id' => $payments_id), 'tbl_payments');
        if (empty($payments_info)) {
            $type = "error";
            $message = "No Record Found";
            set_message($type, $message);
            redirect('admin/invoice/all_payments');
        }
        if ($payments_info->amount != $data['amount']) {
            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'invoice',
                'module_field_id' => $payments_id,
                'activity' => ('activity_update_payment'),
                'icon' => 'fa-shopping-cart',
                'link' => 'admin/invoice/manage_invoice/payments_details/' . $payments_id,
                'value1' => $data['amount'],
                'value2' => $data['payment_date'],
            );
            $this->invoice_model->_table_name = 'tbl_activities';
            $this->invoice_model->_primary_key = 'activities_id';
            $this->invoice_model->save($activity);


            // send notification to client
            if (!empty($payments_info)) {
                $client_info = $this->invoice_model->check_by(array('client_id' => $payments_info->paid_by), 'tbl_client');
                if (!empty($client_info->primary_contact)) {
                    $notifyUser = array($client_info->primary_contact);
                } else {
                    $user_info = $this->invoice_model->check_by(array('company' => $client_info->client_id), 'tbl_account_details');
                    if (!empty($user_info)) {
                        $notifyUser = array($user_info->user_id);
                    }
                }
            }
            if (!empty($notifyUser)) {
                foreach ($notifyUser as $v_user) {
                    if ($v_user != $this->session->userdata('user_id')) {
                        add_notification(array(
                            'to_user_id' => $v_user,
                            'icon' => 'shopping-cart',
                            'description' => 'not_payment_update',
                            'link' => 'client/invoice/manage_invoice/payments_details/' . $payments_id,
                            'value' => lang('trans_id') . ' ' . $payments_info->trans_id . ' ' . lang('new') . ' ' . lang('amount') . ' ' . display_money($data['amount'], $payments_info->currency),
                        ));
                    }
                }
                show_notification($notifyUser);
            }
        }

        $this->invoice_model->_table_name = 'tbl_payments';
        $this->invoice_model->_primary_key = 'payments_id';
        $this->invoice_model->save($data, $payments_id);


        // messages for user
        $type = "success";
        $message = lang('generate_payment');
        set_message($type, $message);
        redirect('admin/invoice/all_payments');
    }

    public
    function send_payment($invoices_id, $paid_amount)
    {
        $this->send_payment_email($invoices_id, $paid_amount); //send email
        $type = "success";
        $message = lang('payment_information_send');
        set_message($type, $message);
        if (empty($_SERVER['HTTP_REFERER'])) {
            redirect('admin/invoice/all_payments');
        } else {
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public
    function change_invoice_status($action, $id)
    {
        if ($action == 'mark_as_sent') {
            $data = array('emailed' => 'Yes', 'date_sent' => date("Y-m-d H:i:s", time()), 'status' => 'Unpaid');
        }
        if ($action == 'mark_as_cancelled') {
            $data = array('status' => 'Cancelled');
        }
        if ($action == 'unmark_as_cancelled') {
            $data = array('status' => 'Unpaid');
        }
        $this->invoice_model->_table_name = 'tbl_invoices';
        $this->invoice_model->_primary_key = 'invoices_id';
        $this->invoice_model->save($data, $id);

        // messages for user
        $type = "success";
        $imessage = lang('invoice_update');
        set_message($type, $imessage);
        redirect('admin/invoice/manage_invoice/invoice_details/' . $id);
    }

    public
    function send_invoice_email($invoice_id, $row = null)
    {
        if (!empty($row)) {
            $invoice_info = $this->invoice_model->check_by(array('invoices_id' => $invoice_id), 'tbl_invoices');
            $client_info = $this->invoice_model->check_by(array('client_id' => $invoice_info->client_id), 'tbl_client');
            if (!empty($client_info)) {
                $client = $client_info->name;
                $currency = $this->invoice_model->client_currency_symbol($client_info->client_id);
            } else {
                $client = '-';
                $currency = $this->invoice_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');
            }

            $amount = $this->invoice_model->calculate_to('invoice_due', $invoice_info->invoices_id);
            $currency = $currency->code;
            $email_template = email_templates(array('email_group' => 'invoice_message'), $invoice_info->client_id);
            $message = $email_template->template_body;
            $ref = $invoice_info->reference_no;
            $subject = $email_template->subject;
            $due_date = $invoice_info->due_date;
        } else {
            $message = $this->input->post('message', TRUE);
            $ref = $this->input->post('ref', TRUE);
            $subject = $this->input->post('subject', TRUE);
            $client = $this->input->post('client_name', TRUE);
            $amount = $this->input->post('amount', true);
            $currency = $this->input->post('currency', TRUE);
            $due_date = $this->input->post('due_date', TRUE);
        }
        $client_name = str_replace("{CLIENT}", $client, $message);
        $Ref = str_replace("{REF}", $ref, $client_name);
        $Amount = str_replace("{AMOUNT}", $amount, $Ref);
        $Currency = str_replace("{CURRENCY}", $currency, $Amount);
        $Due_date = str_replace("{DUE_DATE}", $due_date, $Currency);
        $link = str_replace("{INVOICE_LINK}", base_url() . 'frontend/view_invoice/' . url_encode($invoice_id), $Due_date);
        $message = str_replace("{SITE_NAME}", config_item('company_name'), $link);

        $this->send_email_invoice($invoice_id, $message, $subject); // Email Invoice

        $data = array('status' => 'sent', 'emailed' => 'Yes', 'date_sent' => date("Y-m-d H:i:s", time()));

        $this->invoice_model->_table_name = 'tbl_invoices';
        $this->invoice_model->_primary_key = 'invoices_id';
        $this->invoice_model->save($data, $invoice_id);

        // Log Activity
        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'invoice',
            'module_field_id' => $invoice_id,
            'activity' => ('activity_invoice_sent'),
            'icon' => 'fa-shopping-cart',
            'link' => 'admin/invoice/manage_invoice/invoice_details/' . $invoice_id,
            'value1' => $ref,
            'value2' => $this->input->post('currency', TRUE) . ' ' . $this->input->post('amount'),
        );
        $this->invoice_model->_table_name = 'tbl_activities';
        $this->invoice_model->_primary_key = 'activities_id';
        $this->invoice_model->save($activity);
        // messages for user
        $type = "success";
        $imessage = lang('invoice_sent');
        set_message($type, $imessage);
        redirect('admin/invoice/manage_invoice/invoice_details/' . $invoice_id);
    }

    function send_email_invoice($invoice_id, $message, $subject)
    {
        $invoice_info = $this->invoice_model->check_by(array('invoices_id' => $invoice_id), 'tbl_invoices');
        $client_info = $this->invoice_model->check_by(array('client_id' => $invoice_info->client_id), 'tbl_client');

        $recipient = $client_info->email;

        $data['message'] = $message;

        $message = $this->load->view('email_template', $data, TRUE);
        $params = array(
            'recipient' => $recipient,
            'subject' => $subject,
            'message' => $message
        );
        $params['resourceed_file'] = 'uploads/' . slug_it(lang('invoice') . '_pdf_' . $invoice_info->reference_no) . '.pdf';
        $params['resourcement_url'] = base_url() . 'uploads/' . slug_it(lang('invoice') . '_pdf_' . $invoice_info->reference_no) . '.pdf';
        $this->attach_pdf($invoice_id);
        $this->invoice_model->send_email($params);

        $mobile = client_can_received_sms($invoice_info->client_id);
        if (!empty($mobile)) {
            $merge_fields = [];
            $merge_fields = array_merge($merge_fields, merge_invoice_template($invoice_id));
            $merge_fields = array_merge($merge_fields, merge_invoice_template($invoice_id, 'client', $invoice_info->client_id));
            $this->sms->send(SMS_INVOICE_REMINDER, $mobile, $merge_fields);
        }
        //Delete invoice in tmp folder
        if (is_file('uploads/' . slug_it(lang('invoice') . '_pdf_' . $invoice_info->reference_no) . '.pdf')) {
            unlink('uploads/' . slug_it(lang('invoice') . '_pdf_' . $invoice_info->reference_no) . '.pdf');
        }
        // send notification to client
        if (!empty($client_info->primary_contact)) {
            $notifyUser = array($client_info->primary_contact);
        } else {
            $user_info = $this->invoice_model->check_by(array('company' => $invoice_info->client_id), 'tbl_account_details');
            if (!empty($user_info)) {
                $notifyUser = array($user_info->user_id);
            }
        }
        if (!empty($notifyUser)) {
            foreach ($notifyUser as $v_user) {
                if ($v_user != $this->session->userdata('user_id')) {
                    add_notification(array(
                        'to_user_id' => $v_user,
                        'icon' => 'shopping-cart',
                        'description' => 'not_email_send_alert',
                        'link' => 'client/invoice/manage_invoice/invoice_details/' . $invoice_id,
                        'value' => lang('invoice') . ' ' . $invoice_info->reference_no,
                    ));
                }
            }
            show_notification($notifyUser);
        }
    }

    public
    function attach_pdf($id)
    {
        $this->invoice_details($id, 'attach');
    }

    function invoice_email($invoice_id)
    {
        $data['invoice_info'] = $this->invoice_model->check_by(array('invoices_id' => $invoice_id), 'tbl_invoices');
        $data['title'] = "Invoice PDF"; //Page title
        $message = $this->load->view('admin/invoice/invoice_pdf', $data, TRUE);

        $client_info = $this->invoice_model->check_by(array('client_id' => $data['invoice_info']->client_id), 'tbl_client');

        $recipient = $client_info->email;

        $data['message'] = $message;

        $message = $this->load->view('email_template', $data, TRUE);

        $params = array(
            'recipient' => $recipient,
            'subject' => '[ ' . config_item('company_name') . ' ]' . ' New Invoice' . ' ' . $data['invoice_info']->reference_no,
            'message' => $message
        );
        $params['resourceed_file'] = 'uploads/' . slug_it(lang('invoice') . '_' . $data['invoice_info']->reference_no) . '.pdf';
        $params['resourcement_url'] = base_url() . 'uploads/' . slug_it(lang('invoice') . '_' . $data['invoice_info']->reference_no) . '.pdf';

        $this->attach_pdf($invoice_id);

        $this->invoice_model->send_email($params);

        //Delete invoice in tmp folder
        if (is_file('uploads/' . slug_it(lang('invoice') . '_' . $data['invoice_info']->reference_no) . '.pdf')) {
            unlink('uploads/' . slug_it(lang('invoice') . '_' . $data['invoice_info']->reference_no) . '.pdf');
        }

        $data = array('emailed' => 'Yes', 'date_sent' => date("Y-m-d H:i:s", time()));

        $this->invoice_model->_table_name = 'tbl_invoices';
        $this->invoice_model->_primary_key = 'invoices_id';
        $invoice_id = $this->invoice_model->save($data, $invoice_id);

        $data['invoice_info'] = $this->invoice_model->check_by(array('invoices_id' => $invoice_id), 'tbl_invoices');
        // Log Activity
        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'invoice',
            'module_field_id' => $invoice_id,
            'activity' => ('activity_invoice_sent'),
            'icon' => 'fa-shopping-cart',
            'link' => 'admin/invoice/manage_invoice/invoice_details/' . $invoice_id,
            'value1' => $data['invoice_info']->reference_no,
        );

        $this->invoice_model->_table_name = 'tbl_activities';
        $this->invoice_model->_primary_key = 'activities_id';
        $this->invoice_model->save($activity);

        // send notification to client
        if (!empty($client_info->primary_contact)) {
            $notifyUser = array($client_info->primary_contact);
        } else {
            $user_info = $this->invoice_model->check_by(array('company' => $data['invoice_info']->client_id), 'tbl_account_details');
            if (!empty($user_info)) {
                $notifyUser = array($user_info->user_id);
            }
        }
        if (!empty($notifyUser)) {
            foreach ($notifyUser as $v_user) {
                if ($v_user != $this->session->userdata('user_id')) {
                    add_notification(array(
                        'to_user_id' => $v_user,
                        'icon' => 'shopping-cart',
                        'description' => 'not_email_send_alert',
                        'link' => 'client/invoice/manage_invoice/invoice_details/' . $invoice_id,
                        'value' => lang('invoice') . ' ' . $data['invoice_info']->reference_no,
                    ));
                }
            }
            show_notification($notifyUser);
        }
        // messages for user
        $type = "success";
        $imessage = lang('invoice_sent');
        set_message($type, $imessage);
        redirect('admin/invoice/manage_invoice/invoice_details/' . $invoice_id);
    }

    public
    function tax_rates($action = NULL, $id = NULL)
    {
        $edited = can_action('16', 'edited');
        $deleted = can_action('16', 'deleted');
        $data['page'] = lang('sales');
        $data['sub_active'] = lang('tax_rates');
        if ($action == 'edit_tax_rates') {
            $data['active'] = 2;
            if (!empty($id)) {
                $can_edit = $this->invoice_model->can_action('tbl_tax_rates', 'edit', array('tax_rates_id' => $id));
                if (!empty($can_edit) && !empty($edited)) {
                    $data['tax_rates_info'] = $this->invoice_model->check_by(array('tax_rates_id' => $id), 'tbl_tax_rates');
                }
            }
        } else {
            $data['active'] = 1;
        }
        if ($action == 'delete_tax_rates') {
            $can_delete = $this->invoice_model->can_action('tbl_tax_rates', 'delete', array('tax_rates_id' => $id));
            if (!empty($can_delete) && !empty($deleted)) {
                $tax_rates_info = $this->invoice_model->check_by(array('tax_rates_id' => $id), 'tbl_tax_rates');
                // Log Activity
                $activity = array(
                    'user' => $this->session->userdata('user_id'),
                    'module' => 'invoice',
                    'module_field_id' => $id,
                    'activity' => ('activity_taxt_rate_deleted'),
                    'icon' => 'fa-shopping-cart',
                    'value1' => $tax_rates_info->tax_rate_name,
                );
                $this->invoice_model->_table_name = 'tbl_activities';
                $this->invoice_model->_primary_key = 'activities_id';
                $this->invoice_model->save($activity);

                $this->invoice_model->_table_name = 'tbl_tax_rates';
                $this->invoice_model->_primary_key = 'tax_rates_id';
                $this->invoice_model->delete($id);
                // messages for user
                $type = "success";
                $message = lang('tax_deleted');
            } else {
                $type = "error";
                $message = lang('there_in_no_value');
            }
            echo json_encode(array("status" => $type, 'message' => $message));
            exit();
        } else {
            $data['title'] = "Tax Rates Info"; //Page title
            $subview = 'tax_rates';
            // get permission user
            $data['permission_user'] = $this->invoice_model->all_permission_user('16');
            // get all invoice
            $data['all_tax_rates'] = $this->invoice_model->get_permission('tbl_tax_rates');

            $data['subview'] = $this->load->view('admin/invoice/' . $subview, $data, TRUE);
            $this->load->view('admin/_layout_main', $data); //page load
        }
    }

    function createtax_rates($action = NULL, $id = NULL)
    {
        $edited = can_action('16', 'edited');
        $deleted = can_action('16', 'deleted');
        $data['page'] = lang('sales');
        $data['sub_active'] = lang('tax_rates');
        if ($action == 'edit_tax_rates') {
            $data['active'] = 2;
            if (!empty($id)) {
                $can_edit = $this->invoice_model->can_action('tbl_tax_rates', 'edit', array('tax_rates_id' => $id));
                if (!empty($can_edit) && !empty($edited)) {
                    $data['tax_rates_info'] = $this->invoice_model->check_by(array('tax_rates_id' => $id), 'tbl_tax_rates');
                }
            }
        } else {
            $data['active'] = 1;
        }

        $data['title'] = "Tax Rates Info"; //Page title
        $subview = 'createtax_rates';
        // get permission user
        $data['permission_user'] = $this->invoice_model->all_permission_user('16');
        // get all invoice
        $data['all_tax_rates'] = $this->invoice_model->get_permission('tbl_tax_rates');

        $data['subview'] = $this->load->view('admin/invoice/' . $subview, $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load

    }

    public function taxList()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->table = 'tbl_tax_rates';
            $this->datatables->column_order = array('tax_rate_name', 'tax_rate_name');
            $this->datatables->column_search = array('tax_rate_name', 'tax_rate_name');
            $this->datatables->order = array('tax_rates_id' => 'desc');

            // get all invoice
            $fetch_data = $this->datatables->get_datatable_permission();

            $data = array();

            $edited = can_action('16', 'edited');
            $deleted = can_action('16', 'deleted');
            foreach ($fetch_data as $_key => $v_tax_rates) {

                $action = null;
                $can_delete = $this->invoice_model->can_action('tbl_tax_rates', 'delete', array('tax_rates_id' => $v_tax_rates->tax_rates_id));
                $can_edit = $this->invoice_model->can_action('tbl_tax_rates', 'edit', array('tax_rates_id' => $v_tax_rates->tax_rates_id));

                $sub_array = array();

                $sub_array[] = $v_tax_rates->tax_rate_name;
                $sub_array[] = $v_tax_rates->tax_rate_percent . '%';

                if (!empty($can_edit) && !empty($edited)) {
                    $action .= btn_edit('admin/invoice/createtax_rates/edit_tax_rates/' . $v_tax_rates->tax_rates_id) . ' ';
                }
                if (!empty($can_delete) && !empty($deleted)) {
                    $action .= ajax_anchor(base_url("admin/invoice/tax_rates/delete_tax_rates/$v_tax_rates->tax_rates_id"), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $_key)) . ' ';
                }

                $sub_array[] = $action;
                $data[] = $sub_array;
            }

            render_table($data);
        } else {
            redirect('admin/dashboard');
        }
    }

    public
    function save_tax_rate($id = NULL)
    {
        $data = $this->invoice_model->array_from_post(array('tax_rate_name', 'tax_rate_percent'));
        $permission = $this->input->post('permission', true);
        if (!empty($permission)) {
            if ($permission == 'everyone') {
                $assigned = 'all';
            } else {
                $assigned_to = $this->invoice_model->array_from_post(array('assigned_to'));
                if (!empty($assigned_to['assigned_to'])) {
                    foreach ($assigned_to['assigned_to'] as $assign_user) {
                        $assigned[$assign_user] = $this->input->post('action_' . $assign_user, true);
                    }
                }
            }
            if (!empty($assigned)) {
                if ($assigned != 'all') {
                    $assigned = json_encode($assigned);
                }
            } else {
                $assigned = 'all';
            }
            $data['permission'] = $assigned;
        } else {
            set_message('error', lang('assigned_to') . ' Field is required');
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/invoice/tax_rates');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }

        $this->invoice_model->_table_name = 'tbl_tax_rates';
        $this->invoice_model->_primary_key = 'tax_rates_id';
        $id = $this->invoice_model->save($data, $id);

        // Log Activity
        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'invoice',
            'module_field_id' => $id,
            'activity' => ('activity_taxt_rate_add'),
            'icon' => 'fa-shopping-cart',
            'value1' => $data['tax_rate_name'],
        );
        $this->invoice_model->_table_name = 'tbl_activities';
        $this->invoice_model->_primary_key = 'activities_id';
        $this->invoice_model->save($activity);

        // messages for user
        $type = "success";
        $message = lang('tax_added');
        set_message($type, $message);
        $save = $this->input->post('save', true);
        if ($save == 2) {
            redirect('admin/invoice/tax_rates/edit_tax_rates');
        } else {
            redirect('admin/invoice/tax_rates');
        }
    }

    public
    function zipped($module, $client_id = null, $id = null)
    {

        $this->load->helper('dompdf');
        if ($module == 'estimate') {
            $this->load->model('estimates_model');
        } elseif ($module == 'proposal') {
            $this->load->model('proposal_model');
        } elseif ($module == 'credit_note') {
            $this->load->model('credit_note_model');
        } elseif($module == 'purchase') {
            $this->load->model('purchase_model');
        }
        if ($this->input->post()) {
            if ($module == 'invoice') {
                $view = can_action('13', 'view');
                if (!$view) {
                    access_denied('Zip Invoices');
                }

                $status = $this->input->post('invoice_status');
                $ex = explode('_', $status);
                if (!empty($ex)) {
                    if (!empty($ex[1]) && is_numeric($ex[1])) {
                        $ex = 'year';
                    } else {
                        $ex = 'no';
                    }
                }

                $client_id = $this->input->post('client_id');
                if (!empty($client_id)) {
                    $client_info = $this->db->where('client_id', $client_id)->get('tbl_client')->row();
                    $file_name = slug_it($client_info->name);
                } else {
                    $file_name = slug_it($status);
                    $client_id = null;
                }

                if ($this->input->post('from_date') && $this->input->post('to_date') && $status != 'last_month' && $status != 'this_months' && $ex != 'year' && $status == '') {
                    $from_date = $this->input->post('from_date', true);
                    $to_date = $this->input->post('to_date', true);
                    if (!empty($client_id)) {
                        $this->db->where('client_id', $client_id);
                    }
                    $this->db->where('invoice_date BETWEEN "' . $from_date . '" AND "' . $to_date . '"');
                    $all_invoice = $this->db->get('tbl_invoices')->result();
                } else if ($this->input->post('from_date') && $this->input->post('to_date') && $status != 'last_month' && $status != 'this_months' && $ex != 'year' && $status != '') {
                    $from_date = $this->input->post('from_date', true);
                    $to_date = $this->input->post('to_date', true);
                    $all_invoice = $this->invoice_model->get_invoices_with_date($status, $from_date, $to_date);
                } else {
                    $from_date = null;
                    $to_date = null;
                    $all_invoice = $this->invoice_model->get_invoices($status, $client_id);
                }

                $this->load->helper('file');
                if (!is_really_writable(TEMP_FOLDER)) {
                    show_error('uploads folder is not writable. You need to change the permissions to 755');
                }
                $dir = TEMP_FOLDER . $file_name;

                if (is_dir($dir)) {
                    delete_dir($dir);
                }
                if (empty($all_invoice)) {
                    set_message('error', lang('no_record_available'));
                    if (!empty($client_id)) {
                        redirect('admin/client/details/' . $client_id . '/invoice');
                    } else {
                        redirect('admin/invoice/manage_invoice');
                    }
                }

                mkdir($dir, 0777);
                foreach ($all_invoice as $v_invoice) {
                    $data['invoice_info'] = $v_invoice;
                    $data['paid_amount'] = $this->invoice_model->calculate_to('paid_amount', $data['invoice_info']->invoices_id);
                    $data['invoice_due'] = $this->invoice_model->calculate_to('invoice_due', $data['invoice_info']->invoices_id);
                    $url = base_url('frontend/view_invoice/') . url_encode($v_invoice->invoices_id);
                    $data['qrcode'] = generate_qrcode($url);
                    $pdf_file = $this->load->view('admin/invoice/invoice_pdf', $data, TRUE);
                    $_temp_file_name = slug_it($data['invoice_info']->reference_no);
                    $file_name = $dir . strtoupper($_temp_file_name);
                    if (!empty($client_info->name)) {
                        $cl_name = slug_it($client_info->name);
                    } else {
                        $cl_name = slug_it($status);
                    }
                    pdf_create($pdf_file, slug_it($data['invoice_info']->reference_no), 1, null, true, $cl_name);
                }
            } else if ($module == 'estimate') {
                $view = can_action('14', 'view');
                if (!$view) {
                    access_denied('Zip Estimate');
                }

                $status = $this->input->post('invoice_status', true);
                $ex = explode('_', $status);
                if (!empty($ex)) {
                    if (!empty($ex[1]) && is_numeric($ex[1])) {
                        $ex = 'year';
                    } else {
                        $ex = 'no';
                    }
                }

                $client_id = $this->input->post('client_id', true);
                if (!empty($client_id)) {
                    $client_info = $this->db->where('client_id', $client_id)->get('tbl_client')->row();
                    $file_name = slug_it($client_info->name);
                } else {
                    $file_name = slug_it($status);
                    $client_id = null;
                }
                if ($this->input->post('from_date') && $this->input->post('to_date') && $status != 'last_month' && $status != 'this_months' && $ex != 'year' && $status == '') {
                    $from_date = $this->input->post('from_date', true);
                    $to_date = $this->input->post('to_date', true);
                    if (!empty($client_id)) {
                        $this->db->where('client_id', $client_id);
                    }
                    $this->db->where('estimate_date BETWEEN "' . $from_date . '" AND "' . $to_date . '"');
                    $all_estimate = $this->db->get('tbl_estimates')->result();
                } else if ($this->input->post('from_date') && $this->input->post('to_date') && $status != 'last_month' && $status != 'this_months' && $ex != 'year' && $status != '') {
                    $from_date = $this->input->post('from_date', true);
                    $to_date = $this->input->post('to_date', true);
                    $all_estimate = $this->estimates_model->get_invoices_with_date($status, $from_date, $to_date);
                } else {
                    $from_date = null;
                    $to_date = null;
                    $this->load->model('estimates_model');
                    $all_estimate = $this->estimates_model->get_estimates($status, $client_id);
                }

                $this->load->helper('file');
                if (!is_really_writable(TEMP_FOLDER)) {
                    show_error('uploads folder is not writable. You need to change the permissions to 755');
                }
                $dir = TEMP_FOLDER . $file_name;

                if (is_dir($dir)) {
                    delete_dir($dir);
                }
                if (empty($all_estimate)) {
                    set_message('error', lang('no_record_available'));
                    if (!empty($client_id)) {
                        redirect('admin/client/details/' . $client_id . '/estimate');
                    } else {
                        redirect('admin/estimates');
                    }
                }
                mkdir($dir, 0777);
                foreach ($all_estimate as $v_estimate) {
                    $data['estimates_info'] = $v_estimate;
                    $url = base_url('frontend/estimates/') . url_encode($v_estimate->estimates_id);
                    $data['qrcode'] = generate_qrcode($url);
                    $pdf_file = $this->load->view('admin/estimates/estimates_pdf', $data, TRUE);
                    $_temp_file_name = slug_it($data['estimates_info']->reference_no);
                    $file_name = $dir . strtoupper($_temp_file_name);
                    if (!empty($client_info->name)) {
                        $cl_name = slug_it($client_info->name);
                    } else {
                        $cl_name = slug_it($status);
                    }
                    pdf_create($pdf_file, slug_it($data['estimates_info']->reference_no), 1, null, true, $cl_name);
                }
            } else if ($module == 'credit_note') {
                $view = can_action('14', 'view');
                if (!$view) {
                    access_denied('Zip Credit Notes');
                }
                $status = $this->input->post('invoice_status', true);
                $ex = explode('_', $status);
                if (!empty($ex)) {
                    if (!empty($ex[1]) && is_numeric($ex[1])) {
                        $ex = 'year';
                    } else {
                        $ex = 'no';
                    }
                }

                $client_id = $this->input->post('client_id', true);
                if (!empty($client_id)) {
                    $client_info = $this->db->where('client_id', $client_id)->get('tbl_client')->row();
                    $file_name = slug_it($client_info->name);
                } else {
                    $file_name = slug_it($status);
                    $client_id = null;
                }
                if ($this->input->post('from_date') && $this->input->post('to_date') && $status != 'last_month' && $status != 'this_months' && $ex != 'year' && $status == '') {
                    $from_date = $this->input->post('from_date', true);
                    $to_date = $this->input->post('to_date', true);
                    if (!empty($client_id)) {
                        $this->db->where('client_id', $client_id);
                    }
                    $this->db->where('credit_note_date BETWEEN "' . $from_date . '" AND "' . $to_date . '"');
                    $all_credit_note = $this->db->get('tbl_credit_note')->result();
                } else if ($this->input->post('from_date') && $this->input->post('to_date') && $status != 'last_month' && $status != 'this_months' && $ex != 'year' && $status != '') {
                    $from_date = $this->input->post('from_date', true);
                    $to_date = $this->input->post('to_date', true);
                    $all_credit_note = $this->credit_note_model->get_credit_notes_with_date($status, $from_date, $to_date);
                } else {
                    $from_date = null;
                    $to_date = null;
                    $this->load->model('credit_note_model');
                    $all_credit_note = $this->credit_note_model->get_credit_notes($status, $client_id);
                }

                $this->load->helper('file');
                if (!is_really_writable(TEMP_FOLDER)) {
                    show_error('uploads folder is not writable. You need to change the permissions to 755');
                }
                $dir = TEMP_FOLDER . $file_name;

                if (is_dir($dir)) {
                    delete_dir($dir);
                }
                if (empty($all_credit_note)) {
                    set_message('error', lang('no_record_available'));
                    if (!empty($client_id)) {
                        redirect('admin/client/details/' . $client_id . '/credit_note');
                    } else {
                        redirect('admin/credit_note');
                    }
                }
                mkdir($dir, 0777);
                foreach ($all_credit_note as $v_credit_note) {
                    $data['credit_note_info'] = $v_credit_note;
                    $pdf_file = $this->load->view('admin/credit_note/credit_note_pdf', $data, TRUE);
                    $_temp_file_name = slug_it($data['credit_note_info']->reference_no);
                    $file_name = $dir . strtoupper($_temp_file_name);
                    if (!empty($client_info->name)) {
                        $cl_name = slug_it($client_info->name);
                    } else {
                        $cl_name = slug_it($status);
                    }
                    pdf_create($pdf_file, slug_it($data['credit_note_info']->reference_no), 1, null, true, $cl_name);
                }
            } else if ($module == 'proposal') {
                $view = can_action('140', 'view');
                if (!$view) {
                    access_denied('Zip Proposal');
                }

                $status = $this->input->post('invoice_status', true);
                $ex = explode('_', $status);
                if (!empty($ex)) {
                    if (!empty($ex[1]) && is_numeric($ex[1])) {
                        $ex = 'year';
                    } else {
                        $ex = 'no';
                    }
                }
                $client_id = $this->input->post('client_id', true);
                if (!empty($client_id)) {
                    $client_info = $this->db->where('client_id', $client_id)->get('tbl_client')->row();
                    $file_name = slug_it($client_info->name);
                } else {
                    $file_name = slug_it($status);
                    $client_id = null;
                }
                if ($this->input->post('from_date') && $this->input->post('to_date') && $status != 'last_month' && $status != 'this_months' && $ex != 'year') {
                    $from_date = $this->input->post('from_date', true);
                    $to_date = $this->input->post('to_date', true);
                    if (!empty($client_id)) {
                        $this->db->where('module', 'client');
                        $this->db->where('module_id', $client_id);
                    }
                    $this->db->where('proposal_date BETWEEN "' . $from_date . '" AND "' . $to_date . '"');
                    $all_proposal = $this->db->get('tbl_proposals')->result();
                } else {
                    $from_date = null;
                    $to_date = null;
                    $this->load->model('proposal_model');
                    $all_proposal = $this->proposal_model->get_proposals($status, $client_id);
                }

                $this->load->helper('file');
                if (!is_really_writable(TEMP_FOLDER)) {
                    show_error('uploads folder is not writable. You need to change the permissions to 755');
                }
                $dir = TEMP_FOLDER . $file_name;
                if (is_dir($dir)) {
                    delete_dir($dir);
                }
                if (empty($all_proposal)) {
                    set_message('error', lang('no_record_available'));
                    if (!empty($client_id)) {
                        redirect('admin/client/details/' . $client_id . '/proposal');
                    } else {
                        redirect('admin/proposals');
                    }
                }
                mkdir($dir, 0777);
                foreach ($all_proposal as $v_proposal) {
                    $data['proposals_info'] = $v_proposal;
                    $pdf_file = $this->load->view('admin/proposals/proposals_pdf', $data, TRUE);
                    $_temp_file_name = slug_it($data['proposals_info']->reference_no);
                    $file_name = $dir . strtoupper($_temp_file_name);
                    if (!empty($client_info->name)) {
                        $cl_name = slug_it($client_info->name);
                    } else {
                        $cl_name = slug_it($status);
                    }
                    pdf_create($pdf_file, slug_it($data['proposals_info']->reference_no), 1, null, true, $cl_name);
                }
            } else if ($module == 'payment') {
                $view = can_action('15', 'view');

                if (!$view) {
                    access_denied('Zip Payment');
                }

                $status = $this->input->post('invoice_status', true);
                $ex = explode('_', $status);
                if (!empty($ex)) {
                    if (!empty($ex[1]) && is_numeric($ex[1])) {
                        $ex = 'year';
                    } else {
                        $ex = 'no';
                    }
                }
                $client_id = $this->input->post('client_id', true);
                if (!empty($client_id)) {
                    $client_info = $this->db->where('client_id', $client_id)->get('tbl_client')->row();
                    $file_name = slug_it($client_info->name);
                } else {
                    $file_name = slug_it($status);
                    $client_id = null;
                }
                if ($this->input->post('from_date') && $this->input->post('to_date') && $status != 'last_month' && $status != 'this_months' && $ex != 'year') {
                    $from_date = $this->input->post('from_date', true);
                    $to_date = $this->input->post('to_date', true);
                    if (!empty($client_id)) {
                        $this->db->where('paid_by', $client_id);
                    }
                    $this->db->where('payment_date BETWEEN "' . $from_date . '" AND "' . $to_date . '"');
                    $all_payments = $this->db->get('tbl_payments')->result();
                } else {
                    $from_date = null;
                    $to_date = null;
                    $all_payments = $this->invoice_model->get_payments($status, $client_id);
                }
                $this->load->helper('file');
                if (!is_really_writable(TEMP_FOLDER)) {
                    show_error('uploads folder is not writable. You need to change the permissions to 755');
                }
                $dir = TEMP_FOLDER . $file_name;

                if (is_dir($dir)) {
                    delete_dir($dir);
                }
                if (empty($all_payments)) {
                    set_message('error', lang('no_record_available'));
                    if (!empty($client_id)) {
                        redirect('admin/client/details/' . $client_id . '/payment');
                    } else {
                        redirect('admin/invoice/all_payments');
                    }
                }
                mkdir($dir, 0777);
                foreach ($all_payments as $v_payment) {
                    $data['payments_info'] = $v_payment;
                    $pdf_file = $this->load->view('admin/invoice/payments_pdf', $data, TRUE);
                    $_temp_file_name = slug_it($data['payments_info']->trans_id);
                    $file_name = $dir . strtoupper($_temp_file_name);
                    if (!empty($client_info->name)) {
                        $cl_name = slug_it($client_info->name);
                    } else {
                        $cl_name = slug_it($status);
                    }
                    pdf_create($pdf_file, slug_it($data['payments_info']->trans_id), 1, null, true, $cl_name);
                }
            } elseif($module == 'purchase') {
                $view = can_action('15', 'view');
                if (!$view) {
                    access_denied('Zip Purchases');
                }

                $status = $this->input->post('invoice_status');
                $ex = explode('_', $status);
                if (!empty($ex)) {
                    if (!empty($ex[1]) && is_numeric($ex[1])) {
                        $ex = 'year';
                    } else {
                        $ex = 'no';
                    }
                }

                $file_name = slug_it($status);

                if ($this->input->post('from_date') && $this->input->post('to_date') && $status != 'last_month' && $status != 'this_months' && $ex != 'year' && $status == '') {
                    $from_date = $this->input->post('from_date', true);
                    $to_date = $this->input->post('to_date', true);
                    $this->db->where('purchase_date BETWEEN "' . $from_date . '" AND "' . $to_date . '"');
                    $all_invoice = $this->db->get('tbl_purchases')->result();
                } else if ($this->input->post('from_date') && $this->input->post('to_date') && $status != 'last_month' && $status != 'this_months' && $ex != 'year' && $status != '') {
                    $from_date = $this->input->post('from_date', true);
                    $to_date = $this->input->post('to_date', true);
                    $all_invoice = $this->purchase_model->get_invoices_with_date($status, $from_date, $to_date);
                } else {
                    $from_date = null;
                    $to_date = null;
                    $all_invoice = $this->purchase_model->get_invoices($status);
                }

                $this->load->helper('file');
                if (!is_really_writable(TEMP_FOLDER)) {
                    show_error('uploads folder is not writable. You need to change the permissions to 755');
                }
                $dir = TEMP_FOLDER . $file_name;

                if (is_dir($dir)) {
                    delete_dir($dir);
                }
                if (empty($all_invoice)) {
                    set_message('error', lang('no_record_available'));
                    redirect('admin/purchase');
                }

                mkdir($dir, 0777);
                foreach ($all_invoice as $v_invoice) {
                    $data['purchase_info'] = $v_invoice;
                    $pdf_file = $this->load->view('admin/purchase/purchase_pdf', $data, TRUE);
                    $_temp_file_name = slug_it($data['purchase_info']->reference_no);
                    $file_name = $dir . strtoupper($_temp_file_name);
                    $cl_name = slug_it($status);
                    pdf_create($pdf_file, slug_it($data['purchase_info']->reference_no), 1, null, true, $cl_name);
                }
            }

            $this->load->library('zip');
            // Read the invoices
            $this->zip->read_dir($dir, false);
            // Delete the temp directory for the client
            delete_dir($dir);
            if(!empty($client_info->name)) {
                $cl_name = slug_it($client_info->name);
            } else {
                $cl_name = slug_it($status);
            }
            $this->zip->download($module . '-' . $cl_name . '.zip');
            $this->zip->clear_data();
        } else {
            $data['title'] = lang('zip_' . $module);
            $data['client_id'] = $client_id;
            $data['module'] = $module;
            $data['subview'] = $this->load->view('admin/invoice/zipped', $data, FALSE);
            $this->load->view('admin/_layout_modal', $data);
        }
    }

    public
    function reminder($module, $module_id, $id = null)
    {
        $data['title'] = lang('reminder') . ' ' . lang('list');
        if ($this->input->post()) {
            $r_data['date'] = $this->input->post('date', true);
            $r_data['module'] = $module;
            $r_data['module_id'] = $module_id;
            $r_data['user_id'] = $this->input->post('user_id', true);
            $r_data['description'] = $this->input->post('description', true);
            $notify_by_email = $this->input->post('notify_by_email', true);
            if (empty($notify_by_email)) {
                $notify_by_email = 'No';
            } else {
                $notify_by_email = 'Yes';
            }
            $r_data['notify_by_email'] = $notify_by_email;
            $r_data['created_by'] = $this->session->userdata('user_id');
            $this->invoice_model->_table_name = 'tbl_reminders';
            $this->invoice_model->_primary_key = 'reminder_id';
            $this->invoice_model->save($r_data, $id);
            if ($module == 'client') {
                $url = 'admin/client/details/' . $module_id;
            } elseif ($module == 'invoice') {
                $url = 'admin/invoice/manage_invoice/invoice_details/' . $module_id;
            } elseif ($module == 'estimate') {
                $url = 'admin/estimates/create/estimates_details/' . $module_id;
            } elseif ($module == 'proposal') {
                $url = 'admin/proposals/createproposal/proposals_details/' . $module_id;
            } else if ($module == 'leads') {
                $url = 'admin/leads/leads_details/' . $module_id;
            } else {
                $url = '#';
            }
            // Log Activity
            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => $module,
                'module_field_id' => $module_id,
                'activity' => ('activity_added_reminder'),
                'icon' => 'fa-shopping-cart',
                'link' => $url,
                'value1' => $r_data['description'],
            );
            $this->invoice_model->_table_name = 'tbl_activities';
            $this->invoice_model->_primary_key = 'activities_id';
            $this->invoice_model->save($activity);

            $type = "success";
            $message = lang('update_reminder');
            set_message($type, $message);

            if ($module == 'invoice') {
                redirect('admin/invoice/manage_invoice/invoice_details/' . $module_id);
            } else if ($module == 'estimate') {
                redirect('admin/estimates/create/estimates_details/' . $module_id);
            } else if ($module == 'proposal') {
                redirect('admin/proposals/index/proposals_details/' . $module_id);
            } else if ($module == 'client') {
                redirect('admin/client/details/' . $module_id);
            } else if ($module == 'leads') {
                redirect('admin/leads/leads_details/' . $module_id);
            } else {
                if (empty($_SERVER['HTTP_REFERER'])) {
                    redirect('admin/dashboard');
                } else {
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
        } else {
            if (!empty($id)) {
                $data['active'] = 2;
                $data['reminder_info'] = $this->db->where('reminder_id', $id)->get('tbl_reminders')->row();
            } else {
                $data['active'] = 1;
            }
            $data['all_reminder'] = $this->db->where(array('module' => $module, 'module_id' => $module_id))->get('tbl_reminders')->result();

            $data['module_id'] = $module_id;
            $data['module'] = $module;
            $data['subview'] = $this->load->view('admin/invoice/reminder', $data, FALSE);
            $this->load->view('admin/_layout_modal', $data);
        }
    }

    public
    function delete_reminder($module, $module_id, $id = null)
    {
        $reminder_info = $this->db->where('reminder_id', $id)->get('tbl_reminders')->row();

        if ($module == 'client') {
            $url = 'admin/client/details/' . $module_id;
        } elseif ($module == 'invoice') {
            $url = 'admin/invoice/manage_invoice/invoice_details/' . $module_id;
        } elseif ($module == 'estimate') {
            $url = 'admin/estimates/create/estimates_details/' . $module_id;
        } elseif ($module == 'proposal') {
            $url = 'admin/proposals/index/proposals_details/' . $module_id;
        } else if ($module == 'leads') {
            $url = 'admin/leads/leads_details/' . $module_id;
        } else {
            $url = '#';
        }
        // Log Activity
        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => $module,
            'module_field_id' => $module_id,
            'activity' => ('activity_delete_reminder'),
            'icon' => 'fa-shopping-cart',
            'link' => $url,
            'value1' => $reminder_info->description,
        );
        $this->invoice_model->_table_name = 'tbl_activities';
        $this->invoice_model->_primary_key = 'activities_id';
        $this->invoice_model->save($activity);

        $this->invoice_model->_table_name = 'tbl_reminders';
        $this->invoice_model->_primary_key = 'reminder_id';
        $this->invoice_model->delete($id);

        echo json_encode(array("status" => 'success', 'message' => lang('delete_reminder')));
        exit();
    }

    public function pos_sales()
    {
        $data['title'] = lang('pos_sales');
        $data['all_client'] = $this->items_model->select_data('tbl_client', 'client_id', 'name');
        $data['warehouseList'] = $this->items_model->select_data('tbl_warehouse', 'warehouse_id', 'warehouse_name', array('status' => 'published'));
        $data['permission_user'] = $this->items_model->all_permission_user('13');
        $data['subview'] = $this->load->view('admin/invoice/pos_sales', $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function client_awards()
    {
        $data['title'] = lang('award');
        $data['all_client'] = $this->items_model->select_data('tbl_client', 'tbl_client.client_id', 'name', '', ['tbl_award_points' => 'tbl_award_points.client_id = tbl_client.client_id']);

        $data['subview'] = $this->load->view('admin/invoice/client_award_list', $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function clientawardpointslist()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');

            $this->datatables->table = 'tbl_award_points';
            $this->datatables->join_table = array('tbl_client');
            $this->datatables->join_where = array('tbl_client.client_id=tbl_award_points.client_id');
            $custom_field = custom_form_table_search(15);
            $action_array = array('award_points_id');
            $main_column = array('tbl_client.name', 'client_award_point');
            $result = array_merge($main_column, $custom_field, $action_array);
            $this->datatables->column_order = $result;
            $this->datatables->column_search = $result;
            $this->db->group_by('tbl_award_points.client_id');
            $this->datatables->order = array('award_points_id' => 'desc');

            $fetch_data = $this->datatables->get_datatable_permission();

            $data = array();
            foreach ($fetch_data as $_key => $v_rule) {
                $profile_info = $this->db->where('client_id', $v_rule->client_id)->get('tbl_client')->row();
                $clientpoint = $this->invoice_model->get_client_point_byid($v_rule->client_id);
                if (!empty($profile_info->name)) {
                    $clientname = $profile_info->name;
                } else {
                    $clientname = '-';
                }

                $action = null;
                $sub_array = array();
                $sub_array[] = $clientname;
                $sub_array[] = $clientpoint;
                $data[] = $sub_array;
            }

            render_table($data);
        } else {
            redirect('admin/dashboard');
        }
    }

    public function invoice_pdf()
    {
        $data['title'] = lang('invoice');
        $data['page_header'] = lang('invoice');
        $subview = $this->load->view('admin/common/sales_pdf_back', $data, true);
        // create pdf by dompdf
        $this->load->helper('dompdf');
        pdf_create($subview, lang('invoice'), 1, false, false, false, 'Partially Paid');

//        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function invoice_view()
    {
        $data['title'] = lang('invoice');
        $data['page_header'] = lang('invoice');
        $data['subview'] = $this->load->view('admin/common/invoice_view', $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function invoice_export() {
        $data['title'] = "Export Invoices";
        $subview = 'export_invoice';
        $data['subview'] = $this->load->view('admin/invoice/' . $subview, $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function export_invoiceOld() { // 2024-11-28
        $startDate = $this->input->post('start_date');
        $endDate = $this->input->post('end_date');
        $conditions = [
            'invoice_date >=' => $startDate,
            'invoice_date <=' => $endDate,
        ];
        $joins = [
            'tbl_client' => 'tbl_client.client_id = tbl_invoices.client_id',
            'tbl_items' => 'tbl_items.invoices_id = tbl_invoices.invoices_id',
        ];
        $invoice_data = join_data('tbl_invoices', '*', $conditions, $joins, 'array', ['tbl_invoices.invoices_id']);

        if(!empty($invoice_data)) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->mergeCells('A1:I1');
            $sheet->setCellValue('A1', 'B2B Invoices '. date('M-Y'));

            $sheet->getStyle('A1:I1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1:I1')->getFont()->setBold(true);
            $sheet->getStyle('A1:I1')->getFont()->setSize(14);

            $sheet->setCellValue('A2', 'No');
            $sheet->setCellValue('B2', 'Inv No');
            $sheet->setCellValue('C2', 'Date');
            $sheet->setCellValue('D2', 'Customer');
            $sheet->setCellValue('E2', 'GSTIN');
            $sheet->setCellValue('F2', 'Total');
            $sheet->setCellValue('G2', 'Taxable');
            $sheet->setCellValue('H2', 'CGST');
            $sheet->setCellValue('I2', 'SGST');

            $sheet->getStyle('A2:I2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A2:I2')->getFont()->setBold(true);
            $sheet->getStyle('A2:I2')->getFont()->setSize(12);

            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(12);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(30);
            $sheet->getColumnDimension('F')->setWidth(10);
            $sheet->getColumnDimension('G')->setWidth(10);
            $sheet->getColumnDimension('H')->setWidth(10);
            $sheet->getColumnDimension('I')->setWidth(10);

            $rowNumber = 3;
            $over_amt = 0;
            $over_sub_total = 0;
            $over_tax = 0;
            foreach($invoice_data as $key => $data) {
                $total_cost = $this->invoice_model->calculate_to('invoice_cost', $data['invoices_id']);
                $total_tax = $this->invoice_model->calculate_to('tax', $data['invoices_id']);
                $total = ($total_cost + $total_tax);
                $over_amt += $total;
                $over_sub_total += $total_cost;
                $over_tax += $data['tax'];
                $sheet->setCellValue('A' . $rowNumber, $key + 1);
                $sheet->setCellValue('B' . $rowNumber, $data['reference_no']);
                $sheet->setCellValue('C' . $rowNumber, $data['invoice_date']);
                $sheet->setCellValue('D' . $rowNumber, $data['name']);
                $sheet->setCellValue('E' . $rowNumber, $data['vat']);
                $sheet->setCellValue('F' . $rowNumber, $total);
                $sheet->setCellValue('G' . $rowNumber, $total_cost);
                $sheet->setCellValue('H' . $rowNumber, $data['tax']/2);
                $sheet->setCellValue('I' . $rowNumber, $data['tax']/2);

                if($key == array_key_last($invoice_data)) {
                    $rowNumber++;

                    $sheet->getStyle("A$rowNumber:I$rowNumber")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("A$rowNumber:I$rowNumber")->getFont()->setBold(true);

                    $sheet->setCellValue('A' . $rowNumber, '');
                    $sheet->setCellValue('B' . $rowNumber, '');
                    $sheet->setCellValue('C' . $rowNumber, '');
                    $sheet->setCellValue('D' . $rowNumber, '');
                    $sheet->setCellValue('E' . $rowNumber, 'Total');
                    $sheet->setCellValue('F' . $rowNumber, $over_amt);
                    $sheet->setCellValue('G' . $rowNumber, $over_sub_total);
                    $sheet->setCellValue('H' . $rowNumber, $over_tax/2);
                    $sheet->setCellValue('I' . $rowNumber, $over_tax/2);

                    $rowNumber++;

                    $sheet->mergeCells("H$rowNumber:I$rowNumber");
                    $sheet->getStyle("H$rowNumber:I$rowNumber")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("H$rowNumber:I$rowNumber")->getFont()->setBold(true);

                    $sheet->setCellValue('A' . $rowNumber, '');
                    $sheet->setCellValue('B' . $rowNumber, '');
                    $sheet->setCellValue('C' . $rowNumber, '');
                    $sheet->setCellValue('D' . $rowNumber, '');
                    $sheet->setCellValue('E' . $rowNumber, '');
                    $sheet->setCellValue('F' . $rowNumber, '');
                    $sheet->setCellValue('G' . $rowNumber, '');
                    $sheet->setCellValue('H' . $rowNumber, $over_tax);
                }
                $rowNumber++;
            }

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="report.xlsx"');
            header('Cache-Control: max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        } else {
            set_message('error', lang('no_record_available'));
            redirect('admin/invoice/invoice_export');
        }
    }

    public function export_invoiceOld1() { // 2024-11-29
        $startDate = $this->input->post('start_date');
        $endDate = $this->input->post('end_date');
        $conditions = [
            'invoice_date >=' => $startDate,
            'invoice_date <=' => $endDate,
        ];
        $joins = [
            'tbl_client' => 'tbl_client.client_id = tbl_invoices.client_id',
            'tbl_items' => 'tbl_items.invoices_id = tbl_invoices.invoices_id',
        ];
        $invoice_data = join_data('tbl_invoices', '*', $conditions, $joins, 'array', ['tbl_invoices.invoices_id']);

        if(!empty($invoice_data)) {
            $spreadsheet = new Spreadsheet();

            $GSTR1Sheet = $spreadsheet->getActiveSheet();
            $GSTR1Sheet->setTitle('B2B');

            $HSNSheet = $spreadsheet->createSheet();
            $HSNSheet->setTitle('HSN Summary');

            $GSTR1Sheet->mergeCells('A1:K1');
            $HSNSheet->mergeCells('A1:I1');

            if(date('Y-M-d', strtotime($startDate)) == date('Y-M-d', strtotime($endDate))) {
                $GSTR1Sheet->setCellValue('A1', 'B2B Invoices '. date('Y-M-d', strtotime($startDate)));
                $HSNSheet->setCellValue('A1', 'HSN Summary '. date('Y-M-d', strtotime($startDate)));

                $fileName = "GST-Report-". date('Y-M-d', strtotime($startDate)) .'.xlsx';
            } else {
                $GSTR1Sheet->setCellValue('A1', 'B2B Invoices '. date('Y-M-d', strtotime($startDate)) . ' to ' . date('Y-M-d', strtotime($endDate)));
                $HSNSheet->setCellValue('A1', 'HSN Summary '. date('Y-M-d', strtotime($startDate)) . ' to ' . date('Y-M-d', strtotime($endDate)));

                $fileName = "GST-Report-". date('Y-M-d', strtotime($startDate)) . '_to_' . date('Y-M-d', strtotime($endDate)) .'.xlsx';
            }

            $GSTR1Sheet->getStyle('A1:K1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $GSTR1Sheet->getStyle('A1:K1')->getFont()->setBold(true);
            $GSTR1Sheet->getStyle('A1:K1')->getFont()->setSize(14);

            $HSNSheet->getStyle('A1:I1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $HSNSheet->getStyle('A1:I1')->getFont()->setBold(true);
            $HSNSheet->getStyle('A1:I1')->getFont()->setSize(14);

            $GSTR1Sheet->setCellValue('A2', 'No');
            $GSTR1Sheet->setCellValue('B2', 'GSTIN/UIN of Recipient');
            $GSTR1Sheet->setCellValue('C2', 'Receiver Name');
            $GSTR1Sheet->setCellValue('D2', 'Invoice Number');
            $GSTR1Sheet->setCellValue('E2', 'Invoice Date');
            $GSTR1Sheet->setCellValue('F2', 'Place Of Supply');
            $GSTR1Sheet->setCellValue('G2', 'Tax Rate');
            $GSTR1Sheet->setCellValue('H2', 'Invoice Value');
            $GSTR1Sheet->setCellValue('I2', 'Taxable Value');
            $GSTR1Sheet->setCellValue('J2', 'CGST');
            $GSTR1Sheet->setCellValue('K2', 'SGST');

            $GSTR1Sheet->getStyle('A2:K2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $GSTR1Sheet->getStyle('A2:K2')->getFont()->setBold(true);
            $GSTR1Sheet->getStyle('A2:K2')->getFont()->setSize(12);

            $GSTR1Sheet->getColumnDimension('A')->setWidth(5);
            $GSTR1Sheet->getColumnDimension('B')->setWidth(35);
            $GSTR1Sheet->getColumnDimension('C')->setWidth(30);
            $GSTR1Sheet->getColumnDimension('D')->setWidth(30);
            $GSTR1Sheet->getColumnDimension('E')->setWidth(20);
            $GSTR1Sheet->getColumnDimension('F')->setWidth(20);
            $GSTR1Sheet->getColumnDimension('G')->setWidth(10);
            $GSTR1Sheet->getColumnDimension('H')->setWidth(15);
            $GSTR1Sheet->getColumnDimension('I')->setWidth(15);
            $GSTR1Sheet->getColumnDimension('J')->setWidth(10);
            $GSTR1Sheet->getColumnDimension('K')->setWidth(10);

            $HSNSheet->setCellValue('A2', 'No');
            $HSNSheet->setCellValue('B2', 'HSN');
            $HSNSheet->setCellValue('C2', 'Description');
            $HSNSheet->setCellValue('D2', 'UQC');
            $HSNSheet->setCellValue('E2', 'Total Quantity');
            $HSNSheet->setCellValue('F2', 'Taxable Value');
            $HSNSheet->setCellValue('G2', 'Tax Rate');
            $HSNSheet->setCellValue('H2', 'Central Tax Amount');
            $HSNSheet->setCellValue('I2', 'State/UT Tax Amount');

            $HSNSheet->getStyle('A2:I2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $HSNSheet->getStyle('A2:I2')->getFont()->setBold(true);
            $HSNSheet->getStyle('A2:I2')->getFont()->setSize(12);

            $HSNSheet->getColumnDimension('A')->setWidth(5);
            $HSNSheet->getColumnDimension('B')->setWidth(15);
            $HSNSheet->getColumnDimension('C')->setWidth(35);
            $HSNSheet->getColumnDimension('D')->setWidth(10);
            $HSNSheet->getColumnDimension('E')->setWidth(15);
            $HSNSheet->getColumnDimension('F')->setWidth(15);
            $HSNSheet->getColumnDimension('G')->setWidth(10);
            $HSNSheet->getColumnDimension('H')->setWidth(20);
            $HSNSheet->getColumnDimension('I')->setWidth(20);

            $rowNumber = 3;
            $over_amt = 0;
            $over_sub_total = 0;
            $over_tax = 0;
            foreach($invoice_data as $key => $data) {
                $tax_rate = '';
                $item_tax_name = json_decode($data['item_tax_name']);
                if(!empty($item_tax_name)) {
                    foreach($item_tax_name as $v_tax_name) {
                        $i_tax_name = explode('|', $v_tax_name);
                        $tax_rate .= $i_tax_name[1]. '%, ';
                    }
                }

                $tax_rates = array_filter(array_map('trim', explode(',', $tax_rate)));
                $tax_rates_string = implode("\n", $tax_rates);
                $total_cost = $this->invoice_model->calculate_to('invoice_cost', $data['invoices_id']);
                $total_tax = $this->invoice_model->calculate_to('tax', $data['invoices_id']);
                $total = ($total_cost + $total_tax);
                $over_amt += $total;
                $over_sub_total += $total_cost;
                $over_tax += $data['tax'];
                $GSTR1Sheet->setCellValue('A' . $rowNumber, $key + 1);
                $GSTR1Sheet->setCellValue('B' . $rowNumber, $data['vat']);
                $GSTR1Sheet->setCellValue('C' . $rowNumber, $data['name']);
                $GSTR1Sheet->setCellValue('D' . $rowNumber, $data['reference_no']);
                $GSTR1Sheet->setCellValue('E' . $rowNumber, $data['invoice_date']);
                $GSTR1Sheet->setCellValue('F' . $rowNumber, $data['city']);
                $GSTR1Sheet->setCellValue('G' . $rowNumber, $tax_rates_string);
                $GSTR1Sheet->getStyle('G' . $rowNumber)->getAlignment()->setWrapText(true);
                $GSTR1Sheet->setCellValue('H' . $rowNumber, $total);
                $GSTR1Sheet->setCellValue('I' . $rowNumber, $total_cost);
                $GSTR1Sheet->setCellValue('J' . $rowNumber, $data['tax']/2);
                $GSTR1Sheet->setCellValue('K' . $rowNumber, $data['tax']/2);

                if($key == array_key_last($invoice_data)) {
                    $rowNumber++;

                    $GSTR1Sheet->getStyle("A$rowNumber:K$rowNumber")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $GSTR1Sheet->getStyle("A$rowNumber:K$rowNumber")->getFont()->setBold(true);

                    $GSTR1Sheet->setCellValue('A' . $rowNumber, '');
                    $GSTR1Sheet->setCellValue('B' . $rowNumber, '');
                    $GSTR1Sheet->setCellValue('C' . $rowNumber, '');
                    $GSTR1Sheet->setCellValue('D' . $rowNumber, 'Total');
                    $GSTR1Sheet->setCellValue('E' . $rowNumber, '');
                    $GSTR1Sheet->setCellValue('F' . $rowNumber, '');
                    $GSTR1Sheet->setCellValue('G' . $rowNumber, '');
                    $GSTR1Sheet->setCellValue('H' . $rowNumber, $over_amt);
                    $GSTR1Sheet->setCellValue('I' . $rowNumber, $over_sub_total);
                    $GSTR1Sheet->setCellValue('J' . $rowNumber, $over_tax/2);
                    $GSTR1Sheet->setCellValue('K' . $rowNumber, $over_tax/2);

                    $rowNumber++;

                    $GSTR1Sheet->mergeCells("J$rowNumber:K$rowNumber");
                    $GSTR1Sheet->getStyle("J$rowNumber:K$rowNumber")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $GSTR1Sheet->getStyle("J$rowNumber:K$rowNumber")->getFont()->setBold(true);

                    $GSTR1Sheet->setCellValue('A' . $rowNumber, '');
                    $GSTR1Sheet->setCellValue('B' . $rowNumber, '');
                    $GSTR1Sheet->setCellValue('C' . $rowNumber, '');
                    $GSTR1Sheet->setCellValue('D' . $rowNumber, '');
                    $GSTR1Sheet->setCellValue('E' . $rowNumber, '');
                    $GSTR1Sheet->setCellValue('F' . $rowNumber, '');
                    $GSTR1Sheet->setCellValue('G' . $rowNumber, '');
                    $GSTR1Sheet->setCellValue('H' . $rowNumber, '');
                    $GSTR1Sheet->setCellValue('I' . $rowNumber, '');
                    $GSTR1Sheet->setCellValue('J' . $rowNumber, $over_tax);
                }
                $rowNumber++;
            }

            header('Content-Type: application/vnd.ms-excel');
            header("Content-Disposition: attachment;filename=\"$fileName\"");
            header('Cache-Control: max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        } else {
            set_message('error', lang('no_record_available'));
            redirect('admin/invoice/invoice_export');
        }
    }

    public function export_invoice() {
        $startDate = $this->input->post('start_date');
        $endDate = $this->input->post('end_date');
        
        $Where = [
            'invoice_date >=' => $startDate,
            'invoice_date <=' => $endDate,
        ];

        $this->db->select('tbl_invoices.*, tbl_client.*, tbl_items.*');
        $this->db->from('tbl_invoices');
        $this->db->join('tbl_client', 'tbl_client.client_id = tbl_invoices.client_id');
        $this->db->join('tbl_items', 'tbl_items.invoices_id = tbl_invoices.invoices_id');
        $this->db->where($Where);
        $this->db->where('tbl_client.vat IS NOT NULL');
        $this->db->where('tbl_client.vat !=', '');
        $this->db->group_by('tbl_invoices.invoices_id');

        // $B2BData = join_data('tbl_invoices', '*', $B2BWhere, $B2BJoins, 'array', ['tbl_invoices.invoices_id']);
        $B2BData = $this->db->get()->result_array(); // 2024-12-11

        $this->db->select('tbl_invoices.*, tbl_client.*, tbl_items.*');
        $this->db->from('tbl_invoices');
        $this->db->join('tbl_client', 'tbl_client.client_id = tbl_invoices.client_id');
        $this->db->join('tbl_items', 'tbl_items.invoices_id = tbl_invoices.invoices_id');
        $this->db->where($Where);
        $this->db->group_start();
        $this->db->where('tbl_client.vat IS NULL');
        $this->db->or_where('tbl_client.vat', '');
        $this->db->group_end();
        $this->db->group_by('tbl_invoices.invoices_id');

        $B2CData = $this->db->get()->result_array();

        $hsnSelect = 'tbl_items.hsn_code,
            tbl_items.unit,
            tbl_items.item_tax_name,
            SUM(tbl_items.quantity) AS total_quantity,
            SUM(tbl_items.item_tax_total) AS total_tax,
            SUM(tbl_items.total_cost) AS total_taxable';

        $hsnWhere = [
            'tbl_invoices.invoice_date >=' => $startDate,
            'tbl_invoices.invoice_date <=' => $endDate,
            'tbl_items.hsn_code IS NOT NULL' => null,
            'TRIM(tbl_items.hsn_code) !=' => '',
            'TRIM(tbl_items.hsn_code) !=' => 'null'
        ];

        $hsnJoin = [
            'tbl_invoices' => 'tbl_invoices.invoices_id = tbl_items.invoices_id',
        ];

        $hsnGroupBy = ['tbl_items.hsn_code'];

        $hsnData = join_data('tbl_items', $hsnSelect, $hsnWhere, $hsnJoin, 'array', $hsnGroupBy);

        if (!empty($B2BData) || !empty($B2CData) || !empty($hsnData)) {
            $spreadsheet = $this->initializeSpreadsheet($startDate, $endDate);
            
            $this->populateB2BSheet($spreadsheet, $B2BData);
            $this->populateB2CSheet($spreadsheet, $B2CData);
            $this->populateHSNSheet($spreadsheet, $hsnData);

            $this->downloadSpreadsheet($spreadsheet, $startDate, $endDate);
        } else {
            set_message('error', lang('no_record_available'));
            redirect('admin/invoice/invoice_export');
        }
    }

    private function initializeSpreadsheet($startDate, $endDate) {
        $spreadsheet = new Spreadsheet();
        
        // B2B Sheet
        $B2BSheet = $spreadsheet->getActiveSheet();
        $B2BSheet->setTitle('B2B');
        $this->setSheetHeader($B2BSheet, 'B2B Invoices', $startDate, $endDate, 'A1:K1');
        $this->setColumnWidths($B2BSheet, [
            'A' => 5, 'B' => 35, 'C' => 30, 'D' => 30, 'E' => 20,'F' => 20, 'G' => 10, 'H' => 15, 'I' => 15, 'J' => 10, 'K' => 10,
        ]);
        $this->setTableHeader($B2BSheet, [
            'No',
            'GSTIN/UIN of Recipient',
            'Receiver Name',
            'Invoice Number',
            'Invoice Date',
            'Place Of Supply',
            'Tax Rate',
            'Invoice Value',
            'Taxable Value',
            'CGST',
            'SGST',
        ], 'A2', 'A2:K2');

        // B2C Sheet
        $B2CSheet = $spreadsheet->createSheet();
        $B2CSheet->setTitle('B2C');
        $this->setSheetHeader($B2CSheet, 'B2C Invoices', $startDate, $endDate, 'A1:N1');
        $this->setColumnWidths($B2CSheet, [
            'A' => 5, 'B' => 30, 'C' => 10, 'D' => 30, 'E' => 30,'F' => 10, 'G' => 35, 'H' => 10, 'I' => 15, 'J' => 15, 'K' => 10, 'L' => 10, 'M' => 20, 'N' => 10,
        ]);
        $this->setTableHeader($B2CSheet, [
            'No',
            'Invoice Number',
            'Date',
            'Party Name',
            'GST No',
            'HSN No',
            'Item Name',
            'Qty',
            'Rate',
            'Amt',
            'CGST',
            'SGST',
            'Inv Value',
            'Tax Rate',
        ], 'A2', 'A2:N2');
        
        // HSN Sheet
        $HSNSheet = $spreadsheet->createSheet();
        $HSNSheet->setTitle('HSN Summary');
        $this->setSheetHeader($HSNSheet, 'HSN Summary', $startDate, $endDate, 'A1:I1');
        $this->setColumnWidths($HSNSheet, [
            'A' => 5, 'B' => 15, 'C' => 35, 'D' => 10, 'E' => 15,'F' => 15, 'G' => 10, 'H' => 20, 'I' => 20,
        ]);
        $this->setTableHeader($HSNSheet, [
            'No',
            'HSN',
            'Description',
            'UQC',
            'Total Quantity',
            'Taxable Value',
            'Tax Rate',
            'Central Tax Amount',
            'State/UT Tax Amount',
        ], 'A2', 'A2:I2');
        
        return $spreadsheet;
    }

    private function populateB2BSheet($spreadsheet, $B2BData) {
        $sheet = $spreadsheet->getSheetByName('B2B');
        $rowNumber = 3;
        $totals = ['amount' => 0, 'subTotal' => 0, 'tax' => 0];
        
        foreach ($B2BData as $key => $data) {
            $taxRates = $this->getTaxRates($data['item_tax_name']);
            $totalCost = $this->invoice_model->calculate_to('invoice_cost', $data['invoices_id']);
            $totalTax = $this->invoice_model->calculate_to('tax', $data['invoices_id']);
            $total = $totalCost + $totalTax;

            $totals['amount'] += $total;
            $totals['subTotal'] += $totalCost;
            $totals['tax'] += $data['tax'];

            $sheet->fromArray([
                $key + 1,
                $data['vat'],
                $data['name'],
                $data['reference_no'], 
                $data['invoice_date'],
                $data['city'],
                $taxRates,
                $total,
                $totalCost,
                $data['tax'] / 2,
                $data['tax'] / 2,
            ], null, "A$rowNumber");

            $sheet->getStyle("G$rowNumber")->getAlignment()->setWrapText(true);
            $rowNumber++;
        }
        
        $this->addTotalsRowB2B($sheet, $rowNumber, $totals);
    }

    private function populateB2CSheet($spreadsheet, $B2CData) {
        $sheet = $spreadsheet->getSheetByName('B2C');
        $rowNumber = 3;
        $totals = ['amount' => 0, 'subTotal' => 0, 'tax' => 0];
        $B2CHsnData = [];
        
        foreach ($B2CData as $key => $data) {
            $taxRates = $this->getTaxRates($data['item_tax_name']);
            $totalCost = $this->invoice_model->calculate_to('invoice_cost', $data['invoices_id']);
            $totalTax = $this->invoice_model->calculate_to('tax', $data['invoices_id']);
            $total = $totalCost + $totalTax;

            $itemsData = $this->db->where('invoices_id', $data['invoices_id'])->get('tbl_items')->result_array();

            $hsnCode = '';
            foreach($itemsData as $item) {
                if(!empty($item['hsn_code']) && $item['hsn_code'] != 'null') {
                    if(isset($B2CHsnData[$item['hsn_code']])) {
                        $B2CHsnData[$item['hsn_code']]['taxable_value'] += $item['total_cost'];
                        $B2CHsnData[$item['hsn_code']]['tax'] += $item['item_tax_total'];
                        $B2CHsnData[$item['hsn_code']]['inv_value'] += $item['total_cost'] + $item['item_tax_total'];
                    } else {
                        $B2CHsnData[$item['hsn_code']] = [
                            'particulars' => $item['hsn_code'],
                            'item_name' => $item['item_name'],
                            'taxable_value' => $item['total_cost'],
                            'tax' => $item['item_tax_total'],
                            'inv_value' => $item['total_cost'] + $item['item_tax_total'],
                        ];
                        $hsnCode .= $item['hsn_code'] . ', ';
                    }
                }
            }

            $totals['amount'] += $total;
            $totals['subTotal'] += $totalCost;
            $totals['tax'] += $data['tax'];

            $gstin = !empty($data['vat']) ? $data['vat'] : 'UnRegistered';
            // $hsnCode = (!empty($data['hsn_code']) && $data['hsn_code'] != 'null') ? $data['hsn_code'] : '-';
            $tax = (isset($data['tax']) && $data['tax'] != 0.00) ? $data['tax'] / 2 : ' - ';
            $taxRates = !empty($taxRates) ? $taxRates : ' - ';

            $sheet->fromArray([
                $key + 1,
                $data['reference_no'],
                $data['invoice_date'],
                $data['name'],
                $gstin,
                $hsnCode,
                $data['item_name'],
                $data['quantity'],
                $data['unit_cost'],
                $total,
                $tax,
                $tax,
                $totalCost,
                $taxRates,
            ], null, "A$rowNumber");

            $sheet->getStyle("N$rowNumber")->getAlignment()->setWrapText(true);
            $rowNumber++;
        }

        $this->addTotalsRowB2C($sheet, $rowNumber, $totals, $B2CHsnData);
    }

    private function populateHSNSheet($spreadsheet, $hsnData) {
        $sheet = $spreadsheet->getSheetByName('HSN Summary');
        $rowNumber = 3;
        $totals = ['quantity' => 0, 'total_amount' => 0, 'tax' => 0];

        foreach($hsnData as $key => $data) {
            $taxRates = $this->getTaxRates($data['item_tax_name']);
            $unit = !empty($data['unit']) ? $data['unit'] : 'N/A';

            $totals['quantity'] += $data['total_quantity'];
            $totals['total_amount'] += $data['total_taxable'];
            $totals['tax'] += $data['total_tax'];

            $sheet->fromArray([
                $key + 1,
                $data['hsn_code'],
                "Test",
                $unit,
                $data['total_quantity'],
                $data['total_taxable'],
                $taxRates,
                $data['total_tax'] / 2,
                $data['total_tax'] / 2,
            ], null, "A$rowNumber");

            $sheet->getStyle("G$rowNumber")->getAlignment()->setWrapText(true);
            $rowNumber++;
        }

        $this->addTotalsRowHSN($sheet, $rowNumber, $totals);
    }

    private function downloadSpreadsheet($spreadsheet, $startDate, $endDate) {
        $fileName = $this->generateFileName($startDate, $endDate);
        
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }

    private function setTableHeader($sheet, $headers, $row, $range) {
        $sheet->fromArray($headers, null, $row);
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($range)->getFont()->setBold(true)->setSize(12);
    }

    private function setSheetHeader($sheet, $title, $startDate, $endDate, $mergeRange) {
        $sheet->mergeCells($mergeRange);
        $dateRange = $startDate === $endDate 
            ? date('Y-M-d', strtotime($startDate)) 
            : date('Y-M-d', strtotime($startDate)) . ' to ' . date('Y-M-d', strtotime($endDate));
        $sheet->setCellValue('A1', "$title $dateRange");
        $sheet->getStyle($mergeRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($mergeRange)->getFont()->setBold(true)->setSize(14);
    }

    private function setColumnWidths($sheet, $columns) {
        foreach ($columns as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
    }

    private function addKeyValueRow($sheet, $rowNumber, $key, $value) {
        $sheet->setCellValue("A$rowNumber", $key);
        $sheet->getStyle("A$rowNumber:B$rowNumber")
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue("B$rowNumber", $value);
        return $rowNumber + 1;
    }

    private function addMergedHeader($sheet, $rowNumber, $text) {
        $sheet->mergeCells("A$rowNumber:B$rowNumber");
        $sheet->getStyle("A$rowNumber:B$rowNumber")
              ->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle("A$rowNumber:B$rowNumber")
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue("A$rowNumber", $text);
    }

    private function getTaxRates($itemTaxName) {
        $taxRates = [];
        $taxData = json_decode($itemTaxName, true);
        if (!empty($taxData)) {
            foreach ($taxData as $taxName) {
                $parts = explode('|', $taxName);
                if (isset($parts[1])) {
                    $taxRates[] = $parts[1] . '%';
                }
            }
        }
        return implode("\n", $taxRates);
    }

    private function addTotalsRowB2B($sheet, $rowNumber, $totals) {
        $sheet->mergeCells("A$rowNumber:G$rowNumber");
        $sheet->getStyle("A$rowNumber:G$rowNumber")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->fromArray(['Total', '', '', '', '', '', '', $totals['amount'], $totals['subTotal'], $totals['tax'] / 2, $totals['tax'] / 2], null, "A$rowNumber");

        $nextRow = $rowNumber + 1;
        $sheet->mergeCells("J$nextRow:K$nextRow");
        $sheet->setCellValue("J$nextRow", $totals['tax']);
        $sheet->getStyle("A$rowNumber:K$rowNumber")->getFont()->setBold(true);
        $sheet->getStyle("J$nextRow:K$nextRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("J$nextRow:K$nextRow")->getFont()->setBold(true);
    }

    private function addTotalsRowB2C($sheet, $rowNumber, $totals, $B2CHsnData) {
        $sheet->mergeCells("A$rowNumber:I$rowNumber");
        $sheet->getStyle("A$rowNumber:I$rowNumber")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->fromArray(['Total', '', '', '', '', '', '', '',  '', $totals['amount'], $totals['tax'] / 2, $totals['tax'] / 2, $totals['subTotal'], ''], null, "A$rowNumber");

        $nextRow = $rowNumber + 1;
        $sheet->mergeCells("K$nextRow:L$nextRow");
        $sheet->setCellValue("K$nextRow", $totals['tax']);
        $sheet->getStyle("A$rowNumber:N$rowNumber")->getFont()->setBold(true);
        $sheet->getStyle("K$nextRow:L$nextRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("K$nextRow:L$nextRow")->getFont()->setBold(true);

        $nextRow += 1;

        $this->addB2CHsnTable($sheet, $nextRow, $B2CHsnData);
    }

    private function addTotalsRowHSN($sheet, $rowNumber, $totals) {
        $sheet->mergeCells("A$rowNumber:D$rowNumber");
        $sheet->getStyle("A$rowNumber:D$rowNumber")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->fromArray(['Total', '', '', '', $totals['quantity'], $totals['total_amount'], '', $totals['tax'] / 2, $totals['tax'] / 2], null, "A$rowNumber");

        $nextRow = $rowNumber + 1;
        $sheet->mergeCells("H$nextRow:I$nextRow");
        $sheet->setCellValue("H$nextRow", $totals['tax']);
        $sheet->getStyle("A$rowNumber:I$rowNumber")->getFont()->setBold(true);
        $sheet->getStyle("H$nextRow:I$nextRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("H$nextRow:I$nextRow")->getFont()->setBold(true);
    }

    private function addB2CHsnTable($sheet, $rowNumber, $B2CHsnData) {
        foreach($B2CHsnData as $data) {
            $this->addMergedHeader($sheet, $rowNumber, 'HSN Summary');
            $rowNumber++;
            $rowNumber = $this->addKeyValueRow($sheet, $rowNumber, 'Item Name', $data['item_name']);
            $rowNumber = $this->addKeyValueRow($sheet, $rowNumber, 'HSN No', $data['particulars']);
            $rowNumber++;

            $headerRange = "A$rowNumber:E$rowNumber";
            $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(14);
            $this->setColumnWidths($sheet, [
                'A' => 25, 'B' => 20, 'C' => 10, 'D' => 10, 'E' => 15]);
            $this->setTableHeader($sheet, [
                'Particulars',
                'Taxable Value',
                'CGST',
                'SGST',
                'Inv Value',
            ], "A$rowNumber", $headerRange);

            $rowNumber++;
            $sheet->fromArray([
                'Total Sales',
                $data['taxable_value'],
                $data['tax'] / 2,
                $data['tax'] / 2,
                $data['inv_value'],
            ], null, "A$rowNumber");

            $rowNumber++;
            $sheet->getStyle("A$rowNumber:B$rowNumber")->getFont()->setBold(true)->setSize(12);
            $sheet->fromArray([
                'Net Sales',
                $data['taxable_value'],
                $data['tax'] / 2,
                $data['tax'] / 2,
            ], null, "A$rowNumber");

            $rowNumber +=2 ;
        }
    }

    private function generateFileName($startDate, $endDate) {
        $dateRange = $startDate === $endDate 
            ? date('Y-M-d', strtotime($startDate)) 
            : date('Y-M-d', strtotime($startDate)) . '_to_' . date('Y-M-d', strtotime($endDate));
        return "GST-Report-$dateRange.xlsx";
    }
}