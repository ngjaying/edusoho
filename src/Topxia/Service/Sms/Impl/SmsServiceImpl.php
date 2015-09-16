<?php
namespace Topxia\Service\Sms\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Sms\SmsService;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\CloudPlatform\CloudAPIFactory;
use Topxia\Common\ArrayToolkit;
class SmsServiceImpl extends BaseService implements SmsService
{

    public function isOpen($smsType)
    {
        $cloudSmsSetting = $this->getSettingService()->get('cloud_sms');
        if ((isset($cloudSmsSetting['sms_enabled'])) && ($cloudSmsSetting['sms_enabled'] == '1') 
            && (isset($cloudSmsSetting[$smsType])) && ($cloudSmsSetting[$smsType] == 'on')) {
            return true;
        }

        return false;
    }

    public function smsSend($smsType, $userIds, $description, $parameters=array())
    {
        if ($this->isOpen($smsType)) {
            throw new RuntimeException("云短信相关设置未开启!");
            
        }
        $users = $this->getUserService()->findUsersByIds($userIds);
        $to = '';
        if (!empty($users)) {
            $verifiedMobiles = ArrayToolkit::column($users, 'verifiedMobile');
            $to = implode(',', $verifiedMobiles);
            try {
                    $api = CloudAPIFactory::create('leaf');
                    $result = $api->post("/sms/send", array('mobile' => $to, 'category' => $smsType, 'description' => $description, 'parameters' => $parameters));
                } catch (\RuntimeException $e) {
                    throw new RuntimeException("发送失败！");
            }
            $result['to'] = $to;
            foreach ($users as $user) {
                $result['userId'] = $user['id'];
                $result['nickname'] = $user['nickname'];
                $this->getLogService()->info('sms', $smsType, "userId:{$user['id']},对{$to}发送用于{$smsType}的通知短信", $result);     
            }
        }
        return true;
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }
}

