<?php
/**
 * Slim - a micro PHP 5 framework
 *
 * @author      Josh Lockhart <info@slimframework.com>
 * @copyright   2011-2017 Josh Lockhart
 * @link        http://www.slimframework.com
 * @license     http://www.slimframework.com/license
 * @version     2.6.4
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
class CookiesTest extends SlimTestCase
{
    public function testSetWithStringValue(): void
    {
        $c = new \Slim\Http\Cookies();
        $c->set('foo', 'bar');
        $this->assertAttributeEquals(
            array(
                'foo' => array(
                    'value' => 'bar',
                    'expires' => null,
                    'domain' => null,
                    'path' => null,
                    'secure' => false,
                    'httponly' => false
                )
            ),
            'data',
            $c
        );
    }

    public function testSetWithArrayValue(): void
    {
        $now = time();
        $c = new \Slim\Http\Cookies();
        $c->set('foo', array(
            'value' => 'bar',
            'expires' => $now + 86400,
            'domain' => '.example.com',
            'path' => '/',
            'secure' => true,
            'httponly' => true
        ));
        $this->assertAttributeEquals(
            array(
                'foo' => array(
                    'value' => 'bar',
                    'expires' => $now + 86400,
                    'domain' => '.example.com',
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true
                )
            ),
            'data',
            $c
        );
    }

    public function testRemove(): void
    {
        $c = new \Slim\Http\Cookies();
        $c->remove('foo');
        $prop = new \ReflectionProperty($c, 'data');
        $prop->setAccessible(true);
        $cValue = $prop->getValue($c);
        $this->assertEquals('', $cValue['foo']['value']);
        $this->assertLessThan(time(), $cValue['foo']['expires']);
    }
}
