<?php
$currency = $this->db->where('code', config_item('default_currency'))->get('tbl_currencies')->row();
?>
<table class="items" style="margin-top: 25px">
    <thead class="p-md bg-items">
    <tr>
        <th>#</th>
        <th><?= lang('chart_of_accounts') ?></th>
        <?php
        $colspan = 3; ?>
        <th style="text-align: right"><?= lang('description') ?></th>
        <th style="text-align: right"><?= lang('debit') ?></th>
        <th style="text-align: right"><?= lang('credit') ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (!empty($receipt_items)) :
        foreach ($receipt_items as $key => $item) {
            ?>
            <tr>
                <td class="unit"><?= $key + 1 ?></td>
                <td class="unit"><h3><?= $item->name ?> (<?= $item->code ?>)</h3></td>
                <td class="unit"><?= nl2br($item->description) ?></td>
                <td class="unit" style="text-align: right"><?= display_money($item->debit) ?></td>
                <td class="unit" style="text-align: right"><?= display_money($item->credit) ?></td>
            </tr>
        <?php } ?>
    <?php endif ?>

    </tbody>
    <tfoot>
    <tr class="total">
        <td colspan="<?= $colspan ?>"></td>
        <td colspan="1"><?= lang('total_debit') ?></td>
        <td><?= display_money($sales_info->total_debit, $currency->symbol) ?></td>
    </tr>
    <tr class="total">
        <td colspan="<?= $colspan ?>"></td>
        <td colspan="1"><?= lang('total_credit') ?></td>
        <td><?= display_money($sales_info->total_credit, $currency->symbol) ?></td>
    </tr>
    </tfoot>
</table>
