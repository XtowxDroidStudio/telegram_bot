<?php
// ملف الإعدادات
$token = '8349962631:AAHYP4yF5S5PR57HKsdPOSuiBK17E-1KJCU';
$admin_id = '1919956515';
$bot_status = 'on'; // حالة البوت (on/off)
$main_admin = '1919956515'; // الأدمن الأساسي (المالك)
$admin_users = "https://t.me/darkshotsy";
// تعريف الثوابت بمسارات مطلقة لضمان الحفظ والقراءة الصحيحة
define("BASE_DIR", __DIR__ . DIRECTORY_SEPARATOR); // المسار المطلق للمجلد الحالي
define("BALANCES_FILE", BASE_DIR . "balances00.json");
define("STEPS_DIR", BASE_DIR . "s088teps" . DIRECTORY_SEPARATOR);
define("PRICES_FILE", BASE_DIR . "p880rices.json");
define("CASH_FILE", BASE_DIR . "c880cash.txt");
define("USERS_FILE", BASE_DIR . "u0sers.json");
define("BANNED_FILE", BASE_DIR . "b880anned.json");
define("ADMINS_FILE", BASE_DIR . "a88dmins.json");
define("FORCED_CHANNELS_FILE", BASE_DIR . "f88orced_channels.json");


/**
 * دالة آمنة لتهيئة الملفات والتأكد من صحة محتوى JSON.
 * @param string $file مسار الملف.
 * @param mixed $default القيمة الافتراضية إذا لم يكن الملف موجودًا أو كان تالفًا.
 */
