<?php
$created = can_action_by_label('chart_of_accounts', 'created');
$edited = can_action_by_label('chart_of_accounts', 'edited');
if (!empty($created) || !empty($edited)) {
    ?>
    <div class="panel panel-custom">
        <div class="panel-heading">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                        class="sr-only">Close</span></button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('new') . ' ' . lang('account_sub_type') ?></h4>
        </div>
        <div class="modal-body wrap-modal wrap">
            <form role="form" id="form" data-parsley-validate="" novalidate="" enctype="multipart/form-data"
                  action="<?php echo base_url(); ?>admin/accounting/save_account_sub_type/<?= (!empty($account_sub->account_sub_type_id) ? $account_sub->account_sub_type_id : ''); ?>"
                  method="post" class="form-horizontal form-groups-bordered">
                <div class="form-group">
                    <label class="col-lg-3 control-label"><?= lang('account_type') ?> <span
                                class="text-danger">*</span></label>
                    <div class="col-lg-8">
                        <select name="account_type_id" class="form-control selectpicker" style="width: 100%" required
                                onchange="get_account_sub_types(this.value);"
                        >
                            <?php
                            foreach ($account_types as $account_type_id => $account_type) {
                                ?>
                                <option value="<?= $account_type_id; ?>" <?= (!empty($account_sub->account_type_id) && $account_sub->account_type_id == $account_type_id ? 'selected' : ''); ?>><?= $account_type; ?></option>
                                <?php
                            }
                            ?>
                        </select>

                    </div>
                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-3 control-label"><?= lang('account_sub_type') ?> <span
                                class="required">*</span></label>

                    <div class="col-sm-8">
                        <input type="text" required name="account_sub_type"
                               value="<?= (!empty($account_sub->account_sub_type) ? $account_sub->account_sub_type : ''); ?>"
                               class="form-control"
                        />
                    </div>
                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-3 control-label"><?= lang('status') ?></label>

                    <div class="col-sm-8">
                        <div class="col-sm-4 row">
                            <div class="checkbox-inline c-checkbox">
                                <label>
                                    <input
                                        <?= (!empty($account_sub->status) && $account_sub->status == '1' ? 'checked' : ''); ?>
                                            class="select_one" type="checkbox" name="status" value="1">
                                    <span class="fa fa-check"></span> <?= lang('active') ?>
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="checkbox-inline c-checkbox">
                                <label>
                                    <input
                                        <?= (!empty($account_sub->status) && $account_sub->status == '0' ? 'checked' : ''); ?>
                                            class="select_one" type="checkbox" name="status" value="0">
                                    <span class="fa fa-check"></span> <?= lang('inactive') ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!--hidden input values -->
                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-2">
                        <button type="submit" id="file-save-button"
                                class="btn btn-primary btn-block"><?= lang('save') ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php } ?>
