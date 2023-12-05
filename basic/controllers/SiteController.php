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
        $dateFrom = Yii::$app->request->get('dateFrom');
        $dateTo = Yii::$app->request->get('dateTo');
        $os = Yii::$app->request->get('os');
        $architecture = Yii::$app->request->get('architecture');

        $query = (new \yii\db\Query())
            ->select('DATE(timedate) AS date, COUNT(*) AS request_count, MAX(url) AS popular_url, MAX(browser) AS popular_browser')
            ->from('logs')
            ->groupBy('DATE(timedate)')
            ->orderBy(['date' => SORT_DESC]);

        if ($dateFrom) {
            $query->andWhere(['>=', 'timedate', $dateFrom]);
        }
        if ($dateTo) {
            $query->andWhere(['<=', 'timedate', $dateTo]);
        }
        if ($os) {
            $query->andWhere(['os' => $os]);
        }
        if ($architecture) {
            $query->andWhere(['architecture' => $architecture]);
        }

        $data = $query->all();

        // Calculate browser share
        $totalRequests = array_sum(array_column($data, 'request_count'));
        foreach ($data as &$row) {
            $row['browser_share'] = ($row['request_count'] / $totalRequests) * 100;
        }

        return $this->render('index', [
            'data' => $data,
        ]);
    }





}
