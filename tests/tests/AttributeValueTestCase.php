<?php

use \Concrete\Core\Attribute\Type as AttributeType;

abstract class AttributeValueTestCase extends ConcreteDatabaseTestCase
{
    protected $fixtures = array();
    protected $metadatas = array(
        'Concrete\Core\Entity\Site\Site',
        'Concrete\Core\Entity\Site\Locale',
        'Concrete\Core\Entity\Site\Tree',
        'Concrete\Core\Entity\Site\SiteTree',
        'Concrete\Core\Entity\Site\Type',
        'Concrete\Core\Entity\Attribute\Category',
        'Concrete\Core\Entity\Attribute\Type',
        'Concrete\Core\Entity\Attribute\Key\Key',
        'Concrete\Core\Entity\Attribute\Key\PageKey',
        'Concrete\Core\Entity\Attribute\Value\Value',
        'Concrete\Core\Entity\Attribute\Value\Value\Value',
        'Concrete\Core\Entity\Attribute\Value\PageValue',
        'Concrete\Core\Entity\Page\PagePath',
    );

    protected $tables = array(
        'Collections',
        'CollectionVersions',
        'Pages',
    );

    abstract public function getAttributeTypeHandle();
    abstract public function getAttributeTypeName();
    abstract public function getAttributeKeyHandle();
    abstract public function getAttributeKeyName();
    abstract public function createAttributeKeySettings();
    abstract public function getAttributeValueClassName();

    protected function prepareBaseValueAfterRetrieving($value)
    {
        return $value;
    }

    protected function setUp()
    {
        parent::setUp();
        $service = \Core::make('site');
        if (!$service->getDefault()) {
            $service->installDefault('en_US');
        }

        $this->category = \Concrete\Core\Attribute\Key\Category::add('collection');
        $this->object = Page::addHomePage();
        $type = \Concrete\Core\Attribute\Type::add($this->getAttributeTypeHandle(), $this->getAttributeTypeName());

        $key = new \Concrete\Core\Entity\Attribute\Key\PageKey();
        $key->setAttributeKeyName($this->getAttributeKeyName());
        $key->setAttributeKeyHandle($this->getAttributeKeyHandle());

        \Concrete\Core\Attribute\Key\CollectionKey::add($type, $key, $this->createAttributeKeySettings());
    }

    /**
     *  @dataProvider baseAttributeValues
     */
    public function testBaseAttributeValueGet($input, $expectedBaseValue)
    {
        $this->object->setAttribute($this->getAttributeKeyHandle(), $input);
        $baseValue = $this->object->getAttribute($this->getAttributeKeyHandle());
        $baseValue = $this->prepareBaseValueAfterRetrieving($baseValue);
        $this->assertEquals($expectedBaseValue, $baseValue);

        $value = $this->object->getAttributeValueObject($this->getAttributeKeyHandle());
        $this->assertInstanceOf('Concrete\Core\Entity\Attribute\Value\PageValue', $value);

        $this->assertInstanceOf($this->getAttributeValueClassName(), $value->getValueObject());

    }

    /**
     *  @dataProvider displayAttributeValues
     */
    public function testDisplayAttributeValues($input, $expectedDisplayValue)
    {
        $this->object->setAttribute($this->getAttributeKeyHandle(), $input);
        $displayValue1 = $this->object->getAttribute($this->getAttributeKeyHandle(), 'display');
        $displayValue2 = $this->object->getAttribute($this->getAttributeKeyHandle(), 'displaySanitized');

        $value = $this->object->getAttributeValueObject($this->getAttributeKeyHandle());

        $displayValue3 = $value->getDisplayValue();
        $displayValue4 = $value->getDisplaySanitizedValue();
        $displayValue5 = (string) $value;

        $this->assertEquals($displayValue1, $displayValue2);
        $this->assertEquals($displayValue2, $displayValue3);
        $this->assertEquals($displayValue3, $displayValue4);
        $this->assertEquals($displayValue4, $displayValue5);

        $this->assertEquals($expectedDisplayValue, $displayValue1);
    }

    /**
     *  @dataProvider plaintextAttributeValues
     */
    public function testPlainTextAttributeValues($input, $expectedPlainTextOutput)
    {
        $this->object->setAttribute($this->getAttributeKeyHandle(), $input);
        $value = $this->object->getAttributeValueObject($this->getAttributeKeyHandle());
        $plainTextValue = $value->getPlainTextValue();

        $this->assertEquals($expectedPlainTextOutput, $plainTextValue);
    }

    /**
     *  @dataProvider searchIndexAttributeValues
     */
    public function testSearchIndexAttributeValues($input, $expectedSearchIndexValue)
    {
        $this->object->setAttribute($this->getAttributeKeyHandle(), $input);
        $value = $this->object->getAttributeValueObject($this->getAttributeKeyHandle());
        $searchIndexValue = $value->getSearchIndexValue();

        $this->assertEquals($expectedSearchIndexValue, $searchIndexValue);
    }





}
