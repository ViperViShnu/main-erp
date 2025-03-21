<!DOCTYPE html>
<html>
<head>
    <title>Payment Receipt</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <?php
    $direction = $this->session->userdata('direction');
    $RTL = (!empty($direction) && $direction == 'rtl') ? 'on' : config_item('RTL');
    ?>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            margin: 20px;
            <?php if (!empty($RTL)) { ?> direction: rtl; <?php } ?>
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header img {
            width: 80px;
            margin-bottom: 10px;
        }

        .header .company-name {
            font-size: 22px;
            font-weight: bold;
        }

        .header .company-address {
            font-size: 14px;
            color: #555;
        }

        .summary {
            text-align: center;
            background-color: #1B9BA0;
            color: #fff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .summary h2 {
            margin: 0;
            font-size: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: <?php echo !empty($RTL) ? 'right' : 'left'; ?>;
        }

        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #555;
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
<?php
    $purchase_info = $this->purchase_model->check_by(array('purchase_id' => $payments_info->purchase_id), 'tbl_purchases');
    $supplier_info = $this->purchase_model->check_by(array('supplier_id' => $payments_info->paid_to), 'tbl_suppliers');

    if (is_numeric($payments_info->payment_method)) {
        $payment_methods = $this->purchase_model->check_by(array('payment_methods_id' => $payments_info->payment_method), 'tbl_payment_methods');
    } else {
        $payment_methods->method_name = $payments_info->payment_method;
}
?>
    <div class="header">
        <img src="<?= base_url() . config_item('invoice_logo') ?>" alt="Company Logo">
        <div class="company-name"><?= config_item('company_name') ?></div>
        <div class="company-address"><?= config_item('company_address') ?></div>
    </div>

    <div class="summary">
        <h2><?= lang('payments_received') ?></h2>
        <p><strong><?= lang('amount_received') ?>:</strong> <?= display_money($payments_info->amount, $currency->symbol) ?></p>
    </div>

    <table>
        <tr>
            <th><?= lang('payment_date') ?></th>
            <td><?= display_date($payments_info->payment_date) ?></td>
        </tr>
        <?php if (config_item('amount_to_words') == 'Yes') { ?>
            <tr>
                <th><?= lang('num_word') ?></th>
                <td><?= number_to_word('', $payments_info->amount) ?></td>
            </tr>
        <?php } ?>
        <tr>
            <th><?= lang('transaction_id') ?></th>
            <td><?= $payments_info->trans_id ?></td>
        </tr>
        <tr>
            <th><?= lang('received_from') ?></th>
            <td><?= $supplier_info->name ?></td>
        </tr>
        <tr>
            <th><?= lang('payment_mode') ?></th>
            <td><?= $payment_methods->method_name ?? '-' ?></td>
        </tr>
        <tr>
            <th><?= lang('notes') ?></th>
            <td><?= strip_tags($payments_info->notes) ?></td>
        </tr>
    </table>
    <?php $purchase_due = $this->purchase_model->calculate_to('purchase_due', $payments_info->purchase_id); ?>
    <h3><?= lang('payment_for') ?></h3>
    <table>
        <thead>
            <tr>
                <th><?= lang('invoice_code') ?></th>
                <th><?= lang('purchase_date') ?></th>
                <th><?= lang('purchase_amount') ?></th>
                <th><?= lang('paid_amount') ?></th>
                <?php if ($purchase_due > 0) { ?>
                    <th style="color: red;"><?= lang('due_amount') ?></th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= $purchase_info->reference_no ?></td>
                <td><?= display_datetime($purchase_info->created) ?></td>
                <!-- <td><?= display_money($this->purchase_model->get_purchase_cost($payments_info->purchase_id), $currency->symbol) ?></td> -->
                <td><?= display_money($this->purchase_model->calculate_to('total', $payments_info->purchase_id), $currency->symbol) ?></td>
                <td><?= display_money($payments_info->amount, $currency->symbol) ?></td>
                <?php if ($purchase_due > 0) { ?>
                    <td style="color: red;"><?= display_money($purchase_due, $currency->symbol) ?></td>
                <?php } ?>
            </tr>
        </tbody>
    </table>
</body>
</html>
