@push('scripts')

    <script>
        $(document).ready(function () {

            jQuery('#utshob-pay').bind('paymentProcessStart', function (e) {
                jQuery("#place-order-button").trigger('paymentProcessEnd');
            });
        });

    </script>
@endpush