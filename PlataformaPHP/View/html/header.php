<?php
function write_header($userLevel = 1, $login = 0)
{
    echo '<!DOCTYPE html><html><head>';
    if ($login > -1) {
        echo '<title>Riego IoT - Su Gestor Online</title>';
        echo "<style>
    .button_timetable {     
      border: none;
      color: white;
      padding: 20px;
      text-align: center;
      text-decoration: none;
      display: inline-block;
      font-size: 16px;
      margin: 4px 2px;
    }

    .button1 {border-radius: 2px;}
    .button2 {border-radius: 4px;}
    .button3 {border-radius: 8px;}
    .off {background-color: #F05226;border-radius: 50%;}
    .on {background-color: #04AA6D;border-radius: 50%;}
    </style>";

        echo "<style>.button_img img {
    width: 100px;
    height: 30px;
    }</style>";
        //header
        echo '<style>
    /* Header/Logo Title */
.header1 {
  padding: 60px;
  text-align: center;
  background: #1abc9c;
  color: white;
  font-size: 30px;
}

/* Page Content */
.content1 {padding:20px;}
</style>
    ';
        echo '<style>
    
    .icon1{
   display: inline-block;
   width:   100px;
   height:  100px;
   background-image: url("images/Sample_User_Icon.png");
}
    
    </style>';

        //menu circular
        echo '<link rel="stylesheet" type="text/css" href="View/css/normalize.css" />';
        echo '<link rel="stylesheet" type="text/css" href="View/css/demo.css" />';
        if ($userLevel == 0) {
            echo '<link rel="stylesheet" type="text/css" href="View/css/component1_a.css" />';
        }
        if ($userLevel > 0) {
            echo '<link rel="stylesheet" type="text/css" href="View/css/component1_u.css" />';
        }
        echo '<script src="View/js/modernizr-2.6.2.min.js"></script>';
    }
    if ($login == -1) {
        //<!-- Adapted from foolishdeveloper.com -->
        echo '
    <title>Riego IoT - Inicie sesi&oacute;n</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
    ';
        echo '<link rel="stylesheet" type="text/css" href="View/css/1main.css" />';
    }

    echo '</head><body>';
}

?>
