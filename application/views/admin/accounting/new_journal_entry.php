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
                    href="<?= base_url('admin/accounting/journal_entry') ?>"><?= lang('journal_entry') ?></a>
        </li>
        <li class="active">
            <a href="<?= base_url() ?>admin/accounting/new_journal_entry">
                <?= ' ' . lang('new') . ' ' . lang('journal_entry') ?></a>
        </li>
    </ul>
    <div class="tab-content bg-white pt-lg mt-lg">
        <!-- ************** general *************-->
        <div class="tab-pane active" id="manage">
            <form role="form" id="form" data-parsley-validate="" novalidate="" enctype="multipart/form-data"
                  action="<?php echo base_url(); ?>admin/accounting/save_journal_entry/<?= (!empty($journal_entry->journal_id) ? $journal_entry->journal_id : ''); ?>"
                  method="post" class="form-horizontal form-groups-bordered">
                <div class="form-group">
                    <label class="col-lg-3 control-label"><?= lang('reference_no') ?> <span
                                class="text-danger">*</span></label>
                    <div class="col-lg-7">
                        <?php $this->load->helper('string'); ?>
                        <input type="text" class="form-control" value="<?php
                        if (!empty($journal_entry)) {
                            echo $journal_entry->reference_no;
                        } else {
                            if (empty(config_item('journal_entry_format'))) {
                                echo config_item('journal_entry_prefix');
                            }
                            if (config_item('increment_journal_entry') == 'FALSE') {
                                $this->load->helper('string');
                                echo random_string('nozero', 6);
                            } else {
                                echo $this->accounting_model->generate_journal_entry_prefix();
                            }
                        }
                        ?>"
                               name="reference_no">
                    </div>

                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label"><?= lang('journal_entry_date') ?> <span
                                class="text-danger">*</span></label>
                    <div class="col-lg-7">
                        <div class="input-group">
                            <input required type="text" name="date"
                                   class="form-control datepicker"
                                   value="<?php
                                   if (!empty($journal_entry->date)) {
                                       echo $journal_entry->date;
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
                    <label for="field-1" class="col-sm-3 control-label"><?= lang('notes') ?></label>

                    <div class="col-sm-8">
                    <textarea name="notes"
                              class="form-control textarea"><?= (!empty($journal_entry->notes) ? $journal_entry->notes : ''); ?></textarea>
                    </div>
                </div>

                <div class="table-responsive s_table mt-lg pt-lg">
                    <table class="table invoice-items-table items">
                        <thead style="background: #e8e8e8">
                        <tr>
                            <th>#</th>
                            <th class="col-sm-4"
                            ><?= lang('account') ?></th>
                            <th class="col-sm-3"
                            ><?= lang('description') ?></th>
                            <th
                            ><?= lang('debit') ?></th>
                            <th><?= lang('credit') ?></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody id="journal_entry_items">

                        <?php
                        $i = 1;
                        if (!empty($journal_entry_items)) {
                            foreach ($journal_entry_items as $journal_entry_item) {
                                ?>
                                <tr class="item-row">
                                    <td><?= $i ?></td>
                                    <td>
                                        <select name="account[]" class="form-control accounts-dropdown">
                                            <?php
                                            if (!empty($chart_of_accounts)) {
                                                foreach ($chart_of_accounts as $chart_of_account) {
                                                    ?>
                                                    <option <?= (!empty($journal_entry_item->chart_of_account_id) && $journal_entry_item->chart_of_account_id == $chart_of_account->chart_of_account_id ? 'selected' : '') ?>
                                                            value="<?= $chart_of_account->chart_of_account_id ?>"><?= $chart_of_account->name ?></option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="description[]"
                                               class="form-control description"
                                               value="<?= (!empty($journal_entry_item->description) ? $journal_entry_item->description : ''); ?>">
                                    </td>
                                    <td>
                                        <input type="text" name="debit[]"
                                               class="form-control debit"
                                               value="<?= (!empty($journal_entry_item->debit) ? $journal_entry_item->debit : ''); ?>">
                                    </td>
                                    <td>
                                        <input type="text" name="credit[]"
                                               class="form-control credit"
                                               value="<?= (!empty($journal_entry_item->credit) ? $journal_entry_item->credit : ''); ?>">
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
                                    <select name="account[]" class="form-control accounts-dropdown">
                                        <option value=""><?= lang('select') ?></option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="description[]" class="form-control description">
                                </td>
                                <td>
                                    <input type="text" name="debit[]" class="form-control debit">
                                </td>
                                <td>
                                    <input type="text" name="credit[]" class="form-control credit">
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
                            <tr id="total_debit">
                                <td><span class="bold"><?= lang('total_debit') ?></td>
                                <td class="total_debit">0.00
                                </td>
                                <input type="hidden" name="total_debit" value="0.00">
                            </tr>
                            <tr id="total_credit">
                                <td><span class="bold"><?= lang('total_credit') ?></td>
                                <td class="total_credit">0.00</td>
                                <input type="hidden" name="total_credit" value="0.00">
                            </tr>

                            <tr id="total_diff">
                                <td><span class="bold"><?= lang('total_diff') ?></td>
                                <td class="total_diff">0.00</td>
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
            calculate_journal_entry();
        });

        // onkeyup event for only numbers 0-9
        $(document).on('keyup', '.debit, .credit', function (e) {
            if (/\D/g.test(this.value)) {
                this.value = this.value.replace(/\D/g, '');
            }
            // if write on debit field then set credit field 0
            if ($(this).hasClass('debit')) {
                $(this).closest('tr').find('.credit').val(0);
            }
            // if write on credit field then set debit field 0
            if ($(this).hasClass('credit')) {
                $(this).closest('tr').find('.debit').val(0);
            }
        });

        function calculate_journal_entry() {
            var total_debit = 0;
            var total_credit = 0;
            var total_diff = 0;
            $('.item-row').each(function () {
                var debit = $(this).find('.debit').val();
                var credit = $(this).find('.credit').val();

                if (debit !== '') {
                    total_debit += parseFloat(debit);
                }
                if (credit !== '') {
                    total_credit += parseFloat(credit);
                }
            });
            total_diff = total_debit - total_credit;
            $('#total_debit .total_debit').html(total_debit.toFixed(2));
            $('#total_credit .total_credit').html(total_credit.toFixed(2));
            $('#total_diff .total_diff').html(total_diff.toFixed(2));
            $('input[name="total_debit"]').val(total_debit.toFixed(2));
            $('input[name="total_credit"]').val(total_credit.toFixed(2));

            if (total_diff === 0) {
                $('#file-save-button').prop('disabled', false);
                $('#total_diff').removeClass('text-danger');
            } else {
                $('#file-save-button').prop('disabled', true);
                $('#total_diff').addClass('text-danger');
            }

        }

        $(document).on('click', '#file-save-button', function (e) {
            // check if total debit and credit is equal or not
            if ($('input[name="total_debit"]').val() !== $('input[name="total_credit"]').val()) {
                e.preventDefault();
                toastr['error']('<?= lang('total_debit_credit_not_equal') ?>');
                // highlight the dfiference field
                $('#total_diff').addClass('text-danger');
            }


        });

        $(document).on('click', '.add-row', function (e) {
            e.preventDefault();
            const row = '<tr class="item-row">';
            // calculate row number for new row
            const serial_number_td = '<td>' + ($('.item-row').length + 1) + '</td>';
            // account td
            const account_td = '<td>' +
                '<select name="account[]" class="form-control accounts-dropdown">' +
                '<option value=""><?= lang('select') ?></option>' +
                '</select>' +
                '</td>';
            // description td
            const description_td = '<td>' +
                '<input type="text" name="description[]" class="form-control description">' +
                '</td>';
            // debit td
            const debit_td = '<td>' +
                '<input type="text" name="debit[]" class="form-control debit">' +
                '</td>';
            // credit td
            const credit_td = '<td>' +
                '<input type="text" name="credit[]" class="form-control credit">' +
                '</td>';
            // delete td
            const delete_td = '<td>' +
                '<a href="#" class="text-danger delete-row"><i class="fa fa-trash-o"></i></a>' +
                '</td>';
            // end tr
            const end_tr = '</tr>';
            // append all td
            $('#journal_entry_items').append(row + serial_number_td + account_td + description_td + debit_td + credit_td + delete_td + end_tr);
            select2_init();
            calculate_journal_entry();

        });

        $(document).on('click', '.delete-row', function (e) {
            e.preventDefault();
            if ($('.item-row').length > 1) {
                $(this).closest('tr').remove();
                calculate_journal_entry();
            }
        });


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


    </script>
    <?php } ?>
