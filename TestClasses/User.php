<?php
namespace Crada\Apidoc\TestClasses;

class User
{
    /**
     * @ApiDescription(section="User", description="Get information about user")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/user/get/{id}")
     * @ApiParams(name="id", type="integer", nullable=false, description="User id")
     */
    public function get()
    {

    }

    /**
     * @ApiDescription(section="User", description="Create's a new user")
     * @ApiMethod(type="post", route="/user/create")
     * @ApiRoute(name="/user/create")
     * @ApiParams(name="username", type="string", nullable=false, description="Username")
     * @ApiParams(name="email", type="string", nullable=false, description="Email")
     * @ApiParams(name="password", type="string", nullable=false, description="Password")
     * @ApiParams(name="age", type="integer", nullable=true, description="Age")
     */
    public function create()
    {

    }

    /**
     * @ApiDescription(section="User", description="Delete a user")
     * @ApiMethod(type="delete")
     * @ApiRoute(name="/user/delete")
     * @ApiParams(name="id", type="integer", nullable=false, description="User id")
     */
    public function delete()
    {

    }

    /**
     * @ApiDescription(section="User", description="Delete a user")
     * @ApiMethod(type="put")
     * @ApiRoute(name="/user/update")
     * @ApiParams(name="id", type="integer", nullable=false, description="User id")
     */
    public function update()
    {

    }
}
