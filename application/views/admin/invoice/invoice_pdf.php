<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= lang('invoice') ?></title>
    <?php
    $direction = $this->session->userdata('direction');
    if (!empty($direction) && $direction == 'rtl') {
        $RTL = 'on';
    } else {
        $RTL = config_item('RTL');
    }
    ?>
    <style type="text/css">
        @font-face {
            font-family: "Source Sans Pro", sans-serif;
        }

        .h4 {
            font-size: 18px;
        }

        .h3 {
            font-size: 24px;
        }

        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }

        a {
            color: #0087C3;
            text-decoration: none;
        }

        body {
            color: #555555;
            background: #ffffff;
            font-size: 14px;
            font-family: "Source Sans Pro", sans-serif;
            width: 100%;
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }?>
        }

        header {
            padding: 10px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #aaaaaa;
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }?>
        }

        #logo {
        <?php if(!empty($RTL)){?> text-align: right !important;
            padding-right: 55px;
        <?php }?>
        }

        #company {
        <?php if(!empty($RTL)){?> text-align: left;
        <?php }else{?> text-align: right;
        <?php }?>
        }

        #details {
            margin-bottom: 20px;
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }?>
        }

        #client {
            padding-left: 6px;
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }?>
        }

        #client .to {
            color: #777777;
        }

        h2.name {
            font-size: 1em;
            font-weight: normal;
            margin: 0;
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }?>
        }

        #invoice {
        <?php if(!empty($RTL)){?> text-align: left;
        <?php }else{?> text-align: right;
        <?php }?>
        }

        #invoice h1 {
            color: #0087C3;
            font-size: 1.5em;
            line-height: 1em;
            font-weight: normal;
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }?>
        }

        #invoice .date {
            font-size: 1.1em;
            color: #777777;
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }?>
        }

        table {
            width: 100%;
            border-spacing: 0;
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }?>
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            margin-bottom: 10px;
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }?>
        }

        table.items th,
        table.items td {
            padding: 8px;
            border-bottom: 1px solid #FFFFFF;
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }else{?> text-align: left;
        <?php }?>

        }

        table.items th {
            white-space: nowrap;
            font-weight: normal;
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }?>
        }

        table.items td {
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }else{?> text-align: left;
        <?php }?>
        }

        table.items td h3 {
            color: #57B223;
            font-size: 1em;
            font-weight: normal;
            margin-top: 5px;
            margin-bottom: 5px;
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }?>
        }

        table.items .no {
            background: #dddddd;
        }

        table.items .desc {
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }else{?> text-align: left;
        <?php }?>
        }

        table.items .unit {
            background: #F3F3F3;
        }

        table.items .qty {
        }

        table.items td.unit,
        table.items td.qty,
        table.items td.total {
            font-size: 1em;
        }

        table.items tbody tr:last-child td {
            border: none;

        }

        table.items tfoot td {
            padding: 10px 20px;
            background: #FFFFFF;
            border-bottom: none;
            font-size: 1.2em;
            white-space: nowrap;
            border-top: 1px solid #AAAAAA;
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }?>
        }

        table.items tfoot tr:first-child td {
            border-top: none;
        }

        table.items tfoot tr:last-child td {
            color: #57B223;
            font-size: 1.4em;
            border-top: 1px solid #57B223;

        }

        table.items tfoot tr td:first-child {
            border: none;
        <?php if(!empty($RTL)){?> text-align: left;
        <?php }else{?> text-align: right;
        <?php }?>
        }

        #thanks {
            font-size: 1.5em;
            margin-bottom: 20px;
        }

        #notices {
            padding-left: 6px;
            border-left: 6px solid #0087C3;

        }

        #notices .notice {
            font-size: 1em;
            color: #777;
        }

        footer {
            color: #777777;
            width: 100%;
            height: 30px;
            position: absolute;
            bottom: 0;
            border-top: 1px solid #aaaaaa;
            padding: 8px 0;
            text-align: center;
        }

        tr.total td, tr th.total, tr td.total {
        <?php if(!empty($RTL)){?> text-align: left;
        <?php }else{?> text-align: right;
        <?php }?>
        }

        .bg-items {
            background: #303252 !important;
            color: #FFFFFF
        }

        .p-md {
            padding: 12px !important;
        }

        .left {
        <?php if(!empty($RTL)){?> float: right;
        <?php }else{?> float: left;
        <?php }?>
        }

        .right {
        <?php if(!empty($RTL)){?> float: left;
            padding-right: 20px;
        <?php }else{?> float: right;
            padding-left: 20px;
        <?php }?>
        }

    </style>
