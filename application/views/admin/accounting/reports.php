<?php
$chart_year = ($this->session->userdata('chart_year')) ? $this->session->userdata('chart_year') : date('Y');
$cur = default_currency();
?>

<section class="panel panel-custom">
    <header class="panel-heading">
        <div class="panel-title">
            <?php echo !empty($filterBy) ? ' ' . lang('sales_report') . ' -> ' . lang($filterBy) : lang('sales_report') ?>
            <div class="pull-right">
                <div class="btn-group ">
                    <button class="btn custom-bg btn-xs dropdown-toggle"
                            data-toggle="dropdown"
                            class="btn btn-<?= config_item('theme_color'); ?> btn-sm"><?= lang('report') ?>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="<?= base_url() ?>admin/accounting/reports/trial_balance"><?= lang('trial_balance') ?></a>
                        </li>
                        <li>
                            <a href="<?= base_url() ?>admin/accounting/reports/balance_sheet"><?= lang('balance_sheet') ?></a>
                        </li>
                        <li>
                            <a href="<?= base_url() ?>admin/accounting/reports/ledger_report"><?= lang('ledger_report') ?></a>
                        </li>

                    </ul>
                </div>
                <?php
                if (!empty($filterBy)) {
                    if (empty($status)) {
                        $status = 'all';
                    }
                    if (empty($range)) {
                        $range = array('0', '0');
                    }
                    ?>
                    <a data-toggle="tooltip" data-placement="top"
                       href="<?= base_url() ?>admin/report/sales_report_pdf/<?= $filterBy . '/' . $status . '/' . implode('/', $range) ?>"
                       title="<?= lang('pdf') ?>"
                       class="btn btn-xs btn-danger hidden-xs"><?= lang('pdf') ?>
                        <i class="fa fa-file-pdf-o"></i></a>
                <?php }
                if (!empty($filterBy)) {
                    $id = $filterBy;
                } else {
                    $id = 'sales_report';
                }
                ?>

                <a onclick="print_sales_report('<?= $id ?>')" href="#" data-toggle="tooltip" data-placement="top"
                   title=""
                   data-original-title="Print" class="mr-sm btn btn-xs btn-warning hidden-xs"><?= lang('print') ?>
                    <i class="fa fa-print"></i>
                </a>
            </div>

        </div>
    </header>
    <div id="<?= $id ?>">
        <div class="show_print">
            <div style="width: 100%; border-bottom: 2px solid black;">
                <table style="width: 100%; vertical-align: middle;">
                    <tr>
                        <td style="width: 50px; border: 0px;">
                            <img style="width: 50px;height: 50px;margin-bottom: 5px;"
                                 src="<?= base_url() . config_item('company_logo') ?>" alt="" class="img-circle"/>
                        </td>

                        <td style="border: 0px;">
                            <p style="margin-left: 10px; font: 14px lighter;"><?= config_item('company_name') ?></p>
                        </td>

                    </tr>
                </table>
            </div>
            <br/>
        </div>
        <div class="panel-body table-responsive">
            <?php
            if (!empty($type)) {
                if ($type == 'trial_balance') {
                    $this->load->view('admin/accounting/reports/trial_balance');
                } elseif ($type == 'balance_sheet') {
                    $this->load->view('admin/accounting/reports/balance_sheet');
                } elseif ($type == 'ledger_report') {
                    $this->load->view('admin/accounting/reports/ledger_report');
                }
            } else {
                ?>
                <div class="row">
                    <div class="col-sm-3">
                        <?php
                        if (!empty($all_data)) {
                            foreach ($all_data as $account_type => $all_sub_type) {
                                ?>
                                <section class="panel panel-info">
                                    <div class="panel-body">
                                        <div class="clear"
                                             style="display: flex;justify-content: space-between;align-items: center;">
                                            <span class="text-dark"><?= $account_type ?></span>
                                            <small class="block text-danger pull-right ">
                                                <?= lang('debit') . ': ' . display_money($all_sub_type['debit'], default_currency()) ?>
                                                <br/>
                                                <?= lang('credit') . ': ' . display_money($all_sub_type['credit'], default_currency()) ?>
                                            </small>
                                        </div>
                                    </div>
                                </section>
                            <?php }
                        }
                        ?>
                    </div>


                    <div class="col-md-9 b-top">
                        <?php
                        $left_side_account = array();
                        $right_side_account = array();
                        $total_debit = 0;
                        $total_credit = 0;
                        if (!empty($all_data)) {
                            foreach ($all_data as $account_type => $all_sub_type) {
                                $all_sub_type = $all_sub_type['sub'];
                                // count all $all_sub_type and divide by 2
                                // and make two array for left and right
                                // then loop through them
                                // and display
                                $count = count($all_sub_type);
                                $half = ceil($count / 2);
                                $left_side_account[] = array_slice($all_sub_type, 0, $half);
                                $right_side_account[] = array_slice($all_sub_type, $half);

                                ?>
                            <?php }
                        }

                        ?>
                        <!-- 1st Quarter -->
                        <div class="col-sm-6 col-xs-12">
                            <div class="">
                                <table class="table table-striped table-bordered table-hover small text-muted">
                                    <thead>
                                    <tr>
                                        <th class="text-left">
                                            <div class="pull-left "><?= lang('account_sub_type') ?></div>
                                        </th>
                                        <th class="text-right">
                                            <div class="pull-right "><?= lang('debit') ?></div>
                                        </th>
                                        <th class="text-right">
                                            <div class="pull-right "> <?= lang('credit') ?></div>
                                        </th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    <?php

                                    foreach ($left_side_account as $key => $all_value) {
                                        foreach ($all_value as $value) {
                                            if (empty($value['name'])) continue;

                                            $total_debit += $value['debit'];
                                            $total_credit += $value['credit'];
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="pull-left ">
                                                        <?= $value['name'] ?>
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    <?= display_money($value['debit'], default_currency()) ?>
                                                </td>
                                                <td class="text-right">
                                                    <?= display_money($value['credit'], default_currency()) ?>
                                                </td>
                                            </tr>
                                        <?php }
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div><!-- .widget-body -->
                        </div>
                        <div class="col-sm-6 col-xs-12">
                            <div class="">
                                <table class="table table-striped table-bordered table-hover small text-muted">
                                    <thead>
                                    <tr>
                                        <th class="text-left">
                                            <div class="pull-left "><?= lang('account_sub_type') ?></div>
                                        </th>
                                        <th class="text-right">
                                            <div class="pull-right "><?= lang('debit') ?></div>
                                        </th>
                                        <th class="text-right">
                                            <div class="pull-right "> <?= lang('credit') ?></div>
                                        </th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    <?php
                                    foreach ($right_side_account as $key => $all_value) {
                                        foreach ($all_value as $value) {
                                            if (empty($value['name'])) continue;

                                            $total_debit += $value['debit'];
                                            $total_credit += $value['credit'];
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="pull-left ">
                                                        <?= $value['name'] ?>
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    <?= display_money($value['debit'], default_currency()) ?>
                                                </td>
                                                <td class="text-right">
                                                    <?= display_money($value['credit'], default_currency()) ?>
                                                </td>
                                            </tr>
                                        <?php }
                                    }
                                    ?>

                                    <tr class="hover-muted bt strong text-bold text-danger">
                                        <td class=""><strong><?= lang('total') ?></strong></td>
                                        <td class="text-right">
                                            <strong><?= display_money($total_debit, default_currency()) ?></strong>
                                        </td>
                                        <td class="text-right">
                                            <strong><?= display_money($total_credit, default_currency()) ?></strong>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div><!-- .widget-body -->
                        </div>
                    </div>

                </div>
                <!-- End Row -->
            <?php } ?>
        </div>

</section>

<script type="text/javascript">
    function print_sales_report(printReport) {
        var printContents = document.getElementById(printReport).innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
    }
</script>