<?php
/*************************************/
/*                                   */
/*  MFHS Version : 1.2               */
/*  Dezended & Nulled by: LepeNU     */
/*  Release Date: 1/12/2007          */
/*                                   */
/*************************************/

echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"\r\n    \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\" dir=\"ltr\">\r\n<head>\r\n<title>Calendar</title>\r\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />\r\n";
echo "<s";
echo "tyle>\r\n<!--borrow from phpmyadmin-->\r\n/* No layer effects neccessary */\r\ndiv     {font-family: verdana, arial, helvetica, geneva, sans-serif; font-size: x-small; color: #000000}\r\n.heada  {font-family: verdana, arial, helvetica, geneva, sans-serif; font-size: x-small; color: #000000}\r\n.parent {font-family: verdana, arial, helvetica, geneva, sans-serif; color: #000000; text-decoration: none}\r\n.item, ";
echo ".item:active, .item:hover, .tblItem, .tblItem:active {font-family: verdana, arial, helvetica, geneva, sans-serif; font-size: 90%; color: #333399; text-decoration: none}\r\n.tblItem:hover {color: #FF0000; text-decoration: underline}\r\n    /* Always enabled stylesheets (left frame) */\r\nbody {font-family: verdana, arial, helvetica, geneva, sans-serif; font-size: x-small; }\r\ninput   {font-family: verdana";
echo ", arial, helvetica, geneva, sans-serif; font-size: x-small}\r\nselect  {font-family: verdana, arial, helvetica, geneva, sans-serif; font-size: x-small; background-color: #ffffff; color: #000000}\r\n\r\nimg, input, select, button {\r\n    vertical-align: middle;\r\n}\r\n\r\n/* Calendar */\r\ntable.calendar {\r\n    width: 100%;\r\n}\r\n\r\ntable.calendar td {\r\n    text-align: center;\r\n}\r\n\r\ntable.calendar td a {\r\n    displ";
echo "ay: block;\r\n}\r\n\r\ntable.calendar td a:hover {\r\n    background-color: #CCFFCC;\r\n}\r\n\r\ntable.calendar th {\r\n    background-color: #D3DCE3;\r\n}\r\n\r\ntable.calendar td.selected {\r\n    background-color: #FFCC99;\r\n}\r\n\r\nimg.calendar {\r\n    border: none;\r\n}\r\n\r\nform.clock {\r\n    text-align: center;\r\n}\r\n\r\n.nowrap {\r\n    white-space: nowrap;\r\n}\r\n\r\ndiv.nowrap {\r\n    margin: 0px;\r\n    padding: 0px;\r\n}\r\n\r\nli {\r\n    ";
echo "padding-bottom: 1em;\r\n}\r\n\r\nli form {\r\n    display: inline;\r\n}\r\n\r\nul.main {\r\n    margin: 0px;\r\n    padding-left:2em;\r\n    padding-right:2em;\r\n}\r\n\r\n/* no longer needed\r\nul.main li {\r\n    list-style-image: url(../images/dot_violet.png);\r\n    padding-bottom: 0.1em;\r\n}\r\n*/\r\n\r\nbutton {\r\n    /* buttons in some browsers (eg. Konqueror) are block elements, this breaks design */\r\n    display: inline;\r\n}\r\n\r\n";
echo "/* Tabs */\r\n\r\n/* For both light and non light */\r\n.tab {\r\n    white-space: nowrap;\r\n    font-weight: bolder;\r\n}\r\n\r\n/* For non light */\r\ntd.tab {\r\n    width: 64px;\r\n    text-align: center;\r\n    background-color: #dfdfdf;\r\n}\r\n\r\ntd.tab a {\r\n    display: block;\r\n}\r\n\r\n/* For light */\r\ndiv.tab { }\r\n\r\n/* Highlight active tab */\r\ntd.activetab {\r\n    background-color: silver;\r\n}\r\n\r\n/* Textarea */\r\n\r\ntextar";
echo "ea {\r\n    overflow: auto;\r\n}\r\n\r\n.nospace {\r\n    margin: 0px;\r\n    padding: 0px;\r\n}\r\n</style>\r\n";
echo "<s";
echo "cript type=\"text/javascript\" src=\"date.js\"></script>\r\n";
echo "<s";
echo "cript type=\"text/javascript\">\r\n<!--\r\nvar month_names = new Array(\"Jan\",\"Feb\",\"Mar\",\"Apr\",\"May\",\"Jun\",\"Jul\",\"Aug\",\"Sep\",\"Oct\",\"Nov\",\"Dec\");\r\nvar day_names = new Array(\"Sun\",\"Mon\",\"Tue\",\"Wed\",\"Thu\",\"Fri\",\"Sat\");\r\n//-->\r\n</script>\r\n</head>\r\n<body onload=\"initCalendar();\">\r\n<div id=\"calendar_data\"></div>\r\n<div id=\"clock_data\"></div>\r\n</body>\r\n</html>\r\n";
?>
