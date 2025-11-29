<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ملف الإعدادات
$token = '8502513865:AAFHowxSJKFZFZel2a6wL_7DNcZpuXQ43Ss';
$admin_id = '1919956515';
$bot_status = 'on'; // حالة البوت (on/off)
$main_admin = '1919956515'; // الأدمن الأساسي (المالك)
$admin_users = "https://t.me/darkshotsy";

// تعريف الثوابت بمسارات مطلقة لضمان الحفظ والقراءة الصحيحة
define("BASE_DIR", __DIR__ . DIRECTORY_SEPARATOR); // المسار المطلق للمجلد الحالي
define("BALANCES_FILE", BASE_DIR . "balances.json");
define("STEPS_DIR", BASE_DIR . "steps" . DIRECTORY_SEPARATOR);
define("PRICES_FILE", BASE_DIR . "prices.json");
define("CASH_FILE", BASE_DIR . "cash.txt");
define("USERS_FILE", BASE_DIR . "users.json");
define("BANNED_FILE", BASE_DIR . "banned.json");
define("ADMINS_FILE", BASE_DIR . "admins.json");
define("FORCED_CHANNELS_FILE", BASE_DIR . "forced_channels.json");

/**
 * دالة آمنة لتهيئة الملفات والتأكد من صحة محتوى JSON.
 */
