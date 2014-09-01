<?php


function check($expr) {

	// 先頭が乗除演算子の場合
	if (preg_match('/^\s*[\*\/]/', $expr)) {
		echo "invalid 1\n";
		exit; 
	}
	// 数字の間にスペースがある場合
	if (preg_match('/[0-9][\s\(\)][0-9]/', $expr)) {
		echo "invalid 2\n";
		exit; 
	}
	// 演算子が続く場合に乗除演算子が後に付く場合
	if (preg_match('/[\+\-\*\/][\*\/]/', $expr)) {
		echo "invalid 3\n";
		exit; 
	}
	// 演算子が３つ以上続く場合
	if (preg_match('/[\+\-\*\/]{3}/', $expr)) {
		echo "invalid 4\n";
		exit; 
	}

/*
	$cnt_o = 0;
	foreach (str_split($expr) as $v) {
		if ( preg_match('/[0-9\.]/', $v)) {
			$cnt_o = 0;
		} elseif (preg_match('/[\+\-\*\/]/', $v)) {
			if ($cnt_o > 1) {
				// 演算子が３つ続くのはエラーとする
				echo "invalid\n";
				exit;
			}
			if (preg_match('/[\*\/]/', $v)) {
				// 乗除が続くのはエラーとする
				echo "invalid\n";
				exit;
			}
			$cnt_o++;
		} elseif (preg_match('/\S/', $v)) {
			// スペース以外の文字があればは無効
			echo "invalid\n";
			exit;
		}

	}
*/
echo "Check OK.\n";
	return;
}

function perse($expr) {

	$value = str_split($expr);

	$expr1 = array();
	$number = '';
	$operator = '';
	$cnt_n = 0;
	$cnt_o = 0;
	for ($i = 0; $i < count($value); $i++) {
		$v = $value[$i];
//echo $v."\n";
		if (preg_match('/[0-9\.]/',$v)) {
			$number .= $v;
		} elseif (preg_match('/[\+\-\*\/]/',$v)) {
				if ($number == "") {
					$expr1[] = $v;
				} else {
					$expr1[] = $number;
					$expr1[] = $v;
					$number = "";
				}
		} elseif ($v == '(') {
			// カッコがある場合はネストする
			$expr0 = "";
			$cnt_nested = 0;
			for ($j = $i+1; $j < count($value); $j++) {
				$v = $value[$j];
//echo $v."\n";
				if ($v == '(') {
					$cnt_nested++;
				} elseif ($v == ')') {
					if ($cnt_nested == 0) {
						break;
					}
					$cnt_nested--;
				} elseif (preg_match('/\s/', $v)) {
					continue;
				}
				$expr0 .= $v;
			}
			$expr2 = perse($expr0);
			$i = $j;
			$expr1[] = $expr2;
			
		} else {
			// スペースなど数値と演算子以外を無視
		}

	}
	if ($number != '') {
		$expr1[] = $number;
	}
//var_dump($expr1);

	return $expr1;
}

function multiplying($expr) {

	$value = $expr;

	$expr1 = array();
	$numbers = array('','');
	$cnt_o = 1;
	$operator = '';
	$cnt_n = 0;

	for ($i = 0; $i < count($value); $i++) {
		$v = $value[$i];
//echo $v."\n";
		if (is_array($v)) {
			//$numbers[$cnt_n] = multiplying($v);
			$numbers[$cnt_n] .= adding($v);
			// *戻り値がマイナスの場合に、--となってしまう

		} elseif (is_numeric($v)) {
			$numbers[$cnt_n] .= $v;
			$cnt_n = 1;
			$cnt_o = 0;
		} elseif (preg_match('/[\+\-\*\/]/',$v)) {
			if ($cnt_o == 1) {
				// 演算子が続いた場合、次の数字の正負記号となる（加減演算子であればOK）
				$numbers[$cnt_n] .= $v;
			} else {
				$operator = $v;
				$cnt_o = 1;
			//	$cnt_n++;
			}
		} else {
			// スペースなど数値と演算子以外を無視
			echo "DEBUG:Space!\n";
		}
echo "DEBUG:$numbers[0]$operator$numbers[1]\n";
		if (is_numeric($numbers[1])) {
				// 加減演算は保留
				if (preg_match('/[\+\-]/', $operator)) {
					$expr1[] = $numbers[0];
					$expr1[] = $operator;
					$numbers[0] = $numbers[1];
				} else {
					// 計算
					$result = calculation($numbers, $operator);
					if ($result == "error") {
						return $result;
					}
					$numbers[0] = $result;
				}
				$operator = "";
				$cnt_o = 0;
				$numbers[1] = "";
//				$cnt_n = 1;
		}
//			$cnt_n++;

	}
	if ($numbers[0]) {
		$expr1[] = $numbers[0];
	}
var_dump($expr1);

	return $expr1;
}

function adding($expr) {

	// 乗除を先に行う
	$expr = multiplying($expr);
	if ($expr == "error") {
		return $expr;
	}

	$value = $expr;

	$numbers = array('','');
	$cnt_o = 1;
	$operator = '';
	$cnt_n = 0;

	for ($i = 0; $i < count($value); $i++) {
		$v = $value[$i];
//echo $v."\n";
		if (is_numeric($v)) {
			$numbers[$cnt_n] .= $v;
			$cnt_n = 1;
			$cnt_o = 0;
		} elseif (preg_match('/[\+\-\*\/]/',$v)) {
			if ($cnt_o == 1) {
				// 演算子が続いた場合、次の数字の正負記号となる（加減演算子であればOK）
				$numbers[$cnt_n] .= $v;
			} else {
				$operator = $v;
				$cnt_o = 1;
			//	$cnt_n++;
			}
		} else {
			// スペースなど数値と演算子以外を無視
			echo "DEBUG:Space!\n";
		}

echo "DEBUG:$numbers[0]$operator$numbers[1]\n";
		if (is_numeric($numbers[1])) {
			// 計算
			$result = calculation($numbers, $operator);
			if ($result == "error") {
				return $result;
			}
			$numbers[0] = $result;
			$operator = "";
			$cnt_o = 0;
			$numbers[1] = "";
		}

//var_dump($numbers);
	}

	return $numbers[0];
}

function calculation($numbers, $operator) {
	$result = 0;
	switch ($operator) {
		case '*':
			$result = $numbers[0] * $numbers[1];
			break;
		case '/':
			if (!(int)$numbers[1]) {
				// 0除算
echo "DEBUG:ZERO Divide\n";
				return 'error';
			}
			$result = $numbers[0] / $numbers[1];
			break;
		case '+':
			$result = $numbers[0] + $numbers[1];
			break;
		case '-':
			$result = $numbers[0] - $numbers[1];
			break;
	}
echo "calculation $numbers[0] $operator $numbers[1] =  $result\n";
	return (string)$result;
}

check($argv[1]);
$expr = perse($argv[1]);
//echo "expr = \n";
//var_dump($expr);

$result = adding($expr);
echo "result ".$result."\n";

