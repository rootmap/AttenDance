<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
//For PhpMailer
use PHPMailerAutoload;
use PHPMailer;

class EmailController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //public function index() {
        // $company = Company::all();
        //
        // $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        // return view('module.settings.leavePolicy', ['company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
    //}

    /**
    *For Sending email with PhpMailer
    */
    public function sendMail($email_to, $msg_subject, $msg_body, $full_name) {
      $mail = new PHPMailer;

      // notice the \ you have to use root namespace here
      try {
        $mail->isSMTP(); // tell to use smtp
        $mail->CharSet = 'utf-8'; // set charset to utf8
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587; // most likely something different for you. This is the mailtrap.io port i use for testing.
        $mail->Username = "Bd.hrms@r-pac.co";
        $mail->Password = "Hrms1122!";
        $mail->setFrom('Bd.hrms@r-pac.co', 'BD-HRMS');
        $mail->Subject = $msg_subject;
        $mail->MsgHTML($msg_body);
        $mail->addAddress($email_to, $full_name);
        //$mail->addReplyTo("noreply@systechunimax.com", "SystechUnimaxLtd.");
        $mail->addBCC("fahad@systechunimax.com");
        //$mail->addAttachment(‘/home/kundan/Desktop/abc.doc’, ‘abc.doc’); // Optional name
        $mail->AltBody = 'This is a plain-text message body';
        $mail->SMTPOptions= array(
          "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
            "allow_self_signed" => true
            )
        );

        $mail->send();
      } catch (phpmailerException $e) {
        dd($e);
		exit();
        return $e;
      } catch (Exception $e) {
        //dd($e);
        return $e;
      }
        //dd('success');
        return 1;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
      //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\LeavePolicy  $leavePolicy
     * @return \Illuminate\Http\Response
     */
    public function show() {
      //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LeavePolicy  $leavePolicy
     * @return \Illuminate\Http\Response
     */
    public function edit() {
      //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LeavePolicy  $leavePolicy
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request) {
      //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LeavePolicy  $leavePolicy
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request) {
      //
    }

}
