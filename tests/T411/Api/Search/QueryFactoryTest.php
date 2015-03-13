<?php

namespace Martial\Warez\Tests\T411\Api\Search;


use Martial\Warez\T411\Api\Search\QueryFactory;

class QueryFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testQueryFactory()
    {
        $params = [
            'terms' => 'What an awesome movie',
            'category_id' => 12,
            'offset' => 2,
            'limit' => 20
        ];

        $factory = new QueryFactory();
        $expectedQueryString = urlencode(strtolower($params['terms'])) .
            '&cid=' . $params['category_id'] . '&offset=' . $params['offset'] . '&limit=' . $params['limit'];

        $query = $factory->create($params);
        $this->assertInstanceOf('\Martial\Warez\T411\Api\Search\QueryInterface', $query);
        $this->assertSame($expectedQueryString, $query->build());
    }
}
