<div id="panelChart5" class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title"><?= lang('bugs') . ' ' . lang('report') ?></div>
    </div>
    <div class="panel-body">
        <div class="bar-chart-pie flot-chart"></div>
    </div>
</div>
<?php
$unconfirmed = 0;
$in_progress = 0;
$confirmed = 0;
$resolved = 0;
$verified = 0;

$bugs_info = $this->user_model->my_permission('tbl_bug', $profile_info->user_id);

if (!empty($bugs_info)):foreach ($bugs_info as $v_bugs):
    if ($v_bugs->bug_status == 'unconfirmed') {
        $unconfirmed += count(array($v_bugs->bug_status));
    }
    if ($v_bugs->bug_status == 'in_progress') {
        $in_progress += count(array($v_bugs->bug_status));
    }
    if ($v_bugs->bug_status == 'confirmed') {
        $confirmed += count(array($v_bugs->bug_status));
    }
    if ($v_bugs->bug_status == 'resolved') {
        $resolved += count(array($v_bugs->bug_status));
    }
    if ($v_bugs->bug_status == 'verified') {
        $verified += count(array($v_bugs->bug_status));
    }
endforeach;
endif;
?>
<script src="<?= base_url() ?>assets/plugins/Flot/jquery.flot.js"></script>
<script src="<?= base_url() ?>assets/plugins/Flot/jquery.flot.tooltip.min.js"></script>
<script src="<?= base_url() ?>assets/plugins/Flot/jquery.flot.resize.js"></script>
<script src="<?= base_url() ?>assets/plugins/Flot/jquery.flot.pie.js"></script>
<script src="<?= base_url() ?>assets/plugins/Flot/jquery.flot.time.js"></script>
<script src="<?= base_url() ?>assets/plugins/Flot/jquery.flot.categories.js"></script>
<script src="<?= base_url() ?>assets/plugins/Flot/jquery.flot.spline.min.js"></script>
<?php if (!empty($unconfirmed) || !empty($in_progress) || !empty($confirmed) || !empty($resolved) || !empty($verified)) {?>
    <script type="text/javascript">
        $(document).ready(function () {
            // CHART PIE
            // -----------------------------------
            (function (window, document, $, undefined) {

                $(function () {

                    var data = [{
                        "label": "<?= lang('unconfirmed')?>",
                        "color": "#ff902b",
                        "data": <?= $unconfirmed?>
                    }, {
                        "label": "<?= lang('in_progress')?>",
                        "color": "#5d9cec",
                        "data": <?= $in_progress?>
                    }, {
                        "label": "<?= lang('confirmed')?>",
                        "color": "#23b7e5",
                        "data": <?= $confirmed?>
                    }, {
                        "label": "<?= lang('resolved')?>",
                        "color": "#7266ba",
                        "data": <?= $resolved?>
                    }, {
                        "label": "<?= lang('verified')?>",
                        "color": "#27c24c",
                        "data": <?= $verified?>
                    },];

                    var options = {
                        series: {
                            pie: {
                                show: true,
                                innerRadius: 0,
                                label: {
                                    show: true,
                                    radius: 0.8,
                                    formatter: function (label, series) {
                                        return '<div class="flot-pie-label">' +
                                                //label + ' : ' +
                                            Math.round(series.percent) +
                                            '%</div>';
                                    },
                                    background: {
                                        opacity: 0.8,
                                        color: '#222'
                                    }
                                }
                            }
                        }
                    };

                    var chart = $('.bar-chart-pie');
                    if (chart.length)
                        $.plot(chart, data, options);

                });

            })(window, document, window.jQuery);
        });

    </script>
<?php } ?>