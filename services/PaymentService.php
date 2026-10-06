<?php
/**
 * PaymentService - Gestion des paiements en ligne
 * ----------------------------------------------
 * Supporte 5 méthodes de paiement :
 *   - card             : Carte bancaire (Visa/Mastercard)
 *   - orange_money     : Orange Money
 *   - mtn_money        : MTN Mobile Money
 *   - wave             : Wave
 *   - cash_on_delivery : Paiement à la livraison
 *
 * En MODE SIMULATION (par défaut), aucun paiement réel n'est effectué.
 * Les transactions sont enregistrées dans la table `payments` pour audit.
 *
 * Pour activer de vrais paiements en production :
 *   - Carte bancaire : Stripe (https://stripe.com) ou Paystack
 *   - Mobile Money : CinetPay (https://cinetpay.com) ou Orange Money API
 *   - Wave : Wave Business API
 */

require_once __DIR__ . '/../config/database.php';

class PaymentService
{
    const SIMULATION = true;  // false pour activer les paiements réels

    // Clés API (à remplir pour la production)
    const STRIPE_SECRET_KEY  = '';
    const CINETPAY_API_KEY   = '';
    const CINETPAY_SITE_ID   = '';
    const ORANGE_OM_API_KEY  = '';

    /** Liste des méthodes de paiement disponibles */
    public static function methods()
    {
        return [
            'card' => [
                'label'  => 'Carte bancaire',
                'icon'   => '💳',
                'description' => 'Visa, Mastercard',
                'fields' => ['card_number', 'card_exp', 'card_cvc', 'card_name'],
            ],
            'orange_money' => [
                'label'  => 'Orange Money',
                'icon'   => '🟠',
                'description' => 'Paiement via Orange Money',
                'fields' => ['om_number'],
            ],
            'mtn_money' => [
                'label'  => 'MTN MoMo',
                'icon'   => '🟡',
                'description' => 'MTN Mobile Money',
                'fields' => ['mtn_number'],
            ],
            'wave' => [
                'label'  => 'Wave',
                'icon'   => '🔵',
                'description' => 'Paiement via Wave',
                'fields' => ['wave_number'],
            ],
            'cash_on_delivery' => [
                'label'  => 'Paiement à la livraison',
                'icon'   => '💵',
                'description' => 'Payez en espèces à la réception',
                'fields' => [],
            ],
        ];
    }

    /**
     * Traite un paiement pour une commande
     *
     * @param array $order     La commande à payer
     * @param string $method   Méthode de paiement
     * @param array $data      Données du formulaire (numéro carte, téléphone, etc.)
     * @return array Résultat ['success' => bool, 'transaction_id' => string, 'message' => string]
     */
    public static function process($order, $method, $data)
    {
        $methods = self::methods();
        if (!isset($methods[$method])) {
            return [
                'success'        => false,
                'transaction_id' => null,
                'message'        => 'Méthode de paiement invalide.',
            ];
        }

        // Validation des champs selon la méthode
        $validation = self::validate($method, $data);
        if (!$validation['success']) {
            // Enregistrer l'échec
            self::logPayment($order['id'], $method, $order['total_amount'], 'failed', null, null, $validation['message']);
            return $validation;
        }

        // Cash on delivery : pas de traitement, juste marquer comme "à payer à la livraison"
        if ($method === 'cash_on_delivery') {
            $txId = 'COD-' . $order['id'] . '-' . date('Ymd');
            self::logPayment($order['id'], $method, $order['total_amount'], 'success', $txId, null, null);
            return [
                'success'        => true,
                'transaction_id' => $txId,
                'message'        => 'Paiement à la livraison confirmé. Préparez le montant exact.',
            ];
        }

        // Si mode simulation
        if (self::SIMULATION) {
            // Simuler un léger délai de traitement
            usleep(800000);  // 0.8s

            // Générer un ID de transaction réaliste
            $txId = self::generateTransactionId($method);
            $providerRef = self::generateProviderRef($method);

            // Données spécifiques selon méthode
            $cardLast4 = null;
            $phone = null;
            if ($method === 'card' && !empty($data['card_number'])) {
                $cardLast4 = substr(preg_replace('/\s+/', '', $data['card_number']), -4);
            }
            if (in_array($method, ['orange_money', 'mtn_money', 'wave'])) {
                $phone = $data[$method . '_number'] ?? null;
            }

            self::logPayment($order['id'], $method, $order['total_amount'], 'success', $txId, $providerRef, null, $phone, $cardLast4);

            return [
                'success'        => true,
                'transaction_id' => $txId,
                'provider_ref'   => $providerRef,
                'message'        => self::successMessage($method, $txId),
            ];
        }

        // === PAIEMENT RÉEL ===
        // Décommenter et implémenter selon le prestataire choisi
        /*
        switch ($method) {
            case 'card':
                return self::processStripe($order, $data);
            case 'orange_money':
            case 'mtn_money':
                return self::processCinetPay($order, $data, $method);
            case 'wave':
                return self::processWave($order, $data);
        }
        */

        return [
            'success'        => false,
            'transaction_id' => null,
            'message'        => 'Paiements réels non configurés. Activez SIMULATION=true ou configurez les API.',
        ];
    }

