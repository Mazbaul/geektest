@extends('emails.layout')

@section('content')

<p>Hi {{$receiver->name}},</p>
<p>You have received <strong>{{number_format($transaction->receiving_amount, 2) . ' ' . $transaction->receiver_currency}}</strong> from <strong>{{$sender->name}}.</strong> Your current wallet balance is <strong>{{number_format($receiver->wallet, 2) . ' ' . $receiver->currency}}.</strong></p>
<p>
    Sender: <code>{{$sender->name}}</code>
    <br>
    Receiving Amount: <code>{{number_format($transaction->receiving_amount, 2) . ' ' . $transaction->receiver_currency}}</code>
    <br>
    Transaction Time: <code>{{(new DateTime($transaction->transaction_at))->format('d/m/Y H:i:s')}}</code>
    <br>
</p>
<p>Thanks to stay with us. If you have any query or doubt, <strong>Please contact our support immediately.</strong></p>
<p>
    With love,<br>
    The {{config('app.name')}} Team
</p>

@endsection()