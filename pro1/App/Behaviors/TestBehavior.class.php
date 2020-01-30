<?php
namespace Behaviors;
class TestBehavior extends \Think\Behavior {
function run(&$arg) {
echo "hello,behavior";
}
}
?>