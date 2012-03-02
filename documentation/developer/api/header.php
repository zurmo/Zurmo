<!DOCTYPE html>
<html><head><meta http-equiv = "Content-Type" content = "text/html;charset = iso-8859-1">
<title>$title</title>
<link href = "http://zurmo.org/wp-content/themes/Zurmo/doxygen.css" rel = "stylesheet" type = "text/css">
<link rel = "shortcut icon" href = 'http://zurmo.org/wp-content/themes/Zurmo/zurmo-favicon.ico' />

<!--[if lt IE 9]>
<script src = "http://zurmo.org/wp-content/themes/Zurmo/js/html5.js" type = "text/javascript"></script>
<![endif]-->

<script type = "text/javascript">
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-24772933-1']);
_gaq.push(['_trackPageview']);
(function()
{
var ga = document.createElement('script'); ga.type = 'text/javascript';
ga.async = true;
ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
var s = document.getElementsByTagName('script')[0];
s.parentNode.insertBefore(ga, s);
})();
</script>

</head>
<?php
$fileName = basename($_SERVER['REQUEST_URI'], ".php");
$className = 'doxygen-' . rtrim($fileName, '.php');
?>
<body id = '<?php echo $className;?>'>
<?php echo file_get_contents( "http://zurmo.org/z-header.php" ); ?>