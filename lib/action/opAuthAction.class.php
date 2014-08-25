<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opAuthAction
 *
 * @package    OpenPNE
 * @subpackage action
 * @author     Kousuke Ebihara <ebihara@tejimaya.com>
 */
class opAuthAction extends sfActions
{
  public function executeRegisterEnd(sfWebRequest $request)
  {
    $this->forward404Unless($this->getUser()->setRegisterToken($request['token']));

    $member = $this->getUser()->getMember(true);

    if (opConfig::get('retrieve_uid') == 3
      && !sfConfig::get('app_is_mobile', false)
      && !$member->getConfig('mobile_uid')
    )
    {
      $this->forward('member', 'registerMobileToRegisterEnd');
    }

    $this->getUser()->getAuthAdapter()->activate();

    $this->getUser()->setIsSNSMember(true);

    if ($member->getEmailAddress())
    {
      $generatedPassword = opToolkit::getRandom(16);
      $member->setConfig('password', md5($generatedPassword));

      $fromTsudo = (int)ChokinbakoMemberTable::getInstance()->createQuery('cm')
        ->andWhere('cm.member_id = ?', $member->id)
        ->count() === 0;

      $i18n = sfContext::getInstance()->getI18N();
      $params = array(
        'subject' => $i18n->__('Notify of Your Registering'),
        'url'     => $this->getController()->genUrl(array('sf_route' => 'homepage'), true),
        'mail_address' => $member->getConfig('pc_address'),
        'password' => $generatedPassword,
        'from_tsudo' => $fromTsudo,
      );
      opMailSend::sendTemplateMailToMember('registerEnd', $member, $params);
    }

    $this->setTemplate('registerEnd', 'member');
  }
}
