<?php
namespace Topxia\Service\Sms\Job;

use Topxia\Service\Crontab\Job;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Sms\SmsProcessor\SmsProcessorFactory;

class smsSendOneDayJob implements Job
{
    public function execute($params)
    {
        $daySmsType = 'sms_live_play_one_day';
        $dayIsOpen = $this->getSmsService()->isOpen($daySmsType);
        $parameters = array();
        if ($dayIsOpen) {
            $targetType = $params['targetType'];
            $targetId = $params['targetId'];
            $processor = SmsProcessorFactory::create($targetType);
            $return = $processor->getUrls($targetId, $daySmsType);
            $callbackUrls = $return['urls'];
            $count = $return['count'];
            try {
                    $api = CloudAPIFactory::create('leaf');
                    $result = $api->post("/sms/sendBatch", array('total' => $count, 'callbackUrls' => $callbackUrls));
                } catch (\RuntimeException $e) {
                    throw new RuntimeException("发送失败！");
            }   
        }
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getSmsService()
    {
        return ServiceKernel::instance()->createService('Sms.SmsService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

}
