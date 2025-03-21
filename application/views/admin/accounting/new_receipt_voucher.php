<?= message_box('success'); ?>
<?= message_box('error');

$created = can_action_by_label('chart_of_accounts', 'created');
$edited = can_action_by_label('chart_of_accounts', 'edited');
if (!empty($created) || !empty($edited)) {
?>
<div class="nav-tabs-custom">
    <!-- Tabs within a box -->
    <ul class="nav nav-tabs">
        <li><a
                    href="<?= base_url('admin/accounting/' . $active) ?>"><?= lang($active) ?></a>
        </li>
        <li class="active">
            <a href="<?= base_url('admin/accounting/new_' . $active) ?>">
                <?= ' ' . lang('new') . ' ' . lang($active) ?></a>
        </li>
    </ul>
    <div class="tab-content bg-white pt-lg mt-lg">
        <!-- ************** general *************-->
        <div class="tab-pane active" id="manage">
            <form role="form" id="form" data-parsley-validate="" novalidate="" enctype="multipart/form-data"
                  action="<?php echo base_url(); ?>admin/accounting/save_receipt_voucher/<?= (!empty($receipt_voucher->voucher_id) ? $receipt_voucher->voucher_id : ''); ?>"
                  method="post" class="form-horizontal form-groups-bordered">
                <div class="form-group">
                    <label class="col-lg-3 control-label"><?= lang('reference_no') ?> <span
                                class="text-danger">*</span></label>
                    <div class="col-lg-7">
                        <?php $this->load->helper('string'); ?>
                        <input type="text" class="form-control" value="<?php
                        if (!empty($receipt_voucher)) {
                            echo $receipt_voucher->reference_no;
                        } else {
                            if (empty(config_item('receipt_voucher_format'))) {
                                echo config_item('receipt_voucher_prefix');
                            }
                            if (config_item('increment_receipt_voucher') == 'FALSE') {
                                $this->load->helper('string');
                                echo random_string('nozero', 6);
                            } else {
                                echo $this->accounting_model->generate_receipt_voucher_prefix();
                            }
                        }
                        ?>"
                               name="reference_no">
                    </div>

                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label"><?= lang('date') ?> <span
                                class="text-danger">*</span></label>
                    <div class="col-lg-7">
                        <div class="input-group">
                            <input required type="text" name="date"
                                   class="form-control datepicker"
                                   value="<?php
                                   if (!empty($receipt_voucher->date)) {
                                       echo $receipt_voucher->date;
                                   } else {
                                       echo date('Y-m-d');
                                   }
                                   ?>"
                                   data-date-format="<?= config_item('date_picker_format'); ?>">
                            <div class="input-group-addon">
                                <a href="#"><i class="fa fa-calendar"></i></a>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="form-group">
                    <label for="field-1" class="col-sm-3 control-label"><?= lang('paid_from') ?> <span
                                class="text-danger">*</span></label>

                    <div class="col-sm-8">
                        <select name="account_id" class="form-control select_box" required>
                            <?php
                            foreach ($accounts as $account_id => $account) {
                                ?>
                                <option value="<?= $account_id == 0 ? '' : $account_id;
                                ?>" <?php
                                if (!empty($receipt_voucher->account_id)) {
                                    if ($receipt_voucher->account_id == $account_id) {
                                        echo 'selected';
                                    }
                                }
                                ?>><?= $account ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="field-1" class="col-sm-3 control-label"><?= lang('notes') ?></label>

                    <div class="col-sm-8">
                    <textarea name="notes"
                              class="form-control textarea"><?= (!empty($receipt_voucher->notes) ? $receipt_voucher->notes : ''); ?></textarea>
                    </div>
                </div>

                <div class="table-responsive s_table mt-lg pt-lg">
                    <table class="table invoice-items-table items">
                        <thead style="background: #e8e8e8">
                        <tr>
                            <th>#</th>
                            <th class="col-sm-4"
                            ><?= lang('client') ?></th>
                            <th class="col-sm-3"
                            ><?= lang('description') ?></th>
                            <th><?= lang('amount') ?></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody id="receipt_voucher_items">

                        <?php
                        $i = 1;
                        if (!empty($receipt_voucher_items)) {
                            foreach ($receipt_voucher_items as $receipt_voucher_item) {
                                ?>
                                <tr class="item-row">
                                    <td><?= $i ?></td>
                                    <td>
                                        <select name="supplier_client_id[]" required
                                                class="form-control accounts-dropdown">
                                            <?php
                                            if (!empty($clients)) {
                                                foreach ($clients as $client_id => $client) {
                                                    ?>
                                                    <option value="<?= $client_id == 0 ? '' : $client_id; ?>"
                                                        <?php
                                                        if (!empty($receipt_voucher_item->supplier_client_id)) {
                                                            if ($receipt_voucher_item->supplier_client_id == $client_id) {
                                                                echo 'selected';
                                                            }
                                                        }
                                                        ?>><?= $client ?></option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="description[]"
                                               class="form-control description"
                                               value="<?= (!empty($receipt_voucher_item->description) ? $receipt_voucher_item->description : ''); ?>">
                                    </td>
                                    <td>
                                        <input type="text" name="amount[]"
                                               class="form-control amount"
                                               value="<?= (!empty($receipt_voucher_item->amount) ? $receipt_voucher_item->amount : ''); ?>">
                                    </td>
                                    <td>
                                        <a href="#" class="delete-row text-danger"><i class="fa fa-trash-o"></i></a>
                                    </td>
                                </tr>
                                <?php
                                $i++;
                            }
                        } else {
                            ?>
                            <tr class="item-row">
                                <td><?= $i ?></td>
                                <td>
                                    <select name="supplier_client_id[]" required class="form-control accounts-dropdown">
                                        <?php
                                        if (!empty($clients)) {
                                            foreach ($clients as $client_id => $client) {
                                                ?>
                                                <option value="<?= $client_id == 0 ? '' : $client_id; ?>"
                                                ><?= $client ?></option>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="description[]" class="form-control description">
                                </td>
                                <td>
                                    <input type="text" name="amount[]" required class="form-control amount">
                                </td>
                                <td>
                                    <a href="#" class="text-danger delete-row"><i class="fa fa-trash-o"></i></a>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>

                        <tfoot>
                        <tr>
                            <td colspan="3">
                                <a href="#" class="btn btn-default btn-sm add-row"><i
                                            class="fa fa-plus"></i>
                                    <?= lang('add_row') ?></a>
                            </td>
                            <td colspan="3">
                                <a href="#" class="btn btn-default btn-sm add-row pull-right"><i
                                            class="fa fa-plus"></i>
                                    <?= lang('add_row') ?></a>
                            </td>

                        </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="row">
                    <div class="col-xs-8 pull-right">
                        <table class="table text-right">
                            <tbody>
                            <tr id="total_amount">
                                <td><span class="bold"><?= lang('total_amount') ?></td>
                                <td class="total_amount">0.00
                                </td>
                                <input type="hidden" name="total_amount" value="0.00">
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>


                <!--hidden input values -->
                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-2">

                    </div>
                </div>
                <div class="btn-bottom-toolbar text-right">
                    <button type="submit" id="file-save-button"
                            class="btn btn-primary "><?= lang('save') ?></button>

                </div>
            </form>
        </div>
    </div>
    <script type="text/javascript">

        $(document).on("change blur keyup", function () {
            calculate_receipt_voucher();
        });

        // onkeyup event for only numbers 0-9
        $(document).on('keyup', '.amount', function (e) {
            if (/\D/g.test(this.value)) {
                this.value = this.value.replace(/\D/g, '');
            }
        });

        function calculate_receipt_voucher() {
            var total_amount = 0;
            $('.item-row').each(function () {
                var amount = $(this).find('.amount').val();

                if (amount !== '') {
                    total_amount += parseFloat(amount);
                }
            });
            $('#total_amount .total_amount').html(total_amount.toFixed(2));
            $('input[name="total_amount"]').val(total_amount.toFixed(2));

            if (total_amount > 0) {
                // enable save button
                $('#file-save-button').attr('disabled', false);
            } else {
                // disable save button
                $('#file-save-button').attr('disabled', true);
            }
        }

        $(document).on('click', '.add-row', function (e) {
            e.preventDefault();
            const row = '<tr class="item-row">';
            // calculate row number for new row
            const serial_number_td = '<td>' + ($('.item-row').length + 1) + '</td>';
            // account td
            const account_td = '<td>' +
                '<select name="supplier_client_id[]" required class="form-control accounts-dropdown">' +
                <?php
                if (!empty($clients)) {
                foreach ($clients as $client_id => $client) {
                ?>
                '<option value="<?= $client_id == 0 ? '' : $client_id; ?>"><?= $client ?></option>' +
                <?php
                }
                }
                ?>
                '</select>' +
                '</td>';
            // description td
            const description_td = '<td>' +
                '<input type="text" name="description[]" class="form-control description">' +
                '</td>';
            // debit td
            const amount_td = '<td>' +
                '<input type="text" name="amount[]" required class="form-control amount">' +
                '</td>';
            // delete td
            const delete_td = '<td>' +
                '<a href="#" class="text-danger delete-row"><i class="fa fa-trash-o"></i></a>' +
                '</td>';
            // end tr
            const end_tr = '</tr>';
            // append all td
            $('#receipt_voucher_items').append(row + serial_number_td + account_td + description_td + amount_td + delete_td + end_tr);
            select2_init();
            calculate_receipt_voucher();

        });

        $(document).on('click', '.delete-row', function (e) {
            e.preventDefault();
            if ($('.item-row').length > 1) {
                $(this).closest('tr').remove();
                calculate_receipt_voucher();
            }
        });


        $(document).ready(function () {
            select2_init();
            calculate_receipt_voucher();
        });

        function select2_init() {
            $("select.accounts-dropdown").select2();
        }


    </script>
    <?php } ?>
