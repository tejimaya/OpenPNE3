<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opAuthAdapterMailAddress will handle credential for E-mail address.
 *
 * @package    OpenPNE
 * @subpackage user
 * @author     Kousuke Ebihara <ebihara@tejimaya.com>
 */
class opAuthAdapterMailAddress extends opAuthAdapter
{
  protected $authModuleName = 'opAuthMailAddress';

 /**
  * @see opAuthAdapter::activate()
  */
  public function activate()
  {
    parent::activate();

    $member = sfContext::getInstance()->getUser()->getMember();
    if ($member)
    {
      if ($token = Doctrine::getTable('MemberConfig')->retrieveByNameAndMemberId('mobile_address_token', $member->getId()))
      {
        $token->delete();
      }

      if ($token = Doctrine::getTable('MemberConfig')->retrieveByNameAndMemberId('pc_address_token', $member->getId()))
      {
        $token->delete();
      }

      if ($courseIdPre = Doctrine::getTable('MemberConfig')->retrieveByNameAndMemberId('course_id_pre', $member->id))
      {
        $courseMember = new ChokinbakoMember;
        $courseMember->chokinbako_course_id = $courseIdPre->value;
        $courseMember->member_id = $member->id;
        $courseMember->webpay_customer_id = $member->getConfig('webpay_customer_id');
        $courseMember->save();
        $courseMember->free(true);

        $course = ChokinbakoCourseTable::getInstance()->find($courseIdPre->value);
        $communityMember = Doctrine_Core::getTable('CommunityMember')
          ->findOneByMemberIdAndCommunityId($member->id, $course->community_id);
        if (!$communityMember)
        {
          $communityMember = Doctrine_Core::getTable('CommunityMember')->join($member->id, $course->community_id);
        }
        $course->free(true);

        $courseIdPre->delete();
      }

      if ($registerOptionPre = Doctrine::getTable('MemberConfig')->retrieveByNameAndMemberId('register_option_pre', $member->id))
      {
        $registerOptionPre->name = 'register_option';
        $registerOptionPre->save();
        $registerOptionPre->free(true);
      }
    }

    return $member;
  }

  /**
   * Returns true if the current state is a beginning of register.
   *
   * @return bool returns true if the current state is a beginning of register, false otherwise
   */
  public function isRegisterBegin($member_id = null)
  {
    opActivateBehavior::disable();
    $member = Doctrine::getTable('Member')->find((int)$member_id);
    opActivateBehavior::enable();

    if (!$member)
    {
      return false;
    }

    if (!Doctrine::getTable('MemberConfig')->retrieveByNameAndMemberId('pc_address_pre', $member->getId())
      && !Doctrine::getTable('MemberConfig')->retrieveByNameAndMemberId('mobile_address_pre', $member->getId()))
    {
      return false;
    }

    if (!$member->getIsActive())
    {
      return true;
    }
    else
    {
      return false;
    }
  }

  /**
   * Returns true if the current state is a end of register.
   *
   * @return bool returns true if the current state is a end of register, false otherwise
   */
  public function isRegisterFinish($member_id = null)
  {
    opActivateBehavior::disable();
    $data = Doctrine::getTable('Member')->find((int)$member_id);
    opActivateBehavior::enable();

    if (!$data || !$data->getName() || !$data->getProfiles())
    {
      return false;
    }

    if ($data->getIsActive())
    {
      return false;
    }
    else
    {
      return true;
    }
  }
}
