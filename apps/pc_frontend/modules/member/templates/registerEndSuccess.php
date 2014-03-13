<?php slot('body') ?>
<p>登録が完了しました。</p>
<p>メールを送信しましたのでご確認下さい。</p>
<p>このメールにはログイン・退会等に必要な情報が記載されていますので、大切に保管して下さい。</p>
<?php end_slot() ?>
<?php op_include_box('registerEndSuccess', get_slot('body'), array(
  'title' => __('Register'),
)) ?>
