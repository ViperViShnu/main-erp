<?php
if (is_file(config_item('invoice_logo'))) {
    $img = base_url() . config_item('invoice_logo');
} else {
    $img = base_url() . 'uploads/default_logo.png';
}
$client_lang = 'english';
?>
<?php if (!empty($sales_info->overdue_days)) { ?>
    <div class="alert bg-danger-light hidden-print">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <i class="fa fa-warning"></i>
        <?= $sales_info->overdue_days ?>
    </div>
    <?php
} ?>
<div class="panel" id="sales_details">
    <div class="panel-body mt-lg">
        <div class="row">
            <div class="col-lg-6 hidden-xs">
                <img class="pl-lg" style="width: 233px;height: 120px;" src="<?= $img ?>">
            </div>
            <div class="col-lg-6 col-xs-12 ">

                <div class="pull-right pr-lg">
                    <h4 class="mb0"><?= $sales_info->ref_no ?></h4>
                    <?= $sales_info->start_date ?>
                    <?php if (!empty($sales_info->end_date)) { ?>
                        <br><?= $sales_info->end_date ?>
                    <?php }
                    if (!empty($sales_info->sales_agent)) { ?>
                        <br><?= $sales_info->sales_agent; ?>
                    <?php }
                    ?>
                    <br><?= $sales_info->status ?>
                    <br><?= $sales_info->custom_field ?>
                </div>
                <?php
                if (!empty($qrcode)) { ?>
                    <div class="pull-right pr-lg mt-lg">
                        <?= (!empty($qrcode) ? $qrcode : '') ?>
                    </div>
                <?php }
                ?>

            </div>

        </div>

        <div class="row mb-lg">
            <div class="col-lg-6 col-xs-6">
                <h5 class="p-md bg-items mr-15">
                    <?= lang('our_info') ?>:
                </h5>
                <div class="pl-sm">
                    <h4 class="mb0">
                        <?= (config_item('company_legal_name_' . $client_lang) ? config_item('company_legal_name_' . $client_lang) : config_item('company_legal_name')) ?>
                    </h4>
                    <?= (config_item('company_address_' . $client_lang) ? config_item('company_address_' . $client_lang) : config_item('company_address')) ?>
                    <br><?= (config_item('company_city_' . $client_lang) ? config_item('company_city_' . $client_lang) : config_item('company_city')) ?>
                    , <?= config_item('company_zip_code') ?>
                    <br><?= (config_item('company_country_' . $client_lang) ? config_item('company_country_' . $client_lang) : config_item('company_country')) ?>
                    <br/><?= lang('phone') ?> : <?= config_item('company_phone') ?>
                    <!-- <br/><?= lang('vat_number') ?> : <?= config_item('company_vat') ?> -->
                </div>
            </div>
            <div class="col-lg-6 col-xs-6 ">
                <h5 class="p-md bg-items ml-13">
                    <?= (!empty($sales_info->company_heading) ? $sales_info->company_heading : lang('customer') . ' : ') ?>
                </h5>
                <div class="pl-sm">
                    <h4 class="mb0"><?= (!empty($sales_info->name) ? $sales_info->name : '') ?></h4>
                    <?= (!empty($sales_info->address) ? $sales_info->address . ', ' : '') ?>
                    <br> <?= (!empty($sales_info->city) ? $sales_info->city : '') ?>
                    <?= (!empty($sales_info->zipcode) ? $sales_info->zipcode : '') ?>
                    <br><?= (!empty($sales_info->country) ? $sales_info->country : '') ?>
                    <br><?= (!empty($sales_info->phone) ? lang('phone') . ': ' . $sales_info->phone : '') ?>
                    <?php if (!empty($sales_info->vat)) { ?>
                        <br><?= lang('vat_number') ?>: <?= $sales_info->vat ?>
                    <?php } ?>
                </div>
            </div>

        </div>
        <style type="text/css">
            .dragger {
                background: url(../../../../assets/img/dragger.png) 0px 11px no-repeat;
                cursor: pointer;
            }

            .table > tbody > tr > td {
                vertical-align: initial;
            }
        </style>

        <?php if (empty($sales_info->item_layout)) { ?>

            <div class="table-responsive mb-lg">
                <table class="table items invoice-items-preview" page-break-inside: auto;>
                    <thead class="bg-items">
                    <tr>
                        <th>#</th>
                        <th><?= lang('items') ?></th>
                        <?php if (!empty($invoice_view) && $invoice_view == '2') { ?>
                            <th><?= lang('hsn_code') ?></th>
                        <?php } ?>
                        <th><?= $qty_heading ?></th>
                        <th class="col-sm-1"><?= lang('price') ?></th>
                        <th class="col-sm-2"><?= lang('tax') ?></th>
                        <th class="col-sm-1">Taxable Value</th> <!-- New column for Taxable Price -->
                        <th class="col-sm-1"><?= lang('total') ?></th>
                        <!-- <th class="col-sm-1"><?= lang('total') ?></th> -->
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if (!empty($all_items)) :
                        $total_tax_amount = 0;
                        $total_taxable_price = 0;
                        foreach ($all_items as $key => $v_item) :
                            $item_tax_name = json_decode($v_item->item_tax_name);
                            $tax_rate = !empty($item_tax_name) ? explode('|', $item_tax_name[0])[1] : 0; // Get tax rate (assuming only one tax rate)
                            $taxable_price = $v_item->total_cost / (1 + $tax_rate / 100); // Calculate Taxable Price
                            $total_tax_amount += $v_item->total_cost - $taxable_price;
                            $total_taxable_price += $taxable_price;
                            ?>
                            <tr class="sortable item" data-item-id="<?= $v_item->$itemId ?>">
                                <td><?= $key + 1 ?></td>
                                <td><strong><?= $v_item->item_name ?></strong></td>
                                <?php if (!empty($invoice_view) && $invoice_view == '2') { ?>
                                    <td><?= $v_item->hsn_code ?></td>
                                <?php } ?>
                                <td><?= $v_item->quantity . '   &nbsp' . $v_item->unit ?></td>
                                <td><?= display_money($v_item->unit_cost) ?></td>
                                <td>
                                    <?php
                                    if (!empty($item_tax_name)) {
                                        foreach ($item_tax_name as $v_tax_name) {
                                            $i_tax_name = explode('|', $v_tax_name);
                                            echo '<small>' . $i_tax_name[0] . ' (' . $i_tax_name[1] . ' %)' . '</small> ' . display_money($v_item->total_cost - $taxable_price) . '<br>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td><?= display_money($taxable_price) ?></td> <!-- Display Taxable Price -->
                                <td><?= display_money($v_item->total_cost) ?></td>
                                <!-- <td><?= display_money($total_taxable_price) ?></td> -->
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8"><?= lang('nothing_to_display') ?></td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="row" style="margin-top: 35px">
                <div class="col-xs-8">
                    <p class="well well-sm mt">
                        <?= $sales_info->notes ?>
                    </p>
                </div>
                <div class="col-sm-4 pv">
                    <div class="clearfix">
                        <p class="pull-left"><?= lang('sub_total') ?></p>
                        <p class="pull-right mr">
                            <?= $sales_info->sub_total ? display_money($sales_info->sub_total) : '0.00' ?>
                        </p>
                    </div>
                    <?php if ($sales_info->discount > 0) : ?>
                        <div class="clearfix">
                            <p class="pull-left"><?= lang('discount') ?>
                                (<?php echo $sales_info->discount_percent; ?>
                                %)</p>
                            <p class="pull-right mr">
                                <?= $sales_info->discount ? display_money($sales_info->discount) : '0.00' ?>
                            </p>
                        </div>
                    <?php endif ?>
                    <?php
                    $tax_info = json_decode($sales_info->total_tax);
                    $tax_total = 0;
                    if (!empty($tax_info)) {
                        $tax_name = $tax_info->tax_name;
                        $total_tax = $tax_info->total_tax;
                        if (!empty($tax_name)) {
                            foreach ($tax_name as $t_key => $v_tax_info) {
                                $tax = explode('|', $v_tax_info);
                                $tax_total += $total_tax[$t_key];
                                ?>
                                <div class="clearfix">
                                    <p class="pull-left"><?= $tax[0] . ' (' . $tax[1] . ' %)' ?></p>
                                    <p class="pull-right mr">
                                        <?= display_money($total_tax[$t_key]); ?>
                                    </p>
                                </div>
                            <?php }
                        }
                    } ?>
                    <?php if ($tax_total > 0) : ?>
                        <div class="clearfix">
                            <p class="pull-left"><?= lang('total') . ' ' . lang('tax') ?></p>
                            <p class="pull-right mr">
                                <?= display_money($tax_total); ?>
                            </p>
                        </div>
                    <?php endif ?>
                    <?php if ($sales_info->adjustment > 0) : ?>
                        <div class="clearfix">
                            <p class="pull-left"><?= lang('adjustment') ?></p>
                            <p class="pull-right mr">
                                <?= display_money($sales_info->adjustment); ?>
                            </p>
                        </div>
                    <?php endif;
                    do_action('multicurrency_in_invoice', $sales_info);
                    $currency = get_sales_currency($sales_info);
                    ?>
                    <div class="clearfix">
                        <p class="pull-left"><?= lang('total') ?></p>
                        <p class="pull-right mr">
                            <?= display_money($sales_info->total, $currency->symbol) ?>
                        </p>
                    </div>
                    <?php
                    if (!empty($paid_amount) && $paid_amount > 0) {
                        $total = lang('total_due');
                        if ($paid_amount > 0) {
                            $text = 'text-danger';
                            ?>
                            <div class="clearfix">
                                <p class="pull-left"><?= lang('paid_amount') ?> </p>
                                <p class="pull-right mr">
                                    <?= display_money($paid_amount, $currency->symbol); ?>
                                </p>
                            </div>
                        <?php } else {
                            $text = '';
                        } ?>
                    <?php } ?>

                    <?php
                    if (!empty($paid_amount) && $paid_amount > 0) { ?>
                        <div class="clearfix">
                            <p class="pull-left h3 <?= $text ?>"><?= $total ?></p>
                            <p class="pull-right mr h3"><?= display_money(($invoice_due), $currency->symbol); ?></p>
                        </div>
                    <?php } ?>

                    <?php
                    if (!empty($invoice_due) && $invoice_due > 0) {
                        $due_amount = $invoice_due;
                    } else {
                        $due_amount = $sales_info->total;
                    }
                    if (config_item('amount_to_words') == 'Yes' && !empty($due_amount) && $due_amount > 0) { ?>
                        <div class="clearfix">
                            <p class="pull-right h4"><strong class="h3"><?= lang('num_word') ?>
                                    : </strong> <?= number_to_word($currency->code, $due_amount); ?></p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>

    <?php
    if (empty($sales_info->item_layout)) {
        ?>
        <?= !empty($invoice_view) && $invoice_view > 0 ? $this->gst->summary($all_items) : ''; ?>
        <?php
    }
    ?>
</div>

<?php include_once 'assets/js/sales.php'; ?>

<script type="text/javascript">
    $(document).ready(function () {
        init_items_sortable(true);
    });

    function print_sales_details(sales_details) {
        var printContents = document.getElementById(sales_details).innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
    }
</script>