<?php
/**
 * Created by PhpStorm.
 * User: Shinoa
 * Date: 03.09.2017
 * Time: 0:08
 */

namespace gsoft\Controllers;


use gsoft\Database\CargoMapper;
use gsoft\Database\ClientMapper;
use gsoft\Database\ManagerMapper;
use gsoft\Database\PasswordMapper;
use gsoft\Database\UserMapper;
use gsoft\Exceptions\ControllerException;
use gsoft\Exceptions\JsonException;
use gsoft\LoginManager;
use PHPExcel;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;
use PHPExcel_Worksheet;
use PHPExcel_Writer_Excel5;
use PHPMailer\PHPMailer\PHPMailer;

class InfoController extends PageController
{
    private $root;
    private $pdo;
    
    public function __construct($root, $pdo)
    {
        parent::__construct();
        $this->root = $root;
        $this->pdo = $pdo;
    }

    public function start()
    {
        $this->execute();
    }
    
    /**
     * Echoes json, containing object or FALSE.
     * @param $id
     */
    public function aboutClient($id)
    {
        $clientMapper = new ClientMapper($this->pdo);
        $client = $clientMapper->getClient($id);
        echo json_encode($client, JSON_UNESCAPED_UNICODE) ;
    }
    
    /**
     * Echoes json, containing object or FALSE.
     * @param $id
     */
    public function aboutManager($id)
    {
        $clientMapper = new ManagerMapper($this->pdo);
        $client = $clientMapper->getManager($id);
        echo json_encode($client, JSON_UNESCAPED_UNICODE) ;
    }
    
    /**
     * Echoes json output: sends fileContents and filename of xls file for user to save.
     * If user is not logged or on error: sends false
     * Throws JsonException with info about nature of error,
     * is something is really wrong and operation cannot succeed.
     * @throws JsonException
     */
    public function loggedCargoToExcel()
    {
        $cargoMapper    = new CargoMapper($this->pdo);
        $userMapper     = new UserMapper($this->pdo);
        $passwordMapper = new PasswordMapper($this->pdo);
        $loginMan  = new LoginManager($userMapper, $passwordMapper, $this->pdo);
        //check whether user is logged as client or manager
        if ($loginMan->isClient() ) {
            $clientID = $loginMan->getLoggedID();
            
            //fetch cargo from db
            $cargo = $cargoMapper->getForClient($clientID, 'all');
            //if didn't get it - throw exception
            //why we throwing here exception: because we need to know exact reason of error
            if ( !$cargo ) {
                throw new JsonException('Cannot save cargo for client as xls: cannot fetch cargo from DB');
            }
            
            //build users's cargo xls
            $xls = $this->getCargoToExcel($clientID, $cargo, 'client');
            //get contents of file encoded as string
            $filename = "cargo_for_client_id{$clientID}_" . date('Y-m-d-H-i-s') . '.xls';
            $excelFileContents = $this->getExcelAsBase64($xls, $filename);
    
            //send in json base64 xls content, and also filename, because its not in base64
            $result = ['fileContents' => $excelFileContents, 'filename' => $filename];
            echo json_encode($result);
            
            
        } elseif ($loginMan->isManager()) {
            $manID = $loginMan->getLoggedID();
            //fetch cargo from db
            $cargo = $cargoMapper->getForManager($manID, 'all');
            
            //if didn't get it - throw exception
            //why we throwing here exception: because we need to know exact reason of error
            if ( !$cargo ) {
                throw new JsonException('Cannot save cargo for manager as xls: cannot fetch cargo from DB');
            }
            
            //build users's cargo xls
            $xls = $this->getCargoToExcel($manID, $cargo, 'manager');
            //get contents of file encoded as string
            $filename = "cargo_for_manager_id{$manID}_" . date('Y-m-d-H-i-s') . '.xls';
            $excelFileContents = $this->getExcelAsBase64($xls, $filename);
    
            //send in json base64 xls content, and also filename, because its not in base64
            $result = ['fileContents' => $excelFileContents, 'filename' => $filename];
            
            echo json_encode($result);
            
        } else {
            //if no authed user use this script
            echo json_encode(false);
        }
    }
    
