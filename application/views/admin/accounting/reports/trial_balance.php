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
            <table class="table table-striped table-bordered table-hover">
                <thead>
                <tr>
                    <th class="text-left">
                        <div class="pull-left "><?= lang('account') ?></div>
                    </th>
                    <th class="text-right">
                        <div class="pull-right "><?= lang('debit') ?></div>
                    </th>
                    <th class="text-right">
                        <div class="pull-right "><?= lang('credit') ?></div>
                    </th>
                </tr>
                </thead>

                <tbody>

                <?php
                $total_debit = 0;
                $total_credit = 0;
                if (!empty($all_data)) {
                    foreach ($all_data as $code => $data) {
                        $total_debit += $data['debit'];
                        $total_credit += $data['credit'];
                        ?>
                        <tr>
                            <td>
                                <div class="pull-left "><?= $data['name'] ?> (<?= $code ?>)</div>
                            </td>

                            <td class="text-right">
                                <?= display_money($data['debit'], $cur) ?>
                            <td class="text-right">
                                <?php echo display_money($data['credit'], $cur) ?>
                        </tr>
                    <?php } ?>

                    <tr class="hover-muted bt strong">
                        <td colspan=""><strong><?= lang('total') ?></strong></td>
                        <td class="text-right"><strong><?= display_money($total_debit, $cur) ?></strong></td>
                        <td class="text-right"><strong><?= display_money($total_credit, $cur) ?></strong></td>
                    </tr>

                <?php } ?>

                <!----></tbody>
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
