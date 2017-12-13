<?php

/**
 * This is the model class for table "admin_user".
 *
 * The followings are the available columns in table 'admin_user':
 * @property integer $admin_user_id
 * @property string $admin_user_email
 * @property string $admin_user_password
 * @property string $admin_realname
 * @property integer $admin_status
 * @property string $admin_createtime
 */
class User extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return User the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'admin_user';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('admin_user_email, admin_user_password, admin_realname, admin_createtime', 'required'),
			array('admin_status', 'numerical', 'integerOnly'=>true),
			array('admin_user_email', 'length', 'max'=>45),
			array('admin_user_password', 'length', 'max'=>32),
			array('admin_realname', 'length', 'max'=>15),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('admin_user_id, admin_user_email, admin_user_password, admin_realname, admin_status, admin_createtime', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'admin_user_id' => 'Admin User',
			'admin_user_email' => 'Admin User Email',
			'admin_user_password' => 'Admin User Password',
			'admin_realname' => 'Admin Realname',
			'admin_status' => 'Admin Status',
			'admin_createtime' => 'Admin Createtime',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('admin_user_id',$this->admin_user_id);
		$criteria->compare('admin_user_email',$this->admin_user_email,true);
		$criteria->compare('admin_user_password',$this->admin_user_password,true);
		$criteria->compare('admin_realname',$this->admin_realname,true);
		$criteria->compare('admin_status',$this->admin_status);
		$criteria->compare('admin_createtime',$this->admin_createtime,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}