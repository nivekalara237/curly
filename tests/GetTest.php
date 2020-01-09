<?php

use Nivekaa\Reponse\ResponseBody;
use PHPUnit\Framework\TestCase;

final class GetTest extends TestCase{

    private  $httpClient;
    public  function setUp(): void
    {
        $this->httpClient = new \Nivekaa\Curly\Client("https://jsonplaceholder.typicode.com",false);
    }

    public function tearDown(): void {
        $this->httpClient = null;
    }


    public function testGet(){
        $res = $this->httpClient->get("/photos");
        $this->assertEquals($res->getStatus(), ResponseBody::SUCCESS);
        $this->assertEquals($res->getCode(), 201);
        $this->assertEquals(count($res->getData()), 5000);
    }

    public function testAnotherGet(){
        $res = $this->httpClient->get("/posts_sdsdf");
        $this->assertEquals($res->getStatus(), ResponseBody::ERROR);
    }

    public function testCaching(){
        $res = $this->httpClient
            ->caching(true,"my-dir-cache")
            ->get("/photos");
        $this->assertEquals($res->getStatus(), ResponseBody::SUCCESS);
        $res = $this->httpClient->get("/photos");
        $this->assertEquals($res->getStatus(), ResponseBody::SUCCESS);
        $this->httpClient->caching(false);
    }
}
