<?php
$max_uploaded_size = "20M";
error_reporting(7);
ini_set("display_errors","1");
ini_set("upload_max_filesize", $max_uploaded_size);
ini_set("post_max_size", $max_uploaded_size);

$charset = "utf-8";     //для файловой системы windlows прописать cp1251
$preview_types = array("jpg","png","gif","bmp");
$forbidden = explode(",","php,js,htm,cgi,xml,wml,pl,perl,asp,php3,php4,html");
$uploaddir = "/public/uploads";
$globaldirs = array("images"=>array("ext"=>"jpg,gif,png,bmp"),
 //                   "documents"=>array("ext"=>"doc,xls,pdf,ppt,txt,csv"),
 //                   "media"=>array("ext"=>"mp3,wmv,avi,mp4,mov,flv,mkv,mpg"),
//                    "other"=>array("ext"=>"dat,exe,zip,rar,iso"),
 //                   "downloaded"=>array("ext"=>""),
              );

$OUT = array();
$workdir = $_SERVER["DOCUMENT_ROOT"].$uploaddir;
function GetData($path,$ext=false,$filter=false){
    global $workdir, $charset;
    if(strstr($path,"..")) die();
    $dir = $workdir.$path."/";
	

    $dir = iconv('utf-8', $charset, $dir);

    $files = array_diff(scandir($dir), array('.','..'));
    foreach($files AS $file)
    {
        $val = (is_file($dir.$file) ? "file" : "dir");
        if($ext)
        {
            $type = strtolower(substr(strrchr($file,"."),1));
            $typearr = explode(",",$ext);
            if(in_array($type,$typearr))
                $DATA[$val][] = $file;
        }
        else
        {
            if($filter)
            {
                if(stristr($file, $filter))
                    $DATA[$val][] = $file;
            }
            else
                $DATA[$val][] = $file;
        }
    }
    return $DATA;
}

function GetAttrFiles($path,$arr){
    global $workdir,$preview_types, $charset;
    $num = count($arr);
    if(!$arr)
    {
        return;
    }
    foreach($arr as $key=>$dataarr)
    {
        if($key == "file")
        {
            $path = iconv('utf-8', $charset, $path);
            foreach($dataarr as $filename)
            {
                $fname = iconv($charset, 'utf-8', $filename);
                $file = $workdir.$path."/".$filename;
                $attr = stat($file);
                $modify =  $attr[9];
                $size = $attr[7];
                $type = strtolower(substr(strrchr($filename,"."),1));
                $preview = (in_array($type,$preview_types) ? "p" : "-");
                $edit = ($type == "jpg" || $type == "txt" ? "e" : "-");
                $attr = "-rwr".$edit."v".$preview;
                $FI[] = array($fname,$size,$modify,$attr);
            }
        }
        else
        {
            foreach($dataarr as $filename)
            {
                $fname = iconv($charset, 'utf-8', $filename);
                $file = $workdir.$path."/".$filename;
                $size = 0;
                $stat = stat($file);
                $modify = date($stat[9]);
                $attr = "drwr---";
                $FI[] = array($fname,$size,$modify,$attr);
            }
        }
    }
    return $FI;
}

function sendparamsdir($dir=false){
    global $globaldirs,$max_uploaded_size;
    $dir = $dir;
    $types = ($globaldirs[$dir]["ext"] ? $globaldirs[$dir]["ext"] : "*");
    $data = array(
        "general.hidden_tools"=>"",
        "general.disabled_tools"=>"",
        "filesystem.extensions"=>"*",
        "filesystem.force_directory_template"=>false,
        "upload.maxsize"=>$max_uploaded_size,
        "upload.chunk_size"=>"5MB",
        "upload.extensions"=>$types
    );
    return $data;
}

function sendparamsfile($path,$size,$modify,$file=false){
    global $url;
    $data = array(      //для того чтобы автоматом подключало режим thumbnails
        "path"=>$path,
        "size"=>$size,
        "lastModified"=>$modify,
        "isFile"=>$file,
        "canRead"=>true,
        "canWrite"=>true,
        "canEdit"=>true,
        "canRename"=>true,
        "canView"=>true,
        "canPreview"=>false,
        "exists"=>true,
        "meta"=>array("url"=>$url.$path),
        "info"=>array(""=>"")
    );
    return $data;
}

function refreshdirs(){
    global $globaldirs, $OUT;
    foreach($globaldirs as $gd=>$key)
    {
        $OUT["result"][] = array(
            "name"=>$gd,
            "path"=>"/".strtolower($gd),
            "meta"=>array(
                "standalone"=>false
            ),
            "config"=>sendparamsdir($gd)
        );
    }
    return $OUT;
}

