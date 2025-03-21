<?php
$created = can_action_by_label('chart_of_accounts', 'created');
$edited = can_action_by_label('chart_of_accounts', 'edited');
if (!empty($created) || !empty($edited)) {
    ?>
    <div class="panel panel-custom">
        <div class="panel-heading">
            <button type="button" class="close" data-dismiss="modal">
                <span aria-hidden="true">&times;</span>
                <span class="sr-only">Close</span>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('new') . ' ' . lang('chart_of_account') ?></h4>
        </div>
        <div class="modal-body wrap-modal wrap">
            <form role="form" id="save-chart-of-account-form" data-parsley-validate="" novalidate="" enctype="multipart/form-data"
                  action="<?= base_url('admin/accounting/save_chart_of_account/' . (!empty($chart_of_account->chart_of_account_id) ? $chart_of_account->chart_of_account_id : '')); ?>"
                  method="post" class="form-horizontal form-groups-bordered">
                  
                <!-- Validation errors will be displayed here -->
                <div class="validation-errors" style="text-align: center;"></div>
                
                <!-- Account Type Field -->
                <div class="form-group">
                    <label class="col-lg-3 control-label"><?= lang('account_type') ?> <span class="text-danger">*</span></label>
                    <div class="col-lg-8">
                        <select name="account_type_id" class="form-control selectpicker" style="width: 100%" required onchange="get_account_sub_types(this.value);">
                            <?php foreach ($account_types as $account_type_id => $account_type): ?>
                                <option value="<?= $account_type_id; ?>" <?= (!empty($chart_of_account->account_type_id) && $chart_of_account->account_type_id == $account_type_id ? 'selected' : ''); ?>>
                                    <?= $account_type; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Account Sub Type Field -->
                <div class="form-group">
                    <label class="col-lg-3 control-label"><?= lang('account_sub_type') ?> <span class="text-danger">*</span></label>
                    <div class="col-lg-8">
                        <select name="account_sub_type_id" id="account_sub_type_id" class="form-control selectpicker" style="width: 100%">
                            <?php foreach ($account_sub_types as $account_sub_type_id => $account_sub_type): ?>
                                <option value="<?= $account_sub_type_id; ?>" <?= (!empty($chart_of_account->account_sub_type_id) && $chart_of_account->account_sub_type_id == $account_sub_type_id ? 'selected' : ''); ?>>
                                    <?= $account_sub_type; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Account Code Field -->
                <div class="form-group">
                    <label for="field-1" class="col-sm-3 control-label"><?= lang('account_code') ?> <span class="required">*</span></label>
                    <div class="col-sm-8">
                        <input type="text" required name="code" value="<?= (!empty($chart_of_account->code) ? $chart_of_account->code : ''); ?>" class="form-control"/>
                    </div>
                </div>

                <!-- Account Name Field -->
                <div class="form-group">
                    <label for="field-1" class="col-sm-3 control-label"><?= lang('account_name') ?> <span class="required">*</span></label>
                    <div class="col-sm-8">
                        <input type="text" required name="name" value="<?= (!empty($chart_of_account->name) ? $chart_of_account->name : ''); ?>" class="form-control"/>
                    </div>
                </div>

                <!-- Status Field -->
                <div class="form-group">
                    <label for="field-1" class="col-sm-3 control-label"><?= lang('status') ?></label>
                    <div class="col-sm-8">
                        <div class="checkbox-inline c-checkbox">
                            <label>
                                <input type="checkbox" name="status" value="1" <?= (!empty($chart_of_account->status) && $chart_of_account->status == '1' ? 'checked' : ''); ?> class="select_one">
                                <span class="fa fa-check"></span> <?= lang('active') ?>
                            </label>
                        </div>
                        <div class="checkbox-inline c-checkbox">
                            <label>
                                <input type="checkbox" name="status" value="0" <?= (!empty($chart_of_account->status) && $chart_of_account->status == '0' ? 'checked' : ''); ?> class="select_one">
                                <span class="fa fa-check"></span> <?= lang('inactive') ?>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Description Field -->
                <div class="form-group">
                    <label for="field-1" class="col-sm-3 control-label"><?= lang('description') ?></label>
                    <div class="col-sm-8">
                        <textarea name="description" class="form-control textarea"><?= (!empty($chart_of_account->description) ? $chart_of_account->description : ''); ?></textarea>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-2">
                        <button type="submit" id="file-save-button" class="btn btn-primary btn-block"><?= lang('save') ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script type="text/javascript">
        function get_account_sub_types(account_type_id) {
            $('select[name="account_sub_type_id"]').html('<option value=""><?= lang('loading') ?></option>');
            $('select[name="account_sub_type_id"]').selectpicker('refresh');
            $.ajax({
                url: '<?= base_url('admin/accounting/get_account_sub_types/') ?>' + account_type_id,
                type: 'POST',
                dataType: 'json',
                success: function (data) {
                    if (data.success) {
                        $('select[name="account_sub_type_id"]').html(data.html);
                        $('select[name="account_sub_type_id"]').selectpicker('refresh');
                    }
                }
            });
        }

        $(document).ready(function () {
            $('#save-chart-of-account-form').on('submit', function (e) {
                e.preventDefault();
                var url = '<?php echo base_url('admin/accounting/chart_of_accounts'); ?>';

                $.ajax({
                    url: $(this).attr('action'),
                    type: $(this).attr('method'),
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'error') {
                            $('.validation-errors').html(response.validation_errors);
                        } else {
                            $('#myModal').modal('hide');
                            window.location.href = url;
                        }
                    }
                });
            });
        });
    </script>
<?php } ?>