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
            <form id="form" class="form-horizontal form-groups-bordered" role="form" 
                  data-parsley-validate="" novalidate="" enctype="multipart/form-data"
                  action="<?= base_url('admin/accounting/save_chart_of_account/' . (!empty($chart_of_account->chart_of_account) ? $chart_of_account->chart_of_account : '')); ?>"
                  method="post">
                  
                <div class="form-group">
                    <label class="col-lg-3 control-label"><?= lang('account_type') ?> <span class="text-danger">*</span></label>
                    <div class="col-lg-8">
                        <select name="account_type_id" class="form-control selectpicker" style="width: 100%" required
                                onchange="get_account_sub_types(this.value);">
                            <?php foreach ($account_types as $account_type_id => $account_type): ?>
                                <option value="<?= $account_type_id; ?>" <?= (!empty($chart_of_account->account_type_id) && $chart_of_account->account_type_id == $account_type_id) ? 'selected' : ''; ?>>
                                    <?= $account_type; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-lg-3 control-label"><?= lang('account_sub_type') ?> <span class="text-danger">*</span></label>
                    <div class="col-lg-8">
                        <select name="account_sub_type_id" id="account_sub_type_id" class="form-control selectpicker" style="width: 100%">
                            <?php foreach ($account_sub_types as $account_sub_type_id => $account_sub_type): ?>
                                <option value="<?= $account_sub_type_id; ?>" <?= (!empty($chart_of_account->account_sub_type_id) && $chart_of_account->account_sub_type_id == $account_sub_type_id) ? 'selected' : ''; ?>>
                                    <?= $account_sub_type; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label"><?= lang('account_code') ?> <span class="required">*</span></label>
                    <div class="col-sm-8">
                        <input type="text" name="code" value="<?= (!empty($chart_of_account->code) ? $chart_of_account->code : ''); ?>" class="form-control" required />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label"><?= lang('account_name') ?> <span class="required">*</span></label>
                    <div class="col-sm-8">
                        <input type="text" name="name" value="<?= (!empty($chart_of_account->name) ? $chart_of_account->name : ''); ?>" class="form-control" required />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label"><?= lang('status') ?></label>
                    <div class="col-sm-8">
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="checkbox-inline c-checkbox">
                                    <label>
                                        <input type="checkbox" name="status" value="1" <?= (!empty($chart_of_account->status) && $chart_of_account->status == '1') ? 'checked' : ''; ?>>
                                        <span class="fa fa-check"></span> <?= lang('active') ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="checkbox-inline c-checkbox">
                                    <label>
                                        <input type="checkbox" name="status" value="0" <?= (!empty($chart_of_account->status) && $chart_of_account->status == '0') ? 'checked' : ''; ?>>
                                        <span class="fa fa-check"></span> <?= lang('inactive') ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label"><?= lang('description') ?></label>
                    <div class="col-sm-8">
                        <textarea name="description" class="form-control textarea"><?= (!empty($chart_of_account->description) ? $chart_of_account->description : ''); ?></textarea>
                    </div>
                </div>

                <!-- Hidden input values -->
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
            $('select[name="account_sub_type_id"]').html('<option value=""><?= lang('loading') ?></option>').selectpicker('refresh');
            $.ajax({
                url: '<?= base_url('admin/accounting/get_account_sub_types/'); ?>' + account_type_id,
                type: 'POST',
                dataType: 'json',
                success: function (data) {
                    if (data.success) {
                        $('select[name="account_sub_type_id"]').html(data.html).selectpicker('refresh');
                    }
                }
            });
        }

        $(document).ready(function () {
            $('#form').submit(function (e) {
                e.preventDefault(); // Prevent the default form submission
                // Clear previous errors
                $('.text-danger').remove();
                $('.form-control').removeClass('is-invalid');

                var form = $(this);
                var formData = new FormData(this);

                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'error') {
                            // Display field-specific error messages
                            $.each(response.errors, function (key, value) {
                                if (value) {
                                    var element = $('[name="' + key + '"]');
                                    element.addClass('is-invalid');
                                    element.after('<span class="text-danger">' + value + '</span>');
                                }
                            });
                        } else if (response.status === 'success') {
                            // Display success message and redirect
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(function () {
                                window.location.href = response.redirect;
                            });
                        } else if (response.status === 'unauthorized') {
                            // Handle unauthorized access
                            Swal.fire({
                                icon: 'error',
                                title: 'Unauthorized',
                                text: response.message,
                            });
                        }
                    },
                    error: function () {
                        // Handle server errors
                        Swal.fire({
                            icon: 'error',
                            title: 'An error occurred',
                            text: 'Please try again later.',
                        });
                    }
                });
            });
        });
    </script>
<?php } ?>