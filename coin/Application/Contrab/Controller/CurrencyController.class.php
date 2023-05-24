<?php
namespace Contrab\Controller;


class CurrencyController extends \Think\Controller
{
    /**
     * 定时获取第三方 虚拟币 采集数据入库
     */
    protected function _initialize(){
        $allow_action=array("getCoinData","getCoinHistory","");
        if(!in_array(ACTION_NAME,$allow_action)){
            $this->error("第三方接口访问错误！");
        }
    }
    //获取行情数据
    public function get_maket_api($api){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt ($ch, CURLOPT_URL, $api );
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,10);
        $result = json_decode(curl_exec($ch),true);
        return $result;
    }

    /**
     * 获取第三方虚拟币信息
     * @param $coin
     */
    public function getCoinData($coin){
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        $api = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=".$symbol;
        $data = $this->get_maket_api($api);
        $statusArr=['message'=>'未获取到第三方数据','code'=>404];
        if ($data && $data['status'] == 'ok') { // 获取第三方数据成功
            $newData=[
                'name'=>$cname,
                'ch'=>$data['ch'],
                'ts'=>$data['ts'],
                'data'=>json_encode($data['data']),
                'time'=>time(),
            ];
            //直接更新
           $status= M('currency')->where(['name'=>$cname])->save($newData);
           $statusArr=['message'=>'更新第三方虚拟币数据成功','code'=>200];
           if (!$status) { // 更新不成功  写入添加
               if (!M('currency')->where(['name'=>$cname])->find()) {
                   // 写入数据表中
                   M('currency')->add($newData);
                   $statusArr=['message'=>'写入第三方虚拟币数据成功','code'=>200];
               }else{
                   $statusArr=['message'=>'采集三方业务错误,请核实','code'=>502];
               }
            }
        }
        $returnData=[
            'code'=>$statusArr['code'],
            'message'=>$statusArr['message'],
        ];
        //
        $this->ajaxReturn(['message'=>$returnData['message'],'code'=>$returnData['code']]);
    }


    //获取行情单个行情数据
    public function getCoinHistory($coin){
        $symbol = strtolower($coin).'usdt';
        $cname = strtoupper($coin)."/USDT";
        $url = "https://api.huobi.pro/market/history/trade?symbol=".$symbol."&size=20";
        $result = $this->get_maket_api($url);

        $data = $result['data'];
        if ($result && $result['status'] == 'ok') {
            // 删除原有的数据
            M('history')->where(['name'=>$cname])->delete();
            echo M('history')->getLastSql();
            foreach($data as $key=>$vo){
                $newData=[
                    'name'=>$cname,
                    'direction' => $vo['data'][0]['direction'],
                    'amount'=> $vo['data'][0]['amount'],
                    'price' => $vo['data'][0]['price'],
                    'trade_id'  => $vo['data'][0]['trade-id'],
                    'update_time'=>time()
                ];
                // 写入数据表中
                M('history')->add($newData);
            }
            $this->ajaxReturn(['message'=>'写入第三方虚拟币交易记录数据成功','code'=>200,'name'=>$cname]);
        }
        $this->ajaxReturn(['message'=>'未获取到第三方虚拟币交易记录数据','code'=>502]);
    }

}