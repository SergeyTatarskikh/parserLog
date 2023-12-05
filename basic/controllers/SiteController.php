<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $connection = Yii::$app->getDb();
        $command = $connection->createCommand("
        SELECT DATE(timedate) AS date, COUNT(*) AS request_count, MAX(url) AS popular_url, MAX(browser) AS popular_browser
        FROM logs
        GROUP BY DATE(timedate)
        ORDER BY date DESC
    ");
        $data = $command->queryAll();

        // Calculate browser share
        $browserShares = [];
        foreach ($data as $row) {
            $browserShares[] = $row['request_count'] / array_sum(array_column($data, 'request_count')) * 100;
        }

        // Add browser_share to $data
        foreach ($data as $key => $row) {
            $data[$key]['browser_share'] = $browserShares[$key];
        }

        return $this->render('index', [
            'data' => $data,
        ]);
    }




}
