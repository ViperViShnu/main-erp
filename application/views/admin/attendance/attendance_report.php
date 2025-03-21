<?php $this->load->view('admin/attendance/attendance_report_search'); ?>

<?php if (!empty($date) && !empty($attendace_info)) : ?>
    <div class="row" id="printableArea">
        <div class="col-sm-12 std_print">
            <div class="panel panel-custom">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <strong><?= lang('works_hours_deatils') . ' ' . $month; ?></strong>
                        <div class="show_print">
                            <?= lang('department') . ' : ' . $dept_name->deptname ?>
                        </div>
                        <div class="pull-right hidden-print">
                            <a href="<?= base_url() ?>admin/attendance/attendance_pdf/1/<?= $departments_id . '/' . $date; ?>" class="btn btn-primary btn-xs" data-toggle="tooltip" title="<?= lang('pdf') ?>">
                                <i class="fa fa-file-pdf-o"></i>
                            </a>
                            <a href="javascript:void(0);" onclick="printEmp_report('printableArea')" class="btn btn-danger btn-xs" data-toggle="tooltip" title="<?= lang('print') ?>">
                                <i class="fa fa-print"></i>
                            </a>
                        </div>
                    </h4>
                </div>
                <div class="panel-group" id="accordion" style="margin:8px 5px" role="tablist" aria-multiselectable="true">
                    <?php foreach ($attendace_info as $week => $v_attndc_info) : ?>
                        <div class="panel panel-default" style="border-radius: 0;">
                            <div class="panel-heading" style="border-radius: 0; border: none" role="tab" id="heading<?= $week ?>">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion" href="#collapse<?= $week ?>" aria-expanded="true" aria-controls="collapse<?= $week ?>">
                                        <strong><?= lang('week') ?> : <?= $week; ?></strong>
                                    </a>
                                </h4>
                            </div>
                            <div id="collapse<?= $week ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading<?= $week ?>">
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th><?= lang('name') ?></th>
                                                    <?php if (!empty($v_attndc_info)) : ?>
                                                        <?php foreach ($v_attndc_info as $date => $attendace) : ?>
                                                            <th><?= strftime(config_item('date_format'), strtotime($date)) ?></th>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                    // Initialize total per week
                                                    $week_total_hour = 0;
                                                    $week_total_minutes = 0;
                                                ?>
                                                <?php foreach ($employee_info as $v_employee) : ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($v_employee->fullname) ?></td>
                                                        <?php if (!empty($v_attndc_info)) : ?>
                                                            <?php foreach ($v_attndc_info as $date => $attendace) : ?>
                                                                <td>
                                                                    <?php
                                                                    // Initialize variables for the cell
                                                                    $total_hh = 0;
                                                                    $total_mm = 0;
                                                                    $holiday = 0;
                                                                    $leave = 0;
                                                                    $absent = 0;
                                                                    $hourly_leave = '';
                                                                    $no_clockout = 0;

                                                                    // Iterate through attendace for this date
                                                                    foreach ($attendace as $key => $v_attendace) :
                                                                        if ($key == $v_employee->user_id && !empty($v_attendace)) :
                                                                            foreach ($v_attendace as $v_attandc) :
                                                                                if ($v_attandc->attendance_status == 1) {
                                                                                    if (!empty($v_attandc->clockout_time)) {
                                                                                        // Calculate time difference
                                                                                        $startdatetime = strtotime($v_attandc->date_in . " " . $v_attandc->clockin_time);
                                                                                        $enddatetime = strtotime($v_attandc->date_out . " " . $v_attandc->clockout_time);
                                                                                        $difference = $enddatetime - $startdatetime;

                                                                                        $hours = floor($difference / 3600);
                                                                                        $mins = floor(($difference % 3600) / 60);

                                                                                        $total_mm += $mins;
                                                                                        $total_hh += $hours;

                                                                                        // Handle leave application
                                                                                        if (!empty($v_attandc->leave_application_id)) {
                                                                                            $is_hours = get_row('tbl_leave_application', ['leave_application_id' => $v_attandc->leave_application_id]);
                                                                                            if ($is_hours && $is_hours->leave_type == 'hours') {
                                                                                                $hourly_leave = "<small class='label label-pink text-sm' data-toggle='tooltip' title='" . lang('hourly') . ' ' . lang('leave') . ": " . $is_hours->hours . ":00" . ' ' . lang('hour') . "'>" . lang('hourly') . ' ' . lang('leave') . "</small>";
                                                                                            }
                                                                                        }
                                                                                    } else {
                                                                                        // clockout_time not set
                                                                                        $no_clockout = 1;
                                                                                    }
                                                                                } elseif ($v_attandc->attendance_status == 'H') {
                                                                                    $holiday = 1;
                                                                                } elseif ($v_attandc->attendance_status == '3') {
                                                                                    $leave = 1;
                                                                                } elseif ($v_attandc->attendance_status == '0') {
                                                                                    $absent = 1;
                                                                                }
                                                                            endforeach;
                                                                        endif;
                                                                    endforeach;

                                                                    // Adjust minutes if they exceed 59
                                                                    if ($total_mm > 59) {
                                                                        $total_hh += intval($total_mm / 60);
                                                                        $total_mm = intval($total_mm % 60);
                                                                    }

                                                                    // Add to week's total only if duration was calculated
                                                                    if ($total_hh > 0 || $total_mm > 0) {
                                                                        $week_total_hour += $total_hh;
                                                                        $week_total_minutes += $total_mm;
                                                                    }

                                                                    // Display logic
                                                                    if ($total_hh > 0 || $total_mm > 0) {
                                                                        echo $total_hh . " : " . $total_mm . " m " . $hourly_leave;
                                                                    } elseif ($no_clockout) {
                                                                        echo '-';
                                                                    } elseif ($holiday) {
                                                                        echo '<span class="label label-info std_p" style="font-size: 12px;">' . lang('holiday') . '</span>';
                                                                    } elseif ($leave) {
                                                                        echo '<span class="label label-warning std_p" style="font-size: 12px;">' . lang('on_leave') . '</span>';
                                                                    } elseif ($absent) {
                                                                        echo '<span class="label label-danger std_p" style="font-size: 12px;">' . lang('absent') . '</span>';
                                                                    } else {
                                                                        echo '-';
                                                                    }
                                                                    ?>
                                                                </td>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr>
                                                    <td colspan="<?= count($v_attndc_info) ?>" class="text-right"><strong><?= lang('total_working_hour') ?> :</strong></td>
                                                    <td>
                                                        <?php
                                                        // Adjust total minutes to hours
                                                        if ($week_total_minutes >= 60) {
                                                            $week_total_hour += intval($week_total_minutes / 60);
                                                            $week_total_minutes = intval($week_total_minutes % 60);
                                                        }
                                                        echo $week_total_hour . " : " . $week_total_minutes . " m";
                                                        ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script type="text/javascript">
    function printEmp_report(printableArea) {
        $('.collapse').css('display', 'block');
        var printContents = document.getElementById(printableArea).innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
    }
</script>