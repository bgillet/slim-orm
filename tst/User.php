<?php

/**
 * User model test class for SlimORM.
 * Expect a table named 'user' in your database with a column 'id' and
 * a column 'username'.
 */
class User extends \Model
{
    /**
     * Force Paris using only classname without namespace to determine table
     * name.
     */
    public $_table_use_short_name = true;
}
