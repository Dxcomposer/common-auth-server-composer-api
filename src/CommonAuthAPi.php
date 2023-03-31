<?php


namespace Dx\Commonauthapi;




use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CommonAuthAPi
{

    public $ips=[];

    public $projectKey='';

    /**
     * CommonAuthAPi constructor.
     * @param string $projectKey
     * @param array $ips
     * @throws \Exception
     */
    public function __construct(string $projectKey, array $ips)
    {
        if(strlen($projectKey)!=20)
        {
            throw new \Exception('$projectKey 长度不合法');
        }
        $this->projectKey=$projectKey;
        $this->ips=$ips;
    }


    /**
     * 公共auth登录
     * @param string $source 来源ios,android,web,h5,wx_gzh,wx_xcx,sys
     * @param string $username 用户名
     * @param string $pwd 密码 默认123456
     * @return Result
     * @throws GuzzleException
     */
    public function login(string $source,string $username,string $pwd=''):Result
    {
        $params=['projectKey'=>$this->projectKey,'username'=>$username,'source'=>$source];
        if($pwd){
            $params['pwd']=$pwd;
        }
        var_dump($params);
        return self::http($this->ips,'/api/user/login',$params);
    }

    /**
     * token验证
     * @param string $token
     * @return Result 返回用户信息
     * @throws GuzzleException
     */
    public function checkToken(string $token):Result
    {
        return self::http($this->ips,'/api/user/tokenCheck',['token'=>$token]);
    }

    /**
     * 公共auth登出
     * @param string $token
     * @return Result
     * @throws GuzzleException
     */
    public function loginOut(string $token):Result
    {
        return self::http($this->ips,'/api/user/loginOut',['token'=>$token]);
    }

    /**
     * 项目令牌详情
     * @return Result
     * @throws GuzzleException
     */
    public function projectDetail():Result
    {
        return self::http($this->ips,'/api/project/details',['projectKeys'=>[$this->projectKey]]);
    }

    /**
     * 创建用户
     * @param string $username 用户名
     * @param int $type 用户类型1系统2普通
     * @param int $sort 排序系统默认999普通默认0
     * @param string $nickname 昵称
     * @param string $head 头像url
     * @return Result
     * @throws GuzzleException
     */
    public function createUser(string $username,int $type,int $sort=0,string $nickname='',string $head='')
    {
        $params=[
            'projectKey'=>$this->projectKey,
            'username'=>$username,
            'type'=>$type,
            'sort'=>$sort!=-1?$sort:($type==1?999:0),
        ];
        if($nickname)
        {
            $params['nickname']=$nickname;
        }
        if($head)
        {
            $params['head']=$head;
        }

        return self::http($this->ips,'/api/user/create',$params);
    }

    /**
     * 编辑用户
     * @param string $username 用户名
     * @param int $type  用户类型1系统2普通
     * @param int $sort 排序系统默认999普通默认0
     * @param string $nickname 昵称
     * @param string $head 头像url
     * @return Result
     * @throws GuzzleException
     */
    public function editUser(string $username,int $type,int $sort=-1,string $nickname='',string $head=''):Result
    {
        $params=[
            'projectKey'=>$this->projectKey,
            'username'=>$username,
        ];
        if($type)
        {
            $params['type']=$type;
        }
        if($sort!=-1)
        {
            $params['sort']=$sort;
        }
        if($nickname)
        {
            $params['nickname']=$nickname;
        }
        if($head)
        {
            $params['head']=$head;
        }
        if(count($params)<=2)
        {
            return new Result(false,'未做任何修改');
        }
        return self::http($this->ips,'/api/user/edit',$params);
    }

    /**
     * 查询用户详情
     * @param array $usernames 多个用户名
     * @return Result
     * @throws GuzzleException
     */
    public function userDetail(array $usernames):Result
    {
        return self::http($this->ips,'/api/user/details',[ 'projectKey'=>$this->projectKey,'usernames'=>$usernames]);
    }


    /**
     * 发送http
     * @param array $ips
     * @param string $uri
     * @param array $params
     * @return Result
     * @throws GuzzleException
     */
    public static function http(array $ips,string $uri,array $params=[]):Result
    {
        try {
            $ip=$ips[array_rand($ips)];
            if(strpos($ip,'http')===false)
            {
                $ip='http://'.$ip;
            }
            $response=(new Client())->post($ip.$uri,['form_params'=>$params]);


            $res=$response->getBody()->getContents();

            if(!is_string($res))
            {
                return new Result(false,'返回值数据类型错误');
            }
            $res=json_decode($res,true);
            if(!is_array($res))
            {
                return new Result(false,'返回值数据类型错误');
            }
            if(!isset($res['code'])||!isset($res['data'])||!isset($res['msg']))
            {
                return new Result(false,'api结果不含 code data msg');
            }
            return new Result($res['code']==='00000',$res['msg'],$res['data'],$res['code']);
        }catch (\Exception $e)
        {
            return new Result(false,$e->getMessage());
        }
    }
}