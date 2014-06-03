<?php use_helper('Javascript') ?>
<?php echo javascript_tag('
function getVersion(obj)
{
  if (obj)
  {
    var info = $("#versionInformation");
    info.html("<p class=\""+obj.level+"\">"+obj.message+"</p>");
    info.show();
  }
}

function getDashboard(str)
{
  if (str)
  {
    var dashboard = $("#dashboard");
    dashboard.html(str);
    dashboard.show();
  }
}
'); ?>

<style type="text/css">
#body {
  padding-top: 20px;
}

#dashboard {
  height: 200px;
  overflow-y: scroll;
}

#dashboard, #quickmenu {
  margin: 10px 30px 10px 20px;
  padding-left: 20px;
  padding-bottom: 20px;
  border: 1px solid #000;
}

#quickmenu h4 {
  margin-top: 20px;
}

#quickmenu > ol {
  list-style-type: decimal;
  padding-left: 40px;
}

#quickmenu > ul {
  list-style-type: disc;
  padding-left: 40px;
}

#quickmenu .step-body {
  margin: 5px 0 10px 20px;
}
</style>

<div id="versionInformation" style="display: none;"></div>
<script type="text/javascript" src="//update.openpne.jp/?callback=getVersion&version=<?php echo OPENPNE_VERSION ?>"></script>

<h3>pne.jp サービスメニュー</h3>
<div id="dashboard" style="display: none;"></div>
<script type="text/javascript" src="//pne.jp/info.php?callback=getDashboard"></script>

<h3>ちょきんばこ機能 クイックメニュー</h3>
<div id="quickmenu">
  <h4>&#9660;ちょきんばこ機能初期設定</h4>
  <ol>
    <li>
      クラブの名称を決定する
      <p class="step-body">
        <?php echo link_to('SNS名称設定', 'sns/config#sns_config_sns_name') ?>
      </p>
    </li>
    <li>
      クラブのデザインを変更する
      <p class="step-body">
        <?php echo link_to('スキンテーマ設定', 'opSkinThemePlugin/index') ?>
      </p>
    </li>
    <li>
      クラブ会費を受け取る振込口座を登録する
      <p class="step-body">
        <?php echo link_to('ちょきんばこ設定：口座登録', 'chokinbako/index#sns_config_chokinbako_refund_bank_name') ?>
      </p>
    </li>
    <li>
      入会用HTMLタグを応募サイトに貼り付け
      <div class="step-body">
        <p>&#8251;下記フォームでコースを選択し、HTMLタグをサイトに貼り付けてください。</p>
        <?php echo $coursesWidget->getRawValue()->render('courses', false) ?>
        <textarea id="register-html" cols="80" rows="8"></textarea>
        <div id="form-template" style="display: none">
<form method="post" action="<?php echo app_url_for('pc_frontend', 'opAuthMailAddress/requestRegisterURL', true) ?>">
  <input type="hidden" name="request_register_url[course_id]" value=""/>
  <label>
    メールアドレス: <input type="text" name="request_register_url[mail_address]"/>
  </label>
  <input type="submit" value="送信"/>
</form>
        </div>
        <script type="text/javascript">
        (function($){
          var htmlTextArea = $('#register-html')
          var formTemplate = $('#form-template')
          var courseIdField = $('input[name="request_register_url[course_id]"]', formTemplate)

          $('input[name="courses"]:radio').change(function(){
            courseIdField.val($(this).val())

            var generatedHtml = $.trim(formTemplate.html())
            htmlTextArea.text(generatedHtml)
          })
        })(jQuery)
        </script>
      </div>
    </li>
  </ol>

  <h4>&#9660;その他の設定項目</h4>
  <ul>
    <li>
      会員のプロフィール項目を変更する
      <p class="step-body">
        <?php echo link_to('プロフィール変更', 'profile/list') ?>
      </p>
    </li>
  </ul>
</div>
