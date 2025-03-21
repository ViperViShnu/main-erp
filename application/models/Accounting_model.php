<?php

/**
 * Description of Project_Model
 *
 * @author NaYeM
 */
class Accounting_model extends MY_Model
{

    public $_table_name;
    public $_order_by;
    public $_primary_key;


    public function generate_journal_entry_prefix()
    {
        $strlen = strlen(config_item('journal_entry_prefix_start_no'));
        $query = $this->db->query('SELECT reference_no, journal_id FROM tbl_journals WHERE journal_id = (SELECT MAX(journal_id) FROM tbl_journals)');
        if ($query->num_rows() > 0) {
            $row = $query->row();
            $ref_number = intval(substr($row->reference_no, -$strlen));
            $next_number = ++$row->journal_id;
            if ($next_number < $ref_number) {
                $next_number = $ref_number + 1;
            }
            if ($ref_number < config_item('journal_entry_prefix_start_no')) {
                $next_number = config_item('journal_entry_prefix_start_no');
            }
            $next_number = $this->journal_entry_reference_no_exists($next_number);

            $next_number = sprintf('%04d', $next_number);
        } else {
            $next_number = sprintf('%04d', config_item('journal_entry_prefix_start_no'));
        }
        if (!empty(config_item('journal_entry_format'))) {
            $invoice_format = config_item('journal_entry_format');
            $journal_entry_prefix = str_replace("[" . config_item('journal_entry_prefix') . "]", config_item('journal_entry_prefix'), $invoice_format);
            $yyyy = str_replace("[yyyy]", date('Y'), $journal_entry_prefix);
            $yy = str_replace("[yy]", date('y'), $yyyy);
            $mm = str_replace("[mm]", date('M'), $yy);
            $m = str_replace("[m]", date('m'), $mm);
            $dd = str_replace("[dd]", date('d'), $m);
            $next_number = str_replace("[number]", $next_number, $dd);
        }
        return $next_number;
    }

    public function journal_entry_reference_no_exists($next_number)
    {
        $enext_number = sprintf('%04d', $next_number);
        if (!empty(config_item('journal_entry_format'))) {
            $invoice_format = config_item('journal_entry_format');
            $journal_entry_prefix = str_replace("[" . config_item('journal_entry_prefix') . "]", config_item('journal_entry_prefix'), $invoice_format);
            $yyyy = str_replace("[yyyy]", date('Y'), $journal_entry_prefix);
            $yy = str_replace("[yy]", date('y'), $yyyy);
            $mm = str_replace("[mm]", date('M'), $yy);
            $m = str_replace("[m]", date('m'), $mm);
            $dd = str_replace("[dd]", date('d'), $m);
            $enext_number = str_replace("[number]", $next_number, $dd);
        }
        $records = $this->db->where('reference_no', $enext_number)->get('tbl_journals')->num_rows();
        if ($records > 0) {
            return $this->journal_entry_reference_no_exists($next_number + 1);
        } else {
            return $next_number;
        }
    }

    public function generate_payment_voucher_prefix()
    {
        $strlen = strlen(config_item('payment_voucher_prefix_start_no'));
        $query = $this->db->query('SELECT reference_no, voucher_id FROM tbl_payment_vouchers WHERE voucher_id = (SELECT MAX(voucher_id) FROM tbl_payment_vouchers)');
        if ($query->num_rows() > 0) {
            $row = $query->row();
            $ref_number = intval(substr($row->reference_no, -$strlen));
            $next_number = ++$row->voucher_id;
            if ($next_number < $ref_number) {
                $next_number = $ref_number + 1;
            }
            if ($ref_number < config_item('payment_voucher_prefix_start_no')) {
                $next_number = config_item('payment_voucher_prefix_start_no');
            }
            $next_number = $this->payment_voucher_reference_no_exists($next_number);

            $next_number = sprintf('%04d', $next_number);
        } else {
            $next_number = sprintf('%04d', config_item('payment_voucher_prefix_start_no'));
        }
        if (!empty(config_item('payment_voucher_format'))) {
            $invoice_format = config_item('payment_voucher_format');
            $payment_voucher_prefix = str_replace("[" . config_item('payment_voucher_prefix') . "]", config_item('payment_voucher_prefix'), $invoice_format);
            $yyyy = str_replace("[yyyy]", date('Y'), $payment_voucher_prefix);
            $yy = str_replace("[yy]", date('y'), $yyyy);
            $mm = str_replace("[mm]", date('M'), $yy);
            $m = str_replace("[m]", date('m'), $mm);
            $dd = str_replace("[dd]", date('d'), $m);
            $next_number = str_replace("[number]", $next_number, $dd);
        }
        return $next_number;
    }

