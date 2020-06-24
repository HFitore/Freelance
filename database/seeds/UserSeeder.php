<?php

use App\Role;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //admin
        $username = env("FIRST_ADMIN_USERNAME");
        $email = env("FIRST_ADMIN_EMAIL");
        $password = env("FIRST_ADMIN_PASSWORD");

        //freelancer
        $f_username = env("FREELANCER_USER_USERNAME");
        $f_email = env("FREELANCER_USER_EMAIL");
        $f_password = env("FREELANCER_USER_PASSWORD");

        //employer
        $e_username = env("EMPLOYER_USER_USERNAME");
        $e_email = env("EMPLOYER_USER_EMAIL");
        $e_password = env("EMPLOYER_USER_PASSWORD");

        $password_hashed = Hash::make($password);
        $freelancer_password_hashed = Hash::make($f_password);
        $employer_password_hashed = Hash::make($e_password);

        $adminRole = Role::find(1);
        $freelancerRole = Role::find(2);
        $employerRole = Role::find(3);

        $admin = User::create(['name' => 'admin', 'username' => $username, 'email' => $email, 'password' => $password_hashed, 'image' => 'noimage.jpg']);
        $freelancer = User::create(['name' => 'freelancer', 'username' => $f_username, 'email' => $f_email, 'password' => $freelancer_password_hashed, 'image' => 'noimage.jpg']);
        $employer = User::create(['name' => 'employer', 'username' => $e_username, 'email' => $e_email, 'password' => $employer_password_hashed, 'image' => 'noimage.jpg']);

        $adminRole->users()->save($admin);
        $freelancerRole->users()->save($freelancer);
        $employerRole->users()->save($employer);

    }
}