</head>
<body>

<?php
$client_id = null;
if ($invoice_info->module == 'client') {
    $client_info = $this->invoice_model->check_by(array('client_id' => $invoice_info->module_id), 'tbl_client');
    $currency = $this->invoice_model->client_currency_symbol($invoice_info->module_id);
    $client_lang = $client_info->language;
    $client_id = $invoice_info->module_id;
} else if ($invoice_info->module == 'leads') {
    $client_info = $this->invoice_model->check_by(array('leads_id' => $invoice_info->module_id), 'tbl_leads');
    if (!empty($client_info)) {
        $client_info->name = $client_info->lead_name;
        $client_info->zipcode = null;
    }
    $client_lang = 'english';
    $currency = $this->invoice_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');
} else {
    $client_lang = 'english';
    $currency = $this->invoice_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');
}
unset($this->lang->is_loaded[5]);
$language_info = $this->lang->load('sales_lang', $client_lang, TRUE, FALSE, '', TRUE);
// $img = ROOTPATH . '/' . config_item('invoice_logo');
// $a = file_exists($img);
// if (empty($a)) {
//     $img = base_url() . config_item('invoice_logo');
// }
// if (!file_exists($img)) {
//     $img = ROOTPATH . '/' . 'uploads/default_logo.png';
// }
if(is_file(config_item('invoice_logo'))) {
    $img = base_url() . config_item('invoice_logo');
} else {
    $img = base_url() . 'uploads/default_logo.png';
}
?>
<table class="clearfix">
    <tr>
        <td style="width: 50%;">
            <div id="logo" class="left">
                <img style="width: 233px;height: 120px;float: left !important;" src="<?= $img ?>">
            </div>
        </td>
        <td style="width: 50%;">
            <div class="right" style="">
                <?php if(!empty($qrcode)) { ?>
                    <div class="pull-right pr-lg mt-lg">
                        <?php echo (!empty($qrcode) ? $qrcode : ''); ?>
                    </div>
                <?php } ?>
                <h2 style="margin-bottom: 0"><?= $language_info['invoice'] ?>
                    : <?= $invoice_info->reference_no ?></h2>
                <div class="date"><?= $language_info['invoice_date'] ?>
                    :<?= strftime(config_item('date_format'), strtotime($invoice_info->invoice_date)); ?></div>
                <div class="date"><?= $language_info['due_date'] ?>
                    :<?= strftime(config_item('date_format'), strtotime($invoice_info->due_date)); ?></div>
                <?php if (!empty($invoice_info->user_id)) { ?>
                    <div class="date">
                        <?= lang('sales') . ' ' . lang('agent') ?><?php echo fullname($invoice_info->user_id)
                        ?>
                    </div>
                <?php }
                if ($invoice_info->status == 'accepted') {
                    $label = 'success';
                } else {
                    $label = 'danger';
                } ?>
                <div class="date"><?= lang('invoice') . '  ' . lang('status') ?>
                    : <?= lang($invoice_info->status) ?></div>
                <?php $show_custom_fields = custom_form_label(10, $invoice_info->invoices_id);
                if (!empty($show_custom_fields)) {
                    foreach ($show_custom_fields as $c_label => $v_fields) {
                        if (!empty($v_fields)) {
                            ?>
                            <div class="date"><?= $c_label ?>: <?= $v_fields ?></div>
                        <?php }
                    }
                }
                ?>
            </div>

        </td>
    </tr>
</table>

