<?php
$currency = $this->db->where('code', config_item('default_currency'))->get('tbl_currencies')->row();
?>
<div class="panel-body">
    <div class="table-responsive mb-lg">
        <table class="table items invoice-items-preview" page-break-inside: auto;>
            <thead class="bg-items">
            <tr>
                <th class="col-sm-1">#</th>
                <th><?= $active == 'receipt_voucher' ? lang('clients') : lang('supplier') ?></th>
                <th><?= lang('description') ?></th>
                <th class="col-sm-1"><?= lang('amount') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($receipt_items as $key => $item) {
                ?>
                <tr class="sortable item">
                    <td class="item_no dragger pl-lg"><?= $key + 1 ?></td>
                    <td><strong class="block"><?= $item->name ?></strong>
                    </td>
                    <td><?= nl2br($item->description) ?></td>
                    <td><?= display_money($item->amount) ?></td>
                </tr>
                <?php
            }
            ?>
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
                <p class="pull-left"><?= lang('total') ?></p>
                <p class="pull-right mr">
                    <?= display_money($sales_info->total, $currency->symbol) ?>
                </p>
            </div>
            <?php
            $due_amount = $sales_info->total;
            if (config_item('amount_to_words') == 'Yes' && !empty($due_amount) && $due_amount > 0) { ?>
                <div class="clearfix">
                    <p class="pull-right h4"><strong class="h3"><?= lang('num_word') ?>
                            : </strong> <?= number_to_word($currency->code, $due_amount); ?></p>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
