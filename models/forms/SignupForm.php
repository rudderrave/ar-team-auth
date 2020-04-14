<?php

namespace arteam\auth\models\forms;

use arteam\models\User;
use Yii;
use yii\base\Model;
use yii\helpers\Html;

class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $repeat_password;
    public $captcha;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            ['captcha', 'captcha', 'captchaAction' => '/auth/default/captcha'],
            [['username', 'email', 'password', 'repeat_password', 'captcha'], 'required'],
            [['username', 'email', 'password', 'repeat_password'], 'trim'],
            [['email'], 'email'],
            ['username', 'unique',
                'targetClass' => 'arteam\models\User',
                'targetAttribute' => 'username',
            ],
            ['email', 'unique',
                'targetClass' => 'arteam\models\User',
                'targetAttribute' => 'email',
            ],
            ['username', 'purgeXSS'],
            ['username', 'string', 'max' => 50],
            ['username', 'match', 'pattern' => Yii::$app->arteam->usernameRegexp, 'message' => Yii::t('arteam/auth', 'The username should contain only Latin letters, numbers and the following characters: "-" and "_".')],
            ['username', 'match', 'not' => true, 'pattern' => Yii::$app->arteam->usernameBlackRegexp, 'message' => Yii::t('arteam/auth', 'Username contains not allowed characters or words.')],
            ['password', 'string', 'max' => 255],
            ['repeat_password', 'compare', 'compareAttribute' => 'password'],
        ];

        return $rules;
    }

    /**
     * Remove possible XSS stuff
     *
     * @param $attribute
     */
    public function purgeXSS($attribute)
    {
        $this->$attribute = Html::encode($this->$attribute);
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('arteam/auth', 'Login'),
            'email' => Yii::t('arteam/auth', 'E-mail'),
            'password' => Yii::t('arteam/auth', 'Password'),
            'repeat_password' => Yii::t('arteam/auth', 'Repeat password'),
            'captcha' => Yii::t('arteam/auth', 'Captcha'),
        ];
    }

    /**
     * @param bool $performValidation
     *
     * @return bool|User
     */
    public function signup($performValidation = true)
    {
        if ($performValidation AND !$this->validate()) {
            return false;
        }

        $user = new User();
        $user->password = $this->password;
        $user->username = $this->username;
        $user->email = $this->email;

        if (Yii::$app->arteam->emailConfirmationRequired) {
            $user->status = User::STATUS_INACTIVE;
            $user->generateConfirmationToken();
            // $user->save(false);

            if (!$this->sendConfirmationEmail($user)) {
                $this->addError('username', Yii::t('arteam/auth', 'Could not send confirmation email'));
            }
        }

        if (!$user->save()) {
            $this->addError('username', Yii::t('arteam/auth', 'Login has been taken'));
        } else {
            return $user;
        }

        return FALSE;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    protected function sendConfirmationEmail($user)
    {
        return Yii::$app->mailer->compose(Yii::$app->arteam->emailTemplates['signup-confirmation'], ['user' => $user])
            ->setFrom(Yii::$app->arteam->emailSender)
            ->setTo($user->email)
            ->setSubject(Yii::t('arteam/auth', 'E-mail confirmation for') . ' ' . Yii::$app->name)
            ->send();
    }

    /**
     * Check received confirmation token and if user found - activate it, set username, roles and log him in
     *
     * @param string $token
     *
     * @return bool|User
     */
    public function checkConfirmationToken($token)
    {
        $user = User::findInactiveByConfirmationToken($token);

        if ($user) {
            
            $user->status = User::STATUS_ACTIVE;
            $user->email_confirmed = 1;
            $user->removeConfirmationToken();
            $user->save(false);
            $user->assignRoles(Yii::$app->arteam->defaultRoles);
            Yii::$app->user->login($user);

            return $user;
        }

        return false;
    }
}