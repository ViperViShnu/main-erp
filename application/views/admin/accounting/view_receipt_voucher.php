<?= message_box('success') ?>
<?= message_box('error');
$edited = can_action('13', 'edited');
$deleted = can_action('13', 'deleted');
$currency = $this->db->where('code', config_item('default_currency'))->get('tbl_currencies')->row();
?>

<div class="row mb">


    <div class="col-sm-10">


    </div>
    <div class="col-sm-2 pull-right">
        <?php
        if (!empty(admin_head())) {
            ?>
            <div class="btn-group pull-right">
                <button class="btn btn-xs btn-warning dropdown-toggle" data-toggle="dropdown">
                    <?= lang('action') ?>
                    <span class="caret"></span></button>
                <ul class="dropdown-menu animated zoomIn">
                    <li>
                        <a href="<?= base_url('admin/accounting/change_status/' . $active . '/' . $sales_info->voucher_id . '/approved') ?>"
                           onclick="return confirm('Are you sure you want to approved this voucher?')">
                            <?= lang('approved') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('admin/accounting/change_status/' . $active . '/' . $sales_info->voucher_id . '/rejected') ?>"
                           onclick="return confirm('Are you sure you want to reject this voucher?')">
                            <?= lang('rejected') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('admin/accounting/change_status/' . $active . '/' . $sales_info->voucher_id . '/pending') ?>"
                           onclick="return confirm('Are you sure you want to pending this voucher?')">
                            <?= lang('pending') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('admin/accounting/change_status/' . $active . '/' . $sales_info->voucher_id . '/canceled') ?>"
                           onclick="return confirm('Are you sure you want to cancel this voucher?')">
                            <?= lang('canceled') ?>
                        </a>
                    </li>
                </ul>
            </div>
            <?php
        }
        ?>

        <a onclick="print_sales_details('sales_details')" href="#" data-toggle="tooltip" data-placement="top" title=""
           data-original-title="Print" class="mr-sm btn btn-xs btn-danger pull-right">
            <i class="fa fa-print"></i>
        </a>

        <a href="<?= base_url() ?>admin/accounting/voucher_pdf/<?= $active ?>/<?= $sales_info->voucher_id ?>"
           data-placement="top" title="" data-original-title="PDF" class="btn btn-xs btn-success pull-right mr-sm">
            <i class="fa fa-file-pdf-o"></i>
        </a>
        <a class="btn btn-primary mr-sm btn btn-xs  pull-right "
           href="<?php echo base_url('admin/accounting/new_' . $active . '/' . $sales_info->voucher_id); ?>">
            <i class="fa fa-edit"></i>
        </a>

    </div>
</div>
<?php
$this->view('admin/common/sales_details', $sales_info);
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
