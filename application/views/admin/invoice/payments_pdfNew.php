<!DOCTYPE html>
<html lang="en">
<head>
    <title>Payments PDF</title>
    <meta charset="UTF-8">
    <?php
    $direction = $this->session->userdata('direction') ?? config_item('RTL');
    $RTL = !empty($direction) && $direction === 'rtl' ? 'on' : '';
    ?>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            min-width: 98%;
            min-height: 100%;
            overflow: hidden;
            text-align: <?php echo !empty($RTL) ? 'right' : 'left'; ?>;
        }

        h4 {
            font-size: 16px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 8px;
            text-align: <?php echo !empty($RTL) ? 'right' : 'left'; ?>;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        .header, .header img {
            margin-bottom: 20px;
        }

        .summary {
            text-align: center;
            background-color: #1B9BA0;
            color: white;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .summary span {
            display: block;
            font-size: 14pt;
        }

        .notes {
            color: #777;
            background-color: #f5f5f5;
            border: 1px solid #e3e3e3;
            padding: 10px;
            border-radius: 4px;
        }

        .second-table td, .second-table th {
            text-align: center;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <?php
    if (!empty($all_invoices_info)) {
        foreach ($all_invoices_info as $v_invoice) {
            if (!empty($v_invoice)) {
                $all_payment_info = $this->db->where('invoices_id', $v_invoice->invoices_id)->get('tbl_payments')->result();
                if (!empty($all_payment_info)):
                    foreach ($all_payment_info as $v_payments_info):
                        $client_info = $this->invoice_model->check_by(['client_id' => $v_payments_info->paid_by], 'tbl_client');
                    endforeach;
                endif;
            }
        }
    }
    ?>

    <?php
    $invoice_info = $this->invoice_model->check_by(array('invoices_id' => $payments_info->invoices_id), 'tbl_invoices');
    if (empty($invoice_info)) {
        $invoice_info = new stdClass();
        $invoice_info->adjustment = 0;
        $invoice_info->client_id = 0;
        $invoice_info->date_saved = 0;
        $invoice_info->invoices_id = 0;
        $invoice_info->reference_no = '-';

    }
    $client_info = $this->invoice_model->check_by(array('client_id' => $payments_info->paid_by), 'tbl_client');
    if (is_numeric($payments_info->payment_method)) {
        $payment_methods = $this->invoice_model->check_by(array('payment_methods_id' => $payments_info->payment_method), 'tbl_payment_methods');
    } else {
        $payment_methods = $payments_info->payment_method;
    }
    if (!empty($client_info->client_currency_on_invoice)) {
        $currency = $this->invoice_model->check_by(array('code' => $payments_info->client_currency), 'tbl_currencies');
        if (empty($currency)) {
            $currency = $this->invoice_model->check_by(array('code' => $client_info->currency), 'tbl_currencies');
        }
    } else if (!empty($payments_info->system_currency)) {
        $currency = $this->invoice_model->check_by(array('code' => $payments_info->system_currency), 'tbl_currencies');
    }
    if (empty($currency)) {
        $currency = $this->invoice_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');
    }
    $img = base_url() . config_item('invoice_logo');
    //$a = file_exists($img);
    //if (empty($a)) {
    //    $img = ROOTPATH . '/' . config_item('invoice_logo');
    //}
    //if (!file_exists($img)) {
    //    $img = ROOTPATH . '/' . 'uploads/default_logo.png';
    //}
    ?>
    <div class="header">
        <table>
            <tr>
                <td style="width: 60px;">
                    <img src="<?= base_url() . config_item('invoice_logo') ?>" style="width: 60px;">
                </td>
                <td>
                    <h3><?= config_item('company_name') ?></h3>
                    <p><?= $this->config->item('company_address') ?></p>
                </td>
            </tr>
        </table>
    </div>

    <div class="summary">
        <span><?= lang('amount_received') ?></span>
        <span><?= display_money($payments_info->amount, $currency->symbol) ?></span>
    </div>

    <table>
        <tr>
            <th><?= lang('payment_date') ?></th>
            <td><?= strftime(config_item('date_format'), strtotime($payments_info->payment_date)) ?></td>
        </tr>
        <?php if (config_item('amount_to_words') == 'Yes'): ?>
        <tr>
            <th><?= lang('num_word') ?></th>
            <td><?= number_to_word($currency->code, $payments_info->amount) ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <th><?= lang('transaction_id') ?></th>
            <td><?= $payments_info->trans_id ?></td>
        </tr>
        <tr>
            <th><?= lang('received_from') ?></th>
            <td><?= ucfirst($client_info->name) ?></td>
        </tr>
        <?php
        $role = $this->session->userdata('user_type');
        if ($role == 1 && $payments_info->account_id != 0): 
        $account_info = $this->invoice_model->check_by(array('account_id' => $payments_info->account_id), 'tbl_accounts');
        if(!empty($account_info)) { ?>
            <tr>
                <th><?= lang('received_account') ?></th>
                <td><?= $account_info->account_name ?></td>
            </tr>
        <?php } endif; ?>
        <tr>
            <th><?= lang('payment_mode') ?></th>
            <td><?= !empty($payment_methods->method_name) ? $payment_methods->method_name : '-' ?></td>
        </tr>
        <tr>
            <th><?= lang('notes') ?></th>
            <td><?= strip_html_tags($payments_info->notes, true) ?></td>
        </tr>
    </table>

    <?php $invoice_due = $this->invoice_model->calculate_to('invoice_due', $payments_info->invoices_id); ?>
    <h4><?= lang('payment_for') ?></h4>
    <table class="second-table">
        <thead>
        <tr>
            <th><?= lang('invoice_code') ?></th>
            <th><?= lang('invoice_date') ?></th>
            <th><?= lang('invoice_amount') ?></th>
            <th><?= lang('paid_amount') ?></th>
            <?php if ($invoice_due > 0): ?>
            <th style="color: red;"><?= lang('due_amount') ?></th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><?= $invoice_info->reference_no ?></td>
            <td><?= strftime(config_item('date_format'), strtotime($invoice_info->date_saved)) ?></td>
            <!-- <td><?= display_money($this->invoice_model->get_invoice_cost($payments_info->invoices_id), $currency->symbol) ?></td> -->
            <td><?= display_money($this->invoice_model->calculate_to('total', $payments_info->invoices_id), $currency->symbol) ?></td>
            <td><?= display_money($payments_info->amount, $currency->symbol) ?></td>
            <?php if ($invoice_due > 0): ?>
            <td style="color: red;"><?= display_money($invoice_due, $currency->symbol) ?></td>
            <?php endif; ?>
        </tr>
        </tbody>
    </table>
</body>
</html>