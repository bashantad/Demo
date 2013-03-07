<?php

class Company_IndexController extends Zend_Controller_Action
{

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('delete', 'json')
                ->addActionContext('status', 'json')
                ->addActionContext('filter-contents', 'json')
                ->initContext();
    }

    public function indexAction()
    {
        $companyModel = new Company_Model_Company();
        $this->view->companies = $companyModel->fetchAll();
    }

    public function addAction()
    {
        $this->view->placeholder('title')->set('Add Company');
        $form = new Company_Form_CompanyForm();
        if($this->_getParam("element_id")){
            $elementId = $this->getContentElementId($this->_getParam("element_id"));
            $form = new Company_Form_CompanyForm($elementId);
        }
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $formData = $this->getRequest()->getPost();
                $companyModel = new Company_Model_Company();
                $company_id = $companyModel->add($formData);
                if (!isset($formData['defined_id'])) {
                    $trigger = null;
                    $companyModel->update($trigger, $company_id);
                }
                $this->_helper->redirector('list');
            }
        }
    }

    public function deleteAction()
    {
        $id = $this->_getParam('id');
        $companyModel = new Company_Model_Company();
        $companyModel->delete($id);
    }

    public function editAction()
    {
        $this->view->placeholder('title')->set('Edit Company');
        $id = $this->_getParam('id');
        $companyModel = new Company_Model_Company();
        $company = $companyModel->getDetailbyId($id);
        $form = new Company_Form_CompanyForm();
        $elementId = ($this->_getParam("element_id"))?$this->_getParam("element_id"):$company["element_id"];
        $elementId = $this->getContentElementId($elementId);
        $form = new Company_Form_CompanyForm($elementId);
        $company['element_id'] = $company['element_id'] . "::" . $company['element_defined-id'];
        $company['location_id'] = $company['location_id'] . "::" . $company['location_defined-id'];
        $form->populate($company);
        $this->view->form = $form;
        $this->view->id = $id;
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $formData = $this->getRequest()->getPost();
                $companyModel->update($formData, $id);
                $this->_helper->redirector('list');
            }
        }
    }

    public function listAction()
    {
        $this->view->placeholder('title')->set('Company');
        $config = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'grid.ini', 'production');
        $grid = Bvb_Grid::factory('Table', $config);
        $data = $this->_listdata();
        $source = new Bvb_Grid_Source_Array($data);
        $grid->setSource($source);
        $grid->setImagesUrl('/grid/');
        $editColumn = new Bvb_Grid_Extra_Column();
        $editColumn->setPosition('right')->setName('Edit')->setDecorator("<a href=\"/company/index/edit/id/{{company_id}}\">Edit</a><input class=\"address-id\" name=\"address_id[]\" type=\"hidden\" value=\"{{company_id}}\"/>");
        $deleteColumn = new Bvb_Grid_Extra_Column();
        $deleteColumn->setPosition('right')->setName('Delete')->setDecorator("<a class=\"delete-data\" href=\"/company/index/delete/id/{{company_id}}\">Delete</a>");
        $grid->addExtraColumns($editColumn, $deleteColumn);
        $grid->updateColumn('name', array(
            'decorator' => '{{name}}',
            'escape' => true
        ));
        $grid->updateColumn('company_id', array('hidden' => true));
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
        $filters = new Bvb_Grid_Filters();
        $filters->addFilter('status', array('values' => array('E' => 'Enabled', 'D' => 'Disabled'), 'class' => 'form-select'));
        $grid->addFilters($filters);
        if ($this->_getParam('_exportTo') == null) {
            $grid->addClassCellCondition('status', "'{{status}}'=='E'", "permitted", "notpermitted");
        }
        $this->view->grid = $grid->deploy();
    }

    public function _listdata()
    {
        $rows = array();
        $companyModel = new Company_Model_Company();
        $allData = $companyModel->listAll();
        $i = 1;
        foreach ($allData as $company):
            $data = array();
            $data['sn'] = $i++;
            $data['name'] = "<a href=\"/company/detail/view/id/$company->company_id\">$company->name</a>";
            $data += (array) $company;
            $rows[] = $data;
        endforeach;
        return $rows;
    }

    public function statusAction()
    {
        $company_id = $this->_getParam('id');
        $companyModel = new Company_Model_Company();
        $rowStatus = $companyModel->changeStatus($company_id);
        $permClass = ($rowStatus == 'E') ? 'permitted' : 'notpermitted';
        $this->view->permClass = $permClass;
        $this->view->rowStatus = $rowStatus;
    }
    
    public function filterContentsAction()
    {
        $id = $this->_getParam("id");
        $elementId = $this->getContentElementId($id);
        $contents = '';
        if($elementId){
            $contentMapper = new Content_Model_ContentMapper;
            $contents= $contentMapper->fetchContentHierarchy($elementId);
        }
        $this->view->contents = $contents;
        $this->view->html = $this->view->render("index/filter-contents.phtml");
    }
    public function getContentElementId($id)
    {
        switch($id){
            case 2:
                $elementId = 161;
                break;
            case 3:
                $elementId = 230;
                break;
            case 6:
                $elementId = 252;
                break;
            case 9:
                $elementId = 163;
                break;
            default:
                $elementId = 0;
        }
        return $elementId;
    }
}

?>
