<?= message_box('success'); ?>
<?= message_box('error');
$created = can_action_by_label('chart_of_accounts', 'created');
$edited = can_action_by_label('chart_of_accounts', 'edited');
$deleted = can_action_by_label('chart_of_accounts', 'deleted');
if (!empty($created) || !empty($edited)) {
?>


<div class="nav-tabs-custom">
    <!-- Tabs within a box -->
    <ul class="nav nav-tabs">
        <li class="active"><a
                    href="<?= base_url('admin/accounting/journal_entry') ?>"><?= lang('journal_entry') ?></a>
        </li>
        <li>
            <a href="<?= base_url() ?>admin/accounting/new_journal_entry">
                <?= ' ' . lang('new') . ' ' . lang('journal_entry') ?></a>
        </li>
    </ul>
    <div class="tab-content bg-white">
        <!-- ************** general *************-->
        <div class="tab-pane active" id="manage">
            <?php } else { ?>
            <div class="panel panel-custom">
                <header class="panel-heading ">
                    <div class="panel-title"><strong><?= lang('all') . ' ' . lang('journal_entry') ?></strong></div>
                </header>
                <?php } ?>
                <div class="table-responsive">
                    <table class="table table-striped DataTables " id="DataTables" width="100%">
                        <thead>
                        <tr>
                            <th><?= lang('reference_no') ?></th>
                            <th><?= lang('date') ?></th>
                            <th><?= lang('debit') ?></th>
                            <th><?= lang('credit') ?></th>
                            <th><?= lang('created_by') ?></th>
                            <th><?= lang('action') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <script type="text/javascript">
                            list = base_url + "admin/accounting/journalEntryList";
                        </script>
                    </table>

                </div>
            </div>


        </div>
    </div>
</div>