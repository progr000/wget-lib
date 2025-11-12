<?php

namespace Maksym\Wget;

class WgetDriver
{
    /** @var int */
    private static $instancesCount = 0;
    /** @var string[] */
    private static $allowed_methods = array('GET', 'POST', 'HEAD', 'PUT', 'PATCH', 'DELETE');
    /** @var array */
    private $send_headers = array();
    /** @var string */
    private $flagAs = "x-www-form-urlencoded";

    /** @var \CurlHandle|false|resource */
    private $curl;
    /** @var array  */
    private static $curl_init_sys_options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLINFO_HEADER_OUT => true,
        CURLOPT_VERBOSE => true,
        CURLOPT_HEADER => true,
        CURLOPT_FRESH_CONNECT => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_SSL_VERIFYPEER => 1,
        CURLOPT_ENCODING => "gzip", // TODO: discover this
    );
    private $curl_init_user_options = array();

    /**
     * Constructor
     */
    private function __construct()
    {
        self::$instancesCount++;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        self::$instancesCount--;
    }

    /**
     * @param array $curl_setopt
     * @return WgetDriver
     */
    public static function init($curl_setopt = array())
    {
        $instance = new self();
        $instance->curl = curl_init();
        //curl_reset($instance->curl);
        $instance->curl_init_user_options = array_replace(
            self::$curl_init_sys_options,
            $curl_setopt
        );
        curl_setopt_array($instance->curl, $instance->curl_init_user_options);
        return $instance;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        //curl_reset($this->curl);
        curl_close($this->curl);
        $this->curl = curl_init();
        $this->send_headers = array();
        $this->flagAs = "x-www-form-urlencoded";
        curl_setopt_array($this->curl, $this->curl_init_user_options);
        return $this;
    }

    /**
     * @param string $bearerHash
     * @param array $additional_headers
     * @return $this
     */
    public function setBearerAutorisation($bearerHash, array $additional_headers = array())
    {
        $this->setHeaders(array("Authorization: Bearer $bearerHash"));
        $this->setHeaders($additional_headers);
        return $this;
    }

    /**
     * Send post request as json
     * @return $this
     */
    public function asJson()
    {
        $this->setHeaders(array("Content-Type: application/json"));
        $this->flagAs = "json";
        return $this;
    }

    /**
     * Send post request as xml
     * @return $this
     */
    public function asXml()
    {
        $this->setHeaders(array("Content-Type: application/xml"));
        $this->flagAs = "xml";
        return $this;
    }

    /**
     * Send post request as form
     * @return $this
     */
    public function asXWwwFormUrlencoded()
    {
        $this->setHeaders(array("Content-Type: application/x-www-form-urlencoded"));
        $this->flagAs = "x-www-form-urlencoded";
        return $this;
    }

    /**
     * Send post request as multipart/form-data
     * @return $this
     */
    public function asMultipartForm()
    {
        $this->setHeaders(array("Content-Type: multipart/form-data"));
        $this->flagAs = "multipart-form-data";
        return $this;
    }

    /**
     * @param string $url
     * @return $this
     */
    private function setUrl($url)
    {
        curl_setopt($this->curl, CURLOPT_URL, $url);
        return $this;
    }

    /**
     * @param string $method
     * @return $this
     */
    private function setMethod($method)
    {
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);
        return $this;
    }

    /**
     * @param array|object|string|null $data
     * @return $this
     */
    private function setData($data)
    {
        if (empty($data)) {
            return $this;
        }

        if (!is_string($data)) {
            switch ($this->flagAs) {
                case "json":
                    $data = json_encode($data);
                    break;
                case "x-www-form-urlencoded":
                    $data = http_build_query($data);
                    break;
            }
        }

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
        return $this;
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $key => $value) {
            if (gettype($key) === 'string') {
                $header_name = trim($key);
                $header_value = trim($value);
                $unique_header_name = mb_strtolower($header_name);
            } else {
                $tmp = explode(":", $value);
                if (isset($tmp[0], $tmp[1])) {
                    $header_name = trim($tmp[0]);
                    $header_value = trim($tmp[1]);
                    $unique_header_name = mb_strtolower($header_name);
                }
            }
            if (isset($unique_header_name, $header_name, $header_value)) {
                $this->send_headers[$unique_header_name] = "{$header_name}: {$header_value}";
            }
        }

        return $this;
    }

    /**
     * @param int $port
     * @return $this
     */
    public function setPort($port)
    {
        curl_setopt($this->curl, CURLOPT_PORT, $port);
        return $this;
    }

    /**
     * Execute curl and process response
     * @return WgetResponse
     */
    private function exec()
    {
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->send_headers);
        return new WgetResponse($this->curl, $this);
    }

    /**
     * @param string $url
     * @param null|string|array $get_data
     * @param null|string|array $body_data
     * @return WgetResponse
     */
    public function get($url, $get_data = null, $body_data = null)
    {
        if (!is_null($get_data)) {
            $str = "";
            $url = trim(trim($url, '?'), '&');
            if (is_string($get_data)) {
                $str = trim(trim($get_data, '?'), '&');
            } elseif (is_array($get_data)) {
                foreach ($get_data as $k => $v) {
                    if (!is_array($v) && !is_object($v)) {
                        $str .= $k . '=' . urlencode($v) . '&';
                    } elseif (is_array($v)) {
                        foreach ($v as $v2) {
                            $str .= $k . '=' . urlencode($v2) . '&';
                        }
                    }
                }
            }
            /**/
            if (strrpos($url, '?') === false) {
                $url .= '?' . $str;
            } else {
                $url .= '&' . $str;
            }
        }

        return $this
            ->setUrl($url)
            ->setMethod('GET')
            ->setData($body_data)
            ->exec();
    }

    /**
     * @param string $url
     * @param array|object|string|null $data
     * @return WgetResponse
     */
    public function post($url, $data = null)
    {
        return $this
            ->setUrl($url)
            ->setMethod('POST')
            ->setData($data)
            ->exec();
    }

    /**
     * @param string $url
     * @param array|object|string|null $data
     * @return WgetResponse
     */
    public function put($url, $data = null)
    {
        return $this
            ->setUrl($url)
            ->setMethod('PUT')
            ->setData($data)
            ->exec();
    }

    /**
     * @param string $url
     * @param array|object|string|null $data
     * @return WgetResponse
     */
    public function patch($url, $data = null)
    {
        return $this
            ->setUrl($url)
            ->setMethod('PATCH')
            ->setData($data)
            ->exec();
    }

    /**
     * @param string $url
     * @param array|object|string|null $data
     * @return WgetResponse
     */
    public function delete($url, $data = null)
    {
        return $this
            ->setUrl($url)
            ->setMethod('DELETE')
            ->setData($data)
            ->exec();
    }
}
