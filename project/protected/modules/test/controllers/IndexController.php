<?php
/**
 * Created by PhpStorm.
 * User: wang
 * Date: 2017/11/30
 * Time: 10:00
 */
Yii::import('application.models.process.lib.*');
Yii::import('application.modules.test.models.data.db.*');
Yii::import('application.modules.test.models.process.*');
class IndexController extends Controller
{
    public function actions()
    {
        return array(
        );
    }
    public function actionIndex()
    {
        $data =  UserProcess::getUserList();
        if(UserProcess::getSession() == false){
            GeneralFunc::alert('您还没有登录，请先登录');
            GeneralFunc::gotoUrl('index.php?r=test/Index/Login');
        }else{
            Yii::app()->smarty->assign('DATA',$data);
            Yii::app()->smarty->display('test/index.html');
        }
    }
    public function actionLogin()
    {
        if(Yii::app()->request->isPostRequest){
            $username = Yii::app()->request->getPost('username');
            $password = md5(Yii::app()->request->getPost('pwd').'epiaowang.com');
            if(empty($username) || empty($password)){
               GeneralFunc::alert('用户名或密码不能为空');
               GeneralFunc::gotoUrl('index.php?r=test/Index/Login');
            }else{
                $userInfo = UserProcess::getUserLogin($username,$password);
                if(false == $userInfo){
                    GeneralFunc::alert('用户名或密码错误');
                    GeneralFunc::gotoUrl('index.php?r=test/Index/Login');
                }else{
                    $this->redirect('?r=test/Index/Index');
                }
            }
        }else{
            Yii::app()->smarty->display('test/login.html');
        }

    }
}