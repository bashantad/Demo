<?php

class Content_ReportController extends Zend_Controller_Action
{

    public function init()
    {
        
    }

    public function indexAction()
    {
        $formData = $this->getRequest()->getPost();
        $contentMapper = new Content_Model_ContentMapMapper();
        $results = $contentMapper->searchContent($formData);
        $config = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . "configs" . DIRECTORY_SEPARATOR . "grid.ini", 'production');
        $grid = Bvb_Grid::factory('Table', $config);
        $source = new Bvb_Grid_Source_Array($results);
        $grid->setSource($source);
        $grid->setImagesUrl('/grid/');
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

}

?>
