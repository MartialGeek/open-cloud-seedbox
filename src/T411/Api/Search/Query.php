<?php

namespace Martial\Warez\T411\Api\Search;


class Query implements QueryInterface
{
    /**
     * @var string
     */
    private $terms;

    /**
     * @var int
     */
    private $categoryId;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var int
     */
    private $limit;

    /**
     * Returns the string representation of the query.
     *
     * @return string
     */
    public function build()
    {
        $query = str_replace(' ', '.', strtolower($this->terms));

        if (!is_null($this->categoryId)) {
            $query .= '&cid=' . $this->categoryId;
        }

        if (!is_null($this->offset)) {
            $query .= '&offset=' . $this->offset;
        }

        if (!is_null($this->limit)) {
            $query .= '&limit=' . $this->limit;
        }

        return $query;
    }

    /**
     * Defines the terms of the query.
     *
     * @param string $terms
     * @return QueryInterface
     */
    public function setTerms($terms)
    {
        $this->terms = $terms;

        return $this;
    }

    /**
     * Defines the ID of the category.
     *
     * @param int $id
     * @return QueryInterface
     */
    public function setCategoryId($id)
    {
        $this->categoryId = $id;

        return $this;
    }

    /**
     * Defines the offset of the query.
     *
     * @param int $offset
     * @return QueryInterface
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Defines the limit of the query results.
     *
     * @param int $limit
     * @return QueryInterface
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }
}
