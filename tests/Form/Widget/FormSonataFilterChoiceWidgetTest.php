<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Form\Widget;

use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as SymfonyChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Tests\Fixtures\TestExtension;
use Symfony\Component\Translation\TranslatorInterface;

class FormSonataFilterChoiceWidgetTest extends BaseWidgetTest
{
    protected $type = 'filter';

    public function setUp()
    {
        parent::setUp();
    }

    public function testDefaultValueRendering()
    {
        $choice = $this->factory->create(
            $this->getParentClass(),
            null,
            $this->getDefaultOption()
        );

        $html = $this->cleanHtmlWhitespace($this->renderWidget($choice->createView()));
        $html = $this->cleanHtmlAttributeWhitespace($html);

        $this->assertContains(
            '<option value="1">[trans]label_type_contains[/trans]</option>',
            $html
        );

        $this->assertContains(
            '<option value="2">[trans]label_type_not_contains[/trans]</option>',
            $html
        );

        $this->assertContains(
            '<option value="3">[trans]label_type_equals[/trans]</option></select>',
            $html
        );
    }

    protected function getParentClass()
    {
        if (class_exists(RangeType::class)) {
            return ChoiceType::class;
        }

        return 'sonata_type_filter_choice';
    }

    protected function getChoiceClass()
    {
        if (class_exists(RangeType::class)) {
            return SymfonyChoiceType::class;
        }

        return 'choice';
    }

    protected function getExtensions()
    {
        $mock = $this->getMockBuilder(TranslatorInterface::class)->getMock();

        $mock->method('trans')
            ->will($this->returnCallback(function ($arg) {
                return $arg;
            })
        );

        $extensions = parent::getExtensions();
        $guesser = $this->getMockForAbstractClass(FormTypeGuesserInterface::class);
        $extension = new TestExtension($guesser);
        $type = new ChoiceType($mock);
        $extension->addType($type);

        if (!$extension->hasType($this->getParentClass())) {
            $reflection = new \ReflectionClass($extension);
            $property = $reflection->getProperty('types');
            $property->setAccessible(true);
            $property->setValue($extension, [\get_class($type) => current($property->getValue($extension))]);
        }

        $extensions[] = $extension;

        return $extensions;
    }

    protected function getDefaultOption()
    {
        return ['field_type' => $this->getChoiceClass(),
             'field_options' => [],
             'operator_type' => $this->getChoiceClass(),
             'operator_options' => [],
        ];
    }
}
