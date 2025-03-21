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
                <div class="col-md-4">
                    <label><?= lang('chart_of_account') ?></label>
                    <select name="account_id" class="form-control accounts-dropdown">
                        <?php
                        if (!empty($chart_of_accounts)) {
                            foreach ($chart_of_accounts as $chart_of_account) {
                                ?>
                                <option <?= (!empty($account_id) && $account_id == $chart_of_account->chart_of_account_id ? 'selected' : '') ?>
                                        value="<?= $chart_of_account->chart_of_account_id ?>"><?= $chart_of_account->name ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
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
            <h4><?= lang('chart_of_account') ?>: <?= $account_name ?></h4>
            <?php if (!empty($start_date)) { ?>
                <h4><span><?= lang('FROM') ?></span>&nbsp;<?= $start_date ?>
                    &nbsp;<span><?= lang('TO') ?></span>&nbsp;<?= $end_date ?></h4>
            <?php } ?>
        </div>

        <div class="fill-container">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th><?= lang('reference_no') ?></th>
                        <th><?= lang('date') ?></th>
                        <th><?= lang('created_by') ?></th>
                        <th class="text-right"><?= lang('debit') ?></th>
                        <th class="text-right"><?= lang('credit') ?></th>

                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $total_debit = 0;
                    $total_credit = 0;
                    if (!empty($all_data)) {
                        foreach ($all_data as $data) {
                            $total_debit += $data->debit;
                            $total_credit += $data->credit;
                            ?>
                            <tr>
                                <td>

                                    <a href="<?= base_url() ?>admin/accounting/view_journal_entry/<?= $data->journal_id ?>"><?= $data->reference_no ?></a>
                                </td>
                                <td><?= display_date($data->date) ?></td>
                                <td><?= $data->fullname ?></td>
                                <td class="text-right"><?= display_money($data->debit) ?></td>
                                <td class="text-right"><?= display_money($data->credit) ?></td>

                            </tr>
                            <?php
                        }
                    }
                    ?>
                    </tbody>
                    <tfoot>
                    <tr class="hover-muted bt strong">
                        <td colspan="2"></td>
                        <td colspan=""><strong><?= lang('total') ?></strong></td>
                        <td class="text-right"><strong><?= display_money($total_debit, $cur) ?></strong></td>
                        <td class="text-right"><strong><?= display_money($total_credit, $cur) ?></strong></td>
                    </tr>
                    </tfoot>

                </table>

            </div>
        </div>


    </div>


    <script type="text/javascript">
        $(document).ready(function () {
            select2_init();
        });

        function select2_init() {
            $("select.accounts-dropdown").select2({
                ajax: {
                    url: base_url + "admin/accounting/get_chart_of_accounts",
                    dataType: 'json',
                    processResults: function (data) {
                        return {
                            results: data
                        }
                    },
                },
                escapeMarkup: function (markup) {
                    return markup;
                },
                templateResult: function (data) {
                    return data.html;
                },
                templateSelection: function (data) {
                    return data.text;
                }
            });
        }

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
