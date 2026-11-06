<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;

class MailController extends Controller
{
    public function sendEmail()
    {
        $name = "Daffa";
        $emailTujuan = "user@example.com";

        Mail::to($emailTujuan)->send(new WelcomeMail($name));

        return "Email berhasil dikirim! Silakan cek log di storage/logs/laravel.log";
    }
}