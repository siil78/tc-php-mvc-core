<?php

namespace siil78\phpmvc;

use siil78\phpmvc\db\DbModel;

abstract class UserModel extends DbModel
{

    abstract public function getDisplayName(): string;
}