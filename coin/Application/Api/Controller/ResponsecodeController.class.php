<?php


namespace Api\Controller;


class ResponsecodeController
{
    //成功
    const SUCCESS = 200;
    //系统code
    const INVALID_PARAMS = 400; // 参数验证失败
    const DB_ERROR = 302;   //数据库错误

    const PART_BET_FAILED = 101;

    //通用code
    const GENERAL_EXCEPTION_CODE = 500; //服务端发生异常错误码
    const NOT_FOUND = 404;
    const UNAUTHORIZED = 401; //登录认证错误
    const METHOD_NOT_ALLOWED = 405;
    //系统正在维护
    const SYSTEM_IS_CLOSED = 900;
    //系统正在维护

}