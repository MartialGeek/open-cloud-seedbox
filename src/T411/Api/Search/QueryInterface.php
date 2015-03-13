<?php

namespace Martial\Warez\T411\Api\Search;


interface QueryInterface
{
    /**
     * Returns the string representation of the query.
     *
     * @return string
     */
    public function build();

    /**
     * Defines the terms of the query.
     *
     * @param string $terms
     * @return QueryInterface
     */
    public function setTerms($terms);

    /**
     * Defines the ID of the category.
     *
     * @param int $id
     * @return QueryInterface
     */
    public function setCategoryId($id);

    /**
     * Defines the offset of the query.
     *
     * @param int $offset
     * @return QueryInterface
     */
    public function setOffset($offset);

    /**
     * Defines the limit of the query results.
     *
     * @param int $limit
     * @return QueryInterface
     */
    public function setLimit($limit);
}
