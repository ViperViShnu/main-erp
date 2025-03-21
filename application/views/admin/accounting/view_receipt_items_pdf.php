<?php
$currency = $this->db->where('code', config_item('default_currency'))->get('tbl_currencies')->row();
?>
<table class="items" style="margin-top: 25px">
    <thead class="p-md bg-items">
    <tr>
        <th>#</th>
        <th><?= $active == 'receipt_voucher' ? lang('clients') : lang('supplier') ?></th>
        <?php
        $colspan = 2; ?>
        <th style="text-align: right"><?= lang('description') ?></th>
        <th style="text-align: right"><?= lang('amount') ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (!empty($receipt_items)) :
        foreach ($receipt_items as $key => $item) {
            ?>
            <tr>
                <td class="unit"><?= $key + 1 ?></td>
                <td class="unit"><h3><?= $item->name ?></h3></td>
                <td class="unit"><?= nl2br($item->description) ?></td>
                <td class="unit" style="text-align: right"><?= display_money($item->amount) ?></td>
            </tr>
        <?php } ?>
    <?php endif ?>

    </tbody>
    <tfoot>
    <tr class="total">
        <td colspan="<?= $colspan ?>"></td>
        <td colspan="1"><?= lang('total') ?></td>
        <td><?= display_money($sales_info->total, $currency->symbol) ?></td>
    </tr>
    </tfoot>
</table>
