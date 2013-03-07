<?php

class Company_FeatureController extends Zend_Controller_Action
{

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('delete', 'json')
                ->addActionContext('status', 'json')
                ->addActionContext('parent', 'json')
                ->initContext();
    }

    public function viewAction()
    {
        $parent_feature = '';
        $id = $this->_getParam('id');
        $featureModel = new Company_Model_Feature();
        $feature = $featureModel->getDetailById($id);
        $parentFeature = '';
        if ($feature->upper_feature_id != 0) {
            $parentFeature = $featureModel->getDetailbyId($feature->upper_feature_id)->name;
        }
        $this->view->parent_feature = $parentFeature;
        $this->view->feature = $feature;
    }

    public function indexAction()
    {
        $featureModel = new Company_Model_Feature();
        $this->view->allData = $featureModel->fetchAll();
    }

    public function addAction()
    {
        $this->view->placeholder('title')->set('Add Company Feature');
        $form = new Company_Form_FeatureForm();
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $formData = $this->getRequest()->getPost();
                if($formData['upper_feature_id']==''){
                    $formData['upper_feature_id']=0;
                }
                $featureModel = new Company_Model_Feature();
                $company_feature_id = $featureModel->add($formData);
                if ($formData['defined_id'] == '') {
                    $trigger = null;
                    $featureModel->update($trigger, $company_feature_id);
                }

                $data = array(
                    'company_feature_id' => $company_feature_id,
                    'upper_feature_id' => $formData['upper_feature_id']
                );
                $featureMapModel = new Company_Model_FeatureMap();
                $featureMapModel->add($data);
                $this->_helper->redirector('list');
            }
        }
    }

    public function deleteAction()
    {
        $id = $this->_getParam('id');
        $featureMapModel = new Company_Model_FeatureMap();
        $featureMapModel->delete($id);
        $featureModel = new Company_Model_Feature();
        $featureModel->delete($id);
    }

    public function editAction()
    {
        $this->view->placeholder('title')->set('Edit Company Feature');
        $id = $this->_getParam('id');
        //var_dump($this->getRequest()->getParams());
        $featureModel = new Company_Model_Feature();
        $currentFeature = $featureModel->getDetailbyId($id);
        $allFeatures = $featureModel->fetchFeatureOption($currentFeature->element_id); //where company_feature_id!=$id
        // it prevents accidental assertion of same value to both parent feature and current feature
        $features = array();
        foreach ($allFeatures as $key => $value):
            if ($key != $currentFeature->company_feature_id . "::" . $currentFeature->defined_id) {
                $features[$key] = $value;
            }
        endforeach;
        $form = new Company_Form_FeatureForm();
        $form->upper_feature_id->addMultiOptions($features);
        $arr = (array) $currentFeature;
        $arr['element_id'] = $arr['element_id'] . "::" . $arr['element_defined_id'];
        if ($currentFeature->upper_feature_id != 0) {
            $upperFeature = $featureModel->getDetailbyId($currentFeature->upper_feature_id);
            $arr['upper_feature_id'] = $arr['upper_feature_id'] . "::" . $upperFeature->defined_id;
        }
        $form->populate($arr);
        $this->view->form = $form;
        $this->view->id = $id;
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $formData = $this->getRequest()->getPost();
                 if($formData['upper_feature_id']==''){
                    $formData['upper_feature_id']=0;
                }
                $featureModel->update($formData, $id);
                $data = array(
                    'upper_feature_id' => $formData['upper_feature_id']
                );
                $featureMapModel = new Company_Model_FeatureMap();
                $featureMapModel->update($data, $id);
                $this->_helper->redirector('list');
            }
        }
    }

    public function listAction()
    {
        $this->view->placeholder('title')->set('Company Feature');
        $config = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'grid.ini', 'production');
        $grid = Bvb_Grid::factory('Table', $config);
        $data = $this->getItemList();
        $source = new Bvb_Grid_Source_Array($data);
        $grid->setSource($source);
        $grid->setImagesUrl('/grid/');
        $editColumn = new Bvb_Grid_Extra_Column();//"company-feature-".md5(123).""base64_encode("{{company_feature_id}}").
        //$editColumn->setPosition('right')->setName('Edit')->setDecorator("<a href=\"/company-feature-{{company_feature_id}}-".md5("{{company_feature_id}}")."html\">Edit</a><input class=\"address-id\" name=\"address_id[]\" type=\"hidden\" value=\"{{company_feature_id}}\"/>");
        //$editColumn->setPosition('right')->setName('Edit')->setDecorator("<a href=\"/company-feature-{{company_feature_id}}-".md5(md5("{{company_feature_id}}")).".html\">Edit</a><input class=\"address-id\" name=\"address_id[]\" type=\"hidden\" value=\"{{company_feature_id}}\"/>");
        $editColumn->setPosition('right')->setName('Edit')->setDecorator("<a href=\"/company/feature/edit/id/{{company_feature_id}}\">Edit</a><input class=\"address-id\" name=\"address_id[]\" type=\"hidden\" value=\"{{company_feature_id}}\"/>");

        $deleteColumn = new Bvb_Grid_Extra_Column();
        $deleteColumn->setPosition('right')->setName('Delete')->setDecorator("<a class=\"delete-data\" href=\"/company/feature/delete/id/{{company_feature_id}}\">Delete</a>");
        $grid->addExtraColumns($editColumn, $deleteColumn);
        $filters = new Bvb_Grid_Filters();
        $filters->addFilter('status', array('values' => array('E' => 'Enabled', 'D' => 'Disabled'), 'class' => 'form-select'));
        $grid->addFilters($filters);
        if ($this->_getParam('_exportTo') == null) {
            $grid->addClassCellCondition('status', "'{{status}}'=='E'", "permitted", "notpermitted");
        }
        $grid->updateColumn('name', array(
            'decorator' => '{{name}}',
            'escape' => true
        ));
        $grid->updateColumn('company_feature_id', array('hidden' => true));
        $grid->setExport(array('print', 'word', 'csv', 'excel', 'pdf'));
        $grid->setRecordsPerPage(20);
        $grid->setPaginationInterval(array(
            5 => 5,
            10 => 10,
            20 => 20,
            30 => 30,
            40 => 40,
            50 => 50,
            100 => 100
        ));
        $this->view->grid = $grid->deploy();
    }

    public function getItemList()
    {
        $featureMapModel = new Company_Model_Feature();
        $features = $featureMapModel->fetchHierarchy();
        $allData = array();
        $i = 1;
        foreach ($features as $feature):
            $data = array();
            $data['sn'] = $i++;
            $name = $feature['name'];
            $id = $feature['company_feature_id'];
            $data['name'] = "<a href=\"/company/feature/view/id/$id\">$name</a>";
            $data['company_feature_id'] = $feature['company_feature_id'];
            $data['short_name'] = $feature['short_name'];
            $data['feature_type'] = $feature['feature_type'];
            $data['element_type'] = $feature['element_type'];
            $data['status'] = $feature['status'];

            $allData[] = $data;
        endforeach;
        return $allData;
    }

    public function statusAction()
    {
        $company_feature_id = $this->_getParam('id');
        $featureModel = new Company_Model_Feature();
        $rowStatus = $featureModel->changeStatus($company_feature_id);
        $permClass = ($rowStatus == 'E') ? 'permitted' : 'notpermitted';
        $this->view->permClass = $permClass;
        $this->view->rowStatus = $rowStatus;
    }

    public function parentAction()
    {
        $element_id = $this->_getParam('id');
        $featureModel = new Company_Model_Feature();
        $features = $featureModel->fetchHierarchy($element_id);
        $data = array();
        if ($features != null) {
            foreach ($features as $feature) {
                $arr = array();
                $arr['company_feature_id'] = $feature['company_feature_id'] . "::" . $feature['defined_id'];
                $arr['name'] = $feature['name'];
                $data[] = $arr;
            }
        }
        $this->view->features = $data;
        $this->view->html = $this->view->render('feature/parent.phtml');
    }

}

?>
