<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Fixtures\Form;

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
}
