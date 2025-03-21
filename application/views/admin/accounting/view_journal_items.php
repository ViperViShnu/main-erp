<?php
$currency = $this->db->where('code', config_item('default_currency'))->get('tbl_currencies')->row();
?>
<div class="panel-body">
    <div class="table-responsive mb-lg">
        <table class="table items invoice-items-preview" page-break-inside: auto;>
            <thead class="bg-items">
            <tr>
                <th class="col-sm-1">#</th>
                <th><?= lang('chart_of_accounts') ?></th>
                <th><?= lang('description') ?></th>
                <th class="col-sm-1"><?= lang('debit') ?></th>
                <th class="col-sm-1"><?= lang('credit') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($receipt_items as $key => $item) {
                ?>
                <tr class="sortable item">
                    <td class="item_no dragger pl-lg"><?= $key + 1 ?></td>
                    <td><strong class="block"><?= $item->name ?>
                            (<?= $item->code ?>)
                        </strong>
                    </td>
                    <td><?= nl2br($item->description) ?></td>
                    <td><?= display_money($item->debit) ?></td>
                    <td><?= display_money($item->credit) ?></td>
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
                <p class="pull-left"><?= lang('total_debit') ?></p>
                <p class="pull-right mr">
                    <?= $sales_info->total_debit ? display_money($sales_info->total_debit, $currency->symbol) : '0.00' ?>
                </p>
            </div>
            <div class="clearfix">
                <p class="pull-left"><?= lang('total_credit') ?></p>
                <p class="pull-right mr">
                    <?= display_money($sales_info->total_credit, $currency->symbol) ?>
                </p>
            </div>
        </div>
    </div>
</div>
