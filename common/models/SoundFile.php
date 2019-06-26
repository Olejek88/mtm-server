<?php

namespace common\models;

use Yii;
use common\components\MtmActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\web\UploadedFile;
use yii\base\InvalidConfigException;

/**
 * This is the model class for table "sound_file".
 *
 * @property int $_id Id
 * @property string $uuid
 * @property string $oid
 * @property string $title
 * @property string $soundFile
 * @property string $nodeUuid
 * @property boolean $deleted
 * @property string $createdAt
 * @property string $changedAt
 *
 * @property Node $node
 * @property string $uploadPath
 * @property string $soundFileUrl
 * @property Organisation $organisation
 */
class SoundFile extends MtmActiveRecord
{
    public $sFile;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%sound_file}}';
    }

    /**
     * Behaviors.
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'createdAt',
                'updatedAtAttribute' => 'changedAt',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uuid', 'oid', 'title', 'nodeUuid', 'sFile'], 'required', 'on' => 'default'],
            [['uuid', 'oid', 'title', 'nodeUuid'], 'required', 'on' => 'update'],
            [['deleted'], 'required', 'on' => 'delete'],
            [['deleted'], 'boolean'],
            [['createdAt', 'changedAt'], 'safe'],
            [['uuid', 'oid', 'nodeUuid'], 'string', 'max' => 45],
            [['soundFile'], 'safe'],
            [['sFile'], 'file', 'extensions' => 'mp3, ogg'],
            [['title'], 'string', 'max' => 150],
            [['uuid'], 'unique'],
            [['nodeUuid'], 'exist', 'skipOnError' => true, 'targetClass' => Node::class, 'targetAttribute' => ['nodeUuid' => 'uuid']],
            [['oid'], 'exist', 'skipOnError' => true, 'targetClass' => Organisation::class, 'targetAttribute' => ['oid' => 'uuid']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            '_id' => 'Id',
            'uuid' => 'Uuid',
            'oid' => 'Oid',
            'title' => 'Название',
            'soundFile' => 'Звуковой файл',
            'nodeUuid' => 'Контроллер',
            'deleted' => 'Удалён',
            'createdAt' => 'Создан',
            'changedAt' => 'Изменён',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getNode()
    {
        return $this->hasOne(Node::class, ['uuid' => 'nodeUuid']);
    }

    /**
     * @return ActiveQuery
     */
    public function getOrganisation()
    {
        return $this->hasOne(Organisation::class, ['uuid' => 'oid']);
    }

    /**
     * fetch stored image url
     * @return string
     */
    public function getSoundFileUrl()
    {
        return $this->uploadPath . '/' . $this->soundFile;
    }

    /**
     * Process upload of image
     *
     * @return mixed the uploaded image instance
     */
    public function uploadSoundFile()
    {
        // get the uploaded file instance. for multiple file uploads
        // the following data will return an array (you may need to use
        // getInstances method)
        $uploadFile = UploadedFile::getInstance($this, 'sFile');

        // if no image was uploaded abort the upload
        if (empty($uploadFile)) {
            return false;
        }

        $this->sFile = "exist";
        $res = explode(".", $uploadFile->name);
        $ext = end($res);

        // generate a unique file name
        $this->soundFile = $this->uuid . ".{$ext}";

        // the uploaded image instance
        return $uploadFile;
    }

    /**
     * Process deletion of image
     *
     * @return boolean the status of deletion
     */
    public function deleteSoundFile()
    {
        $file = $this->getSoundFile();

        // check if file exists on server
        if (empty($file) || !file_exists($file)) {
            return false;
        }

        // check if uploaded file can be deleted on server
        if (!unlink($file)) {
            return false;
        }

        // if deletion successful, reset your file attributes
        $this->soundFile = 'deleted';

        return true;
    }

    /**
     * fetch stored image file name with complete path
     * @return string
     */
    public function getSoundFile()
    {
        return $this->uploadPath . '/' . $this->soundFile;
    }

    /**
     * @throws InvalidConfigException
     */
    public function getUploadPath()
    {
        /** @var User $identity */
        $identity = Yii::$app->user->identity;
        $org = $identity->organisation;
        $node = Node::find()->where(['uuid' => $this->nodeUuid])->one();

        return 'files/sound/' . $org->_id . '/' . $node->_id;
    }
}
