<table style="width: 100%">
    <tbody>
        <tr>
            <td>Payment Method:</td>
            <td style="text-align: right">{{ $paymentMethodString }}</td>
        </tr>
        <tr>
            <td>Registration Subtotal:</td>
            <td style="text-align: right">${{ number_format($breakdown['subtotal'] + (isset($breakdown['coupon']) && $breakdown['coupon'] > 0 ? $breakdown['coupon'] : 0 ),2) }}</td>
        </tr>
        @if (isset($breakdown['deposit_subtotal']) && $breakdown['deposit_subtotal'] > 0)
            <tr>
                <td>Deposit :</td>
                <td style="text-align: right">${{ number_format($breakdown['deposit_subtotal'], 2) }}</td>
            </tr>
        @endif
        @if (isset($breakdown['coupon']) && $breakdown['coupon'] > 0)
            <tr>
                <td>Used Coupon :</td>
                <td style="text-align: right">${{ number_format($breakdown['coupon'], 2) }}</td>
            </tr>
        @endif

        @if ($breakdown['own_meet_refund'] > 0)
            <tr>
                <td>Own Meet Refund :</td>
                <td style="text-align: right">- ${{ number_format($breakdown['own_meet_refund'], 2) }}</td>
            </tr>
        @endif

        @if ($breakdown['handling'] > 0 && $breakdown['deposit_handling'] == 0)
            <tr>
                <td>Handling Fee:</td>
                <td style="text-align: right">${{ number_format($breakdown['handling'], 2) }}</td>
            </tr>
        @else
            <tr>
                <td>Handling Fee:</td>
                <td style="text-align: right">${{ number_format($breakdown['deposit_handling'], 2) }}</td>
            </tr>
        @endif

        @if ($breakdown['used_balance'] > 0)
            <tr>
                <td>Used Balance:</td>
                <td style="text-align: right">${{ number_format(-$breakdown['used_balance'], 2) }}</td>
            </tr>
        @endif

        @if ($breakdown['processor'] > 0)
            <tr>
                <td>Payment Processor Fee:</td>
                <td style="text-align: right">${{ number_format($breakdown['processor'], 2) }}</td>
            </tr>
        @endif

        <tr>        
            <td><strong>Total:</strong></td>
            <td style="text-align: right"><strong>${{ number_format($breakdown['total'], 2) }}</strong></td>
        </tr>
        @if($breakdown['deposit_total'] > 0)
        <tr>        
            <td><strong>Deposit Total:</strong></td>
            <td style="text-align: right"><strong>${{ number_format($breakdown['deposit_total'], 2) }}</strong></td>
        </tr>
        @endif
    </tbody>
</table>