    public function payment_voucher_reference_no_exists($next_number)
    {
        $enext_number = sprintf('%04d', $next_number);
        if (!empty(config_item('payment_voucher_format'))) {
            $invoice_format = config_item('payment_voucher_format');
            $payment_voucher_prefix = str_replace("[" . config_item('payment_voucher_prefix') . "]", config_item('payment_voucher_prefix'), $invoice_format);
            $yyyy = str_replace("[yyyy]", date('Y'), $payment_voucher_prefix);
            $yy = str_replace("[yy]", date('y'), $yyyy);
            $mm = str_replace("[mm]", date('M'), $yy);
            $m = str_replace("[m]", date('m'), $mm);
            $dd = str_replace("[dd]", date('d'), $m);
            $enext_number = str_replace("[number]", $next_number, $dd);
        }
        $records = $this->db->where('reference_no', $enext_number)->get('tbl_payment_vouchers')->num_rows();
        if ($records > 0) {
            return $this->payment_voucher_reference_no_exists($next_number + 1);
        } else {
            return $next_number;
        }
    }


    public function generate_receipt_voucher_prefix()
    {
        $strlen = strlen(config_item('receipt_voucher_prefix_start_no'));
        $query = $this->db->query('SELECT reference_no, voucher_id FROM tbl_receipt_vouchers WHERE voucher_id = (SELECT MAX(voucher_id) FROM tbl_receipt_vouchers)');
        if ($query->num_rows() > 0) {
            $row = $query->row();
            $ref_number = intval(substr($row->reference_no, -$strlen));
            $next_number = ++$row->voucher_id;
            if ($next_number < $ref_number) {
                $next_number = $ref_number + 1;
            }
            if ($ref_number < config_item('receipt_voucher_prefix_start_no')) {
                $next_number = config_item('receipt_voucher_prefix_start_no');
            }
            $next_number = $this->receipt_voucher_reference_no_exists($next_number);

            $next_number = sprintf('%04d', $next_number);
        } else {
            $next_number = sprintf('%04d', config_item('receipt_voucher_prefix_start_no'));
        }
        if (!empty(config_item('receipt_voucher_format'))) {
            $invoice_format = config_item('receipt_voucher_format');
            $receipt_voucher_prefix = str_replace("[" . config_item('receipt_voucher_prefix') . "]", config_item('receipt_voucher_prefix'), $invoice_format);
            $yyyy = str_replace("[yyyy]", date('Y'), $receipt_voucher_prefix);
            $yy = str_replace("[yy]", date('y'), $yyyy);
            $mm = str_replace("[mm]", date('M'), $yy);
            $m = str_replace("[m]", date('m'), $mm);
            $dd = str_replace("[dd]", date('d'), $m);
            $next_number = str_replace("[number]", $next_number, $dd);
        }
        return $next_number;
    }

    public function receipt_voucher_reference_no_exists($next_number)
    {
        $enext_number = sprintf('%04d', $next_number);
        if (!empty(config_item('receipt_voucher_format'))) {
            $invoice_format = config_item('receipt_voucher_format');
            $receipt_voucher_prefix = str_replace("[" . config_item('receipt_voucher_prefix') . "]", config_item('receipt_voucher_prefix'), $invoice_format);
            $yyyy = str_replace("[yyyy]", date('Y'), $receipt_voucher_prefix);
            $yy = str_replace("[yy]", date('y'), $yyyy);
            $mm = str_replace("[mm]", date('M'), $yy);
            $m = str_replace("[m]", date('m'), $mm);
            $dd = str_replace("[dd]", date('d'), $m);
            $enext_number = str_replace("[number]", $next_number, $dd);
        }
        $records = $this->db->where('reference_no', $enext_number)->get('tbl_receipt_vouchers')->num_rows();
        if ($records > 0) {
            return $this->receipt_voucher_reference_no_exists($next_number + 1);
        } else {
            return $next_number;
        }
    }