    /**
     * Valide les données de paiement selon la méthode
     */
    public static function validate($method, $data)
    {
        if ($method === 'cash_on_delivery') {
            return ['success' => true, 'message' => ''];
        }

        if ($method === 'card') {
            $number = preg_replace('/\s+/', '', $data['card_number'] ?? '');
            $exp    = trim($data['card_exp'] ?? '');
            $cvc    = trim($data['card_cvc'] ?? '');
            $name   = trim($data['card_name'] ?? '');

            if (strlen($number) < 13 || strlen($number) > 19 || !ctype_digit($number)) {
                return ['success' => false, 'transaction_id' => null, 'message' => 'Numéro de carte invalide.'];
            }
            if (!preg_match('#^\d{2}/\d{2}$#', $exp)) {
                return ['success' => false, 'transaction_id' => null, 'message' => "Date d'expiration invalide (format MM/AA)."];
            }
            [$mm, $yy] = explode('/', $exp);
            $mm = (int)$mm; $yy = (int)$yy;
            if ($mm < 1 || $mm > 12) {
                return ['success' => false, 'transaction_id' => null, 'message' => "Mois d'expiration invalide."];
            }
            if ($yy < (int)date('y') || ($yy === (int)date('y') && $mm < (int)date('m'))) {
                return ['success' => false, 'transaction_id' => null, 'message' => 'La carte a expiré.'];
            }
            if (!ctype_digit($cvc) || strlen($cvc) < 3 || strlen($cvc) > 4) {
                return ['success' => false, 'transaction_id' => null, 'message' => 'Code CVC invalide.'];
            }
            if (strlen($name) < 3) {
                return ['success' => false, 'transaction_id' => null, 'message' => 'Nom du titulaire requis.'];
            }
            return ['success' => true, 'message' => ''];
        }

        if (in_array($method, ['orange_money', 'mtn_money', 'wave'])) {
            $phone = trim($data[$method . '_number'] ?? '');
            $phone = preg_replace('/[\s\-\.\(\)]/', '', $phone);
            if (!preg_match('/^\+?\d{8,15}$/', $phone)) {
                return ['success' => false, 'transaction_id' => null, 'message' => 'Numéro de téléphone invalide.'];
            }
            // Vérifier que le numéro commence par les bons préfixes (Côte d'Ivoire)
            $clean = ltrim($phone, '+');
            if ($method === 'orange_money' && !preg_match('/^(225)?07/', $clean)) {
                return ['success' => false, 'transaction_id' => null, 'message' => 'Orange Money nécessite un numéro Orange (07...).'];
            }
            if ($method === 'mtn_money' && !preg_match('/^(225)?05/', $clean)) {
                return ['success' => false, 'transaction_id' => null, 'message' => 'MTN MoMo nécessite un numéro MTN (05...).'];
            }
            if ($method === 'wave' && !preg_match('/^(225)?(01|05|07|27|39|77)/', $clean)) {
                return ['success' => false, 'transaction_id' => null, 'message' => 'Numéro Wave invalide.'];
            }
            return ['success' => true, 'message' => ''];
        }

        return ['success' => false, 'transaction_id' => null, 'message' => 'Méthode non supportée.'];
    }

    // ============================================================
    // Méthodes privées
    // ============================================================

    private static function generateTransactionId($method)
    {
        $prefix = [
            'card'         => 'CB',
            'orange_money' => 'OM',
            'mtn_money'    => 'MM',
            'wave'         => 'WV',
        ][$method] ?? 'TX';
        return $prefix . '-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
    }

    private static function generateProviderRef($method)
    {
        $providers = [
            'card'         => 'Stripe',
            'orange_money' => 'Orange Money CI',
            'mtn_money'    => 'MTN MoMo CI',
            'wave'         => 'Wave CI',
        ];
        return ($providers[$method] ?? 'Unknown') . ' #' . strtoupper(bin2hex(random_bytes(6)));
    }

    private static function successMessage($method, $txId)
    {
        $messages = [
            'card'         => 'Paiement carte bancaire accepté. Transaction: ' . $txId,
            'orange_money' => 'Paiement Orange Money confirmé. Référence: ' . $txId,
            'mtn_money'    => 'Paiement MTN MoMo confirmé. Référence: ' . $txId,
            'wave'         => 'Paiement Wave confirmé. Référence: ' . $txId,
        ];
        return $messages[$method] ?? 'Paiement confirmé. Référence: ' . $txId;
    }

    private static function logPayment($orderId, $method, $amount, $status, $txId = null, $providerRef = null, $error = null, $phone = null, $cardLast4 = null)
    {
        $stmt = db()->prepare('
            INSERT INTO payments (order_id, method, amount, status, transaction_id, provider_ref, phone, card_last4, error_message)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $orderId,
            $method,
            $amount,
            $status,
            $txId,
            $providerRef,
            $phone,
            $cardLast4,
            $error,
        ]);
    }
}
