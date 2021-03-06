<?php
/*
 * 消息订阅者
 */
class HttpConsumer
{
	//签名
	private static $signature = "Signature";
	//Consumer ID
	private static $consumerid = "ConsumerID";
	//访问码
	private static $ak = "AccessKey";
	//配置信息
	private static $config = null;
	//订阅流程
	public function process()
	{
		header("Content-type:text/html;charset=utf-8");
		//获取Topic
		$topic = C("aliyun_mq_topic");
		//获取保存Topic的URL路径
		$url = C("aliyun_mq_url");
		//读取阿里云访问码
		$ak = C("aliyun_mq_acessKey");
		//读取阿里云密钥
		$sk = C("aliyun_mq_secretKey");
		//Consumer ID
		$cid = C("aliyun_mq_consumerId");
		$newline = "\n";
		//构造工具对象
		import('Common.Libs.AliyunUtil');
		$util = new \Util();
		try
		{
			//构造时间戳
			$date = (time())*1000;
			//签名字符串
			$signString = $topic.$newline.$cid.$newline.$date;
			//计算签名
			$sign = $util->calSignatue($signString,$sk);
			//构造签名标记
			$signFlag = $this::$signature.":".$sign;
			//构造密钥标记
			$akFlag = $this::$ak.":".$ak;
			//标记
			$consumerFlag = $this::$consumerid.":".$cid;
			//构造HTTP请求发送内容类型标记
			$contentFlag = "Content-Type:text/html;charset=UTF-8";
			//构造HTTP头部信息
			$headers = array(
				$signFlag,
				$akFlag,
				$consumerFlag,
				$contentFlag,
			);
			//构造HTTP请求URL
			$getUrl = $url."/message/?topic=".$topic."&time=".$date."&num=20";
			//初始化网络通信模块
			$ch = curl_init();
			//填充HTTP头部信息
			curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
			//设置HTTP请求类型,此处为GET
			curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"GET");
			//设置HTTP请求URL
			curl_setopt($ch,CURLOPT_URL,$getUrl);
			//构造执行环境
			ob_start();
			//开始发送HTTP请求
			curl_exec($ch);
			//获取请求应答消息
			$result = ob_get_contents();
			//清理执行环境
			ob_end_clean();
			//关闭HTTP网络连接
			curl_close($ch);
			//解析HTTP应答信息
			$messages = json_decode($result,true);
			//如果应答信息中的没有包含任何的Topic信息,则直接跳过
			if (count($messages) ==0)
			{
				return array();exit;
			}return $messages;exit;
			//依次遍历每个Topic消息
			foreach ((array)$messages as $message)
			{
				//获取时间戳
				$date = (int)($util->microtime_float()*1000);
				//构造删除Topic消息URL
				$delUrl = $url."/message/?msgHandle=".$message['msgHandle']."&topic=".$topic."&time=".$date;
				//签名字符串
				$signString = $topic.$newline.$cid.$newline.$message['msgHandle'].$newline.$date;
				//计算签名
				$sign = $util->calSignatue($signString,$sk);
				//构造签名标记
				$signFlag = $this::$signature.":".$sign;
				//构造密钥标记
				$akFlag = $this::$ak.":".$ak;
				//构造消费者组标记
				$consumerFlag = $this::$consumerid.":".$cid;
				//构造HTTP请求头部信息
				$delheaders = array(
					$signFlag,
					$akFlag,
					$consumerFlag,
					$contentFlag,
				);
				//初始化网络通信模块
				$ch = curl_init();
				//填充HTTP请求头部信息
				curl_setopt($ch,CURLOPT_HTTPHEADER,$delheaders);
				//设置HTTP请求URL信息
				curl_setopt($ch,CURLOPT_URL,$delUrl);
				//设置HTTP请求类型,此处为DELETE
				curl_setopt($ch,CURLOPT_CUSTOMREQUEST,'DELETE');
				//构造执行环境
				ob_start();
				//开始发送HTTP请求
				curl_exec($ch);
				//获取请求应答消息
				$result = ob_get_contents();
				//清理执行环境
				ob_end_clean();
				//关闭连接
				curl_close($ch);
				//打印应答消息
				return $result;exit;
			}
		}
		catch (Exception $e)
		{
			//打印异常信息
			echo $e->getMessage();
		}
	}
}     
?>