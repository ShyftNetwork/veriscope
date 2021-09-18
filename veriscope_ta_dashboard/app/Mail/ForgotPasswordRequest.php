<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Constant;
use Illuminate\Support\Facades\Log;
use Sichikawa\LaravelSendgridDriver\SendGrid;
use App\Exceptions\SendgridDisabledException;


class ForgotPasswordRequest extends Mailable
{
    use Queueable, SerializesModels, SendGrid;

    protected $user;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        // $url = env('APP_URL').'/auth/password/set/'.$this->user->remember_token;
        // $toEmail = $this->user->email;
        // Log::info('Transition not allowed for state: ');
        // Log::info($url);
        // return $this->view('grant-access-request')->to($toEmail)->with([
        //                 'url' => $url
        //             ]);;
        
        $sendgrid = Constant::where('name', 'sendgrid')->first();
        if($sendgrid->value == 0){
            // fail the job
            throw new SendgridDisabledException('Sendgrid Disabled');
        }

        $url = config('shyft.url').'/auth/password/set/'.$this->user->remember_token;

        $toEmail = $this->user->email;
        // override if mail traping set
        if(config('mail.trap') !== '') $toEmail = config('mail.trap');

        return $this
            ->view('emails.dummy')
            ->to($toEmail)
            ->with(['user' => $this->user])
            ->sendgrid([
                'template_id' => 'd-bf7da7fdf592465bbdaf56c2f930b34a',
                'personalizations' => [
                    [
                        'to' => [
                            'email' => $toEmail,
                            //'email' => 'shyfttest@mailinator.com',
                            'name' => $this->user->first_name . ' ' . $this->user->last_name,
                        ],
                        'dynamic_template_data' => [
                            'url' => $url,
                            'user' => $this->user,
                        ],
                    ],
                ],
            ]);
    }
}
