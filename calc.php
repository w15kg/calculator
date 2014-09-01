<?php


class Calculator
{
	function check($expr) {

		// 先頭が乗除演算子の場合
		if (preg_match('/^\s*[\*\/]/', $expr)) {
			return false;
		}
		// 数字の間にスペースがある場合
		if (preg_match('/[0-9][\s\(\)][0-9]/', $expr)) {
			return false;
		}
		// 演算子が続く場合に乗除演算子が後に付く場合
		if (preg_match('/[\+\-\*\/][\*\/]/', $expr)) {
			return false;
		}
		// 演算子が３つ以上続く場合
		if (preg_match('/[\+\-\*\/]{3}/', $expr)) {
			return false;
		}

		// 整数のみを扱えることとする場合
		// 数字の間にドットがある場合をエラーとする
		if (preg_match('/[0-9][\.][0-9]/', $expr)) {
			return false;
		}

		return true;
	}

	function perse($expr) {

		$value = str_split($expr);

		$expr1 = array();
		$number = '';
		for ($i = 0; $i < count($value); $i++) {
			$v = $value[$i];
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
				$expr2 = $this->perse($expr0);
				$i = $j;
				$expr1[] = $expr2;
				
			} else {
				// スペースなど数値と演算子以外を無視
			}

		}
		if ($number != '') {
			$expr1[] = $number;
		}

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
			if (is_array($v)) {
				//$numbers[$cnt_n] = multiplying($v);
				$numbers[$cnt_n] .= $this->adding($v);
				// *戻り値がマイナスの場合に、--となってしまう件
				if (preg_match('/[\+\-]{2}/',$numbers[$cnt_n])) {
					// +-が並ぶため置換
					$numbers[$cnt_n] = str_replace('--','+',$numbers[$cnt_n]);
					$numbers[$cnt_n] = str_replace('+-','-',$numbers[$cnt_n]);
					$numbers[$cnt_n] = str_replace('-+','-',$numbers[$cnt_n]);
					$numbers[$cnt_n] = str_replace('++','+',$numbers[$cnt_n]);
				}

				$cnt_n = 1;
				$cnt_o = 0;
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
				}
			} else {
				// スペースなど数値と演算子以外を無視
				echo "DEBUG:Space!\n";
			}
//echo "DEBUG:$numbers[0]$operator$numbers[1]\n";
			if (is_numeric($numbers[1])) {
					// 加減演算は保留
					if (preg_match('/[\+\-]/', $operator)) {
						$expr1[] = $numbers[0];
						$expr1[] = $operator;
						$numbers[0] = $numbers[1];
					} else {
						// 計算
						$result = $this->calculation($numbers, $operator);
						if ($result == "error") {
							return $result;
						}
						$numbers[0] = $result;
					}
					$operator = "";
					$cnt_o = 0;
					$numbers[1] = "";
			}
		}
		if ($numbers[0]) {
			$expr1[] = $numbers[0];
		}

		return $expr1;
	}

	function adding($expr) {

		// 乗除を先に行う
		$expr = $this->multiplying($expr);
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
				}
			} else {
				// スペースなど数値と演算子以外を無視
				echo "DEBUG:Space!\n";
			}

//echo "DEBUG:$numbers[0]$operator$numbers[1]\n";
			if (is_numeric($numbers[1])) {
				// 計算
				$result = $this->calculation($numbers, $operator);
				if ($result == "error") {
					return $result;
				}
				$numbers[0] = $result;
				$operator = "";
				$cnt_o = 0;
				$numbers[1] = "";
			}
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
//echo "DEBUG:Division by ZERO\n";
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
//echo "DEBUG:calculation $numbers[0] $operator $numbers[1] =  $result\n";
		return (string)$result;
	}

	public function calculate($expr) {
		if (!$this->check($expr)) {
			return "error";
		}
		$expr1 = $this->perse($expr);

		$result = $this->adding($expr1);
		return $result;
	}
}

$calc = new Calculator();

$result = $calc->calculate($argv[1]);
echo $result."\n";


