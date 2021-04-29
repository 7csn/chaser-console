## 控制台组件

该组件可以创建命令行命令，用于任何反复（执行）的任务，如定时任务，或其他批处理工作。

### 运行环境

- PHP >= 8.0

### 安装

```
composer require 7csn/console
```

### 快速入门

* 脚本文件（index.php）

  ```php
  #!/usr/bin/env php
  <?php
  
  use app\console\DemoCommand;
  use chaser\console\Application;
  
  require __DIR__ . '/vendor/autoload.php';
  
  $app = new Application();
  
  // 添加用户自定义命令对象（命令名 => demo）
  if (class_exists(DemoCommand::class)) {
      $app->add(new DemoCommand());
  }
  
  $code = $app->run(); # 0 ~ 255
  
  exit($code);
  ```
  
  > 命令行输入回车：
  > * php index.php command parameters options -- raw_parameters
  > 
  > 说明：
  > * command &emsp;&emsp;&emsp;&nbsp; 调用的命令名称
  > * parameter &emsp;&emsp;&emsp; 命令位置参数值，不能以”-“开头
  > * option &emsp;&emsp;&emsp;&emsp;&emsp; 选项（--名称、-快捷方式）及值（不能以”-“开头）
  > * raw_parameter &emsp; 同 parameter，但可以”-“开头

  无参运行：
  ```shell
  > php index.php
  ```

* 消息输出设置

  > 应用程序选项，名称为”output“，快捷方式为”o“，对所有命令有效
  >
  > 取值（默认 0）：0（装饰标签）、1（去除标签）、2（原样输出）、3（不输出）
  >
  > 使用：
  > * --output=值
  > * --output 值
  > * -o 值

  示例：
  ```shell
  > php index.php -o 1
  ```

* 列出命令：list

  > 应用程序默认命令，用于列出指定前缀名的命令

  示例：
  ```shell
  # 列出全部命令
  > php index.php list
    
  # 列出以“demo”开头的命令
  > php index.php list demo
  ```

* 查看命令：help

  > 应用程序默认命令，用于查看指定命令的详情

  示例：
  ```shell
  # 查看 help 命令自身详情
  > php index.php help
    
  # 查看 demo 命令详情
  > php index.php help demo
  ```

* 自定义命令（DemoCommand）

  ```php
  <?php
  
  namespace app\console;
  
  use chaser\console\argument\Option;
  use chaser\console\argument\Parameter;
  use chaser\console\command\Command;
  use chaser\console\input\InputInterface;
  use chaser\console\output\OutputInterface;
  
  class DemoCommand extends Command
  {
      /**
       * 返回命令默认名称
       *
       * @return string
       */
      public static function getDefaultName(): string
      {
          return 'demo';
      }
  
      /**
       * 返回命令默认描述
       *
       * @return string
       */
      public static function getDefaultDescription(): string
      {
          return 'This is a demo command';
      }
  
      /**
       * 返回命令输入定义参数组
       *
       * @return <Parameter|Option>[]
       */
      public function getArguments(): array
      {
          return [];
      }
  
      /**
       * 运行命令
       *
       * @param InputInterface $input
       * @param OutputInterface $output
       * @return int
       */
      public function run(InputInterface $input, OutputInterface $output): int
      {
          return 0;
      }
  }
  ```

* 自定义命令 - 输入定义参数组

  > 定义参数组为输入定义参数索引数组，输入定义参数分为：Parameter（位置参数）、Option（选项）
  >  
  > 其中 Parameter 之间严格按照顺序，Option 不作顺序要求

  * 位置参数（Parameter）
  
    ```php
    public function __construct(string $name, int $mode = 0, string $description = '', string ...$defaults){}
    ```
    
    说明：
    > * name 参数名称
    >   * 不能为空字符串，不能含空字符 
    
    > * mode 取值模式
    >   * 0&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;&nbsp;&nbsp;可选择是否提供值
    >   * Parameter::REQUIRED&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;&nbsp;&nbsp;必须提供值
    >   * Parameter::COMPLEX&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;复合值（可多值）
    >   * Parameter::COMPLEX | Parameter::REQUIRED&emsp;复合值、必须提供值
  
    > * description 命令简介
    
    > * defaults 默认值列表
        
    注意：
    >   * 含 REQUIRED 模式，不能设置默认值
    >   * 含 COMPLEX 模式，默认值才能设置多个
    >   * 参数值不建议以”-“开头
  
  * 选项（Option）
  
    ```php
    public function __construct(string $name, ?string $shortcut = null, int $mode = 0, string $description = '', string ...$defaults){}
    ```
  
    说明：
    > * name 参数名称
        >   * 不能为空字符串，不能含空字符
  
    > * shortcut 快捷方式
        >   * 单字符，不能为空字符，不能为“-”
  
    > * mode 取值模式
    >   * 0&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;不取值，只用于判断是否含该选项
    >   * Option::REQUIRED&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;必须提供值
    >   * Option::OPTIONAL&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;&nbsp;&nbsp;&nbsp;可选择是否提供值
    >   * Option::COMPLEX | Option::REQUIRED&emsp;复合值（可多值）、必须提供值
    >   * Option::COMPLEX | Option::OPTIONAL&emsp;&nbsp;复合值（可多值）、可选择是否提供值
  
    > * description 命令说明
  
    > * defaults 默认值列表
  
    注意：
    >   * 含 REQUIRED 模式，不能设置默认值
    >   * 含 COMPLEX 模式，默认值才能设置多个
    >   * 含 OPTIONAL 模式，必须设置默认值；反之，必不能设置默认值

* 自定义命令 - 运行命令函数

  ```php
  public function run(InputInterface $input, OutputInterface $output): int{}
  ```
  简单使用：
  
  > * 输入实体
  >   ```php
  >   # 获取输入实体（检测参数组是否定义合理、参数提供的值是否合理，异常则报错）
  >   $concrete = $this->getConcrete($input);
  >   ```

  > * 位置参数
  >
  >   ```php
  >   # 判断是否有位置参数值
  >   $concrete->hasParameter('位置参数名');
  >   
  >   # 获取位置参数值
  >   $concrete->getParameter('位置参数名');
  >   ```

  > * 选项
  >
  >   ```php
  >   # 判断是否有选项
  >   $concrete->hasOption('选项名');
  >
  >   # 获取选项值
  >   $concrete->hasOption('选项名');    
  >   ```

  > * 消息输出
  >
  >   ```php
  >   $output->write('这是普通消息');
  >   $output->write(PHP_EOL);
  >   
  >   $output->write('这是信息标签样式：<info>信息</info>');
  >   $output->write(PHP_EOL);
  >   
  >   $output->write('这是注释标签样式：<comment>注释</comment>');
  >   $output->write(PHP_EOL);
  >   
  >   $output->write('这是错误标签样式：<error>错误</error>');
  >   $output->write(PHP_EOL);
  >   
  >   $output->write(sprintf(
  >       '<css href="%s" fg="%s" bg="%s" options="%s">%s</css>',
  >       'http://baidu.com', 'red', 'green', 'highlight,underline', '自定义标签：携带超链接、红字、绿底、高亮、下划线'
  >   ));
  >   $output->write(PHP_EOL);
  >   
  >   $output->writeln('在 write 的基础上换行');
  >   $output->writeln('输出消息并换 2 行', 2);