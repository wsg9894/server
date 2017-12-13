<?php
/**
 * Created by PhpStorm.
 * User: wang
 * Date: 2017/12/5
 * Time: 14:09
 */
Yii::import('application.models.process.lib.*');
Yii::import('application.modules.market.models.data.db.*');
Yii::import('application.modules.market.models.process.*');
class LoginController extends Controller
{
    public function actions()
    {
        return array(

        );
    }

    public function actionLogin()
    {
        if(Yii::app()->request->isPostRequest){
            $username = Yii::app()->request->getPost('username');
            $password = md5(Yii::app()->request->getPost('password').'epiaowang.com');
            if(empty($username) || empty($password)){
                GeneralFunc::alert('用户名或密码不能为空');
                GeneralFunc::gotoUrl('index.php?r=market/Login/Login');
            }else{
                $userInfo = AdminUserProcess::getUserLogin($username,$password);
                $msg = $userInfo['msg'];
                if($userInfo['data'] == ''){
                    GeneralFunc::alert("$msg");
                    GeneralFunc::gotoUrl('index.php?r=market/Login/Login');
                }else{
                    $this->redirect('?r=market/Index/Index');
                }
            }
        }else{
            Yii::app()->smarty->display('market/login.html');
        }
    }

    public function actionLogout()
    {
        $clearSess = AdminUserProcess::clearSession();
        if($clearSess == NULL){
            $this->redirect('?r=market/Login/Login');
        }
    }
}