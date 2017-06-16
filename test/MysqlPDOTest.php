<?php
require_once 'code/library/db/MysqlPDO.php';
use  crawl\library\db\MysqlPDO;
/**
 * MysqlPDO test case.
 */
class MysqlPDOTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var MysqlPDO
     */
    private $mysqlPDO;

    private static $i = 0;
    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        // TODO Auto-generated MysqlPDOTest::setUp()
        $dbconfig = array (
            'host' => 'mysql:host=localhost;dbname=test;',
            'user' => 'root',
            'password' => 'root',
            'tablePrefix' => '',
        );

        $this->mysqlPDO = new MysqlPDO($dbconfig);

        $this->mysqlPDO->exec('CREATE TABLE IF NOT EXISTS pdo_test(
                 id INT NOT NULL AUTO_INCREMENT,
                 name VARCHAR(100) NOT NULL,
                 email VARCHAR(100) NOT NULL,
                 PRIMARY KEY (id));');


        $this->mysqlPDO->insert('pdo_test', array(
            'name' => 'xwx'.self::$i++,
            'email' => 'xwx'.self::$i++.'@gmail.com'
        ));
     }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated MysqlPDOTest::tearDown()
        $this->mysqlPDO = null;

        parent::tearDown();
    }

    /**
     * Tests MysqlPDO->find()
     */
    public function testFind()
    {
        // TODO Auto-generated MysqlPDOTest->testFind()
        $this->markTestIncomplete("find test not implemented");
        $row = $this->mysqlPDO->find('pdo_test', ['id' => '221']);
        $this->assertNotEmpty($row);
        return $row;


    }

    /**
     * Tests MysqlPDO->findAll()
     */
    public function testFindAll()
    {
        // TODO Auto-generated MysqlPDOTest->testFindAll()
        $this->markTestIncomplete("findAll test not implemented");

        $this->mysqlPDO->findAll(/* parameters */);
    }

    /**
     * Tests MysqlPDO->escape()
     */
    public function testEscape()
    {
        // TODO Auto-generated MysqlPDOTest->testEscape()
        $this->markTestIncomplete("escape test not implemented");

        $this->mysqlPDO->escape(/* parameters */);
    }

    /**
     * Tests MysqlPDO->insert()
     */
    public function testInsert()
    {
        // TODO Auto-generated MysqlPDOTest->testInsert()
        $this->markTestIncomplete("insert test not implemented");

        $this->mysqlPDO->insert(/* parameters */);
    }

    /**
     * Tests MysqlPDO->insertAll()
     */
    public function testInsertAll()
    {
        // TODO Auto-generated MysqlPDOTest->testInsertAll()
        $this->markTestIncomplete("insertAll test not implemented");

        $this->mysqlPDO->insertAll(/* parameters */);
    }

    /**
     * Tests MysqlPDO->createInsert()
     */
    public function testCreateInsert()
    {
        // TODO Auto-generated MysqlPDOTest->testCreateInsert()
        $this->markTestIncomplete("createInsert test not implemented");

        $this->mysqlPDO->createInsert(/* parameters */);
    }

    /**
     * Tests MysqlPDO->delete()
     */
    public function testDelete()
    {
        // TODO Auto-generated MysqlPDOTest->testDelete()
        $this->markTestIncomplete("delete test not implemented");

        $this->mysqlPDO->delete(/* parameters */);
    }

    /**
     * Tests MysqlPDO->findBy()
     */
    public function testFindBy()
    {
        // TODO Auto-generated MysqlPDOTest->testFindBy()
        $this->markTestIncomplete("findBy test not implemented");

        $this->mysqlPDO->findBy(/* parameters */);
    }

    /**
     * Tests MysqlPDO->getSqlList()
     */
    public function testGetSqlList()
    {
        // TODO Auto-generated MysqlPDOTest->testGetSqlList()
        $this->markTestIncomplete("getSqlList test not implemented");

        $this->mysqlPDO->getSqlList(/* parameters */);
    }

    /**
     * Tests MysqlPDO->getLastSql()
     */
    public function testGetLastSql()
    {
        // TODO Auto-generated MysqlPDOTest->testGetLastSql()
        $this->markTestIncomplete("getLastSql test not implemented");

        $this->mysqlPDO->getLastSql(/* parameters */);
    }

    /**
     * Tests MysqlPDO->affectedRows()
     */
    public function testAffectedRows()
    {
        // TODO Auto-generated MysqlPDOTest->testAffectedRows()
        $this->markTestIncomplete("affectedRows test not implemented");

        $this->mysqlPDO->affectedRows(/* parameters */);
    }

    /**
     * Tests MysqlPDO->count()
     */
    public function testCount()
    {
        // TODO Auto-generated MysqlPDOTest->testCount()
        $this->markTestIncomplete("count test not implemented");

        $this->mysqlPDO->count(/* parameters */);
    }

    /**
     * Tests MysqlPDO->update()
     */
    public function testUpdate()
    {
        // TODO Auto-generated MysqlPDOTest->testUpdate()
        $this->markTestIncomplete("update test not implemented");

        $this->mysqlPDO->update(/* parameters */);
    }

    /**
     * Tests MysqlPDO->updateField()
     */
    public function testUpdateField()
    {
        // TODO Auto-generated MysqlPDOTest->testUpdateField()
        $this->markTestIncomplete("updateField test not implemented");

        $this->mysqlPDO->updateField(/* parameters */);
    }

    /**
     * Tests MysqlPDO->deleteByPk()
     */
    public function testDeleteByPk()
    {
        // TODO Auto-generated MysqlPDOTest->testDeleteByPk()
        $this->markTestIncomplete("deleteByPk test not implemented");

        $this->mysqlPDO->deleteByPk(/* parameters */);
    }

    /**
     * Tests MysqlPDO->getArray()
     */
    public function testGetArray()
    {
        // TODO Auto-generated MysqlPDOTest->testGetArray()
        $this->markTestIncomplete("getArray test not implemented");

        $this->mysqlPDO->getArray(/* parameters */);
    }

    /**
     * Tests MysqlPDO->lastInsertId()
     */
    public function testLastInsertId()
    {
        // TODO Auto-generated MysqlPDOTest->testLastInsertId()
        $this->markTestIncomplete("lastInsertId test not implemented");

        $this->mysqlPDO->lastInsertId(/* parameters */);
    }

    /**
     * Tests MysqlPDO->setlimit()
     */
    public function testSetlimit()
    {
        // TODO Auto-generated MysqlPDOTest->testSetlimit()
        $this->markTestIncomplete("setlimit test not implemented");

        $this->mysqlPDO->setlimit(/* parameters */);
    }

    /**
     * Tests MysqlPDO->exec()
     */
    public function testExec()
    {
        // TODO Auto-generated MysqlPDOTest->testExec()
        $this->markTestIncomplete("exec test not implemented");

        $this->mysqlPDO->exec(/* parameters */);
    }

    /**
     * Tests MysqlPDO->getTableInfo()
     */
    public function testGetTableInfo()
    {
        // TODO Auto-generated MysqlPDOTest->testGetTableInfo()
        $this->markTestIncomplete("getTableInfo test not implemented");

        $this->mysqlPDO->getTableInfo(/* parameters */);
    }

    /**
     * Tests MysqlPDO->getTableNmae()
     */
    public function testGetTableNmae()
    {
        // TODO Auto-generated MysqlPDOTest->testGetTableNmae()
        $this->markTestIncomplete("getTableNmae test not implemented");

        $this->mysqlPDO->getTableNmae(/* parameters */);
    }

    /**
     * Tests MysqlPDO->getConn()
     */
    public function testGetConn()
    {
        // TODO Auto-generated MysqlPDOTest->testGetConn()
        $this->markTestIncomplete("getConn test not implemented");

        $this->mysqlPDO->getConn(/* parameters */);
    }
}

