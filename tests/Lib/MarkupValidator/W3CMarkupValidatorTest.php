<?php

namespace Kolyunya\Codeception\Tests\Lib\MarkupValidator;

use PHPUnit\Framework\TestCase;
use Kolyunya\Codeception\Lib\MarkupValidator\MarkupValidatorMessageInterface;
use Kolyunya\Codeception\Lib\MarkupValidator\W3CMarkupValidator;

class W3CMarkupValidatorTest extends TestCase
{
    /**
     * @var W3CMarkupValidator
     */
    private $validator;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->validator = $this
            ->getMockBuilder('Kolyunya\Codeception\Lib\MarkupValidator\W3CMarkupValidator')
            ->enableProxyingToOriginalMethods()
            ->getMock()
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown()
    {
    }

    /**
     * @dataProvider testValidateMarkupDataProvider
     */
    public function testValidateMarkup($markup, $messagesData)
    {
        $messages = $this->validator->validate($markup);

        $this->assertEquals(count($messagesData), count($messages));

        foreach ($messagesData as $messageIndex => $messageData) {
            $message = $messages[$messageIndex];

            $this->assertEquals($message->getType(), $messageData['type']);
            $this->assertEquals($message->getSummary(), $messageData['summary']);
            $this->assertEquals($message->getDetails(), $messageData['details']);
            $this->assertContains($messageData['markup'], $message->getMarkup());
        }
    }

    public function testValidateMarkupDataProvider()
    {
        return array(
            array(
                <<<HTML
                    <!DOCTYPE HTML>
                    <html>
                        <head>
                            <title>
                                A valid page.
                            </title>
                        </head>
                    </html>
HTML
                ,
                array(
                ),
            ),
            array(
                <<<HTML
                    <!DOCTYPE HTML>
                    <html>
                        <head>
                        </head>
                    </html>
HTML
                ,
                array(
                    array(
                        'type' => MarkupValidatorMessageInterface::TYPE_ERROR,
                        'summary' => 'Element “head” is missing a required instance of child element “title”.',
                        'details' => null,
                        'markup' => '</head>',
                    ),
                ),
            ),
            array(
                <<<HTML
                    <!DOCTYPE HTML>
                    <html>
                        <head>
                        </head>
                        <body>
                            <form>
                                <button role="button">
                                </button>
                            </form>
                        </body>
                    </html>
HTML
                ,
                array(
                    array(
                        'type' => MarkupValidatorMessageInterface::TYPE_ERROR,
                        'summary' => 'Element “head” is missing a required instance of child element “title”.',
                        'details' => null,
                        'markup' => '</head>',
                    ),
                    array(
                        'type' => MarkupValidatorMessageInterface::TYPE_WARNING,
                        'summary' => 'The “button” role is unnecessary for element “button”.',
                        'details' => null,
                        'markup' => '<button role="button">',
                    ),
                ),
            ),
        );
    }
}