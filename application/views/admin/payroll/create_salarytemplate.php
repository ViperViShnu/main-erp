<?= message_box('success'); ?>
<?= message_box('error');
$created = can_action('94', 'created');
$edited = can_action('94', 'edited');
$deleted = can_action('94', 'deleted');
if (!empty($created) || !empty($edited)) {
?>
<div class="nav-tabs-custom">
    <!-- Tabs within a box -->
    <ul class="nav nav-tabs">
        <li class="<?= $active == 1 ? 'active' : ''; ?>"><a
                    href="<?= base_url('admin/payroll/salary_template') ?>"><?= lang('salary_template_list') ?></a>
        </li>
        <li class="<?= $active == 2 ? 'active' : ''; ?>"><a
                    href="<?= base_url('admin/payroll/create_salarytemplate') ?>"><?= lang('new_template') ?></a></li>
    </ul>
    <div class="tab-content bg-white">

        <div class="tab-pane <?= $active == 2 ? 'active' : ''; ?>" id="create">
            <form data-parsley-validate="" novalidate="" role="form" enctype="multipart/form-data"
                  action="<?php echo base_url() ?>admin/payroll/set_salary_details/<?php
                  if (!empty($salary_template_info->salary_template_id)) {
                      echo $salary_template_info->salary_template_id;
                  }
                  ?>" method="post"
                  class="form-horizontal form-groups-bordered">
                <div class="row">
                    <div class="form-group" id="border-none">
                        <label for="field-1" class="col-sm-3 control-label"><?= lang('salary_grade') ?><span
                                    class="required"> *</span></label>
                        <div class="col-sm-5">
                            <input type="text" name="salary_grade" value="<?php
                            if (!empty($salary_template_info->salary_grade)) {
                                echo $salary_template_info->salary_grade;
                            }
                            ?>" class="form-control" required
                                   placeholder="<?= lang('enter') . ' ' . lang('salary_grade') ?>">
                        </div>
                    </div>
                    <div class="form-group" id="border-none">
                        <label for="field-1" class="col-sm-3 control-label"><?= lang('basic_salary') ?><span
                                    class="required"> *</span></label>
                        <div class="col-sm-5">
                            <input type="text" data-parsley-type="number" name="basic_salary"
                                   id="basic_salary"
                                   value="<?php
                                   if (!empty($salary_template_info->basic_salary)) {
                                       echo $salary_template_info->basic_salary;
                                   } else {
                                       echo 100;
                                   }
                                   ?>"
                                   class="form-control" required
                                   placeholder="<?= lang('enter') . ' ' . lang('basic_salary') ?>">
                        </div>
                    </div>
                    <div class="form-group" id="border-none">
                        <label for="field-1" class="col-sm-3 control-label"><?= lang('overtime_rate') ?>
                            <small> ( <?= lang('per_hour') ?>)</small>
                        </label>
                        <div class="col-sm-5">
                            <input type="text" data-parsley-type="number" name="overtime_salary" value="<?php
                            if (!empty($salary_template_info->overtime_salary)) {
                                echo $salary_template_info->overtime_salary;
                            }
                            ?>"
                                   class="form-control"
                                   placeholder="<?= lang('enter') . ' ' . lang('overtime_rate') ?>">
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="panel panel-custom">
                            <div class="panel-heading">
                                <div class="panel-title">
                                    <strong><?= lang('allowances') ?>
                                    </strong>
                                    <small
                                            style="display: block;
                                            font-size: 12px;
                                            font-weight: 400;
                                            line-height: 1.42857143;
                                            color: #999;"
                                    ><?= lang('the_percentage_value_will_be_calculated_based_on_basic_salary') ?></small>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div id="add_new">

                                    <?php
                                    $total_salary = 0;
                                    if (!empty($salary_allowance_info)) : foreach ($salary_allowance_info as $v_allowance_info) : ?>

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <input type="text" style="margin:5px 0px;height: 28px;width: 56%;"
                                                       class="form-control pull-left" name="allowance_label[]"
                                                       value="<?php echo $v_allowance_info->allowance_label; ?>">
                                            </div>
                                            <div class="col-sm-9">
                                                <div class="input-group">
                                                    <input type="text" data-parsley-type="number"
                                                           name="allowance_percent[]"
                                                           value="<?php echo $v_allowance_info->allowance_percent; ?>"
                                                           class="salary form-control">

                                                    <input type="hidden" data-parsley-type="number"
                                                           name="allowance_value[]"
                                                           value="<?php echo $v_allowance_info->allowance_value; ?>"
                                                           class="allowance_amount form-control">


                                                    <input type="hidden" name="salary_allowance_id[]"
                                                           value="<?php echo $v_allowance_info->salary_allowance_id; ?>"
                                                           class="form-control">

                                                    <div class="input-group-addon p0 b0">
                                                        <select name="allowance_type[]"
                                                                class="allowance_type p-sm b"
                                                                data-width="100%">
                                                            <option value="percent"
                                                                <?= ($v_allowance_info->allowance_type == 'percent') ? 'selected' : ''; ?>
                                                            ><?= lang('percent') ?></option>
                                                            <option value="fixed"
                                                                <?= ($v_allowance_info->allowance_type == 'fixed') ? 'selected' : ''; ?>
                                                            ><?= lang('fixed') ?></option>
                                                        </select>
                                                    </div>
                                                    <div class="input-group-addon calculated_amount">
                                                        <?php echo $v_allowance_info->allowance_value; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-3"><strong><a href="javascript:void(0);" class="remCF"><i
                                                                class="fa fa-times"></i>&nbsp;Remove</a></strong>
                                            </div>
                                        </div>
                                        <?php $total_salary += $v_allowance_info->allowance_value; ?>
                                    <?php endforeach; ?>
                                    <?php else : ?>
                                        <div class="">
                                            <label class="control-label"><?= lang('house_rent_allowance') ?> </label>
                                            <div class="input-group">
                                                <input type="text" data-parsley-type="number"
                                                       name="house_rent_allowance"
                                                       value="" class="salary form-control">

                                                <input type="text" data-parsley-type="number"
                                                       name="house_rent_allowance_amount"
                                                       value="0"
                                                       class="allowance_amount form-control">

                                                <div class="input-group-addon p0 b0">
                                                    <select name="house_rent_allowance_type"
                                                            class="p-sm b allowance_type"
                                                            data-width="100%">
                                                        <option value="percent"><?= lang('percent') ?></option>
                                                        <option value="fixed"><?= lang('fixed') ?></option>
                                                    </select>
                                                </div>
                                                <div class="input-group-addon calculated_amount">
                                                    0
                                                </div>
                                            </div>
                                        </div>
                                        <div class="">
                                            <label class="control-label"><?= lang('medical_allowance') ?> </label>
                                            <div class="input-group">
                                                <input type="text" data-parsley-type="number"
                                                       name="medical_allowance"
                                                       value="" class="salary form-control">

                                                <input type="hidden" data-parsley-type="number"
                                                       name="medical_allowance_amount"
                                                       value="0"
                                                       class="allowance_amount form-control">

                                                <div class="input-group-addon p0 b0">
                                                    <select name="medical_allowance_type"
                                                            class="p-sm b allowance_type"
                                                            data-width="100%">
                                                        <option value="percent"><?= lang('percent') ?></option>
                                                        <option value="fixed"><?= lang('fixed') ?></option>
                                                    </select>
                                                </div>
                                                <div class="input-group-addon calculated_amount">
                                                    0
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                </div>
                                <div class="margin">
                                    <strong><a href="javascript:void(0);" id="add_more" class="addCF "><i
                                                    class="fa fa-plus"></i>&nbsp;<?= lang('add_more') ?>
                                        </a></strong>
                                </div>
                            </div>
                            <div class="panel-footer">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <?= lang('total_allowance') ?> :
                                        <span id="total_allowance">
                                            <?php
                                            if (!empty($total_salary)) {
                                                echo $total_salary;
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- ********************Allowance End ******************-->

                    <!-- ************** Deduction Panel Column  **************-->
                    <div class="col-sm-6">
                        <div class="panel panel-custom">
                            <div class="panel-heading">
                                <div class="panel-title">
                                    <strong><?= lang('deductions') ?></strong>
                                    <small
                                            style="display: block;
                                            font-size: 12px;
                                            font-weight: 400;
                                            line-height: 1.42857143;
                                            color: #999;"
                                    ><?= lang('the_percentage_value_will_be_calculated_based_on_basic_salary') ?></small>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div id="add_new_deduc">
                                    <?php
                                    $total_deduction = 0;
                                    if (!empty($salary_deduction_info)) : foreach ($salary_deduction_info as $v_deduction_info) :
                                        ?>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <input type="text" style="margin:5px 0px;height: 28px;width: 56%;"
                                                       class="form-control" name="deduction_label[]"
                                                       value="<?php echo $v_deduction_info->deduction_label; ?>"
                                                       class="">
                                            </div>
                                            <div class="col-sm-9">
                                                <div class="input-group">
                                                    <input type="text" data-parsley-type="number"
                                                           name="deduction_percent[]"
                                                           value="<?php echo $v_deduction_info->deduction_percent; ?>"
                                                           class="deduction form-control">

                                                    <input type="hidden" data-parsley-type="number"
                                                           name="deduction_value[]"
                                                           value="<?php echo $v_deduction_info->deduction_value; ?>"
                                                           class="deduction_amount form-control">

                                                    <input type="hidden" name="salary_deduction_id[]"
                                                           value="<?php echo $v_deduction_info->salary_deduction_id; ?>"
                                                           class="form-control">

                                                    <div class="input-group-addon p0 b0">
                                                        <select name="deduction_type[]"
                                                                class="deduction_type p-sm b"
                                                                data-width="100%">
                                                            <option value="percent"
                                                                <?= ($v_deduction_info->deduction_type == 'percent') ? 'selected' : ''; ?>
                                                            ><?= lang('percent') ?></option>
                                                            <option value="fixed"
                                                                <?= ($v_deduction_info->deduction_type == 'fixed') ? 'selected' : ''; ?>
                                                            ><?= lang('fixed') ?></option>
                                                        </select>
                                                    </div>
                                                    <div class="input-group-addon calculated_amount">
                                                        <?php echo $v_deduction_info->deduction_value; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-3"><strong><a href="javascript:void(0);"
                                                                             class="remCF_deduc"><i
                                                                class="fa fa-times"></i>&nbsp;Remove</a></strong>
                                            </div>
                                        </div>
                                        <?php $total_deduction += $v_deduction_info->deduction_value ?>
                                    <?php endforeach; ?>
                                    <?php else : ?>
                                        <div class="">
                                            <label class="control-label"><?= lang('provident_fund') ?> </label>
                                            <div class="input-group">
                                                <input type="text" data-parsley-type="number" name="provident_fund"
                                                       value=""
                                                       class="deduction form-control">
                                                <input type="hidden" data-parsley-type="number"
                                                       name="provident_fund_amount"
                                                       value="0"
                                                       class="deduction_amount form-control">

                                                <div class="input-group-addon p0 b0">
                                                    <select name="provident_fund_type"
                                                            class="p-sm b deduction_type"
                                                            data-width="100%">
                                                        <option value="percent"><?= lang('percent') ?></option>
                                                        <option value="fixed"><?= lang('fixed') ?></option>
                                                    </select>
                                                </div>
                                                <div class="input-group-addon calculated_amount">
                                                    0
                                                </div>
                                            </div>
                                        </div>
                                        <div class="">
                                            <label class="control-label"><?= lang('tax_deduction') ?> </label>
                                            <div class="input-group">
                                                <input type="text" data-parsley-type="number" name="tax_deduction"
                                                       value=""
                                                       class="deduction form-control">
                                                <input type="hidden" data-parsley-type="number"
                                                       name="tax_deduction_amount"
                                                       value="0"
                                                       class="deduction_amount form-control">

                                                <div class="input-group-addon p0 b0">
                                                    <select name="tax_deduction_type"
                                                            class="p-sm b tax_deduction_type"
                                                            data-width="100%">
                                                        <option value="percent"><?= lang('percent') ?></option>
                                                        <option value="fixed"><?= lang('fixed') ?></option>
                                                    </select>
                                                </div>
                                                <div class="input-group-addon calculated_amount">
                                                    0
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="margin">
                                    <strong><a href="javascript:void(0);" id="add_more_deduc" class="addCF "><i
                                                    class="fa fa-plus"></i>&nbsp;<?= lang('add_more') ?>
                                        </a></strong>
                                </div>
                            </div>
                            <div class="panel-footer">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <?= lang('total_deductions') ?> :
                                        <span id="total_deductions">
                                            <?php
                                            if (!empty($total_deduction)) {
                                                echo $total_deduction;
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- ****************** Deduction End  *******************-->
                    <!-- ************** Total Salary Details Start  **************-->
                </div>
                <div class="row">
                    <div class="col-md-8 pull-right">
                        <div class="panel panel-custom">
                            <div class="panel-heading">
                                <div class="panel-title">
                                    <strong><?= lang('total_salary_details') ?></strong>
                                </div>
                            </div>
                            <div class="panel-body">
                                <table class="table table-bordered custom-table">
                                    <tr>
                                        <!-- Sub total -->
                                        <th class="col-sm-8 vertical-td"><strong><?= lang('gross_salary') ?>
                                                :</strong>
                                        </th>
                                        <td class="">
                                            <input type="text" name="" disabled value="<?php
                                            if (!empty($total_salary) || !empty($salary_template_info->basic_salary)) {
                                                echo $total = $total_salary + $salary_template_info->basic_salary;
                                            }
                                            ?>" id="total"
                                                   class="form-control">
                                        </td>
                                    </tr> <!-- / Sub total -->
                                    <tr>
                                        <!-- Total tax -->
                                        <th class="col-sm-8 vertical-td"><strong><?= lang('total_deduction') ?>
                                                :</strong></th>
                                        <td class="">
                                            <input type="text" name="" disabled value="<?php
                                            if (!empty($total_deduction)) {
                                                echo $total_deduction;
                                            }
                                            ?>" id="deduc"
                                                   class="form-control">
                                        </td>
                                    </tr><!-- / Total tax -->
                                    <tr>
                                        <!-- Grand Total -->
                                        <th class="col-sm-8 vertical-td"><strong><?= lang('net_salary') ?>
                                                :</strong>
                                        </th>
                                        <td class="">
                                            <input type="text" name="" disabled required value="<?php
                                            if (!empty($total) || !empty($total_deduction)) {
                                                echo $total - $total_deduction;
                                            }
                                            ?>" id="net_salary"
                                                   class="form-control">
                                        </td>
                                    </tr><!-- Grand Total -->
                                </table><!-- Order Total table list start -->

                            </div>
                        </div>
                    </div><!-- ****************** Total Salary Details End  *******************-->
                </div>
                <div class="btn-bottom-toolbar text-right">
                    <?php
                    if (!empty($salary_template_info)) { ?>
                        <button type="submit" class="btn btn-sm btn-primary"><?= lang('updates') ?></button>
                        <button type="button" onclick="goBack()"
                                class="btn btn-sm btn-danger"><?= lang('cancel') ?></button>
                    <?php } else {
                        ?>
                        <button type="submit" class="btn btn-sm btn-primary"><?= lang('save') ?></button>
                    <?php }
                    ?>
                </div>

            </form>
            <?php } else { ?>
        </div>
        <?php } ?>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        var maxAppend = 0;
        $("#add_more").on("click", function () {
            if (maxAppend >= 100) {
                alert("Maximum 100 File is allowed");
            } else {
                var add_new = $(
                    '<div class="row">\n\
        <div class="col-sm-12"><input type="text" name="allowance_label[]" style="margin:5px 0px;height: 28px;width: 56%;" class="form-control"  placeholder="<?= lang('enter') . ' ' . lang('allowances') . ' ' . lang('label') ?>" required ></div>\n\
<div class="col-sm-9"><div class="input-group">\n\<input  type="text" data-parsley-type="number" name="allowance_percent[]" placeholder="<?= lang('enter') . ' ' . lang('allowances') . ' ' . lang('value') ?>" required  value=""  class="salary form-control"><input type="hidden" data-parsley-type="number" name="allowance_value[]" value="0" class="allowance_amount form-control"><div class="input-group-addon p0 b0"><select name="allowance_type[]" class="allowance_type p-sm b" data-width="100%"><option value="percent"><?= lang('percent') ?></option><option value="fixed"><?= lang('fixed') ?></option></select></div><div class="input-group-addon calculated_amount">0</div></div></div><div class="col-sm-3"><strong><a href="javascript:void(0);" class="remCF"><i class="fa fa-times"></i>&nbsp;Remove</a></strong></div></div>'
                );
                maxAppend++;
                $("#add_new").append(add_new);
            }
        });

        $("#add_new").on('click', '.remCF', function () {
            $(this).parent().parent().parent().remove();
            calculate_payroll();
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        var maxAppend = 0;
        $("#add_more_deduc").on("click", function () {
            if (maxAppend >= 100) {
                alert("Maximum 100 File is allowed");
            } else {
                var add_new = $(
                    '<div class="row">\n\
        <div class="col-sm-12"><input type="text" name="deduction_label[]" style="margin:5px 0px;height: 28px;width: 56%;" class="form-control" placeholder="<?= lang('enter') . ' ' . lang('deductions') . ' ' . lang('label') ?>" required></div>\n\
<div class="col-sm-9"><div class="input-group">\n\<input  type="text" data-parsley-type="number" name="deduction_percent[]" placeholder="<?= lang('enter') . ' ' . lang('deductions') . ' ' . lang('value') ?>" required  value="" class="deduction form-control"><input type="hidden" data-parsley-type="number" name="deduction_value[]" value="0" class="deduction_amount form-control"><div class="input-group-addon p0 b0"><select name="deduction_type[]" class="deduction_type p-sm b" data-width="100%"><option value="percent"><?= lang('percent') ?></option><option value="fixed"><?= lang('fixed') ?></option></select></div><div class="input-group-addon calculated_amount">0</div></div></div>\n\
<div class="col-sm-3"><strong><a href="javascript:void(0);" class="remCF_deduc"><i class="fa fa-times"></i>&nbsp;Remove</a></strong></div></div>'
                );
                maxAppend++;
                $("#add_new_deduc").append(add_new);
            }
        });

        $("#add_new_deduc").on('click', '.remCF_deduc', function () {
            $(this).parent().parent().parent().remove();
            calculate_payroll();
        });
    });
</script>
<script type="text/javascript">
    $(document).on("change blur", function () {
        calculate_payroll();
    });

    function calculate_payroll() {
        var sum = 0;
        var deduc = 0;
        let total_allowance = 0;
        let basic_salary = $("#basic_salary").val();
        $(".salary").each(function () {
            const allowance_type = $(this).parent().find('.allowance_type').val();
            let amount = 0;
            if (allowance_type === 'fixed') {
                amount = $(this).val();
            } else {
                amount = $(this).val() * basic_salary / 100;
            }
            // if amount is not a number then set it to 0
            if (isNaN(amount)) {
                amount = 0;
            }
            // show the amount into allowance_amount input
            $(this).parent().find('.allowance_amount').val(Math.round(amount));
            $(this).parent().find('.calculated_amount').html(allowance_type === 'fixed' ? '= ' + (amount) : '% ' + Math.round(amount));
            // total allowance
            total_allowance += +amount;
            sum += +amount;
        });
        $('#total_allowance').html(Math.round(total_allowance));

        $(".deduction").each(function () {
            const deduction_type = $(this).parent().find('.deduction_type').val();
            let amount = 0;
            if (deduction_type === 'fixed') {
                amount = $(this).val();
            } else {
                amount = $(this).val() * basic_salary / 100;
            }
            // if amount is not a number then set it to 0
            if (isNaN(amount)) {
                amount = 0;
            }
            // show the amount into deduction_amount input
            $(this).parent().find('.deduction_amount').val(Math.round(amount));
            $(this).parent().find('.calculated_amount').html(
                deduction_type === 'fixed' ? '= ' + (amount) : '% ' + Math.round(amount)
            );
            deduc += +amount;
        });
        $('#total_deductions').html(deduc);

        let total_gross = Math.round(sum) + Math.round(basic_salary);

        $("#total").val(total_gross);
        $("#deduc").val(deduc);
        var net_salary = 0;
        net_salary = total_gross - deduc;
        $("#net_salary").val(net_salary);
    }
</script>

