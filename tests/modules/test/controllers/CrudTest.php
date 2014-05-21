<?php
/**
 * @copyright Bluz PHP Team
 * @link https://github.com/bluzphp/skeleton
 */

/**
 * @namespace
 */
namespace Application\Tests\Test;

use Application\Tests\BootstrapTest;
use Application\Tests\ControllerTestCase;
use Bluz\Http;

/**
 * @package  Application\Tests\Test
 * @author   Anton Shevchuk
 * @created  21.05.14 11:28
 */
class CrudTest extends ControllerTestCase
{
    /**
     * Setup `test` table before the first test
     */
    public static function setUpBeforeClass()
    {
        BootstrapTest::getInstance()->getDb()->query(
            "CREATE TABLE `test` (
              `id` bigint(20) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) DEFAULT NULL,
              `email` varchar(512) DEFAULT NULL,
              `status` enum('active','disable','delete') DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=utf8
            "
        );

        BootstrapTest::getInstance()->getDb()->insert('test')->setArray(
            [
                'id' => 1,
                'name' => 'Donatello',
                'email' => 'donatello@turtles.org'
            ]
        )->execute();

        BootstrapTest::getInstance()->getDb()->insert('test')->setArray(
            [
                'id' => 2,
                'name' => 'Leonardo',
                'email' => 'leonardo@turtles.org'
            ]
        )->execute();

        BootstrapTest::getInstance()->getDb()->insert('test')->setArray(
            [
                'id' => 3,
                'name' => 'Michelangelo',
                'email' => 'michelangelo@turtles.org'
            ]
        )->execute();

        BootstrapTest::getInstance()->getDb()->insert('test')->setArray(
            [
                'id' => 4,
                'name' => 'Raphael',
                'email' => 'raphael@turtles.org'
            ]
        )->execute();
    }

    /**
     * Drop `test` table after the last test
     */
    public static function tearDownAfterClass()
    {
        BootstrapTest::getInstance()->getDb()->query(
            "DROP TABLE `test`;"
        );
    }

    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->app->useLayout(false);
    }

    /**
     * GET request should return FORM for create record
     */
    public function testCreateForm()
    {
        $this->dispatchRouter('/test/crud/');
        $this->assertOk();

        $this->assertQueryCount('form[method="POST"]', 1);
    }

    /**
     * GET request with ID record should return FORM for edit
     */
    public function testEditForm()
    {
        $this->dispatchRouter('/test/crud/', ['id' => 1]);
        $this->assertOk();

        $this->assertQueryCount('form[method="PUT"]', 1);
        $this->assertQueryCount('input[name="id"][value="1"]', 1);
    }

    /**
     * GET request with wrong ID record should return ERROR 404
     */
    public function testEditFormError()
    {
        $this->dispatchRouter('/test/crud/', ['id' => 42]);
        $this->assertResponseCode(404);
    }

    /**
     * POST request should CREATE record
     */
    public function testCreate()
    {
        $this->dispatchRouter(
            '/test/crud/',
            ['name' => 'Splinter', 'email' => 'splinter@turtles.org'],
            Http\Request::METHOD_POST
        );
        $this->assertOk();

        $count = $this->app->getDb()->fetchOne(
            'SELECT count(*) FROM `test` WHERE `name` = ?',
            ['Splinter']
        );
        $this->assertEquals($count, 1);
    }

    /**
     * POST request with invalid data should return ERROR and information
     */
    public function testCreateValidationErrors()
    {
        $result = $this->dispatchRouter(
            '/test/crud/',
            ['name' => '', 'email' => ''],
            Http\Request::METHOD_POST
        );

        $this->assertNotNull($result->getBody()->errors);
        $this->assertEquals(sizeof($result->getBody()->errors), 2);
        $this->assertOk();
    }

    /**
     * PUT request CRUD controller should UPDATE record
     */
    public function testUpdate()
    {
        $this->dispatchRouter(
            '/test/crud/',
            ['id' => 2, 'name' => 'Leonardo', 'email' => 'leonardo@turtles.ua'],
            Http\Request::METHOD_PUT
        );
        ;
        $this->assertOk();

        $id = $this->app->getDb()->fetchOne(
            'SELECT `id` FROM `test` WHERE `email` = ?',
            ['leonardo@turtles.ua']
        );
        $this->assertEquals($id, 2);
    }

    /**
     * PUT request with invalid data should return ERROR and information
     */
    public function testUpdateValidationErrors()
    {
        $result = $this->dispatchRouter(
            '/test/crud/',
            ['id' => 2, 'name' => '', 'email' => 'leonardo@turtles.ua'],
            Http\Request::METHOD_PUT
        );
        ;
        $this->assertNotNull($result->getBody()->errors);
        $this->assertEquals(sizeof($result->getBody()->errors), 1);
        $this->assertOk();
    }

    /**
     * DELETE request should remove record
     */
    public function testDelete()
    {
        $this->dispatchRouter(
            '/test/crud/',
            ['id' => 3],
            Http\Request::METHOD_DELETE
        );
        $this->assertOk();

        $count = $this->app->getDb()->fetchOne(
            'SELECT count(*) FROM `test` WHERE `email` = ?',
            ['michelangelo@turtles.org']
        );
        $this->assertEquals($count, 0);
    }

    /**
     * DELETE request with invalid id should return ERROR
     */
    public function testDeleteError()
    {
        $this->dispatchRouter(
            '/test/crud/',
            ['id' => 42],
            Http\Request::METHOD_DELETE
        );

        $this->assertResponseCode(404);
    }
}
