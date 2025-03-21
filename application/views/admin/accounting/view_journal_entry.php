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
                        <a href="<?= base_url('admin/accounting/change_status/' . $active . '/' . $sales_info->journal_id . '/approved') ?>"
                           onclick="return confirm('Are you sure you want to approved this voucher?')">
                            <?= lang('approved') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('admin/accounting/change_status/' . $active . '/' . $sales_info->journal_id . '/rejected') ?>"
                           onclick="return confirm('Are you sure you want to reject this voucher?')">
                            <?= lang('rejected') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('admin/accounting/change_status/' . $active . '/' . $sales_info->journal_id . '/pending') ?>"
                           onclick="return confirm('Are you sure you want to pending this voucher?')">
                            <?= lang('pending') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('admin/accounting/change_status/' . $active . '/' . $sales_info->journal_id . '/canceled') ?>"
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

        <a href="<?= base_url() ?>admin/accounting/voucher_pdf/<?= $active ?>/<?= $sales_info->journal_id ?>"
           data-placement="top" title="" data-original-title="PDF" class="btn btn-xs btn-success pull-right mr-sm">
            <i class="fa fa-file-pdf-o"></i>
        </a>
        <a class="btn btn-primary mr-sm btn btn-xs  pull-right "
           href="<?php echo base_url('admin/accounting/new_' . $active . '/' . $sales_info->journal_id); ?>">
            <i class="fa fa-edit"></i>
        </a>

    </div>
</div>
<?php
$this->view('admin/common/sales_details', $sales_info);
$this->view($sales_info->item_layout, $sales_info);
?>

