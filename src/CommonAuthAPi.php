<?php


namespace Dxkjcomposer\Comauthapi;




use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CommonAuthAPi
{

    public array $ips=[];

    public string $projectKey = '';
    public string $requestCode='';

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

        if(class_exists(\Hyperf\Context\Context::class))
        {
            $this->requestCode=\Hyperf\Context\Context::get('request-code','');
        }
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
        return $this->http($this->ips,'/api/user/login',$params,$this->requestCode);
    }

    /**
     * token验证
     * @param string $token
     * @return Result 返回用户信息
     * @throws GuzzleException
     */
    public function checkToken(string $token):Result
    {
        return $this->http($this->ips,'/api/user/tokenCheck',['token'=>$token],$this->requestCode);
    }

    /**
     * 公共auth登出
     * @param string $token
     * @return Result
     * @throws GuzzleException
     */
    public function loginOut(string $token):Result
    {
        return $this->http($this->ips,'/api/user/loginOut',['token'=>$token],$this->requestCode);
    }

    /**
     * 项目令牌详情
     * @return Result
     * @throws GuzzleException
     */
    public function projectDetail():Result
    {
        return $this->http($this->ips,'/api/project/details',['projectKeys'=>[$this->projectKey]],$this->requestCode);
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

        return $this->http($this->ips,'/api/user/create',$params,$this->requestCode);
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
        return $this->http($this->ips,'/api/user/edit',$params,$this->requestCode);
    }

    /**
     * 查询用户详情
     * @param array $usernames 多个用户名
     * @return Result
     * @throws GuzzleException
     */
    public function userDetail(array $usernames):Result
    {
        return $this->http($this->ips,'/api/user/details',[ 'projectKey'=>$this->projectKey,'usernames'=>$usernames],$this->requestCode);
    }

    /**
     * 查询当前项目下所有的用户
     * @param int $type 1系统用户  2普通用户
     * @param int $del -1所有 0未删除  1已删除
     * @return Result
     * @throws GuzzleException
     */
    public function userAll(int $type=0,int $del=-1):Result
    {
        $params=[
            'projectKey'=>$this->projectKey,
        ];
        if(in_array($type,[1,2]))
        {
            $params['type']=(int)$type;
        }

        if ($del!=-1&&in_array($del,[0,1]))
        {
            $params['isDel']=(int)$del;
        }
        return $this->http($this->ips,'/api/user/all',$params,$this->requestCode);
    }


    /**
     * 发送http
     * @param array $ips
     * @param string $uri
     * @param array $params
     * @param string $requestCode
     * @return Result
     * @throws GuzzleException
     */
    public static function http(array $ips,string $uri,array $params=[],string $requestCode=''):Result
    {
        try {
            $ip=$ips[array_rand($ips)];
            if(strpos($ip,'http')===false)
            {
                $ip='http://'.$ip;
            }
            $response=(new Client())->post($ip.$uri,['form_params'=>$params,'headers'=>['request-code'=>$requestCode]]);

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