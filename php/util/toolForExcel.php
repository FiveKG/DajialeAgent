<?php
include_once dirname(__FILE__) . "/../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../util/toolTime.php";
include_once dirname(__FILE__) . "/../util/toolRedis.php";

class ToolForExcel {
    /**
     * 返回账单表格
     * @param $data
     * @param $filename
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     * @return bool
     */
    static function generateSql2Excel($data,$filename) {
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel/IOFactory.php';
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel.php';
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel/Writer/Excel2007.php';
        setlocale(LC_ALL, 'zh_CN');
        $header_arr = array('A','B','C','D','E','F','G','H','I','J','K','L','M', 'N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

        $objPHPExcel = new PHPExcel();						//初始化PHPExcel(),不使用模板
//        $template = $filename.'.xls';			//使用模板
//        $objPHPExcel = PHPExcel_IOFactory::load($template);  	//加载excel文件,设置模板

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

        $dirname = dirname(dirname(dirname(__FILE__))) . '/dajiale/'."$filename";

        //接下来就是写数据到表格里面去
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setCellValue('A1',  "ID");
        $objActSheet->setCellValue('B1',  "手机号");
        $objActSheet->setCellValue('C1',  "邀请码");
        $objActSheet->setCellValue('D1',  "等级");
        $objActSheet->setCellValue('E1',  "角色");
        $objActSheet->setCellValue('F1',  "父级ID");
        $objActSheet->setCellValue('G1',  "父级手机号");
        $objActSheet->setCellValue('H1',  "姓名");


        $i = 2;//第3条开始
        foreach ($data as $agentInfo) {
            $rows = 0;
            foreach ($agentInfo as $key=>$value) {
                $objActSheet->setCellValue($header_arr[$rows].$i," ".$value);
                $rows ++;
            }
            $i++;
        }

        // 1.保存至本地Excel表格
        $objWriter->save($dirname);
        return true;
    }

    static function generateSql2Excel2($data,$filename) {
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel/IOFactory.php';
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel.php';
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel/Writer/Excel2007.php';
        setlocale(LC_ALL, 'zh_CN');
        $header_arr = array('A','B','C','D','E','F','G','H','I','J','K','L','M', 'N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

        $objPHPExcel = new PHPExcel();						//初始化PHPExcel(),不使用模板
//        $template = $filename.'.xls';			//使用模板
//        $objPHPExcel = PHPExcel_IOFactory::load($template);  	//加载excel文件,设置模板

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

        $dirname = dirname(dirname(dirname(__FILE__))) . '/dajiale/'."$filename";

        //接下来就是写数据到表格里面去
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setCellValue('A1',  "ID");
        $objActSheet->setCellValue('B1',  "昵称");
        $objActSheet->setCellValue('C1',  "玩家手机号");
        $objActSheet->setCellValue('D1',  "userId");
        $objActSheet->setCellValue('E1',  "承办商ID");
        $objActSheet->setCellValue('F1',  "创建时间");
        $objActSheet->setCellValue('G1',  "unionId");
        $objActSheet->setCellValue('H1',  "承办商手机");


        $i = 2;//第3条开始
        foreach ($data as $agentInfo) {
            $rows = 0;
            foreach ($agentInfo as $key=>$value) {
                $objActSheet->setCellValue($header_arr[$rows].$i," ".$value);
                $rows ++;
            }
            $i++;
        }

        // 1.保存至本地Excel表格
        $objWriter->save($dirname);

        return true;
    }

    static function generateSql2Excel3($data,$filename) {
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel/IOFactory.php';
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel.php';
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel/Writer/Excel2007.php';
        setlocale(LC_ALL, 'zh_CN');
        $header_arr = array('A','B','C','D','E','F','G','H','I','J','K','L','M', 'N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

        $objPHPExcel = new PHPExcel();						//初始化PHPExcel(),不使用模板
//        $template = $filename.'.xls';			//使用模板
//        $objPHPExcel = PHPExcel_IOFactory::load($template);  	//加载excel文件,设置模板

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

        $dirname = dirname(dirname(dirname(__FILE__))) . '/dajiale/'."$filename";

        //接下来就是写数据到表格里面去
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setCellValue('A1',  "userId");
        $objActSheet->setCellValue('B1',  "invitationUserId");

        $i = 2;//第3条开始
        foreach ($data as $agentInfo) {
            $rows = 0;
            foreach ($agentInfo as $key=>$value) {
                $objActSheet->setCellValue($header_arr[$rows].$i," ".$value);
                $rows ++;
            }
            $i++;
        }

        // 1.保存至本地Excel表格
        $objWriter->save($dirname);
        return true;
    }

    static function generateSql2Excel4($data,$filename) {
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel/IOFactory.php';
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel.php';
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel/Writer/Excel2007.php';
        setlocale(LC_ALL, 'zh_CN');
        $header_arr = array('A','B','C','D','E','F','G','H','I','J','K','L','M', 'N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

        $objPHPExcel = new PHPExcel();						//初始化PHPExcel(),不使用模板
//        $template = $filename.'.xls';			//使用模板
//        $objPHPExcel = PHPExcel_IOFactory::load($template);  	//加载excel文件,设置模板

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

        $dirname = dirname(dirname(dirname(__FILE__))) . '/dajiale/'."$filename";

        //接下来就是写数据到表格里面去
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setCellValue('A1',  "userId");

        $i = 2;//第3条开始
        foreach ($data as $agentInfo) {
            $rows = 0;
            foreach ($agentInfo as $key=>$value) {
                $objActSheet->setCellValue($header_arr[$rows].$i," ".$value);
                $rows ++;
            }
            $i++;
        }

        // 1.保存至本地Excel表格
        $objWriter->save($dirname);
        return true;
    }


    static function A2A() {
        $sql = "select my.id,my.tel,my.id as ivcode, my.level,'代理', my.parent_id, parent.tel as  parent_tel , my.username from ".Config::SQL_DB.".agentusers as my left join ".Config::SQL_DB.".agentusers as parent on my.parent_id = parent.id where my.status = 1 and my.level<>0 ;";
        $result = ToolMySql::query($sql);
        $data = $result->fetch_all(MYSQLI_ASSOC);
        self::generateSql2Excel($data,'代理和代理关系.xlsx');
        return true;
    }

    static function A2P() {
        $sql = "select playerusers.rid,playerusers.username,playerusers.tel,playerusers.id,playerusers.parent_id,playerusers.create_at,playerusers.wxunionid,agentusers.tel as parent_tel from ".Config::SQL_DB.".playerusers as  playerusers inner join ".Config::SQL_DB.".agentusers as agentusers on playerusers.parent_id = agentusers.id;";
        $result = ToolMySql::query($sql);
        $data = $result->fetch_all(MYSQLI_ASSOC);
        self::generateSql2Excel2($data,'代理和玩家关系.xlsx');
        return true;
    }

    static function P2P() {
        $sql = "select id, from_uid from ".Config::SQL_DB.".playerusers where from_uid <>''; ";
        $result = ToolMySql::query($sql);
        $data = $result->fetch_all(MYSQLI_ASSOC);
        self::generateSql2Excel3($data,'玩家之间关系.xlsx');
        return true;
    }

    static function Player() {
        $sql = "select id from ".Config::SQL_DB.".playerusers ";
        $result = ToolMySql::query($sql);
        $data = $result->fetch_all(MYSQLI_ASSOC);
        self::generateSql2Excel4($data,'玩家表.xlsx');
        return true;
    }
}
ToolMySql::conn();
var_dump(ToolForExcel::A2A());
var_dump(ToolForExcel::A2P());
var_dump(ToolForExcel::Player());
var_dump(ToolForExcel::P2P());
ToolMySql::close();



