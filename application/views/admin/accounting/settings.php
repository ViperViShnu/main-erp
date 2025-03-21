<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>
<div class="row mt-lg">
    <div class="col-sm-2">
        <ul class=" nav nav-pills nav-stacked navbar-custom-nav">
            <li class="<?php if ($active == 'receipt_voucher') echo 'active'; ?>"><a
                        href="<?= base_url('admin/accounting/settings/receipt_voucher/'); ?>"><?= lang('receipt_voucher'); ?></a>
            </li>
            <li class="<?php if ($active == 'payment_voucher') echo 'active'; ?>"><a
                        href="<?= base_url('admin/accounting/settings/payment_voucher/'); ?>"><?= lang('payment_voucher'); ?></a>
            </li>
            <li class="<?php if ($active == 'journal_entry') echo 'active'; ?>"><a
                        href="<?= base_url('admin/accounting/settings/journal_entry/'); ?>"><?= lang('journal_entry'); ?></a>
            </li>
            <li class="<?php if ($active == 'account_sub_type') echo 'active'; ?>"><a
                        href="<?= base_url('admin/accounting/settings/account_sub_type/'); ?>"><?= lang('account_sub_type'); ?></a>
            </li>
        </ul>
    </div>
    <div class="col-sm-10">

        <div class="tab-content b0 p0">

            <?= $page_content ?>

        </div>
    </div>
</div>