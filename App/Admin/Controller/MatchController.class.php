<?php
/*
 * 匹配控制器
 */

namespace Admin\Controller;

class MatchController extends AdminCoreController {

    //系统默认模型
    private $Model = null;

    protected function _initialize() {
        //继承初始化方法
        parent::_initialize ();
        //设置控制器默认模型
        $this->Model = D('Task');
    }
    //完全匹配首页
    public function exactIndex(){

            $map = array('del' => 0);
            $get_data = I('get.');
            if ($get_data['start']) {
                $map["FROM_UNIXTIME(create_time, '%Y-%m')"] = array('eq', str_replace('.','-',$get_data['start']));
            } else {
                $map["FROM_UNIXTIME(create_time, '%Y-%m')"] = array('eq', str_replace('.','-',date('Y.m', strtotime('first day of previous month'))));
            }
            $_list = $this->Model->where($map)->select();
            $this->assign('list',$_list);
            $this->display ();
    }

    /*
    完全匹配添加
     **/
    public function exactAdd(){
        if(IS_GET){
            $get_data = I('get.company');
            if ($get_data){
                $map['unicom_name'] = array('like','%'.$get_data.'%');
            }
            $_list = M('Company')->where($map)->field('unicom_name,id')->select();
            $this->assign('list',$_list);
            $this->display();
        }else{
            $this->display();
        }
    }

    public function exactAsdf() {
        $this->ajaxReturn(I('param.company_name'));
    }

    //动态搜索
    public function exactSearch(){

            $p = I('get.p');
            $map = array(
                'del' => 0
            );
            if($p)
            {
                $map['unicom_name'] = array('LIKE','%'.$p.'%');
            }
            $temp = array();
            $list = M('company')->where($map)->order('create_time desc')->field('id,unicom_name as name')->select();
            foreach($list as $key=>$value)
            {
                $temp['result'][$key][] = $value['name'];
                $temp['result'][$key][] = $value['id'];

            }
            $temp = json_encode($temp);
            echo $temp;exit;
    }

    /* 编辑
     **/
    public function edit(){
        if(IS_POST){
            $post_data=I('post.');

            $post_data["group_ids"]=implode(",",$post_data["group_ids"]);
            $data=$this->Model->create($post_data);
            if($data){
                $result = $this->Model->where(array('id'=>$post_data['id'],'del'=>0))->save($data);
                if($result){
                    action_log('Edit_Company', 'Company', $post_data['id']);
                    $this->success ( "操作成功！",U('index'));
                }else{
                    $error = $this->Model->getError();
                    $this->error($error ? $error : "操作失败！");
                }
            }else{
                $error = $this->Model->getError();
                $this->error($error ? $error : "操作失败！");
            }
        }else{
            $_info=I('get.');
            $_info = $this->Model->where(array('id'=>$_info['id'],'del'=>0))->find();
            $this->assign('info', $_info);
            $this->display();
        }
    }

    /* 查看
     **/
    public function view(){
        $_info=I('get.');
        $_info = $this->Model->where(array('id'=>$_info['id'],'del'=>0))->find();
        $_info['group'] = '';
        if($_info['group_ids'])
        {
            $group_list = M('AuthGroup')->where(array('id'=>array('in',$_info['group_ids'],'del'=>0)))->field('title')->select();
            if($group_list)
            {
                $group = array_column($group_list,'title');
                $_info['group'] = join(',',$group);
            }
        }
        $this->assign('info', $_info);
        $this->display();
    }

    /* 删除
     **/
    public function del(){
        $id=I('get.id');
        empty($id)&&$this->error('参数不能为空！');
        if($this->Model->where(array('id'=>$id,'del'=>0))->count()==0)
        {
            $this->error('企业已删除！');
        }
        $res=$this->Model->delete($id);
        if(!$res){
            $this->error($this->Model->getError());
        }else{
            action_log('Del_Company', 'Company', $id);
            $this->success('删除成功！');
        }
    }
}