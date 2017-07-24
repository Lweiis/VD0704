<?php
/*
 * ��Ϣ��������
 */
class HttpProducer
{
	//ǩ��
	private static $signature = "Signature";
	//��MQ����̨������Producer ID
	private static $producerid = "ProducerID";
	//�����������֤��
	private static $aks = "AccessKey";
	//����md5
	private function md5($str)
	{
		return md5($str);
	}
	//������Ϣ����
	public function process($content='')
	{
		//��ȡTopic
		$topic = C("aliyun_mq_topic");
		//��ȡ����Topic
		$sub_topic = C("aliyun_mq_sub_topic");
		//��ȡ����Topic��URL·��
		$url = C("aliyun_mq_url");
		//��ȡ�����Ʒ�����
		$ak = C("aliyun_mq_acessKey");
		//��ȡ��������Կ
		$sk = C("aliyun_mq_secretKey");
		//��ȡProducer ID
		$pid = C("aliyun_mq_producerId");
		//HTTP����������
		$body = utf8_encode($content);
		$newline = "\n";
		//���칤�߶���
		import('Common.Libs.AliyunUtil');
		$util = new \Util();
		//����ʱ���
			$date = time()*1000;
			//POST����url
			$postUrl = $url."/message/?topic=".$topic."&time=".$date."&tag=".$sub_topic."&key=http";
			//ǩ���ַ���
			$signString = $topic.$newline.$pid.$newline.$this->md5($body).$newline.$date;
			//����ǩ��
			$sign = $util->calSignatue($signString,$sk);
			//��ʼ������ͨ��ģ��
			$ch = curl_init();
			//����ǩ�����
			$signFlag = $this::$signature.":".$sign;
			//������Կ���
			$akFlag = $this::$aks.":".$ak;
			//���
			$producerFlag = $this::$producerid.":".$pid;
			//����HTTP����ͷ���������ͱ��
			$contentFlag = "Content-Type:text/html;charset=UTF-8";
			//����HTTP����ͷ��
			$headers = array(
				$signFlag,
				$akFlag,
				$producerFlag,
				$contentFlag,
			);
			//����HTTPͷ������
			curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
			//����HTTP��������,�˴�ΪPOST
			curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"POST");
			//����HTTP�����URL
			curl_setopt($ch,CURLOPT_URL,$postUrl);
			//����HTTP�����body
			curl_setopt($ch,CURLOPT_POSTFIELDS,$body);
			//����ִ�л���
			ob_start();
			//��ʼ����HTTP����
			curl_exec($ch);
			//��ȡ����Ӧ����Ϣ
			$result = ob_get_contents();
			var_dump($result);
			//����ִ�л���
			ob_end_clean();
			//��ӡ����Ӧ����
			//�ر�����
			curl_close($ch);
/* 		for ($i = 0; $i<500; $i++) {
			
		} */
	}
}
?>