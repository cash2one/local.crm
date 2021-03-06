<?php
Yii::app()->clientScript->registerScriptFile('http://myzd.oss-cn-hangzhou.aliyuncs.com/static/mobile/js/jquery.form.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile('http://myzd.oss-cn-hangzhou.aliyuncs.com/static/mobile/js/jquery.validate.min.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(Yii::app()->theme->baseUrl . '/js/custom/callOut.js', CClientScript::POS_END);

$user = $this->getCurrentUser();
$callOutUrl = $this->createUrl('phone/callOut');
$savePhoneRecordUrl = $this->createUrl('phone/createPhoneRecord');
$actionUrl = $this->createUrl('phone/createPhoneRecordRemark');
$phoneRecordListUrl = $this->createUrl('phone/phoneRecordList');
$recordFileUrl = $this->createUrl('phone/recordFile', array('uniqueId' => ''));
?>
<style>.w50{width: 50%;}</style>
<div class="modal fade" id="outingCallsModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center">呼出电话</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8 col-sm-12">
                        <?php
                        $form = $this->beginWidget('CActiveForm', array(
                            'id' => 'outingCalls-form',
                            'htmlOptions' => array('class' => 'form-horizontal', 'data-action' => $actionUrl, 'data-phoneRecordListUrl' => $phoneRecordListUrl, 'data-recordFileUrl' => $recordFileUrl),
                            'enableAjaxValidation' => false,
                        ));
                        echo CHtml::hiddenField("call[mobile]");
                        echo CHtml::hiddenField("call[cno]", $user->cno);
                        echo CHtml::hiddenField("call[phoneRecordId]");
                        ?>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label">来电号码</label>
                                <span id="callOutMobile"><?php //echo $model->patient_mobile;           ?></span>
                                <span id="callOut" class="btn btn-primary ml30">呼出</span>
                            </div>
                        </div>
                        <div class="form-group hide">
                            <div class="col-sm-4">
                                <label class="control-label">通话状态：</label>
                                <span>通话中...     </span>
                            </div>
                            <div class="col-sm-2">
                                <label class="pt7">通话异常</label>
                            </div>
                            <div class="col-sm-3">
                                <select class="form-control">
                                    <option value="">选择</option>
                                    <option value="1">问题1</option>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <label class="color-red pt7">遇见了务必标记</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label">通话类型</label>
                                <span>呼出</span>
                                <span>（<?php echo $user->fullname; ?>）</span>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label">备注</label>
                                <textarea id="call_remark" name="call[remark]" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="mt10 text-right clearfix">
                            <button id="submitCallOutBtn" class="btn btn-primary">保存</button>
                            <input id="formReset" type="reset" name="reset" style="display: none;" />
                        </div>
                        <?php $this->endWidget(); ?>
                    </div>
                </div>
                <div class="divider-line mt10 mb10"></div>
                <div class="recordList">
                    <p>通话历史 ( <span id="callMobile"></span> ) ：</p>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>呼出时间</th>
                                <th>挂机时间</th>
                                <th>操作员</th>
                                <th>通话状态</th>
                                <th>备注</th>
                                <th>录音</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="9">加载中...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script>
    $(document).ready(function () {
        //初始化信息
        $('#outingCallsModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var mobile = button.data('mobile');
            var modal = $(this);
            modal.find('.modal-body input#call_mobile').val(mobile);
            var mobileText = new String(mobile);
            modal.find('#callOutMobile').text(mobileText.substr(0, 3) + '****' + mobileText.substr(7));
            modal.find('#callMobile').text(mobileText.substr(0, 3) + '****' + mobileText.substr(7));
            var phoneRecordListUrl = '<?php echo $phoneRecordListUrl; ?>';
            var recordFileUrl = '<?php echo $recordFileUrl; ?>';
            ajaxLoadPhoneRecordByMobile(mobile, phoneRecordListUrl, recordFileUrl);
            enableBtn($('#callOut'));
        });
        $('#callOut').click(function () {
            disabledBtn($(this));
            var mobile = $('#call_mobile').val();
            var cno = $('#call_cno').val();
            var callOutUrl = '<?php echo $callOutUrl; ?>';
            var savePhoneRecordUrl = '<?php echo $savePhoneRecordUrl; ?>';
            callOut(callOutUrl, savePhoneRecordUrl, mobile, cno);
        });
    });

</script>