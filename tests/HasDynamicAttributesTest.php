<?php

namespace Tests;

use Tests\Fixtures\Form;
use Tests\Fixtures\FormWithGuarded;
use Tests\Fixtures\FormWithFillables;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HasDynamicAttributesTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    public function tearDown(): void
    {
        Schema::drop('forms');

        parent::tearDown();
    }

    public function test_it_saves_attribute()
    {
        $model = Form::make(['foo' => 'bar']);
        $model->save();

        $this->assertArrayHasKey('foo', $model->refresh()->getAttributes());
        $this->assertSame('bar', $model->getAttributes()['foo']);
        $this->assertSame('bar', $model->getAttribute('foo'));
    }

    public function test_it_sets_casts()
    {
        $model = Form::make(['amount' => 100]);
        $model->save();

        $this->assertSame(100, $model->refresh()->getAttribute('amount'));
    }

    public function test_manually_setting_cast()
    {
        $model = Form::make(['is_active' => 1]);
        $model->setDynamicAttributeCast('is_active', 'boolean');
        $model->save();

        $this->assertSame(true, $model->refresh()->getAttribute('is_active'));
    }

    public function test_whereAttribute_query_scope()
    {
        $this->markTestSkipped();

        Form::create(['foo' => 'bar']);
        Form::create(['amount' => 100]);

        $this->assertSame(1, Form::whereAttribute('foo', 'bar')->count());
        $this->assertSame(1, Form::whereAttribute('amount', '>', 60)->count());
        $this->assertSame(0, Form::whereAttribute('amount', '>', 110)->count());
    }

    public function test_it_saves_attribute_when_model_has_fillables()
    {
        $model = FormWithFillables::make(['foo' => 'bar']);
        $model->save();

        $this->assertArrayHasKey('foo', $model->refresh()->getAttributes());
        $this->assertSame('bar', $model->getAttributes()['foo']);
        $this->assertSame('bar', $model->getAttribute('foo'));
    }

    public function test_it_guards_attributes()
    {
        $model = FormWithGuarded::make([
            'title' => 'foo',
            'password' => 'secret',
        ]);
        $model->save();

        $this->assertArrayHasKey('title', $model->refresh()->getAttributes());
        $this->assertSame('foo', $model->getAttributes()['title']);
        $this->assertSame('foo', $model->getAttribute('title'));

        $this->assertNull($model->getAttribute('password'));
    }
}
