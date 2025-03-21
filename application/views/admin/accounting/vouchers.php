<?= message_box('success'); ?>
<?= message_box('error');
$created = can_action_by_label('chart_of_accounts', 'created');
$edited = can_action_by_label('chart_of_accounts', 'edited');
$deleted = can_action_by_label('chart_of_accounts', 'deleted');
if (!empty($created) || !empty($edited)) { ?>
<div class="nav-tabs-custom">
    <!-- Tabs within a box -->
    <ul class="nav nav-tabs">
        <li class="active"><a
                    href="<?= base_url('admin/accounting/' . $active) ?>"><?= lang($active) ?></a>
        </li>
        <li>
            <a href="<?= base_url('admin/accounting/new_' . $active) ?>">
                <?= ' ' . lang('new') . ' ' . lang($active) ?></a>
        </li>
    </ul>
    <div class="tab-content bg-white">
        <!-- ************** general *************-->
        <div class="tab-pane active" id="manage">
            <?php } else { ?>
            <div class="panel panel-custom">
                <header class="panel-heading ">
                    <div class="panel-title"><strong><?= lang('all') . ' ' . lang($active) ?></strong></div>
                </header>
                <?php } ?>
                <div class="table-responsive">
                    <table class="table table-striped DataTables " id="DataTables" width="100%">
                        <thead>
                        <tr>
                            <th><?= lang('reference_no') ?></th>
                            <th><?= lang('date') ?></th>
                            <th><?= lang('paid_from') ?></th>
                            <th><?= lang('amount') ?></th>
                            <th><?= lang('status') ?></th>
                            <th><?= lang('action') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <script type="text/javascript">
                            list = base_url + "admin/accounting/vouchersList/<?= $active ?>";
                        </script>
                    </table>

                </div>
            </div>


        </div>
    </div>
</div>