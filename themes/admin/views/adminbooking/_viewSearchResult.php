<?php
$urlResImage = Yii::app()->baseUrl;
$pbOrder = isset($data->orderAdminbooking) ? $data->orderAdminbooking : null;
$doctorMobile = isset($data->bkOwner) ? $data->bkOwner->username : '无';
//var_dump($data);
//print_r(CJSON::encode($data));exit;
$userDoctorMobile = isset($data->userDoctorMobile) ? $data->userDoctorMobile : null;
?>
<?php if (count($pbOrder) == 0) { ?>
    <tr>
        <td ><a target="_blank" href="<?php echo $this->createUrl('view', array('id' => $data->id)) ?>" ><?php echo $data->ref_no; ?></a></td>
        <td ><?php echo $data->patient_name; ?></td>
        <td >
            <a href="<?php echo $this->createUrl('patient/view', array('mobile' => $data->patient_mobile)); ?>" target="_blank"><?php echo $data->patient_mobile; ?></a>
            <?php
            if (is_null($userDoctorMobile) == false) {
                echo "<a target='_blank' href={$this->createUrl('user/view', array('id' => $userDoctorMobile->id))}><i class='fa fa-user-md' aria-hidden='true'></i></a>";
            }
            ?>
        </td>
        <td ><?php echo $data->disease_name; ?></td>
        <td ><?php echo $data->date_created; ?></td>
        <td ><?php echo $data->getWorkSchedule(); ?></td>
        <td ><?php echo $data->admin_user_name; ?></td>
        <td ><?php echo $data->getCustomerAgent(); ?></td>
        <td ><?php echo $data->bd_user_name; ?></td>
        <td colspan="2" class="text-center">暂无支付单信息</td>
        <td ><?php echo $data->getCustomerRequest(); ?></td>
        <td ><?php echo $data->final_time; ?></td>
        <td ><?php echo $data->creator_doctor_name; ?></td>
        <td ><?php echo $doctorMobile; ?></td>
        <td ><?php echo $data->getCustomerIntention(); ?></td>
        <td ><?php echo $data->expected_doctor_name; ?></td>
        <td ><?php echo $data->expected_hospital_name; ?></td>
        <td ><?php echo $data->expected_doctor_mobile; ?></td>
        <td ><?php echo $data->getCustomerDiversion(); ?></td>
        <td ><?php echo $data->getBookingType(); ?></td>
    </tr> 
    <?php
} else {
    foreach ($pbOrder as $key => $order) {
        ?>
        <tr>
            <?php if ($key == 0) { ?>
                <td rowspan="<?php echo count($pbOrder); ?>"><a target="_blank" href="<?php echo $this->createUrl('view', array('id' => $data->id)) ?>" ><?php echo $data->ref_no; ?></a></td>
                <td rowspan="<?php echo count($pbOrder); ?>"><?php echo $data->patient_name; ?></td>
                <td rowspan="<?php echo count($pbOrder); ?>">
                    <a href="<?php echo $this->createUrl('patient/view', array('mobile' => $data->patient_mobile)); ?>" target="_blank"><?php echo $data->patient_mobile; ?></a>
                    <?php
                    if (is_null($userDoctorMobile) == false) {
                        echo "<a target='_blank' href={$this->createUrl('user/view', array('id' => $userDoctorMobile->id))}><i class='fa fa-user-md' aria-hidden='true'></i></a>";
                    }
                    ?>
                </td>
                <td rowspan="<?php echo count($pbOrder); ?>"><?php echo $data->disease_name; ?></td>
                <td rowspan="<?php echo count($pbOrder); ?>"><?php echo $data->date_created; ?></td>
                <td rowspan="<?php echo count($pbOrder); ?>"><?php echo $data->getWorkSchedule(); ?></td>
                <td rowspan="<?php echo count($pbOrder); ?>"><?php echo $data->admin_user_name; ?></td>
                <td rowspan="<?php echo count($pbOrder); ?>"><?php echo $data->getCustomerAgent(); ?></td>
                <td rowspan="<?php echo count($pbOrder); ?>"><?php echo $data->bd_user_name; ?></td>
            <?php } ?>
            <td><?php echo $order->getOrderType(); ?></td>
            <td><?php echo $order->getIsPaid(); ?></td>
            <?php if ($key == 0) { ?>
                <td rowspan="<?php echo count($pbOrder); ?>"><?php echo $data->getCustomerRequest(); ?></td>
                <td rowspan="<?php echo count($pbOrder); ?>"><?php echo $data->final_time; ?></td>
                <td rowspan="<?php echo count($pbOrder); ?>"><?php echo $data->creator_doctor_name; ?></td>
                <td rowspan="<?php echo count($pbOrder); ?>"><?php echo $doctorMobile; ?></td>
                <td rowspan="<?php echo count($pbOrder); ?>"><?php echo $data->getCustomerIntention(); ?></td>
                <td rowspan="<?php echo count($pbOrder); ?>"><?php echo $data->expected_doctor_name; ?></td>
                <td rowspan="<?php echo count($pbOrder); ?>"><?php echo $data->expected_hospital_name; ?></td>
                <td rowspan="<?php echo count($pbOrder); ?>"><?php echo $data->expected_doctor_mobile; ?></td>
                <td rowspan="<?php echo count($pbOrder); ?>"><?php echo $data->getCustomerDiversion(); ?></td>
                <td rowspan="<?php echo count($pbOrder); ?>"><?php echo $data->getBookingType(); ?></td>
            <?php } ?>

        </tr>
        <?php
    }
}
?>


