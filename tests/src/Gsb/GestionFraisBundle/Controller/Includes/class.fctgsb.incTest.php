<?php

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-02-02 at 23:40:04.
 */
class FctGsbTest extends PHPUnit_Framework_TestCase {

    /**
     * @var FctGsb
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        require_once ("T:\wamp\www\gsb_symfony\src\Gsb\GestionFraisBundle\Controller\Includes\class.fctgsb.inc.php");
        $this->object = new FctGsb;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }

    /**
     * Generated from @assert ('01/01/2016') == '2016-01-01'.
     *
     * @covers FctGsb::dateFrancaisVersAnglais
     */
    public function testDateFrancaisVersAnglais() {
        $this->assertEquals(
            '2016-01-01'
            , FctGsb::dateFrancaisVersAnglais('01/01/2016')
        );
    }

    /**
     * Generated from @assert ('2016-02-02') == '02/02/2016'.
     *
     * @covers FctGsb::dateAnglaisVersFrancais
     */
    public function testDateAnglaisVersFrancais() {
        $this->assertEquals(
            '02/02/2016'
            , FctGsb::dateAnglaisVersFrancais('2016-02-02')
        );
    }

    /**
     * Generated from @assert ('02/02/2016') == "201602".
     *
     * @covers FctGsb::getMois
     */
    public function testGetMois() {
        $this->assertEquals(
            "201602"
            , FctGsb::getMois('02/02/2016')
        );
    }

    /**
     * Generated from @assert (1) == true.
     *
     * @covers FctGsb::estEntierPositif
     */
    public function testEstEntierPositif() {
        $this->assertEquals(
            true
            , FctGsb::estEntierPositif(1)
        );
    }

    /**
     * Generated from @assert (0) == true.
     *
     * @covers FctGsb::estEntierPositif
     */
    public function testEstEntierPositif2() {
        $this->assertEquals(
            true
            , FctGsb::estEntierPositif(0)
        );
    }

    /**
     * Generated from @assert ('r') == false.
     *
     * @covers FctGsb::estEntierPositif
     */
    public function testEstEntierPositif3() {
        $this->assertEquals(
            false
            , FctGsb::estEntierPositif('r')
        );
    }

    /**
     * Generated from @assert (array(0,1,2)) == true.
     *
     * @covers FctGsb::estTableauEntiers
     */
    public function testEstTableauEntiers() {
        $this->assertEquals(
            true
            , FctGsb::estTableauEntiers(array(0, 1, 2))
        );
    }

    /**
     * Generated from @assert (array(-1,'n',1)) == false.
     *
     * @covers FctGsb::estTableauEntiers
     */
    public function testEstTableauEntiers2() {
        $this->assertEquals(
            false
            , FctGsb::estTableauEntiers(array(-1, 'n', 1))
        );
    }

    /**
     * Generated from @assert ('01/02/9999') == true.
     *
     * @covers FctGsb::estDateSuperieure
     */
    public function testEstDateSuperieure() {
        $this->assertEquals(
            true
            , FctGsb::estDateSuperieure('01/02/9999')
        );
    }

    /**
     * Generated from @assert ('01/01/1000') == false.
     *
     * @covers FctGsb::estDateSuperieure
     */
    public function testEstDateSuperieure2() {
        $this->assertEquals(
            false
            , FctGsb::estDateSuperieure('01/01/1000')
        );
    }

    /**
     * Generated from @assert ('01/01/2016') == false.
     *
     * @covers FctGsb::estDateDepassee
     */
    public function testEstDateDepassee() {
        $this->assertEquals(
            false
            , FctGsb::estDateDepassee('01/01/2016')
        );
    }

    /**
     * Generated from @assert ('01/01/2013') == true.
     *
     * @covers FctGsb::estDateDepassee
     */
    public function testEstDateDepassee2() {
        $this->assertEquals(
            true
            , FctGsb::estDateDepassee('01/01/2013')
        );
    }

    /**
     * Generated from @assert ('01/01/2016') == true.
     *
     * @covers FctGsb::estDateValide
     */
    public function testEstDateValide() {
        $this->assertEquals(
            true
            , FctGsb::estDateValide('01/01/2016')
        );
    }

    /**
     * Generated from @assert ('20160101') == false.
     *
     * @covers FctGsb::estDateValide
     */
    public function testEstDateValide2() {
        $this->assertEquals(
            false
            , FctGsb::estDateValide('20160101')
        );
    }

    /**
     * Generated from @assert (array(0,1,2)) == true.
     *
     * @covers FctGsb::lesQteFraisValides
     */
    public function testLesQteFraisValides() {
        $this->assertEquals(
            true
            , FctGsb::lesQteFraisValides(array(0, 1, 2))
        );
    }

    /**
     * Generated from @assert (array(-1,'n',1)) == false.
     *
     * @covers FctGsb::lesQteFraisValides
     */
    public function testLesQteFraisValides2() {
        $this->assertEquals(
            false
            , FctGsb::lesQteFraisValides(array(-1, 'n', 1))
        );
    }

    /**
     * @covers FctGsb::estConnecte
     * @todo   Implement testEstConnecte().
     */
    public function testEstConnecte() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers FctGsb::connecter
     * @todo   Implement testConnecter().
     */
    public function testConnecter() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers FctGsb::deconnecter
     * @todo   Implement testDeconnecter().
     */
    public function testDeconnecter() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers FctGsb::valideInfosFrais
     * @todo   Implement testValideInfosFrais().
     */
    public function testValideInfosFrais() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers FctGsb::ajouterErreur
     * @todo   Implement testAjouterErreur().
     */
    public function testAjouterErreur() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers FctGsb::nbErreurs
     * @todo   Implement testNbErreurs().
     */
    public function testNbErreurs() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers FctGsb::estDateCampagneValidation
     * @todo   Implement testEstDateCampagneValidation().
     */
    public function testEstDateCampagneValidation() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers FctGsb::actualiserEtatFiche
     * @todo   Implement testActualiserEtatFiche().
     */
    public function testActualiserEtatFiche() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

}
