<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opPresetProfileForm.
 *
 * @package    OpenPNE
 * @subpackage form
 * @author     Kousuke Ebihara <ebihara@tejimaya.com>
 */
class opPresetProfileForm extends ProfileForm
{
  public function configure()
  {
    parent::configure();

    $this->validatorSchema->setPostValidator(new sfValidatorPass());

    $this->setWidget('preset', new sfWidgetFormSelect(array('choices' => $this->getPresetChoiceList())));
    $this->setValidator('preset', new sfValidatorChoice(array('choices' => array_keys($this->getPresetChoiceList()))));
    $this->widgetSchema->moveField('preset', sfWidgetFormSchema::FIRST);

    unset($this['name'], $this['form_type'], $this['value_type'], $this['is_unique'], $this['value_min'], $this['value_max'], $this['value_type'], $this['value_regexp']);
    $embeds = array_keys($this->getEmbeddedForms());
    foreach ($embeds as $embed)
    {
      unset($this[$embed]);
    }
  }

  protected function getPresetList()
  {
    $configPath = 'config/preset_profile.yml';
    sfContext::getInstance()->getConfigCache()->registerConfigHandler($configPath, 'sfSimpleYamlConfigHandler', array());
    $list = include(sfContext::getInstance()->getConfigCache()->checkConfig($configPath));

    return $list;
  }

  protected function getPresetDefault()
  {
    return array(
      'Name' => '',
      'Caption' => '',
      'FormType' => 'input',
      'ValueType' => 'string',
      'IsRegist' => true,
      'IsConfig' => true,
      'IsSearch' => true,
      'IsRequired' => false,
      'IsEditPublicFlag' => true,
      'DefaultPublicFlag' => 0,
    );
  }

  protected function getPresetChoiceList()
  {
    $list = $this->getPresetList();

    $result = array();

    foreach ($list as $k => $v)
    {
      $result[$k] = $v['Caption'];
    }

    return $result;
  }

  protected function mergePresetAndValues($preset, $values)
  {
    $result = array();

    foreach ($preset as $k => $v)
    {
      $k = sfInflector::underscore($k);

      if (in_array($k, array('is_config', 'is_regist', 'is_search')))
      {
        $k = str_replace('is_', 'is_disp_', $k);
      }

      $result[$k] = $v;
    }

    $result = array_merge($result, $values);

    return $result;
  }

  public function save($con = null)
  {
    $values = $this->getValues();
    $presetList = $this->getPresetList();
    $presetName = $values['preset'];
    $preset = $presetList[$presetName];

    $values = $this->mergePresetAndValues($preset, $values);
    $values['name'] = 'op_preset_'.$values['name'];

    unset($values['preset'], $values['choices'], $values['caption']);

    $this->values = $values;

    parent::save($con);
  }
}