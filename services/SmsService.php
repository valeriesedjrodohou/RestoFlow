<?php
/**
 * SmsService - Service d'envoi de SMS
 * ------------------------------
 * En mode SIMULATION (par défaut), les SMS sont enregistrés dans la table
 * `sms_logs` et écrits dans /assets/logs/sms.log pour vérification.
 *
 * Pour activer l'envoi réel de SMS en production :
 *   1. Créer un compte sur Twilio (https://twilio.com) ou Orange SMS API
 *      ou OVH (https://api.ovh.com)
 *   2. Renseigner l<?php
/** Admin — Journal SMS */
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <h1 class="h3 fw-bold">Journal des SMS</h1>
    <span class="badge bg-light text-dark border p-2 rounded-3">
        <i data-lucide="message-square" class="rf-icon"></i> <?= count($logs) ?> entrée(s)
    </span>
</div>

<?php if (empty($logs)): ?>
    <div class="rf-empty">
        <div class="rf-empty-icon"><i data-lucide="message-square"></i></div>
        <h2>Aucun SMS envoyé</h2>
        <p>Les SMS de notification apparaîtront ici après la première commande.</p>
    </div>
<?php else: ?>
    <div class="rf-table-card">
        <div class="table-responsive">
            <table class="rf-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Destinataire</th>
                        <th>Type</th>
                        <th>Statut</th>
                        <th>Message</th>
                        <th>Commande</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="fw-bold">#<?= (int)$log['id'] ?></td>
                            <td><small><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></small></td>
                            <td><code><?= e($log['phone']) ?></code></td>
                            <td>
                                <?php
                                $typeLabels = [
                                    'order_confirmation' => 'Confirmation',
                                    'status_update'      => 'Mise à jour',
                                    'admin_notification' => 'Notif. admin',
                                    'notification'       => 'Notification',
                                ];
                                $typeIcons = [
                                    'order_confirmation' => 'check-circle',
                                    'status_update'      => 'refresh-cw',
                                    'admin_notification' => 'megaphone',
                                    'notification'       => 'message-square',
                                ];
                                ?>
                                <i data-lucide="<?= $typeIcons[$log['type']] ?? 'message-square' ?>" class="rf-icon"></i>
                                <?= e($typeLabels[$log['type']] ?? $log['type']) ?>
                            </td>
                            <td>
                                <?php if ($log['status'] === 'sent'): ?>
                                    <span class="rf-tag rf-tag-success">Envoyé</span>
                                <?php elseif ($log['status'] === 'simulated'): ?>
                                    <span class="rf-tag rf-tag-info">Simulé</span>
                                <?php else: ?>
                                    <span class="rf-tag rf-tag-danger">Échec</span>
                                <?php endif; ?>
                            </td>
                            <td><small class="text-muted"><?= e($log['message']) ?></small></td>
                            <td>
                                <?php if ($log['order_id']): ?>
                                    <a href="<?= url('admin_orders') ?>" class="badge bg-primary-subtle text-primary">#<?= (int)$log['order_id'] ?></a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="rf-table-card mt-4" style="background: var(--primary-50); border-left: 4px solid var(--primary);">
        <h6 class="fw-bold mb-2"><i data-lucide="info" class="rf-icon"></i> À propos du mode simulation</h6>
        <p class="small text-muted mb-2">
            Par défaut, les SMS ne sont pas réellement envoyés. Ils sont enregistrés dans la base de données
            (table <code>sms_logs</code>) et dans le fichier <code>assets/logs/sms.log</code>.
        </p>
        <p class="small text-muted mb-0">
            Pour activer l'envoi réel, ouvrez <code>services/SmsService.php</code>, définissez
            <code>SMS_SIMULATION = false</code> et renseignez vos clés API Twilio.
        </p>
    </div>
<?php endif; ?>
es clés API ci-dessous
 *   3. Définir SMS_SIMULATION = false
 *   4. Implémenter l'appel API dans la méthode send()
 */

require_once __DIR__ . '/../config/database.php';

class SmsService
{
    // === Configuration (à remplacer par vos clés API réelles) ===
    const SMS_SIMULATION = true;   // false pour activer l'envoi réel
    const TWILIO_SID      = '';
    const TWILIO_TOKEN    = '';
    const TWILIO_FROM     = '';