<table id="details" class="clearfix">
    <tr>
        <td style="width: 50%;overflow: hidden">
            <h4 class="p-md bg-items ">
                <?= lang('our_info') ?>
            </h4>
        </td>
        <td style="width: 50%">
            <h4 class="p-md bg-items ">
                <?= lang('customer') ?>
            </h4>
        </td>
    </tr>
    <tr style="margin-top: 0px">
        <td style="width: 50%;overflow: hidden">
            <div style="padding-left: 5px">
                <h3 style="margin: 0px"><?= (config_item('company_legal_name_' . $client_lang) ? config_item('company_legal_name_' . $client_lang) : config_item('company_legal_name')) ?></h3>
                <div><?= (config_item('company_address_' . $client_lang) ? config_item('company_address_' . $client_lang) : config_item('company_address')) ?></div>
                <div><?= (config_item('company_city_' . $client_lang) ? config_item('company_city_' . $client_lang) : config_item('company_city')) ?>
                    , <?= config_item('company_zip_code') ?></div>
                <div><?= (config_item('company_country_' . $client_lang) ? config_item('company_country_' . $client_lang) : config_item('company_country')) ?></div>
                <div> <?= config_item('company_phone') ?></div>
                <div><a href="mailto:<?= config_item('company_email') ?>"><?= config_item('company_email') ?></a></div>
                <div><?= config_item('company_vat') ?></div>
            </div>
        </td>
        <td style="width: 50%">
            <div style="padding-left: 5px">
                <?php
                if (!empty($client_info)) {
                    $client_name = $client_info->name;
                    $address = $client_info->address;
                    $city = $client_info->city;
                    $zipcode = $client_info->zipcode;
                    $country = $client_info->country;
                    $phone = $client_info->phone;
                    $email = $client_info->email;
                } else {
                    $client_name = '-';
                    $address = '-';
                    $city = '-';
                    $zipcode = '-';
                    $country = '-';
                    $phone = '-';
                    $email = '-';
                }
                ?>
                <h3 style="margin: 0px"><?= $client_name ?></h3>
                <div class="address"><?= $address ?></div>
                <div class="address"><?= $city ?>, <?= $zipcode ?>
                    ,<?= $country ?></div>
                <div class="address"><?= $phone ?></div>
                <div class="email"><a href="mailto:<?= $email ?>"><?= $email ?></a></div>
                <?php if (!empty($client_info->vat)) { ?>
                    <div class="email"><?= lang('vat_number') ?>: <?= $client_info->vat ?></div>
                <?php } ?>
            </div>
        </td>
    </tr>
</table>

