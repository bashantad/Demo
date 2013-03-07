<?php

class Default_TestController extends Zend_Controller_Action
{

    public function getmicrotime($e = 7)
    {
        list($u, $s) = explode(' ', microtime());
        return bcadd($u, $s, $e);
    }

    public function guidAction()
    {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            var_dump((double) microtime(true) * 10000);
            exit;
            mt_srand((double) microtime() * 10000); //optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            var_dump($charid);
            $hyphen = chr(45); // "-"
            $uuid = chr(123)// "{"
                    . substr($charid, 0, 8) . $hyphen
                    . substr($charid, 8, 4) . $hyphen
                    . substr($charid, 12, 4) . $hyphen
                    . substr($charid, 16, 4) . $hyphen
                    . substr($charid, 20, 12)
                    . chr(125); // "}"
            return $uuid;
        }
    }

    public function logTimeAction($start, $end, $functionName)
    {
        echo "$functionName() = " . (float) ($end - $start) * (60 * 60) . "<br>";
    }

    public function timeStampsAction()
    {
        $day = '01';
        $month = '01';
        $year = '2000';

        $time = array();
        $timestamps = array();
        $time[] = array(microtime(true), 'start');

        $timestamps[] = mktime(0, 0, 0, $month, $day, $year);
        $time[] = array(microtime(true), 'mktime');

        $timestamps[] = strtotime($year . '-' . $month . '-' . $day);
        $time[] = array(microtime(true), 'strtotime');

        $timestamps[] = strtotime(implode('-', array($year, $month, $day)));
        $time[] = array(microtime(true), 'strtotime_Array');

        $timestamps[] = date_create_from_format('!Y-m-d', $year . '-' . $month . '-' . $day)->getTimestamp();
        $time[] = array(microtime(true), 'date_create_from_format');

        $timestamps[] = date_create_from_format('!Y-m-d', implode('-', array($year, $month, $day)))->getTimestamp();
        $time[] = array(microtime(true), 'date_create_from_format_Array');

        $timestamps[] = DateTime::createFromFormat('!Y-m-d', $year . '-' . $month . '-' . $day)->getTimestamp();
        $time[] = array(microtime(true), 'DateTime');

        $timestamps[] = DateTime::createFromFormat('!Y-m-d', implode('-', array($year, $month, $day)))->getTimestamp();
        $time[] = array(microtime(true), 'DateTime_Array');

        foreach ($time as $key => $value) {
            if (end($time) == $value) {
                
            } else {
                $this->logTimeAction($value[0], $time[$key + 1][0], $time[$key + 1][1]);
            }
        }

        // just to show that all timestamps are correct 
        var_dump($timestamps);
    }

    public function makeThumbAction()
    {
        $dir = APPLICATION_PATH . '/../public/uploads/element/images';
        $dirFiles = scandir($dir);
        foreach ($dirFiles as $filename) {
            $file = $dir . '/' . $filename;
            if (is_file($file)) {
                $transform = new Zend_Image_Transform($file, new Zend_Image_Driver_Gd);
                $transform->resizeResample(40, 40);
                $thumbDir = $dir . '/../thumbnails/40x/images';
//                if (! is_dir($thumbDir)) {
//                    mkdir($thumbDir);
//                }
                $transform->save($thumbDir . '/' . $filename);
            }
        }
        print "Default Test Controller";
        exit;
    }

    public function indexAction()
    {
        return;

        //sequences
        $datenow = date("Y-m-d H:i:s");
        //how many squences today
        $sequencedtoday = 5;
        var_dump((uniqid(5, 1)));
        //generate code:
        $code = 'BNA';
        $ts = microtime(true);
        $ymd = (date('Y-m-d', $ts));
        var_dump(strtotime($ymd));
        var_dump(strtotime(date('ymd')));
        $sequence = $sequencedtoday + 1;
        $sequence = str_pad($sequence, 4, 0, STR_PAD_LEFT);
        //return
        printf("BNA-%s-%s", $ymd, $sequence);
        exit;

        $this->timeStampsAction();
        exit;

        print md5('__himal__:himal@magentoadmin123');
        $d = Zend_Date::now()->toValue();
        for ($i = 1; $i <= 5; $i++) {
            var_dump($d);
            var_dump(date('Y-M-d H:i:s', $d));
            $d++;
        }
//        exit;
        var_dump('default test controller');
        exit;
        $dir = '/home/nepaladvisor/public_html/nepaladvisor/public/package/images';
        $dirFiles = scandir($dir);
        foreach ($dirFiles as $filename) {
            $file = $dir . '/' . $filename;
            if (is_file($file)) {
                $transform = new Zend_Image_Transform($file, new Zend_Image_Driver_Gd);
                $transform->resizeResample(70, 70);
                $thumbDir = $dir . '/../thumbnails/70x70/images';
//                if (! is_dir($thumbDir)) {
//                    mkdir($thumbDir);
//                }
                $transform->save($thumbDir . '/' . $filename);
            }
        }
        exit;
        $file = '/home/nepaladvisor/Desktop/test/boudha.jpg';
        $transform = new Zend_Image_Transform($file, new Zend_Image_Driver_Gd);
//        header('Content-Type: image/jpeg');
        $transform->resizeResample(700, 333);
        $transform->save('/home/nepaladvisor/Desktop/test/heaven.jpg');
        exit;
    }

    public function loginAction()
    {
        
    }

    public function excelAction()
    {
        require_once 'Spreadsheet/Excel/Writer.php';

// Creating a workbook
        $workbook = new Spreadsheet_Excel_Writer();

// sending HTTP headers
        $workbook->send('test.xls');

// Creating a worksheet
        $worksheet = & $workbook->addWorksheet('My first worksheet');

// The actual data
        $worksheet->write(0, 0, 'Name');
        $worksheet->write(0, 1, 'Age');
        $worksheet->write(1, 0, 'John Smith');
        $worksheet->write(1, 1, 30);
        $worksheet->write(2, 0, 'Johann Schmidt');
        $worksheet->write(2, 1, 31);
        $worksheet->write(3, 0, 'Juan Herrera');
        $worksheet->write(3, 1, 32);

// Let's send the file
        $workbook->close();
        exit;
    }

    public function pdfAction()
    {
//        $html2pdf = new Pdf_html2pdf();
//        $content = "<page>
//                        <h1>Exemple d'utilisation</h1>
//                        <br>
//                        Ceci est un <b>exemple d'utilisation</b>
//                        de <a href='http://html2pdf.fr/'>HTML2PDF</a>.<br>
//                    </page>";
//        $content = '<page><html xmlns="http://www.w3.org/1999/xhtml"><head>
//        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
//        <style>
//            body{width:570px; margin:auto; font-size:12px; font-family:Arial, Helvetica, sans-serif; line-height:18px;}
//            h1{font-weight:lighter; font-size:30px; padding:10px;}
//            .triphighlights-table{ border: 1px solid #ddd;}
//            .triphighlights-table td{border-top:1px solid #dfdfdf;}
//            .itin-day{background:#ddd;}
//            ul{margin:0; padding:0;}
//            .style1 {background: #ddd; font-weight: bold; }
//            .addressbar{font-size:11px; color:#999;}
//            .booking-details td, .booking-details th,
//                .personal-details td, .personal-details th{border-bottom:1px solid #ddd;}
//            .booking-details th, 
//                .personal-details th{background:#eee;}
//        </style>
//    </head>
//
//    <body>
//        <table border="0" cellpadding="0" cellspacing="0">
//            <thead>
//                <tr>
//                    <th width="35%">&nbsp;</th>
//                    <th width="65%">&nbsp;</th>
//                </tr>
//            </thead>
//            <tbody>
//                <tr>
//                    <td colspan="2"><img src="http://dev.nepaladvisor.local/images/pdf-header.jpg"></td>
//                </tr>
//                <tr>
//                    <td colspan="2" class="addressbar">iTravel Pvt. Ltd. P.O.B # xxxxxx, Phone: +977 1 xxxxxxx, email: info@nepaladvisor.com, fax: +977 1 xxxxxxx</td>
//                </tr>
//                <tr>
//                    <td colspan="2">
//                        <h1>Experiencing Hinduism</h1>
//                    </td>
//                </tr>
//                
//                <tr>
//                    <td colspan="2">
//                        <table width="100%" border="0" cellpadding="2" cellspacing="0" class="booking-details">
//                            <tbody>
//                                <tr>
//                                    <td colspan="2"><h2>Booking Details</h2></td>
//                                </tr>
//                                <tr>
//                                    <td><strong>Booking Code:</strong></td>
//                                    <td><strong>48</strong></td>
//                                </tr>
//                                <tr>
//                                    <td><strong>Booking Status:</strong></td>
//                                    <td><strong>Pending</strong></td>
//                                </tr>
//                                <tr>
//                                    <td width="109">Name:</td>
//                                    <td width="478">Dhiraj Golchha</td>
//                                </tr>
//                                <tr>
//                                    <td>Email:</td>
//                                    <td>dhirajgolchha@gmail.com</td>
//                                </tr>
//                                <tr>
//                                    <td>&nbsp;</td>
//                                    <td>&nbsp;</td>
//                                </tr>
//                            </tbody>
//                        </table>
//                    </td>
//                </tr>
//                
//                <tr>
//                    <td colspan="2"><h2>Personal Details</h2></td>
//                </tr>
//                
//                <tr>
//                    <td colspan="2">
//                        <table width="100%" border="0" cellpadding="2" cellspacing="0" class="personal-details">
//                            <thead>
//                                <tr>
//                                    <th width="30%" align="left">Name</th>
//                                    <th width="50%" align="left">Email</th>
//                                    <th width="20%" align="left">DOB</th>
//                                </tr>
//                            </thead>
//                            <tbody>
//                                                                    <tr>
//                                        <td>Dhiraj Golchha</td>
//                                        <td>dhirajgolchha@gmail.com</td>
//                                        <td>0000-00-00</td>
//                                    </tr>
//                                                                    <tr>
//                                        <td>Dhiraj Golchha</td>
//                                        <td>dhirajgolchha@gmail.com</td>
//                                        <td>0000-00-00</td>
//                                    </tr>
//                                                                <tr>
//                                    <td>&nbsp;</td>
//                                    <td>&nbsp;</td>
//                                    <td>&nbsp;</td>
//                                </tr>
//                            </tbody>
//                        </table>
//                    </td>
//                </tr>
//                <tr>
//                    <td>&nbsp;</td>
//                    <td>&nbsp;</td>
//                </tr>
//                <tr>
//                    <td valign="top">
//                        <table cellpadding="2" cellspacing="0" class="triphighlights-table">
//                            <tbody><tr>
//                                <th colspan="2">Trip Overview</th>
//                            </tr>
//
//                            <tr>
//                                <td colspan="2"><img src="http://dev.nepaladvisor.local/public/package/images/Experiencing%2BHinduism_1340358617.jpg" width="190"></td>
//                            </tr>
//                            <tr>
//                                <td width="84">Duration:</td>
//                                <td width="106">8 Days</td>
//                            </tr>
//                            <tr>
//                                <td>Trip Cost:</td>
//                                <td>$2151.00</td>
//                            </tr>
//                        </tbody></table>
//                    </td>
//                    <td>
//                        In this 8 days tour, we take you deep into ancient religious and cultural life of Nepali people based on Hinduism-one of the oldest religions of the world. We visit innumerable temples and monuments that demonstrate the perfect blend of modern and antique Hindu architectural designs. We take you to the holiest Hindu temples from Pashupatinath- the most sacred Hindu shrine of Lord Shiva to Buddhanilkantha-the temple of god Bishnu reclining on the coils of a cosmic serpent, from Dakshinkali- popular Hindu worship place dedicated to goddess Kali to Pagoda Style Changu Narayan Temple. You can observe artistic sense and excellence of traditional artists and sculptors reflected via wood, metal and stone carvings in these temples and get spiritual ecstasy. We take a tour of all three Durbar Squares: Kathmandu Durbar Square, Patan Durbar Square and Bhaktapur Durbar Square that replicate the plentiful and astonishingly distinctive structures in the world. On the sixth day, we fly to Janakpur, a popular pilgrimage spot and birth place of Sita, - the heroine of the epic,\' Ramayana\' tovisit Janaki temple.  The trip to Manakamana in modern cable car may fulfill all your unfulfilled wishes as it is the temple of wish fulfilling deity. 
//                                            </td>
//                </tr>
//            </tbody>
//        </table>
//    
//</body></html></page>';
//
//        $html2pdf->convert($content, 'test.pdf', 'bookingpdf/test.pdf');
//        exit;
//        $pdf = new Pdf_html2pdf();
//        
//        $html = "asdfdsafsadfsadf";
//        $pdf->convert($html,'uploads/pdf/xcv.pdf','F');
//        
//        //$this->view->pdf = $pdf;
//        exit();
//        $bookingModel = new Default_Model_Booking();
//        $result = $bookingModel->createPdfAttachment(48);
//        var_dump($result);
        $bookingModel = new Default_Model_Booking();
        $emailParams = array(
            'package' => "sdfsdf",
            'quantity' => 1,
            'amount' => 100,
            'total' => 100,
            'username' => 'Asd',
            'invoice' => 12312,
            'txn_no' => 1231,
            'payment_mode' => 'Paypal',
            'date' => date("Y-M-d"),
            'cost' => 100,
            'duration' => 2,
            'count' => 2,
            'status' => 'Pending',
            'url' => $this->view->siteUrl() . '/payment/list'
        );
        $pdfResult = $bookingModel->createPdfAttachment(71);
        if ($pdfResult) {
            $attachment = $this->view->siteUrl() . '/public/bookingpdf/NepalAdvisor-BNA082071.pdf';
        } else {
            $attachment = '';
        }
        $modelNotification = new Notification_Model_EmailSettings();
        $modelNotification->sendEmail($emailParams, 'booking_recieved', 'mailforabhinav@gmail.com', $attachment, 'booking');
        exit;
    }

    public function pdfviewAction()
    {
        $bookingModel = new Default_Model_Booking();
        $bookingData = $bookingModel->getCompleteDetailById(71);
//        echo "<pre>";
//        print_r($bookingData);
//        exit;
//        $html = $bookingModel->createPdfAttachment(48);
        $data = $bookingModel->getbookingdataforattachment(71);
        $html = $this->view->partial('pdf/attachment.phtml', array('data' => $data));
        $this->view->data = $html;
        $this->_helper->layout()->disableLayout();
    }

    public function testmailAction()
    {
        $bookingModel = new Default_Model_Booking();
        $mail = new Zend_Mail();
        $mail->setType(Zend_Mime::MULTIPART_RELATED);
        $mail->setBodyHtml("afasdfsadfsdafdsf");

        $mail->setFrom('info@nepaladvisor.com', 'Nepal Advisor');
        $mail->addTo('mailforabhinav@gmail.com', 'Abhinav');
        $mail->setSubject('Sending email using Zend Framework');

        $attachment = $this->view->siteUrl() . '/public/bookingpdf/BNA082071.pdf';
        $fileContents = file_get_contents($attachment);
        $file = $mail->createAttachment($fileContents);
        $file->filename = "BNA082071.pdf";

        $mail->send();
        exit;
    }

    public function packageprintAction()
    {
        $packageModel = new Package_Model_Mapper_NadPackageMst();
        $data = $packageModel->getpackagedataforprint(25);

        $html = $this->view->partial('pdf/package_detail_print.phtml', array('data' => $data));

        $this->view->data = $html;
        $this->_helper->layout()->disableLayout();
//        print $html;
//        exit;
    }

}

