<?php 
/*
 * 菜单模型
 * Auth   : Ghj
 * Time   : 1452660063 
 * QQ     : 912524639
 * Email  : 912524639@qq.com
 * Site   : http://guanblog.sinaapp.com/
 */
 
namespace Admin\Model;
use Think\Model;

class AuthRuleModel extends Model{
	
    /*模型中定义的表*/
	protected $tableName = ''; 

    /* 自动验证规则 */
	protected $_validate = array( 
	);

    /* 自动完成规则 */
	protected $_auto = array(
     
	);
	public function delete($id=0)
	{
		$mod = M('AuthRule');
		$pk = $mod->getPk();
		$result = $mod->where(array($pk=>$id))->save(array('del'=>1));
		return $result;
	}
}