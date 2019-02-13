<?php
include_once "database.php";
include_once "session.php";

if (isset($_SESSION['profile_ID'])) {
    $con = Connection::connect();
    $result = $con->prepare("SELECT * FROM Resumes where account_ID = ?");
    if ($result->execute(array($_SESSION['profile_ID']))) {
        $row = $result->fetch();
        $filename = getName($_SESSION['profile_ID']) . "_resume." . $row['file_extension'];
        $ctype = "";
        switch ($row['file_extension'])
        {
            case "pdf": $ctype="application/pdf"; break;
            case "exe": $ctype="application/octet-stream"; break;
            case "zip": $ctype="application/zip"; break;
            case "docx":
            case "doc": $ctype="application/msword"; break;
            case "csv":
            case "xls":
            case "xlsx": $ctype="application/vnd.ms-excel"; break;
            case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
            case "gif": $ctype="image/gif"; break;
            case "png": $ctype="image/png"; break;
            case "jpeg":
            case "jpg": $ctype="image/jpg"; break;
            case "tif":
            case "tiff": $ctype="image/tiff"; break;
            case "psd": $ctype="image/psd"; break;
            case "bmp": $ctype="image/bmp"; break;
            case "ico": $ctype="image/vnd.microsoft.icon"; break;
            default: $ctype="application/force-download";
        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);
        header("Content-Type: $ctype");
        header("Content-Disposition: attachment; filename=\"".$filename."\";" );
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".strlen($row['resume_file']));
        echo $row['resume_file'];
    }
}