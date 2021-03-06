<?php

class DoctorController extends AdminController {

    public $model;  // Doctor model.
    public $model_fd_join;  // FacultyDoctorJoin model.

    /**
     * @param int $id Doctor.id from either GET or POST.     
     */

    public function filterDoctorContext($filterChain) {
        $id = null;
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
        } else if (isset($_POST['id'])) {
            $id = $_POST['id'];
        }
        $this->loadModel($id);

        $filterChain->run();
    }

    /**
     * @param int $id FacultyDoctorJoin.id from GET or POST.
     */
    public function filterFacultyDoctorContext($filterChain) {
        $id = null;
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
        } else if (isset($_POST['id'])) {
            $id = $_POST['id'];
        }
        $this->loadFacultyDoctorJoin($id);

        $filterChain->run();
    }

    /**
     * @return array action filters
     */
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
            'postOnly + delete', // we only allow deletion via POST request
            'DoctorContext + update delete addFaculty addAvatar',
            //  'FacultyDoctorJoinContext + deleteDF'
            'rights',
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            array('allow', // allow all users to perform 'index' and 'view' actions
                'actions' => array(''),
                'users' => array('*'),
            ),
            /*
              array('allow', // allow authenticated user to perform 'create' and 'update' actions
              'actions' => array('create', 'update'),
              'users' => array('@'),
              ),
             */
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array('index', 'view', 'create', 'update', 'admin', 'delete', 'createExpertTeam', 'addFaculty', 'addAvatar', 'createDoctor', 'ajaxLoadloadHospitalDept', 'addDisease', 'updateDisease', 'updateContracted'),
//                'users' => array('superbeta'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id) {
        $model = Doctor::model()->getByAttributes(array('id' => $id), array('doctorDiseases', 'doctorExpertTeam'));
        //var_dump($model); exit();
        $this->render('view', array(
            'model' => $model,
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        //$model = new Doctor;
        $form = new DoctorForm();
        $form->initModel();

        // Uncomment the following line if AJAX validation is needed
        $this->performAjaxValidation($form);

        if (isset($_POST['DoctorForm'])) {
            $form->attributes = $_POST['DoctorForm'];
            $doctorMgr = new DoctorManager();
            $doctorMgr->createDoctor($form);
            if ($form->hasErrors() === false) {
                $this->redirect(array('view', 'id' => $form->id));
            }
        }

        $this->render('create', array(
            'model' => $form,
        ));
    }

    /**
     * 创建一个医生 只存于doctor  虚拟角色
     */
    public function actionCreateDoctor() {
//        var_dump($_POST['DoctorFormAdmin']); exit();
        //$model = new Doctor;
        $form = new DoctorFormAdmin();
        $form->initModel();

        // Uncomment the following line if AJAX validation is needed
        $this->performAjaxValidation($form);

        if (isset($_POST['DoctorFormAdmin'])) {
            $form->attributes = $_POST['DoctorFormAdmin'];
            if ($form->validate()) {
                $form->name = $form->fullname;
                //给省市id 医院 科室名称赋值
                $hospital = Hospital::model()->getById($form->hospital_id);
                $form->state_id = $hospital->state_id;
                $form->city_id = $hospital->city_id;
                $form->country_id = $hospital->country_id;
                $form->hospital_name = $hospital->getName();
                $dept = HospitalDepartment::model()->getById($form->hp_dept_id);
                $form->hp_dept_name = $dept->getName();
                //判断是否有值，若无则赋值为null
                if (strIsEmpty($form->description)) {
                    $form->description = null;
                }
                if (strIsEmpty($form->career_exp)) {
                    $form->career_exp = null;
                }
                if (strIsEmpty($form->reason_one)) {
                    $form->reason_one = null;
                }
                if (strIsEmpty($form->reason_two)) {
                    $form->reason_two = null;
                }
                if (strIsEmpty($form->reason_three)) {
                    $form->reason_three = null;
                }
                if (strIsEmpty($form->reason_four)) {
                    $form->reason_four = null;
                }
                if (strIsEmpty($form->honour)) {
                    $form->honour = null;
                }
                $doctor = new Doctor();
                $doctor->setAttributes($form->attributes);

                $doctor->honour = $form->honour; //explode('#', $form->honour);
                if ($doctor->save()) {
                    //保存成功 保存关联表
                    $join = new HospitalDeptDoctorJoin();
                    $join->hp_dept_id = $doctor->hp_dept_id;
                    $join->doctor_id = $doctor->getId();
                    if ($join->save()) {
                        $this->redirect(array('view', 'id' => $doctor->getId()));
                    }
                }
            }
        }
        $this->render('create', array(
            'model' => $form,
        ));
    }

    /**
     * 异步加载科室
     * @param type $hid
     */
    public function actionAjaxLoadloadHospitalDept($hid) {
        $this->headerUTF8();
        $models = HospitalDepartment::model()->getAllByHospitalId($hid);
        $promptText = '--无--';
        $output = null;
        if (is_array($models)) {
            if (count($models) == 1) {
                $listData = CHtml::listData($models, 'id', 'name');
                foreach ($listData as $id => $name) {
                    $output.= CHtml::tag('option', array('value' => $id), CHtml::encode($name), true);
                }
            } else if (count($models) > 1) {
                $listData = CHtml::listData($models, 'id', 'name');
                $output.= CHtml::tag('option', array('value' => ''), CHtml::encode($promptText), true);
                foreach ($listData as $id => $name) {
                    $output.= CHtml::tag('option', array('value' => $id), CHtml::encode($name), true);
                }
            }
        } else {
            $output = CHtml::tag('option', array('value' => ''), CHtml::encode($promptText), true);
        }
        echo $output;
        Yii::app()->end();
    }

    /**
     * 加载医生关联疾病
     */
    public function actionAddDisease($id) {
        $diseaseMgr = new DiseaseManager();
        $data = $diseaseMgr->loadAllDiseasesByDoctorId($id);
        $this->render('addDisease', array(
            'data' => $data,
        ));
    }

    /**
     * 修改医生关联疾病
     */
    public function actionUpdateDisease() {
        //var_dump($_POST['DoctorDiseaseJoinForm']);        exit();
        $doctorId = null;
        if (isset($_POST['DoctorDiseaseJoinForm'])) {
            $values = $_POST['DoctorDiseaseJoinForm'];
            $doctorId = $values['id'];
            //获取数据库现存的关联疾病
            $diseaseMgr = new DiseaseManager();
            $data = $diseaseMgr->loadAllDiseasesByDoctorId($doctorId);
            $diseaseList = $data->diseaseIds;  // array of DiseaseDoctorJoin model.
            //获取前台更改的关联疾病
            $diseaseIdsInput = $values['disease'];
            $form = new DoctorDiseaseJoinForm();
            $form->initModel($doctorId, $diseaseList);
            $form->setDiseaseListInput($diseaseIdsInput, true);
            //具体的添加 删除操作
            $diseaseMgr->updateDoctorDiseaseJoin($form);
            if ($form->hasErrors() === false) {
                //成功页面跳转
                $this->redirect(array('view', 'id' => $doctorId));
            }
        }
        $this->redirect(array($this->createUrl('addDisease'), 'id' => $doctorId));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
//    public function actionUpdate($id) {
//
//        $model = $this->loadModel($id);
//        $form = new DoctorForm();
//        $form->initModel($model);
//        //   var_dump($model->attributes);
//        //   var_dump($form->attributes);
//        // Uncomment the following line if AJAX validation is needed
//        $this->performAjaxValidation($form);
//
//        if (isset($_POST['DoctorForm'])) {
//            $form->attributes = $_POST['DoctorForm'];
//
//            $doctorMgr = new DoctorManager();
//            $doctorMgr->updateDoctor($form);
//            if ($form->hasErrors() === false) {
//                $this->redirect(array('view', 'id' => $model->getId()));
//            }
//        }
//
//        $this->render('update', array(
//            'model' => $form,
//        ));
//    }

    public function actionUpdate($id) {
        $form = new DoctorFormAdmin();
        $model = $this->loadModel($id);
        $form->initModel($model);
        // Uncomment the following line if AJAX validation is needed
        $this->performAjaxValidation($form);
        if (isset($_POST['DoctorFormAdmin'])) {
            $form->attributes = $_POST['DoctorFormAdmin'];
            $doctorMgr = new DoctorManager();
            $doctorMgr->updateAdminDoctor($form);
            if ($form->hasErrors() === false) {
                $this->redirect(array('view', 'id' => $model->getId()));
            }
        }
//        if (!isset($_POST['DoctorFormAdmin']) && is_null($form->honour) == false) {
//            $honour = '';
//            foreach ($form->honour as $v) {
//                $honour.=$v . '#';
//            }
//            $form->honour = substr($honour, 0, strlen($honour) - 1);
//        }
        $this->render('update', array(
            'model' => $form,
        ));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id) {
        $model = $this->loadModel($id);
        $doctorMgr = new DoctorManager();
        $doctorMgr->deleteDoctor($model);

        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if (!isset($_GET['ajax']))
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
    }

    /**
     * Lists all models.
     */
    public function actionIndex() {
        $dataProvider = new CActiveDataProvider('Doctor', array(
            'pagination' => array(
                'pageSize' => 20,
            //  'pageVar' => 'page'
            ),
        ));
        $this->render('index', array(
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * Manages all models.
     */
    public function actionAdmin() {
        $model = new Doctor('search');
        $model->unsetAttributes();  // clear any default values
        if (isset($_GET['Doctor']))
            $model->attributes = $_GET['Doctor'];

        $this->render('search', array(
            'model' => $model,
        ));
    }

    public function actionSearchResult() {
        $pbSeach = new DoctorSearch($_GET);
        $criteria = $pbSeach->criteria;
        $dataProvider = new CActiveDataProvider('Doctor', array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => 20,
            ),
        ));
        $this->renderPartial('searchResult', array(
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * 添加医生头像
     * @param type $id
     */
    public function actionAddAvatar($id) {
        $doctor = $this->loadModel($id);
        if (isset($_POST['DoctorAvatar'])) {
            $docMgr = new DoctorManager();
            if (isset($_FILES['file'])) {
                $file = $_FILES['file'];
                if ($docMgr->saveDoctorAvatar($doctor, $file)) {
                    $this->redirect(array('view', 'id' => $doctor->id));
                }
            }
        }
        $this->render('addAvatar', array(
            'doctor' => $doctor
        ));
    }

    /**
     * 创建团队
     * @param type $id
     */
    public function actionCreateExpertTeam($id) {
        $doctor = $this->loadModel($id, array('doctorExpertTeam'));
        $form = new ExpertTeamForm();
        $form->initExpertTeam($doctor->getDoctorExpertTeam());
        if (isset($_POST['ExpertTeamForm'])) {
            $values = $_POST['ExpertTeamForm'];
            $doctorMgr = new DoctorManager();
            $doctorMgr->saveExpertTeam($doctor, $values);
            $this->redirect(array('view', 'id' => $doctor->id));
        }
        $this->render('createExpertTeam', array(
            'model' => $doctor,
            'teamForm' => $form,
        ));
    }

    public function actionAddFaculty($id) {
        $model = $this->loadModel($id, array('doctorFaculties'));
        $fdJoin = new FacultyDoctorJoin();
        $fdJoin->doctor_id = $model->id;
        $fdJoin->setExistingFacultyList($model->getFaculties());
        $fdJoin->loadOptionsFaculty();

        $this->performAjaxValidation($fdJoin);

        if (isset($_POST['FacultyDoctorJoin'])) {
            $values = $_POST['FacultyDoctorJoin'];
            $fdJoin->attributes = $values;

            if ($fdJoin->save()) {
                //go to hospital/view.
                $this->redirect(array('view', 'id' => $model->id));
            }
        }

        $this->render('addFaculty', array(
            'model' => $model,
            'fdJoin' => $fdJoin
        ));
    }

    /*     * ** 修改医生签约信息 *** */

    public function actionUpdateContracted($id) {
        $doctorMgr = new DoctorManager();
        $output = $doctorMgr->updateDoctorContracted($id);
        $this->renderJsonOutput($output);
    }

    /*
      public function actionLoadAvatar($uid=null) {
      $fileUrl = '';
      if ($uid === null || $uid == '') {
      $fileUrl = DoctorAvatar::getAbsDefaultAvatarUrl();
      } else {
      $avatar = DoctorAvatar::model()->getByUID($uid);

      if (isset($avatar)) {
      $fileUrl = $avatar->getAbsThumbnailUrl();
      } else {
      $fileUrl = DoctorAvatar::getAbsDefaultAvatarUrl();
      }
      }
      header('Location: ' . $fileUrl);
      }
     */

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return Doctor the loaded model
     * @throws CHttpException
     */
    public function loadModel($id, $with = null) {
        $model = Doctor::model()->getById($id, $with);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }

    public function loadFacultyDoctorJoin($id) {
        if ($this->model_fd_join === null) {
            $this->model_fd_join = FacultyDoctorJoin::model()->getById($id);
            if ($this->model_fd_join === null) {
                throw new CHttpException(404, 'The requested page does not exist.');
            }
        }
        return $this->model_fd_join;
    }

    /**
     * Performs the AJAX validation.
     * @param Doctor $model the model to be validated
     */
    protected function performAjaxValidation($model) {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'doctor-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    public function actionSearchDoctor($name) {
        $doctorMgr = new DoctorManager();
        $output = $doctorMgr->searchDoctorProfileByDoctorName($name);
        $this->renderJsonOutput($output);
    }

    //保存文件信息
    public function actionAjaxSaveAvatarFile() {
        $output = array('status' => 'no');
        if (isset($_POST['doctor'])) {
            $values = $_POST['doctor'];
            $doctor = Doctor::model()->getById($values['id']);
            $doctor->base_url = $values['remote_domain'];
            $doctor->avatar_url = $values['remote_file_key'];
            $doctor->has_remote = StatCode::HAS_REMOTE;
            $doctor->remote_domain = $values['remote_domain'];
            $doctor->remote_file_key = $values['remote_file_key'];
            if ($doctor->save()) {
                $output['status'] = 'ok';
                $output['doctorId'] = $doctor->id;
            } else {
                $output['errors'] = $doctor->getErrors();
            }
        } else {
            $output['errors'] = 'no data....';
        }
        $this->renderJsonOutput($output);
    }

    public function actionAjaxUpload() {
        $url = 'http://file.mingyizhudao.com/api/tokendravatar';
        $data = $this->send_get($url);
        $output = array('uptoken' => $data['uptoken']);
        $this->renderJsonOutput($output);
    }

}