<table class="items">
    <thead class="p-md bg-items">
    <tr>
        <th><?= $language_info['items'] ?></th>
        <?php
        $colspan = 4;
        $invoice_view = config_item('invoice_view');
        if (!empty($invoice_view) && $invoice_view == '2') {
            $colspan = 5;
            ?>
            <th><?= lang('hsn_code') ?></th>
        <?php } ?>
        <th><?= $language_info['qty'] ?></th>
        <th><?= $language_info['unit'] . ' ' . $language_info['price'] ?></th>
        <th><?= $language_info['sub_total'] ?></th>
        <th><?= $language_info['tax'] ?></th>
        <th><?= $language_info['total'] ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
    $invoice_items = $this->invoice_model->ordered_items_by_id($invoice_info->invoices_id);

    if (!empty($invoice_items)) :
        foreach ($invoice_items as $key => $v_item) :
            $item_name = $v_item->item_name ? $v_item->item_name : $v_item->item_desc;
            $item_tax_name = json_decode($v_item->item_tax_name);
            ?>
            <tr>
                <td class="unit"><h3><?= $item_name ?></h3><?= nl2br($v_item->item_desc) ?></td>
                <?php
                $invoice_view = config_item('invoice_view');
                if (!empty($invoice_view) && $invoice_view == '2') {
                    ?>
                    <td class="unit"><?= $v_item->hsn_code ?></td>
                <?php } ?>
                <td class="unit"><?= $v_item->quantity . '   ' . $v_item->unit ?></td>
                <td class="unit"><?= display_money($v_item->unit_cost) ?></td>
                <td class="unit"><?= display_money($v_item->total_cost) ?></td>
                <td class="unit"><?php
                    if (!empty($item_tax_name)) {
                        foreach ($item_tax_name as $v_tax_name) {
                            $i_tax_name = explode('|', $v_tax_name);
                            echo '<small class="pr-sm">' . $i_tax_name[0] . ' (' . $i_tax_name[1] . ' %)' . '</small>' . display_money($v_item->total_cost / 100 * $i_tax_name[1]) . ' <br>';
                        }
                    }
                    ?></td>
                <td class="unit"><?= display_money($v_item->total_cost + $v_item->item_tax_total) ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif ?>

    </tbody>
    <tfoot>
    <tr class="total">
        <td colspan="<?= $colspan ?>"></td>
        <td colspan="1"><?= $language_info['sub_total'] ?></td>
        <td><?= display_money($this->invoice_model->calculate_to('invoice_cost', $invoice_info->invoices_id)) ?></td>
    </tr>
    <?php if ($invoice_info->discount_total > 0): ?>
        <tr class="total">
            <td colspan="<?= $colspan ?>"></td>
            <td colspan="1"><?= $language_info['discount'] ?>(<?php echo $invoice_info->discount_percent; ?>%)</td>
            <td> <?= display_money($this->invoice_model->calculate_to('discount', $invoice_info->invoices_id)) ?></td>
        </tr>
    <?php endif;
    $tax_info = json_decode($invoice_info->total_tax);
    $tax_total = 0;
    if (!empty($tax_info)) {
        $tax_name = $tax_info->tax_name;
        $total_tax = $tax_info->total_tax;
        if (!empty($tax_name)) {
            foreach ($tax_name as $t_key => $v_tax_info) {
                $tax = explode('|', $v_tax_info);
                $tax_total += $total_tax[$t_key];
                ?>
                <tr class="total">
                    <td colspan="<?= $colspan ?>"></td>
                    <td colspan="1"><?= $tax[0] . ' (' . $tax[1] . ' %)' ?></td>
                    <td> <?= display_money($total_tax[$t_key]); ?></td>
                </tr>
            <?php }
        }
    } ?>
    <?php if ($tax_total > 0): ?>
        <tr class="total">
            <td colspan="<?= $colspan ?>"></td>
            <td colspan="1"><?= $language_info['total'] . ' ' . $language_info['tax'] ?></td>
            <td><?= display_money($tax_total); ?></td>
        </tr>
    <?php endif;
    if ($invoice_info->adjustment > 0): ?>
        <tr class="total">
            <td colspan="<?= $colspan ?>"></td>
            <td colspan="1"><?= $language_info['adjustment'] ?></td>
            <td><?= display_money($invoice_info->adjustment); ?></td>
        </tr>
    <?php endif ?>
    <tr class="total">
        <td colspan="<?= $colspan ?>"></td>
        <td colspan="1"><?= $language_info['total'] ?></td>
        <td>
            <?php
            $invoice_total = $this->invoice_model->calculate_to('total', $invoice_info->invoices_id);
            echo display_money($invoice_total, $currency->symbol); ?>
        </td>
    </tr>
    <?php if (!empty($paid_amount) && $paid_amount > 0) { ?>
        <tr class="total">
            <td colspan="<?= $colspan ?>"></td>
            <td colspan="1"><?= $language_info['paid_amount'] ?></td>
            <td>
                <?php
                echo display_money($paid_amount, $currency->symbol); ?>
            </td>
        </tr>
    <?php } ?>

    <?php if (!empty($invoice_due) && $invoice_due > 0) { ?>
        <tr class="total">
            <td colspan="<?= $colspan ?>"></td>
            <td colspan="1" style="color: red;"><?= $language_info['total_due'] ?></td>
            <td>
                <?php echo display_money(($invoice_due), $currency->symbol); ?>
            </td>
        </tr>
    <?php } ?>
    </tfoot>
</table>
<?php if (config_item('amount_to_words') == 'Yes') { ?>
    <div class="clearfix">
        <p class="right h4"><strong class="h3"><?= lang('num_word') ?>
                : </strong> <?= number_to_word($client_id, $invoice_total); ?></p>
    </div>
<?php } ?>
<div id="thanks"><?= lang('thanks') ?>!</div>
<!-- <div id="notices">
    <div class="notice"><?= strip_html_tags($invoice_info->notes, true) ?></div>
</div> -->
<footer>
    <?= config_item('invoice_footer') ?>
</footer>
</body>
</html>