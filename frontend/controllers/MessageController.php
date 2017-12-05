<?php
/**
 * Created by PhpStorm.
 * User: simplyworld
 * Date: 05.12.17
 * Time: 11:52
 */

namespace frontend\controllers;

class MessageController extends SiteController
{

    public function actionIndex()
    {
        return $this->render('chat');
    }

}