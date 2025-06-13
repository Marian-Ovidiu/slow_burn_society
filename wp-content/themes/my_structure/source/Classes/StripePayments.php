<?php
namespace Classes;

use Classes\GrazieEmail;
use Models\Progetto;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripePayments
{
    public static function createIntent()
    {
        Stripe::setApiKey(my_env('SECRET_KEY'));
        $data         = json_decode(file_get_contents("php://input"), true);
        $amount       = isset($data['amount']) ? (int) $data['amount'] : 0;
        $progetto_id  = $data['progetto_id'] ?? null;
        $progetto     = Progetto::find($progetto_id);
        $progettoName = $progetto ? "Donazione per il progetto: " . $progetto->title : "Donazione generica";

        error_log("[createIntent] Ricevuto importo: {$amount}, progetto_id: {$progetto_id}");

        try {
            $paymentIntent = PaymentIntent::create([
                'amount'                    => $amount,
                'currency'                  => 'eur',
                'automatic_payment_methods' => ['enabled' => true],
                'description'               => $progettoName,
            ]);
            error_log("🔍 Status PaymentIntent: " . $paymentIntent->status);

            error_log("[createIntent] PaymentIntent creato con ID: " . $paymentIntent->id);
            wp_send_json_success(['clientSecret' => $paymentIntent->client_secret]);
        } catch (\Exception $e) {
            error_log("[createIntent] Stripe error: " . $e->getMessage());
            wp_send_json_error(['message' => 'Errore nella creazione del pagamento']);
        }
    }

    public static function completePayment()
    {
        $data   = json_decode(file_get_contents("php://input"), true);
        $amount = isset($data['amount']) ? (int) $data['amount'] : 0;
        $email  = $data['email'] ?? null;

        error_log("[completePayment] Email ricevuta: " . print_r($email, true));
        error_log("[completePayment] Dati ricevuti: " . print_r($data, true));

        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            error_log("[completePayment] Email non valida: {$email}");
            wp_send_json_error(['message' => 'Indirizzo email non valido.']);
            return;
        }

        Stripe::setApiKey(my_env('SECRET_KEY'));

        try {
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount'              => $amount * 100,
                'currency'            => 'eur',
                'payment_method'      => ['card', 'paypal'],
                'confirmation_method' => 'manual',
                'confirm'             => true,
                'return_url'          => 'https://project-africa-conservation.org',
            ]);
            error_log("🔍 Status PaymentIntent: " . $paymentIntent->status);

            error_log("[completePayment] PaymentIntent ID: " . $paymentIntent->id);
            error_log("[completePayment] Status PaymentIntent: " . $paymentIntent->status);

            if ($paymentIntent->status === 'succeeded') {
            // if (true) {
                $progetto_id  = $data['progettoId'] ?? null;
                $progetto     = Progetto::find($progetto_id);
                $progettoName = $progetto ? "Donazione per il progetto: " . htmlspecialchars($progetto->title, ENT_QUOTES, 'UTF-8') : "Donazione generica";

                error_log("[completePayment] Invio email a: {$email}");
                GrazieEmail::sendThankYouEmail($email, $progettoName, $amount);

                if (! email_exists($email)) {
                    error_log("[completePayment] Creazione nuovo utente: {$email}");
                    self::createUser($data);
                }

                wp_send_json_success([
                    'success'  => true,
                    'redirect' => 'progetto/',
                    'message'  => 'Ordine completato con successo.',
                ]);
            } else {
                error_log("[completePayment] Pagamento non riuscito. Status: " . $paymentIntent->status);
                wp_send_json_error(['message' => 'Il pagamento non è riuscito.']);
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log("[completePayment] Errore di pagamento: " . $e->getMessage());
            wp_send_json_error(['message' => 'Errore di pagamento: ' . $e->getMessage()]);
        }
    }

    public static function createUser($data)
    {
        $email   = $data['email'];
        $user_id = email_exists($email);

        if (! $user_id) {
            $user_id = wp_insert_user([
                'user_login' => $email,
                'user_pass'  => wp_generate_password(),
                'user_email' => $email,
                'first_name' => $data['name'],
                'last_name'  => $data['surname'],
                'role'       => 'donator',
            ]);
            error_log("[createUser] Utente creato con ID: {$user_id}");
        } else {
            error_log("[createUser] L'utente esiste già con ID: {$user_id}");
        }

        update_user_meta($user_id, 'telefono', $data['phone'] ?? '');
        update_user_meta($user_id, 'codice_fiscale', $data['codiceFiscale'] ?? '');
        update_user_meta($user_id, 'importo_donato', $data['amount']);
        update_user_meta($user_id, 'title', $data['progettoId']);
        update_user_meta($user_id, 'name', $data['name']);
    }
}
