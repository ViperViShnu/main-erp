<!-- Include Required Prerequisites -->
<script type="text/javascript" src="//cdn.jsdelivr.net/jquery/1/jquery.min.js"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

<!-- Include Date Range Picker -->
<script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css"/>

<?php
$cur = default_currency();
if (!empty($range[0])) {
    $start_date = date('F d, Y', strtotime($range[0]));
    $end_date = date('F d, Y', strtotime($range[1]));
}
$status = (isset($status)) ? $status : 'all';
?>


<div class="panel-body">
    <div class="hidden-print">
        <div class="criteria-band">
            <address class="row">
                <?php echo form_open(base_url() . 'admin/accounting/reports/' . $type . '/' . $filterBy); ?>
                <div class="col-md-4">
                    <label><?= lang('date_range') ?></label>
                    <input type="text" name="range" id="reportrange"
                           class="pull-right form-control">
                    <i class="fa fa-calendar"></i>&nbsp;
                    <span></span> <b class="caret"></b>
                </div>

                <div class="col-md-2">
                    <label style="visibility: hidden"><?= lang('run_report') ?></label>
                    <button class="btn btn-purple" style="display: block" type="submit">
                        <?= lang('run_report') ?>
                    </button>
                </div>
            </address>
        </div>
        </form>
    </div>


    <div class="rep-container">
        <div class="page-header text-center">
            <h3 class="reports-headerspacing"><?= lang($type) ?></h3>
            <?php if (!empty($start_date)) { ?>
                <h4><span><?= lang('FROM') ?></span>&nbsp;<?= $start_date ?>
                    &nbsp;<span><?= lang('TO') ?></span>&nbsp;<?= $end_date ?></h4>
            <?php } ?>
        </div>

        <div class="fill-container">
            <table width="99%" align="left" class="table table-striped table-bordered table-hover">
                <tbody class="table-bordered">
                <tr>
                    <th><?= lang('account_type') ?></th>
                    <th class="text-right"><?= lang('balance') ?></th>
                    <th class="text-right"><?= lang('debit') ?></th>
                    <th class="text-right"><?= lang('credit') ?></th>
                </tr>
                <?php
                $total_debit = 0;
                $total_credit = 0;
                $total_balance = 0;
                if (!empty($all_data)) {
                    foreach ($all_data as $account_type => $all_data_balance) {
                        $total_debit += $all_data_balance['debit'];
                        $total_credit += $all_data_balance['credit'];
                        $total_balance += $all_data_balance['balance'];
                        ?>
                        <tr>
                            <td align="left"><?= $account_type ?></td>
                            <td class="text-right" class="profitamount">
                                <?= display_money($all_data_balance['balance'], default_currency()) ?>
                            </td>
                            <td class="text-right" class="profitamount">
                                <?= display_money($all_data_balance['debit'], default_currency()) ?>
                            </td>
                            <td class="text-right" class="profitamount">
                                <?= display_money($all_data_balance['credit'], default_currency()) ?>
                            </td>
                        </tr>
                        <?php foreach ($all_data_balance['sub'] as $account_sub_type => $all_chart_data) { ?>
                            <tr>
                                <td align="left" style="padding-left: 80px;">
                                    <?= $account_sub_type ?>
                                </td>
                                <td class="text-right" class="profitamount">
                                    <?= display_money($all_data_balance['balance'], default_currency()) ?>
                                </td>
                                <td class="text-right" class="profitamount">
                                    <?= display_money($all_data_balance['debit'], default_currency()) ?>
                                </td>
                                <td class="text-right" class="profitamount">
                                    <?= display_money($all_data_balance['credit'], default_currency()) ?>
                                </td>
                            </tr>

                            <?php foreach ($all_chart_data['accounts'] as $code => $account_data) { ?>
                                <tr>
                                    <td align="left" style="padding-left: 160px;">
                                        <?= $account_data['name'] . ' (' . $code . ')' ?>
                                    </td>
                                    <td class="text-right" class="profitamount">
                                        <?= display_money($account_data['balance'], default_currency()) ?>
                                    </td>
                                    <td class="text-right" class="profitamount">
                                        <?= display_money($account_data['debit'], default_currency()) ?>
                                    </td>
                                    <td class="text-right" class="profitamount">
                                        <?= display_money($account_data['credit'], default_currency()) ?>
                                    </td>
                                </tr>
                            <?php } ?>

                        <?php } ?>

                    <?php } ?>

                <?php } ?>
                </tbody>
                <tfoot class="table-bordered">
                <tr>
                    <td class="text-right"><strong></strong>
                    </td>
                    <td class="text-right" class="profitamount">
                        <strong><?= display_money($total_balance, default_currency()) ?></strong>
                    </td>
                    <td class="text-right" class="profitamount">
                        <strong><?= display_money($total_debit, default_currency()) ?></strong>
                    </td>
                    <td class="text-right" class="profitamount">
                        <strong><?= display_money($total_credit, default_currency()) ?></strong>
                    </td>
                </tr>
                </tfoot>
            </table>
        </div>


    </div>


    <script type="text/javascript">
        $('#reportrange').daterangepicker({
            autoUpdateInput: <?= !empty($start_date) ? 'true' : 'false'?>,
            locale: {
                format: 'MMMM D, YYYY'
            },
            <?php if(!empty($start_date)){?>
            startDate: '<?=$start_date?>',
            endDate: '<?=$end_date?>',
            <?php }?>
            "opens": "right",
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });
        $('#reportrange').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('MMMM D, YYYY') + ' - ' + picker.endDate.format('MMMM D, YYYY'));
        });

        $('#reportrange').on('cancel.daterangepicker', function (ev, picker) {
            $(this).val('');
        });
    </script>
