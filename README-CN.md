# XSS-Sec 靶场项目

本项目是一个以“实战为导向”的 XSS 漏洞练习靶场，覆盖反射型、存储型、DOM 型、SVG、CSP、框架注入、协议绕过等多种场景。页面样式统一，逻辑清晰，适合系统化学习与教学演示。

![](1.png)
![](2.png)
![](3.png)

## 快速开始

### 1. 使用 Docker Compose（推荐）
您可以使用 Docker 通过一条命令运行完整的实验环境。这能确保所有依赖都已满足，并提供良好的隔离性。

**启动环境：**
在项目根目录下运行以下命令：
```bash
docker compose up -d
```
- **访问靶场：** 在浏览器中打开 `http://localhost:8080/index.php`。
- **端口：** 应用默认运行在 `8080` 端口。

**停止与数据管理：**
请务必了解“停止”与“重置”的区别：

*   **停止容器但保存进度：**
    ```bash
    docker compose down
    ```
    此命令停止容器，但**保留您的数据**（如存储型 XSS 的评论、上传的文件）在 Docker 数据卷中。如果您只是暂停练习，请使用此命令。

*   **停止容器并重置所有数据：**
    ```bash
    docker compose down -v
    ```
    此命令停止容器并**删除所有数据**（清空数据卷）。实验环境将恢复到初始的干净状态。如果您想从头开始挑战，请使用此命令。

### 2. 使用预构建镜像
如果您不想从源码构建（例如用于快速测试），可以直接拉取预构建的镜像：
```bash
docker pull suc2es2/xss-sec:latest
```

使用以下命令快速运行：
```bash
docker run -d -p 8080:80 suc2es2/xss-sec:latest
```

### 3. 手动搭建（旧方式）
- **依赖：** 建议安装 PHP 7+（或更高），Chrome（部分关卡仅在 Chrome 下按预期生效）
- **启动（内置服务器示例）：**
  - 在终端切换到项目根目录（示例路径：`d:\Book\XSS-Sec`）
  - 运行 PHP 内置服务器：`php -S 127.0.0.1:3000`
  - 浏览器访问：`http://127.0.0.1:3000/index.php`
- **其他：** 也可使用 Nginx/Apache 指向项目根目录（确保 .php 可执行）

## 项目结构
- `index.php`：首页与分页，列出所有关卡并跳转
- `headers.php`：全局 HTTP 响应头（为教学目的有意弱化/调整安全策略）
- `assets/`：前端资源（`style.css`、`angular.min.js`、`admin-bot.js` 等）
- `levelXX/`：各关卡目录（每关一个 `index.php`）
- `writeup.md`：按关卡记录的代码审计与通关思路

## 重要提示
- 安全性：为教学目的，部分页面刻意设置为“可被利用”，请勿在生产环境部署
- 浏览器兼容：个别关卡依赖特定浏览器行为（如 Level 34 在 Chrome 下）
- CSP 与 CORS：`headers.php` 中设置了统一的实验性响应头，便于练习与联动演示

## 关卡总览（名称与简介）
- Level 1: Reflected XSS — The basics.
- Level 2: DOM-based XSS — Client-side manipulation.
- Level 3: Stored XSS — Persistent payloads.
- Level 4: Attribute Breakout — Escape the attribute.
- Level 5: Filter Bypass — No <script> allowed.
- Level 6: Quote Filtering — Break out of single quotes.
- Level 7: Keyword Removal — Double write bypass.
- Level 8: Encoding Bypass — HTML entities are your friend.
- Level 9: URL Validation — Must contain http://
- Level 10: Protocol Bypass — Case sensitivity matters.
- Level 11: JS Context — Break out of JS string.
- Level 12: DOM XSS via Hash — The server sees nothing.
- Level 13: Frontend Filter — Bypass the regex.
- Level 14: Double Encoding — Double the trouble.
- Level 15: Framework Injection — AngularJS Template Injection.
- Level 16: PostMessage XSS — Talk to the parent.
- Level 17: CSP Bypass — Strict CSP? Find a gadget.
- Level 18: Anchor Href XSS — Stored XSS in href.
- Level 19: DOM XSS in Select — Break out of select.
- Level 20: jQuery Anchor XSS — DOM XSS in jQuery attr().
- Level 21: JS String Reflection — Reflected XSS in JS string.
- Level 22: Reflected DOM XSS — Server reflection + Client sink.
- Level 23: Stored DOM XSS — Replace only once.
- Level 24: WAF Bypass (Tags/Attrs) — Reflected XSS with strict WAF.
- Level 25: SVG Animate XSS — SVG-specific vector bypass.
- Level 26: Canonical Link XSS — Escaping single quotes issue.
- Level 27: Stored XSS in onclick — Entities vs escaping pitfall.
- Level 28: Template Literal XSS — Reflected into JS template string.
- Level 29: Cookie Exfiltration — Stored XSS steals session cookie.
- Level 30: Angular Sandbox Escape — No strings, escape Angular sandbox.
- Level 31: AngularJS CSP Escape — Bypass CSP and escape Angular sandbox.
- Level 32: Reflected XSS (href/events blocked) — Bypass via SVG animate to set href.
- Level 33: JS URL XSS (chars blocked) — Reflected XSS in javascript: URL with chars blocked.
- Level 34: CSP Bypass (report-uri token) — Chrome-only CSP directive injection via report-uri.
- Level 35: Upload Path URL XSS — Independent lab: upload HTML, random rename, URL concat XSS.
- Level 36: Hidden Adurl Reflected XSS — Independent lab: hidden ad anchor reflects adurl/adid.
- Level 37: Data URL Base64 XSS — Blacklist filter; must use data:text/html;base64 in object.
- Level 38: PDF Upload XSS — Independent lab: upload PDF, view opens HTML-in-PDF causing XSS.
- Level 39: Regex WAF Bypass — src/="data:..." bypasses WAF regex.
- Level 40: Bracket String Bypass — href reflects; use window["al"+"ert"] to evade WAF.
- Level 41: Fragment Eval/Window Bypass — Echo HTML; split strings then eval or window[a+b].
- Level 42: Login DB Error XSS — Independent lab: invalid DB shows error, SQL reflects username.
- Level 43: Chat Agent Link XSS — Independent lab: chat echoes, agent clicks user link executes.
- Level 44: CSS Animation Event XSS — Strong WAF: only @keyframes+xss onanimationend allowed.
- Level 45: RCDATA Textarea Breakout XSS — Strong WAF: only textarea/title RCDATA breakout works.
- Level 46: JS String Escape (eval) — theme string injection; escape with eval(myUndefVar); alert(1);
- Level 47: Throw onerror comma XSS — Strong WAF: only throw onerror=alert,cookie
- Level 48: Symbol.hasInstance Bypass — Strong WAF: only instanceof+eval chain
- Level 49: Video Source onerror XSS — Strong WAF: only video source onerror
- Level 50: Bootstrap RealSite XSS — Independent site: only xss onanimationstart

## 学习建议
- 每一关都包含一个明确的“输入点”与“执行点（Sink）”，建议先阅读源码再尝试利用
- 结合 `writeup.md` 中的审计记录复盘要点，理解过滤逻辑缺陷与浏览器解析特性
- 逐步增加约束（字符黑名单、标签过滤、CSP 等），练习不同绕过技巧

## 致谢与用途声明
- 本项目仅用于安全研究与教学演示，切勿将其中技巧应用于未授权的系统
- 如需在课堂或培训中使用，可在 `writeup.md` 基础上扩展自己的解题过程
