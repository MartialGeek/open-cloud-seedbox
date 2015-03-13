<?php

namespace Martial\Warez\Tests\T411\Api\Search;

use Martial\Warez\T411\Api\Search\Query;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildQuery()
    {
        $this->buildQuery('What an awesome movie', 12, 2, 30);
    }

    public function testBuildQueryWithOnlyTerms()
    {
        $this->buildQuery('What an awesome movie');
    }

    protected function buildQuery($terms, $categoryId = null, $offset = null, $limit = null)
    {
        $expectedQuery = urlencode(strtolower($terms));

        if (!is_null($categoryId)) {
            $expectedQuery .= '&cid=' . $categoryId;
        }

        if (!is_null($offset)) {
            $expectedQuery .= '&offset=' . $offset;
        }

        if (!is_null($limit)) {
            $expectedQuery .= '&limit=' . $limit;
        }

        $query = new Query();

        $query
            ->setTerms($terms)
            ->setCategoryId($categoryId)
            ->setOffset($offset)
            ->setLimit($limit);

        $this->assertSame($expectedQuery, $query->build());
    }
}
