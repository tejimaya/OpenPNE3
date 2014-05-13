<?php
$options = array(
  'title' => __('Member Registration'),
  'url'   => url_for('member/registerInput?token='.$token),
  'button' => __('Register'),
);
op_include_form('RegisterForm', $form, $options);
?>

<style>
#scrollTop {
  position: fixed;
  bottom: 10px;
  right: 10px;

  display: block;
  width: 90px;
  height: 25px;
  background-color: #fff;

  line-height: 25px;
  text-align: center;
}
</style>
<a id="scrollTop" href="#top">TOPへ戻る</a>