function delTree($dir) {
    $files = glob($dir.'*', GLOB_MARK);
    foreach($files as $file){
        if(substr($file, -1) == '/')
            delTree($file);
        else
            unlink($file);
    }
    rmdir($dir);
}


if($_SERVER["REQUEST_METHOD"] == "POST")
{
    $JSON = json_decode(stripslashes($_POST["json"]), true);

    $id = $JSON["id"];
    $method = $JSON["method"];  //: listRoots, listFiles, FileInfo, getConfig
    $jsrpc = "2.0";

    $PARAMS = (array) $JSON["params"];
    $url = "http://".$_SERVER["HTTP_HOST"];
    $path = $PARAMS["path"];
    $DIR = explode("/",$path);

    if($JSON["method"] == "zip" || $JSON["method"] == "unzip")
    {
        $OUT["jsonrpc"] = $jsrpc;
        $OUT["error"] = array(
            "code"=>"100",
            "message"=>"This method not supported!",
            "data"=>""
        );
        $OUT["id"] = $id;
    }


    //инициализация, вывод папок, конфиги для них
    if($method == "listRoots")
    {
        $OUT["jsonrpc"] = $jsrpc;
        $OUT = refreshdirs();
        $OUT["id"] = $id;
    }


    if($method == "listFiles")
    {
        $OUT["jsonrpc"] = $jsrpc;
        if($PARAMS["only_files"])   //просмотр изображений
        {
            $extensions = $PARAMS["extensions"];    //"jpg,gif,png,jpeg",
            $filez = GetData($path,$extensions,$PARAMS["filter"]);
            $filesinfo = GetAttrFiles($path,$filez);
            $stat = stat($workdir.$path);
            $modify = date($stat[9]);
            $OUT["result"] = array(
                "columns"=> array("name","size","modified","attrs","info"),
                "config"=>sendparamsdir(trim($DIR[1])),
                "file"=>sendparamsfile($path,4096,$modify,false),
                "urlFile"=>null,
                "data"=>$filesinfo,
            );
        }
        else
        {
            $filez = GetData($path,false,$PARAMS["filter"]);
            $filesinfo = GetAttrFiles($path,$filez);
            if(!$filesinfo)
                $filesinfo = array(""=>"");


            $path = iconv('utf-8', $charset, $path);
            $stat = stat($workdir.$path);
            $modify = date($stat[9]);
            $path = iconv($charset, 'utf-8', $path);
            $OUT["result"] = array(
                "columns"=> array("name","size","modified","attrs","info"),
                "config"=>sendparamsdir(trim($DIR[1])),
                "file"=>sendparamsfile($path,0,$modify,false),
                "urlFile"=>null,
                "data"=>$filesinfo,
            );
        }
        $OUT["id"] = $id;
    }

    if($method == "getFileContents")
    {
        $OUT["jsonrpc"] = $jsrpc;
        $patharr = explode("/",$path);
        if($globaldirs[$patharr[1]] && !strstr($path,".."))
        {
            $rdata = file_get_contents($workdir.$path);
            $m = iconv($charset, 'utf-8', $rdata);
            $OUT["result"] = array("content"=>$m);
        }
        $OUT["id"] = $id;
    }


    if($method == "putFileContents")
    {
        $OUT["jsonrpc"] = $jsrpc;
        $patharr = explode("/",$path);
        if($globaldirs[$patharr[1]] && !strstr($path,".."))
        {
            $data = iconv('utf-8', $charset, $PARAMS["content"]);
            file_put_contents($workdir.$path,$data);
            $OUT = refreshdirs();
        }
        $OUT["id"] = $id;
    }



    if($method == "FileInfo")
    {
        $OUT["jsonrpc"] = $jsrpc;
        //вставка изображения
        if($PARAMS["insert"])
        {
            $file = $workdir.$PARAMS["paths"][0];
            $file = iconv('utf-8', $charset, $file);
            $attr = stat($file);
            $modify =  $attr[9];
            $size = $attr[7];
            $type = strtolower(substr(strrchr($file,"."),1));
            $preview = ($type == "jpg" ? "p" : "-");
            $attr = "-rwr-v".$preview;
            $OUT["result"][] = sendparamsfile($PARAMS["paths"][0],$size,$modify,true);
			
        }
        else
        {
            $path = iconv('utf-8', $charset, $path);
            $stat = stat($workdir.$path);
            $modify = date($stat[9]);
            $path = iconv($charset, 'utf-8', $path);
            $OUT["result"]=sendparamsfile($path,0,$modify,false);
        }
        $OUT["id"] = $id;
    }

    if($method == "getConfig")  // аплод файлов
    {
        $OUT["jsonrpc"] = $jsrpc;
        $OUT["result"] = sendparamsdir($DIR[1]);
        $OUT["id"] = $id;
    }

    if($_GET["action"] == "upload") //закачка файлов
    {
        $OUT["jsonrpc"] = $jsrpc;
        $file = $_FILES["file"]["tmp_name"];
        $name = $_GET["name"];
        $type = strtolower(substr(strrchr($name,"."),1));
        if(in_array($type,$forbidden))
        {
           $OUT["error"] = array(
            "code"=>"100",
            "message"=>"This filetype is forbidden!",
            "data"=>""
           );
        }
        else
        {
            $copyto = $_GET["path"];
            $muf = iconv('utf-8', $charset, $workdir.$copyto."/".$name);
            if(move_uploaded_file($file, $muf) == true)
            $OUT = refreshdirs();
        }
        $OUT["id"] = $id;
    }

    if($method == "delete")  // удаление файлов
    {
        $OUT["jsonrpc"] = $jsrpc;
        foreach($PARAMS["paths"] as $file)
        {
            $patharr = explode("/",$file);
            if($globaldirs[$patharr[1]] && !strstr($file,".."))
            {
                $file = iconv('utf-8', $charset, $file);
                if(is_dir($workdir.$file))
                    delTree($workdir.$file."/");
                else
                    unlink($workdir.$file);
            }
        }
        $OUT = refreshdirs();
        $OUT["id"] = $id;
    }

    if($method == "createDirectory")  // создание директории
    {
        mkdir($workdir.$path);
        $OUT["jsonrpc"] = $jsrpc;
        $OUT = refreshdirs();
        $OUT["id"] = $id;
    }

    if($method == "moveTo")  // переименование, перенос
    {
        $OUT["jsonrpc"] = $jsrpc;
        $from = $PARAMS["from"];
        $to = iconv('utf-8', $charset, $workdir.$PARAMS["to"]);
        if(is_array($from))
        {
            foreach($from as $file)
            {
                $patharr = explode("/",$file);
                $filename = explode("/",$file);
                $num = count($filename) - 1;
                $file = iconv('utf-8', $charset, $file);
                $filename[$num] = iconv('utf-8', $charset, $filename[$num]);
                if($globaldirs[$patharr[1]] && !strstr($file,".."))
                    rename($workdir.$file,$to."/".$filename[$num]);
            }
        }
        else
        {
            $patharr = explode("/",$from);
            $patharr2 = explode("/",$to);
            $type = strtolower(substr(strrchr($to,"."),1));
            if(!strstr($from,"..") && !strstr($to,"..") && !in_array($type,$forbidden))
            {
                $from = iconv('utf-8', $charset, $from);
                rename($workdir.$from,$to);  //переименование
            }
            else
               $OUT["error"] = array("code"=>"100","message"=>"This filetype is forbidden! Rename aborted!","data"=>"");
        }
        $OUT = refreshdirs();
        $OUT["id"] = $id;
    }

    if($method == "copyTo")  // копирование
    {
        $from = $PARAMS["from"];
        $to = iconv('utf-8', $charset, $workdir.$PARAMS["to"]);
        foreach($from as $file)
        {
            $filename = explode("/",$file);
            $num = count($filename) - 1;
            $file = iconv('utf-8', $charset, $file);
            $filename[$num] = iconv('utf-8', $charset, $filename[$num]);
            copy($workdir.$file, $to."/".$filename[$num]);
        }
        $OUT["jsonrpc"] = $jsrpc;
        $OUT = refreshdirs();
        $OUT["id"] = $id;
    }

    header('Content-type: application/json');
    echo json_encode($OUT);
}



if($_GET["action"] == "language")
{
    header('Content-type: application/json');
    die();
}

$path = urldecode($_GET["path"]);
$type = strtolower(substr(strrchr($path,"."),1));

if($_GET["action"] == "streamfile" && in_array($type,$preview_types))
{
    $thumb = $_GET["thumb"];
    $path = iconv('utf-8', $charset, $path);
    $file = $uploaddir.$path;
    header("Location: ".$file);
    exit();
}

if($_GET["action"] == "download")
{
    require("phpzip.inc.php");
    $z = new PHPZip();
    $path = $workdir.$_GET["path"];
    $names = $_GET["names"];
    $filenames = explode(",",$names);
    $num = count($filenames);
    foreach($filenames as $file)
        $files[] = $path."/".$file;
    $file = $workdir."/downloaded/".$num."_files.zip";
    $z -> Zip($files, $file);
    header("Content-Type: application/zip");
    header("Content-Length: " . filesize($file));
    header("Content-Disposition: attachment; filename=".$num."_files.zip");
    readfile($file);
}

if($_GET["action"] == "PluginJs")
{
    header('Content-type: application/json');
    die();
}
