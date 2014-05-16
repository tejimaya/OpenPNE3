<?php slot('_request_register_url_body') ?>
<?php echo __('Please input your e-mail address. Invitation for %1% will be sent to your e-mail address.', array('%1%' => $op_config['sns_name'])) ?>
<?php end_slot(); ?>

<?php echo op_include_form('requestRegisterURL', $form, array(
  'title' => __('Register'),
  'body' => get_slot('_request_register_url_body'),
)); ?>

<script type="text/javascript">
(function($){
  var agreeTermsButton = $('#requestRegister_url_agree_terms')
  var submitButton = $('#requestRegisterURL input[type="submit"]')

  submitButton.prop('disabled', !agreeTermsButton.prop('checked'))

  agreeTermsButton.change(function(){
    submitButton.prop('disabled', !agreeTermsButton.prop('checked'))
  })
})(jQuery)
</script>
