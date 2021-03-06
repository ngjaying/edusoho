<?php

/**
 * Pure-PHP implementation of AES.
 *
 * Uses mcrypt, if available/possible, and an internal implementation, otherwise.
 *
 * PHP version 5
 *
 * NOTE: Since AES.php is (for compatibility and phpseclib-historical reasons) virtually
 * just a wrapper to Rijndael.php you may consider using Rijndael.php instead of
 * to save one include_once().
 *
 * If {@link \Topxia\Service\Util\Phpsec\Crypt\AES::setKeyLength() setKeyLength()} isn't called, it'll be calculated from
 * {@link \Topxia\Service\Util\Phpsec\Crypt\AES::setKey() setKey()}.  ie. if the key is 128-bits, the key length will be 128-bits.  If it's 136-bits
 * it'll be null-padded to 192-bits and 192 bits will be the key length until {@link \Topxia\Service\Util\Phpsec\Crypt\AES::setKey() setKey()}
 * is called, again, at which point, it'll be recalculated.
 *
 * Since \Topxia\Service\Util\Phpsec\Crypt\AES extends \Topxia\Service\Util\Phpsec\Crypt\Rijndael, some functions are available to be called that, in the context of AES, don't
 * make a whole lot of sense.  {@link \Topxia\Service\Util\Phpsec\Crypt\AES::setBlockLength() setBlockLength()}, for instance.  Calling that function,
 * however possible, won't do anything (AES has a fixed block length whereas Rijndael has a variable one).
 *
 * Here's a short example of how to use this library:
 * <code>
 * <?php
 *    include 'vendor/autoload.php';
 *
 *    $aes = new \Topxia\Service\Util\Phpsec\Crypt\AES();
 *
 *    $aes->setKey('abcdefghijklmnop');
 *
 *    $size = 10 * 1024;
 *    $plaintext = '';
 *    for ($i = 0; $i < $size; $i++) {
 *        $plaintext.= 'a';
 *    }
 *
 *    echo $aes->decrypt($aes->encrypt($plaintext));
 * ?>
 * </code>
 *
 * @category  Crypt
 * @package   AES
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2008 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 * @link      http://phpseclib.sourceforge.net
 */

namespace Topxia\Service\Util\Phpsec\Crypt;

use Topxia\Service\Util\Phpsec\Crypt\Rijndael;

/**
 * Pure-PHP implementation of AES.
 *
 * @access  public
 * @package AES
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
class AES extends Rijndael
{
    /**
     * Dummy function
     *
     * Since \Topxia\Service\Util\Phpsec\Crypt\AES extends \Topxia\Service\Util\Phpsec\Crypt\Rijndael, this function is, technically, available, but it doesn't do anything.
     *
     * @access public
     * @see \Topxia\Service\Util\Phpsec\Crypt\Rijndael::setBlockLength()
     *
     * @param Integer $length
     */
    public function setBlockLength($length)
    {
        return;
    }

    /**
     * Sets the key length
     *
     * Valid key lengths are 128, 192, and 256.  If the length is less than 128, it will be rounded up to
     * 128.  If the length is greater than 128 and invalid, it will be rounded down to the closest valid amount.
     *
     * @access public
     * @see \Topxia\Service\Util\Phpsec\Crypt\Rijndael:setKeyLength()
     *
     * @param Integer $length
     */
    public function setKeyLength($length)
    {
        switch ($length) {
            case 160:
                $length = 192;
                break;
            case 224:
                $length = 256;
        }

        parent::setKeyLength($length);
    }

    /**
     * Sets the key.
     *
     * Rijndael supports five different key lengths, AES only supports three.
     *
     * @access public
     * @see \Topxia\Service\Util\Phpsec\Crypt\Rijndael:setKey()
     * @see setKeyLength()
     *
     * @param String $key
     */
    public function setKey($key)
    {
        parent::setKey($key);

        if (!$this->explicit_key_length) {
            $length = strlen($key);

            switch (true) {
                case $length <= 16:
                    $this->key_size = 16;
                    break;
                case $length <= 24:
                    $this->key_size = 24;
                    break;
                default:
                    $this->key_size = 32;
            }

            $this->_setEngine();
        }
    }
}