    public function get_chart_of_accounts_by_search($search)
    {
        // get result from tbl_account_type,tbl_account_sub_type,tbl_chart_of_accounts
        $this->db->select('tbl_chart_of_accounts.*,tbl_account_type.account_type,tbl_account_sub_type.account_sub_type');
        $this->db->from('tbl_chart_of_accounts');
        $this->db->join('tbl_account_type', 'tbl_account_type.account_type_id = tbl_chart_of_accounts.account_type_id', 'left');
        $this->db->join('tbl_account_sub_type', 'tbl_account_sub_type.account_sub_type_id = tbl_chart_of_accounts.account_sub_type_id', 'left');
        $this->db->where('tbl_chart_of_accounts.code LIKE', '%' . $search . '%');
        $this->db->where('tbl_chart_of_accounts.name LIKE', '%' . $search . '%');
        $this->db->or_where('tbl_account_type.account_type LIKE', '%' . $search . '%');
        $this->db->or_where('tbl_account_sub_type.account_sub_type LIKE', '%' . $search . '%');
        $this->db->order_by('tbl_chart_of_accounts.name', 'asc');
        $query = $this->db->get();
        return $query->result();
    }

    public function get_trial_balance()
    {
        // get trial balance from tbl_journals,tbl_journal_items and tbl_chart_of_accounts,tbl_account_type,tbl_account_sub_type
        // if date is empty then takes 1 year date
        $range = explode('-', $this->input->post('range', true));
        if (!empty($range[0])) {
            $start_date = date('Y-m-d', strtotime($range[0]));
            $end_date = date('Y-m-d', strtotime($range[1]));
        } else {
            $start_date = date('Y-m-d', strtotime('-1 year'));
            $end_date = date('Y-m-d');
        }

        $this->db->select('tbl_journal_items.*,tbl_journals.*,tbl_chart_of_accounts.name,tbl_chart_of_accounts.code');
        $this->db->from('tbl_journal_items');
        $this->db->join('tbl_journals', 'tbl_journal_items.journal_id = tbl_journals.journal_id', 'left');
        $this->db->join('tbl_chart_of_accounts', 'tbl_journal_items.chart_of_account_id = tbl_chart_of_accounts.chart_of_account_id', 'left');
        $this->db->where('tbl_journals.date >=', $start_date);
        $this->db->where('tbl_journals.date <=', $end_date);
        $this->db->order_by('tbl_chart_of_accounts.name', 'asc');
        $query = $this->db->get();
        $accounts = $query->result();
        $data = array();
        foreach ($accounts as $account) {
            $data[$account->code]['name'] = $account->name;
            $data[$account->code]['code'] = $account->code;
            $data[$account->code]['debit'] += $account->debit;
            $data[$account->code]['credit'] += $account->credit;
            $data[$account->code]['balance'] += $account->debit - $account->credit;
        }
        return $data;
    }

    public function get_ledger_report()
    {
        $account_id = $this->input->post('account_id', true);
        if (empty($account_id)) {
            // get first account id from tbl_chart_of_accounts
            $account = $this->db->order_by('chart_of_account_id', 'asc')->limit(1)->get('tbl_chart_of_accounts')->row();
            $account_id = $account->chart_of_account_id;
        }
        $range = explode('-', $this->input->post('range', true));
        if (!empty($range[0])) {
            $start_date = date('Y-m-d', strtotime($range[0]));
            $end_date = date('Y-m-d', strtotime($range[1]));
        } else {
            $start_date = date('Y-m-d', strtotime('-1 year'));
            $end_date = date('Y-m-d');
        }
        $this->db->select('tbl_journal_items.*,tbl_journals.*,tbl_chart_of_accounts.name,tbl_chart_of_accounts.code,tbl_account_details.fullname');
        $this->db->from('tbl_journal_items');
        $this->db->join('tbl_journals', 'tbl_journal_items.journal_id = tbl_journals.journal_id', 'left');
        $this->db->join('tbl_chart_of_accounts', 'tbl_journal_items.chart_of_account_id = tbl_chart_of_accounts.chart_of_account_id', 'left');
        $this->db->join('tbl_account_details', 'tbl_journals.created_by = tbl_account_details.user_id', 'left');
        $this->db->where('tbl_journals.date >=', $start_date);
        $this->db->where('tbl_journals.date <=', $end_date);
        $this->db->where('tbl_journal_items.chart_of_account_id', $account_id);
        $this->db->order_by('tbl_chart_of_accounts.name', 'asc');
        $query = $this->db->get();
        return $query->result();

    }