function safe_init_file($file, $default = []) {
    if (!file_exists($file)) {
        // إذا لم يكن الملف موجودًا، قم بإنشائه بالمحتوى الافتراضي
        if (file_put_contents($file, json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
            error_log("Failed to create file: " . $file);
        }
    } else {
        $content = file_get_contents($file);
        // التحقق من صحة JSON، وإعادة تهيئة الملف إذا كان تالفًا
        json_decode($content);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Corrupted JSON file detected: " . $file . ". Re-initializing.");
            if (file_put_contents($file, json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
                error_log("Failed to re-initialize corrupted file: " . $file);
            }
        }
    }
}
 // ملف القنوات الإجبارية

// تهيئة الملفات والمجلدات
if (!file_exists(STEPS_DIR)) {
    if (!mkdir(STEPS_DIR, 0755, true)) { // 0755 هي الأذونات الشائعة
        error_log("Failed to create directory: " . STEPS_DIR);
    }
}
if (!file_exists(BASE_DIR . "data_trans")) {
    if (!mkdir(BASE_DIR . "data_trans", 0755, true)) { // 0755 هي الأذونات الشائعة
        error_log("Failed to create directory: " . BASE_DIR . "data_trans");
    }
}

safe_init_file(BALANCES_FILE, []);
safe_init_file(USERS_FILE, []);
safe_init_file(BANNED_FILE, []);
// التأكد من وجود ملف الأدمن وإضافة الأدمن الأساسي إذا لم يكن موجودًا
if (!file_exists(ADMINS_FILE)) {
    if (file_put_contents(ADMINS_FILE, json_encode([$admin_id], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
        error_log("Failed to create ADMINS_FILE.");
    }
}
safe_init_file(FORCED_CHANNELS_FILE, []);
// تهيئة ملف الأسعار إذا لم يكن موجودًا
if (!file_exists(PRICES_FILE)) {
    $default_prices = [
        "💎 110" => 8700, "محذوف" => 17000, "💎 330" => 25000,
        "💎 530" => 39000, "محذوف" => 51000, "💎 1080" => 74000,
        "محذوف" => 99000, "💎 2180" => 145000, "محذوف" => 235000,
        "محذوف" => 460000,
        "العضوية الأسبوعية" => 9000, "العضوية الشهرية" => 25000,
        "UC 60" => 8500, "UC 325" => 25000, "UC 660" => 45000,
        "UC 1800" => 120000, "UC 3850" => 235000, "UC 8100" => 460000
    ];
    if (file_put_contents(PRICES_FILE, json_encode($default_prices, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
        error_log("Failed to create PRICES_FILE.");
    }
}
// تهيئة ملف الكاش إذا لم يكن موجودًا
if (!file_exists(CASH_FILE)) {
    if (file_put_contents(CASH_FILE, "62324913") === false) {
        error_log("Failed to create CASH_FILE.");
    }
}

// تحميل البيانات مع معالجة الأخطاء
// استخدام safe_init_file يضمن أن الملفات موجودة وصالحة
$balances = json_decode(file_get_contents(BALANCES_FILE), true);
if (!is_array($balances)) {
    $balances = [];
    error_log("Balances file corrupted after load. Resetting to empty array.");
}

$prices = json_decode(file_get_contents(PRICES_FILE), true);
if (!is_array($prices)) {
    $prices = [];
    error_log("Prices file corrupted after load. Resetting to empty array.");
}

$users = json_decode(file_get_contents(USERS_FILE), true);
if (!is_array($users)) {
    $users = [];
    error_log("Users file corrupted after load. Resetting to empty array.");
}

$banned = json_decode(file_get_contents(BANNED_FILE), true);
if (!is_array($banned)) {
    $banned = [];
    error_log("Banned file corrupted after load. Resetting to empty array.");
}

$admins = json_decode(file_get_contents(ADMINS_FILE), true);
if (!is_array($admins)) {
    $admins = [];
    error_log("Admins file corrupted after load. Resetting to empty array.");
}

$forced_channels = json_decode(file_get_contents(FORCED_CHANNELS_FILE), true);
if (!is_array($forced_channels)) {
    $forced_channels = [];
    error_log("Forced channels file corrupted after load. Resetting to empty array.");
}

// استقبال التحديث من Telegram
$update = json_decode(file_get_contents("php://input"), true);
// التحقق مما إذا كان هناك تحديث لتجنب الأخطاء
if (empty($update)) {
    // لا يوجد تحديث، قد يكون هذا طلب HTTP عادي وليس من Telegram
    // يمكنك تسجيل الخطأ هنا أو عدم فعل شيء
    exit();
}

$message = $update["message"] ?? null;
$callback = $update["callback_query"] ?? null;
$data = $callback["data"] ?? null;
$text = $message["text"] ?? null;
$cid = $message["chat"]["id"] ?? $callback["message"]["chat"]["id"] ?? null;
$uid = $message["from"]["id"] ?? $callback["from"]["id"] ?? null;
// --- الدوال المساعدة (بدون تغيير في منطقها الداخلي) ---

/**
 * دالة مركزية لحفظ البيانات في الملفات
 * @param string $type نوع البيانات المراد حفظها (مثل: 'balances', 'users', etc.)
 * @param mixed $data البيانات المراد حفظها
 * @return bool
 */
function save_data($type, $data) {
    global $BALANCES_FILE, $USERS_FILE, $BANNED_FILE, $ADMINS_FILE, $FORCED_CHANNELS_FILE, $PRICES_FILE, $CASH_FILE;
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
            // The cash file is a simple text file, not JSON
            if (file_put_contents($file_path, $data) === false) {
                error_log("Failed to save cash file.");
                return false;
            }
            return true;
        default:
            error_log("Attempted to save to an unknown data type: " . $type);
            return false;
    }

    if (file_put_contents($file_path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
        error_log("Failed to save data to file: " . $file_path);
        return false;
    }
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
    $result = file_get_contents("https://api.telegram.org/bot$token/sendMessage?" . http_build_query($d));
    if ($result === FALSE) {
        error_log("Failed to send message to $id");
    }
}

function answer($cid, $text) {
    global $token;
    $result = file_get_contents("https://api.telegram.org/bot$token/answerCallbackQuery?callback_query_id=$cid&text=" . urlencode($text));
    if ($result === FALSE) {
        error_log("Failed to answer callback $cid");
    }
}

function deleteMessage($chat_id, $message_id) {
    global $token;
    $result = file_get_contents("https://api.telegram.org/bot$token/deleteMessage?chat_id=$chat_id&message_id=$message_id");
    if ($result === FALSE) {
        error_log("Failed to delete message $message_id");
    }
}

function saveStep($uid, $step) { 
    // استخدام المسار المطلق
    if (!@file_put_contents(STEPS_DIR . $uid, $step)) {
        error_log("Failed to save step for $uid to " . STEPS_DIR . $uid);
    }
}
function getStep($uid) { 
    // استخدام المسار المطلق
    return file_exists(STEPS_DIR . $uid) ?
    file_get_contents(STEPS_DIR . $uid) : null;
}
function delStep($uid) { 
    // استخدام المسار المطلق
    if (file_exists(STEPS_DIR . $uid)) {
        if (!@unlink(STEPS_DIR . $uid)) {
            error_log("Failed to delete step for $uid from " . STEPS_DIR . $uid);
        }
    }
}

// ----------------------------------------------------
// بداية الدالة الجديدة لمعالجة منطق التحديثات
function handle_update_logic($input_text, $input_data, $input_cid, $input_uid, $input_callback = null) {
    // جلب المتغيرات العامة التي تحتاجها الدالة
        global $token, $admin_id, $bot_status, $main_admin, $admin_users;
    global $balances, $prices, $users, $banned, $admins, $forced_channels;
    
    // جلب الثوابت المتعلقة بالملفات
    global $BALANCES_FILE, $STEPS_DIR, $PRICES_FILE, $CASH_FILE, $USERS_FILE, $BANNED_FILE, $ADMINS_FILE, $FORCED_CHANNELS_FILE;
    // إعادة تعريف المتغيرات المحلية لتكون مثل المتغيرات العامة التي كان الكود يعتمد عليها
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
            // إعادة إرسال رسالة /start باستخدام الدالة
            handle_update_logic("/start", null, $cid, $uid);
            return;
        } else {
            answer($callback["id"], "❌ لم تشترك في جميع القنوات المطلوبة");
        }
    }

    // معالجة التحقق من حالة البوت
    if ($data == "check_bot_status") {
        if ($bot_status == 'on') {
            answer($callback["id"], "✅ البوت يعمل الآن");
            deleteMessage($callback["message"]["chat"]["id"], $callback["message"]["message_id"]);
            handle_update_logic("/start", null, $cid, $uid);
            return;
        } else {
            answer($callback["id"], "⚠️ البوت لا يزال متوقفًا");
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
        if ($bot_status == 'off' && !in_array($uid, $admins)) {
            send($cid, "⚠️ البوت متوقف حاليًا للصيانة. سنعود قريبًا!", false, [
                [["text" => "🔄 تحديث", "callback_data" => "check_bot_status"]]
            ]);
            return; 
        }
        
        $start_buttons = [
            [["text" => "FREE FIRE 💎"], ["text" => "PUBG ⚜️"]],
            [["text" => "شحن رصيدي 💸"], ["text" => "معلومات الحساب 👤"]],
            [["text" => "🚨 المساعدة والدعم 🚨"]]
        ];
        if (in_array($uid, $admins)) {
            $start_buttons[] = [["text" => "/admin"]];
            $start_buttons[] = [["text" => "📊 إحصائيات البوت"]];
            // عرض زر التشغيل/الإيقاف حسب الحالة الحالية للبوت
            if ($bot_status == 'on') {
                $start_buttons[] = [["text" => "⏹️ إيقاف البوت"]];
            } else {
                $start_buttons[] = [["text" => "▶️ تشغيل البوت"]];
            }
        }
        
        send($cid, "♕     اخـتـر مـن أحـد الأوامـر الـتـالـيـة     ♕ :", false, $start_buttons);
    }

    // التحقق من صلاحيات الأدمن عند استخدام أمر /admin
    if ($text == "/admin") {
        if (!in_array($uid, $admins)) {
            send($cid, "عذراً، هذا الأمر متاح فقط للإدمن.");
            // لا نستخدم exit هنا، نكتفي بالرسالة والعودة
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
        $admin_users = 'https://t.me/l\_9\_e'; 
        $admin_buttons[] = [["text" => "📊 إحصائيات البوت"]];
        // عرض زر التشغيل/الإيقاف حسب الحالة الحالية للبوت
        if ($bot_status == 'on') {
            $admin_buttons[] = [["text" => "⏹️ إيقاف البوت"]];
        } else {
            $admin_buttons[] = [["text" => "▶️ تشغيل البوت"]];
        }
        
        send($cid, " اهـــلا بـــك ايــهـا الادمــن ", false, $admin_buttons);
    }

    // الأوامر العامة
    if ($text == "🚨 المساعدة والدعم 🚨") {
        send($cid, " 
اهـلا وسـهـلا تـفـضـل اطـرح الـمـشـكـلـه الـتـي تـواجـهـك 🌔 : 
  \n$admin_users"); // تم تعديل هذا السطر بناءً على طلب المستخدم
    }

    if ($text == "معلومات الحساب 👤") {
        // بما أن safe_init_file و check for !isset($balances[$uid]) تتم في بداية handle_update_logic،
        // فليس هناك حاجة لتكرارها هنا.
        // if (!isset($balances[$uid])) {
        //     $balances[$uid] = ["balance" => 0, "spend" => 0];
        //     file_put_contents(BALANCES_FILE, json_encode($balances));
        // }

        // MODIFICATION START: Correctly get user's first and last name from global message/callback
        global $message, $callback; // Declare global variables within the function

        $source_obj = null;
        if ($callback) {
            $source_obj = $callback;
        } elseif ($message) {
            $source_obj = $message;
        }

        $first_name = $source_obj['from']['first_name'] ?? "مستخدم";
        $last_name = $source_obj['from']['last_name'] ?? "";
        $full_name = trim("$first_name $last_name");
        // MODIFICATION END
        
        $balance = $balances[$uid]["balance"] ?? 0;
        $spend = $balances[$uid]["spend"] ?? 0;
        $credit = number_format($balance / 15000, 4);

        $info_message = "👾 *معلومات حسابي* 👾\n";
        $info_message .= "🔆 *الاسم:* [$full_name](tg://user?id=$uid)\n";
        $info_message .= "🔆 *ايدي حسابك:* `$uid`";
        $info_message .= "\n";
        $info_message .= "🔆 `$credit` الرصيد بـ CREDIT\n";
        $info_message .= "🔆 `".number_format($balance)."` رصيدك بـ اليرة السورية\n";
        $info_message .= "🔆  ليرة سورية`".number_format($spend)."` إجمالي المصروفات\n";
        $info_message .= "";
        $buttons = [
            [["text" => "  شكرا لاستخدامك بوتنا", "callback_data" => "refresh_info"]],
        ];
        send($cid, $info_message, true);
    }

    // قسم الألعاب
    
    
// استبدال قسم الألعاب بالكود التالي:
    if ($text == "FREE FIRE 💎") {
    $keys = [
        [["text" => "FREEFIRE AUTO", "callback_data" => "show_categories:FF:manual"]]
    ];
    send($cid, "🔆 اللعبة FREE FIRE  

🔆 اختر سيرفر الشحن المناسب :", true, $keys);
}

if ($text == "PUBG ⚜️") {
    $keys = [
        [["text" => "PUBG AUTO", "callback_data" => "show_categories:PUBG:manual"]]
    ];
    send($cid, "🔆 اللعبة PUBG

🔆 اختر سيرفر الشحن المناسب :", true, $keys);
}

// إضافة هذا الكود في قسم معالجة الكال باك:
if (strpos($data, "show_categories:") === 0) {
    list(, $game, $type) = explode(":", $data);
    $keys = [];
    foreach ($prices as $name => $price) {
        if (($game == "FF" && (strpos($name, "💎") !== false || strpos($name, "Membership") !== false && $name != "محذوف")) ||
            ($game == "PUBG" && strpos($name, "UC") !== false && $name != "محذوف")) {
            $keys[] = [["text" => "$name", "callback_data" => "show_details:$game:$name"]];
        }
    }
    
    send($cid, "$game AUTO 
اختر حزمة :", true, $keys);
    answer($callback["id"], "تم عرض الفئات");
}
 
    // شحن الرصيد بالتعديلات المطلوبة
    if ($text == "شحن رصيدي 💸") {
        // استخدام المسار المطلق عند قراءة CASH_FILE
        $cash_number = file_get_contents(CASH_FILE);
        $payment_button = [[
            ["text" => "syriatel cash ( تلقائي )", 
             "callback_data" => "show_cash_number"]
        ]];
        send($cid, "اختر طريقة الإيداع المناسبة :", true, $payment_button);
    }

    // معالجة الكال باك
    if ($data) {
    // عرض رقم الكاش عند الضغط على زر الدفع
    if ($data == "show_cash_number") {

        // إرسال صورة عبر رابط مباشر (تأكد أن يكون رابط مباشر لصورة وليس t.me)
        file_get_contents("https://api.telegram.org/bot$token/sendPhoto?chat_id=$uid&photo=https://t.me/ad_mins_chnanl_sp/4");

        // قراءة رقم الكاش من الملف
        $cash_number = file_get_contents(CASH_FILE);
        $copyable_code = "`$cash_number`";

        // إرسال رسالة تحتوي على رقم الكاش وتعليمات
        send($cid, "*syriatel cash ( تلقائي )*\n\n".
                   "قم بإيداع المبلغ المطلوب على أحد الرموز التالية عن طريق التحويل اليدوي، ولسنا مسؤولين عن أية عملية تعبئة رصيد (وحدات):\n\n".
                   "$copyable_code\n\n".
                   "علماً أنَّ:\n\n".
                   "*1 CREADIT = 10400 ل.س*\n\n".
                   "--------------------------\n\n".
                   "📝 *أدخل رقم عملية التحويل:*", false);

        // حفظ الخطوة التالية للمستخدم
        saveStep($uid, "wait_trans_id");

        // الرد على الضغط على الزر
        answer($callback["id"], "تم عرض رقم الكاش");
    }

        // معالجة اختيار الفئة
        if (strpos($data, "show_details:") === 0) {
            list(, $game, $pack) = explode(":", $data);
            $price = $prices[$pack];
            $price_usd = number_format($price / 15000, 2);
            
            deleteMessage($callback["message"]["chat"]["id"], $callback["message"]["message_id"]);
            
            send($cid, "♕ تفاصيل الحزمة ♕:

♪ اللعبة: $game ( اوتوماتيكي ) 
♪ الفئة: $pack
♪ السعر: $price ل.س 

اختر طريقة الشحن 👇👇:", true, [
                [["text" => "عن طريق الـID", "callback_data" => "enter_id:$game:$pack"]],
                [["text" => "تغيير السيرفر", "callback_data" => "back_to_games:$game"]]
            ]);
            answer($callback["id"], "تم عرض التفاصيل");
        }
        
        // معالجة إدخال ID
        elseif (strpos($data, "enter_id:") === 0) {
            list(, $game, $pack) = explode(":", $data);
            saveStep($uid, "wait_game_id:$game:$pack");
            
            deleteMessage($callback["message"]["chat"]["id"], $callback["message"]["message_id"]);
            
            // MODIFICATION: Add cancel button when asking for game ID
            send($cid, "يرجى إرسال ID حسابك :", true, [[["text" => "إلغاء ❌", "callback_data" => "cancel_current_action"]]]);
            answer($callback["id"], "انتظر إدخال ID");
        }
        
        // معالجة الرجوع للألعاب
        elseif (strpos($data, "back_to_games:") === 0) {
            $game = explode(":", $data)[1];
            deleteMessage($callback["message"]["chat"]["id"], $callback["message"]["message_id"]);
            
            $simulated_text = "";
            if ($game == "FF") {
                $simulated_text = "𝗙𝗥𝗘𝗘 𝗙𝗜𝗥𝗘 💎";
            } else {
                $simulated_text = "𝗣𝗨𝗕𝗚 ⚜️";
            }
            handle_update_logic($simulated_text, null, $cid, $uid, $callback);
            // تمرير الـ callback لتمرير معلومات المرسل
            return;
        }
        
        // تأكيد الطلب
        elseif (strpos($data, "confirm_order:") === 0) {
            list(, $game, $pack, $player_id) = explode(":", $data);
            $price = $prices[$pack];
            
            deleteMessage($callback["message"]["chat"]["id"], $callback["message"]["message_id"]);
            
            if ($balances[$uid]["balance"] < $price) {
                send($cid, "❌ رصيدك غير كافي. يرجى شحن الرصيد أولاً.");
                return;
            }
            
            $balances[$uid]["balance"] -= $price;
            $balances[$uid]["spend"] += $price;
            save_data('balances', $balances);
            
            $order_id = uniqid();
            $now = time();
            $price_usd = number_format($price / 15000, 2);
            $price_credit = number_format($price / 15000, 4);
            // استخدام المسار المطلق لملفات data_trans
            if (file_put_contents(BASE_DIR . "data_trans/order_$order_id.json", json_encode([
                "game" => $game, "pack" => $pack, "price_usd" => $price_usd,
                "price_lira" => $price, "price_credit" => $price_credit,
                "player_id" => $player_id, "user_id" => $uid,
                "time" => $now
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
                error_log("Failed to create order file for order ID: " . $order_id);
                send($cid, "❌ حدث خطأ داخلي عند إنشاء طلبك. يرجى المحاولة مرة أخرى لاحقاً.");
                return;
            }
            
            send($cid, "هذه خدمة آلية سوف يتم تنفيذ طلبك خلال دقيقة ✅

♕ رقم الطلب: $order_id
♕ اللعبة: $game
♕ الحزمة: $pack
♕ السعر: $price_usd $
♕ السعر بالليرة: " . number_format($price) . " ل.س
♕ آيدى اللاعب: $player_id

♕ سيتم تنفيذ الطلب خلال (1 ثانية - 3 دقائق )");
            send($admin_id, "🎮 طلب شحن جديد:

    ⨗ معرف الطلب: $order_id
    ⨗ اللعبة: $game
    ⨗ الفئة: $pack
    ⨗ السعر: $price_credit credits
    ⨗ من: $uid", true, [
                [["text" => "✅ تم الشحن", "callback_data" => "okorder:$order_id"]],
                [["text" => "❌ لن يتم الشحن", "callback_data" => "rejectorder:$order_id"]]
            ]);
            
            answer($callback["id"], "تم تأكيد الطلب");
        }
        
        // موافقة الأدمن على الطلب
        elseif (strpos($data, "okorder:") === 0) {
            $order_id = explode(":", $data)[1];
            // استخدام المسار المطلق
            $data_file = BASE_DIR . "data_trans/order_$order_id.json";
            if (!file_exists($data_file)) {
                answer($callback["id"], "❌ الطلب غير موجود أو تم معالجته مسبقًا.");
                return;
            }
            $order = json_decode(file_get_contents($data_file), true);
            $time_diff = time() - $order["time"];
            $mins = floor($time_diff / 60);
            $secs = $time_diff % 60;
            $msg = "تم اكتمال طلبك اوتوماتيكيا بنجاح ✅️

✓ رقم الطلب : $order_id
✓ اللعبة: {$order["game"]}
✓ الحزمة : {$order["pack"]}
✓ السعر: {$order["price_credit"]} credits
✓ معرف اللاعب: {$order["player_id"]}

    ⏱️ الوقت المستغرق: {$mins} دقائق و {$secs} ثانية ";
            send($order["user_id"], $msg);
            answer($callback["id"], "✅ تم الشحن");
            deleteMessage($callback["message"]["chat"]["id"], $callback["message"]["message_id"]); // تم إضافة هذا السطر
            if (!unlink($data_file)) {
                error_log("Failed to delete order file: " . $data_file);
            }
        }
        
        
        // رفض الأدمن للطلب
        elseif (strpos($data, "rejectorder:") === 0) {
            $order_id = explode(":", $data)[1];
            // استخدام المسار المطلق
            $data_file = BASE_DIR . "data_trans/order_$order_id.json";
            if (!file_exists($data_file)) {
                answer($callback["id"], "❌ الطلب غير موجود أو تم معالجته مسبقًا.");
                return;
            }
            $order = json_decode(file_get_contents($data_file), true);
            // إعادة الرصيد للمستخدم عند الرفض
            if (isset($balances[$order["user_id"]])) {
                $balances[$order["user_id"]]["balance"] += $order["price_lira"];
                if (save_data('balances', $balances) === false) {
                    error_log("Failed to refund balance for user: " . $order["user_id"]);
                }
            } else {
                error_log("Attempted to refund non-existent user: " . $order["user_id"]);
            }

            $time_diff = time() - $order["time"];
            $h = floor($time_diff / 3600);
            $m = floor(($time_diff % 3600) / 60);
            $s = $time_diff % 60;
            $msg = "تم انتهاء الكمية ولن نستطيع تنفيذ طلبك اوتوماتيكيا ❌️. تم إعادة الرصيد إلى حسابك.
▪️ معرف الطلب: $order_id
▪️ اللعبة: {$order["game"]}
▪️ الحزمة: {$order["pack"]}
▪️ السعر: {$order["price_usd"]} $
▪️ معرف اللاعب: {$order["player_id"]}

    ⏱️ الوقت المستغرق: {$h} ساعات و {$m} دقائق و {$s} ثانية";
            send($order["user_id"], $msg);
            answer($callback["id"], "❌ تم الرفض وإعادة الرصيد.");
            deleteMessage($callback["message"]["chat"]["id"], $callback["message"]["message_id"]); // تم إضافة هذا السطر
            if (!unlink($data_file)) {
                error_log("Failed to delete order file: " . $data_file);
            }
        }
        
        // إضافة رصيد من قبل الأدمن (من لوحة التحكم) - هذا لا يتطلب زر إلغاء هنا مباشرة لأنه يأتي من خطوة لاحقة
        elseif (strpos($data, "add:") === 0) {
            $parts = explode(":", $data);
            $tid = $parts[1];
            $amount = isset($parts[2]) ? intval($parts[2]) : 0;
            // تأكد من وجود المبلغ
            
            if (!is_numeric($tid) || $amount <= 0) { // تحقق من صحة البيانات
                answer($callback["id"], "❌ بيانات غير صحيحة.");
                return;
            }

            if (!isset($balances[$tid])) {
                $balances[$tid] = ["balance" => 0, "spend" => 0];
            }
            $balances[$tid]["balance"] += $amount;
            save_data('balances', $balances);
            send($tid, "تم التحقق من العمليه بنجاح ✅ تمت اضافة $amount ليرة سورية إلى حسابك ");
            answer($callback["id"], "✅ تمت الإضافة.");
        }

        // --- تعديل الكود يبدأ هنا ---
        elseif (strpos($data, "deny:") === 0) {
            list(, $user_id, $amount) = explode(":", $data);
            
            // تحقق من صلاحيات الأدمن
            if (!in_array($uid, $admins)) {
                answer($callback["id"], "⛔ ليس لديك صلاحية تنفيذ هذا الأمر!");
                return;
            }
            
            // إرسال رسالة للمستخدم
            send($user_id, "❌ تم رفض طلب الشحن الخاص بك بقيمة $amount ل.س. للمزيد من التفاصيل، يرجى التواصل مع الإدارة.");
            
            // تعديل الرسالة التي تحتوي على الأزرار في لوحة الأدمن
            $new_text = "*تم رفض طلب الشحن بقيمة $amount ل.س*";
            file_get_contents("https://api.telegram.org/bot$token/editMessageText?chat_id={$callback['message']['chat']['id']}&message_id={$callback['message']['message_id']}&text=" . urlencode($new_text) . "&parse_mode=Markdown");
            
            // الرد على الضغط على الزر
            answer($callback["id"], "تم رفض الطلب بنجاح.");
            
            // يمكنك حذف ملف المعاملة إذا كنت ترغب في ذلك
            // $trans_file = BASE_DIR . "data_trans/transaction_$trans_id.json";
            // if (file_exists($trans_file)) {
            //     unlink($trans_file);
            // }
            
        }
        // --- تعديل الكود ينتهي هنا ---
        
        // تعديل الأسعار من قبل الأدمن
        elseif (strpos($data, "setprice:") === 0) {
            $pack = explode(":", $data)[1];
            saveStep($uid, "price|$pack");
            // MODIFICATION: Add cancel button
            send($cid, "💵 أرسل السعر الجديد لـ $pack:", true, [[["text" => "إلغاء ❌", "callback_data" => "cancel_current_action"]]]);
        }
        // تبديل حالة البوت
        elseif ($data == "toggle_bot_on") {
            // تحقق من صلاحية الأدمن قبل التغيير
            if (!in_array($uid, $admins)) {
                answer($callback["id"], "⛔ ليس لديك صلاحية تنفيذ هذا الأمر!");
                return;
            }
            $bot_status = 'on'; // يتم تعديل هذا المتغير العالمي مباشرة
            answer($callback["id"], "✅ تم تشغيل البوت");
            send($callback["message"]["chat"]["id"], "✅ تم تشغيل البوت بنجاح", true, [
                [["text" => "⏹️ إيقاف البوت", "callback_data" => "toggle_bot_off"]]
            ]);
        }
        elseif ($data == "toggle_bot_off") {
            // تحقق من صلاحية الأدمن قبل التغيير
            if (!in_array($uid, $admins)) {
                answer($callback["id"], "⛔ ليس لديك صلاحية تنفيذ هذا الأمر!");
                return;
            }
            $bot_status = 'off';
            answer($callback["id"], "⏹️ تم إيقاف البوت");
            send($callback["message"]["chat"]["id"], "⏹️ تم إيقاف البوت بنجاح", true, [
                [["text" => "▶️ تشغيل البوت", "callback_data" => "toggle_bot_on"]]
            ]);
        }
        // إدارة القنوات الإجبارية
        elseif (strpos($data, "forced_channels_") === 0) {
            if (!isMainAdmin($uid)) {
                answer($callback["id"], "⛔ ليس لديك صلاحية الوصول لهذه الميزة");
                return;
            }
            if ($data == "forced_channels_add") {
                saveStep($uid, "wait_channel_username");
                // MODIFICATION: Add cancel button
                send($cid, "أرسل معرف القناة (مثال: @channel) لإضافتها للاشتراك الإجباري:", true, [[["text" => "إلغاء ❌", "callback_data" => "cancel_current_action"]]]);
            } elseif ($data == "forced_channels_remove") {
                if (empty($forced_channels)) {
                    send($cid, "❌ لا توجد قنوات مسجلة حالياً."); // تم تعديل هذه الرسالة
                    return;
                }
                $buttons = [];
                foreach ($forced_channels as $index => $channel) {
                    $buttons[] = [
                        ["text" => $channel['username'], "callback_data" => "show_channel:$index"],
                        ["text" => "🗑️ حذف", "callback_data" => "forced_channel_delete:$index"]
                    ];
                }
                $buttons[] = [["text" => "🔙 رجوع", "callback_data" => "forced_channels_back"]];
                send($cid, "📋 قائمة القنوات الإجبارية:", true, $buttons);
            } elseif ($data == "forced_channels_back") {
                // العودة إلى لوحة التحكم باستخدام الدالة
                handle_update_logic("/admin", null, $cid, $uid, $callback);
                return;
            } elseif (strpos($data, "forced_channel_delete:") === 0) {
                // MODIFICATION: Add answer call before deletion logic
                answer($callback["id"], "جارٍ الحذف...");
                
                $index = explode(":", $data)[1];
                if (isset($forced_channels[$index])) {
                    $deleted_channel = $forced_channels[$index]['username'];
                    unset($forced_channels[$index]);
                    $forced_channels = array_values($forced_channels); // إعادة ترقيم المصفوفة
                    if (save_data('forced_channels', $forced_channels) === false) {
                        error_log("Failed to delete forced channel: " . $deleted_channel);
                    }
                    send($cid, "✅ تم حذف القناة @$deleted_channel بنجاح");
                    deleteMessage($callback["message"]["chat"]["id"], $callback["message"]["message_id"]); // تم إضافة هذا السطر
                    // إعادة عرض القائمة المحدثة
                    if (empty($forced_channels)) {
                        send($cid, "❌ لا توجد قنوات مسجلة حالياً.");
                    } else {
                        $buttons = [];
                        foreach ($forced_channels as $idx => $channel) {
                            $buttons[] = [
                                ["text" => $channel['username'], "callback_data" => "show_channel:$idx"],
                                ["text" => "🗑️ حذف", "callback_data" => "forced_channel_delete:$idx"]
                            ];
                        }
                        $buttons[] = [["text" => "🔙 رجوع", "callback_data" => "forced_channels_back"]];
                        send($cid, "📋 قائمة القنوات الإجبارية (محدّثة):", true, $buttons);
                    }
                }
            } elseif (strpos($data, "show_channel:") === 0) {
                $index = explode(":", $data)[1];
                if (isset($forced_channels[$index])) {
                    $channel = $forced_channels[$index];
                    answer($callback["id"], "القناة: @{$channel['username']}");
                }
            }
            answer($callback["id"], "تمت المعالجة");
        }
        // MODIFICATION: Handle cancel callback
        elseif ($data == "cancel_current_action") {
            answer($callback["id"], "تم إلغاء العملية.");
            delStep($uid);
            // Return to admin menu if admin, otherwise main menu
            if (in_array($uid, $admins)) {
                handle_update_logic("/admin", null, $cid, $uid, null); // Pass null for callback if text command
            } else {
                handle_update_logic("/start", null, $cid, $uid, null);
            }
            return;
        }
    }

    // معالجة الخطوات
    elseif ($step = getStep($uid)) {
        // انتظار إدخال ID اللعبة
        if (strpos($step, "wait_game_id:") === 0) {
            if (!is_numeric($text)) {
                send($cid, "❌ يجب إدخال أرقام فقط. الرجاء المحاولة مرة أخرى من البداية.");
                delStep($uid);
                return;
            }
            list(, $game, $pack) = explode(":", $step);
            $price = $prices[$pack];
            send($cid, "♕ تفاصيل الطلب ♕ :
✽ اللعبة: $game
✽ الفئة: $pack
✽ السعر: $price ل.س
ID الحساب: $text
يرجى التأكد من الآيدي والضغط على تاكيد الطلب ", true, [
                [["text" => " تأكيد الطلب ", "callback_data" => "confirm_order:$game:$pack:$text"]],
                [["text" => " إلغاء الطلب ", "callback_data" => "cancel_order"]]
            ]);
            delStep($uid);
        }
        // انتظار رقم التحويل (الجديد)
        elseif ($step == "wait_trans_id") {
            if (!is_numeric($text)) {
                send($cid, "❌ يجب إدخال أرقام فقط. الرجاء المحاولة مرة أخرى من البداية.");
                delStep($uid);
                return;
            }
            // استخدام المسار المطلق لـ data_trans
            if (file_put_contents(BASE_DIR . "data_trans/{$uid}_trans_id.txt", $text) === false) {
                error_log("Failed to save trans_id for user: " . $uid);
                send($cid, "❌ حدث خطأ داخلي. يرجى المحاولة مرة أخرى.");
                delStep($uid);
                return;
            }
            saveStep($uid, "wait_amount");
            // MODIFICATION: Add cancel button
            send($cid, "الرجاء ادخال المبلغ ( بالارقام فقط ) ", true, [[["text" => "إلغاء ❌", "callback_data" => "cancel_current_action"]]]);
        }
        // انتظار المبلغ المحول (الجديد)
        elseif ($step == "wait_amount") {
            if (!is_numeric($text)) {
                send($cid, "❌ يجب إدخال أرقام فقط. الرجاء المحاولة مرة أخرى من البداية.");
                delStep($uid);
                return;
            }
            // استخدام المسار المطلق لـ data_trans
            $trans_id_file = BASE_DIR . "data_trans/{$uid}_trans_id.txt";
            if (!file_exists($trans_id_file)) {
                send($cid, "❌ حدث خطأ: لا يمكن العثور على رقم التحويل. يرجى البدء من جديد.");
                delStep($uid);
                return;
            }
            $trans_id = file_get_contents($trans_id_file);
            // استخدام المسار المطلق لـ data_trans
            if (file_put_contents(BASE_DIR . "data_trans/transaction_$trans_id.json", json_encode([
                "user_id" => $uid,
                "amount" => $text,
                "status" => "pending",
                "timestamp" => time()
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
                error_log("Failed to save transaction file for trans_id: " . $trans_id);
                send($cid, "❌ حدث خطأ داخلي عند تسجيل المعاملة. يرجى المحاولة مرة أخرى.");
                delStep($uid);
                return;
            }
            
            // حذف ملف trans_id المؤقت
            if (!unlink($trans_id_file)) {
                error_log("Failed to delete temporary trans_id file: " . $trans_id_file);
            }

            delStep($uid); // مسح الخطوة بعد الحصول على المبلغ
            
            send($admin_id, "💰 طلب شحن رصيد جديد:
    ⨗ من المستخدم: $uid
    ⨗ رقم عملية التحويل: `$trans_id`
    ⨗ المبلغ: $text ل.س", true, [
                [["text" => "✅ إضافة الرصيد", "callback_data" => "add:$uid:$text"]],
                [["text" => "❌ رفض", "callback_data" => "deny:$uid:$text"]]
            ]);
            send($cid, "✅ تم إرسال طلبك بنجاح. يرجى الانتظار ليتم التحقق منه من قبل الإدارة.");
        }

        // انتظار اسم المستخدم للقناة الإجبارية
        elseif ($step == "wait_channel_username") {
            if (empty($text) || strpos($text, '@') !== 0) {
                send($cid, "❌ يرجى إرسال معرف القناة بالصيغة الصحيحة (مثال: @channel).");
                return;
            }
            // تحقق من أن القناة غير مضافة مسبقًا
            $channel_exists = false;
            foreach ($forced_channels as $channel) {
                if ($channel['username'] == $text) {
                    $channel_exists = true;
                    break;
                }
            }
            if ($channel_exists) {
                send($cid, "❌ هذه القناة مضافة بالفعل.");
                delStep($uid);
                return;
            }
            $forced_channels[] = ['username' => $text, 'added_by' => $uid, 'timestamp' => time()];
            if (save_data('forced_channels', $forced_channels) === false) {
                error_log("Failed to add forced channel: " . $text);
                send($cid, "❌ حدث خطأ عند إضافة القناة. يرجى المحاولة مرة أخرى.");
                delStep($uid);
                return;
            }
            send($cid, "✅ تم إضافة القناة $text بنجاح إلى قائمة الاشتراك الإجباري.");
            delStep($uid);
        }

        // انتظار اسم المستخدم للحظر
        elseif ($step == "ban_user") {
            if (!is_numeric($text)) {
                send($cid, "❌ يرجى إرسال ID المستخدم فقط.");
                return;
            }
            if (!in_array($text, $banned)) {
                $banned[] = $text;
                if (save_data('banned', $banned) === false) {
                    error_log("Failed to ban user: " . $text);
                }
                send($cid, "✅ تم حظر المستخدم $text بنجاح.");
            } else {
                send($cid, "❌ المستخدم $text محظور بالفعل.");
            }
            delStep($uid);
        }

        // انتظار اسم المستخدم لفك الحظر
        elseif ($step == "unban_user") {
            if (!is_numeric($text)) {
                send($cid, "❌ يرجى إرسال ID المستخدم فقط.");
                return;
            }
            if (($key = array_search($text, $banned)) !== false) {
                unset($banned[$key]);
                $banned = array_values($banned); // إعادة ترقيم المصفوفة
                if (save_data('banned', $banned) === false) {
                    error_log("Failed to unban user: " . $text);
                }
                send($cid, "✅ تم فك حظر المستخدم $text بنجاح.");
            } else {
                send($cid, "❌ المستخدم $text غير محظور.");
            }
            delStep($uid);
        }

        // انتظار ID المستخدم لإضافة/خصم الرصيد
        elseif (strpos($step, "credit_user:") === 0) {
            $parts = explode(":", $step);
            $action = $parts[1]; // 'add' or 'deduct'
            $target_uid = $text; // Get the user ID from the text
            
            if (!is_numeric($target_uid)) {
                send($cid, "❌ يرجى إدخال معرف المستخدم (ID) بالأرقام فقط.");
                return;
            }

            saveStep($uid, "credit_amount:$action:$target_uid");
            // MODIFICATION: Add cancel button
            send($cid, "الرجاء إدخال المبلغ (بالليرة السورية):", true, [[["text" => "إلغاء ❌", "callback_data" => "cancel_current_action"]]]);
        }

        // انتظار المبلغ لإضافة/خصم الرصيد
        elseif (strpos($step, "credit_amount:") === 0) {
            $parts = explode(":", $step);
            $action = $parts[1];
            $target_uid = $parts[2];
            $amount = intval($text);

            if (!is_numeric($text) || $amount <= 0) {
                send($cid, "❌ يرجى إدخال مبلغ صحيح (أرقام فقط وموجب).");
                return;
            }

            if (!isset($balances[$target_uid])) {
                $balances[$target_uid] = ["balance" => 0, "spend" => 0];
            }

            if ($action == "add") {
                $balances[$target_uid]["balance"] += $amount;
                send($target_uid, "✅ تم إضافة $amount ل.س إلى رصيدك من قبل الإدارة.");
                send($cid, "✅ تم إضافة $amount ل.س إلى رصيد المستخدم $target_uid.");
            } elseif ($action == "deduct") {
                if ($balances[$target_uid]["balance"] < $amount) {
                    send($cid, "❌ رصيد المستخدم $target_uid غير كاف لخصم $amount ل.س.");
                    delStep($uid);
                    return;
                }
                $balances[$target_uid]["balance"] -= $amount;
                send($target_uid, "⚠️ تم خصم $amount ل.س من رصيدك من قبل الإدارة.");
                send($cid, "✅ تم خصم $amount ل.س من رصيد المستخدم $target_uid.");
            }
            if (save_data('balances', $balances) === false) {
                error_log("Failed to update balances for user: " . $target_uid);
            }
            delStep($uid);
        }

        // انتظار السعر الجديد لتعديل الأسعار
        elseif (strpos($step, "price|") === 0) {
            $pack_name = explode("|", $step)[1];
            if (!is_numeric($text) || intval($text) <= 0) {
                send($cid, "❌ يرجى إدخال سعر صحيح (أرقام فقط وموجب).");
                return;
            }
            $new_price = intval($text);
            $prices[$pack_name] = $new_price;
            if (save_data('prices', $prices) === false) {
                error_log("Failed to update prices file.");
            }
            send($cid, "✅ تم تحديث سعر $pack_name إلى $new_price ل.س بنجاح.");
            delStep($uid);
        }
        // انتظار رقم الكاش الجديد
        elseif ($step == "change_cash_number") {
            if (!is_numeric($text)) {
                send($cid, "❌ يرجى إدخال رقم كاش صحيح (أرقام فقط).");
                return;
            }
            if (save_data('cash', $text) === false) {
                error_log("Failed to update CASH_FILE.");
            }
            send($cid, "✅ تم تحديث رقم الكاش إلى $text بنجاح.");
            delStep($uid);
        }

        // انتظار رسالة الإذاعة
        elseif ($step == "broadcast_message") {
            // إرسال الرسالة إلى جميع المستخدمين
            $sent_count = 0;
            foreach ($users as $user_id) {
                send($user_id, "📢 رسالة من الإدارة:\n" . $text);
                $sent_count++;
            }
            send($cid, "✅ تم إرسال رسالة الإذاعة إلى $sent_count مستخدم.");
            delStep($uid);
        }

        // انتظار ID المستخدم لإضافة الأدمن
        elseif ($step == "add_admin") {
            if (!is_numeric($text)) {
                send($cid, "❌ يرجى إدخال ID المستخدم (أرقام فقط).");
                return;
            }
            if (!in_array($text, $admins)) {
                $admins[] = $text;
                if (save_data('admins', $admins) === false) {
                    error_log("Failed to add admin: " . $text);
                }
                send($cid, "✅ تم إضافة المستخدم $text كأدمن بنجاح.");
            } else {
                send($cid, "❌ المستخدم $text هو أدمن بالفعل.");
            }
            delStep($uid);
        }

        // انتظار ID المستخدم لحذف الأدمن
        elseif ($step == "delete_admin") {
            if (!is_numeric($text)) {
                send($cid, "❌ يرجى إدخال ID المستخدم (أرقام فقط).");
                return;
            }
            if (($key = array_search($text, $admins)) !== false) {
                unset($admins[$key]);
                $admins = array_values($admins); // إعادة ترقيم المصفوفة
                if (save_data('admins', $admins) === false) {
                    error_log("Failed to delete admin: " . $text);
                }
                send($cid, "✅ تم حذف المستخدم $text من قائمة الأدمن بنجاح.");
            } else {
                send($cid, "❌ المستخدم $text ليس أدمن.");
            }
            delStep($uid);
        }
        // معالجة إضافة عدة قنوات إجبارية
        elseif ($step == "wait_multiple_channels") {
            $channel_usernames = explode("\n", $text);
            $added_count = 0;
            $failed_channels = [];

            foreach ($channel_usernames as $username) {
                $username = trim($username);
                if (empty($username) || strpos($username, '@') !== 0) {
                    $failed_channels[] = $username . " (صيغة غير صحيحة)";
                    continue;
                }

                // تحقق من أن القناة غير مضافة مسبقًا
                $channel_exists = false;
                foreach ($forced_channels as $channel) {
                    if ($channel['username'] == $username) {
                        $channel_exists = true;
                        break;
                    }
                }

                if ($channel_exists) {
                    $failed_channels[] = $username . " (مضافة بالفعل)";
                } else {
                    $forced_channels[] = ['username' => $username, 'added_by' => $uid, 'timestamp' => time()];
                    $added_count++;
                }
            }

            // حفظ التغييرات بعد معالجة جميع القنوات
            if ($added_count > 0) {
                if (save_data('forced_channels', $forced_channels) === false) {
                    error_log("Failed to update forced channels file after adding multiple.");
                    send($cid, "❌ حدث خطأ عند حفظ بعض القنوات. يرجى التحقق من السجلات.");
                }
            }

            $response_message = "✅ تم إضافة $added_count قناة بنجاح.";
            if (!empty($failed_channels)) {
                $response_message .= "\n\n❌ فشل إضافة القنوات التالية:\n" . implode("\n", $failed_channels);
            }
            send($cid, $response_message);
            delStep($uid);
        }
    }

    // MODIFICATION: Handle /cancel text command
    if ($text == "/cancel") {
        delStep($uid);
        send($cid, "تم إلغاء العملية.", false);
        // Return to main menu (or admin menu if admin)
        if (in_array($uid, $admins)) {
            handle_update_logic("/admin", null, $cid, $uid, null); // Pass null for callback if text command
        } else {
            handle_update_logic("/start", null, $cid, $uid, null);
        }
        return;
    }

    // معالجة الأوامر من لوحة الأدمن (الأوامر النصية التي تؤدي لطلب خطوات لاحقة)
    if ($text == "➕ إضافة رصيد") {
        if (!in_array($uid, $admins)) {
            send($cid, "⛔ ليس لديك صلاحية تنفيذ هذا الأمر!");
            return;
        }
        saveStep($uid, "credit_user:add");
        // MODIFICATION: Add cancel button
        send($cid, "الرجاء إدخال ID المستخدم الذي تريد إضافة الرصيد له:", true, [[["text" => "إلغاء ❌", "callback_data" => "cancel_current_action"]]]);
    }
    elseif ($text == "➖ خصم رصيد") {
        if (!in_array($uid, $admins)) {
            send($cid, "⛔ ليس لديك صلاحية تنفيذ هذا الأمر!");
            return;
        }
        saveStep($uid, "credit_user:deduct");
        // MODIFICATION: Add cancel button
        send($cid, "الرجاء إدخال ID المستخدم الذي تريد خصم الرصيد منه:", true, [[["text" => "إلغاء ❌", "callback_data" => "cancel_current_action"]]]);
    }
    elseif ($text == "💵 تعديل الأسعار") {
        if (!in_array($uid, $admins)) {
            send($cid, "⛔ ليس لديك صلاحية تنفيذ هذا الأمر!");
            return;
        }
        $price_buttons = [];
        foreach ($prices as $name => $price) {
            $price_buttons[] = [["text" => "$name ($price ل.س)", "callback_data" => "setprice:$name"]];
        }
        // MODIFICATION: Add cancel button to price selection menu
        $price_buttons[] = [["text" => "إلغاء ❌", "callback_data" => "cancel_current_action"]];
        send($cid, "اختر الحزمة التي تريد تعديل سعرها:", true, $price_buttons);
    }
    elseif ($text == "🔁 تغيير رقم الكاش") {
        if (!isMainAdmin($uid)) {
            send($cid, "⛔ ليس لديك صلاحية تنفيذ هذا الأمر!");
            return;
        }
        saveStep($uid, "change_cash_number");
        // MODIFICATION: Add cancel button
        send($cid, "الرجاء إرسال رقم الكاش الجديد:", true, [[["text" => "إلغاء ❌", "callback_data" => "cancel_current_action"]]]);
    }
    elseif ($text == "📢 إرسال إذاعة") {
        if (!in_array($uid, $admins)) {
            send($cid, "⛔ ليس لديك صلاحية تنفيذ هذا الأمر!");
            return;
        }
        saveStep($uid, "broadcast_message");
        // MODIFICATION: Add cancel button
        send($cid, "الرجاء إرسال رسالة الإذاعة:", true, [[["text" => "إلغاء ❌", "callback_data" => "cancel_current_action"]]]);
    }
    elseif ($text == "🚫 حظر مستخدم") {
        if (!in_array($uid, $admins)) {
            send($cid, "⛔ ليس لديك صلاحية تنفيذ هذا الأمر!");
            return;
        }
        saveStep($uid, "ban_user");
        // MODIFICATION: Add cancel button
        send($cid, "الرجاء إرسال ID المستخدم الذي تريد حظره:", true, [[["text" => "إلغاء ❌", "callback_data" => "cancel_current_action"]]]);
    }
    elseif ($text == "✅ فك حظر مستخدم") {
        if (!in_array($uid, $admins)) {
            send($cid, "⛔ ليس لديك صلاحية تنفيذ هذا الأمر!");
            return;
        }
        saveStep($uid, "unban_user");
        // MODIFICATION: Add cancel button
        send($cid, "الرجاء إرسال ID المستخدم الذي تريد فك حظره:", true, [[["text" => "إلغاء ❌", "callback_data" => "cancel_current_action"]]]);
    }
    elseif ($text == "📊 إحصائيات البوت") {
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
        send($cid, $message, true);
    }
    elseif ($text == "👨‍💼 إضافة أدمن") {
        if (!isMainAdmin($uid)) {
            send($cid, "⛔ ليس لديك صلاحية تنفيذ هذا الأمر!");
            return;
        }
        saveStep($uid, "add_admin");
        // MODIFICATION: Add cancel button
        send($cid, "الرجاء إرسال ID المستخدم الذي تريد إضافته كأدمن:", true, [[["text" => "إلغاء ❌", "callback_data" => "cancel_current_action"]]]);
    }
    elseif ($text == "👨‍💼 حذف أدمن") {
        if (!isMainAdmin($uid)) {
            send($cid, "⛔ ليس لديك صلاحية تنفيذ هذا الأمر!");
            return;
        }
        saveStep($uid, "delete_admin");
        // MODIFICATION: Add cancel button
        send($cid, "الرجاء إرسال ID المستخدم الذي تريد حذفه من قائمة الأدمن:", true, [[["text" => "إلغاء ❌", "callback_data" => "cancel_current_action"]]]);
    }
    elseif ($text == "📢 إدارة الاشتراك الإجباري") {
        if (!isMainAdmin($uid)) {
            send($cid, "⛔ ليس لديك صلاحية الوصول لهذه الميزة");
            return;
        }
        // MODIFICATION: Add cancel button to this menu as well
        send($cid, "اختر الإجراء المطلوب لإدارة الاشتراك الإجباري:", true, [
            [["text" => "➕ إضافة قناة", "callback_data" => "forced_channels_add"]],
            [["text" => "➖ حذف قناة", "callback_data" => "forced_channels_remove"]],
            [["text" => "➕ إضافة عدة قنوات", "callback_data" => "forced_channels_add_multi"]],
            [["text" => "🔙 رجوع", "callback_data" => "forced_channels_back"]],
            [["text" => "إلغاء ❌", "callback_data" => "cancel_current_action"]] // Added cancel here
        ]);
    }
    elseif ($text == "⏹️ إيقاف البوت") {
        if (!in_array($uid, $admins)) {
            send($cid, "⛔ ليس لديك صلاحية تنفيذ هذا الأمر!");
            return;
        }
        
        $bot_status = 'off'; // يتم تعديل هذا المتغير العالمي مباشرة
        send($cid, "⏹️ تم إيقاف البوت بنجاح", true, [
            [["text" => "▶️ تشغيل البوت", "callback_data" => "toggle_bot_on"]]
        ]);
    }
    elseif ($text == "▶️ تشغيل البوت") {
        if (!in_array($uid, $admins)) {
            send($cid, "⛔ ليس لديك صلاحية تنفيذ هذا الأمر!");
            return;
        }
        
        $bot_status = 'on'; // يتم تعديل هذا المتغير العالمي مباشرة
        send($cid, "✅ تم تشغيل البوت بنجاح", true, [
            [["text" => "⏹️ إيقاف البوت", "callback_data" => "toggle_bot_off"]]
        ]);
    }
    // معالجة إضافة عدة قنوات إجبارية (الزر الجديد)
    elseif ($data == "forced_channels_add_multi") {
        if (!isMainAdmin($uid)) {
            answer($callback["id"], "⛔ ليس لديك صلاحية الوصول لهذه الميزة");
            return;
        }
        saveStep($uid, "wait_multiple_channels");
        // MODIFICATION: Add cancel button
        send($cid, "أرسل معرفات القنوات (مثال: @channel1\\n@channel2) كل قناة في سطر جديد:", true, [[["text" => "إلغاء ❌", "callback_data" => "cancel_current_action"]]]);
        answer($callback["id"], "الرجاء إدخال القنوات المتعددة.");
    }
    // معالجة الأوامر غير المعروفة
    // تم حذف هذا الجزء للسماح بالمرونة
}

// البدء في معالجة التحديث
handle_update_logic($text, $data, $cid, $uid, $callback);
