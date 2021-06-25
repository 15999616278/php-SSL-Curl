<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/5/20
 * Time: 14:26
 */

namespace app\common;

/**
 * 深圳邮政EMS 快递类
 * Class Ems
 * @package app\common
 */
class SzEms
{

    /**
     * 打印标签
     * 获取base64字符串
     * @param string $data
     * @return mixed
     */
    public function getlabel($data = '')
    {
        if (!$data) {
            return false;
        }

        /**
         *  data 格式：
         *  $data = '{ "mailCode":"AS830645094CN" }';
         */
        $url = 'https://cbip.shenzhenpost.com.cn/szsyqdzx-itf-web/p/custom/getlabel';
        $sign = $this->sign($data);
        $header = array(
            'Content-Type: application/json',
            'partnercode:10985',
            'datadigest:' . $sign,
        );

        $res = $this->getData($url, 1, $data, 1, 30, $header);
        $base = JD(JD($res)['obj'])['labelContent'];
        return $base;
//        $te = './test.pdf';
//        file_put_contents($te, base64_decode($base) . "\r\n", 8);
//        $pdf = str_shuffle(md5(time())) . '.pdf';
//        AliyunUpload::uploadPdf($te, $pdf);
//        return $pdf;
    }


    /**
     * 创建订单
     * @param string $data 发送的报文，json 格式
     * @return bool|mixed
     */
    public function createOrder($data = '')
    {
        if (!$data) {
            return false;
        }

        $sign = $this->sign($data);
        $url = 'https://cbip.shenzhenpost.com.cn/szsyqdzx-itf-web/p/custom/createorder';
        $header = array(
            'Content-Type: application/json',
            'partnercode:10985',
            'datadigest:' . $sign,
        );
        $res = $this->getData($url, 1, $data, 1, 30, $header);
        $res = JD($res);

        /**
         * res
         * Array
         * (
         * [msg] => SUCCESS
         * [obj] => {"status":"true","orderId":"111-4511364-256185211",
         * "mailCode":"AQ953992894CN","customMailCode":""}
         * [resCode] => A00001
         * )
         */


        if ($res['msg'] == 'SUCCESS') {
            $mailCode = [JD($res['obj'])['orderId'], JD($res['obj'])['mailCode']];
            return $mailCode;
        } else {
            return $res;
        }

        /**
         * data 格式示例：
         *
         */
        /*
        $data = '{
                     "orderSource": "1",
                     "productCode": "YYB818001",
                     "orderId": "1101268788278989533",

                     "volume": "",
                     "length": 25.0,
                     "width": 6.6,
                     "height": 18.8,
                     "mailWeight": 500,
                     "mailValue": 1000,
                     "valueType": "RMB",
                     "batteryFlag": 1,
                     "innerType": "3",
                     "remark": "这是一个测试订单的信息",
                     "sender": {
                       "name": "zhangsan",
                       "company": "COMPANY",
                       "postCode": "1111111111",
                       "phone": "15083609300",
                       "mobile": "15083609200",
                       "email": "123456789@qq.com",
                       "idType": 1,
                       "idNO": "360502190009156010",
                       "countryCode": "CN",
                       "province": "GuangDong",
                       "city": "ShengZhen",
                       "address": "老街地铁站",
                       "duty": "123456789"
                     },
                     "receiver": {
                       "name": "lisi",
                       "company": "COMPANY",
                       "postCode": "1111111111",
                       "phone": "15083609300",
                       "mobile": "15083609200",
                       "email": "987654321@qq.com",
                       "idType": 1,
                       "idNO": "360502190009156010",
                       "countryCode": "US",
                       "province": "GuangDong",
                       "city": "ShengZhen",
                       "address": "老街地铁站",
                       "duty": "987654321"},
                     "cargo": [{
                         "innerName": "防疫口罩",
                         "innerNameEn": "defended_mask",
                         "innerQty": 2,
                         "innerWeight": 400,
                         "innerPrice": 89.9,
                         "sku": "111000202004230099",
                         "original": "CN",
                         "customsCode": "customs_code_01",
                         "innerIngredient": "纤维"}
                     ]
                   }';
        */


    }


