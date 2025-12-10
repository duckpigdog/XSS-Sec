# Standalone Admin (PHP)

- 位置：`challenges/16_github/admin/`
- 无数据库，使用固定数据与凭据
- 登录凭据：`admin / Admin@123` 或 `analyst / Analyst@123`

## 运行

1. 进入项目根目录
2. 启动 PHP 内置服务器并指定文档根目录：

```bash
php -S localhost:8080 -t challenges/16_github/admin
```

3. 打开浏览器访问 `http://localhost:8080/`

## 结构

- `index.php` 入口，按登录状态跳转
- `login.php` 登录页，固定凭据
- `logout.php` 退出并清理会话
- `dashboard.php` 仪表盘
- `users.php` 用户列表，支持添加与删除（会话内临时数据）
- `config.php` 固定凭据与固定数据
- `bootstrap.php` 会话初始化与数据注入
- `auth_guard.php` 受保护页面的登录校验
- `assets/style.css` 基础样式