    /**
     * Throws JsonException with info about nature of error,
     * is something is really wrong and operation cannot succeed.
     * @param string $email
     * @throws JsonException
     */
    public function loggedCargoMail($email = '')
    {
        //mappers for db quering
        $cargoMapper    = new CargoMapper($this->pdo);
        $userMapper     = new UserMapper($this->pdo);
        $passwordMapper = new PasswordMapper($this->pdo);
        $loginMan  = new LoginManager($userMapper, $passwordMapper, $this->pdo);
        //check whether user is logged as client or manager
        if ($loginMan->isClient() ) {
            //find id of client and it's email
            $clientID = $loginMan->getLoggedID();
            $clientMapper = new ClientMapper($this->pdo);
            //if no email specified - send to user's mail
            if (empty($email)) {
                $email = $clientMapper->getClient($clientID)->getEmail();
            }
    
            //fetch cargo from db
            $cargo = $cargoMapper->getForClient($clientID, 'all');
            //if didn't get it - throw exception
            //why we throwing here exception: because we need to know exact reason of error
            if ( !$cargo ) {
                throw new JsonException('Cannot save cargo for client as xls: cannot fetch cargo from DB');
            }
            
            //build users's cargo xls
            $xls = $this->getCargoToExcel($clientID, $cargo, 'client');
            //construct filename for temporary file to attach
            $filename = "cargo_for_client_{$clientID}_" . date('Y-m-d-H-i-s');
            //send file through mail
            //result to return
            $success = $this->mailExcel($xls, $filename, 'Ваши грузы ' . date('Y-m-d-H-i-s'), $email);
            if ($success) {
                $result = $email;
            } else {
                throw new JsonException('Cannot send xls for client to its mail');
            }
            
        } elseif ($loginMan->isManager()) {
            //find id of manager and it's email
            $manID = $loginMan->getLoggedID();
            $clientMapper = new ManagerMapper($this->pdo);
            //if no email specified - send to user's mail
            if (empty($email)) {
                $email = $clientMapper->getManager($manID)->getEmail();
            }
    
            //fetch cargo from db
            $cargo = $cargoMapper->getForManager($manID, 'all');
            //if didn't get it - throw exception
            //why we throwing here exception: because we need to know exact reason of error
            if ( !$cargo ) {
                throw new JsonException('Cannot save cargo for client as xls: cannot fetch cargo from DB');
            }
    
            //build users's cargo xls
            $xls = $this->getCargoToExcel($manID, $cargo, 'manager');
            //construct filename for temporary file to attach
            $filename = "cargo_for_manager_{$manID}_" . date('Y-m-d-H-i-s');
            //send file through mail
            //result to return
            $success = $this->mailExcel($xls, $filename, 'Грузы ваших клиентов ' . date('Y-m-d-H-i-s'), $email);
            //return email where sent on success instead of just true
            if ($success) {
                $result = $email;
            } else {
                throw new JsonException('Cannot send xls for client to its mail');
            }
            
        } else {
            //if user is not logged - indicate that cannot send
            $result = false;
        }
        
        //echo result in json
        echo json_encode($result);
    }
    
