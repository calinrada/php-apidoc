<?php
namespace Crada\Apidoc\TestClasses;

class User
{
    /**
     * @ApiDescription(section="User", description="Get information about user")
     * @ApiMethod(type="get", route="/user/get/{id}")
     * @ApiParams(name="id", type="integer", nullable=false, description="User id")
     */
    public function get()
    {

    }

    /**
     * @ApiDescription(section="User", description="Create's a new user")
     * @ApiMethod(type="post", route="/user/create")
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
     * @ApiMethod(type="delete", route="/user/delete")
     * @ApiParams(name="id", type="integer", nullable=false, description="User id")
     */
    public function delete()
    {

    }

    /**
     * @ApiDescription(section="User", description="Delete a user")
     * @ApiMethod(type="put", route="/user/update")
     * @ApiParams(name="id", type="integer", nullable=false, description="User id")
     */
    public function update()
    {

    }
}
