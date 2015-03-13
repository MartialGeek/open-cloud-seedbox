<?php

namespace Martial\Warez\T411\Api\Search;


class QueryFactory implements QueryFactoryInterface
{
    /**
     * Creates a query from the given parameters. Supported parameters are:
     * <ul>
     * <li>terms</li>
     * <li>category_id</li>
     * <li>offset</li>
     * <li>limit</li>
     * </ul>
     *
     * @param array $params
     * @return QueryInterface
     */
    public function create(array $params)
    {
        $categoryId = isset($params['category_id']) ? $params['category_id'] : null;
        $offset = isset($params['offset']) ? $params['offset'] : null;
        $limit = isset($params['limit']) ? $params['limit'] : null;

        $query = new Query($params);
        $query
            ->setTerms($params['terms'])
            ->setCategoryId($categoryId)
            ->setOffset($offset)
            ->setLimit($limit);

        return $query;
    }
}
