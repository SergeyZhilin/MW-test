<?php
/**
 * Created by PhpStorm.
 * User: simplyworld
 * Date: 05.12.17
 * Time: 11:52
 */

namespace frontend\controllers;


use frontend\models\MessageForm;

class MessageController extends SiteController
{

    public function actionIndex()
    {
        return $this->render('chat');
    }

    public function actionPage()
    {
        $form_model = new MessageForm();
        return $this->render('chat', compact('form_model'));
    }

}