<?php
define('BASE','http://szentiras.hu/'); //base url of the site
define('FILE','/var/www/szentiras.hu/'); //base url of the site
define('DBPREF','kar_'); // prefixes of the tables in the database
define('DROPBOXF','Bibliafordítások'); // A dropbox foldere a megosztottaknak

//Redundancia miatt szükség van erre
$fileurl = BASE;//"http://szentiras.hu/";
$baseurl = BASE;//"http://szentiras.hu/";

$sitetitle = 'Szentírás <sup>v3</sup>';
$subsitetitle = 'Katolikus Biblia fordítások az interneten';
$content = '';
$meta = '';
$share = '';
$comments = '';
$title = false;
$tipps = array();	

$mysqlhost = 'localhost';
$mysqldb = 'bible';
$mysqlpw = "$_ENV['MYSQL_SZENTIRAS_PWD']";
$mysqluser = "$_ENV['MYSQL_SZENTIRAS_USER']";
$database = array('mysql:host=localhost;dbname=bible',"$_ENV['MYSQL_SZENTIRAS_PWD']","$_ENV['MYSQL_SZENTIRAS_PWD']");

try { 
	$db = new PDO($database[0], $database[1], $database[2],array('charset'=>'UTF-8',PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
} catch (PDOException $exception) { 
	echo '<strong>Végzetes hiba törént!</strong>';
	echo '<br/>Nem sikerült az adatbázishoz kapcsolódni!';
	echo '<br/>Részletek: '. $exception->getMessage(); 
   exit; 
} 

/**/
$copyright = "
<p><!-- &copy; <a href='http://www.kereszteny.hu/mkie' class='menulink'>MKIE</a> - 
				<a href='http://www.oki-iroda.hu' class='menulink'>ÖKI</a> - 
				<a href='http://kim.katolikus.hu' class='menulink'>KIM</a>
				 2001-2010,<br>-->
				 &copy; <a href='http://www.eleklaszlo.hu' class='menulink'>Elek László SJ</a> (<a href='http://jezsuita.hu' alt='Jézus Társasága Magyarországi Rendtartománya'>JTMR</a>) 2013-2014.<br>
				 Minden jog fenntartva.</p>";
?>