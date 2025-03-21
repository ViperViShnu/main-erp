<?php
$created = can_action_by_label('chart_of_accounts', 'created');
$edited = can_action_by_label('chart_of_accounts', 'edited');
$deleted = can_action_by_label('chart_of_accounts', 'deleted');
if (!empty($created) || !empty($edited)) {
?>


<div class="nav-tabs-custom">
    <!-- Tabs within a box -->
    <ul class="nav nav-tabs">
        <li class="active"><a
                    href="<?= base_url('admin/accounting/settings/account_sub_type/') ?>"><?= lang('chart_of_accounts') ?></a>
        </li>
        <li>
            <a href="<?= base_url() ?>admin/accounting/new_account_sub_type" data-toggle="modal" data-placement="top"
               data-target="#myModal_lg">
                <?= ' ' . lang('new') . ' ' . lang('sub_type') ?></a>

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
                            <th><?= lang('account_sub_type') ?></th>
                            <th><?= lang('account_type') ?></th>
                            <th><?= lang('status') ?></th>
                            <th class="col-options no-sort"><?= lang('action') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <script type="text/javascript">
                            list = base_url + "admin/accounting/accountSubTypeList";
                        </script>
                    </table>

                </div>
            </div>


        </div>
    </div>