<?php

/**
 * This is the model class for table "p2p_messages".
 *
 * The followings are the available columns in table 'p2p_messages':
 * @property integer $id
 * @property string $uid
 * @property string $title
 * @property string $contents
 * @property string $mobile
 * @property string $email
 * @property integer $type
 * @property integer $status
 * @property integer $create_time
 * @property string $number
 */
class Messages extends CActiveRecord
{
    const TYPE_ONE   = 1; //消息中心
    const TYPE_TWO   = 2; //邮件
    const TYPE_THREE = 3; //消息中心和邮件
    const TYPE_FOUR  = 4; //短信
    const TYPE_FIVE  = 5; //消息中心和短信
    const TYPE_SIX   = 6; //邮件和短信
    const TYPE_SEVEN = 7; //消息中心、邮件和短信

    const STATUS_NO_SEND = 0;   //未发送
    const STATUS_SENDED = 1;    //已发送

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'p2p_messages';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('uid, title, contents, mobile, email', 'required'),
			array('type, status, create_time', 'numerical', 'integerOnly'=>true),
			array('uid, number', 'length', 'max'=>11),
			array('title', 'length', 'max'=>45),
			array('mobile', 'length', 'max'=>20),
			array('email', 'length', 'max'=>120),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, uid, title, contents, mobile, email, type, status, create_time, number', 'safe', 'on'=>'search'),
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
			'id' => 'ID',
			'uid' => 'Uid',
			'title' => 'Title',
			'contents' => 'Contents',
			'mobile' => 'Mobile',
			'email' => 'Email',
			'type' => 'Type',
			'status' => 'Status',
			'create_time' => 'Create Time',
			'number' => 'Number',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('uid',$this->uid,true);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('contents',$this->contents,true);
		$criteria->compare('mobile',$this->mobile,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('type',$this->type);
		$criteria->compare('status',$this->status);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('number',$this->number,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Messages the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	* 获取个人消息记录
	*/
	public function getPersonalMessage($uid, $type=0,$index = 0, $count = 0){
		$CDC = new CDbCriteria;
		$CDC->condition = 'uid = :uid AND number = :number';
		if ($index){
			$CDC->offset = $index;
		}
		if ($count != 0){
			$CDC->limit = $count;
		}
		$CDC->params = array(':uid'=>$uid,'number'=>$type);
		$message = $this->findAll($CDC);
		return $message;
	}
	/**
	* 设置个人消息已读
	*/
	public function setPersonalMessageRead($id){
		$rs_row = $this->updateByPk($id, array('number'=>1));
		return $rs_row;
	}
	/**
	 * 设置个人消息
	 * @param number $type
	 * @param unknown $msg_key
	 * @param unknown $replaced
	 * @param unknown $replace
	 */
	public function setPersonalMessage($uid,$type,$msg_key,$replaced,$replace){
		$this->title= Yii::app()->params['message_title'][$type];
		$content = Yii::app()->params['message'][$msg_key];
		$this->contents  = str_replace($replaced, $replace, $content);//个人中心消息
		$this->uid = $uid;
		$this->email =" ";
		$this->create_time =time();
		return $this->insert();
	}
	
	/**
	 * 设置个人消息和短信
	 * @param number $type
	 * @param unknown $msg_key
	 * @param unknown $replaced
	 * @param unknown $replace
	 * @phone 
	 * @email
	 */
	public function setSPersonalMessage($uid,$type,$msg_key,$replaced,$replace,$phone="",$email=""){
		$this->title= Yii::app()->params['message_title'][$type];
		$content = Yii::app()->params['message'][$msg_key];
		$this->contents  = str_replace($replaced, $replace, $content);//个人中心消息
		$this->uid = $uid;
		$this->type= 5;
		$this->mobile = $phone;
		$this->email =$email;
		$this->create_time =time();
		return $this->insert();
	}

    public static function publicMessages($uid,$content,$type,$title,$timedate=null,$money=null,$project=null,$total=null,$fee=null,$corpus=null)
    {

        $userInfo = User::model()->findByPk($uid);
        if(!empty($userInfo))
        {

            $user_name=trim($userInfo->user_name);//获取用户名除去左右空格
            if($timedate!==null && $money!=null && $project===null && $total===null && $fee===null && $corpus===null)    //提现成功
                $content = str_replace(array('#UserName#','#timedate','#money'),array($user_name,$timedate,$money),$content);
            else if($timedate!==null && $money!=null && $project!==null && $total!==null && $fee===null && $corpus===null)   //投资成功
                $content = str_replace(array('#UserName#','#timedate','#project','#money','#total'),array($user_name,$timedate,$project,$money,$total),$content);
            else if($timedate!==null && $money!==null && $total!==null && $project===null && $fee===null && $corpus===null)       //充值成功
                $content = str_replace(array('#UserName#','#timedate','#money','#total'),array($user_name,$timedate,$money,$total),$content);
            else if($fee!==null && $corpus!==null)     //还款成功
                $content = str_replace(array('#UserName#','#project','#fee','#corpus','#total'),array($user_name,$project,$fee,$corpus,$total),$content);
            else
                $content = str_replace('#UserName#',$user_name,$content);


            $messages = new Messages();
            $messages->uid=$uid;
            $messages->title=$title;
            $messages->contents=$content;
            $messages->mobile=$userInfo->mobile;
            $messages->create_time=time();
            $messages->email=$userInfo->email;
            $messages->type=$type;
            return $messages->insert();
        }
        return false;
    }

}