    /**
     * Envoie un SMS à un numéro de téléphone.
     *
     * @param string $phone   Numéro du destinataire (format international ou local)
     * @param string $message Contenu du SMS
     * @param int|null $orderId  ID de commande associée (pour tracer)
     * @param string $type       Type de SMS (order_confirmation, status_update, etc.)
     * @return array Résultat ['success' => bool, 'message' => string, 'log_id' => int|null]
     */
    public static function send($phone, $message, $orderId = null, $type = 'notification')
    {
        $phone = self::normalizePhone($phone);

        // Validation basique
        if (!preg_match('/^\+?\d{8,15}$/', $phone)) {
            return [
                'success' => false,
                'message' => 'Numéro de téléphone invalide: ' . $phone,
                'log_id'  => null,
            ];
        }

        if (self::SMS_SIMULATION || !defined('CURL_HTTP_VERSION_2_0')) {
            // === MODE SIMULATION ===
            // On enregistre le SMS dans la base + un fichier log
            return self::logSms($phone, $message, $type, 'simulated', $orderId);
        }

        // === MODE RÉEL (Twilio) ===
        // Décommenter et configurer pour activer
        /*
        $url = 'https://api.twilio.com/2010-04-01/Accounts/' . self::TWILIO_SID . '/Messages.json';
        $auth = base64_encode(self::TWILIO_SID . ':' . self::TWILIO_TOKEN);
        $postData = http_build_query([
            'From' => self::TWILIO_FROM,
            'To'   => $phone,
            'Body' => $message,
        ]);
        $ctx = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Authorization: Basic $auth\r\n" .
                             "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $postData,
                'timeout' => 15,
            ],
        ]);
        $response = @file_get_contents($url, false, $ctx);
        $success = ($response !== false && strpos($http_response_header[0], '200') !== false);
        return self::logSms($phone, $message, $type, $success ? 'sent' : 'failed', $orderId, $response);
        */

        // Si on arrive ici, on bascule en simulation
        return self::logSms($phone, $message, $type, 'simulated', $orderId);
    }

    /**
     * Envoie une notification de confirmation de commande au client
     */
    public static function sendOrderConfirmation($order)
    {
        $message = sprintf(
            "%s: Commande #%d confirmee! Total: %s. Livraison estimee: 30-45 min. Adresse: %s. Merci!",
            SMS_SENDER,
            $order['id'],
            format_price($order['total_amount']),
            mb_substr($order['delivery_address'], 0, 40)
        );
        // Tronquer à 160 caractères (limite SMS standard)
        if (mb_strlen($message) > 160) {
            $message = mb_substr($message, 0, 157) . '...';
        }
        return self::send($order['customer_phone'], $message, $order['id'], 'order_confirmation');
    }

    /**
     * Envoie une notification de mise à jour de statut au client
     */
    public static function sendStatusUpdate($order, $newStatusLabel)
    {
        $messages = [
            'preparing'  => sprintf("%s: Votre commande #%d est en preparation. A bientot!", SMS_SENDER, $order['id']),
            'delivering' => sprintf("%s: Votre commande #%d est en route! Livraison imminente.", SMS_SENDER, $order['id']),
            'delivered'  => sprintf("%s: Votre commande #%d a ete livree. Bon appetit! Notez-nous.", SMS_SENDER, $order['id']),
            'cancelled'  => sprintf("%s: Votre commande #%d a ete annulee. Contactez-nous pour plus d'infos.", SMS_SENDER, $order['id']),
        ];
        $msg = $messages[$newStatusLabel] ?? sprintf("%s: Mise a jour de votre commande #%d.", SMS_SENDER, $order['id']);
        return self::send($order['customer_phone'], $msg, $order['id'], 'status_update');
    }

    /**
     * Notifie l'administrateur d'une nouvelle commande
     */
    public static function notifyAdminNewOrder($order)
    {
        $msg = sprintf(
            "Nouvelle commande #%d - %s - Total: %s - Client: %s",
            $order['id'],
            $order['customer_name'],
            format_price($order['total_amount']),
            $order['customer_phone']
        );
        // Numéro admin (à configurer)
        $adminPhone = defined('ADMIN_PHONE') ? ADMIN_PHONE : '+22500000000';
        return self::send($adminPhone, $msg, $order['id'], 'admin_notification');
    }

    // ============================================================
    // Méthodes privées
    // ============================================================

    /** Normalise un numéro de téléphone (format simple) */
    private static function normalizePhone($phone)
    {
        $phone = preg_replace('/[\s\-\.\(\)]/', '', $phone);
        if (strpos($phone, '+') !== 0) {
            // Si numéro local ivoirien à 10 chiffres (07XXXXXXXX), préfixer +225
            if (preg_match('/^0\d{9}$/', $phone)) {
                $phone = '+225' . substr($phone, 1);
            }
            // Si déjà 8-15 chiffres sans +, l'ajouter
            elseif (preg_match('/^\d{8,15}$/', $phone)) {
                $phone = '+' . $phone;
            }
        }
        return $phone;
    }

    /** Journalise un SMS dans la base + un fichier log */
    private static function logSms($phone, $message, $type, $status, $orderId, $apiResponse = null)
    {
        // Insertion en base
        $stmt = db()->prepare('
            INSERT INTO sms_logs (phone, message, type, status, order_id)
            VALUES (?, ?, ?, ?, ?)
        ');
        $stmt->execute([$phone, $message, $type, $status, $orderId]);
        $logId = db()->lastInsertId();

        // Écriture dans fichier log
        $logLine = sprintf(
            "[%s] [%s] ID=%d | To: %s | Type: %s | Order: %s\n    Message: %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($status),
            $logId,
            $phone,
            $type,
            $orderId ?? '-',
            $message
        );
        $logFile = __DIR__ . '/../assets/logs/sms.log';
        @file_put_contents($logFile, $logFile ? $logLine . "\n" : $logLine, FILE_APPEND);

        return [
            'success' => ($status === 'sent' || $status === 'simulated'),
            'message' => $status === 'simulated'
                ? 'SMS simulé (enregistré dans la base et le log)'
                : 'SMS envoyé avec succès',
            'log_id'  => $logId,
            'status'  => $status,
        ];
    }
}