    public function get_balance_sheet()
    {
        $range = explode('-', $this->input->post('range', true));
        if (!empty($range[0])) {
            $start_date = date('Y-m-d', strtotime($range[0]));
            $end_date = date('Y-m-d', strtotime($range[1]));
        } else {
            $start_date = date('Y-m-d', strtotime('-1 year'));
            $end_date = date('Y-m-d');
        }
        $this->db->select('tbl_journal_items.*,tbl_journals.*,tbl_chart_of_accounts.name,tbl_chart_of_accounts.code,tbl_account_type.account_type,tbl_account_sub_type.account_sub_type');
        $this->db->from('tbl_journal_items');
        $this->db->join('tbl_journals', 'tbl_journal_items.journal_id = tbl_journals.journal_id', 'left');
        $this->db->join('tbl_chart_of_accounts', 'tbl_journal_items.chart_of_account_id = tbl_chart_of_accounts.chart_of_account_id', 'left');
        $this->db->join('tbl_account_type', 'tbl_chart_of_accounts.account_type_id = tbl_account_type.account_type_id', 'left');
        $this->db->join('tbl_account_sub_type', 'tbl_chart_of_accounts.account_sub_type_id = tbl_account_sub_type.account_sub_type_id', 'left');
        $this->db->where('tbl_journals.date >=', $start_date);
        $this->db->where('tbl_journals.date <=', $end_date);
        $this->db->order_by('tbl_chart_of_accounts.name', 'asc');
        $query = $this->db->get();
        $accounts = $query->result();
        $data = array();
        foreach ($accounts as $account) {
            $data[$account->account_type]['name'] = $account->account_type;
            $data[$account->account_type]['balance'] += $account->debit - $account->credit;
            $data[$account->account_type]['debit'] += $account->debit;
            $data[$account->account_type]['credit'] += $account->credit;

            $data[$account->account_type]['sub'][$account->account_sub_type]['name'] = $account->account_sub_type;
            $data[$account->account_type]['sub'][$account->account_sub_type]['balance'] += $account->debit - $account->credit;
            $data[$account->account_type]['sub'][$account->account_sub_type]['debit'] += $account->debit;
            $data[$account->account_type]['sub'][$account->account_sub_type]['credit'] += $account->credit;
            $data[$account->account_type]['sub'][$account->account_sub_type]['accounts'][$account->code]['name'] = $account->name;
            $data[$account->account_type]['sub'][$account->account_sub_type]['accounts'][$account->code]['code'] = $account->code;
            $data[$account->account_type]['sub'][$account->account_sub_type]['accounts'][$account->code]['debit'] += $account->debit;
            $data[$account->account_type]['sub'][$account->account_sub_type]['accounts'][$account->code]['credit'] += $account->credit;
            $data[$account->account_type]['sub'][$account->account_sub_type]['accounts'][$account->code]['balance'] += $account->debit - $account->credit;
        }
        return $data;
    }

    public function get_all_account_balance()
    {
        $this->db->select('tbl_chart_of_accounts.*,tbl_account_type.account_type,tbl_account_sub_type.account_sub_type');
        $this->db->from('tbl_chart_of_accounts');
        $this->db->join('tbl_account_type', 'tbl_chart_of_accounts.account_type_id = tbl_account_type.account_type_id', 'left');
        $this->db->join('tbl_account_sub_type', 'tbl_chart_of_accounts.account_sub_type_id = tbl_account_sub_type.account_sub_type_id', 'left');
        $this->db->order_by('tbl_account_type.account_type', 'asc');
        $query = $this->db->get();
        $accounts = $query->result();
        $data = array();
        foreach ($accounts as $account) {
            // get data from journal items table by chart of account id
            $this->db->select('SUM(debit) as debit,SUM(credit) as credit');
            $this->db->from('tbl_journal_items');
            $this->db->where('chart_of_account_id', $account->chart_of_account_id);
            $query = $this->db->get();
            $journal_items = $query->row();
            $data[$account->account_type]['name'] = $account->account_type;
            $data[$account->account_type]['balance'] += $journal_items->debit - $journal_items->credit;
            $data[$account->account_type]['debit'] += $journal_items->debit;
            $data[$account->account_type]['credit'] += $journal_items->credit;
            $data[$account->account_type]['sub'][$account->account_sub_type]['name'] = $account->account_sub_type;
            $data[$account->account_type]['sub'][$account->account_sub_type]['balance'] += $journal_items->debit - $journal_items->credit;
            $data[$account->account_type]['sub'][$account->account_sub_type]['debit'] += $journal_items->debit;
            $data[$account->account_type]['sub'][$account->account_sub_type]['credit'] += $journal_items->credit;
        }
        return $data;


    }

}
