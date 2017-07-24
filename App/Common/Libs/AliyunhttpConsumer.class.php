<?php
/*
 * ��Ϣ������
 */
class HttpConsumer
{
	//ǩ��
	private static $signature = "Signature";
	//Consumer ID
	private static $consumerid = "ConsumerID";
	//������
	private static $ak = "AccessKey";
	//������Ϣ
	private static $config = null;
	//��������
	public function process()
	{
		header("Content-type:text/html;charset=utf-8");
		//��ȡTopic
		$topic = C("aliyun_mq_topic");
		//��ȡ����Topic��URL·��
		$url = C("aliyun_mq_url");
		//��ȡ�����Ʒ�����
		$ak = C("aliyun_mq_acessKey");
		//��ȡ��������Կ
		$sk = C("aliyun_mq_secretKey");
		//Consumer ID
		$cid = C("aliyun_mq_consumerId");
		$newline = "\n";
		//���칤�߶���
		import('Common.Libs.AliyunUtil');
		$util = new \Util();
		try
		{
			//����ʱ���
			$date = (time())*1000;
			//ǩ���ַ���
			$signString = $topic.$newline.$cid.$newline.$date;
			//����ǩ��
			$sign = $util->calSignatue($signString,$sk);
			//����ǩ�����
			$signFlag = $this::$signature.":".$sign;
			//������Կ���
			$akFlag = $this::$ak.":".$ak;
			//���
			$consumerFlag = $this::$consumerid.":".$cid;
			//����HTTP�������������ͱ��
			$contentFlag = "Content-Type:text/html;charset=UTF-8";
			//����HTTPͷ����Ϣ
			$headers = array(
				$signFlag,
				$akFlag,
				$consumerFlag,
				$contentFlag,
			);
			//����HTTP����URL
			$getUrl = $url."/message/?topic=".$topic."&time=".$date."&num=20";
			//��ʼ������ͨ��ģ��
			$ch = curl_init();
			//���HTTPͷ����Ϣ
			curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
			//����HTTP��������,�˴�ΪGET
			curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"GET");
			//����HTTP����URL
			curl_setopt($ch,CURLOPT_URL,$getUrl);
			//����ִ�л���
			ob_start();
			//��ʼ����HTTP����
			curl_exec($ch);
			//��ȡ����Ӧ����Ϣ
			$result = ob_get_contents();
			//����ִ�л���
			ob_end_clean();
			//�ر�HTTP��������
			curl_close($ch);
			//����HTTPӦ����Ϣ
			$messages = json_decode($result,true);
			//���Ӧ����Ϣ�е�û�а����κε�Topic��Ϣ,��ֱ������
			if (count($messages) ==0)
			{
				return array();exit;
			}return $messages;exit;
			//���α���ÿ��Topic��Ϣ
			foreach ((array)$messages as $message)
			{
				//��ȡʱ���
				$date = (int)($util->microtime_float()*1000);
				//����ɾ��Topic��ϢURL
				$delUrl = $url."/message/?msgHandle=".$message['msgHandle']."&topic=".$topic."&time=".$date;
				//ǩ���ַ���
				$signString = $topic.$newline.$cid.$newline.$message['msgHandle'].$newline.$date;
				//����ǩ��
				$sign = $util->calSignatue($signString,$sk);
				//����ǩ�����
				$signFlag = $this::$signature.":".$sign;
				//������Կ���
				$akFlag = $this::$ak.":".$ak;
				//��������������
				$consumerFlag = $this::$consumerid.":".$cid;
				//����HTTP����ͷ����Ϣ
				$delheaders = array(
					$signFlag,
					$akFlag,
					$consumerFlag,
					$contentFlag,
				);
				//��ʼ������ͨ��ģ��
				$ch = curl_init();
				//���HTTP����ͷ����Ϣ
				curl_setopt($ch,CURLOPT_HTTPHEADER,$delheaders);
				//����HTTP����URL��Ϣ
				curl_setopt($ch,CURLOPT_URL,$delUrl);
				//����HTTP��������,�˴�ΪDELETE
				curl_setopt($ch,CURLOPT_CUSTOMREQUEST,'DELETE');
				//����ִ�л���
				ob_start();
				//��ʼ����HTTP����
				curl_exec($ch);
				//��ȡ����Ӧ����Ϣ
				$result = ob_get_contents();
				//����ִ�л���
				ob_end_clean();
				//�ر�����
				curl_close($ch);
				//��ӡӦ����Ϣ
				return $result;exit;
			}
		}
		catch (Exception $e)
		{
			//��ӡ�쳣��Ϣ
			echo $e->getMessage();
		}
	}
}     
?>