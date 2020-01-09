<?php


use Nivekaa\Reponse\ResponseBody;

class PostTest extends \PHPUnit\Framework\TestCase
{
    private  $httpClient;
    public  function setUp(): void
    {
        $this->httpClient = new \Nivekaa\Curly\Client("https://jsonplaceholder.typicode.com",false);
    }

    public function tearDown(): void {
        $this->httpClient = null;
    }

    public function testPost(){
        $res = $this->httpClient->post("/posts",[
            "title" => 'foo',
            "body" => 'bar',
            "userId" =>1
        ]);
        $this->assertEquals($res->getStatus(), ResponseBody::SUCCESS);
        $this->assertIsArray($res->toArray());
        $this->assertEquals(101, $res->toArray()["data"]["id"]);
    }
}
