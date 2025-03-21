<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Accounting extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('accounting_model');
    }

    public function journal_entry()
    {
        $data['title'] = lang('chart_of_accounts');
        $data['breadcrumbs'] = lang('chart_of_accounts');
        $data['subview'] = $this->load->view('admin/accounting/journal_entry', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }

    public function journalEntryList()
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/dashboard');
        }
        $this->load->model('datatables');
        $this->datatables->table = 'tbl_journals';
        $this->datatables->join_table = array('tbl_account_details');
        $this->datatables->join_where = array('tbl_journals.created_by = tbl_account_details.user_id');
        $column = array('journal_id', 'tbl_journals.date', 'tbl_journals.reference_no', 'tbl_journals.total_debit', 'tbl_journals.total_credit', 'tbl_users.fullname');
        $this->datatables->column_order = $column;
        $this->datatables->column_search = $column;
        $this->datatables->order = array('tbl_journals.journal_id' => 'desc');

        $fetch_data = make_datatables();
        $data = array();
        $edited = can_action_by_label('journal_entry', 'edited');
        $deleted = can_action_by_label('journal_entry', 'deleted');
        foreach ($fetch_data as $key => $row) {
            $sub_array = array();
            $sub_array[] = '<a href="' . base_url('admin/accounting/view_journal_entry/' . $row->journal_id) . '">' . $row->reference_no . '</a>';
            $sub_array[] = display_date($row->date);
            $sub_array[] = display_money($row->total_debit);
            $sub_array[] = display_money($row->total_credit);
            $sub_array[] = $row->fullname;
            $action = '';
            if (!empty($edited)) {
                $action .= btn_edit('admin/accounting/new_journal_entry/' . $row->journal_id);

            }
            if (!empty($deleted)) {
                $action .= ' ' . ajax_anchor(base_url("admin/accounting/delete_journal_entry/$row->journal_id"), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $key));
            }
            $sub_array[] = $action;
            $data[] = $sub_array;
        }
        render_table($data);
    }

    public function new_journal_entry($id = null)
    {
        $data['title'] = lang('new_journal_entry');
        $data['breadcrumbs'] = lang('new_journal_entry');
        if (!empty($id)) {
            $data['journal_entry'] = get_row('tbl_journals', array('journal_id' => $id));
            $data['journal_entry_items'] = get_result('tbl_journal_items', array('journal_id' => $id));
            $data['chart_of_accounts'] = get_result('tbl_chart_of_accounts', array('status' => 1));
        }

        $data['subview'] = $this->load->view('admin/accounting/new_journal_entry', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }

    public function get_chart_of_accounts()
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/dashboard');
        }
        // get the data from url
        $search = $this->input->get('q');
        $result = $this->accounting_model->get_chart_of_accounts_by_search($search);
        // html output with account_type and sub_type and name
        $data = array();
        foreach ($result as $row) {
            $data[] = array(
                'id' => $row->chart_of_account_id,
                'text' => $row->name,
                'html' => $row->name . ' <small>(' . $row->account_type . ' - ' . $row->account_sub_type . ')</small>',
            );
        }
        echo json_encode($data);
        exit();
    }

    public function save_journal_entry($id = null)
    {

        $data = $this->accounting_model->array_from_post(array('date', 'reference_no', 'total_debit', 'total_credit', 'notes'));
        $data['created_by'] = $this->session->userdata('user_id');

        // check total debit and credit amount is equal or not
        if ($data['total_debit'] != $data['total_credit']) {
            set_message('error', lang('total_debit_credit_not_equal'));
            redirect('admin/accounting/new_journal_entry');
        }
        // check debit and credit amount is empty or 0 or not
        if ($data['total_debit'] == 0 || $data['total_credit'] == 0) {
            set_message('error', lang('debit_credit_empty'));
            redirect('admin/accounting/new_journal_entry');
        }


        $account = $this->input->post('account', true);
        $debit = $this->input->post('debit', true);
        $credit = $this->input->post('credit', true);
        $description = $this->input->post('description', true);


        $this->accounting_model->_table_name = "tbl_journals"; // table name
        $this->accounting_model->_primary_key = "journal_id"; // $id

        $journal_id = $this->accounting_model->save($data, $id);

        if ($journal_id) {
            $this->accounting_model->_table_name = "tbl_journal_items"; // table name
            $this->accounting_model->_primary_key = "journal_item_id"; // $id
            $this->accounting_model->delete_multiple(array('journal_id' => $journal_id));

            foreach ($account as $key => $value) {
                $journal_detail = array(
                    'journal_id' => $journal_id,
                    'chart_of_account_id' => $value,
                    'debit' => $debit[$key],
                    'credit' => $credit[$key],
                    'description' => $description[$key],
                );
                $this->accounting_model->save($journal_detail);
            }

            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'accounting',
                'module_field_id' => $id,
                'activity' => 'activity_added_journal_entry',
                'icon' => 'fa-circle-o',
                'value1' => $data['reference_no'] . ' - ' . $data['total_debit'] . ' - ' . $data['total_credit'] . ' - ' . $data['date'],
            );
            $this->accounting_model->_table_name = 'tbl_activities';
            $this->accounting_model->_primary_key = 'activities_id';
            $this->accounting_model->save($activity);

            set_message('success', lang('journal_entry_saved_successfully'));
        } else {
            set_message('error', lang('journal_entry_saved_failed'));
        }
        redirect('admin/accounting/journal_entry');
    }

    public function delete_journal_entry($id = null)
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/dashboard');
        }
        if (!empty($id)) {
            $journal_entry = get_row('tbl_journals', array('journal_id' => $id));

            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'accounting',
                'module_field_id' => $id,
                'activity' => 'activity_delete_journal_entry',
                'icon' => 'fa-circle-o',
                'value1' => $journal_entry->reference_no . ' - ' . $journal_entry->total_debit . ' - ' . $journal_entry->total_credit . ' - ' . $journal_entry->date,
            );
            $this->accounting_model->_table_name = 'tbl_activities';
            $this->accounting_model->_primary_key = 'activities_id';
            $this->accounting_model->save($activity);

            $this->accounting_model->_table_name = "tbl_journals"; // table name
            $this->accounting_model->_primary_key = "journal_id"; // $id
            $this->accounting_model->delete($id);

            $this->accounting_model->_table_name = "tbl_journal_items"; // table name
            $this->accounting_model->_primary_key = "journal_item_id"; // $id
            $this->accounting_model->delete_multiple(array('journal_id' => $id));
            $type = 'success';
            $message = lang('journal_entry_deleted_successfully');

        } else {
            $type = 'error';
            $message = lang('journal_entry_deleted_failed');
        }
        echo json_encode(array("status" => $type, 'message' => $message));
        exit();
    }

    public function view_journal_entry($id, $pdf = null)
    {

        $data['active'] = 'journal_entry';
        $data['title'] = lang('view') . ' ' . lang('journal_entry');
        $data['sales_info'] = join_data('tbl_journals', 'tbl_journals.*,tbl_account_details.fullname', array('journal_id' => $id), array('tbl_account_details' => 'tbl_account_details.user_id=tbl_journals.created_by'));

        if (!empty($data['sales_info'])) {
            $data['sales_info']->ref_no = lang('payment') . ' : ' . $data['sales_info']->reference_no;
            $data['sales_info']->start_date = lang('date') . ' : ' . display_date($data['sales_info']->date);
            $data['sales_info']->sales_agent = lang('created_by') . ' : ' . ($data['sales_info']->fullname);
            $data['sales_info']->end_date = '';
            if ($data['sales_info']->status === 'pending') {
                $label = 'warning';
            } elseif ($data['sales_info']->status === 'approved') {
                $label = 'success';
            } elseif ($data['sales_info']->status === 'rejected') {
                $label = 'danger';
            } else {
                $label = 'default';
            }
            $data['sales_info']->status = lang('status') . ' :  <span class="label label-' . $label . '">' . lang($data['sales_info']->status) . '</span>';
            $data['sales_info']->custom_field = '';
            $data['sales_info']->company_heading = '';
            $data['sales_info']->name = '';
            $data['journal_entry_items'] = get_result('tbl_journal_items', array('journal_id' => $id));
            $data['receipt_items'] = join_data('tbl_journal_items', 'tbl_journal_items.*,tbl_chart_of_accounts.name,tbl_chart_of_accounts.code', array('journal_id' => $id),
                array('tbl_chart_of_accounts' => 'tbl_chart_of_accounts.chart_of_account_id=tbl_journal_items.chart_of_account_id'), 'object');
            if (!empty($pdf)) {
                $data['sales_info']->item_layout = 'admin/accounting/view_journal_items_pdf';
            } else {
                $data['sales_info']->item_layout = 'admin/accounting/view_journal_items';
            }

        } else {
            set_message('error', 'No data Found');
            redirect('admin/accounting/journal_entry');
        }
        if (!empty($pdf)) {
            $this->common_model->sales_pdf($data, $pdf);
        }

        $data['subview'] = $this->load->view('admin/accounting/view_journal_entry', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }

    public function chart_of_accounts()
    {
        $data['title'] = lang('chart_of_accounts');
        $data['breadcrumbs'] = lang('chart_of_accounts');
        $data['subview'] = $this->load->view('admin/accounting/chart_of_accounts', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }

    public function chartOfAccountsList()
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/dashboard');
        }
        $this->load->model('datatables');
        $this->datatables->table = 'tbl_chart_of_accounts';
        $this->datatables->join_table = array('tbl_account_type', 'tbl_account_sub_type');
        $this->datatables->join_where = array('tbl_chart_of_accounts.account_type_id = tbl_account_type.account_type_id', 'tbl_chart_of_accounts.account_sub_type_id = tbl_account_sub_type.account_sub_type_id');
        $column = array('chart_of_account_id', 'tbl_account_type.account_type', 'tbl_account_sub_type.account_sub_type', 'tbl_chart_of_accounts.name', 'tbl_chart_of_accounts.code', 'tbl_chart_of_accounts.notes', 'tbl_chart_of_accounts.status');
        $this->datatables->column_order = $column;
        $this->datatables->column_search = $column;
        $this->datatables->order = array('tbl_chart_of_accounts.chart_of_account_id' => 'desc');

        $fetch_data = make_datatables();
        $data = array();
        $edited = can_action_by_label('chart_of_accounts', 'edited');
        $deleted = can_action_by_label('chart_of_accounts', 'deleted');
        foreach ($fetch_data as $key => $row) {
            $sub_array = array();
            $sub_array[] = $row->code;
            $sub_array[] = $row->name;
            $sub_array[] = $row->account_type . ' - ' . $row->account_sub_type;
            $sub_array[] = 0;
            $sub_array[] = ($row->status == 1) ? '<span class="label label-success">' . lang('active') . '</span>' : '<span class="label label-danger">' . lang('inactive') . '</span>';
            $action = '';
            if (!empty($edited)) {
                $action .= '<a href="' . base_url() . 'admin/accounting/new_chart_of_account/' . $row->chart_of_account_id . '"
                               class="btn btn-primary btn-xs" title="' . lang('edit') . '" data-toggle="modal"
                               data-target="#myModal_lg"><span class="fa fa-pencil-square-o"></span></a>  ';
            }
            if (!empty($deleted)) {
                $action .= ' ' . ajax_anchor(base_url("admin/accounting/delete_chart_of_account/$row->chart_of_account_id"), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $key));
            }
            $sub_array[] = $action;
            $data[] = $sub_array;
        }
        render_table($data);
    }

    // new_chart_of_account
    public function new_chart_of_account($id = null)
    {
        $data['title'] = lang('chart_of_account');
        $where = array('status' => 1);
        $data['account_types'] = $this->accounting_model->select_data('tbl_account_type', 'account_type_id', 'account_type', $where);
        if (!empty($id)) {
            $edited = can_action_by_label('chart_of_accounts', 'edited');
            if (!empty($edited)) {
                $data['chart_of_account'] = get_row('tbl_chart_of_accounts', array('chart_of_account_id' => $id));
                $where['account_type_id'] = $data['chart_of_account']->account_type_id;
                $data['account_sub_types'] = $this->accounting_model->select_data('tbl_account_sub_type', 'account_sub_type_id', 'account_sub_type', $where);
            }
            if (empty($data['chart_of_account'])) {
                $type = "error";
                $message = lang("no_record_found");
                set_message($type, $message);
                redirect('admin/accounting/chart_of_accounts');
            }
        }
        $data['subview'] = $this->load->view('admin/accounting/new_chart_of_account', $data, false);
        $this->load->view('admin/_layout_modal_lg', $data); //page load
    }

    public function get_account_sub_types($account_type_id)
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/dashboard');
        }
        $where = array('status' => 1);
        if (!empty($account_type_id)) {
            $where['account_type_id'] = $account_type_id;
        }
        $account_sub_types = $this->accounting_model->select_data('tbl_account_sub_type', 'account_sub_type_id', 'account_sub_type', $where);
        $html = '';
        if (!empty($account_sub_types)) {
            foreach ($account_sub_types as $key => $value) {
                $html .= '<option value="' . $key . '">' . $value . '</option>';
            }
        }
        $data['success'] = true;
        $data['html'] = $html;
        echo json_encode($data);
        exit;
    }

    public function save_chart_of_account($id = null)
    {
        $created = can_action_by_label('chart_of_accounts', 'created');
        $edited = can_action_by_label('chart_of_accounts', 'edited');
        
        if (!empty($created) || !empty($edited) && !empty($id)) {
            // Set validation rules
            $this->form_validation->set_rules('account_type_id', 'Account Type', 'required');
            $this->form_validation->set_rules('account_sub_type_id', 'Account Sub Type', 'required');
            $this->form_validation->set_rules('name', 'Account Name', 'required');
            $this->form_validation->set_rules('code', 'Account Code', 'required|callback_code_exists');
            
            if ($this->form_validation->run() == FALSE) {
                // Validation fails, return errors as JSON
                $response = array(
                    'status' => 'error',
                    'validation_errors' => validation_errors('<span class="text-danger">', '</span>')
                );
                echo json_encode($response);
                exit;
            } else {
                // Validation passes, save data to the database
                $this->accounting_model->_table_name = 'tbl_chart_of_accounts'; // Table name
                $this->accounting_model->_primary_key = 'chart_of_account_id'; // Primary key
                $data = $this->accounting_model->array_from_post(array('account_type_id', 'account_sub_type_id', 'name', 'code', 'notes', 'status'));

                if (!empty($id)) {
                    $return_id = $this->accounting_model->save($data, $id);
                    $msg = lang('update_chart_of_account');
                } else {
                    $return_id = $this->accounting_model->save($data);
                    $msg = lang('save_chart_of_account');
                }

                // Log activity
                $activity = array(
                    'user' => $this->session->userdata('user_id'),
                    'module' => 'accounting',
                    'module_field_id' => $return_id,
                    'activity' => !empty($id) ? 'activity_chart_of_account_update' : 'activity_chart_of_account_create',
                    'icon' => 'fa-circle-o',
                    'value1' => $data['name']
                );
                $this->accounting_model->_table_name = 'tbl_activities';
                $this->accounting_model->_primary_key = 'activities_id';
                $this->accounting_model->save($activity);

                // Return success status
                $response = array(
                    'status' => 'success',
                    'message' => $msg,
                );
                echo json_encode($response);
                exit;
            }
        } else {
            redirect('admin/accounting/chart_of_accounts');
        }
    }

    // Callback function to check if the code already exists in the database
    public function code_exists($code)
    {
        $id = $this->input->post('chart_of_account_id'); // Get ID of current record (if any)
        $where = array('code' => $code);
        
        // Exclude current record from check if editing
        if (!empty($id)) {
            $where['chart_of_account_id !='] = $id;
        }
        
        $code_exists = $this->accounting_model->check_by($where, 'tbl_chart_of_accounts');
        
        if ($code_exists) {
            $this->form_validation->set_message('code_exists', 'The {field} field must contain a unique value.');
            return FALSE;
        }
        return TRUE;
    }

    public function delete_chart_of_account($id = null)
    {
        $deleted = can_action_by_label('chart_of_accounts', 'deleted');
        if (!empty($deleted)) {
            if (!empty($id)) {

                $account = get_row('tbl_chart_of_accounts', array('chart_of_account_id' => $id));

                $type = "success";
                $message = lang("deleted_chart_of_account");
                $action = 'activity_chart_of_account_delete';
                $activity = array(
                    'user' => $this->session->userdata('user_id'),
                    'module' => 'accounting',
                    'module_field_id' => $id,
                    'activity' => $action,
                    'icon' => 'fa-circle-o',
                    'value1' => $account->name
                );
                $this->accounting_model->_table_name = 'tbl_activities';
                $this->accounting_model->_primary_key = 'activities_id';
                $this->accounting_model->save($activity);


                $this->accounting_model->_table_name = 'tbl_chart_of_accounts'; // table name
                $this->accounting_model->_primary_key = 'chart_of_account_id'; // $id
                $this->accounting_model->delete($id);

                echo json_encode(array("status" => $type, 'message' => $message));
                exit();

            }
        } else {
            echo json_encode(array("status" => 'error', 'message' => lang('access_denied')));
            exit();
        }
    }

    public function settings($type = null)
    {
        $data['active'] = (!empty($type)) ? $type : 'account_sub_type';
        $data['title'] = lang('accounting_settings');
        $data['page_content'] = $this->load->view('admin/accounting/settings/' . $data['active'], $data, true);
        $data['subview'] = $this->load->view('admin/accounting/settings', $data, true);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function accountSubTypeList()
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/dashboard');
        }
        $this->load->model('datatables');
        $this->datatables->table = 'tbl_account_sub_type';
        $this->datatables->join_table = array('tbl_account_type');
        $this->datatables->join_where = array('tbl_account_sub_type.account_type_id = tbl_account_type.account_type_id');
        $column = array('tbl_account_sub_type.account_type_id', 'tbl_account_sub_type.account_sub_type', 'tbl_account_type.account_type', 'tbl_account_sub_type.status');
        $this->datatables->column_order = $column;
        $this->datatables->column_search = $column;
        $this->datatables->order = array('tbl_account_sub_type.account_sub_type_id' => 'desc');

        $fetch_data = make_datatables();
        $data = array();
        $edited = can_action_by_label('chart_of_accounts', 'edited');
        $deleted = can_action_by_label('chart_of_accounts', 'deleted');
        foreach ($fetch_data as $key => $row) {
            $sub_array = array();
            $sub_array[] = $row->account_sub_type;
            $sub_array[] = $row->account_type;
            $sub_array[] = ($row->status == 1) ? '<span class="label label-success">' . lang('active') . '</span>' : '<span class="label label-danger">' . lang('inactive') . '</span>';
            $action = '';
            if (!empty($edited)) {
                $action .= '<a href="' . base_url() . 'admin/accounting/new_account_sub_type/' . $row->account_sub_type_id . '" 
                               class="btn btn-primary btn-xs" title="' . lang('edit') . '" data-toggle="modal"
                               data-target="#myModal_lg"><span class="fa fa-pencil-square-o"></span></a>  ';
            }
            if (!empty($deleted)) {
                $action .= ' ' . ajax_anchor(base_url("admin/accounting/delete_account_sub_type/$row->account_sub_type_id"), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $key));
            }
            $sub_array[] = $action;
            $data[] = $sub_array;
        }
        render_table($data);
    }

    public function new_account_sub_type($id = null)
    {
        $data['title'] = lang('account_sub_type');
        $where = array('status' => 1);
        $data['account_types'] = $this->accounting_model->select_data('tbl_account_type', 'account_type_id', 'account_type', $where);
        if (!empty($id)) {
            $data['account_sub'] = get_row('tbl_account_sub_type', array('account_sub_type_id' => $id));
            if (empty($data['account_sub'])) {
                $type = "error";
                $message = lang("no_record_found");
                set_message($type, $message);
                redirect('admin/accounting/settings/account_sub_type');
            }
        }
        $data['subview'] = $this->load->view('admin/accounting/settings/new_account_sub_type', $data, false);
        $this->load->view('admin/_layout_modal_lg', $data); //page load
//        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function save_account_sub_type($id = null)
    {
        $created = can_action_by_label('chart_of_accounts', 'created');
        $edited = can_action_by_label('chart_of_accounts', 'edited');
        if (!empty($created) || !empty($edited) && !empty($id)) {
            $this->accounting_model->_table_name = 'tbl_account_sub_type'; // table name
            $this->accounting_model->_primary_key = 'account_sub_type_id'; // $id
            $data = $this->accounting_model->array_from_post(array('account_type_id', 'account_sub_type', 'status'));
            if (!empty($id)) {
                $return_id = $this->accounting_model->save($data, $id);
            } else {
                $return_id = $this->accounting_model->save($data);
            }

            if (!empty($id)) {
                $action = 'activity_account_sub_type_update';
                $msg = lang('update_account_sub_type');
            } else {
                $action = 'activity_account_sub_type_create';
                $msg = lang('save_account_sub_type');
            }
            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'accounting',
                'module_field_id' => $return_id,
                'activity' => $action,
                'icon' => 'fa-circle-o',
                'value1' => $data['account_sub_type']
            );
            $this->accounting_model->_table_name = 'tbl_activities';
            $this->accounting_model->_primary_key = 'activities_id';
            $this->accounting_model->save($activity);

            $type = "success";
            set_message($type, $msg);
            redirect('admin/accounting/settings/account_sub_type');
        } else {
            redirect('admin/dashboard');
        }
    }

    public function delete_account_sub_type($id = null)
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/dashboard');
        }

        $account = get_row('tbl_account_sub_type', array('account_sub_type_id' => $id));
        $type = "success";
        $message = lang("deleted_account_sub_type");
        $action = 'activity_account_sub_type_delete';
        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'accounting',
            'module_field_id' => $id,
            'activity' => $action,
            'icon' => 'fa-circle-o',
            'value1' => $account->account_sub_type
        );
        $this->accounting_model->_table_name = 'tbl_activities';
        $this->accounting_model->_primary_key = 'activities_id';
        $this->accounting_model->save($activity);


        $this->accounting_model->_table_name = 'tbl_account_sub_type'; // table name
        $this->accounting_model->_primary_key = 'account_sub_type_id'; // $id
        $this->accounting_model->delete($id);

        echo json_encode(array("status" => $type, 'message' => $message));
        exit();

    }

    public function save_journal_entry_settings()
    {

        $input_data = $this->accounting_model->array_from_post(array('journal_entry_prefix', 'journal_entry_prefix_start_no', 'journal_entry_format', 'increment_journal_entry'));
        $this->accounting_model->update_config($input_data);

        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'accounting',
            'module_field_id' => $this->session->userdata('user_id'),
            'activity' => ('activity_journal_entry_settings'),
            'value1' => $input_data['journal_entry_prefix']
        );
        $this->accounting_model->_table_name = 'tbl_activities';
        $this->accounting_model->_primary_key = 'activities_id';
        $this->accounting_model->save($activity);
        // messages for user
        $type = "success";
        $message = lang('save_journal_entry_settings');
        set_message($type, $message);
        redirect('admin/accounting/settings/journal_entry');
    }

    public function save_payment_voucher_settings()
    {

        $input_data = $this->accounting_model->array_from_post(array('payment_voucher_prefix', 'payment_voucher_prefix_start_no', 'payment_voucher_format', 'increment_payment_voucher'));
        $this->accounting_model->update_config($input_data);

        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'accounting',
            'module_field_id' => $this->session->userdata('user_id'),
            'activity' => ('activity_payment_voucher_settings'),
            'value1' => $input_data['payment_voucher_prefix']
        );
        $this->accounting_model->_table_name = 'tbl_activities';
        $this->accounting_model->_primary_key = 'activities_id';
        $this->accounting_model->save($activity);
        // messages for user
        $type = "success";
        $message = lang('save_payment_voucher_settings');
        set_message($type, $message);
        redirect('admin/accounting/settings/payment_voucher');
    }

    public function save_receipt_voucher_settings()
    {

        $input_data = $this->accounting_model->array_from_post(array('receipt_voucher_prefix', 'receipt_voucher_prefix_start_no', 'receipt_voucher_format', 'increment_receipt_voucher'));
        $this->accounting_model->update_config($input_data);

        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'accounting',
            'module_field_id' => $this->session->userdata('user_id'),
            'activity' => ('activity_receipt_voucher_settings'),
            'value1' => $input_data['receipt_voucher_prefix']
        );
        $this->accounting_model->_table_name = 'tbl_activities';
        $this->accounting_model->_primary_key = 'activities_id';
        $this->accounting_model->save($activity);
        // messages for user
        $type = "success";
        $message = lang('save_receipt_voucher_settings');
        set_message($type, $message);
        redirect('admin/accounting/settings/receipt_voucher');
    }

    public function payment_voucher()
    {
        $data['title'] = lang('payment_voucher');
        $data['active'] = 'payment_voucher';
        $data['subview'] = $this->load->view('admin/accounting/vouchers', $data, true);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function vouchersList($type = 'payment_voucher')
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/dashboard');
        }
        $this->load->model('datatables');
        if ($type == 'payment_voucher') {
            $table = 'tbl_payment_vouchers';
        } else {
            $table = 'tbl_receipt_vouchers';
        }
        $this->datatables->table = $table;
        $this->datatables->join_table = array('tbl_accounts');
        $this->datatables->join_where = array('tbl_accounts.account_id = ' . $table . '.account_id');
        $columns = array('reference_no', 'date', 'total_amount', 'status', 'voucher_id'); //set column field database for datatable orderable
        $this->datatables->column_order = $columns;
        $this->datatables->column_search = array('reference_no', 'date', 'total_amount', 'status'); //set column field database for datatable searchable
        $this->datatables->order = array('voucher_id' => 'desc'); // default order
        $fetch_data = make_datatables();
        $data = array();
        foreach ($fetch_data as $key => $row) {
            $sub_array = array();
            $sub_array[] = '<a href="' . base_url('admin/accounting/view_' . $type . '/' . $row->voucher_id) . '">' . $row->reference_no . '</a>';
            $sub_array[] = display_date($row->date);
            $sub_array[] = $row->account_name;
            $sub_array[] = display_money($row->total_amount);
            if ($row->status === 'pending') {
                $label = 'warning';
            } elseif ($row->status === 'approved') {
                $label = 'success';
            } elseif ($row->status === 'rejected') {
                $label = 'danger';
            } else {
                $label = 'default';
            }
            $sub_array[] = '<span class="label label-' . $label . '">' . lang($row->status) . '</span>';
            $action = ' ' . btn_edit('admin/accounting/new_' . $type . '/' . $row->voucher_id);
            $action .= ' ' . ajax_anchor(base_url("admin/accounting/delete_' . $type . '/' . $row->voucher_id"), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $key));
            $action .= ' ' . btn_view('admin/accounting/view_' . $type . '/' . $row->voucher_id);

            $sub_array[] = $action;

            $data[] = $sub_array;
        }
        render_table($data);
    }

    public function new_payment_voucher($id = null)
    {
        $data['title'] = lang('new') . ' ' . lang('payment_voucher');
        $data['active'] = 'payment_voucher';
        $data['accounts'] = $this->accounting_model->select_data('tbl_accounts', 'account_id', 'account_name');
        $data['suppliers'] = $this->accounting_model->select_data('tbl_suppliers', 'supplier_id', 'name');

        if (!empty($id)) {
            $data['payment_voucher'] = get_row('tbl_payment_vouchers', array('voucher_id' => $id));
            $data['payment_voucher_items'] = get_result('tbl_voucher_items', array('module' => 'payment', 'module_id' => $id));
        }
        $data['subview'] = $this->load->view('admin/accounting/new_payment_voucher', $data, true);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function save_payment_voucher($id = null)
    {

        $data = $this->accounting_model->array_from_post(array('date', 'reference_no', 'account_id', 'notes', 'total_amount'));
        $status = $this->input->post('status', true);
        if (empty($id)) {
            $data['created_by'] = $this->session->userdata('user_id');
            if (empty($status)) {
                $data['status'] = 'pending';
            }
        }
        $supplier_client_id = $this->input->post('supplier_client_id', true);
        $amount = $this->input->post('amount', true);
        $description = $this->input->post('description', true);


        $this->accounting_model->_table_name = "tbl_payment_vouchers"; // table name
        $this->accounting_model->_primary_key = "voucher_id"; // $id
        $voucher_id = $this->accounting_model->save($data, $id);

        if ($voucher_id) {
            $this->accounting_model->_table_name = "tbl_voucher_items"; // table name
            $this->accounting_model->_primary_key = "voucher_item_id"; // $id
            $this->accounting_model->delete_multiple(array('module' => 'payment', 'module_id' => $voucher_id));

            foreach ($supplier_client_id as $key => $value) {
                $voucher_detail = array(
                    'module' => 'payment',
                    'module_id' => $voucher_id,
                    'supplier_client_id' => $value,
                    'amount' => $amount[$key],
                    'description' => $description[$key],
                );
                $this->accounting_model->save($voucher_detail);
            }

            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'accounting',
                'module_field_id' => $id,
                'activity' => 'activity_added_new_payment_voucher',
                'icon' => 'fa-circle-o',
                'value1' => $data['reference_no'] . ' - ' . $data['total_amount']
            );
            $this->accounting_model->_table_name = 'tbl_activities';
            $this->accounting_model->_primary_key = 'activities_id';
            $this->accounting_model->save($activity);

            set_message('success', lang('save_payment_voucher'));
        } else {
            set_message('error', lang('error'));
        }
        redirect('admin/accounting/payment_voucher');
    }

    public function delete_payment_voucher($id)
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/accounting/payment_voucher');
        }
        if (!empty($id)) {
            $voucher = get_row('tbl_payment_vouchers', array('voucher_id' => $id));
            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'accounting',
                'module_field_id' => $id,
                'activity' => 'activity_deleted_payment_voucher',
                'icon' => 'fa-circle-o',
                'value1' => $voucher->reference_no . ' - ' . $voucher->total_amount
            );
            $this->accounting_model->_table_name = 'tbl_activities';
            $this->accounting_model->_primary_key = 'activities_id';
            $this->accounting_model->save($activity);


            $this->accounting_model->_table_name = "tbl_payment_vouchers"; // table name
            $this->accounting_model->_primary_key = "voucher_id"; // $id
            $this->accounting_model->delete($id);

            $this->accounting_model->_table_name = "tbl_voucher_items"; // table name
            $this->accounting_model->_primary_key = "voucher_item_id"; // $id
            $this->accounting_model->delete_multiple(array('module' => 'payment', 'module_id' => $id));
            $type = 'success';
            $message = lang('payment_voucher_deleted_successfully');

        } else {
            $type = 'error';
            $message = lang('payment_voucher_deleted_failed');
        }
        echo json_encode(array("status" => $type, 'message' => $message));
        exit();
    }

    public function receipt_voucher()
    {
        $data['title'] = lang('receipt_voucher');
        $data['active'] = 'receipt_voucher';
        $data['link'] = 'receipt';
        $data['subview'] = $this->load->view('admin/accounting/vouchers', $data, true);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function new_receipt_voucher($id = null)
    {
        $data['title'] = lang('new') . ' ' . lang('receipt_voucher');
        $data['active'] = 'receipt_voucher';
        $data['accounts'] = $this->accounting_model->select_data('tbl_accounts', 'account_id', 'account_name');
        $data['clients'] = $this->accounting_model->select_data('tbl_client', 'client_id', 'name');

        if (!empty($id)) {
            $data['receipt_voucher'] = get_row('tbl_receipt_vouchers', array('voucher_id' => $id));
            $data['receipt_voucher_items'] = get_result('tbl_voucher_items', array('module' => 'receipt', 'module_id' => $id));
        }
        $data['subview'] = $this->load->view('admin/accounting/new_receipt_voucher', $data, true);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function save_receipt_voucher($id = null)
    {

        $data = $this->accounting_model->array_from_post(array('date', 'reference_no', 'account_id', 'notes', 'total_amount'));
        $status = $this->input->post('status', true);
        if (empty($id)) {
            $data['created_by'] = $this->session->userdata('user_id');
            if (empty($status)) {
                $data['status'] = 'pending';
            }
        }
        $supplier_client_id = $this->input->post('supplier_client_id', true);
        $amount = $this->input->post('amount', true);
        $description = $this->input->post('description', true);


        $this->accounting_model->_table_name = "tbl_receipt_vouchers"; // table name
        $this->accounting_model->_primary_key = "voucher_id"; // $id
        $voucher_id = $this->accounting_model->save($data, $id);

        if ($voucher_id) {
            $this->accounting_model->_table_name = "tbl_voucher_items"; // table name
            $this->accounting_model->_primary_key = "voucher_item_id"; // $id
            $this->accounting_model->delete_multiple(array('module' => 'receipt', 'module_id' => $voucher_id));

            foreach ($supplier_client_id as $key => $value) {
                $voucher_detail = array(
                    'module' => 'receipt',
                    'module_id' => $voucher_id,
                    'supplier_client_id' => $value,
                    'amount' => $amount[$key],
                    'description' => $description[$key],
                );
                $this->accounting_model->save($voucher_detail);
            }

            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'accounting',
                'module_field_id' => $id,
                'activity' => 'activity_added_new_receipt_voucher',
                'icon' => 'fa-circle-o',
                'value1' => $data['reference_no'] . ' - ' . $data['total_amount']
            );
            $this->accounting_model->_table_name = 'tbl_activities';
            $this->accounting_model->_primary_key = 'activities_id';
            $this->accounting_model->save($activity);

            set_message('success', lang('save_receipt_voucher'));
        } else {
            set_message('error', lang('error'));
        }
        redirect('admin/accounting/receipt_voucher');
    }

    public function delete_receipt_voucher($id)
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/accounting/receipt_voucher');
        }
        if (!empty($id)) {
            $voucher = get_row('tbl_receipt_vouchers', array('voucher_id' => $id));
            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'accounting',
                'module_field_id' => $id,
                'activity' => 'activity_deleted_receipt_voucher',
                'icon' => 'fa-circle-o',
                'value1' => $voucher->reference_no . ' - ' . $voucher->total_amount
            );
            $this->accounting_model->_table_name = 'tbl_activities';
            $this->accounting_model->_primary_key = 'activities_id';
            $this->accounting_model->save($activity);


            $this->accounting_model->_table_name = "tbl_receipt_vouchers"; // table name
            $this->accounting_model->_primary_key = "voucher_id"; // $id
            $this->accounting_model->delete($id);

            $this->accounting_model->_table_name = "tbl_voucher_items"; // table name
            $this->accounting_model->_primary_key = "voucher_item_id"; // $id
            $this->accounting_model->delete_multiple(array('module' => 'receipt', 'module_id' => $id));
            $type = 'success';
            $message = lang('receipt_voucher_deleted_successfully');

        } else {
            $type = 'error';
            $message = lang('receipt_voucher_deleted_failed');
        }
        echo json_encode(array("status" => $type, 'message' => $message));
        exit();
    }

    public function view_receipt_voucher($id, $pdf = null)
    {
        $data['active'] = 'receipt_voucher';

        $data['title'] = lang('view') . ' ' . lang('receipt_voucher');
        $data['sales_info'] = join_data('tbl_receipt_vouchers', 'tbl_receipt_vouchers.*,tbl_accounts.account_name', array('voucher_id' => $id),
            array('tbl_accounts' => 'tbl_accounts.account_id=tbl_receipt_vouchers.account_id'));
        if (!empty($data['sales_info'])) {
            $data['sales_info']->ref_no = lang('receipt') . ' : ' . $data['sales_info']->reference_no;
            $data['sales_info']->start_date = lang('date') . ' : ' . display_date($data['sales_info']->date);
            $data['sales_info']->sales_agent = lang('created_by') . ' : ' . fullname($data['sales_info']->created_by);
            if ($data['sales_info']->status === 'pending') {
                $label = 'warning';
            } elseif ($data['sales_info']->status === 'approved') {
                $label = 'success';
            } elseif ($data['sales_info']->status === 'rejected') {
                $label = 'danger';
            } else {
                $label = 'default';
            }
            $data['sales_info']->status = lang('status') . ' :  <span class="label label-' . $label . '">' . lang($data['sales_info']->status) . '</span>';
            $data['sales_info']->custom_field = '';
            $data['sales_info']->sub_total = $data['sales_info']->total_amount;
            $data['sales_info']->total = $data['sales_info']->total_amount;
            $data['sales_info']->company_heading = lang('paid_from');
            $data['sales_info']->name = lang('account') . ' : ' . $data['sales_info']->account_name;

            $data['receipt_items'] = join_data('tbl_voucher_items', 'tbl_voucher_items.*,tbl_client.name', array('module' => 'receipt', 'module_id' => $id),
                array('tbl_client' => 'tbl_client.client_id=tbl_voucher_items.supplier_client_id'), 'object');
            if (!empty($pdf)) {
                $data['sales_info']->item_layout = 'admin/accounting/view_receipt_items_pdf';
            } else {
                $data['sales_info']->item_layout = 'admin/accounting/view_receipt_items';
            }

        } else {
            set_message('error', 'No data Found');
            redirect('admin/accounting/payment_voucher');
        }
        if (!empty($pdf)) {
            $this->common_model->sales_pdf($data, $pdf);
        }
        $data['subview'] = $this->load->view('admin/accounting/view_receipt_voucher', $data, true);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function voucher_pdf($type, $id)
    {
        $view = 'view_' . $type;
        $this->$view($id, true);
    }

    public function view_payment_voucher($id, $pdf = false)
    {

        $data['active'] = 'payment_voucher';

        $data['title'] = lang('view') . ' ' . lang('payment_voucher');
        $data['sales_info'] = join_data('tbl_payment_vouchers', 'tbl_payment_vouchers.*,tbl_accounts.account_name', array('voucher_id' => $id),
            array('tbl_accounts' => 'tbl_accounts.account_id=tbl_payment_vouchers.account_id'));
        if (!empty($data['sales_info'])) {
            $data['sales_info']->ref_no = lang('payment') . ' : ' . $data['sales_info']->reference_no;
            $data['sales_info']->start_date = lang('date') . ' : ' . display_date($data['sales_info']->date);
            $data['sales_info']->sales_agent = lang('created_by') . ' : ' . fullname($data['sales_info']->created_by);
            $data['sales_info']->end_date = '';
            if ($data['sales_info']->status === 'pending') {
                $label = 'warning';
            } elseif ($data['sales_info']->status === 'approved') {
                $label = 'success';
            } elseif ($data['sales_info']->status === 'rejected') {
                $label = 'danger';
            } else {
                $label = 'default';
            }
            $data['sales_info']->status = lang('status') . ' :  <span class="label label-' . $label . '">' . lang($data['sales_info']->status) . '</span>';
            $data['sales_info']->custom_field = '';
            $data['sales_info']->sub_total = $data['sales_info']->total_amount;
            $data['sales_info']->total = $data['sales_info']->total_amount;
            $data['sales_info']->company_heading = lang('paid_from');
            $data['sales_info']->name = lang('account') . ' : ' . $data['sales_info']->account_name;

            $data['receipt_items'] = join_data('tbl_voucher_items', 'tbl_voucher_items.*,tbl_suppliers.name', array('module' => 'payment', 'module_id' => $id),
                array('tbl_suppliers' => 'tbl_suppliers.supplier_id=tbl_voucher_items.supplier_client_id'), 'object');

            if (!empty($pdf)) {
                $data['sales_info']->item_layout = 'admin/accounting/view_receipt_items_pdf';
            } else {
                $data['sales_info']->item_layout = 'admin/accounting/view_receipt_items';
            }

        } else {
            set_message('error', 'No data Found');
            redirect('admin/accounting/payment_voucher');
        }
        if (!empty($pdf)) {
            $this->common_model->sales_pdf($data, $pdf);
        }

        $data['subview'] = $this->load->view('admin/accounting/view_receipt_voucher', $data, true);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function change_status($type, $id, $status)
    {
        $table_id = 'voucher_id';
        if ($type === 'receipt_voucher') {
            $table = 'tbl_receipt_vouchers';
        } elseif ($type === 'payment_voucher') {
            $table = 'tbl_payment_vouchers';
        } else if ($type === 'journal_entry') {
            $table = 'tbl_journals';
            $table_id = 'journal_id';
        }

        $this->accounting_model->_table_name = $table; // table name
        $this->accounting_model->_primary_key = $table_id; // $id
        $this->accounting_model->save(array('status' => $status), $id);
        $voucher = get_row($table, array($table_id => $id));
        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'accounting',
            'module_field_id' => $id,
            'activity' => 'activity_change_status_' . $type,
            'icon' => 'fa-circle-o',
            'value1' => $voucher->reference_no . ' ' . lang('of') . ' ' . lang($type),
            'value2' => lang($status)
        );
        $this->accounting_model->_table_name = 'tbl_activities';
        $this->accounting_model->_primary_key = 'activities_id';
        $this->accounting_model->save($activity);
        set_message('success', lang('status_changed_successfully'));
        redirect('admin/accounting/' . $type);
    }

    public function reports($type = null)
    {
        $data['active'] = 'reports';
        $data['title'] = lang('reports');
        if (!empty($type)) {
            $data['title'] = lang('reports') . ' ' . lang($type);
            $data['type'] = $type;
            $get = 'get_' . $type;
            $data['all_data'] = $this->accounting_model->$get();
            if ($type === 'ledger_report') {
                $data['account_id'] = $this->input->post('account_id', true);
                $chart_of_account = get_row('tbl_chart_of_accounts', array('chart_of_account_id' => $data['account_id']));
                $data['account_name'] = $chart_of_account->name . ' (' . $chart_of_account->code . ')';
                $data['chart_of_accounts'] = get_result('tbl_chart_of_accounts', array('status' => 1));
            }
            $start_date = date('Y-m-d', strtotime('-1 year'));
            $end_date = date('Y-m-d');
            if ($this->input->post()) {
                $range = explode('-', $this->input->post('range', true));
                if (!empty($range[0])) {
                    $start_date = date('Y-m-d', strtotime($range[0]));
                    $end_date = date('Y-m-d', strtotime($range[1]));
                    $data['range'] = array($start_date, $end_date);
                }
            } else {
                $range = array($start_date, $end_date);
                $data['range'] = $range;
            }
        } else {
            $data['all_data'] = $this->accounting_model->get_all_account_balance();
        }
        $data['subview'] = $this->load->view('admin/accounting/reports', $data, true);
        $this->load->view('admin/_layout_main', $data); //page load
    }


}