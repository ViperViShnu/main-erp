<!DOCTYPE html>
<html>
<head>
    <title>Payments PDF</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <?php
    $direction = $this->session->userdata('direction') ?? config_item('RTL');
    $RTL = !empty($direction) && $direction === 'rtl' ? 'on' : null;
    ?>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #333;
        }

        th, td {
            padding: 10px;
            text-align: <?= !empty($RTL) ? 'right' : 'left' ?>;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .notes {
            color: #777;
            padding: 15px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .header {
            border-bottom: 2px solid black;
            margin-bottom: 20px;
        }

        .header img {
            width: 60px;
            margin-right: 10px;
        }

        .header p {
            margin: 0;
        }

        .summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #1B9BA0;
            color: white;
            padding: 15px;
            border-radius: 5px;
        }

        .summary span {
            font-size: 18px;
            font-weight: bold;
        }

        .content-title {
            margin: 20px 0;
            font-size: 18px;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .highlight {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>

<?php
$return_stock_info = $this->return_stock_model->check_by(['return_stock_id' => $payments_info->return_stock_id], 'tbl_return_stock');
$supplier_info = $return_stock_info->module === 'client' 
    ? $this->return_stock_model->check_by(['client_id' => $return_stock_info->module_id], 'tbl_client') 
    : $this->return_stock_model->check_by(['supplier_id' => $return_stock_info->module_id], 'tbl_suppliers');

$payment_methods = is_numeric($payments_info->payment_method)
    ? $this->return_stock_model->check_by(['payment_methods_id' => $payments_info->payment_method], 'tbl_payment_methods')
    : (object) ['method_name' => $payments_info->payment_method];

// $img = ROOTPATH . '/' . config_item('invoice_logo');
// if (!file_exists($img)) {
//     $img = ROOTPATH . '/uploads/default_logo.png';
// }
    if (is_file(config_item('invoice_logo'))) {
        $img = base_url() . config_item('invoice_logo');
    } else {
        $img = base_url() . 'uploads/default_logo.png';
    }
?>

<div class="header">
    <table>
        <tr>
            <td style="width: 10%;">
                <img src="<?= $img ?>" alt="Logo">
            </td>
            <td>
                <p><?= config_item('company_name') ?></p>
                <p><?= config_item('company_address') ?></p>
            </td>
        </tr>
    </table>
</div>

<div class="summary">
    <div>
        <p><?= lang('payments_received') ?></p>
    </div>
    <div class="text-right">
        <span><?= display_money($payments_info->amount, $currency->symbol); ?></span>
    </div>
</div>

<div>
    <table>
        <tr>
            <th><?= lang('payment_date') ?></th>
            <td><?= display_date($payments_info->payment_date); ?></td>
        </tr>
        <tr>
            <th><?= lang('transaction_id') ?></th>
            <td><?= $payments_info->trans_id ?></td>
        </tr>
        <tr>
            <th><?= lang('received_from') ?></th>
            <td><?= $supplier_info->name; ?></td>
        </tr>
        <?php if ($role == 1 && $payments_info->account_id != 0): 
            $account_info = $this->return_stock_model->check_by(['account_id' => $payments_info->account_id], 'tbl_accounts');
            if (!empty($account_info)): ?>
                <tr>
                    <th><?= lang('received_account') ?></th>
                    <td><?= $account_info->account_name; ?></td>
                </tr>
            <?php endif; 
        endif; ?>
        <tr>
            <th><?= lang('payment_mode') ?></th>
            <td><?= $payment_methods->method_name ?? '-'; ?></td>
        </tr>
        <tr>
            <th><?= lang('notes') ?></th>
            <td><?= strip_tags($payments_info->notes); ?></td>
        </tr>
    </table>
</div>
<?php $return_stock_due = $this->return_stock_model->calculate_to('return_stock_due', $payments_info->return_stock_id); ?>
<div class="content-title"><?= lang('payment_for') ?></div>
<table>
    <thead>
        <tr>
            <th><?= lang('reference_no') ?></th>
            <th class="text-right"><?= lang('return_stock') . ' ' . lang('date') ?></th>
            <th class="text-right"><?= lang('return_stock') . ' ' . lang('amount') ?></th>
            <th class="text-right"><?= lang('paid_amount') ?></th>
            <?php if ($return_stock_due > 0): ?>
                <th class="text-right highlight"><?= lang('due_amount') ?></th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?= $return_stock_info->reference_no; ?></td>
            <td class="text-right"><?= display_date($return_stock_info->return_stock_date); ?></td>
            <td class="text-right">
                <?= display_money($this->return_stock_model->get_return_stock_cost($payments_info->return_stock_id), $currency->symbol); ?>
                (- <?= lang('tax') ?>)
            </td>
            <td class="text-right"><?= display_money($payments_info->amount, $currency->symbol); ?></td>
            <?php if ($return_stock_due > 0): ?>
                <td class="text-right highlight"><?= display_money($return_stock_due, $currency->symbol); ?></td>
            <?php endif; ?>
        </tr>
    </tbody>
</table>

</body>
</html>
