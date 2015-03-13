<?php

namespace Martial\Warez\T411\Api\Search;


interface QueryFactoryInterface
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
    public function create(array $params);
}
