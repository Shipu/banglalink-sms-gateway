<?php

namespace Shipu\BanglalinkSmsGateway\Services;


use Apiz\AbstractApi;

class Banglalink extends AbstractApi
{
    protected $sms = [];

    protected $mobiles = [];

    protected $config;

    protected $debug = true;

    protected $template = true;

    protected $numberPrefix = '88';

    protected $prefix = 'sendSMS/sendSMS';

    protected $sendingParameters = [];

    /**
     * Banglalink constructor.
     *
     * @param $config
     */
    public function __construct( $config )
    {
        $this->config = $config;
        parent::__construct();
    }

    /**
     * Set Number Prefix
     *
     * @param string $prefix
     * @return $this
     */
    public function numberPrefix( $prefix = '88' )
    {
        $this->numberPrefix = $prefix;

        return $this;
    }

    /**
     * Set Message
     *
     * @param string $message
     * @param null $to
     *
     * @return $this
     */
    public function message( $message = '', $to = null )
    {
        $this->sms[] = $message;
        if ( !is_null($to) ) {
            $this->to($to);
        }

        return $this;
    }

    /**
     * Set Phone Numbers
     *
     * @param $to
     *
     * @return $this
     */
    public function to( $to )
    {
        if ( is_array($to) ) {
            $this->mobiles = array_merge($this->mobiles, $to);
        } else {
            $this->mobiles[] = $to;
        }

        return $this;
    }

    /**
     * Send Method
     *
     * @param array $array
     *
     * @return mixed
     */
    public function send( $array = [] )
    {
        return $this->makingSmsFormatAndSendingSMS($array);
    }

    /**
     * Formatting Given Data
     *
     * @param array $array
     *
     * @return array
     */
    protected function makingSmsFormatAndSendingSMS( $array = [] )
    {
        if ( $array ) {
            $this->sms = array_merge($this->sms, $this->splitSmsAndNumbers($array));
        } else {
            $this->sms = $this->splitSmsAndNumbers($array);
        }

        if ( count($this->sms) == 1 ) {
            $this->sms = $this->sms[ 0 ];

            return $this->singleSMSOrTemplate();
        } else {
            return $this->makeMultiSmsMultiUser();
        }
    }

    /**
     * Formatting sms and mobiles property
     *
     * @param $array
     *
     * @return array
     */
    private function splitSmsAndNumbers( $array )
    {
        if ( $array && !isset($array[ 'message' ]) && !isset($array[ 'to' ]) ) {
            $this->sms = array_merge($this->sms, array_values($array));
            $arrayKeys = array_keys($array);
            if ( $arrayKeys[ 0 ] != 0 ) {
                $this->mobiles = array_merge($this->mobiles, $arrayKeys);
            }
        } else {
            $this->sms = array_merge($this->sms, $array);
        }
        $sms = $mobiles = [];
        if ( is_array($this->sms) ) {
            foreach ( $this->sms as $key => $message ) {
                if ( is_array($message) && isset($message[ 'message' ]) && isset($message[ 'to' ]) ) {
                    $sms[]     = rawurlencode($message[ 'message' ]);
                    $mobiles[] = $message[ 'to' ];
                } elseif ( $key === 'to' ) {
                    $mobiles[] = $message;
                } else {
                    $sms[] = rawurlencode($message);
                }
            }
        }

        if ( $mobiles ) {
            $this->mobiles = array_merge($this->mobiles, $mobiles);
        }

        return $sms;
    }

    /**
     * Rendering template
     *
     * @return array|bool
     */
    private function singleSMSOrTemplate()
    {
        try {
            $this->mobiles = implode(',', $this->mobiles);

            return $this->makeSingleSmsToUser();
        } catch ( \ErrorException $exception ) {
            if ( $this->template ) {
                $template          = rawurldecode($this->sms);
                $putDataInTemplate = $sms = $mobiles = [];

                if ( is_array($this->mobiles) ) {
                    foreach ( $this->mobiles as $mobile => $message ) {
                        try {
                            $putData                      = vsprintf($template, $message);
                            $sms[]                        = $putData;
                            $mobiles[]                    = $mobile;
                            $putDataInTemplate[ $mobile ] = $putData;
                        } catch ( \ErrorException $exception ) {
                            $putData                            = vsprintf($template, $message[ 1 ]);
                            $sms[]                              = $putData;
                            $mobiles[]                          = $message[ 0 ];
                            $putDataInTemplate[ $message[ 0 ] ] = $putData;
                        }
                    }
                }
                if ( $sms ) {
                    $this->sms = $sms;
                }
                if ( $mobiles ) {
                    $this->mobiles = $mobiles;
                }

                return $this->makeMultiSmsMultiUser();
            }

            return false;
        }
    }

    /**
     * Sending Single SMS
     *
     * @return array
     */
    protected function makeSingleSmsToUser()
    {
        $this->gettingParameters($this->sms, $this->numberPrefix . $this->mobiles);

        return $this->sendToServer();
    }

    /**
     * Prepare Sending parameters
     *
     * @param $sms
     * @param $mobiles
     *
     * @return $this
     */
    private function gettingParameters( $sms, $mobiles )
    {
        $this->sendingParameters = [
            'user'     => $this->config[ 'user' ],
            'password' => $this->config[ 'password' ],
            'message'  => $sms,
            'msisdn'   => $mobiles,
        ];

        return $this;
    }

    /**
     * Getting response from api
     *
     * @return mixed
     */
    private function sendToServer()
    {
        if ( $this->debug ) {
            return $this->sendingParameters;
        }

        return $this->query($this->sendingParameters)
            //            ->headers([ 'Accept' => 'application/xml' ])
            ->get($this->prefix);
    }

    /**
     * Sending Multiple SMS
     *
     * @return array
     */
    protected function makeMultiSmsMultiUser()
    {
        $response = [];
        $count    = 1;
        if ( is_array($this->sms) ) {
            foreach ( $this->sms as $key => $message ) {
                if ( isset($this->mobiles[ $key ]) ) {
                    $number = $this->numberPrefix . $this->mobiles[ $key ];
                    $this->gettingParameters($message, $number);
                    $response[ 'res-' . $count++ . '-' . $number ] = $this->sendToServer();
                }
            }
        }

        return $response;
    }

    /**
     * Set Debug
     *
     * @param bool $debug
     *
     * @return $this
     */
    public function debug( $debug = false )
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * set base URL for guzzle client
     *
     * @return string
     */
    protected function setBaseUrl()
    {
        return 'https://vas.banglalinkgsm.com';
    }

}