function safe_init_file($file, $default = []) {
    if (!file_exists($file)) {
        file_put_contents($file, json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    } else {
        $content = file_get_contents($file);
        json_decode($content);
        if (json_last_error() !== JSON_ERROR_NONE) {
            file_put_contents($file, json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
}

// تهيئة الملفات والمجلدات
if (!file_exists(STEPS_DIR)) {
    mkdir(STEPS_DIR, 0755, true);
}
if (!file_exists(BASE_DIR . "data_trans")) {
    mkdir(BASE_DIR . "data_trans", 0755, true);
}

safe_init_file(BALANCES_FILE, []);
safe_init_file(USERS_FILE, []);
safe_init_file(BANNED_FILE, []);

// التأكد من وجود ملف الأدمن وإضافة الأدمن الأساسي إذا لم يكن موجودًا
if (!file_exists(ADMINS_FILE)) {
    file_put_contents(ADMINS_FILE, json_encode([$admin_id], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
safe_init_file(FORCED_CHANNELS_FILE, []);

// تهيئة ملف الأسعار إذا لم يكن موجودًا
if (!file_exists(PRICES_FILE)) {
    $default_prices = [
        "💎 110" => 8700, "💎 330" => 25000,
        "💎 530" => 39000, "💎 1080" => 74000,
        "💎 2180" => 145000,
        "العضوية الأسبوعية" => 9000, "العضوية الشهرية" => 25000,
        "UC 60" => 8500, "UC 325" => 25000, "UC 660" => 45000,
        "UC 1800" => 120000, "UC 3850" => 235000, "UC 8100" => 460000
    ];
    file_put_contents(PRICES_FILE, json_encode($default_prices, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// تهيئة ملف الكاش إذا لم يكن موجودًا
if (!file_exists(CASH_FILE)) {
    file_put_contents(CASH_FILE, "62324913");
}

// تحميل البيانات مع معالجة الأخطاء
$balances = json_decode(file_get_contents(BALANCES_FILE), true) ?: [];
$prices = json_decode(file_get_contents(PRICES_FILE), true) ?: [];
$users = json_decode(file_get_contents(USERS_FILE), true) ?: [];
$banned = json_decode(file_get_contents(BANNED_FILE), true) ?: [];
$admins = json_decode(file_get_contents(ADMINS_FILE), true) ?: [];
$forced_channels = json_decode(file_get_contents(FORCED_CHANNELS_FILE), true) ?: [];

// استقبال التحديث من Telegram
$update = json_decode(file_get_contents("php://input"), true);

// التحقق مما إذا كان هناك تحديث لتجنب الأخطاء
if (empty($update)) {
    exit("Bot is working!");
}

$message = $update["message"] ?? null;
$callback = $update["callback_query"] ?? null;
$data = $callback["data"] ?? null;
$text = $message["text"] ?? null;
$cid = $message["chat"]["id"] ?? $callback["message"]["chat"]["id"] ?? null;
$uid = $message["from"]["id"] ?? $callback["from"]["id"] ?? null;

/**
 * دالة مركزية لحفظ البيانات في الملفات
 */
function save_data($type, $data) {
    $file_path = '';

    switch ($type) {
        case 'balances':
            $file_path = BALANCES_FILE;
            break;
        case 'users':
            $file_path = USERS_FILE;
            break;
        case 'banned':
            $file_path = BANNED_FILE;
            break;
        case 'admins':
            $file_path = ADMINS_FILE;
            break;
        case 'forced_channels':
            $file_path = FORCED_CHANNELS_FILE;
            break;
        case 'prices':
            $file_path = PRICES_FILE;
            break;
        case 'cash':
            $file_path = CASH_FILE;
            file_put_contents($file_path, $data);
            return true;
        default:
            return false;
    }

    file_put_contents($file_path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    return true;
}

// دالة التحقق من الأدمن الأساسي
function isMainAdmin($user_id) {
    global $main_admin;
    return $user_id == $main_admin;
}

// دالة التحقق من الاشتراك في القنوات الإجبارية
function checkChannelsSubscription($user_id) {
    global $forced_channels, $token;
    if (empty($forced_channels)) return true;
    
    foreach ($forced_channels as $channel) {
        $channel_id = str_replace('@', '', $channel['username']);
        $result = json_decode(file_get_contents("https://api.telegram.org/bot$token/getChatMember?chat_id=@$channel_id&user_id=$user_id"), true);
        
        if (!isset($result['result']['status']) || $result['result']['status'] == 'left' || $result['result']['status'] == 'kicked') {
            return false;
        }
    }
    return true;
}

// دالة إحصاءات البوت
function getBotStatistics() {
    global $users, $balances, $banned, $admins, $forced_channels;
    
    $total_users = count($users);
    $total_banned = count($banned);
    $total_admins = count($admins);
    $total_channels = count($forced_channels);
    
    $total_balance = 0;
    foreach ($balances as $user) {
        $total_balance += $user['balance'] ?? 0;
    }
    
    return [
        'users' => $total_users,
        'banned' => $total_banned,
        'admins' => $total_admins,
        'channels' => $total_channels,
        'balance' => $total_balance
    ];
}

function send($id, $text, $inline = false, $keys = null) {
    global $token;
    $d = ["chat_id" => $id, "text" => $text, "parse_mode" => "Markdown"];
    if ($keys) {
        $markup = $inline ?
        ["inline_keyboard" => $keys] : ["keyboard" => $keys, "resize_keyboard" => true];
        $d["reply_markup"] = json_encode($markup);
    }
    file_get_contents("https://api.telegram.org/bot$token/sendMessage?" . http_build_query($d));
}

function answer($cid, $text) {
    global $token;
    file_get_contents("https://api.telegram.org/bot$token/answerCallbackQuery?callback_query_id=$cid&text=" . urlencode($text));
}

function deleteMessage($chat_id, $message_id) {
    global $token;
    file_get_contents("https://api.telegram.org/bot$token/deleteMessage?chat_id=$chat_id&message_id=$message_id");
}

function saveStep($uid, $step) { 
    file_put_contents(STEPS_DIR . $uid, $step);
}

function getStep($uid) { 
    return file_exists(STEPS_DIR . $uid) ? file_get_contents(STEPS_DIR . $uid) : null;
}

function delStep($uid) { 
    if (file_exists(STEPS_DIR . $uid)) {
        unlink(STEPS_DIR . $uid);
    }
}

// ----------------------------------------------------
// بداية الدالة لمعالجة منطق التحديثات
function handle_update_logic($input_text, $input_data, $input_cid, $input_uid, $input_callback = null) {
    global $token, $admin_id, $bot_status, $main_admin, $admin_users;
    global $balances, $prices, $users, $banned, $admins, $forced_channels;
    
    $text = $input_text;
    $data = $input_data;
    $cid = $input_cid;
    $uid = $input_uid;
    $callback = $input_callback;

    // التحقق من حالة البوت
    if ($bot_status == 'off' && !in_array($uid, $admins)) {
        if ($text == '/start') {
            // السماح ببدء المحادثة حتى لو كان البوت متوقفاً
        } else {
            send($cid, "⚠️ البوت متوقف حاليًا للصيانة. سنعود قريبًا!", false, [
                [["text" => "🔄 تحديث", "callback_data" => "check_bot_status"]]
            ]);
            return;
        }
    }

    // التحقق من المستخدم المحظور
    if (in_array($uid, $banned)) {
        send($cid, "🚫 تم حظرك من استخدام البوت. للاستفسار راسل الدعم.");
        return; 
    }

    // التحقق من الاشتراك في القنوات الإجبارية عند /start
    if ($text == "/start" && !in_array($uid, $admins)) {
        if (!checkChannelsSubscription($uid)) {
            $channels_list = "";
            $buttons = [];
            foreach ($forced_channels as $channel) {
                $channels_list .= "- @{$channel['username']}\n";
                $buttons[] = [["text" => "انضمام إلى @{$channel['username']}", "url" => "https://t.me/{$channel['username']}"]];
            }
            
            $buttons[] = [["text" => "✅ تحقق من الاشتراك", "callback_data" => "check_subscription"]];
            send($cid, "📢 يرجى الاشتراك في القنوات التالية لاستخدام البوت:\n$channels_list", true, $buttons);
            return;
        }
    }

    // معالجة التحقق من الاشتراك
    if ($data == "check_subscription") {
        if (checkChannelsSubscription($uid)) {
            answer($callback["id"], "✅ تم التحقق من اشتراكك في جميع القنوات");
            deleteMessage($callback["message"]["chat"]["id"], $callback["message"]["message_id"]);
            handle_update_logic("/start", null, $cid, $uid);
            return;
        } else {
            answer($callback["id"], "❌ لم تشترك في جميع القنوات المطلوبة");
        }
    }

    // إنشاء سجل للمستخدم الجديد مع التحقق من الصلاحيات
    if (!isset($balances[$uid])) {
        $balances[$uid] = ["balance" => 0, "spend" => 0];
        save_data('balances', $balances);
    }
    if (!in_array($uid, $users)) {
        $users[] = $uid;
        save_data('users', $users);
    }

    // بدء البوت مع زر التشغيل/الإيقاف
    if ($text == "/start") {
        $start_buttons = [
            [["text" => "FREE FIRE 💎"], ["text" => "PUBG ⚜️"]],
            [["text" => "شحن رصيدي 💸"], ["text" => "معلومات الحساب 👤"]],
            [["text" => "🚨 المساعدة والدعم 🚨"]]
        ];
        if (in_array($uid, $admins)) {
            $start_buttons[] = [["text" => "/admin"]];
            $start_buttons[] = [["text" => "📊 إحصائيات البوت"]];
        }
        
        send($cid, "♕     اخـتـر مـن أحـد الأوامـر الـتـالـيـة     ♕ :", false, $start_buttons);
    }

    // التحقق من صلاحيات الأدمن عند استخدام أمر /admin
    if ($text == "/admin") {
        if (!in_array($uid, $admins)) {
            send($cid, "عذراً، هذا الأمر متاح فقط للإدمن.");
            return;
        }
        
        $admin_buttons = [
            [["text" => "➕ إضافة رصيد"], ["text" => "➖ خصم رصيد"]],
            [["text" => "💵 تعديل الأسعار"], ["text" => "🔁 تغيير رقم الكاش"]],
            [["text" => "📢 إرسال إذاعة"], ["text" => "🚫 حظر مستخدم"]],
            [["text" => "✅ فك حظر مستخدم"]]
        ];
        
        if (isMainAdmin($uid)) {
            $admin_buttons[] = [["text" => "👨‍💼 إضافة أدمن"], ["text" => "👨‍💼 حذف أدمن"]];
            $admin_buttons[] = [["text" => "📢 إدارة الاشتراك الإجباري"]];
        }
        
        $admin_buttons[] = [["text" => "📊 إحصائيات البوت"]];
        
        send($cid, " اهـــلا بـــك ايــهـا الادمــن ", false, $admin_buttons);
    }

    // الأوامر العامة
    if ($text == "🚨 المساعدة والدعم 🚨") {
        send($cid, " 
اهـلا وسـهـلا تـفـضـل اطـرح الـمـشـكـلـه الـتـي تـواجـهـك 🌔 : 
  \n$admin_users");
    }

    if ($text == "معلومات الحساب 👤") {
        $balance = $balances[$uid]["balance"] ?? 0;
        $spend = $balances[$uid]["spend"] ?? 0;
        $credit = number_format($balance / 15000, 4);

        $info_message = "👾 *معلومات حسابي* 👾\n";
        $info_message .= "🔆 *ايدي حسابك:* `$uid`\n";
        $info_message .= "🔆 `$credit` الرصيد بـ CREDIT\n";
        $info_message .= "🔆 `".number_format($balance)."` رصيدك بـ اليرة السورية\n";
        $info_message .= "🔆 `".number_format($spend)."` إجمالي المصروفات";
        
        send($cid, $info_message);
    }

    // قسم الألعاب
    if ($text == "FREE FIRE 💎") {
        $keys = [
            [["text" => "💎 110 - 8,700 ل.س", "callback_data" => "show_details:FF:💎 110"]],
            [["text" => "💎 330 - 25,000 ل.س", "callback_data" => "show_details:FF:💎 330"]],
            [["text" => "💎 530 - 39,000 ل.س", "callback_data" => "show_details:FF:💎 530"]],
            [["text" => "💎 1080 - 74,000 ل.س", "callback_data" => "show_details:FF:💎 1080"]]
        ];
        send($cid, "🔆 اللعبة FREE FIRE\n\nاختر الحزمة:", true, $keys);
    }

    if ($text == "PUBG ⚜️") {
        $keys = [
            [["text" => "UC 60 - 8,500 ل.س", "callback_data" => "show_details:PUBG:UC 60"]],
            [["text" => "UC 325 - 25,000 ل.س", "callback_data" => "show_details:PUBG:UC 325"]],
            [["text" => "UC 660 - 45,000 ل.س", "callback_data" => "show_details:PUBG:UC 660"]],
            [["text" => "UC 1800 - 120,000 ل.س", "callback_data" => "show_details:PUBG:UC 1800"]]
        ];
        send($cid, "🔆 اللعبة PUBG\n\nاختر الحزمة:", true, $keys);
    }

    // شحن الرصيد
    if ($text == "شحن رصيدي 💸") {
        $cash_number = file_get_contents(CASH_FILE);
        send($cid, "💳 *طريقة الشحن:*\n\nارسل المبلغ على الرقم:\n`$cash_number`\n\nثم أرسل رقم العملية:", false, [[["text" => "إلغاء", "callback_data" => "cancel"]]]);
        saveStep($uid, "wait_trans_id");
    }

    // معالجة الكال باك
    if ($data) {
        // معالجة اختيار الفئة
        if (strpos($data, "show_details:") === 0) {
            list(, $game, $pack) = explode(":", $data);
            $price = $prices[$pack];
            
            if ($callback) {
                deleteMessage($callback["message"]["chat"]["id"], $callback["message"]["message_id"]);
            }
            
            send($cid, "♕ تفاصيل الحزمة ♕:\n\n♪ اللعبة: $game\n♪ الفئة: $pack\n♪ السعر: " . number_format($price) . " ل.س\n\nاختر طريقة الشحن:", true, [
                [["text" => "عن طريق الـID", "callback_data" => "enter_id:$game:$pack"]],
                [["text" => "تغيير السيرفر", "callback_data" => "back_to_games"]]
            ]);
            answer($callback["id"], "تم عرض التفاصيل");
        }
        
        // معالجة إدخال ID
        elseif (strpos($data, "enter_id:") === 0) {
            list(, $game, $pack) = explode(":", $data);
            saveStep($uid, "wait_game_id:$game:$pack");
            
            if ($callback) {
                deleteMessage($callback["message"]["chat"]["id"], $callback["message"]["message_id"]);
            }
            
            send($cid, "يرجى إرسال ID حسابك :");
            answer($callback["id"], "انتظر إدخال ID");
        }
        
        // تأكيد الطلب
        elseif (strpos($data, "confirm_order:") === 0) {
            list(, $game, $pack, $player_id) = explode(":", $data);
            $price = $prices[$pack];
            
            if ($callback) {
                deleteMessage($callback["message"]["chat"]["id"], $callback["message"]["message_id"]);
            }
            
            if ($balances[$uid]["balance"] < $price) {
                send($cid, "❌ رصيدك غير كافي. يرجى شحن الرصيد أولاً.");
                return;
            }
            
            $balances[$uid]["balance"] -= $price;
            $balances[$uid]["spend"] += $price;
            save_data('balances', $balances);
            
            $order_id = uniqid();
            $price_credit = number_format($price / 15000, 4);
            
            file_put_contents(BASE_DIR . "data_trans/order_$order_id.json", json_encode([
                "game" => $game, "pack" => $pack, "price_credit" => $price_credit,
                "price_lira" => $price, "player_id" => $player_id, 
                "user_id" => $uid, "time" => time()
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            send($cid, "هذه خدمة آلية سوف يتم تنفيذ طلبك خلال دقيقة ✅\n\n♕ رقم الطلب: $order_id\n♕ اللعبة: $game\n♕ الحزمة: $pack\n♕ السعر: " . number_format($price) . " ل.س\n♕ آيدى اللاعب: $player_id\n\n♕ سيتم تنفيذ الطلب خلال (1 ثانية - 3 دقائق)");
            
            send($admin_id, "🎮 طلب شحن جديد:\n⨗ معرف الطلب: $order_id\n⨗ اللعبة: $game\n⨗ الفئة: $pack\n⨗ السعر: $price_credit credits\n⨗ من: $uid", true, [
                [["text" => "✅ تم الشحن", "callback_data" => "okorder:$order_id"]],
                [["text" => "❌ لن يتم الشحن", "callback_data" => "rejectorder:$order_id"]]
            ]);
            
            answer($callback["id"], "تم تأكيد الطلب");
        }
        
        // موافقة الأدمن على الطلب
        elseif (strpos($data, "okorder:") === 0) {
            $order_id = explode(":", $data)[1];
            $data_file = BASE_DIR . "data_trans/order_$order_id.json";
            
            if (file_exists($data_file)) {
                $order = json_decode(file_get_contents($data_file), true);
                $time_diff = time() - $order["time"];
                $mins = floor($time_diff / 60);
                $secs = $time_diff % 60;
                
                $msg = "تم اكتمال طلبك اوتوماتيكيا بنجاح ✅️\n✓ رقم الطلب : $order_id\n✓ اللعبة: {$order["game"]}\n✓ الحزمة : {$order["pack"]}\n✓ السعر: {$order["price_credit"]} credits\n✓ معرف اللاعب: {$order["player_id"]}\n⏱️ الوقت المستغرق: {$mins} دقائق و {$secs} ثانية";
                
                send($order["user_id"], $msg);
                answer($callback["id"], "✅ تم الشحن");
                deleteMessage($callback["message"]["chat"]["id"], $callback["message"]["message_id"]);
                unlink($data_file);
            }
        }
    }

    // معالجة الخطوات
    if ($step = getStep($uid)) {
        // انتظار إدخال ID اللعبة
        if (strpos($step, "wait_game_id:") === 0) {
            if (!is_numeric($text)) {
                send($cid, "❌ يجب إدخال أرقام فقط. الرجاء المحاولة مرة أخرى من البداية.");
                delStep($uid);
                return;
            }
            list(, $game, $pack) = explode(":", $step);
            $price = $prices[$pack];
            send($cid, "♕ تفاصيل الطلب ♕:\n✽ اللعبة: $game\n✽ الفئة: $pack\n✽ السعر: " . number_format($price) . " ل.س\nID الحساب: $text\nيرجى التأكد من الآيدي والضغط على تاكيد الطلب", true, [
                [["text" => "تأكيد الطلب", "callback_data" => "confirm_order:$game:$pack:$text"]],
                [["text" => "إلغاء الطلب", "callback_data" => "cancel"]]
            ]);
            delStep($uid);
        }
        
        // انتظار رقم التحويل
        elseif ($step == "wait_trans_id") {
            if (!is_numeric($text)) {
                send($cid, "❌ يجب إدخال أرقام فقط.");
                delStep($uid);
                return;
            }
            
            file_put_contents(BASE_DIR . "data_trans/{$uid}_trans_id.txt", $text);
            saveStep($uid, "wait_amount");
            send($cid, "الرجاء ادخال المبلغ (بالارقام فقط):");
        }
        
        // انتظار المبلغ المحول
        elseif ($step == "wait_amount") {
            if (!is_numeric($text)) {
                send($cid, "❌ يجب إدخال أرقام فقط.");
                delStep($uid);
                return;
            }
            
            $trans_id_file = BASE_DIR . "data_trans/{$uid}_trans_id.txt";
            if (file_exists($trans_id_file)) {
                $trans_id = file_get_contents($trans_id_file);
                file_put_contents(BASE_DIR . "data_trans/transaction_$trans_id.json", json_encode([
                    "user_id" => $uid,
                    "amount" => $text,
                    "status" => "pending",
                    "timestamp" => time()
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                
                unlink($trans_id_file);
            }
            
            delStep($uid);
            send($admin_id, "💰 طلب شحن رصيد جديد:\n⨗ من المستخدم: $uid\n⨗ رقم عملية التحويل: $trans_id\n⨗ المبلغ: $text ل.س", true, [
                [["text" => "✅ إضافة الرصيد", "callback_data" => "add:$uid:$text"]],
                [["text" => "❌ رفض", "callback_data" => "deny:$uid:$text"]]
            ]);
            
            send($cid, "✅ تم إرسال طلبك بنجاح. يرجى الانتظار ليتم التحقق منه من قبل الإدارة.");
        }
    }

    // معالجة الأوامر من لوحة الأدمن
    if ($text == "➕ إضافة رصيد") {
        if (!in_array($uid, $admins)) {
            send($cid, "⛔ ليس لديك صلاحية تنفيذ هذا الأمر!");
            return;
        }
        saveStep($uid, "credit_user:add");
        send($cid, "الرجاء إدخال ID المستخدم الذي تريد إضافة الرصيد له:");
    }
    
    if ($text == "📊 إحصائيات البوت") {
        if (!in_array($uid, $admins)) {
            send($cid, "⛔ ليس لديك صلاحية تنفيذ هذا الأمر!");
            return;
        }
        $stats = getBotStatistics();
        $message = "📊 *إحصائيات البوت* 📊\n\n";
        $message .= "👥 إجمالي المستخدمين: `{$stats['users']}`\n";
        $message .= "🚫 المستخدمين المحظورين: `{$stats['banned']}`\n";
        $message .= "👨‍💼 عدد المشرفين: `{$stats['admins']}`\n";
        $message .= "📢 عدد القنوات الإجبارية: `{$stats['channels']}`\n";
        $message .= "💸 إجمالي الأرصدة: `".number_format($stats['balance'])."` ل.س";
        send($cid, $message);
    }
}

// البدء في معالجة التحديث
handle_update_logic($text, $data, $cid, $uid, $callback);
?>
