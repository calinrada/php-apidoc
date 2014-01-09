<?php

namespace Crada\Apidoc\TestClasses;

class Article
{
    /**
     * @ApiDescription(section="Article", description="Get article")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/article/get/{id}")
     * @ApiParams(name="id", type="integer", nullable=false, description="Article id")
     */
    public function get()
    {

    }

    /**
     * @ApiDescription(section="Article", description="Create a new article")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/article/create")
     * @ApiParams(name="title", type="string", nullable=false, description="Article title")
     * @ApiParams(name="content", type="string", nullable=false, description="Article content")
     * @ApiParams(name="user_id", type="integer", nullable=false, description="User id")
     */
    public function create()
    {

    }

    /**
     * @ApiDescription(section="Article", description="Delete article")
     * @ApiMethod(type="delete")
     * @ApiRoute(name="/article/delete")
     * @ApiParams(name="id", type="integer", nullable=false, description="Article id")
     */
    public function delete()
    {

    }

    /**
     * @ApiDescription(section="Article", description="Updates an article")
     * @ApiMethod(type="put")
     * @ApiRoute(name="/article/update")
     * @ApiParams(name="id", type="integer", nullable=false, description="Article id")
     */
    public function update()
    {

    }
}