    public function createPdf($arr, $path)
    {
        require_once VENDOR_PATH . 'FPDI-master/src/autoload.php';
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $pdf->setPrintHeader(false);
        foreach ($arr as $v) {
            $pdf->setSourceFile($v);
            $tpl = $pdf->importPage(1);
            $pdf->addPage();
            $pdf->useTemplate($tpl);
        }
        $pdf->output($path, "F");
        return $path;
    }


    /**
     * 签名方式：
     * 比如客户创建订单过程，推送订单信息到邮政自主商业平台，业务人员会提供签名字符 串 signKey 给 客 户 ，
     * 客 户 IT 人 员 根 据 bodyContent 和 signKey ， 生 成 签 名 体 =bodyContent+signKey，
     * 对签名体通过 DigestUtils.sha256Hex 方法签名处理，生成签名 datadigest。
     * 例如： datadigest= DigestUtils.sha256Hex(bodyContent+signKey)。
     * bodyContent="hello1234"，signKey="key123";
     * datadigest= DigestUtils.sha256Hex(“hello1234 key123”)
     * 签名结果: 502572e711793da475c8cf919479f00a2a5d988c7032580b91b5b9d8716e9474
     * @param $data
     * @return string
     */
    protected function sign($data)
    {
        $bodyContent = $data;
        $signKey = '8247eee39d4f4ee3826efbb90a41c858';
        $content = $bodyContent . $signKey;
        $sign = hash('sha256', $content, true);
        return bin2hex($sign);

    }


    /**
     * 数据请求方式
     * @param $url
     * @param int $isPost
     * @param string $postData
     * @param int $isHttps
     * @param int $timeout
     * @param array $header
     * @return bool|string
     */
    protected function getData($url, $isPost = 0, $postData = '', $isHttps = 0, $timeout = 45, $header = array())
    {
        $ch = curl_init();
        if (false == $ch) {
            return false;
        }
        if ($isHttps == 1) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }
        if ($isPost == 1) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        curl_setopt($ch, CURLOPT_SSLCERT, getcwd() . '/cbip0151.pem');
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => false, //true  false 不显示
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_RETURNTRANSFER => 1,
        );
        if (!empty($header)) {
            $options[CURLOPT_HTTPHEADER] = $header;
        }
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }


    /**
     * 数据请求方法二
     * @param $url
     * @param $data
     * @param $header
     * @return bool|string
     */
    protected function getPostData($url, $data, $header)
    {
        $tuCurl = curl_init();
        curl_setopt($tuCurl, CURLOPT_URL, $url);
        curl_setopt($tuCurl, CURLOPT_PORT, 443);
        curl_setopt($tuCurl, CURLOPT_VERBOSE, 0);
        curl_setopt($tuCurl, CURLOPT_HEADER, 0);
        curl_setopt($tuCurl, CURLOPT_SSLVERSION, 3);
        curl_setopt($tuCurl, CURLOPT_SSLCERT, getcwd() . "/cbip0151.cer");
        curl_setopt($tuCurl, CURLOPT_SSLKEY, getcwd() . "/cbip0151.key");
        curl_setopt($tuCurl, CURLOPT_CAINFO, getcwd() . "/cbip0151_ca.crt");
        curl_setopt($tuCurl, CURLOPT_POST, 1);
        curl_setopt($tuCurl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($tuCurl, CURLOPT_SSLVERSION, 0);
        curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($tuCurl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($tuCurl, CURLOPT_HTTPHEADER, $header);
        $tuData = curl_exec($tuCurl);
//        if (!curl_errno($tuCurl)) {
//            $info = curl_getinfo($tuCurl);
//            echo 'Took ' . $info['total_time'] . ' seconds to send a request to ' . $info['url'];
//        } else {
//            echo 'Curl error: ' . curl_error($tuCurl);
//        }

        curl_close($tuCurl);
        return $tuData;
    }


}