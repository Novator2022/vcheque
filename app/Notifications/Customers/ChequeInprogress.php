<?php

namespace App\Notifications\Customers;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use App\Cheque;


class ChequeInprogress extends Notification  implements ShouldQueue
{
    use Queueable;

    protected $data;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Cheque $data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Статус чека.')
                    ->line('Ваш чек №' . $this->data->doc_num . ' отпечатан.')
                    ->line('Спасибо, что пользуетесь нашим сервисом!')
                    ->greeting('Здавстсвуйте! ' . ($this->data->user->name ?? ''))
                    ->salutation('C Уважением, Команда "Чеки"');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