    /**
     * @param $personID
     * @param string $forWhom
     * @return PHPExcel
     * @throws \Exception
     */
    private function getCargoToExcel($personID, $cargo, $forWhom = 'client')
    {
        //check for whom fetch cargo
        if ($forWhom === 'client') {
            $title = 'Ваши грузы';
        } elseif ($forWhom === 'manager') {
            $title = 'Грузы ваших клиентов';
        } else {
            throw new \Exception('Incorrect type of person to fetch xls for.');
        }
        
        $xls = new PHPExcel();
        $xls->setActiveSheetIndex(0);
        //Get active list
        $sheet = $xls->getActiveSheet();
        //Name it
        $sheet->setTitle($title);
    
        //set size of cols to auto
        for($col = 'A'; $col !== 'G'; $col++) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        //Insert welcome text into A1
        $sheet->setCellValue("A1", $title);
        $sheet->getStyle('A1')->getFill()->setFillType(
            PHPExcel_Style_Fill::FILL_SOLID);
        $sheet->getStyle('A1')->getFill()->getStartColor()->setRGB('EEEEEE');
        //Merge cells
        $sheet->mergeCells('A1:F1');
        //Align cells
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(
            PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        //populate xls
        $this->arrayOfCargoToXls($sheet, $cargo);
        return  $xls;
    }
    
    /**
     * Dump array of Cargo to xls format.
     * @param PHPExcel_Worksheet $sheet
     * @param $cargo
     */
    private function arrayOfCargoToXls(PHPExcel_Worksheet $sheet, $cargo)
    {
        //name columns
        $row = 2;
        $sheet->setCellValueByColumnAndRow(0, $row, 'ID груза');
        $sheet->setCellValueByColumnAndRow(1, $row, 'Номер контейнера');
        $sheet->setCellValueByColumnAndRow(2, $row, 'Компания-клиент');
        $sheet->setCellValueByColumnAndRow(3, $row, 'Фамилия, Имя менеджера');
        $sheet->setCellValueByColumnAndRow(4, $row, 'Ожидаемая дата прибытия');
        $sheet->setCellValueByColumnAndRow(5, $row, 'Статус');
        //style names' row
        $sheet->getStyle('A2:F2')
              ->getFill()
              ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
              ->getStartColor()
              ->setARGB('3392E28C');
        //starting row of data insertion
        $row = 3;
        //for each cargo insert data
        foreach ($cargo as $item) {
            //for each field
            $sheet->setCellValueByColumnAndRow(0, $row, $item->getId());
            $sheet->setCellValueByColumnAndRow(1, $row, $item->getContainer());
            $sheet->setCellValueByColumnAndRow(2, $row, $item->getClientName());
            $sheet->setCellValueByColumnAndRow(3, $row, $item->getManagerName());
            $sheet->setCellValueByColumnAndRow(4, $row, $item->getDateArrival());
            $sheet->setCellValueByColumnAndRow(5, $row, $item->getStatus());
            // Alignment
            for ($col = 0; $col < 6; $col++) {
                $sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->
                setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            }
            $row++;
        }
    }
    
    /**
     * Returns string, containing base64 encoded content of xls file.
     * @param PHPExcel $xls
     * @param $filename
     * @return string
     */
    private function getExcelAsBase64(PHPExcel $xls, $filename)
    {
        // Важно! Поскольку вывод в stdout, выпущенные файлы нужно "ловить"
        ob_start();
        // Выводим HTTP-заголовки
        header ( "Expires: Mon, 1 Apr 1974 05:00:00 GMT" );
        header ( "Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT" );
        header ( "Cache-Control: no-cache, must-revalidate" );
        header ( "Pragma: no-cache" );
        header ( "Content-type: application/vnd.ms-excel" );
        header ( "Content-Disposition: attachment; filename=$filename" );
    
        // Выводим содержимое файла
        $objWriter = new PHPExcel_Writer_Excel5($xls);
        $objWriter->save('php://output');
        $excelFileContents = ob_get_clean();
        
        return base64_encode($excelFileContents);
    }
    
    /**
     * @param PHPExcel $xls
     * @param $filename
     * @param $title
     * @param $address
     * @return bool
     */
    private function mailExcel(PHPExcel $xls, $filename, $title, $address)
    {
        $email = new PHPMailer();
        $email->CharSet = 'UTF-8';
        $email->From      = 'webmaster@gsoft.local';
        $email->FromName  = 'Shinoa the Webmaster';
        $email->Subject   = $title;
        $email->Body      = 'Запрошенная вами таблица грузов в прикреплённом xls файле.';
        $email->AddAddress($address);
        
        //create random temp file to store xls and get it's path
        //it will be deleted automatically
        $handle = tmpfile();
        $meta_data = stream_get_meta_data($handle);
        $filepath = $meta_data['uri'];
        //write xls into temp file
        $objWriter = new PHPExcel_Writer_Excel5($xls);
        $objWriter->save($filepath);
        $email->AddAttachment($filepath, "$filename.xls" );
        
        return $email->Send();
    }
}