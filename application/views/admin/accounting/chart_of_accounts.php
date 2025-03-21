<?= message_box('success'); ?>
<?= message_box('error');
$created = can_action_by_label('chart_of_accounts', 'created');
$edited = can_action_by_label('chart_of_accounts', 'edited');
$deleted = can_action_by_label('chart_of_accounts', 'deleted');
if (!empty($created) || !empty($edited)) {
$currency = $this->db->where('code', config_item('default_currency'))->get('tbl_currencies')->row();
?>


<div class="nav-tabs-custom">
    <!-- Tabs within a box -->
    <ul class="nav nav-tabs">
        <li class="active"><a
                    href="<?= base_url('admin/accounting/chart_of_accounts') ?>"><?= lang('chart_of_accounts') ?></a>
        </li>
        <li>
            <a href="<?= base_url() ?>admin/accounting/new_chart_of_account" data-toggle="modal" data-placement="top"
               data-target="#myModal_lg">
                <?= ' ' . lang('new') . ' ' . lang('account') ?></a>

        </li>
    </ul>
    <div class="tab-content bg-white">
        <!-- ************** general *************-->
        <div class="tab-pane active" id="manage">
            <?php } else { ?>
            <div class="panel panel-custom">
                <header class="panel-heading ">
                    <div class="panel-title"><strong><?= lang('all') . ' ' . lang('account') ?></strong></div>
                </header>
                <?php } ?>
                <div class="table-responsive">
                    <table class="table table-striped DataTables " id="DataTables" width="100%">
                        <thead>
                        <tr>
                            <th><?= lang('code') ?></th>
                            <th><?= lang('name') ?></th>
                            <th><?= lang('type') ?></th>
                            <th><?= lang('balance') ?></th>
                            <th><?= lang('status') ?></th>
                            <th class="col-options no-sort"><?= lang('action') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <script type="text/javascript">
                            list = base_url + "admin/accounting/chartOfAccountsList";
                        </script>
                    </table>

                </div>
            </div>


        </div>
    </div>