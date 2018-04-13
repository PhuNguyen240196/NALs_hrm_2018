<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 4/12/2018
 * Time: 1:36 PM
 */

namespace App\Models;


use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class Employee extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

<<<<<<< HEAD
=======

>>>>>>> f6c1705755d820741a6617d4bb95356d29bfe2b5
    protected $fillable = [
        'name',
        'email',
        'password',
        'birthday',
        'gender',
        'mobile',
        'address',
        'marital_status',
        'startwork_date',
        'endwork_date',
        'curriculum_vitae',
        'is_employee',
        'company',
        'avatar'
    ];


    protected $hidden = [
        'password',
    ];
}