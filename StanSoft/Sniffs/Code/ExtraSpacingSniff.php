<?php
/**
 * StanSoft_Code_ExtraSpacingSniff.
 *
 * Verifies that operators have valid spacing surrounding them.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Stanimir Stoyanov <stan@linux.com>
 * @copyright 2016 StanSoft.BG Ltd (CN 204 095 446)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class StanSoft_Sniffs_Code_ExtraSpacingSniff implements PHP_CodeSniffer_Sniff
{

	/**
	 * The number of spaces before and after a string concat.
	 *
	 * @var int
	 */
	public $spacing = 1;
	/**
	 * Allow newlines instead of spaces.
	 *
	 * @var boolean
	 */
	public $ignoreNewlines = true;
	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register()
	{
		return array(T_EQUAL, T_IS_NOT_EQUAL, T_IS_EQUAL,T_DOUBLE_ARROW, T_IS_IDENTICAL, T_STRING_CONCAT,
			T_IS_NOT_IDENTICAL, T_IS_GREATER_OR_EQUAL, T_IS_SMALLER_OR_EQUAL, T_CONCAT_EQUAL, T_PLUS, T_MINUS, T_MULTIPLY, T_DIVIDE);
	}//end register()
	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param int                  $stackPtr  The position of the current token in the
	 *                                        stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
	{
		$this->spacing = (int) $this->spacing;
		$tokens = $phpcsFile->getTokens();
		if ($tokens[($stackPtr - 1)]['code'] !== T_WHITESPACE) {
			$before = 0;
		} elseif ($tokens[($stackPtr - 2)]['code'] === T_COMMA) {
			return;
		} else {
			if ($tokens[($stackPtr - 2)]['line'] !== $tokens[$stackPtr]['line']) {
				$before = 'newline';
			} else {
				$before = $tokens[($stackPtr - 1)]['length'];
			}
		}
		if ($tokens[$stackPtr]['code'] === T_MINUS && $tokens[$stackPtr - 1]['code'] === T_WHITESPACE &&
			$tokens[$stackPtr + 1]['type'] === 'T_LNUMBER') {
			// conditions like $something = -1;
			return;
		}
		if ($tokens[($stackPtr + 1)]['code'] !== T_WHITESPACE) {
			$after = 0;
		} else {
			if ($tokens[($stackPtr + 2)]['line'] !== $tokens[$stackPtr]['line']) {
				$after = 'newline';
			} else {
				$after = $tokens[($stackPtr + 1)]['length'];
			}
		}
		$phpcsFile->recordMetric($stackPtr, 'Spacing before string concat', $before);
		$phpcsFile->recordMetric($stackPtr, 'Spacing after string concat', $after);
		if (($before === $this->spacing || ($before === 'newline' && $this->ignoreNewlines === true))
			&& ($after === $this->spacing || ($after === 'newline' && $this->ignoreNewlines === true))
		) {
			return;
		}
		if ($this->spacing === 0) {
			$message = 'Operator must not be surrounded by spaces';
			$data = array();
		} else {
			if ($this->spacing > 1) {
				$message = 'Operator must be surrounded by %s spaces';
			} else {
				$message = 'Operator must be surrounded by a single space';
			}
			$data = array($this->spacing);
		}
		$fix = $phpcsFile->addFixableError($message, $stackPtr, 'PaddingFound', $data);
		if ($fix === true) {
			$padding = str_repeat(' ', $this->spacing);
			if ($before !== 'newline' || $this->ignoreNewlines === false) {
				if ($tokens[($stackPtr - 1)]['code'] === T_WHITESPACE) {
					$phpcsFile->fixer->replaceToken(($stackPtr - 1), $padding);
				} elseif ($this->spacing > 0) {
					$phpcsFile->fixer->addContent(($stackPtr - 1), $padding);
				}
			}
			if ($after !== 'newline' || $this->ignoreNewlines === false) {
				if ($tokens[($stackPtr + 1)]['code'] === T_WHITESPACE) {
					$phpcsFile->fixer->replaceToken(($stackPtr + 1), $padding);
				} elseif ($this->spacing > 0) {
					$phpcsFile->fixer->addContent($stackPtr, $padding);
				}
			}
		}
	}//end process()
}//end class
