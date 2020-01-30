<?php
namespace app\edu\controller;
class Test{
	public function demo1()
	{
		return '娃哈哈';
	} 
	public function demo2()
	{
		return '哈哈娃';
	} 
	public function test($name,$age)
	{
		return $name.$age;
	} 
	public function demo3($id)
	{
		return 'demo3'.$id;
	} 
	public function demo4($name)
	{
		return 'demo4'.$name;
	} 
	public function demo5($isok)
	{
		return 'demo5'.$isok;
	} 
	public function add($n,$m)
  {
    return $n.'+'.$m.'='.($n+$m);
  }
    
}
