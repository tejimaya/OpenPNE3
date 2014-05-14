<?php use_helper('Javascript') ?>
<?php echo javascript_tag('
function getVersion(obj)
{
  if (obj)
  {
    var info = $("#versionInformation");
    info.before("<p class=\""+obj.level+"\">"+obj.message+"</p>");
    info.show();
  }
}

function getDashboard(str)
{
  if (str)
  {
    var dashboard = $("#dashboard");
    dashboard.before(str);
    dashboard.show();
  }
}
'); ?>

<div id="versionInformation" style="display: none;"></div>
<script type="text/javascript" src="//update.openpne.jp/?callback=getVersion&version=<?php echo OPENPNE_VERSION ?>"></script>

<div id="dashboard" style="display: none;"></div>
<script type="text/javascript" src="//xpne.info/info.php?callback=getDashboard"></script>
