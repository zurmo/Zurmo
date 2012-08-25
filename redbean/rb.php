<?php /*
                   .______.                         
_______   ____   __| _/\_ |__   ____ _____    ____  
\_  __ \_/ __ \ / __ |  | __ \_/ __ \\__  \  /    \ 
 |  | \/\  ___
 |__|    \___  >____ |  |___  /\___  >____  /___|  /
             \/     \/      \/     \/     \/     \/ 
RedBean Database Objects -
Written by Gabor de Mooij (c) copyright 2010
RedBean is DUAL Licensed BSD and GPLv2. You may choose the license that fits
best for your project.
BSD/GPLv2 License
Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
* Redistributions of source code must retain the above copyright
notice, this list of conditions and the following disclaimer.
* Redistributions in binary form must reproduce the above copyright
notice, this list of conditions and the following disclaimer in the
documentation and/or other materials provided with the distribution.
* Neither the name of RedBeanPHP nor the
names of its contributors may be used to endorse or promote products
derived from this software without specific prior written permission.
THIS SOFTWARE IS PROVIDED BY GABOR DE MOOIJ ''AS IS'' AND ANY
EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL GABOR DE MOOIJ BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
RedBeanPHP is Written by Gabor de Mooij (G.J.G.T de Mooij) Copyright (c) 2010.
GPLv2 LICENSE
        GNU GENERAL PUBLIC LICENSE
           Version 2, June 1991
 Copyright (C) 1989, 1991 Free Software Foundation, Inc.
 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 Everyone is permitted to copy and distribute verbatim copies
 of this license document, but changing it is not allowed.
          Preamble
  The licenses for most software are designed to take away your
freedom to share and change it.  By contrast, the GNU General Public
License is intended to guarantee your freedom to share and change free
software--to make sure the software is free for all its users.  This
General Public License applies to most of the Free Software
Foundation's software and to any other program whose authors commit to
using it.  (Some other Free Software Foundation software is covered by
the GNU Lesser General Public License instead.)  You can apply it to
your programs, too.
  When we speak of free software, we are referring to freedom, not
price.  Our General Public Licenses are designed to make sure that you
have the freedom to distribute copies of free software (and charge for
this service if you wish), that you receive source code or can get it
if you want it, that you can change the software or use pieces of it
in new free programs; and that you know you can do these things.
  To protect your rights, we need to make restrictions that forbid
anyone to deny you these rights or to ask you to surrender the rights.
These restrictions translate to certain responsibilities for you if you
distribute copies of the software, or if you modify it.
  For example, if you distribute copies of such a program, whether
gratis or for a fee, you must give the recipients all the rights that
you have.  You must make sure that they, too, receive or can get the
source code.  And you must show them these terms so they know their
rights.
  We protect your rights with two steps: (1) copyright the software, and
(2) offer you this license which gives you legal permission to copy,
distribute and/or modify the software.
  Also, for each author's protection and ours, we want to make certain
that everyone understands that there is no warranty for this free
software.  If the software is modified by someone else and passed on, we
want its recipients to know that what they have is not the original, so
that any problems introduced by others will not reflect on the original
authors' reputations.
  Finally, any free program is threatened constantly by software
patents.  We wish to avoid the danger that redistributors of a free
program will individually obtain patent licenses, in effect making the
program proprietary.  To prevent this, we have made it clear that any
patent must be licensed for everyone's free use or not licensed at all.
  The precise terms and conditions for copying, distribution and
modification follow.
        GNU GENERAL PUBLIC LICENSE
   TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION
  0. This License applies to any program or other work which contains
a notice placed by the copyright holder saying it may be distributed
under the terms of this General Public License.  The "Program", below,
refers to any such program or work, and a "work based on the Program"
means either the Program or any derivative work under copyright law:
that is to say, a work containing the Program or a portion of it,
either verbatim or with modifications and/or translated into another
language.  (Hereinafter, translation is included without limitation in
the term "modification".)  Each licensee is addressed as "you".
Activities other than copying, distribution and modification are not
covered by this License; they are outside its scope.  The act of
running the Program is not restricted, and the output from the Program
is covered only if its contents constitute a work based on the
Program (independent of having been made by running the Program).
Whether that is true depends on what the Program does.
  1. You may copy and distribute verbatim copies of the Program's
source code as you receive it, in any medium, provided that you
conspicuously and appropriately publish on each copy an appropriate
copyright notice and disclaimer of warranty; keep intact all the
notices that refer to this License and to the absence of any warranty;
and give any other recipients of the Program a copy of this License
along with the Program.
You may charge a fee for the physical act of transferring a copy, and
you may at your option offer warranty protection in exchange for a fee.
  2. You may modify your copy or copies of the Program or any portion
of it, thus forming a work based on the Program, and copy and
distribute such modifications or work under the terms of Section 1
above, provided that you also meet all of these conditions:
    a) You must cause the modified files to carry prominent notices
    stating that you changed the files and the date of any change.
    b) You must cause any work that you distribute or publish, that in
    whole or in part contains or is derived from the Program or any
    part thereof, to be licensed as a whole at no charge to all third
    parties under the terms of this License.
    c) If the modified program normally reads commands interactively
    when run, you must cause it, when started running for such
    interactive use in the most ordinary way, to print or display an
    announcement including an appropriate copyright notice and a
    notice that there is no warranty (or else, saying that you provide
    a warranty) and that users may redistribute the program under
    these conditions, and telling the user how to view a copy of this
    License.  (Exception: if the Program itself is interactive but
    does not normally print such an announcement, your work based on
    the Program is not required to print an announcement.)
These requirements apply to the modified work as a whole.  If
identifiable sections of that work are not derived from the Program,
and can be reasonably considered independent and separate works in
themselves, then this License, and its terms, do not apply to those
sections when you distribute them as separate works.  But when you
distribute the same sections as part of a whole which is a work based
on the Program, the distribution of the whole must be on the terms of
this License, whose permissions for other licensees extend to the
entire whole, and thus to each and every part regardless of who wrote it.
Thus, it is not the intent of this section to claim rights or contest
your rights to work written entirely by you; rather, the intent is to
exercise the right to control the distribution of derivative or
collective works based on the Program.
In addition, mere aggregation of another work not based on the Program
with the Program (or with a work based on the Program) on a volume of
a storage or distribution medium does not bring the other work under
the scope of this License.
  3. You may copy and distribute the Program (or a work based on it,
under Section 2) in object code or executable form under the terms of
Sections 1 and 2 above provided that you also do one of the following:
    a) Accompany it with the complete corresponding machine-readable
    source code, which must be distributed under the terms of Sections
    1 and 2 above on a medium customarily used for software interchange; or,
    b) Accompany it with a written offer, valid for at least three
    years, to give any third party, for a charge no more than your
    cost of physically performing source distribution, a complete
    machine-readable copy of the corresponding source code, to be
    distributed under the terms of Sections 1 and 2 above on a medium
    customarily used for software interchange; or,
    c) Accompany it with the information you received as to the offer
    to distribute corresponding source code.  (This alternative is
    allowed only for noncommercial distribution and only if you
    received the program in object code or executable form with such
    an offer, in accord with Subsection b above.)
The source code for a work means the preferred form of the work for
making modifications to it.  For an executable work, complete source
code means all the source code for all modules it contains, plus any
associated interface definition files, plus the scripts used to
control compilation and installation of the executable.  However, as a
special exception, the source code distributed need not include
anything that is normally distributed (in either source or binary
form) with the major components (compiler, kernel, and so on) of the
operating system on which the executable runs, unless that component
itself accompanies the executable.
If distribution of executable or object code is made by offering
access to copy from a designated place, then offering equivalent
access to copy the source code from the same place counts as
distribution of the source code, even though third parties are not
compelled to copy the source along with the object code.
  4. You may not copy, modify, sublicense, or distribute the Program
except as expressly provided under this License.  Any attempt
otherwise to copy, modify, sublicense or distribute the Program is
void, and will automatically terminate your rights under this License.
However, parties who have received copies, or rights, from you under
this License will not have their licenses terminated so long as such
parties remain in full compliance.
  5. You are not required to accept this License, since you have not
signed it.  However, nothing else grants you permission to modify or
distribute the Program or its derivative works.  These actions are
prohibited by law if you do not accept this License.  Therefore, by
modifying or distributing the Program (or any work based on the
Program), you indicate your acceptance of this License to do so, and
all its terms and conditions for copying, distributing or modifying
the Program or works based on it.
  6. Each time you redistribute the Program (or any work based on the
Program), the recipient automatically receives a license from the
original licensor to copy, distribute or modify the Program subject to
these terms and conditions.  You may not impose any further
restrictions on the recipients' exercise of the rights granted herein.
You are not responsible for enforcing compliance by third parties to
this License.
  7. If, as a consequence of a court judgment or allegation of patent
infringement or for any other reason (not limited to patent issues),
conditions are imposed on you (whether by court order, agreement or
otherwise) that contradict the conditions of this License, they do not
excuse you from the conditions of this License.  If you cannot
distribute so as to satisfy simultaneously your obligations under this
License and any other pertinent obligations, then as a consequence you
may not distribute the Program at all.  For example, if a patent
license would not permit royalty-free redistribution of the Program by
all those who receive copies directly or indirectly through you, then
the only way you could satisfy both it and this License would be to
refrain entirely from distribution of the Program.
If any portion of this section is held invalid or unenforceable under
any particular circumstance, the balance of the section is intended to
apply and the section as a whole is intended to apply in other
circumstances.
It is not the purpose of this section to induce you to infringe any
patents or other property right claims or to contest validity of any
such claims; this section has the sole purpose of protecting the
integrity of the free software distribution system, which is
implemented by public license practices.  Many people have made
generous contributions to the wide range of software distributed
through that system in reliance on consistent application of that
system; it is up to the author/donor to decide if he or she is willing
to distribute software through any other system and a licensee cannot
impose that choice.
This section is intended to make thoroughly clear what is believed to
be a consequence of the rest of this License.
  8. If the distribution and/or use of the Program is restricted in
certain countries either by patents or by copyrighted interfaces, the
original copyright holder who places the Program under this License
may add an explicit geographical distribution limitation excluding
those countries, so that distribution is permitted only in or among
countries not thus excluded.  In such case, this License incorporates
the limitation as if written in the body of this License.
  9. The Free Software Foundation may publish revised and/or new versions
of the General Public License from time to time.  Such new versions will
be similar in spirit to the present version, but may differ in detail to
address new problems or concerns.
Each version is given a distinguishing version number.  If the Program
specifies a version number of this License which applies to it and "any
later version", you have the option of following the terms and conditions
either of that version or of any later version published by the Free
Software Foundation.  If the Program does not specify a version number of
this License, you may choose any version ever published by the Free Software
Foundation.
  10. If you wish to incorporate parts of the Program into other free
programs whose distribution conditions are different, write to the author
to ask for permission.  For software which is copyrighted by the Free
Software Foundation, write to the Free Software Foundation; we sometimes
make exceptions for this.  Our decision will be guided by the two goals
of preserving the free status of all derivatives of our free software and
of promoting the sharing and reuse of software generally.
          NO WARRANTY
  11. BECAUSE THE PROGRAM IS LICENSED FREE OF CHARGE, THERE IS NO WARRANTY
FOR THE PROGRAM, TO THE EXTENT PERMITTED BY APPLICABLE LAW.  EXCEPT WHEN
OTHERWISE STATED IN WRITING THE COPYRIGHT HOLDERS AND/OR OTHER PARTIES
PROVIDE THE PROGRAM "AS IS" WITHOUT WARRANTY OF ANY KIND, EITHER EXPRESSED
OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE.  THE ENTIRE RISK AS
TO THE QUALITY AND PERFORMANCE OF THE PROGRAM IS WITH YOU.  SHOULD THE
PROGRAM PROVE DEFECTIVE, YOU ASSUME THE COST OF ALL NECESSARY SERVICING,
REPAIR OR CORRECTION.
  12. IN NO EVENT UNLESS REQUIRED BY APPLICABLE LAW OR AGREED TO IN WRITING
WILL ANY COPYRIGHT HOLDER, OR ANY OTHER PARTY WHO MAY MODIFY AND/OR
REDISTRIBUTE THE PROGRAM AS PERMITTED ABOVE, BE LIABLE TO YOU FOR DAMAGES,
INCLUDING ANY GENERAL, SPECIAL, INCIDENTAL OR CONSEQUENTIAL DAMAGES ARISING
OUT OF THE USE OR INABILITY TO USE THE PROGRAM (INCLUDING BUT NOT LIMITED
TO LOSS OF DATA OR DATA BEING RENDERED INACCURATE OR LOSSES SUSTAINED BY
YOU OR THIRD PARTIES OR A FAILURE OF THE PROGRAM TO OPERATE WITH ANY OTHER
PROGRAMS), EVEN IF SUCH HOLDER OR OTHER PARTY HAS BEEN ADVISED OF THE
POSSIBILITY OF SUCH DAMAGES.
*/

/**
 * RedBean_ObjectDatabase
 * @file 		RedBean/RedBean_ObjectDatabase.php
 * @description		RedBean simulates an object oriented database. This interface
 *					describes the API for the object database. It is the
 *					abstract core of RedBean describing its main functionality.
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
interface RedBean_ObjectDatabase {
	/**
	 * This interface describes how ANY Object Database should
	 * behave.For detailed descriptions of RedBean specific implementation
	 * see: RedBean_OODB.
	 * An Object Database should be able to load a bean using a $type and $id.
	 * The $type argument indicated what kind of bean you are looking for.
	 * The $id argument specifies the primary key ID; which links the bean to
	 * a (series) of record(s) in the database.
	 *
	 * @param string $type
	 * @param integer $id
	 *
	 * @return RedBean_OODBBean $bean
	 */
	public function load( $type, $id );
	/**
	 * This interface describes how ANY Object Database should
	 * behave.For detailed descriptions of RedBean specific implementation
	 * see: RedBean_OODB.
	 * An Object Database should be able to store a RedBean_OODBBean $bean.
	 *
	 * @param RedBean_OODBBean $bean
	 *
	 * @return integer $newid
	 */
	public function store( RedBean_OODBBean $bean );
	/**
	 * This interface describes how ANY Object Database should
	 * behave.For detailed descriptions of RedBean specific implementation
	 * see: RedBean_OODB.
	 *
	 * @param RedBean_OODBBean $bean
	 */
	public function trash( RedBean_OODBBean $bean );
	/**
	 * This interface describes how ANY Object Database should
	 * behave.For detailed descriptions of RedBean specific implementation
	 * see: RedBean_OODB.
	 *
	 * @param string $type
	 * @param array $ids
	 *
	 * @return array $beans
	 */
	public function batch( $type, $ids );
	/**
	 * This interface describes how ANY Object Database should
	 * behave.For detailed descriptions of RedBean specific implementation
	 * see: RedBean_OODB.
	 *
	 * @param string $type
	 *
	 * @return RedBean_OODBBean $bean
	 */
	public function dispense( $type );
	/**
	 * This interface describes how ANY Object Database should
	 * behave.For detailed descriptions of RedBean specific implementation
	 * see: RedBean_OODB.
	 *
	 * @param string $type
	 *
	 * @return integer $numbeans
	 */
	public function count( $type );
	/**
	 * This interface describes how ANY Object Database should
	 * behave.For detailed descriptions of RedBean specific implementation
	 * see: RedBean_OODB.
	 *
	 * @param string $type
	 *
	 * @return mixed $undefined (impl. specific)
	 */
	public function wipe( $type );
	/**
	 * =====================================================
	 * Note: that not all methods in OODB are mentioned here;
	 * freeze(), isFrozen(), convertToBeans() etc. are extra
	 * services provided by OODB but not required for the
	 * Object Database interface to be implemented!
	 *
	 * If you are writing Hyper-portable code, please do
	 * not rely on OODB specific methods...!
	 * =====================================================
	 *
	 */
}
/**
 * RedBean Plugin
 * @file			RedBean/Plugin.php
 * @description		Marker interface for plugins.
 *
 * @author			Gabor de Mooij
 * @license			BSD
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
interface RedBean_Plugin {
}
/**
 * Interface for database drivers
 * @file					RedBean/Driver.php
 * @description		Describes the API for database classes
 *							The Driver API conforms to the ADODB pseudo standard
 *							for database drivers.
 * @author				Gabor de Mooij
 * @license				BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
interface RedBean_Driver {
	/**
	 * Implements Singleton (or multiton)
	 * Requests an instance of the database
	 * Returns an instance of the driver Driver wrapper.
	 *
	 * @param string $dsn    Database connection string
	 * @param string $user   DB account to be used
	 * @param string $pass   password
	 * @param string $dbname name of the database you
	 *
	 * @return RedBean_Driver $driver	  driver wrapper instance
	 */
	public static function getInstance( $host, $user, $pass, $dbname );
	/**
	 * Runs a query and fetches results as a multi dimensional array.
	 *
	 * @param  string $sql SQL to be executed
	 *
	 * @return array $results result
	 */
	public function GetAll( $sql, $aValues=array() );
	/**
	 * Runs a query and fetches results as a column.
	 *
	 * @param  string $sql SQL Code to execute
	 *
	 * @return array	$results Resultset
	 */
	public function GetCol( $sql, $aValues=array() );
	/**
	 * Runs a query an returns results as a single cell.
	 *
	 * @param string $sql SQL to execute
	 *
	 * @return mixed $cellvalue result cell
	 */
	public function GetCell( $sql, $aValues=array() );
	/**
	 * Runs a query and returns a flat array containing the values of
	 * one row.
	 *
	 * @param string $sql SQL to execute
	 *
	 * @return array $row result row
	 */
	public function GetRow( $sql, $aValues=array() );
	/**
	 * Returns the error constant of the most
	 * recent error.
	 *
	 * @return mixed $error error code
	 */
	public function ErrorNo();
	/**
	 * Returns the error message of the most recent
	 * error.
	 *
	 * @return string $message error message
	 */
	public function Errormsg();
	/**
	 * Executes SQL code and allows key-value binding.
	 * This function allows you to provide an array with values to bind
	 * to query parameters. For instance you can bind values to question
	 * marks in the query. Each value in the array corresponds to the
	 * question mark in the query that matches the position of the value in the
	 * array. You can also bind values using explicit keys, for instance
	 * array(":key"=>123) will bind the integer 123 to the key :key in the
	 * SQL. This method has no return value.
	 *
	 * @param string $sql	  SQL Code to execute
	 * @param array  $aValues Values to bind to SQL query
	 *
	 * @return void
	 */
	public function Execute( $sql, $aValues=array() );
	/**
	 * Escapes a string for use in SQL using the currently selected
	 * driver driver.
	 *
	 * @param string $string string to be escaped
	 *
	 * @return string $string escaped string
	 */
	public function Escape( $str );
	/**
	 * Returns the latest insert ID if driver does support this
	 * feature.
	 *
	 * @return integer $id primary key ID
	 */
	public function GetInsertID();
	/**
	 * Returns the number of rows affected by the most recent query
	 * if the currently selected driver driver supports this feature.
	 *
	 * @return integer $numOfRows number of rows affected
	 */
	public function Affected_Rows();
	/**
	 * Toggles debug mode. In debug mode the driver will print all
	 * SQL to the screen together with some information about the
	 * results. All SQL code that passes through the driver will be
	 * passes on to the screen for inspection.
	 * This method has no return value.
	 *
	 * @param boolean $trueFalse turn on/off
	 *
	 * @return void
	 */
	public function setDebugMode( $tf );
	/**
	 * Returns a raw result resource from the underlying driver driver.
	 *
	 * @return Resource $driverResult driver result resource object
	 */
	public function GetRaw();
	/**
	 * Starts a transaction.
	 * This method is part of the transaction mechanism of
	 * RedBeanPHP. All queries in a transaction are executed together.
	 * In case of an error all commands will be rolled back so none of the
	 * SQL in the transaction will affect the DB. Using transactions is
	 * considered best practice.
	 * This method has no return value.
	 *
	 * @return void
	 */
	public function CommitTrans();
	/**
	 * Commits a transaction.
	 * This method is part of the transaction mechanism of
	 * RedBeanPHP. All queries in a transaction are executed together.
	 * In case of an error all commands will be rolled back so none of the
	 * SQL in the transaction will affect the DB. Using transactions is
	 * considered best practice.
	 * This method has no return value.
	 *
	 * @return void
	 */
	public function StartTrans();
	/**
	 * Rolls back a transaction.
	 * This method is part of the transaction mechanism of
	 * RedBeanPHP. All queries in a transaction are executed together.
	 * In case of an error all commands will be rolled back so none of the
	 * SQL in the transaction will affect the DB. Using transactions is
	 * considered best practice.
	 * This method has no return value.
	 *
	 * @return void
	 */
	public function FailTrans();
}

/**
 * PDO Driver
 * @file				RedBean/PDO.php
 * @description	PDO Driver
 *						This Driver implements the RedBean Driver API
 * @author			Desfrenes
 * @license			BSD
 *
 *
 * (c) Desfrenes & Gabor de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 *
 */
class RedBean_Driver_PDO implements RedBean_Driver {
	/**
	 * @var string
	 * Contains database DSN for connecting to database.
	 */
	private $dsn;
	/**
	 * @var RedBean_Driver_PDO
	 * Holds the instance of this class.
	 */
	private static $instance;
	/**
	 * @var boolean
	 * Whether we are in debugging mode or not.
	 */
	private $debug = false;
	/**
	 * @var PDO
	 * Holds the PDO instance.
	 */
	private $pdo;
	/**
	 * @var integer
	 * Holds integer number of affected rows from latest query
	 * if driver supports this feature.
	 */
	private $affected_rows;
	/**
	 * @var resource
	 * Holds result resource.
	 */
	private $rs;
	/**
	 * @var boolean
	 * Flag, indicates whether SQL execution has taken place.
	 */
	private $exc =0;
	/**
	 * @var array
	 * Contains arbitrary connection data.
	 *
	 */
	private $connectInfo = array();
	/**
	 * @var bool
	 * Whether you want to use classic String Only binding -
	 * backward compatibility.
	 */
	public $flagUseStringOnlyBinding = false;
	/**
	 *
	 * @var boolean
	 * 
	 * Whether we are currently connected or not.
	 * This flag is being used to delay the connection until necessary.
	 * Delaying connections is a good practice to speed up scripts that
	 * don't need database connectivity but for some reason want to
	 * init RedbeanPHP.
	 */
	private $isConnected = false;
	/**
	 * Returns an instance of the PDO Driver.
	 *
	 * @param string $dsn    Database connection string
	 * @param string $user   DB account to be used
	 * @param string $pass   password
	 * @param string $dbname name of the database you
	 *
	 * @return RedBean_Driver_PDO $pdo	  PDO wrapper instance
	 */
	public static function getInstance($dsn, $user, $pass, $dbname) {
		if(is_null(self::$instance)) {
			self::$instance = new RedBean_Driver_PDO($dsn, $user, $pass);
		}
		return self::$instance;
	}
	/**
	 * Constructor. You may either specify dsn, user and password or
	 * just give an existing PDO connection.
	 * Examples:
	 *    $driver = new RedBean_Driver_PDO($dsn, $user, $password);
	 *    $driver = new RedBean_Driver_PDO($existingConnection);
	 *
	 * @param string|PDO  $dsn	 database connection string
	 * @param string      $user optional
	 * @param string      $pass optional
	 *
	 * @return void
	 */
	public function __construct($dsn, $user = NULL, $pass = NULL) {
		if ($dsn instanceof PDO) {
			$this->pdo = $dsn;
			$this->isConnected = true;
			$this->pdo->setAttribute(1002, 'SET NAMES utf8');
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			
			
			
			$this->dsn = $this->getDatabaseType();
		} else {
			$this->dsn = $dsn;
			$this->connectInfo = array( "pass"=>$pass, "user"=>$user );
		}
	}
	/**
	 * Establishes a connection to the database using PHP PDO
	 * functionality. If a connection has already been established this
	 * method will simply return directly. This method also turns on
	 * UTF8 for the database and PDO-ERRMODE-EXCEPTION as well as
	 * PDO-FETCH-ASSOC.
	 *
	 * @return void
	 */
	public function connect() {
		if ($this->isConnected) return;
		$user = $this->connectInfo["user"];
		$pass = $this->connectInfo["pass"];
		
		$this->pdo = new PDO(
				  $this->dsn,
				  $user,
				  $pass,
				  array(1002 => 'SET NAMES utf8',
							 PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
							 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				  )
		);
		$this->pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, TRUE);
		$this->isConnected = true;
	}
	/**
	 * Runs a query and fetches results as a multi dimensional array.
	 *
	 * @param  string $sql SQL to be executed
	 *
	 * @return array $results result
	 */
	public function GetAll( $sql, $aValues=array() ) {
		$this->connect();
		$this->exc = 0;
		if ($this->debug) {
			echo "<HR>" . $sql.print_r($aValues,1);
		}
		try {
			if (strpos("pgsql",$this->dsn)===0) {
				$s = $this->pdo->prepare($sql, array(PDO::PGSQL_ATTR_DISABLE_NATIVE_PREPARED_STATEMENT => true));
			}
			else {
				$s = $this->pdo->prepare($sql);
			}
			if ($this->flagUseStringOnlyBinding) {
				$s->execute($aValues);
			}
			else {
				foreach($aValues as $key=>&$value) {
					if (is_integer($key)) {
						if (ctype_digit(strval($value)) && $value < 2147483648) { $s->bindParam($key+1,$value,PDO::PARAM_INT); }
						else $s->bindParam($key+1,$value,PDO::PARAM_STR);
					}
					else {
						if (ctype_digit(strval($value)) &&  $value < 2147483648) $s->bindParam($key,$value,PDO::PARAM_INT);
						else $s->bindParam($key,$value,PDO::PARAM_STR);
					}
				}
				$s->execute();
			}
			
		  if ($s->columnCount()) {
		    $this->rs = $s->fetchAll();
		    
		    
	    }
		  else {
		    $this->rs = null;
		  }
		  
			$rows = $this->rs;
		}catch(PDOException $e) {
			
			
			if (version_compare(PHP_VERSION, '5.3.0', '<')) {
				$x = new RedBean_Exception_SQL( $e->getMessage(), 0);
			}
			else {
				$x = new RedBean_Exception_SQL( $e->getMessage(), 0, $e );
			}
			$x->setSQLState( $e->getCode() );
			throw $x;
		}
		if(!$rows) {
			$rows = array();
		}
		if ($this->debug) {
			if (count($rows) > 0) {
				echo "<br><b style='color:green'>resultset: " . count($rows) . " rows</b>";
			}
		}
		return $rows;
	}
	 /**
	 * Runs a query and fetches results as a column.
	 *
	 * @param  string $sql SQL Code to execute
	 *
	 * @return array	$results Resultset
	 */
	public function GetCol($sql, $aValues=array()) {
		$this->connect();
		$this->exc = 0;
		$rows = $this->GetAll($sql,$aValues);
		$cols = array();
		if ($rows && is_array($rows) && count($rows)>0) {
			foreach ($rows as $row) {
				$cols[] = array_shift($row);
			}
		}
		return $cols;
	}
	/**
	 * Runs a query an returns results as a single cell.
	 *
	 * @param string $sql SQL to execute
	 *
	 * @return mixed $cellvalue result cell
	 */
	public function GetCell($sql, $aValues=array()) {
		$this->connect();
		$this->exc = 0;
		$arr = $this->GetAll($sql,$aValues);
		$row1 = array_shift($arr);
		$col1 = array_shift($row1);
		return $col1;
	}
	/**
	 * Runs a query and returns a flat array containing the values of
	 * one row.
	 *
	 * @param string $sql SQL to execute
	 *
	 * @return array $row result row
	 */
	public function GetRow($sql, $aValues=array()) {
		$this->connect();
		$this->exc = 0;
		$arr = $this->GetAll($sql, $aValues);
		return array_shift($arr);
	}
	/**
	 * Returns the error constant of the most
	 * recent error.
	 *
	 * @return mixed $error error code
	 */
	public function ErrorNo() {
		$this->connect();
		if (!$this->exc) return 0;
		$infos = $this->pdo->errorInfo();
		return $infos[1];
	}
	/**
	 * Returns the error message of the most recent
	 * error.
	 *
	 * @return string $message error message
	 */
	public function Errormsg() {
		$this->connect();
		if (!$this->exc) return "";
		$infos = $this->pdo->errorInfo();
		return $infos[2];
	}
	/**
	 * Executes SQL code and allows key-value binding.
	 * This function allows you to provide an array with values to bind
	 * to query parameters. For instance you can bind values to question
	 * marks in the query. Each value in the array corresponds to the
	 * question mark in the query that matches the position of the value in the
	 * array. You can also bind values using explicit keys, for instance
	 * array(":key"=>123) will bind the integer 123 to the key :key in the
	 * SQL. This method has no return value.
	 *
	 * @param string $sql	  SQL Code to execute
	 * @param array  $aValues Values to bind to SQL query
	 *
	 * @return void
	 */
	public function Execute( $sql, $aValues=array() ) {
		$this->connect();
		$this->exc = 0;
		if ($this->debug) {
			echo "<HR>" . $sql.print_r($aValues,1);
		}
		try {
			if (strpos("pgsql",$this->dsn)===0) {
				$s = $this->pdo->prepare($sql, array(PDO::PGSQL_ATTR_DISABLE_NATIVE_PREPARED_STATEMENT => true));
			}
			else {
				$s = $this->pdo->prepare($sql);
			}
			
			if ($this->flagUseStringOnlyBinding) {
				$s->execute($aValues);
			}
			else {
			
				foreach($aValues as $key=>&$value) {
					if (is_integer($key)) {
						if (ctype_digit(strval($value)) && $value < 2147483648) { $s->bindParam($key+1,$value,PDO::PARAM_INT); }
						else $s->bindParam($key+1,$value,PDO::PARAM_STR);
					}
					else {
						if (ctype_digit(strval($value)) &&  $value < 2147483648) $s->bindParam($key,$value,PDO::PARAM_INT);
						else $s->bindParam($key,$value,PDO::PARAM_STR);
					}
				}
				$s->execute();
			}
			$this->affected_rows=$s->rowCount();
			return $this->affected_rows;
		}
		catch(PDOException $e) {
			
			
			if (version_compare(PHP_VERSION, '5.3.0', '<')) {
				$x = new RedBean_Exception_SQL( $e->getMessage(), 0);
			}
			else {
				$x = new RedBean_Exception_SQL( $e->getMessage(), 0, $e );
			}
			$x->setSQLState( $e->getCode() );
			throw $x;
		}
	}
	/**
	 * Escapes a string for use in SQL using the currently selected
	 * PDO driver.
	 *
	 * @param string $string string to be escaped
	 *
	 * @return string $string escaped string
	 */
	public function Escape( $str ) {
		$this->connect();
		return substr(substr($this->pdo->quote($str), 1), 0, -1);
	}
	/**
	 * Returns the latest insert ID if driver does support this
	 * feature.
	 *
	 * @return integer $id primary key ID
	 */
	public function GetInsertID() {
		$this->connect();
		return (int) $this->pdo->lastInsertId();
	}
	/**
	 * Returns the number of rows affected by the most recent query
	 * if the currently selected PDO driver supports this feature.
	 *
	 * @return integer $numOfRows number of rows affected
	 */
	public function Affected_Rows() {
		$this->connect();
		return (int) $this->affected_rows;
	}
	/**
	 * Toggles debug mode. In debug mode the driver will print all
	 * SQL to the screen together with some information about the
	 * results. All SQL code that passes through the driver will be
	 * passes on to the screen for inspection.
	 * This method has no return value.
	 *
	 * @param boolean $trueFalse turn on/off
	 *
	 * @return void
	 */
	public function setDebugMode( $tf ) {
		$this->connect();
		$this->debug = (bool)$tf;
	}
	/**
	 * Returns a raw result resource from the underlying PDO driver.
	 *
	 * @return Resource $PDOResult PDO result resource object
	 */
	public function GetRaw() {
		$this->connect();
		return $this->rs;
	}
	/**
	 * Starts a transaction.
	 * This method is part of the transaction mechanism of
	 * RedBeanPHP. All queries in a transaction are executed together.
	 * In case of an error all commands will be rolled back so none of the
	 * SQL in the transaction will affect the DB. Using transactions is
	 * considered best practice.
	 * This method has no return value.
	 *
	 * @return void
	 */
	public function StartTrans() {
		$this->connect();
		$this->pdo->beginTransaction();
	}
	/**
	 * Commits a transaction.
	 * This method is part of the transaction mechanism of
	 * RedBeanPHP. All queries in a transaction are executed together.
	 * In case of an error all commands will be rolled back so none of the
	 * SQL in the transaction will affect the DB. Using transactions is
	 * considered best practice.
	 * This method has no return value.
	 *
	 * @return void
	 */
	public function CommitTrans() {
		$this->connect();
		$this->pdo->commit();
	}
	/**
	 * Rolls back a transaction.
	 * This method is part of the transaction mechanism of
	 * RedBeanPHP. All queries in a transaction are executed together.
	 * In case of an error all commands will be rolled back so none of the
	 * SQL in the transaction will affect the DB. Using transactions is
	 * considered best practice.
	 * This method has no return value.
	 *
	 * @return void
	 */
	public function FailTrans() {
		$this->connect();
		$this->pdo->rollback();
	}
	/**
	 * Returns the name of the database type/brand: i.e. mysql, db2 etc.
	 *
	 * @return string $typeName database identification
	 */
	public function getDatabaseType() {
		$this->connect();
		return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
	}
	/**
	 * Returns the version number of the database.
	 *
	 * @return mixed $version version number of the database
	 */
	public function getDatabaseVersion() {
		$this->connect();
		return $this->pdo->getAttribute(PDO::ATTR_CLIENT_VERSION);
	}
	/**
	 * Returns the underlying PHP PDO instance.
	 * 
	 * @return PDO $pdo PDO instance used by PDO wrapper
	 */
	public function getPDO() {
		$this->connect();
		return $this->pdo;
	}
}

/**
 * RedBean_OODBBean (Object Oriented DataBase Bean)
 * @file 		RedBean/RedBean_OODBBean.php
 * @description		The Bean class used for passing information
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_OODBBean implements IteratorAggregate, ArrayAccess {
	private $properties = array();
	/**
	 * Meta Data storage. This is the internal property where all
	 * Meta information gets stored.
	 * @var array
	 */
	private $__info = NULL;
	public function getIterator() {
		return new ArrayIterator($this->properties);
	}
	/**
	 * Imports all values in associative array $array. Every key is used
	 * for a property and every value will be assigned to the property
	 * identified by the key. So basically this method converts the
	 * associative array to a bean by loading the array. You can filter
	 * the values using the $selection parameter. If $selection is boolean
	 * false, no filtering will be applied. If $selection is an array
	 * only the properties specified (as values) in the $selection
	 * array will be taken into account. To skip a property, omit it from
	 * the $selection array. Also, instead of providing an array you may
	 * pass a comma separated list of property names. This method is
	 * chainable because it returns its own object.
	 * Imports data into bean
	 * @param array $array
	 * @param mixed $selection
	 * @param boolean $notrim
	 * @return RedBean_OODBBean $this
	 */
	public function import( $arr, $selection=false, $notrim=false ) {
		if (is_string($selection)) $selection = explode(",",$selection);
		
		if (!$notrim && is_array($selection)) foreach($selection as $k=>$s){ $selection[$k]=trim($s); }
		foreach($arr as $k=>$v) {
			if ($k != "__info") {
				if (!$selection || ($selection && in_array($k,$selection))) {
					$this->$k = $v;
				}
			}
		}
		return $this;
	}
	/**
	 * Exports the bean as an array.
	 * This function exports the contents of a bean to an array and returns
	 * the resulting array. If $meta eq uals boolean TRUE, then the array will
	 * also contain the __info section containing the meta data inside the
	 * RedBean_OODBBean Bean object.
	 * @param boolean $meta
	 * @return array $arr
	 */
	public function export($meta = false) {
		$arr = array();
		$arr = $this->properties;
		if ($meta) $arr["__info"] = $this->__info;
		return $arr;
	}
	/**
	 * Implements isset() function for use as an array.
	 * Returns whether bean has an element with key
	 * named $property. Returns TRUE if such an element exists
	 * and FALSE otherwise.
	 * @param string $property
	 * @return boolean $hasProperty
	 */
	public function __isset( $property ) {
		return (isset($this->properties[$property]));
	}
	/**
	 * Magic Getter. Gets the value for a specific property in the bean.
	 * If the property does not exist this getter will make sure no error
	 * occurs. This is because RedBean allows you to query (probe) for
	 * properties. If the property can not be found this method will
	 * return NULL instead.
	 * @param string $property
	 * @return mixed $value
	 */
	public function __get( $property ) {
		if (!isset($this->properties[$property]))  return NULL;
		return $this->properties[$property];
	}
	/**
	 * Magic Setter. Sets the value for a specific property.
	 * This setter acts as a hook for OODB to mark beans as tainted.
	 * The tainted meta property can be retrieved using getMeta("tainted").
	 * The tainted meta property indicates whether a bean has been modified and
	 * can be used in various caching mechanisms.
	 * @param string $property
	 * @param  mixed $value
	 */
	public function __set( $property, $value ) {
		$this->setMeta("tainted",true);
		if ($value===false) {
			$value = "0";
		}
		if ($value===true) {
			$value = "1";
		}
		$this->properties[$property] = $value;
	}
	/**
	 * Returns the value of a meta property. A meta property
	 * contains extra information about the bean object that will not
	 * get stored in the database. Meta information is used to instruct
	 * RedBean as well as other systems how to deal with the bean.
	 * For instance: $bean->setMeta("buildcommand.unique.0", array(
	 * "column1", "column2", "column3") );
	 * Will add a UNIQUE constaint for the bean on columns: column1, column2 and
	 * column 3.
	 * To access a Meta property we use a dot separated notation.
	 * If the property cannot be found this getter will return NULL instead.
	 * @param string $path
	 * @param mixed $default
	 * @return mixed $value
	 */
	public function getMeta( $path, $default = NULL) {
		return (isset($this->__info[$path])) ? $this->__info[$path] : $default;
	}
	/**
	 * Stores a value in the specified Meta information property. $value contains
	 * the value you want to store in the Meta section of the bean and $path
	 * specifies the dot separated path to the property. For instance "my.meta.property".
	 * If "my" and "meta" do not exist they will be created automatically.
	 * @param string $path
	 * @param mixed $value
	 */
	public function setMeta( $path, $value ) {
		$this->__info[$path] = $value;
	}
	/**
	 * Copies the meta information of the specified bean
	 * This is a convenience method to enable you to
	 * exchange meta information easily.
	 * @param RedBean_OODBBean $bean
	 * @return RedBean_OODBBean
	 */
	public function copyMetaFrom( RedBean_OODBBean $bean ) {
		$this->__info = $bean->__info;
		return $this;
	}
	/**
	 * Sleep function fore serialize() call. This will be invoked if you
	 * perform a serialize() operation.
	 *
	 * @return mixed $array
	 */
	public function __sleep() {
		
		return array('properties','__info');
	}
	/**
	 * Reroutes a call to Model if exists. (new fuse)
	 * @param string $method
	 * @param array $args
	 * @return mixed $mixed
	 */
	public function __call($method, $args) {
		return null;
		if (!isset($this->__info["model"])) {
			
			$modelName = RedBean_ModelHelper::getModelName( $this->getMeta("type") );
			if (!class_exists($modelName)) return null;
			$obj = new $modelName();
			$obj->loadBean($this);
			$this->__info["model"] = $obj;
		}
		if (!method_exists($this->__info["model"],$method)) return null;
		return call_user_func_array(array($this->__info["model"],$method), $args);
	}
	/**
	 * Implementation of __toString Method
	 * Routes call to Model.
	 * @return string $string
	 */
	public function __toString() {
		return $this->__call('__toString',array());
	}
	/**
	 * Implementation of Array Access Interface, you can access bean objects
	 * like an array.
	 * Call gets routed to __set.
	 *
	 * @param  mixed $offset offset string
	 * @param  mixed $value value
	 *
	 * @return void
	 */
	public function offsetSet($offset, $value) {
        $this->__set($offset, $value);
    }
	/**
	 * Implementation of Array Access Interface, you can access bean objects
	 * like an array.
	 *
	 * @param  mixed $offset property
	 *
	 * @return
	 */
    public function offsetExists($offset) {
        return isset($this->properties[$offset]);
    }
	/**
	 * Implementation of Array Access Interface, you can access bean objects
	 * like an array.
	 * Unsets a value from the array/bean.
	 *
	 * @param  mixed $offset property
	 *
	 * @return
	 */
    public function offsetUnset($offset) {
        unset($this->properties[$offset]);
    }
	/**
	 * Implementation of Array Access Interface, you can access bean objects
	 * like an array.
	 * Returns value of a property.
	 *
	 * @param  mixed $offset property
	 *
	 * @return
	 */
    public function offsetGet($offset) {
        return $this->__get($offset);
    }
}

/**
 * Observable
 * Base class for Observables
 * @file 		RedBean/Observable.php
 * @description		Part of the observer pattern in RedBean
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
abstract class RedBean_Observable {
	/**
	 *
	 * @var array
	 */
	private $observers = array();
	/**
	 * Adds a listener to this instance
	 * @param $eventname
	 * @param $observer
	 * @return unknown_type
	 */
	public function addEventListener( $eventname, RedBean_Observer $observer ) {
		if (!isset($this->observers[ $eventname ])) {
			$this->observers[ $eventname ] = array();
		}
		
		foreach($this->observers[$eventname] as $o) if ($o==$observer) return;
		$this->observers[ $eventname ][] = $observer;
	}
	/**
	 * Sends an event (signal) to the registered listeners
	 * @param $eventname
	 * @return unknown_type
	 */
	public function signal( $eventname, $info ) {
		if (!isset($this->observers[ $eventname ])) {
			$this->observers[ $eventname ] = array();
		}
		foreach($this->observers[$eventname] as $observer) {
			$observer->onEvent( $eventname, $info );
		}
	}
}
/**
 * Observer
 * @file 		RedBean/Observer.php
 * @description		Part of the observer pattern in RedBean
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
interface RedBean_Observer {
	/**
	 * Part of the RedBean Observer Infrastructure.
	 * @param string $eventname
	 * @param RedBean_OODBBean $bean
	 */
	public function onEvent( $eventname, $bean );
}
/**
 * Adapter Interface
 * @file 		RedBean/Adapter.php
 * @description		Describes the API for a RedBean Database Adapter.
 * @author			Gabor de Mooij
 * @license			BSD
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 *
 */
interface RedBean_Adapter {
	/**
	 * Returns the latest SQL statement
	 *
	 * @return string $SQLString SQLString
	 */
	public function getSQL();
	/**
	 * Escapes a value for usage in an SQL statement
	 *
	 * @param string $sqlvalue value
	 */
	public function escape( $sqlvalue );
	/**
	 * Executes an SQL Statement using an array of values to bind
	 * If $noevent is TRUE then this function will not signal its
	 * observers to notify about the SQL execution; this to prevent
	 * infinite recursion when using observers.
	 *
	 * @param string  $sql     SQL
	 * @param array   $aValues values
	 * @param boolean $noevent no event firing
	 */
	public function exec( $sql , $aValues=array(), $noevent=false);
	/**
	 * Executes an SQL Query and returns a resultset.
	 * This method returns a multi dimensional resultset similar to getAll
	 * The values array can be used to bind values to the place holders in the
	 * SQL query.
	 *
	 * @param string $sql     SQL
	 * @param array  $aValues values
	 */
	public function get( $sql, $aValues = array() );
	/**
	 * Executes an SQL Query and returns a resultset.
	 * This method returns a single row (one array) resultset.
	 * The values array can be used to bind values to the place holders in the
	 * SQL query.
	 *
	 * @param string $sql	  SQL
	 * @param array  $aValues values to bind
	 *
	 * @return array $aMultiDimArray row
	 */
	public function getRow( $sql, $aValues = array() );
	/**
	 * Executes an SQL Query and returns a resultset.
	 * This method returns a single column (one array) resultset.
	 * The values array can be used to bind values to the place holders in the
	 * SQL query.
	 *
	 * @param string $sql	  SQL
	 * @param array  $aValues values to bind
	 *
	 * @return array $aSingleDimArray column
	 */
	public function getCol( $sql, $aValues = array() );
	/**
	 * Executes an SQL Query and returns a resultset.
	 * This method returns a single cell, a scalar value as the resultset.
	 * The values array can be used to bind values to the place holders in the
	 * SQL query.
	 *
	 * @param string $sql     SQL
	 * @param array  $aValues values to bind
	 *
	 * @return string $sSingleValue value from cell
	 */
	public function getCell( $sql, $aValues = array() );
	/**
	 * Executes the SQL query specified in $sql and takes
	 * the first two columns of the resultset. This function transforms the
	 * resultset into an associative array. Values from the the first column will
	 * serve as keys while the values of the second column will be used as values.
	 * The values array can be used to bind values to the place holders in the
	 * SQL query.
	 *
	 * @param string $sql    SQL
	 * @param array  $values values to bind
	 *
	 * @return array $associativeArray associative array result set
	 */
	public function getAssoc( $sql, $values = array() );
	/**
	 * Returns the latest insert ID.
	 *
	 * @return integer $id primary key ID
	 */
	public function getInsertID();
	/**
	 * Returns the number of rows that have been
	 * affected by the last update statement.
	 *
	 * @return integer $count number of rows affected
	 */
	public function getAffectedRows();
	/**
	 * Returns the original database resource. This is useful if you want to
	 * perform operations on the driver directly instead of working with the
	 * adapter. RedBean will only access the adapter and never to talk
	 * directly to the driver though.
	 *
	 * @return object $driver driver
	 */
	public function getDatabase();
	/**
	 * Returns the latest error message; if any.
	 *
	 * @return string $message error message from server
	 */
	public function getErrorMsg();
	/**
	 * This method is part of the RedBean Transaction Management
	 * mechanisms.
	 * Starts a transaction.
	 */
	public function startTransaction();
	/**
	 * This method is part of the RedBean Transaction Management
	 * mechanisms.
	 * Commits the transaction.
	 */
	public function commit();
	/**
	 * This method is part of the RedBean Transaction Management
	 * mechanisms.
	 * Rolls back the transaction.
	 */
	public function rollback();
}
/**
 * DBAdapter		(Database Adapter)
 * @file				RedBean/Adapter/DBAdapter.php
 * @description	An adapter class to connect various database systems to RedBean
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_Adapter_DBAdapter extends RedBean_Observable implements RedBean_Adapter {
	/**
	 * @var RedBean_Driver
	 *
	 * ADODB compatible class
	 */
	private $db = null;
	/**
	 * @var string
	 *
	 * Contains SQL snippet
	 */
	private $sql = "";
	/**
	 * Constructor.
	 * Creates an instance of the RedBean Adapter Class.
	 * This class provides an interface for RedBean to work
	 * with ADO compatible DB instances.
	 *
	 * @param RedBean_Driver $database ADO Compatible DB Instance
	 */
	public function __construct($database) {
		$this->db = $database;
	}
	/**
	 * Returns the latest SQL Statement.
	 *
	 * @return string $SQL latest SQL statement
	 */
	public function getSQL() {
		return $this->sql;
	}
	/**
	 * Escapes a string for use in a Query.
	 *
	 * @param  string $sqlvalue SQL value to escape
	 *
	 * @return string $escapedValue escaped value
	 */
	public function escape( $sqlvalue ) {
		return $this->db->Escape($sqlvalue);
	}
	/**
	 * Executes SQL code; any query without
	 * returning a resultset.
	 * This function allows you to provide an array with values to bind
	 * to query parameters. For instance you can bind values to question
	 * marks in the query. Each value in the array corresponds to the
	 * question mark in the query that matches the position of the value in the
	 * array. You can also bind values using explicit keys, for instance
	 * array(":key"=>123) will bind the integer 123 to the key :key in the
	 * SQL.
	 *
	 * @param  string  $sql			SQL Code to execute
	 * @param  array   $values		assoc. array binding values
	 * @param  boolean $noevent   if TRUE this will suppress the event 'sql_exec'
	 *
	 * @return mixed  $undefSet	whatever driver returns, undefined
	 */
	public function exec( $sql , $aValues=array(), $noevent=false) {
		if (!$noevent) {
			$this->sql = $sql;
			$this->signal("sql_exec", $this);
		}
		return $this->db->Execute( $sql, $aValues );
	}
	/**
	 * Multi array SQL fetch. Fetches a multi dimensional array.
	 * This function allows you to provide an array with values to bind
	 * to query parameters. For instance you can bind values to question
	 * marks in the query. Each value in the array corresponds to the
	 * question mark in the query that matches the position of the value in the
	 * array. You can also bind values using explicit keys, for instance
	 * array(":key"=>123) will bind the integer 123 to the key :key in the
	 * SQL.
	 *
	 * @param  string $sql		SQL code to execute
	 * @param  array  $values	assoc. array binding values
	 *
	 * @return array  $result	two dimensional array result set
	 */
	public function get( $sql, $aValues = array() ) {
		$this->sql = $sql;
		$this->signal("sql_exec", $this);
		return $this->db->GetAll( $sql,$aValues );
	}
	/**
	 * Executes SQL and fetches a single row.
	 * This function allows you to provide an array with values to bind
	 * to query parameters. For instance you can bind values to question
	 * marks in the query. Each value in the array corresponds to the
	 * question mark in the query that matches the position of the value in the
	 * array. You can also bind values using explicit keys, for instance
	 * array(":key"=>123) will bind the integer 123 to the key :key in the
	 * SQL.
	 *
	 * @param  string $sql		SQL code to execute
	 * @param  array  $values	assoc. array binding values
	 *
	 * @return array	$result	one dimensional array result set
	 */
	public function getRow( $sql, $aValues = array() ) {
		$this->sql = $sql;
		$this->signal("sql_exec", $this);
		return $this->db->GetRow( $sql,$aValues );
	}
	/**
	 * Executes SQL and returns a one dimensional array result set.
	 * This function rotates the result matrix to obtain a column result set.
	 * This function allows you to provide an array with values to bind
	 * to query parameters. For instance you can bind values to question
	 * marks in the query. Each value in the array corresponds to the
	 * question mark in the query that matches the position of the value in the
	 * array. You can also bind values using explicit keys, for instance
	 * array(":key"=>123) will bind the integer 123 to the key :key in the
	 * SQL.
	 *
	 * @param  string $sql		SQL code to execute
	 * @param  array  $values	assoc. array binding values
	 *
	 * @return array  $result	one dimensional array result set
	 */
	public function getCol( $sql, $aValues = array() ) {
		$this->sql = $sql;
		$this->signal("sql_exec", $this);
		return $this->db->GetCol( $sql,$aValues );
	}
	/**
	 * Executes an SQL Query and fetches the first two columns only.
	 * Then this function builds an associative array using the first
	 * column for the keys and the second result column for the
	 * values. For instance: SELECT id, name FROM... will produce
	 * an array like: id => name.
	 * This function allows you to provide an array with values to bind
	 * to query parameters. For instance you can bind values to question
	 * marks in the query. Each value in the array corresponds to the
	 * question mark in the query that matches the position of the value in the
	 * array. You can also bind values using explicit keys, for instance
	 * array(":key"=>123) will bind the integer 123 to the key :key in the
	 * SQL.
	 *
	 * @param  string $sql		SQL code to execute
	 * @param  array  $values	assoc. array binding values
	 *
	 * @return array  $result	multi dimensional assoc. array result set
	 */
	public function getAssoc( $sql, $aValues = array() ) {
		$this->sql = $sql;
		$this->signal("sql_exec", $this);
		$rows = $this->db->GetAll( $sql, $aValues );
		$assoc = array();
		if ($rows) {
			foreach($rows as $row) {
				if (count($row)>0) {
					$key = array_shift($row);
				}
				if (count($row)>0) {
					$value = array_shift($row);
				}
				else {
					$value = $key;
				}
				$assoc[ $key ] = $value;
			}
		}
		return $assoc;
	}
	/**
	 * Retrieves a single cell.
	 * This function allows you to provide an array with values to bind
	 * to query parameters. For instance you can bind values to question
	 * marks in the query. Each value in the array corresponds to the
	 * question mark in the query that matches the position of the value in the
	 * array. You can also bind values using explicit keys, for instance
	 * array(":key"=>123) will bind the integer 123 to the key :key in the
	 * SQL.
	 *
	 * @param  string $sql	  sql code to execute
	 * @param  array  $values assoc. array binding values
	 *
	 * @return array  $result scalar result set
	 */
	public function getCell( $sql, $aValues = array(), $noSignal = null ) {
		$this->sql = $sql;
		if (!$noSignal) $this->signal("sql_exec", $this);
		$arr = $this->db->getCol( $sql, $aValues );
		if ($arr && is_array($arr))	return ($arr[0]); else return false;
	}
	/**
	 * Returns latest insert id, most recently inserted id.
	 *
	 * @return integer $id latest insert ID
	 */
	public function getInsertID() {
		return $this->db->getInsertID();
	}
	/**
	 * Returns number of affected rows.
	 *
	 * @return integer $numOfAffectRows
	 */
	public function getAffectedRows() {
		return $this->db->Affected_Rows();
	}
	/**
	 * Unwrap the original database object.
	 *
	 * @return RedBean_Driver $database	returns the inner database object
	 */
	public function getDatabase() {
		return $this->db;
	}
	/**
	 * Return latest error message.
	 * 
	 * @return string $message most recent error message
	 */
	public function getErrorMsg() {
		return $this->db->Errormsg();
	}
	/**
	 * Transactions.
	 * Part of the transaction management infrastructure of RedBean.
	 * Starts a transaction.
	 */
	public function startTransaction() {
		return $this->db->StartTrans();
	}
	/**
	 * Transactions.
	 * Part of the transaction management infrastructure of RedBean.
	 * Commits a transaction.
	 */
	public function commit() {
		return $this->db->CommitTrans();
	}
	/**
	 * Transactions.
	 * Part of the transaction management infrastructure of RedBean.
	 * Rolls back transaction.
	 */
	public function rollback() {
		return $this->db->FailTrans();
	}
}

/**
 * QueryWriter
 * Interface for QueryWriters
 * @file			RedBean/QueryWriter.php
 * @description		Describes the API for a QueryWriter
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
interface RedBean_QueryWriter {
	/**
	 * QueryWriter Constant Identifier.
	 * Identifies a situation in which a table has not been found in
	 * the database.
	 */
	const C_SQLSTATE_NO_SUCH_TABLE = 1;
	/**
	 * QueryWriter Constant Identifier.
	 * Identifies a situation in which a perticular column has not
	 * been found in the database.
	 */
	const C_SQLSTATE_NO_SUCH_COLUMN = 2;
	/**
	 * QueryWriter Constant Identifier.
	 * Identifies a situation in which a perticular column has not
	 * been found in the database.
	 */
	const C_SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION = 3;
	/**
	 * Returns the table to store beans of a given type.
	 * @param string $type 
	 */
	public function getFormattedTableName($type);
	/**
	 * Returns the tables that are in the database.
	 * @return array $arrayOfTables
	 */
	public function getTables();
	/**
	 * Creates the table with the specified name.
	 * @param string $table
	 */
	public function createTable( $table );
	/**
	 * Returns an array containing all the columns of the specified table.
	 * @param string $table
	 */
	public function getColumns( $table );
	/**
	 * Returns the type of a certain value.
	 * @param mixed $value
	 * @return integer $type
	 */
	public function scanType( $value );
	/**
	 * Adds the column to $table using column name $column and
	 * making it of type $type.
	 * @param string $table
	 * @param string $column
	 * @param integer $type
	 *
	 */
	public function addColumn( $table, $column, $type );
	/**
	 * Codes an SQL Column Type Description to a RedBean QueryWriter type.
	 * @param integer $typedescription
	 * @return integer $type
	 */
	public function code( $typedescription );
	/**
	 * Widens the column to support type $type.
	 * @param string $table
	 * @param string $column
	 * @param integer $type
	 */
	public function widenColumn( $table, $column, $type );
	/**
	 * Updates a record
	 * @param string $table
	 * @param array $updatevalues
	 * @param integer $id
	 */
	public function updateRecord( $table, $updatevalues, $id);
	/**
	 * Inserts a record
	 * @param string $table
	 * @param array $insertcolumns
	 * @param array $insertvalues
	 */
	public function insertRecord( $table, $insertcolumns, $insertvalues );
	/**
	 * Selects a record
	 * @param string $type
	 * @param integer $ids
	 */
	public function selectRecord($type, $ids);
	/**
	 * Removes a record from a table
	 * @param string $table
	 * @param integer $id
	 */
	public function deleteRecord( $table, $id );
	/**
	 * Adds a UNIQUE constraint index to a table on columns $columns.
	 * @param string $table
	 * @param array $columnsPartOfIndex
	 */
	public function addUniqueIndex( $table,$columns );
	/**
	 * Returns the property that contains the Primary Key ID in an
	 * OODBBean instance.
	 * @param string $tableOfTheBean
	 */
	public function getIDField( $table );
	/**
	 * Selects a set of columns using criteria.
	 * @param string $select - the column to be selected
	 * @param string $table - the name of the table
	 * @param string $column - name of the column that needs to be compared
	 * @param string $value - value to compare against
	 * @param boolean $withUnion - whether you want a union with inverted column
	 */
	public function selectByCrit( $select, $table, $column, $value, $withUnion=false );
	/**
	 * Deletes by criteria.
	 * @param string $table
	 * @param array $crits
	 */
	public function deleteByCrit( $table, $crits );
	/**
	 * Returns $str surrounded by keyword protecting / esc symbols
	 * @param string $str
	 */
	public function noKW($str);
	/**
	 * Checks whether the SQL state is in the list of specified states
	 * and returns true if it does appear in this list or false if it
	 * does not.
	 * @param string $state
	 * @param array $list
	 * @return boolean $isInList
	 */
	public function sqlStateIn( $state, $list );
	/**
	 * Returns a snippet of SQL to filter records using SQL and a list of
	 * keys.
	 *
	 * @param string  $idfield ID Field to use for selecting primary key
	 * @param array   $keys		List of keys to use for filtering
	 * @param string  $sql		SQL to append, if any
	 * @param boolean $inverse Whether you want to inverse the selection
	 *
	 * @return string $snippet SQL Snippet crafted by function
	 */
	public function getSQLSnippetFilter( $idfield, $keys, $sql=null, $inverse=false );
}
/**
 * RedBean Abstract Query Writer
 * @file 		RedBean/QueryWriter/AQueryWriter.php
 * @description
 *					Represents an abstract Database to RedBean
 *					To write a driver for a different database for RedBean
 *					Contains a number of functions all implementors can
 *					inherit or override.
 *
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
abstract class RedBean_QueryWriter_AQueryWriter {
	/**
	 *
	 * @var RedBean_IBeanFormatter
	 * Holds the bean formatter to be used for applying
	 * table schema.
	 */
	public $tableFormatter;
  
  
	/**
	 * @var array
	 * Supported Column Types.
	 */
	public $typeno_sqltype = array();
	
	/**
	 *
	 * @var RedBean_Adapter_DBAdapter
	 * Holds a reference to the database adapter to be used.
	 */
	protected $adapter;
	/**
	 * @var string
	 * Indicates the field name to be used for primary keys;
	 * default is 'id'.
	 */
  protected $idfield = "id";
	/**
	 * @var string
	 * default value to for blank field (passed to PK for auto-increment)
	 */
  protected $defaultValue = 'NULL';
  
	/**
	 * @var string
	 * character to escape keyword table/column names
	 */
  protected $quoteCharacter = '';
	
	/**
	 * Do everything that needs to be done to format a table name.
	 *
	 * @param string $name of table
	 * 
	 * @return string table name
	 */
	public function safeTable($name, $noQuotes = false) {
		$name = $this->getFormattedTableName($name);
		$name = $this->check($name);
		if (!$noQuotes) $name = $this->noKW($name);
		return $name;
	}
	
	/**
	 * Do everything that needs to be done to format a column name.
	 *
	 * @param string $name of column
	 *
	 * @return string $column name
	 */
	public function safeColumn($name, $noQuotes = false) {
		$name = $this->check($name);
		if (!$noQuotes) $name = $this->noKW($name);
		return $name;
	}
	
	/**
	 * Returns the sql that should follow an insert statement.
	 *
	 * @param string $table name
	 *
	 * @return string sql
	 */
  protected function getInsertSuffix ($table) {
    return "";
  }
	
	/**
	 * Returns the string identifying a table for a given type.
	 *
	 * @param string $type
	 *
	 * @return string $table
	 */
	public function getFormattedTableName($type) {
		if ($this->tableFormatter) return $this->tableFormatter->formatBeanTable($type);
		return $type;
	}
	/**
	 * Sets the Bean Formatter to be used to handle
	 * custom/advanced DB<->Bean
	 * Mappings. This method has no return value.
	 *
	 * @param RedBean_IBeanFormatter $beanFormatter the bean formatter
	 * 
	 * @return void
	 */
	public function setBeanFormatter( RedBean_IBeanFormatter $beanFormatter ) {
		$this->tableFormatter = $beanFormatter;
	}
	
	/**
	 * Get sql column type.
	 *
	 * @param integer $type constant
	 *
	 * @return string sql type
	 */
	public function getFieldType( $type = "" ) {
		return array_key_exists($type, $this->typeno_sqltype) ? $this->typeno_sqltype[$type] : "";
	}
	/**
	 * Returns the column name that should be used
	 * to store and retrieve the primary key ID.
	 *
	 * @param string $type type of bean to get ID Field for
	 *
	 * @return string $idfieldtobeused ID field to be used for this type of bean
	 */
	public function getIDField( $type ) {
		$nArgs = func_num_args();
		if ($nArgs>1) $safe = func_get_arg(1); else $safe = false;
		if ($this->tableFormatter) return $this->tableFormatter->formatBeanID($type);
		return $safe ? $this->safeColumn($this->idfield) : $this->idfield;
	}
	
	/**
	 * Checks table name or column name.
	 *
	 * @param string $table table string
	 *
	 * @return string $table escaped string
	 */
	public function check($table) {
		
		if ($this->quoteCharacter && strpos($table, $this->quoteCharacter)!==false) {
		  throw new Redbean_Exception_Security("Illegal chars in table name");
    }
		return $this->adapter->escape($table);
	}
	
	/**
	 * Puts keyword escaping symbols around string.
	 *
	 * @param string $str keyword
	 *
	 * @return string $keywordSafeString escaped keyword
	 */
	public function noKW($str) {
		$q = $this->quoteCharacter;
		return $q.$str.$q;
	}
	
	/**
	 * Adds a column of a given type to a table.
	 *
	 * @param string  $table  name of the table
	 * @param string  $column name of the column
	 * @param integer $type   type
	 *
	 */
	public function addColumn( $table, $column, $type ) {
		$table = $this->safeTable($table);
		$column = $this->safeColumn($column);
		$type = $this->getFieldType($type);
		$sql = "ALTER TABLE $table ADD $column $type ";
		$this->adapter->exec( $sql );
	}
	
	/**
	 * Update a record using a series of update values.
	 *
	 * @param string  $table		  table
	 * @param array   $updatevalues update values
	 * @param integer $id			  primary key for record
	 */
	public function updateRecord( $table, $updatevalues, $id) {
		$idfield = $this->getIDField($table, true);
		$table = $this->safeTable($table);
		$sql = "UPDATE $table SET ";
		$p = $v = array();
		foreach($updatevalues as $uv) {
			$p[] = " {$this->safeColumn($uv["property"])} = ? ";
			
			$v[]=$uv["value"];
		}
		$sql .= implode(",", $p ) ." WHERE $idfield = ".intval($id);
		$this->adapter->exec( $sql, $v );
	}
	/**
	 * Inserts a record into the database using a series of insert columns
	 * and corresponding insertvalues. Returns the insert id.
	 *
	 * @param string $table			  table to perform query on
	 * @param array  $insertcolumns columns to be inserted
	 * @param array  $insertvalues  values to be inserted
	 *
	 * @return integer $insertid	  insert id from driver, new record id
	 */
	public function insertRecord( $table, $insertcolumns, $insertvalues ) {
		$default = $this->defaultValue;
		$idfield = $this->getIDField($table, true);
		$suffix = $this->getInsertSuffix($table);
		$table = $this->safeTable($table);
		if (count($insertvalues)>0 && is_array($insertvalues[0]) && count($insertvalues[0])>0) {
			foreach($insertcolumns as $k=>$v) {
				$insertcolumns[$k] = $this->safeColumn($v);
			}
			$insertSQL = "INSERT INTO $table ( $idfield, ".implode(",",$insertcolumns)." ) VALUES ";
			$insertSQL .= "( $default, ". implode(",",array_fill(0,count($insertcolumns)," ? "))." ) $suffix";
			$first=true;
			
			foreach($insertvalues as $i=>$insertvalue) {
				$ids[] = $this->adapter->getCell( $insertSQL, $insertvalue, $i );
			}
			$result = count($ids)===1 ? array_pop($ids) : $ids;
		}
		else {
			$result = $this->adapter->getCell( "INSERT INTO $table ($idfield) VALUES($default) $suffix");
		}
		if ($suffix) return $result;
	   $last_id = $this->adapter->getInsertID();
		return ($this->adapter->getErrorMsg()=="" ?  $last_id : 0);
	}
	
	/**
	 * Selects a record based on type and id.
	 *
	 * @param string  $type type
	 * @param integer $id   id
	 *
	 * @return array $row	resulting row or NULL if none has been found
	 */
	public function selectRecord($type, $ids) {
		$idfield = $this->getIDField($type, true);
		$table = $this->safeTable($type);
		$sql = "SELECT * FROM $table WHERE $idfield IN ( ".implode(',', array_fill(0, count($ids), " ? "))." )";
		$rows = $this->adapter->get($sql,$ids);
		return ($rows && is_array($rows) && count($rows)>0) ? $rows : NULL;
	}
	/**
	 * Deletes a record based on a table, column, value and operator
	 *
	 * @param string  $table  table
	 * @param integer $value  primary key id
	 *
	 * @todo validate arguments for security
	 */
	public function deleteRecord($table, $value) {
		$column = $this->getIDField($table, true);
		$table = $this->safeTable($table);
		
		$this->adapter->exec("DELETE FROM $table WHERE $column = ? ",array(strval($value)));
	}
	
	/**
	 * Selects a record using a criterium.
	 * Specify the select-column, the target table, the criterium column
	 * and the criterium value. This method scans the specified table for
	 * records having a criterium column with a value that matches the
	 * specified value. For each record the select-column value will be
	 * returned, most likely this will be a primary key column like ID.
	 * If $withUnion equals true the method will also return the $column
	 * values for each entry that has a matching select-column. This is
	 * handy for cross-link tables like page_page.
	 *
	 * @param string  $select the column to be selected
	 * @param string  $table  the table to select from
	 * @param string  $column the column to compare the criteria value against
	 * @param string  $value  the criterium value to match against
	 * @param boolean $union with union (default is false)
	 * @param string  $sql    optional template (in this case rows are returned instead of keys) 
	 *
	 * @return array $array selected column with values
	 */
	public function selectByCrit( $select, $table, $column, $value, $withUnion = false, $sqlTemplate = false ) {
		$table = $this->safeTable($table);
		$select = $this->safeColumn($select);
		$column = $this->safeColumn($column);
		$value = $this->adapter->escape($value);
		$sql = "SELECT $select FROM $table WHERE $column = ? ";
		$values = array($value);
		if ($withUnion) {
			$sql .= " UNION SELECT $column FROM $table WHERE $select = ? ";
			$values[] = $value;
		}
		if ($sqlTemplate) {
			$sql = str_replace(":sql",$sql,$sqlTemplate);
			return $this->adapter->get($sql,$values);
		}
		return $this->adapter->getCol($sql,$values);
	}
	
	/**
	 * This method takes an array with key=>value pairs.
	 * Each record that has a complete match with the array is
	 * deleted from the table.
	 *
	 * @param string $table table
	 * @param array  $crits criteria
	 *
	 * @return integer $affectedRows num. of affected rows.
	 */
	public function deleteByCrit( $table, $crits ) {
		$table = $this->safeTable($table);
		$values = array();
		foreach($crits as $key=>$val) {
			$values[] = $this->adapter->escape($val);
			$conditions[] = $this->safeColumn($key) ."= ? ";
		}
		$sql = "DELETE FROM $table WHERE ".implode(" AND ", $conditions);
		return (int) $this->adapter->exec($sql, $values);
	}
	/**
	 * Returns a snippet of SQL to filter records using SQL and a list of
	 * keys.
	 *
	 * @param string  $idfield ID Field to use for selecting primary key
	 * @param array   $keys		List of keys to use for filtering
	 * @param string  $sql		SQL to append, if any
	 * @param boolean $inverse Whether you want to inverse the selection
	 *
	 * @return string $snippet SQL Snippet crafted by function
	 */
	public function getSQLSnippetFilter( $idfield, $keys, $sql=null, $inverse=false ) {
		if (!$sql) $sql=" 1 ";
		if (!$inverse && count($keys)===0) return " 0 ";
		$idfield = $this->noKW($idfield);
		$sqlInverse = ($inverse) ? "NOT" : "";
		$sqlKeyFilter = ($keys) ? " $idfield $sqlInverse IN (".implode(",",$keys).") AND " : " ";
		$sqlSnippet = $sqlKeyFilter . $sql;
		return $sqlSnippet;
	}
	
	/**
	 * Truncates a table
	 *
	 * @param string $table
	 */
	public function wipe($table) {
		$table = $this->safeTable($table);
		$sql = "TRUNCATE $table ";
		$this->adapter->exec($sql);
	}
	/**
	 * Counts rows in a table.
	 *
	 * @param string $beanType
	 *
	 * @return integer $numRowsFound
	 */
	public function count($beanType) {
		$table = $this->safeTable($beanType);
		$sql = "SELECT count(*) FROM $table ";
		return (int) $this->adapter->getCell($sql);
	}
	/**
	 * Optimized version for related + SQL.
	 * Facade uses this method if fearlesscode flag is 'on'.
	 *
	 * @param  string $table 	  reference table
	 * @param  string $idfield    ID field to be used
	 * @param  string $sqlSnippet SQL snippet to include in the query
	 *
	 * @return string $sqlTemplate the resulting SQL code.
	 */
	public function __fastSelectCritRelated($table, $idfield, $sqlSnippet = "1") {
		
		$idfield = $this->safeColumn($idfield);
		$sqlTemplate = " SELECT * FROM $table WHERE $idfield IN ( :sql ) AND $sqlSnippet ";
		return $sqlTemplate;
	}
}

/**
 * RedBean MySQLWriter
 * 
 * @file				RedBean/QueryWriter/MySQL.php
 * @description	Represents a MySQL Database to RedBean
 *						To write a driver for a different database for RedBean
 *						you should only have to change this file.
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_QueryWriter_MySQL extends RedBean_QueryWriter_AQueryWriter implements RedBean_QueryWriter {
	/**
	 * Here we describe the datatypes that RedBean
	 * Uses internally. If you write a QueryWriter for
	 * RedBean you should provide a list of types like this.
	 */
	/**
	 * @var integer
	 *
	 * DATA TYPE
	 * Boolean Data type
	 */
	const C_DATATYPE_BOOL = 0;
	/**
	 *
	 * @var integer
	 *
	 * DATA TYPE
	 * Unsigned 8BIT Integer
	 * 
	 */
	const C_DATATYPE_UINT8 = 1;
	/**
	 *
	 * @var integer
	 *
	 * DATA TYPE
	 * Unsigned 32BIT Integer
	 * 
	 */
	const C_DATATYPE_UINT32 = 2;
	/**
	 * @var integer
	 *
	 * DATA TYPE
	 * Double precision floating point number and
	 * negative numbers.
	 * 
	 */
	const C_DATATYPE_DOUBLE = 3;
	/**
	 * @var integer
	 * 
	 * DATA TYPE
	 * Standard Text column (like varchar255)
	 * At least 8BIT character support.
	 * 
	 */
	const C_DATATYPE_TEXT8 = 4;
	/**
	 * @var integer
	 * 
	 * DATA TYPE
	 * Long text column (16BIT)
	 * 
	 */
	const C_DATATYPE_TEXT16 = 5;
	/**
	 * @var integer
	 *
	 * DATA TYPE
	 * 32BIT long textfield (number of characters can be as high as 32BIT) Data type
	 * This is the biggest column that RedBean supports. If possible you may write
	 * an implementation that stores even bigger values.
	 * 
	 */
	const C_DATATYPE_TEXT32 = 6;
	/**
	 * @var integer
	 * 
	 * DATA TYPE
	 * Specified. This means the developer or DBA
	 * has altered the column to a different type not
	 * recognized by RedBean. This high number makes sure
	 * it will not be converted back to another type by accident.
	 * 
	 */
	const C_DATATYPE_SPECIFIED = 99;
	/**
	 * @var array
	 * Supported Column Types
	 */
	public $typeno_sqltype = array(
			  RedBean_QueryWriter_MySQL::C_DATATYPE_BOOL=>"  SET('1')  ",
			  RedBean_QueryWriter_MySQL::C_DATATYPE_UINT8=>" TINYINT(3) UNSIGNED ",
			  RedBean_QueryWriter_MySQL::C_DATATYPE_UINT32=>" INT(11) UNSIGNED ",
			  RedBean_QueryWriter_MySQL::C_DATATYPE_DOUBLE=>" DOUBLE ",
			  RedBean_QueryWriter_MySQL::C_DATATYPE_TEXT8=>" VARCHAR(255) ",
			  RedBean_QueryWriter_MySQL::C_DATATYPE_TEXT16=>" TEXT ",
			  RedBean_QueryWriter_MySQL::C_DATATYPE_TEXT32=>" LONGTEXT "
	);
	/**
	 *
	 * @var array
	 * Supported Column Types and their
	 * constants (magic numbers)
	 */
	public $sqltype_typeno = array(
			  "set('1')"=>RedBean_QueryWriter_MySQL::C_DATATYPE_BOOL,
			  "tinyint(3) unsigned"=>RedBean_QueryWriter_MySQL::C_DATATYPE_UINT8,
			  "int(11) unsigned"=>RedBean_QueryWriter_MySQL::C_DATATYPE_UINT32,
			  "double" => RedBean_QueryWriter_MySQL::C_DATATYPE_DOUBLE,
			  "varchar(255)"=>RedBean_QueryWriter_MySQL::C_DATATYPE_TEXT8,
			  "text"=>RedBean_QueryWriter_MySQL::C_DATATYPE_TEXT16,
			  "longtext"=>RedBean_QueryWriter_MySQL::C_DATATYPE_TEXT32
	);
	/**
	 *
	 * @var RedBean_Adapter_DBAdapter
	 */
	protected $adapter;
	
	/**
	 * @var string
	 * character to escape keyword table/column names
	 */
  protected $quoteCharacter = '`';
	/**
	 * Constructor.
	 * The Query Writer Constructor also sets up the database.
	 *
	 * @param RedBean_Adapter_DBAdapter $adapter adapter
	 * @param boolean							$frozen  allow schema modif.?
	 *
	 *
	 */
	public function __construct( RedBean_Adapter $adapter, $frozen = false ) {
		$this->adapter = $adapter;
	}
	/**
	 * Returns all tables in the database.
	 * 
	 * @return array $tables tables
	 */
	public function getTables() {
		return $this->adapter->getCol( "show tables" );
	}
	/**
	 * Creates an empty, column-less table for a bean.
	 * 
	 * @param string $table table
	 */
	public function createTable( $table ) {
		$idfield = $this->getIDfield($table, true);
		$table = $this->safeTable($table);
		$sql = "
                     CREATE TABLE $table (
                    $idfield INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
                     PRIMARY KEY ( $idfield )
                     ) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
				  ";
		$this->adapter->exec( $sql );
	}
	/**
	 * Returns an array containing the column names of the specified table.
	 *
	 * @param string $table table
	 *
	 * @return array $columns columns
	 */
	public function getColumns( $table ) {
		$table = $this->safeTable($table);
		$columnsRaw = $this->adapter->get("DESCRIBE $table");
		foreach($columnsRaw as $r) {
			$columns[$r["Field"]]=$r["Type"];
		}
		return $columns;
	}
	/**
	 * Returns the MySQL Column Type Code (integer) that corresponds
	 * to the given value type.
	 *
	 * @param string $value value
	 * 
	 * @return integer $type type
	 */
	public function scanType( $value ) {
		if (is_null($value)) {
			return RedBean_QueryWriter_MySQL::C_DATATYPE_BOOL;
		}
		$orig = $value;
		$value = strval($value);
		if ($value=="1" || $value=="") {
			return RedBean_QueryWriter_MySQL::C_DATATYPE_BOOL;
		}
		if (is_numeric($value) && (floor($value)==$value) && $value >= 0 && $value <= 255 ) {
			return RedBean_QueryWriter_MySQL::C_DATATYPE_UINT8;
		}
		if (is_numeric($value) && (floor($value)==$value) && $value >= 0  && $value <= 4294967295 ) {
			return RedBean_QueryWriter_MySQL::C_DATATYPE_UINT32;
		}
		if (is_numeric($value)) {
			return RedBean_QueryWriter_MySQL::C_DATATYPE_DOUBLE;
		}
		if (strlen($value) <= 255) {
			return RedBean_QueryWriter_MySQL::C_DATATYPE_TEXT8;
		}
		return RedBean_QueryWriter_MySQL::C_DATATYPE_TEXT16;
	}
	
	/**
	 * Returns the Type Code for a Column Description.
	 *
	 * @param string $typedescription description
	 *
	 * @return integer $typecode code
	 */
	public function code( $typedescription ) {
		return ((isset($this->sqltype_typeno[$typedescription])) ? $this->sqltype_typeno[$typedescription] : self::C_DATATYPE_SPECIFIED);
	}
	/**
	 * Change (Widen) the column to the give type.
	 *
	 * @param string $table table
	 * @param string $column column
	 * 
	 * @param integer $type
	 */
	public function widenColumn( $table, $column, $type ) {
		$table = $this->safeTable($table);
		$column = $this->safeColumn($column);
		$newtype = $this->getFieldType($type);
		$changecolumnSQL = "ALTER TABLE $table CHANGE $column $column $newtype ";
		$this->adapter->exec( $changecolumnSQL );
	}
	/**
	 * Adds a Unique index constrain to the table.
	 *
	 * @param string $table table
	 * @param string $col1  column
	 * @param string $col2  column
	 *
	 * @return void
	 */
	public function addUniqueIndex( $table,$columns ) {
		$table = $this->safeTable($table);
		sort($columns); 
		foreach($columns as $k=>$v) {
			$columns[$k]= $this->safeColumn($v);
		}
		$r = $this->adapter->get("SHOW INDEX FROM $table");
		$name = "UQ_".sha1(implode(',',$columns));
		if ($r) {
			foreach($r as $i) {
				if ($i["Key_name"]== $name) {
					return;
				}
			}
		}
		$sql = "ALTER IGNORE TABLE $table
                ADD UNIQUE INDEX $name (".implode(",",$columns).")";
		$this->adapter->exec($sql);
	}
	/**
	 * Tests whether a given SQL state is in the list of states.
	 *
	 * @param string $state code
	 * @param array  $list  array of sql states
	 *
	 * @return boolean $yesno occurs in list
	 */
	public function sqlStateIn($state, $list) {
		$sqlState = "0";
		if ($state == "42S02") $sqlState = RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE;
		if ($state == "42S22") $sqlState = RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN;
		if ($state == "23000") $sqlState = RedBean_QueryWriter::C_SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION;
		return in_array($sqlState, $list);
	}
}

/**
 * RedBean SQLiteWriter
 * 
 * @file				RedBean/QueryWriter/SQLite.php
 * @description	Represents a SQLite Database to RedBean
 *						To write a driver for a different database for RedBean
 *						you should only have to change this file.
 * @author			Gabor de Mooij
 * @license			BSD
 */
class RedBean_QueryWriter_SQLite extends RedBean_QueryWriter_AQueryWriter implements RedBean_QueryWriter {
	/**
	 *
	 * @var RedBean_Adapter_DBAdapter
	 * Holds database adapter
	 */
	protected $adapter;
	
	/**
	 * @var string
	 * character to escape keyword table/column names
	 */
  protected $quoteCharacter = '`';
	/**
	 * Constructor
	 * The Query Writer Constructor also sets up the database
	 *
	 * @param RedBean_Adapter_DBAdapter $adapter adapter
	 */
	public function __construct( RedBean_Adapter $adapter, $frozen = false ) {
		$this->adapter = $adapter;
	}
	/**
	 * Returns all tables in the database
	 *
	 * @return array $tables tables
	 */
	public function getTables() {
		return $this->adapter->getCol( "SELECT name FROM sqlite_master
			WHERE type='table' AND name!='sqlite_sequence';" );
	}
	/**
	 * Creates an empty, column-less table for a bean.
	 * 
	 * @param string $table table
	 */
	public function createTable( $table ) {
		$idfield = $this->getIDfield($table, true);
		$table = $this->safeTable($table);
		$sql = "
                     CREATE TABLE $table ( $idfield INTEGER PRIMARY KEY AUTOINCREMENT )
				  ";
		$this->adapter->exec( $sql );
	}
	/**
	 * Returns an array containing the column names of the specified table.
	 *
	 * @param string $table table
	 *
	 * @return array $columns columns
	 */
	public function getColumns( $table ) {
		$table = $this->safeTable($table, true);
		$columnsRaw = $this->adapter->get("PRAGMA table_info('$table')");
		$columns = array();
		foreach($columnsRaw as $r) {
			$columns[$r["name"]]=$r["type"];
		}
		return $columns;
	}
	/**
	 * Returns the MySQL Column Type Code (integer) that corresponds
	 * to the given value type.
	 *
	 * @param string $value value
	 *
	 * @return integer $type type
	 */
	public function scanType( $value ) {
		return 1;
	}
	/**
	 * Returns the Type Code for a Column Description
	 *
	 * @param string $typedescription type description
	 *
	 * @return integer $typecode type code
	 */
	public function code( $typedescription ) {
		return 1;
	}
	/**
	 * Change (Widen) the column to the give type.
	 *
	 * @param string $table  table
	 * @param string $column column
	 *
	 * @param integer $type type
	 */
	public function widenColumn( $table, $column, $type ) {
		return true;
	}
	/**
	 * Adds a Unique index constrain to the table.
	 *
	 * @param string $table   table
	 * @param string $column1 first column
	 * @param string $column2 second column
	 *
	 * @return void
	 */
	public function addUniqueIndex( $table,$columns ) {
		$table = $this->safeTable($table);
		$name = "UQ_".sha1(implode(',',$columns));
		$sql = "CREATE UNIQUE INDEX IF NOT EXISTS $name ON $table (".implode(",",$columns).")";
		$this->adapter->exec($sql);
	}
	/**
	 * Given an Database Specific SQLState and a list of QueryWriter
	 * Standard SQL States this function converts the raw SQL state to a
	 * database agnostic ANSI-92 SQL states and checks if the given state
	 * is in the list of agnostic states.
	 *
	 * @param string $state state
	 * @param array  $list  list of states
	 *
	 * @return boolean $isInArray whether state is in list
	 */
	public function sqlStateIn($state, $list) {
		$sqlState = "0";
		if ($state == "HY000") $sqlState = RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE;
		if ($state == "23000") $sqlState = RedBean_QueryWriter::C_SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION;
		return in_array($sqlState, $list);
	}
	/**
	 * Counts rows in a table.
	 * Uses SQLite optimization for deleting all records (i.e. no WHERE)
	 *
	 * @param string $beanType
	 *
	 * @return integer $numRowsFound
	 */
	public function wipe($type) {
		$table = $this->safeTable($type);
		$this->adapter->exec("DELETE FROM $table");
	}
}

class RedBean_QueryWriter_SQLiteT extends RedBean_QueryWriter_SQLite {
	/**
	 * Here we describe the datatypes that RedBean
	 * Uses internally. If you write a QueryWriter for
	 * RedBean you should provide a list of types like this.
	 */
	/**
	 * DATA TYPE
	 * Integer Data type
	 * @var integer
	 */
	const C_DATATYPE_INTEGER = 0;
	/**
	 * DATA TYPE
	 * Numeric Data type (for REAL and date/time)
	 * @var integer
	 */
	const C_DATATYPE_NUMERIC = 1;
	/**
	 * DATA TYPE
	 * Text type
	 * @var integer
	 */
	const C_DATATYPE_TEXT = 2;
	/**
	 * DATA TYPE
	 * Specified. This means the developer or DBA
	 * has altered the column to a different type not
	 * recognized by RedBean. This high number makes sure
	 * it will not be converted back to another type by accident.
	 * @var integer
	 */
	const C_DATATYPE_SPECIFIED = 99;
	/**
	 * @var array
	 * Supported Column Types
	 */
	public $typeno_sqltype = array(
			  RedBean_QueryWriter_SQLiteT::C_DATATYPE_INTEGER=>"INTEGER",
			  RedBean_QueryWriter_SQLiteT::C_DATATYPE_NUMERIC=>"NUMERIC",
			  RedBean_QueryWriter_SQLiteT::C_DATATYPE_TEXT=>"TEXT",
	);
	/**
	 *
	 * @var array
	 * Supported Column Types and their
	 * constants (magic numbers)
	 */
	public $sqltype_typeno = array(
			  "INTEGER"=>RedBean_QueryWriter_SQLiteT::C_DATATYPE_INTEGER,
			  "NUMERIC"=>RedBean_QueryWriter_SQLiteT::C_DATATYPE_NUMERIC,
			  "TEXT"=>RedBean_QueryWriter_SQLiteT::C_DATATYPE_TEXT,
	);
	/**
	 * Returns the MySQL Column Type Code (integer) that corresponds
	 * to the given value type.
	 *
	 * @param  string $value value
	 * 
	 * @return integer $type type
	 */
	public function scanType( $value ) {
		if (is_numeric($value) && (intval($value)==$value) && $value<2147483648) return self::C_DATATYPE_INTEGER;
		if ((is_numeric($value) && $value < 2147483648)
				  || preg_match("/\d\d\d\d\-\d\d\-\d\d/",$value)
				  || preg_match("/\d\d\d\d\-\d\d\-\d\d\s\d\d:\d\d:\d\d/",$value)
		) {
			return self::C_DATATYPE_NUMERIC;
		}
		
		
		return self::C_DATATYPE_TEXT;
	}
	/**
	 * Adds a column of a given type to a table
	 *
	 * @param string  $table  table
	 * @param string  $column column
	 * @param integer $type	  type
	 */
	public function addColumn( $table, $column, $type) {
		$table = $this->getFormattedTableName($table);
		$column = $this->check($column);
		$table = $this->check($table);
		$type=$this->typeno_sqltype[$type];
		$sql = "ALTER TABLE `$table` ADD `$column` $type ";
		$this->adapter->exec( $sql );
	}
	/**
	 * Returns the Type Code for a Column Description
	 *
	 * @param string $typedescription description
	 *
	 * @return integer $typecode code
	 */
	public function code( $typedescription ) {
		return ((isset($this->sqltype_typeno[$typedescription])) ? $this->sqltype_typeno[$typedescription] : 99);
	}
	/**
	 * Quote Items, to prevent issues with reserved words.
	 *
	 * @param array $items items to quote
	 *
	 * @return $quotedfItems quoted items
	 */
	private function quote( $items ) {
		foreach($items as $k=>$item) {
			$items[$k]=$this->noKW($item);
		}
		return $items;
	}
	/**
	 * Change (Widen) the column to the give type.
	 *
	 * @param string  $table  table to widen
	 * @param string  $column column to widen
	 * @param integer $type   new column type
	 */
	public function widenColumn( $table, $column, $type ) {
		$table = $this->getFormattedTableName($table);
		$idfield = $this->idfield;
		$column = $this->check($column);
		$table = $this->check($table);
		$newtype = $this->typeno_sqltype[$type];
		$oldColumns = $this->getColumns($table);
		$oldColumnNames = $this->quote(array_keys($oldColumns));
		$newTableDefStr="";
		foreach($oldColumns as $oldName=>$oldType) {
			if ($oldName != $idfield) {
				if ($oldName!=$column) {
					$newTableDefStr .= ",`$oldName` $oldType";
				}
				else {
					$newTableDefStr .= ",`$oldName` $newtype";
				}
			}
		}
	
		$q = array();
		$q[] = "DROP TABLE IF EXISTS tmp_backup;";
		$q[] = "CREATE TEMPORARY TABLE tmp_backup(".implode(",",$oldColumnNames).");";
		$q[] = "INSERT INTO tmp_backup SELECT * FROM `$table`;";
		$q[] = "DROP TABLE `$table`;";
		$q[] = "CREATE TABLE `$table` ( `$idfield` INTEGER PRIMARY KEY AUTOINCREMENT  $newTableDefStr  );";
		$q[] = "INSERT INTO `$table` SELECT * FROM tmp_backup;";
		$q[] = "DROP TABLE tmp_backup;";
		foreach($q as $sq) {
			$this->adapter->exec($sq);
		}
	}
}

/**
 * RedBean PostgreSQL Query Writer
 * @file				RedBean/QueryWriter/PostgreSQL.php
 * @description	QueryWriter for the PostgreSQL database system.
 *
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_QueryWriter_PostgreSQL extends RedBean_QueryWriter_AQueryWriter implements RedBean_QueryWriter {
	/**
	 * DATA TYPE
	 * Integer Data Type
	 * @var integer
	 */
	const C_DATATYPE_INTEGER = 0;
	/**
	 * DATA TYPE
	 * Double Precision Type
	 * @var integer
	 */
	const C_DATATYPE_DOUBLE = 1;
	/**
	 * DATA TYPE
	 * String Data Type
	 * @var integer
	 */
	const C_DATATYPE_TEXT = 3;
	/**
	 * @var array
	 * Supported Column Types
	 */
	public $typeno_sqltype = array(
			  self::C_DATATYPE_INTEGER=>" integer ",
			  self::C_DATATYPE_DOUBLE=>" double precision ",
			  self::C_DATATYPE_TEXT=>" text "
	);
	/**
	 *
	 * @var array
	 * Supported Column Types and their
	 * constants (magic numbers)
	 */
	public $sqltype_typeno = array(
			  "integer"=>self::C_DATATYPE_INTEGER,
			  "double precision" => self::C_DATATYPE_DOUBLE,
			  "text"=>self::C_DATATYPE_TEXT
	);
	/**
	 *
	 * @var RedBean_DBAdapter
	 * Holds Database Adapter
	 */
	protected $adapter;
	
	/**
	 * @var string
	 * character to escape keyword table/column names
	 */
  protected $quoteCharacter = '"';
  /**
   *
   * @var string
   * Default Value
   */
  protected $defaultValue = 'DEFAULT';
  /**
   * Returns the insert suffix SQL Snippet
   * 
   * @param string $table table
   *
   * @return  string $sql SQL Snippet
   */
  protected function getInsertSuffix($table) {
    return "RETURNING ".$this->getIDField($table);
  }  
	/**
	 * Constructor
	 * The Query Writer Constructor also sets up the database
	 *
	 * @param RedBean_DBAdapter $adapter adapter
	 */
	public function __construct( RedBean_Adapter_DBAdapter $adapter ) {
		$this->adapter = $adapter;
	}
	/**
	 * Returns all tables in the database
	 *
	 * @return array $tables tables
	 */
	public function getTables() {
		return $this->adapter->getCol( "select table_name from information_schema.tables
where table_schema = 'public'" );
	}
	/**
	 * Creates an empty, column-less table for a bean.
	 *
	 * @param string $table table to create
	 */
	public function createTable( $table ) {
		$idfield = $this->getIDfield($table);
		$table = $this->safeTable($table);
		$sql = " CREATE TABLE $table ($idfield SERIAL PRIMARY KEY); ";
		$this->adapter->exec( $sql );
	}
	/**
	 * Returns an array containing the column names of the specified table.
	 *
	 * @param string $table table to get columns from
	 *
	 * @return array $columns array filled with column (name=>type)
	 */
	public function getColumns( $table ) {
		$table = $this->safeTable($table, true);
		$columnsRaw = $this->adapter->get("select column_name, data_type from information_schema.columns where table_name='$table'");
		foreach($columnsRaw as $r) {
			$columns[$r["column_name"]]=$r["data_type"];
		}
		return $columns;
	}
	/**
	 * Returns the pgSQL Column Type Code (integer) that corresponds
	 * to the given value type.
	 *
	 * @param string $value value to determine type of
	 *
	 * @return integer $type type code for this value
	 */
	public function scanType( $value ) {
		
		if (is_numeric($value)
				  && floor($value)==$value
				  && $value < 2147483648
				  && $value > -2147483648) {
			return self::C_DATATYPE_INTEGER;
		}
		elseif(is_numeric($value)) {
			return self::C_DATATYPE_DOUBLE;
		}
		else {
			return self::C_DATATYPE_TEXT;
		}
	}
	/**
	 * Returns the Type Code for a Column Description
	 *
	 * @param string $typedescription type description to get code for
	 *
	 * @return integer $typecode type code
	 */
	public function code( $typedescription ) {
		return ((isset($this->sqltype_typeno[$typedescription])) ? $this->sqltype_typeno[$typedescription] : 99);
	}
	/**
	 * Change (Widen) the column to the give type.
	 *
	 * @param string  $table  table to widen
	 * @param string  $column column to widen
	 * @param integer $type   new column type
	 */
	public function widenColumn( $table, $column, $type ) {
		$table = $this->safeTable($table);
		$column = $this->safeColumn($column);
		$newtype = $this->typeno_sqltype[$type];
		$changecolumnSQL = "ALTER TABLE $table \n\t ALTER COLUMN $column TYPE $newtype ";
		try {
			$this->adapter->exec( $changecolumnSQL );
		}catch(Exception $e) {
			die($e->getMessage());
		}
	}
	/**
	 * Gets information about changed records using a type and id and a logid.
	 * RedBean Locking shields you from race conditions by comparing the latest
	 * cached insert id with a the highest insert id associated with a write action
	 * on the same table. If there is any id between these two the record has
	 * been changed and RedBean will throw an exception. This function checks for changes.
	 * If changes have occurred it will throw an exception. If no changes have occurred
	 * it will insert a new change record and return the new change id.
	 * This method locks the log table exclusively.
	 *
	 * @param  string  $type  type
	 * @param  integer $id    id
	 * @param  integer $logid log id
	 *
	 * @return integer $newchangeid new change id
	 */
	public function checkChanges($type, $id, $logid) {
		$table = $this->safeTable($type);
		$idfield = $this->getIDfield($type);
		$id = (int) $id;
		$logid = (int) $logid;
		$num = $this->adapter->getCell("
        SELECT count(*) FROM __log WHERE tbl=$table AND itemid=$id AND action=2 AND $idfield > $logid");
		if ($num) {
			throw new RedBean_Exception_FailedAccessBean("Locked, failed to access (type:$type, id:$id)");
		}
		$newid = $this->insertRecord("__log",array("action","tbl","itemid"),
				  array(array(2,  $type, $id)));
		if ($this->adapter->getCell("select id from __log where tbl=:tbl AND id < $newid and id > $logid and action=2 and itemid=$id ",
		array(":tbl"=>$type))) {
			throw new RedBean_Exception_FailedAccessBean("Locked, failed to access II (type:$type, id:$id)");
		}
		return $newid;
	}
	/**
	 * Adds a Unique index constrain to the table.
	 *
	 * @param string $table table to add index to
	 * @param string $col1  column to be part of index
	 * @param string $col2  column 2 to be part of index
	 *
	 * @return void
	 */
	public function addUniqueIndex( $table,$columns ) {
		$table = $this->safeTable($table, true);
		sort($columns); 
		foreach($columns as $k=>$v) {
			$columns[$k]=$this->safeColumn($v);
		}
		$r = $this->adapter->get("SELECT
									i.relname as index_name
								FROM
									pg_class t,
									pg_class i,
									pg_index ix,
									pg_attribute a
								WHERE
									t.oid = ix.indrelid
									AND i.oid = ix.indexrelid
									AND a.attrelid = t.oid
									AND a.attnum = ANY(ix.indkey)
									AND t.relkind = 'r'
									AND t.relname = '$table'
								ORDER BY  t.relname,  i.relname;");
		/*
		 *
		 * ALTER TABLE testje ADD CONSTRAINT blabla UNIQUE (blaa, blaa2);
		*/
		$name = "UQ_".sha1($table.implode(',',$columns));
		if ($r) {
			foreach($r as $i) {
				if (strtolower( $i["index_name"] )== strtolower( $name )) {
					return;
				}
			}
		}
		$sql = "ALTER TABLE \"$table\"
                ADD CONSTRAINT $name UNIQUE (".implode(",",$columns).")";
		$this->adapter->exec($sql);
	}
	/**
	 * Given an Database Specific SQLState and a list of QueryWriter
	 * Standard SQL States this function converts the raw SQL state to a
	 * database agnostic ANSI-92 SQL states and checks if the given state
	 * is in the list of agnostic states.
	 *
	 * @param string $state state
	 * @param array  $list  list of states
	 *
	 * @return boolean $isInArray whether state is in list
	 */
	public function sqlStateIn($state, $list) {
		$sqlState = "0";
		if ($state == "42P01") $sqlState = RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE;
		if ($state == "42703") $sqlState = RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN;
		return in_array($sqlState, $list);
	}
	/**
	 * Returns a snippet of SQL to filter records using SQL and a list of
	 * keys.
	 *
	 * @param string  $idfield ID Field to use for selecting primary key
	 * @param array   $keys		List of keys to use for filtering
	 * @param string  $sql		SQL to append, if any
	 * @param boolean $inverse Whether you want to inverse the selection
	 *
	 * @return string $snippet SQL Snippet crafted by function
	 */
	public function getSQLSnippetFilter( $idfield, $keys, $sql=null, $inverse=false ) {
		if (!$sql) $sql=" TRUE ";
		if (!$inverse && count($keys)===0) return " TRUE ";
		$idfield = $this->noKW($idfield);
		$sqlInverse = ($inverse) ? "NOT" : "";
		$sqlKeyFilter = ($keys) ? " $idfield $sqlInverse IN (".implode(",",$keys).") AND " : " ";
		$sqlSnippet = $sqlKeyFilter . $sql;
		return $sqlSnippet;
	}
	public function createIndexIfNotExist($table, $indexName, $indexColumns, $drop=true) {
		$indexName = $this->adapter->escape($indexName);
		
		
		$sql = "select
				 t.relname as table_name,
				 i.relname as index_name,
				 a.attname as column_name
			from
				 pg_class t,
				 pg_class i,
				 pg_index ix,
				 pg_attribute a
			where
				 t.oid = ix.indrelid
				 and i.oid = ix.indexrelid
				 and a.attrelid = t.oid
				 and a.attnum = ANY(ix.indkey)
				 and t.relkind = 'r'
				 and t.relname = ?
			order by
				 t.relname,
				 i.relname;";
		$indexes = $this->adapter->get($sql,array($table));
		print_r($indexes);
		
		foreach($indexes as $index) {
			if ($index["index_name"]===$indexName) {
				if (!$drop) return false;
				$sql = "DROP INDEX $indexName ";
				$this->adapter->exec($sql);
				break;
			}
		}
		
		foreach($indexColumns as $key=>$indexColumn) {
			$indexColumns[$key] = $this->safeColumn($indexColumn);
		}
		$columnStr = implode(",", $indexColumns);
		
		$indexName = $this->safeTable($indexName);
		$sql = "CREATE INDEX $indexName ON ".$this->safeTable($table)." ($columnStr) ";
		$this->adapter->exec($sql);
		return true;
	}
}

/**
 * RedBean Exception Base
 * 
 * @file				RedBean/Exception.php
 * @description	Represents the base class
 * 					for RedBean Exceptions
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_Exception extends Exception {
}
/**
 * RedBean Exception SQL
 * 
 * @file				RedBean/Exception/SQL.php
 * @description	Represents a generic database exception independent of the
 *						underlying driver.
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_Exception_SQL extends Exception {
	/**
	 * @var string
	 * Holds the current SQL Strate code.
	 */
	private $sqlState;
	/**
	 * Returns an ANSI-92 compliant SQL state.
	 * 
	 * @return string $state ANSI state code
	 */
	public function getSQLState() {
		return $this->sqlState;
	}
	/**
	 * @todo parse state to verify valid ANSI92!
	 * Stores ANSI-92 compliant SQL state.
	 * 
	 * @param string $sqlState code
	 */
	public function setSQLState( $sqlState ) {
		$this->sqlState = $sqlState;
	}
	/**
	 * To String prints both code and SQL state.
	 *
	 * @return string $message prints this exception instance as a string
	 */
	public function __toString() {
		return "[".$this->getSQLState()."] - ".$this->getMessage();
	}
} 
/**
 * Exception Security.
 * Part of the RedBean Exceptions Mechanism.
 * 
 * @file				RedBean/Exception
 * @description	Represents a subtype in the RedBean Exception System.
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_Exception_Security extends RedBean_Exception {
} 
/**
 * Exception Failed Access
 * Part of the RedBean Exceptions Mechanism
 * 
 * @file				RedBean/Exception
 * @description	Represents a subtype in the RedBean Exception System
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_Exception_FailedAccessBean extends Exception {
}
/**
 * Exception NotImplemented.
 * Part of the RedBean Exceptions Mechanism.
 * 
 * @file				RedBean/Exception/NotImplemented
 * @description	Represents a subtype in the RedBean Exception System.
 *						This Exception indicates a certain feature has not been
 *						implemented yet and should be handled on Application level.
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_Exception_NotImplemented extends RedBean_Exception {
}
/**
 * Exception Unsupported Database
 * Part of the RedBean Exceptions Mechanism
 * 
 * @file				RedBean/Exception
 * @description	Represents a subtype in the RedBean Exception System
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_Exception_UnsupportedDatabase extends RedBean_Exception {
}
/**
 * @name RedBean OODB
 * @file RedBean
 * @author Gabor de Mooij and the RedBean Team
 * @copyright Gabor de Mooij (c)
 * @license BSD
 *
 * The RedBean OODB Class is the main class of RedBean.
 * It takes RedBean_OODBBean objects and stores them to and loads them from the
 * database as well as providing other CRUD functions. This class acts as a
 * object database.
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_OODB extends RedBean_Observable implements RedBean_ObjectDatabase {
	/**
	 *
	 * @var array
	 */
	private $stash = NULL;
	/**
	 *
	 * @var RedBean_Adapter_DBAdapter
	 */
	private $writer;
	/**
	 *
	 * @var boolean
	 */
	private $isFrozen = false;
	/**
	 * The RedBean OODB Class is the main class of RedBean.
	 * It takes RedBean_OODBBean objects and stores them to and loads them from the
	 * database as well as providing other CRUD functions. This class acts as a
	 * object database.
	 * Constructor, requires a DBAadapter (dependency inversion)
	 * @param RedBean_Adapter_DBAdapter $adapter
	 */
	public function __construct( RedBean_QueryWriter $writer ) {
		$this->writer = $writer;
	}
	/**
	 * Toggles fluid or frozen mode. In fluid mode the database
	 * structure is adjusted to accomodate your objects. In frozen mode
	 * this is not the case.
	 * @param boolean $trueFalse
	 */
	public function freeze( $tf ) {
		$this->isFrozen = (bool) $tf;
	}
	/**
	 * Returns the current mode of operation of RedBean.
	 * In fluid mode the database
	 * structure is adjusted to accomodate your objects.
	 * In frozen mode
	 * this is not the case.
	 * @return <type>
	 */
	public function isFrozen() {
		return (bool) $this->isFrozen;
	}
	/**
	 * Dispenses a new bean (a RedBean_OODBBean Bean Object)
	 * of the specified type. Always
	 * use this function to get an empty bean object. Never
	 * instantiate a RedBean_OODBBean yourself because it needs
	 * to be configured before you can use it with RedBean. This
	 * function applies the appropriate initialization /
	 * configuration for you.
	 * @param string $type
	 * @return RedBean_OODBBean $bean
	 */
	public function dispense($type ) {
		$this->signal( "before_dispense", $type );
		$bean = new RedBean_OODBBean();
		$bean->setMeta("type", $type );
		$idfield = $this->writer->getIDField($bean->getMeta("type"));
		$bean->setMeta("sys.idfield",$idfield);
		$bean->$idfield = 0;
		if (!$this->isFrozen) $this->check( $bean );
		$bean->setMeta("tainted",false);
		$this->signal( "dispense", $bean );
		return $bean;
	}
	/**
	 * Checks whether a RedBean_OODBBean bean is valid.
	 * If the type is not valid or the ID is not valid it will
	 * throw an exception: RedBean_Exception_Security.
	 * @throws RedBean_Exception_Security $exception
	 * @param RedBean_OODBBean $bean
	 */
	public function check( RedBean_OODBBean $bean ) {
		$idfield = $this->writer->getIDField($bean->getMeta("type"));
		
		if (!isset($bean->$idfield) ) {
			throw new RedBean_Exception_Security("Bean has incomplete Meta Information $idfield ");
		}
		if (!($bean->getMeta("type"))) {
			throw new RedBean_Exception_Security("Bean has incomplete Meta Information II");
		}
		
		$pattern = '/[^abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_]/';
		
		if (preg_match($pattern,$bean->getMeta("type"))) {
			throw new RedBean_Exception_Security("Bean Type is invalid");
		}
		
		foreach($bean as $prop=>$value) {
			if (
			is_array($value) ||
					  is_object($value) ||
					  strlen($prop)<1 ||
					  preg_match($pattern,$prop)
			) {
				throw new RedBean_Exception_Security("Invalid Bean: property $prop  ");
			}
		}
	}
	/**
	 * Checks whether the specified table already exists in the database.
	 * Not part of the Object Database interface!
	 * @param string $table
	 * @return boolean $exists
	 */
	public function tableExists($table) {
		
		$tables = $this->writer->getTables();
		return in_array($this->writer->getFormattedTableName($table), $tables);
	}
	/**
	 * Stores a bean in the database. This function takes a
	 * RedBean_OODBBean Bean Object $bean and stores it
	 * in the database. If the database schema is not compatible
	 * with this bean and RedBean runs in fluid mode the schema
	 * will be altered to store the bean correctly.
	 * If the database schema is not compatible with this bean and
	 * RedBean runs in frozen mode it will throw an exception.
	 * This function returns the primary key ID of the inserted
	 * bean.
	 * @throws RedBean_Exception_Security $exception
	 * @param RedBean_OODBBean $bean
	 * @return integer $newid
	 */
	public function store( RedBean_OODBBean $bean ) {
		$this->signal( "update", $bean );
		if (!$this->isFrozen) $this->check($bean);
		
		$table = $bean->getMeta("type");
		$idfield = $this->writer->getIDField($table);
		
		if (!$this->isFrozen && !$this->tableExists($table)) {
			$this->writer->createTable( $table );
		}
		if (!$this->isFrozen) {
			$columns = $this->writer->getColumns($table) ;
		}
		
		$insertvalues = array();
		$insertcolumns = array();
		$updatevalues = array();
		foreach( $bean as $p=>$v ) {
			if ($p!=$idfield) {
				if (!$this->isFrozen) {
					
					if ($bean->getMeta("cast.$p",-1)!==-1) {
						$cast = $bean->getMeta("cast.$p");
						if ($cast=="string") {
							$typeno = $this->writer->scanType("STRING");
						}
						else {
							throw new RedBean_Exception("Invalid Cast");
						}
					}
					else {
						
						$typeno = $this->writer->scanType($v);
					}
					
					if (isset($columns[$p])) {
						
						$sqlt = $this->writer->code($columns[$p]);
						if ($typeno > $sqlt) {
							
							$this->writer->widenColumn( $table, $p, $typeno );
						}
					}
					else {
						
						$this->writer->addColumn($table, $p, $typeno);
					}
				}
				
				$insertvalues[] = $v;
				$insertcolumns[] = $p;
				$updatevalues[] = array( "property"=>$p, "value"=>$v );
			}
		}
		if (!$this->isFrozen && ($uniques = $bean->getMeta("buildcommand.unique"))) {
			foreach($uniques as $unique) {
				$this->writer->addUniqueIndex( $table, $unique );
			}
		}
		if ($bean->$idfield) {
			if (count($updatevalues)>0) {
				$this->writer->updateRecord( $table, $updatevalues, $bean->$idfield );
			}
			$bean->setMeta("tainted",false);
			$this->signal( "after_update", $bean );
			return (int) $bean->$idfield;
		}
		else {
			$id = $this->writer->insertRecord( $table, $insertcolumns, array($insertvalues) );
			$bean->$idfield = $id;
			$bean->setMeta("tainted",false);
			$this->signal( "after_update", $bean );
			return (int) $id;
		}
	}
	/**
	 * Loads a bean from the object database.
	 * It searches for a RedBean_OODBBean Bean Object in the
	 * database. It does not matter how this bean has been stored.
	 * RedBean uses the primary key ID $id and the string $type
	 * to find the bean. The $type specifies what kind of bean your
	 * are looking for; this is the same type as used with the
	 * dispense() function. If RedBean finds the bean it will return
	 * the RedBean_OODB Bean object; if it cannot find the bean
	 * RedBean will return a new bean of type $type and with
	 * primary key ID 0. In the latter case it acts basically the
	 * same as dispense().
	 * If the bean cannot be found in the database a new bean of
	 * the specified type will be generated and returned.
	 * @param string $type
	 * @param integer $id
	 * @return RedBean_OODBBean $bean
	 */
	public function load($type, $id) {
		$this->signal("before_open",array("type"=>$type,"id"=>$id));
		$tmpid = intval( $id );
		if ($tmpid < 0) throw new RedBean_Exception_Security("Id less than zero not allowed");
		$bean = $this->dispense( $type );
		if ($this->stash && isset($this->stash[$id])) {
			$row = $this->stash[$id];
		}
		else {
			try {
				$rows = $this->writer->selectRecord($type,array($id));
			}catch(RedBean_Exception_SQL $e ) {
				if (
				$this->writer->sqlStateIn($e->getSQLState(),
				array(
				RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN,
				RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
				)
				) {
					$rows = 0;
					if ($this->isFrozen) throw $e; 
				}
				else throw $e;
			}
			if (!$rows) return $this->dispense($type);
			$row = array_pop($rows);
		}
		foreach($row as $p=>$v) {
			
			$bean->$p = $v;
		}
		$this->signal( "open", $bean );
		$bean->setMeta("tainted",false);
		return $bean;
	}
	/**
	 * Removes a bean from the database.
	 * This function will remove the specified RedBean_OODBBean
	 * Bean Object from the database.
	 * @throws RedBean_Exception_Security $exception
	 * @param RedBean_OODBBean $bean
	 */
	public function trash( RedBean_OODBBean $bean ) {
		$idfield = $this->writer->getIDField($bean->getMeta("type"));
		$this->signal( "delete", $bean );
		if (!$this->isFrozen) $this->check( $bean );
		try {
			$this->writer->deleteRecord( $bean->getMeta("type"), $bean->$idfield );
		}catch(RedBean_Exception_SQL $e) {
			if (!$this->writer->sqlStateIn($e->getSQLState(),
			array(
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN,
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
			)) throw $e;
		}
		$this->signal( "after_delete", $bean );
		
	}
	/**
	 * Loads and returns a series of beans of type $type.
	 * The beans are loaded all at once.
	 * The beans are retrieved using their primary key IDs
	 * specified in the second argument.
	 * @throws RedBean_Exception_Security $exception
	 * @param string $type
	 * @param array $ids
	 * @return array $beans
	 */
	public function batch( $type, $ids ) {
		if (!$ids) return array();
		$collection = array();
		try {
			$rows = $this->writer->selectRecord($type,$ids);
		}catch(RedBean_Exception_SQL $e) {
			if (!$this->writer->sqlStateIn($e->getSQLState(),
			array(
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN,
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
			)) throw $e;
			$rows = false;
		}
		$this->stash = array();
		if (!$rows) return array();
		foreach($rows as $row) {
			$this->stash[$row[$this->writer->getIDField($type)]] = $row;
		}
		foreach($ids as $id) {
			$collection[ $id ] = $this->load( $type, $id );
		}
		$this->stash = NULL;
		return $collection;
	}
	/**
	 * This is a convenience method; it converts database rows
	 * (arrays) into beans.
	 * @param string $type
	 * @param array $rows
	 * @return array $collectionOfBeans
	 */
	public function convertToBeans($type, $rows) {
		$collection = array();
		$this->stash = array();
		foreach($rows as $row) {
			$id = $row[$this->writer->getIDField($type)];
			$this->stash[$id] = $row;
			$collection[ $id ] = $this->load( $type, $id );
		}
		$this->stash = NULL;
		return $collection;
	}
	/**
	 * Returns the number of beans we have in DB of a given type.
	 * 
	 * @param string $type type of bean we are looking for
	 * 
	 * @return integer $num number of beans found 
	 */
	public function count($type) {
		try {
			return (int) $this->writer->count($type);
		}catch(RedBean_Exception_SQL $e) {
			if (!$this->writer->sqlStateIn($e->getSQLState(),
			array(RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
			)) throw $e;
		}
		return 0;
	}
	/**
	 * Trash all beans of a given type.
	 *
	 * @param string $type type
	 *
	 * @return boolean $yesNo whether we actually did some work or not..
	 */
	public function wipe($type) {
		try {
			$this->writer->wipe($type);
			return true;
		}catch(RedBean_Exception_SQL $e) {
			if (!$this->writer->sqlStateIn($e->getSQLState(),
			array(RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
			)) throw $e;
		}
		return false;
	}
}

/**
 * ToolBox
 * Contains most important redbean tools
 * @file			RedBean/ToolBox.php
 * @description		The ToolBox acts as a resource locator for RedBean but can
 *					be integrated in larger resource locators (nested).
 *					It does not do anything more than just store the three most
 *					important RedBean resources (tools): the database adapter,
 *					the redbean core class (oodb) and the query writer.
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_ToolBox {
	/**
	 *
	 * @var RedBean_OODB
	 */
	private $oodb;
	/**
	 *
	 * @var RedBean_QueryWriter
	 */
	private $writer;
	/**
	 *
	 * @var RedBean_Adapter_DBAdapter
	 */
	private $adapter;
	/**
	 * Constructor.
	 * The Constructor of the ToolBox takes three arguments: a RedBean_OODB $redbean
	 * object database, a RedBean_Adapter $databaseAdapter and a
	 * RedBean_QueryWriter $writer. It stores these objects inside and acts as
	 * a micro service locator. You can pass the toolbox to any object that needs
	 * one of the RedBean core objects to interact with.
	 * @param RedBean_OODB $oodb
	 * @param RedBean_Adapter_DBAdapter $adapter
	 * @param RedBean_QueryWriter $writer
	 * return RedBean_ToolBox $toolbox
	 */
	public function __construct( RedBean_OODB $oodb, RedBean_Adapter $adapter, RedBean_QueryWriter $writer ) {
		$this->oodb = $oodb;
		$this->adapter = $adapter;
		$this->writer = $writer;
		return $this;
	}
	/**
	 * The Toolbox acts as a kind of micro service locator, providing just the
	 * most important objects that make up RedBean. You can pass the toolkit to
	 * any object that needs one of these objects to function properly.
	 * Returns the QueryWriter; normally you do not use this object but other
	 * object might want to use the default RedBean query writer to be
	 * database independent.
	 * @return RedBean_QueryWriter $writer
	 */
	public function getWriter() {
		return $this->writer;
	}
	/**
	 * The Toolbox acts as a kind of micro service locator, providing just the
	 * most important objects that make up RedBean. You can pass the toolkit to
	 * any object that needs one of these objects to function properly.
	 * Retruns the RedBean OODB Core object. The RedBean OODB object is
	 * the ultimate core of Redbean. It provides the means to store and load
	 * beans. Extract this object immediately after invoking a kickstart method.
	 * @return RedBean_OODB $oodb
	 */
	public function getRedBean() {
		return $this->oodb;
	}
	/**
	 * The Toolbox acts as a kind of micro service locator, providing just the
	 * most important objects that make up RedBean. You can pass the toolkit to
	 * any object that needs one of these objects to function properly.
	 * Returns the adapter. The Adapter can be used to perform queries
	 * on the database directly.
	 * @return RedBean_Adapter_DBAdapter $adapter
	 */
	public function getDatabaseAdapter() {
		return $this->adapter;
	}
}
/**
 * CompatManager (Compatibility Management)
 * 
 * @file				RedBean/CompatManager.php
 * @description	Offers easy to use tools to check for database compatibility.
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_CompatManager {
	/**
	 * List of Database constants to be used
	 * for version detection.
	 */
	const C_SYSTEM_MYSQL			= "mysql";
	const C_SYSTEM_SQLITE		= "sqlite";
	const C_SYSTEM_DB2			= "db2";
	const C_SYSTEM_POSTGRESQL	= "pgsql";
	const C_SYSTEM_ORACLE		= "oracle";
	const C_SYSTEM_MSSQL			= "mssql";
	const C_SYSTEM_HYPERTABLE	= "hypertable";
	const C_SYSTEM_INFORMIX		= "informix";
	const C_SYSTEM_SYBASE		= "sybase";
	const C_SYSTEM_FOXPRO		= "foxpro";
	/**
	 *
	 * @var boolean $ignoreWarning
	 */
	private static $ignoreVersion = false;
	/**
	 *
	 * @var string $messageUnsupported
	 */
	protected $messageUnsupported = "
Unfortunately ##YOU## is not supported by this module or class.
Supported System(s): ##DBS##.
To suppress this Exception use: RedBean_CompatManager::ignore(TRUE); ";
	/**
	 *
	 * @var array $supportedSystems
	 */
	protected $supportedSystems = array();
	/**
	 * This method toggles the exception system globally.
	 * If you set this to true exceptions will not be thrown. Use this
	 * if you think the version specification of a module is incorrect
	 * or too narrow.
	 * @param bool $ignore
	 */
	public static function ignore( $tf = TRUE ) {
		self::$ignoreVersion = (bool) $tf;
	}
	/**
	 * Scans the toolbox to determine whether the database adapter
	 * is compatible with the current class, plugin or module.
	 *
	 * @throws RedBean_Exception_UnsupportedDatabase $exception
	 *
	 * @param RedBean_ToolBox $toolbox toolbox
	 *
	 * @return bool $compatible compatible
	 */
	public function scanToolBox( RedBean_ToolBox $toolbox ) {
		
		$brand = strtolower(trim($toolbox->getDatabaseAdapter()->getDatabase()->getDatabaseType()));
		
		$version = $toolbox->getDatabaseAdapter()->getDatabase()->getDatabaseVersion();
		if (!is_numeric($version)) {
			$version = 999; 
		}
		
		if (isset($this->supportedSystems[$brand])
				  && ((float)$this->supportedSystems[$brand] <= (float) $version)
		) {
			return true;
		}
		else {
			if (!self::$ignoreVersion) {
				$this->messageUnsupported = str_replace("##YOU##",$brand." v".$version,$this->messageUnsupported);
				$list = array();
				foreach($this->supportedSystems as $supported=>$version) {
					$list[] = " ".$supported . " v".$version."+";
				}
				$this->messageUnsupported = str_replace("##DBS##",implode(",",$list),$this->messageUnsupported);
				throw new RedBean_Exception_UnsupportedDatabase($this->messageUnsupported);
			}
			else {
				return false;
			}
		}
	}
	/**
	 * Static Variant
	 * Scans the toolbox to determine whether the database adapter
	 * is compatible with the current class, plugin or module.
	 *
	 * @throws RedBean_Exception_UnsupportedDatabase $exception
	 *
	 * @param RedBean_ToolBox $toolbox toolbox
	 * @param array			  $list    list of systems that are supported
	 * 
	 * @return bool $compatible compatible
	 */
	public static function scanDirect( RedBean_ToolBox $toolbox, $list = array() ) {
		$compat = new RedBean_CompatManager();
		$compat->supportedSystems = $list;
		return $compat->scanToolBox($toolbox);
	}
}
/**
 * RedBean Association
 * @file				RedBean/AssociationManager.php
 * @description	Manages simple bean associations.
 *
 * @author			Gabor de Mooij
 * @license			BSD
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_AssociationManager extends RedBean_CompatManager {
	/**
	 * Specify what database systems are supported by this class.
	 * @var array $databaseSpecs
	 */
	protected $supportedSystems = array(
			  RedBean_CompatManager::C_SYSTEM_MYSQL => "5",
			  RedBean_CompatManager::C_SYSTEM_SQLITE=>"3",
			  RedBean_CompatManager::C_SYSTEM_POSTGRESQL=>"8"
	);
	/**
	 * @var RedBean_OODB
	 */
	protected $oodb;
	/**
	 * @var RedBean_Adapter_DBAdapter
	 */
	protected $adapter;
	/**
	 * @var RedBean_QueryWriter
	 */
	protected $writer;
	/**
	 * Constructor
	 * 
	 * @param RedBean_ToolBox $tools toolbox
	 */
	public function __construct( RedBean_ToolBox $tools ) {
		$this->oodb = $tools->getRedBean();
		$this->adapter = $tools->getDatabaseAdapter();
		$this->writer = $tools->getWriter();
	}
	/**
	 * Creates a table name based on a types array.
	 *
	 * @param array $types types
	 *
	 * @return string $table table
	 */
	public function getTable( $types ) {
		sort($types);
		return ( implode("_", $types) );
	}
	/**
	 * Associates two beans with eachother.
	 *
	 * @param RedBean_OODBBean $bean1 bean1
	 * @param RedBean_OODBBean $bean2 bean2
	 */
	public function associate(RedBean_OODBBean $bean1, RedBean_OODBBean $bean2) {
		$table = $this->getTable( array($bean1->getMeta("type") , $bean2->getMeta("type")) );
		$bean = $this->oodb->dispense($table);
		return $this->associateBeans( $bean1, $bean2, $bean );
	}
	/**
	 * Associates a pair of beans. This method associates two beans, no matter
	 * what types.
	 *
	 * @param RedBean_OODBBean $bean1 first bean
	 * @param RedBean_OODBBean $bean2 second bean
	 * @param RedBean_OODBBean $bean  base bean
	 *
	 * @return mixed $id either the link ID or null
	 */
	protected function associateBeans(RedBean_OODBBean $bean1, RedBean_OODBBean $bean2, RedBean_OODBBean $bean) {
		$idfield1 = $this->writer->getIDField($bean1->getMeta("type"));
		$idfield2 = $this->writer->getIDField($bean2->getMeta("type"));
		$property1 = $bean1->getMeta("type") . "_id";
		$property2 = $bean2->getMeta("type") . "_id";
		if ($property1==$property2) $property2 = $bean2->getMeta("type")."2_id";
		$bean->setMeta( "buildcommand.unique" , array( array( $property1, $property2 )));
		$this->oodb->store($bean1);
		$this->oodb->store($bean2);
		$bean->$property1 = $bean1->$idfield1;
		$bean->$property2 = $bean2->$idfield2;
		try {
			return $this->oodb->store( $bean );
		}
		catch(RedBean_Exception_SQL $e) {
			if (!$this->writer->sqlStateIn($e->getSQLState(),
			array(
			RedBean_QueryWriter::C_SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION
			))) throw $e;
		}
	}
	/**
	 * Gets related beans of type $type for bean $bean
	 *
	 * @param RedBean_OODBBean  $bean 		bean
	 * @param string			$type 		type
	 * @param bool				$linksOnly  whether you want keys of links themselves
	 * @param string			$sql		optional SQL template to use,
	 *
	 * @return array $idsOrRows ids or rows
	 */
	public function related( RedBean_OODBBean $bean, $type, $getLinks=false, $sql=false ) {
		$table = $this->getTable( array($bean->getMeta("type") , $type) );
		$idfield = $this->writer->getIDField($bean->getMeta("type"));
		if ($type==$bean->getMeta("type")) {
			$type .= "2";
			$cross = 1;
		}
		else $cross=0;
		if (!$getLinks) $targetproperty = $type."_id"; else $targetproperty="id";
		$property = $bean->getMeta("type")."_id";
		try {
			if ($cross) {
				$sqlFetchKeys = $this->writer->selectByCrit(
						  $targetproperty,
						  $table,
						  $property,
						  $bean->$idfield,
						  true,$sql
				);
			}
			else {
				$sqlFetchKeys = $this->writer->selectByCrit(
						  $targetproperty,
						  $table,
						  $property,
						  $bean->$idfield,false,$sql
				);
			}
			return ( $sqlFetchKeys ); 
		}catch(RedBean_Exception_SQL $e) {
			if (!$this->writer->sqlStateIn($e->getSQLState(),
			array(
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN,
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
			)) throw $e;
			return array();
		}
	}
	/**
	 * Breaks the association between two beans
	 *
	 * @param RedBean_OODBBean $bean1 first bean
	 * @param RedBean_OODBBean $bean2 second bean
	 */
	public function unassociate(RedBean_OODBBean $bean1, RedBean_OODBBean $bean2) {
		$this->oodb->store($bean1);
		$this->oodb->store($bean2);
		$table = $this->getTable( array($bean1->getMeta("type") , $bean2->getMeta("type")) );
		$idfield1 = $this->writer->getIDField($bean1->getMeta("type"));
		$idfield2 = $this->writer->getIDField($bean2->getMeta("type"));
		$type = $bean1->getMeta("type");
		if ($type==$bean2->getMeta("type")) { 
			$type .= "2";
			$cross = 1;
		}
		else $cross = 0;
		$property1 = $type."_id";
		$property2 = $bean2->getMeta("type")."_id";
		$value1 = (int) $bean1->$idfield1;
		$value2 = (int) $bean2->$idfield2;
		try {
			$this->writer->deleteByCrit($table,array($property1=>$value1,$property2=>$value2));
			if ($cross) {
				$this->writer->deleteByCrit($table,array($property2=>$value1,$property1=>$value2));
			}
		}catch(RedBean_Exception_SQL $e) {
			if (!$this->writer->sqlStateIn($e->getSQLState(),
			array(
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN,
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
			)) throw $e;
		}
	}
	/**
	 * Removes all relations for a bean
	 *
	 * @param RedBean_OODBBean $bean bean
	 * @param string           $type type
	 */
	public function clearRelations(RedBean_OODBBean $bean, $type) {
		$this->oodb->store($bean);
		$table = $this->getTable( array($bean->getMeta("type") , $type) );
		$idfield = $this->writer->getIDField($bean->getMeta("type"));
		if ($type==$bean->getMeta("type")) {
			$property2 = $type."2_id";
			$cross = 1;
		}
		else $cross = 0;
		$property = $bean->getMeta("type")."_id";
		try {
			$this->writer->deleteByCrit($table,array($property=>$bean->$idfield));
			if ($cross) {
				$this->writer->deleteByCrit($table,array($property2=>$bean->$idfield));
			}
		}catch(RedBean_Exception_SQL $e) {
			if (!$this->writer->sqlStateIn($e->getSQLState(),
			array(
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN,
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
			)) throw $e;
		}
	}
	/**
	 * @deprecated
	 * Creates a 1 to Many Association
	 * If the association fails it throws an exception.
	 * @throws RedBean_Exception_SQL $failedToEnforce1toN
	 *
	 * @param RedBean_OODBBean $bean1 bean1
	 * @param RedBean_OODBBean $bean2 bean2
	 *
	 * @return RedBean_AssociationManager $chainable chainable
	 */
	public function set1toNAssoc(RedBean_OODBBean $bean1, RedBean_OODBBean $bean2) {
		$type = $bean1->getMeta("type");
		$this->clearRelations($bean2, $type);
		$this->associate($bean1, $bean2);
		if (count( $this->related($bean2, $type) )===1) {
			return $this;
		}
		else {
			throw new RedBean_Exception_SQL("Failed to enforce 1toN Relation for $type ");
		}
	}
}
/**
 * RedBean Tree
 *
 * @file			RedBean/TreeManager.php
 * @description		Tree structure for beans.
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_TreeManager extends RedBean_CompatManager {
	/**
	 * Specify what database systems are supported by this class.
	 * @var array $databaseSpecs
	 */
	protected $supportedSystems = array(
			  RedBean_CompatManager::C_SYSTEM_MYSQL => "5",
			  RedBean_CompatManager::C_SYSTEM_SQLITE=>"3"
	);
	/**
	 *
	 * @var string
	 */
	private $property = "parent_id";
	/**
	 * @var RedBean_OODB
	 */
	private $oodb;
	/**
	 * @var RedBean_Adapter_DBAdapter
	 */
	private $adapter;
	/**
	 * @var RedBean_QueryWriter
	 */
	private $writer;
	/**
	 * Constructor.
	 * @param RedBean_ToolBox $tools
	 */
	public function __construct( RedBean_ToolBox $tools ) {
		$this->oodb = $tools->getRedBean();
		$this->adapter = $tools->getDatabaseAdapter();
		$this->writer = $tools->getWriter();
	}
	/**
	 * Checks whether types of beans match. If the types do not match
	 * this method will throw a RedBean_Exception_Security exception.
	 * @param RedBean_OODBBean $bean1
	 * @param RedBean_OODBBean $bean2
	 */
	private function equalTypes( RedBean_OODBBean $bean1, RedBean_OODBBean $bean2 ) {
		if ($bean1->getMeta("type")!==$bean2->getMeta("type")) {
			throw new RedBean_Exception_Security("Incompatible types, tree can only work with identical types.");
		}
	}
	/**
	 * Attaches the specified child node to the specified parent node.
	 * @param RedBean_OODBBean $parent
	 * @param RedBean_OODBBean $child
	 */
	public function attach( RedBean_OODBBean $parent, RedBean_OODBBean $child ) {
		$this->equalTypes( $parent, $child );
		$idfield = $this->writer->getIDField($parent->getMeta("type"));
		if (!intval($parent->$idfield)) $this->oodb->store($parent);
		$child->{$this->property} = $parent->$idfield;
		$this->oodb->store($child);
	}
	/**
	 * Returns all the nodes that have been attached to the specified
	 * parent node.
	 * @param RedBean_OODBBean $parent
	 * @return array $childObjects
	 */
	public function children( RedBean_OODBBean $parent ) {
		$idfield = $this->writer->getIDField($parent->getMeta("type"));
		try {
			$ids = $this->writer->selectByCrit( $idfield,
					  $parent->getMeta("type"),
					  $this->property,
					  intval( $parent->$idfield ) );
		}
		catch(RedBean_Exception_SQL $e) {
			return array();
		}
		return $this->oodb->batch($parent->getMeta("type"),$ids	);
	}
	public function getParent( RedBean_OODBBean $bean ) {
		return $this->oodb->load( $bean->getMeta("type"), (int)$bean->parent_id);
	}
}
/**
 * RedBean Links
 * @file                RedBean/LinkManager.php
 * @description		Manages foreign keys
 *
 * @author			Gabor de Mooij
 * @license			BSD
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_LinkManager extends RedBean_CompatManager {
	/**
	 * Specify what database systems are supported by this class.
	 * @var array $databaseSpecs
	 */
	protected $supportedSystems = array(
			  RedBean_CompatManager::C_SYSTEM_MYSQL => "5",
			  RedBean_CompatManager::C_SYSTEM_SQLITE=>"3",
			  RedBean_CompatManager::C_SYSTEM_POSTGRESQL=>"8"
	);
	/**
	 * @var RedBean_OODB
	 */
	protected $oodb;
	/**
	 * @var RedBean_Adapter_DBAdapter
	 */
	protected $adapter;
	/**
	 * @var RedBean_QueryWriter
	 */
	protected $writer;
	/**
	 * Constructor
	 * @param RedBean_ToolBox $tools
	 */
	public function __construct( RedBean_ToolBox $tools ) {
		$this->oodb = $tools->getRedBean();
		$this->adapter = $tools->getDatabaseAdapter();
		$this->writer = $tools->getWriter();
	}
	/**
	 * Returns the fieldname for a foreign key.
	 * @param string $typeName
	 * @return string $fieldName
	 */
	public function getLinkField( $typeName, $name = null ) {
		$fieldName = strtolower( $typeName )."_id";
		if ($name !== null) {
			$fieldName = "{$name}_$fieldName";
		}
		$fieldName = preg_replace( "/\W/","", $fieldName );
		return $fieldName;
	}
	/**
	 * Adds a reference to bean2 in bean1.
	 * @param RedBean_OODBBean $bean1
	 * @param RedBean_OODBBean $bean2
	 */
	public function link(RedBean_OODBBean $bean1, RedBean_OODBBean $bean2, $name = null) {
		if (!$bean2->id) {
			$this->oodb->store( $bean2 );
		}
		$fieldName = $this->getLinkField( $bean2->getMeta("type"), $name);
		$bean1->$fieldName = $bean2->id;
		return $this;
	}
	/**
	 * Returns a linked bean.
	 * @param RedBean_OODBBean $bean
	 * @param string $typeName
	 * @return RedBean_OODBBean $bean
	 */
	public function getBean( RedBean_OODBBean $bean, $typeName, $name = null) {
		$fieldName = $this->getLinkField($typeName, $name);
		$id = (int)$bean->$fieldName;
		if ($id) {
			return $this->oodb->load($typeName, $id);
		}
		else {
			return null;
		}
	}
	/**
	 * Removes a linked bean.
	 * @param RedBean_OODBBean $bean
	 * @param string $typeName
	 */
	public function breakLink( RedBean_OODBBean $bean, $typeName, $name = null) {
		$fieldName = $this->getLinkField($typeName, $name);
		$bean->$fieldName = NULL;
	}
	/**
	 * Returns a linked bean ID.
	 * @param RedBean_OODBBean $bean
	 * @param string $typeName
	 * @return RedBean_OODB $bean
	 */
	public function getKey(RedBean_OODBBean $bean, $typeName, $name = null) {
		$fieldName = $this->getLinkField($typeName, $name);
		$id = (int)$bean->$fieldName;
		return $id;
	}
	/**
	 * Returns all beans that are linked to the given bean.
	 * @param RedBean_OODBBean $bean
	 * @param string $typeName
	 * @return array $beans
	 */
	public function getKeys( RedBean_OODBBean $bean, $typeName ) {
		$fieldName = $this->getLinkField($typeName);
		$id = (int)$bean->$fieldName;
		$ids = $this->writer->selectByCrit($this->writer->getIDField($this->writer->getFormattedTableName($typeName)),
				  $typeName,
				  $bean->getMeta("type")."_id",
				  $bean->id);
		return $ids;
	}
}
/**
 * RedBean Extended Association
 * @file			RedBean/ExtAssociationManager.php
 * @description		Manages complex bean associations.
 *
 * @author			Gabor de Mooij
 * @license			BSD
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_ExtAssociationManager extends RedBean_AssociationManager {
	/**
	 * Associates two beans with eachother.
	 *
	 * @param RedBean_OODBBean $bean1 bean 1
	 * @param RedBean_OODBBean $bean2 bean 2
	 *
	 */
	public function extAssociate(RedBean_OODBBean $bean1, RedBean_OODBBean $bean2, RedBean_OODBBean $baseBean ) {
		$table = $this->getTable( array($bean1->getMeta("type") , $bean2->getMeta("type")) );
		$baseBean->setMeta("type", $table );
		return $this->associateBeans( $bean1, $bean2, $baseBean );
	}
}
/**
 * RedBean Setup
 * Helper class to quickly setup RedBean for you
 * @file 		RedBean/Setup.php
 * @description		Helper class to quickly setup RedBean for you
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_Setup {
	/**
	 *
	 * @var array
	 * Keeps track of the observers
	 */
	private static $observers = array();
	/**
	 *
	 * @var RedBean_ToolBox $toolbox
	 */
	private static $toolbox = NULL;
	/**
	 * This method checks the DSN string. If the DSN string contains a
	 * database name that is not supported by RedBean yet then it will
	 * throw an exception RedBean_Exception_NotImplemented. In any other
	 * case this method will just return boolean TRUE.
	 * @throws RedBean_Exception_NotImplemented
	 * @param string $dsn
	 * @return boolean $true
	 */
	private static function checkDSN($dsn) {
		$dsn = trim($dsn);
		$dsn = strtolower($dsn);
		if (
		strpos($dsn, "mysql:")!==0
				  && strpos($dsn,"sqlite:")!==0
				  && strpos($dsn,"pgsql:")!==0
		) {
			throw new RedBean_Exception_NotImplemented("
					Support for this DSN has not been implemented yet. \n
					Begin your DSN with: 'mysql:' or 'sqlite:'
				");
		}
		else {
			return true;
		}
	}
	/**
	 * Generic Kickstart method.
	 * This is the generic kickstarter. It will establish a database connection
	 * using the $dsn, the $username and the $password you provide.
	 * If $frozen is boolean TRUE it will start RedBean in frozen mode, meaning
	 * that the database cannot be altered. If RedBean is started in fluid mode
	 * it will adjust the schema of the database if it detects an
	 * incompatible bean.
	 * This method returns a RedBean_Toolbox $toolbox filled with a
	 * RedBean_Adapter, a RedBean_QueryWriter and most importantly a
	 * RedBean_OODB; the object database. To start storing beans in the database
	 * simply say: $redbean = $toolbox->getRedBean(); Now you have a reference
	 * to the RedBean object.
	 * Optionally instead of using $dsn you may use an existing PDO connection.
	 * Example: RedBean_Setup::kickstart($existingConnection, true);
	 *
	 * @param  string|PDO $dsn
	 * @param  string $username
	 * @param  string $password
	 * @return RedBean_ToolBox $toolbox
	 */
	public static function kickstart( $dsn, $username=NULL, $password=NULL, $frozen=false ) {
		if ($dsn instanceof PDO) {
			$pdo = new RedBean_Driver_PDO($dsn);
			$dsn = $pdo->getDatabaseType() ;
		}
		else {
			self::checkDSN($dsn);
			$pdo = new RedBean_Driver_PDO( $dsn,$username,$password );
		}
		$adapter = new RedBean_Adapter_DBAdapter( $pdo );
		if (strpos($dsn,"pgsql")===0) {
			$writer = new RedBean_QueryWriter_PostgreSQL( $adapter, $frozen );
		}
		else if (strpos($dsn,"sqlite")===0) {
			$writer = new RedBean_QueryWriter_SQLiteT( $adapter, $frozen );
		}
		else {
			$writer = new RedBean_QueryWriter_MySQL( $adapter, $frozen );
		}
		$redbean = new RedBean_OODB( $writer );
		$toolbox = new RedBean_ToolBox( $redbean, $adapter, $writer );
		
		self::$toolbox = $toolbox;
		return self::$toolbox;
	}
	/**
	 * Kickstart for development phase.
	 * Use this method to quickly setup RedBean for use during development phase.
	 * This Kickstart establishes a database connection
	 * using the $dsn, the $username and the $password you provide.
	 * It will start RedBean in fluid mode; meaning the database will
	 * be altered if required to store your beans.
	 * This method returns a RedBean_Toolbox $toolbox filled with a
	 * RedBean_Adapter, a RedBean_QueryWriter and most importantly a
	 * RedBean_OODB; the object database. To start storing beans in the database
	 * simply say: $redbean = $toolbox->getRedBean(); Now you have a reference
	 * to the RedBean object.
	 * @param  string $dsn
	 * @param  string $username
	 * @param  string $password
	 * @return RedBean_ToolBox $toolbox
	 */
	public static function kickstartDev( $dsn, $username="root", $password="" ) {
		$toolbox = self::kickstart($dsn, $username, $password);
		return $toolbox;
	}
	/**
	 * @param  string $dsn
	 * @return RedBean_ToolBox $toolbox
	 */
	public static function kickstartDevL( $dsn ) {
		self::checkDSN($dsn);
		$pdo = new RedBean_Driver_PDO( $dsn ,"","");
		$adapter = new RedBean_Adapter_DBAdapter( $pdo );
		$writer = new RedBean_QueryWriter_SQLiteT( $adapter, false );
		$redbean = new RedBean_OODB( $writer );
		$toolbox = new RedBean_ToolBox( $redbean, $adapter, $writer );
		
		self::$toolbox = $toolbox;
		return self::$toolbox;
	}
	/**
	 * Almost the same as Dev, but adds the journaling plugin by default for you.
	 * This Kickstart establishes a database connection
	 * using the $dsn, the $username and the $password you provide.
	 * The Journaling plugin detects Race Conditions, for more information please
	 * consult the RedBean_Plugin_ChangeLogger Documentation.
	 * This method returns a RedBean_Toolbox $toolbox filled with a
	 * RedBean_Adapter, a RedBean_QueryWriter and most importantly a
	 * RedBean_OODB; the object database. To start storing beans in the database
	 * simply say: $redbean = $toolbox->getRedBean(); Now you have a reference
	 * to the RedBean object.
	 * @param  string $dsn
	 * @param  string $username
	 * @param  string $password
	 * @return RedBean_ToolBox $toolbox
	 */
	public static function KickStartDevWithJournal($dsn, $username="root", $password="") {
		$toolbox = self::kickstart($dsn, $username, $password);
		$redbean = $toolbox->getRedBean();
		$logger = new RedBean_Plugin_ChangeLogger( $toolbox );
		self::$observers["logger"] = $logger;
		$redbean->addEventListener( "open", $logger );
		$redbean->addEventListener( "update", $logger);
		$redbean->addEventListener( "delete", $logger);
		return $toolbox;
	}
	/**
	 * Kickstart method for production environment.
	 * This Kickstart establishes a database connection
	 * using the $dsn, the $username and the $password you provide.
	 * This method will start RedBean in frozen mode which is
	 * the preferred mode of operation for a production environment.
	 * In frozen mode, RedBean will not alter the schema of the database;
	 * which improves performance and security.
	 * This method returns a RedBean_Toolbox $toolbox filled with a
	 * RedBean_Adapter, a RedBean_QueryWriter and most importantly a
	 * RedBean_OODB; the object database. To start storing beans in the database
	 * simply say: $redbean = $toolbox->getRedBean(); Now you have a reference
	 * to the RedBean object.
	 * @param  string $dsn
	 * @param  string $username
	 * @param  string $password
	 * @return RedBean_ToolBox $toolbox
	 */
	public static function kickstartFrozen( $dsn, $username, $password ) {
		$toolbox = self::kickstart($dsn, $username, $password, true);
		$toolbox->getRedBean()->freeze(true);
		return $toolbox;
	}
	/**
	 * Kickstart Method for debugging.
	 * This method returns a RedBean_Toolbox $toolbox filled with a
	 * RedBean_Adapter, a RedBean_QueryWriter and most importantly a
	 * RedBean_OODB; the object database. To start storing beans in the database
	 * simply say: $redbean = $toolbox->getRedBean(); Now you have a reference
	 * to the RedBean object.
	 * @param  string $dsn
	 * @param  string $username
	 * @param  string $password
	 * @return RedBean_ToolBox $toolbox
	 */
	public static function kickstartDebug( $dsn, $username="root", $password="" ) {
		$toolbox = self::kickstart($dsn, $username, $password);
		$toolbox->getDatabaseAdapter()->getDatabase()->setDebugMode( true );
		return $toolbox;
	}
	/**
	 * During a kickstart method observers may be attached to the RedBean_OODB object.
	 * Setup keeps track of the observers that are connected to RedBean.
	 * Returns the observers that have been attached by Setup.
	 * @return array $observers
	 */
	public static function getAttachedObservers() {
		return self::$observers;
	}
	/**
	 * This is a convenience method. By default a kickstart method
	 * returns the RedBean_ToolBox $toolbox for you with all necessary
	 * objects inside. If for some reason you need to have access to the
	 * latest toolbox that Setup has assembled you can use this function
	 * to retrieve it.
	 * Returns the most recently assembled toolbox
	 * @return RedBean_ToolBox $toolbox
	 */
	public static function getToolBox() {
		return self::$toolbox;
	}
}

/**
 * RedBean ChangeLogger
 * Shields you from race conditions automatically.
 * @file 		RedBean/ChangeLogger.php
 * @description		Shields you from race conditions automatically.
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_Plugin_ChangeLogger extends RedBean_CompatManager implements RedBean_Plugin,RedBean_Observer {
	/**
	 * Specify what database systems are supported by this class.
	 * @var array $databaseSpecs
	 */
	protected $supportedSystems = array(
			  RedBean_CompatManager::C_SYSTEM_MYSQL => "5",
	);
	/**
	 * @var RedBean_QueryWriter
	 * Contains a reference to the query writer.
	 */
	private $writer;
	/**
	 *
	 * @var RedBean_Adapter
	 * Contains a reference to the database adapter.
	 */
	private $adapter;
	/**
	 *
	 * @var array
	 * Our secret stash of redbeans... ;)
	 */
	private $stash = array();
	/**
	 *
	 * @var RedBean_OODB
	 * Contains a reference to the RedBean OODB object.
	 */
	private $redbean;
	/**
	 * Constructor, requires a writer
	 * 
	 * @param RedBean_QueryWriter $writer
	 */
	public function __construct(RedBean_ToolBox $toolbox) {
		
		$this->scanToolBox( $toolbox );
		$this->writer = $toolbox->getWriter();
		$this->adapter = $toolbox->getDatabaseAdapter();
		$this->redbean = $toolbox->getRedBean();
		if (!$this->redbean->isFrozen()) {
			$this->adapter->exec("
						CREATE TABLE IF NOT EXISTS `__log` (
						`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
						`tbl` VARCHAR( 255 ) NOT NULL ,
						`action` TINYINT( 2 ) NOT NULL ,
						`itemid` INT( 11 ) NOT NULL
						) ENGINE = MYISAM ;
				"); 
		}
		$maxid = $this->adapter->getCell("SELECT MAX(id) FROM __log");
		$this->adapter->exec("DELETE FROM __log WHERE id < $maxid - 200 ");
	}
	/**
	 * Throws an exception if information in the bean has been changed
	 * by another process or bean. This is actually the same as journaling
	 * using timestamps however with timestamps you risk race conditions
	 * when the measurements are not fine-grained enough; with
	 * auto-incremented primary key ids we dont have this risk.
	 *
	 * @param string $event				event name
	 * @param RedBean_OODBBean $item item
	 */
	public function onEvent( $event, $item ) {
		$id = $item->id;
		if (! ((int) $id)) $event="open";
		$type = $item->getMeta("type");
		if ($event=="open") {
			if (isset($this->stash[$id])) {
				$insertid = $this->stash[$id];
				unset($this->stash[$id]);
				return $insertid;
			}
			$insertid = $this->writer->insertRecord("__log",array("action","tbl","itemid"),
					  array(array(1,  $type, $id)));
			$item->setMeta("opened",$insertid);
		}
		if ($event=="update" || $event=="delete") {
			if (($item->getMeta("opened"))) $oldid = $item->getMeta("opened"); else $oldid=0;
			$newid = $this->checkChanges($type,$id, $oldid);
			$item->setMeta("opened",$newid);
		}
	}
	/**
	 * Facilitates preloading. If you want to load multiple beans at once
	 * these beans can be locked individually; given N beans this means approx.
	 * N*3 queries which is quite a lot. This method allows you to pre-lock or pre-open
	 * multiple entries at once. All beans will get an opened stamp that correspond to
	 * the first bean opened. This means this approach is conservative; it might
	 * produce a higher rate of false alarms but it does not compromise
	 * concurrency security.
	 *
	 * @param string $type type
	 * @param array  $ids  series of ids
	 */
	public function preLoad( $type, $ids ) {
		$this->adapter->exec("INSERT INTO __log (id,action,tbl,itemid)
		VALUES(NULL, :action,:tbl,:id)",array(":action"=>1,":tbl"=>"__no_type__",":id"=>0)); 
		$insertid = $this->adapter->getInsertID();
		$values = array();
		foreach($ids as $id) { 
			$this->stash[$id]=$insertid; 
			$values[] = array(1, $type, $id); 
		}
		$this->writer->insertRecord("__log",array("action","tbl","itemid"), $values); 
	}
	/**
	 * For testing only, dont use.
	 * 
	 * @return array $stash stash
	 */
	public function testingOnly_getStash() {
		return $this->stash;
	}
	/**
	 * Gets information about changed records using a type and id and a logid.
	 * RedBean Locking shields you from race conditions by comparing the latest
	 * cached insert id with a the highest insert id associated with a write action
	 * on the same table. If there is any id between these two the record has
	 * been changed and RedBean will throw an exception. This function checks for changes.
	 * If changes have occurred it will throw an exception. If no changes have occurred
	 * it will insert a new change record and return the new change id.
	 * This method locks the log table exclusively.
	 *
	 * @param  string  $type  type
	 * @param  integer $id    id
	 * @param  integer $logid log id
	 *
	 * @return integer $newchangeid new id
	 */
	public function checkChanges($type, $id, $logid) {
		$type = $this->writer->check($type);
		$id = (int) $id;
		$logid = (int) $logid;
		$num = $this->adapter->getCell("
        SELECT count(*) FROM __log WHERE tbl=\"$type\" AND itemid=$id AND action=2 AND id > $logid");
		if ($num) {
			throw new RedBean_Exception_FailedAccessBean("Locked, failed to access (type:$type, id:$id)");
		}
		$this->adapter->exec("INSERT INTO __log (id,action,tbl,itemid) VALUES(NULL, 2,:tbl,:id)",array(":tbl"=>$type, ":id"=>$id));
		$newid = $this->adapter->getInsertID();
		if ($this->adapter->getCell("select id from __log where tbl=:tbl AND id < $newid and id > $logid and action=2 and itemid=$id ",
		array(":tbl"=>$type))) {
			throw new RedBean_Exception_FailedAccessBean("Locked, failed to access II (type:$type, id:$id)");
		}
		return $newid;
	}
}
/**
 * RedBean Bean Cache
 * @file				RedBean/Plugin/Cache.php
 * @description	Decorator for RedBean core class RedBean_OODB
 *						Adds primitive caching to RedBean.
 *
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_Plugin_Cache extends RedBean_Observable implements RedBean_Plugin, RedBean_ObjectDatabase {
	/**
	 * @var RedBean_OODB
	 * Contains a reference to the RedBean OODB object.
	 */
	private $oodb;
	/**
	 * @var RedBean_QueryWriter
	 * Contains a reference to the query writer.
	 */
	private $writer;
	/**
	 * @var array
	 * Cache array.
	 */
	private $cache = array();
	/**
	 * @var array
	 * Keeps track of original beans.
	 */
	private $originals = array();
	/**
	 * @var integer
	 * A simple column counter.
	 */
	private $columnCounter = 0;
	/**
	 * Constructor.
	 *
	 * @param RedBean_OODB    $oodb    object database
	 * @param RedBean_ToolBox $toolBox toolbox
	 */
	public function __construct( RedBean_OODB $oodb, RedBean_ToolBox $toolBox ) {
		$this->oodb = $oodb;
		$this->writer = $toolBox->getWriter();
	}
	/**
	 * Adds event listener.
	 * 
	 * @param string				$event	 event identifier
	 * @param RedBean_Observer $observer observer
	 */
	public function addEventListener($event, RedBean_Observer $o) {
		$this->oodb->addEventListener($event, $o);
	}
	/**
	 * Generates a key based on the ID and TYPE of a bean to
	 * identify the bean in the cache.
	 *
	 * @param RedBean_OODBBean $bean bean to make fingerprint of
	 * 
	 * @return string $key fingerprint of bean
	 */
	private function generateKey( RedBean_OODBBean $bean ) {
		$type=$bean->getMeta("type");
		$idfield = $this->writer->getIDField($type);
		$id = $bean->$idfield;
		return sha1($type."-".$id);
	}
	/**
	 * Puts a bean in the cache and stores a copy of the bean in the
	 * cache archive.
	 *
	 * @param RedBean_OODBBean $bean bean to put in cache
	 *
	 * @return RedBean_Plugin_Cache $myself chainable
	 */
	private function putInCache( RedBean_OODBBean $bean ) {
		$key = $this->generateKey($bean);
		$this->cache[$key]=$bean;
		$copy = clone $bean;
		$copy->copyMetaFrom($bean);
		$this->originals[ $key ] = $copy;
		return $this;
	}
	/**
	 * Fetches a bean from the cache or returns NULL.
	 *
	 * @param RedBean_OODBBean $bean bean
	 *
	 * @return RedBean_OODBBean $bean bean
	 */
	private function fetchFromCache( RedBean_OODBBean $bean ) {
		$key = $this->generateKey($bean);
		if (isset($this->cache[$key])) {
			return $this->cache[$key];
		}
		else {
			return NULL;
		}
	}
	/**
	 * Fetches a bean from the cache or returns NULL.
	 * This function takes a TYPE and ID.
	 *
	 * @param string  $type type
	 * @param integer $id	id
	 *
	 * @return  RedBean_OODBBean $bean
	 */
	private function fetchFromCacheByTypeID( $type, $id ) {
		$bean = $this->oodb->dispense($type);
		$idfield = $this->writer->getIDField($type);
		$bean->$idfield = $id;
		return $this->fetchFromCache($bean);
	}
	/**
	 * Fetches the original bean as it was stored in the cache
	 * archive or NULL.
	 *
	 * @param RedBean_OODBBean $bean bean
	 *
	 * @return RedBean_OODBBean $bean bean
	 */
	private function fetchOriginal(RedBean_OODBBean $bean) {
		$key = $this->generateKey($bean);
		if (isset($this->originals[$key])) {
			return $this->originals[$key];
		}
		else {
			return NULL;
		}
	}
	/**
	 * Removes a bean from the cache and the archive.
	 * 
	 * @param RedBean_OODBBean $bean bean
	 */
	private function removeFromCache( RedBean_OODBBean $bean ) {
		$key = $this->generateKey($bean);
		unset($this->cache[$key]);
		unset($this->originals[$key]);
		return $this;
	}
	/**
	 * Tries to load a bean from cache, if this fails, it asks
	 * the oodb object to load the bean from the database.
	 *
	 * @param string  $type type of bean to load
	 * @param integer $id   primary key of bean
	 * 
	 * @return RedBean_OODB $bean the bean that was found in cache or DB
	 */
	public function load( $type, $id ) {
		$bean = $this->fetchFromCacheByTypeID($type, $id);
		if ($bean) {
			return $bean;
		}
		else {
			$bean = $this->oodb->load($type, $id);
			$this->putInCache($bean);
			return $bean;
		}
	}
	/**
	 * Stores a bean and updates cache.
	 *
	 * @param  RedBean_OODBBean $bean bean
	 * 
	 * @return integer $id id
	 */
	public function store( RedBean_OODBBean $bean ) {
		$this->columnCounter = 0;
		$type=$bean->getMeta("type");
		$idfield = $this->writer->getIDField($type);
		$newbean = $this->oodb->dispense($type);
		$newbean->$idfield = $bean->$idfield;
		$oldBean = $this->fetchOriginal($bean);
		
		if ($oldBean) {
			
			$dirty = false;
			
			foreach($oldBean as $p=>$v) {
				if ($v !== $bean->$p && $p!=$idfield) {
					$newbean->$p = $bean->$p;
					
					$this->columnCounter++; 
					
					$dirty=true;
				}
			}
			
			foreach($bean as $p=>$v) {
				if (!isset($oldBean->$p)) {
					$dirty=true;
					$newbean->$p = $bean->$p;
					$this->columnCounter++;
				}
			}
			
			if ($dirty) {
				$newbean->copyMetaFrom($bean);
				$id = $this->oodb->store($newbean);
				$bean->copyMetaFrom($newbean);
				$this->putInCache($bean);
				return $id;
			}
			else {
				return $bean->$idfield;
			}
		}
		else {
			$id = $this->oodb->store($bean);
			$this->putInCache($bean);
			return $id;
		}
	}
	/**
	 * Trashes a bean and removes the bean from cache.
	 * @param RedBean_OODBBean $bean
	 */
	public function trash( RedBean_OODBBean $bean ) {
		$this->removeFromCache($bean);
		return $this->oodb->trash($bean);
	}
	/**
	 * Loads a batch of beans all at once.
	 * This function first inspects the cache; if every element in the batch
	 * is available in the cache, the function will return the collected beans
	 * from the cache. If one or more beans cannot be found, the function will
	 * ask oodb for the beans and update the cache.
	 *
	 * @param string  $type type you are looking for
	 * @param integer $ids  series of keys of beans you want to load in memory
	 *
	 * @return array $beans collection of beans
	 */
	public function batch( $type, $ids ) {
		$idfield = $this->writer->getIDField($type);
		$collect = array();
		foreach($ids as $id) {
			$bean = $this->fetchFromCacheByTypeID($type, $id);
			if ($bean) $collect[$id] = $bean;
		}
		if (count($collect) == count($ids)) {
			return $collect;
		}
		else {
			$beans = $this->oodb->batch($type, $ids);
			foreach($beans as $bean) {
				$this->putInCache( $bean );
			}
			return $beans;
		}
	}
	/**
	 * Dispenses a bean, just like oodb does
	 *
	 * @param string $type type of the bean
	 *
	 * @return RedBean_OODBBean $bean freshly dispensed bean
	 */
	public function dispense( $type ) {
		return $this->oodb->dispense($type);
	}
	/**
	 * For testing only; returns the number of properties that has
	 * been updated in the latest store action.
	 * 
	 * @return integer $count count
	 */
	public function test_getColCount() {
		return $this->columnCounter;
	}
	/**
	 * Added to comply with new interface Object Database
	 */
	/**
	 * Returns the number of beans we have in DB of a given type.
	 *
	 * @todo implement Caching here
	 *
	 * @param string $type type of bean we are looking for
	 *
	 * @return integer $num number of beans found
	 */
	public function count($type) {
		try {
			return (int) $this->writer->count($type);
		}catch(RedBean_Exception_SQL $e) {
			if (!$this->writer->sqlStateIn($e->getSQLState(),
			array(RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
			)) throw $e;
		}
		return 0;
	}
	/**
	 * Trash all beans of a given type.
	 *
	 * @param string $type type
	 *
	 * @return boolean $yesNo whether we actually did some work or not..
	 */
	public function wipe($type) {
		try {
			$this->writer->wipe($type);
			
			$this->cache = array();
			$this->originals = array();
			return true;
		}catch(RedBean_Exception_SQL $e) {
			if (!$this->writer->sqlStateIn($e->getSQLState(),
			array(RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
			)) throw $e;
		}
		return false;
	}
}
/**
 * RedBean Bean Finder
 *
 * @file					RedBean/Plugin/Finder.php
 * @description		Provides a more convenient way to find beans
 *
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_Plugin_Finder implements RedBean_Plugin {
	/**
	 * Fetches a collection of OODB Bean objects based on the SQL
	 * criteria provided. For instance;
	 *
	 * - RedBean_Plugin_Finder::where("page", " name LIKE '%more%' ");
	 *
	 * Will return all pages that have the word 'more' in their name.
	 * The second argument is actually just plain SQL; the function expects
	 * this SQL to be compatible with a SELECT * FROM TABLE WHERE X query,
	 * where X is ths search string you provide in the second parameter.
	 * Another example, using slots:
	 *
	 * - RedBean_Plugin_Finder::where("page", " name LIKE :str ",array(":str"=>'%more%'));
	 *
	 * Also, note that the default search is always 1. So if you do not
	 * specify a search parameter this function will just return every
	 * bean of the given type:
	 *
	 * - RedBean_Plugin_Finder::where("page"); 
	 *
	 *
	 * @param string $type   type of bean you are looking for
	 * @param string $SQL    SQL code, start with 1 if you want no WHERE-clause
	 * @param array  $values values to bind to slots in query
	 *
	 * @return array $beans beans we come up with..
	 */
	public static function where( $type, $SQL = " 1 ", $values=array(),
			  $tools = false, $ignoreGSQLWarn = false ) {
		if ($SQL==="") $SQL = " 1 ";
		
		$type = preg_replace("/\W/","", $type);
		
		if (!$tools) $tools = RedBean_Setup::getToolBox();
		RedBean_CompatManager::scanDirect($tools, array(
				  RedBean_CompatManager::C_SYSTEM_MYSQL => "5",
				  RedBean_CompatManager::C_SYSTEM_SQLITE => "3",
				  RedBean_CompatManager::C_SYSTEM_POSTGRESQL => "7"
		));
		
		$redbean = $tools->getRedBean();
		$adapter = $tools->getDatabaseAdapter();
		$writer = $tools->getWriter();
		
		if (!$redbean->isFrozen()) {
			$SQL = self::parseGoldSQL($SQL, $type, $tools);
		}
		else {
			if (!$ignoreGSQLWarn && strpos($SQL,"@")!==false) {
				throw new RedBean_Exception_SQL("Gold SQL is
					only allowed in FLUID mode,
					to ignore use extra argument TRUE for RedBean_Plugin_Finder::Where");
			}
		}
		$table = $writer->getFormattedTableName($type);
		
		try {
			$SQL = "SELECT * FROM $table WHERE ".$SQL;
			
			$rows = $adapter->get($SQL, $values);
		}
		catch(RedBean_Exception_SQL $e) {
			if ($writer->sqlStateIn($e->getSQLState(),array(
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN,
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE
			))) {
				return array();
			}
			else {
				throw $e;
			}
		}
		
		
		return $redbean->convertToBeans($type, $rows);
	}
	/**
	 * Parses Gold SQL.
	 * Checks whether columns and tables prefixed with @ exists,
	 * if not they are being replaced by NULL leaving intact the
	 * rest of the query and making the SQL continue to work even
	 * if it's partially broken.
	 * 
	 * @param string $SQL				  sql code to execute
	 * @param string $currentTable	  name of the table
	 * @param RedBean_ToolBox $toolbox toolbox to use
	 *
	 * @return string $SQL resulting sql
	 */
	public static function parseGoldSQL( $SQL, $currentTable,  RedBean_ToolBox $toolbox ) {
		$writer = $toolbox->getWriter();
		
		$matches = array();
		
		$pattern = "/@[\w\.]+/";
		if (preg_match_all($pattern, $SQL, $matches)) {
			
			$columns = array_keys( $toolbox->getWriter()->getColumns($currentTable) );
			
			$tables = $writer->getTables();
			
			$checks = array_shift( $matches );
			
			foreach($checks as $checkItem) {
				$itemName = substr($checkItem, 1);
				
				if (strpos($itemName,".")!==false) {
					list($table, $column) = explode(".", $itemName);
					if (!in_array($table, $tables)) {
						$SQL = str_replace("@".$itemName, "NULL", $SQL);
					}
					else {
						$tableCols = array_keys( $toolbox->getWriter()->getColumns($table) );
						if (!in_array($column, ($tableCols))) {
							$SQL = str_replace("@".$itemName, "NULL", $SQL);
						}
						else {
							$SQL = str_replace("@".$itemName, $itemName, $SQL);
						}
					}
				}
				else {
					if (!in_array($itemName, ($columns))) {
						$SQL = str_replace("@".$itemName, "NULL", $SQL);
					}
					else {
						$SQL = str_replace("@".$itemName, $itemName, $SQL);
					}
				}
			}
		}
		return $SQL;
	}
}

/**
 * RedBean Bean Constraint
 * @file			RedBean/Plugin/Constraint.php
 * @description		Adds Cascaded Delete functionality for a pair of beans
 *
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_Plugin_Constraint {
	/**
	 *
	 * @var array
	 * Keeps track of foreign keys (only to improve fluid performance)
	 */
	private static $fkcache = array();
	private static $toolbox = null;
	public static function setToolBox( RedBean_ToolBox $toolbox ) {
		self::$toolbox = $toolbox;
	}
	/**
	 * Ensures that given an association between
	 * $bean1 and $bean2,
	 * if one of them gets trashed the association will be
	 * automatically removed.
	 *
	 * @param RedBean_OODBBean $bean1 bean
	 * @param RedBean_OODBBean $bean2 bean
	 *
	 * @return boolean $addedFKS whether we succeeded
	 */
	public static function addConstraint( RedBean_OODBBean $bean1, RedBean_OODBBean $bean2, $dontCache = false ) {
		
		if ((self::$toolbox)) { 
			$toolbox = self::$toolbox;
		}
		else {
			$toolbox = RedBean_Setup::getToolBox();
		}
		RedBean_CompatManager::scanDirect($toolbox, array(
				  RedBean_CompatManager::C_SYSTEM_MYSQL => "5",
				  RedBean_CompatManager::C_SYSTEM_SQLITE => "3",
				  RedBean_CompatManager::C_SYSTEM_POSTGRESQL => "7",));
		
		$association = new RedBean_AssociationManager( $toolbox );
		$writer = $toolbox->getWriter();
		$oodb = $toolbox->getRedBean();
		$adapter = $toolbox->getDatabaseAdapter();
		
		if ($oodb->isFrozen()) return false;
		$table1 = $bean1->getMeta("type");
		$table2 = $bean2->getMeta("type");
		$table = $association->getTable( array( $table1,$table2) );
		$idfield1 = $writer->getIDField($bean1->getMeta("type"));
		$idfield2 = $writer->getIDField($bean2->getMeta("type"));
		$bean = $oodb->dispense($table);
		$property1 = $bean1->getMeta("type") . "_id";
		$property2 = $bean2->getMeta("type") . "_id";
		if ($property1==$property2) $property2 = $bean2->getMeta("type")."2_id";
		$table = $adapter->escape($table);
		$table1 = $adapter->escape($table1);
		$table2 = $adapter->escape($table2);
		$property1 = $adapter->escape($property1);
		$property2 = $adapter->escape($property2);
		
		$fkCode = "fk".md5($table.$property1.$property2);
		if (isset(self::$fkcache[$fkCode])) return false;
		
		try {
			if ($writer instanceof RedBean_QueryWriter_PostgreSQL) {
				return self::constraintPostgreSQL($toolbox, $table, $table1, $table2, $property1, $property2, $dontCache);
			}
			if ($writer instanceof RedBean_QueryWriter_SQLite) {
				return self::constraintSQLite($toolbox, $table, $table1, $table2, $property1, $property2, $dontCache);
			}
			if ($writer instanceof RedBean_QueryWriter_MySQL) {
				return self::constraintMySQL($toolbox, $table, $table1, $table2, $property1, $property2, $dontCache);
			}
		}
		catch(RedBean_Exception_SQL $e) {
			if (!$writer->sqlStateIn($e->getSQLState(),
			array(
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN,
			RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
			)) throw $e;
		}
		return false;
	}
	/**
	 * Add the constraints for a specific database driver: PostgreSQL.
	 * @todo Too many arguments; find a way to solve this in a neater way.
	 *
	 * @param RedBean_ToolBox $toolbox   toolbox
	 * @param string			  $table     table
	 * @param string			  $table1    table1
	 * @param string			  $table2    table2
	 * @param string			  $property1 property1
	 * @param string			  $property2 property2
	 * @param boolean			  $dontCache want to have cache?
	 *
	 * @return boolean $succes whether the constraint has been applied
	 */
	private static function constraintPostgreSQL($toolbox, $table, $table1, $table2, $property1, $property2, $dontCache) {
		$writer = $toolbox->getWriter();
		$oodb = $toolbox->getRedBean();
		$adapter = $toolbox->getDatabaseAdapter();
		$fkCode = "fk".md5($table.$property1.$property2);
		$sql = "
					SELECT
							c.oid,
							n.nspname,
							c.relname,
							n2.nspname,
							c2.relname,
							cons.conname
					FROM pg_class c
					JOIN pg_namespace n ON n.oid = c.relnamespace
					LEFT OUTER JOIN pg_constraint cons ON cons.conrelid = c.oid
					LEFT OUTER JOIN pg_class c2 ON cons.confrelid = c2.oid
					LEFT OUTER JOIN pg_namespace n2 ON n2.oid = c2.relnamespace
					WHERE c.relkind = 'r'
					AND n.nspname IN ('public')
					AND (cons.contype = 'f' OR cons.contype IS NULL)
					AND
					(  cons.conname = '{$fkCode}a'	OR  cons.conname = '{$fkCode}b' )
				  ";
		$rows = $adapter->get( $sql );
		if (!count($rows)) {
			
			$table = $writer->getFormattedTableName($table);
			$table1 = $writer->getFormattedTableName($table1);
			$table2 = $writer->getFormattedTableName($table2);
			
			if (!$dontCache) self::$fkcache[ $fkCode ] = true;
			$sql1 = "ALTER TABLE $table ADD CONSTRAINT
					  {$fkCode}a FOREIGN KEY ($property1)
						REFERENCES $table1 (id) ON DELETE CASCADE ";
			$sql2 = "ALTER TABLE $table ADD CONSTRAINT
					  {$fkCode}b FOREIGN KEY ($property2)
						REFERENCES $table2 (id) ON DELETE CASCADE ";
			$adapter->exec($sql1);
			$adapter->exec($sql2);
		}
		return true;
	}
	/**
	 * Add the constraints for a specific database driver: MySQL.
	 * @todo Too many arguments; find a way to solve this in a neater way.
	 *
	 * @param RedBean_ToolBox $toolbox   toolbox
	 * @param string			  $table     table
	 * @param string			  $table1    table1
	 * @param string			  $table2    table2
	 * @param string			  $property1 property1
	 * @param string			  $property2 property2
	 * @param boolean			  $dontCache want to have cache?
	 *
	 * @return boolean $succes whether the constraint has been applied
	 */
	private static function constraintMySQL($toolbox, $table, $table1, $table2, $property1, $property2, $dontCache) {
		$writer = $toolbox->getWriter();
		$oodb = $toolbox->getRedBean();
		$adapter = $toolbox->getDatabaseAdapter();
		$db = $adapter->getCell("select database()");
		$fkCode = "fk".md5($table.$property1.$property2);
		$fks =  $adapter->getCell("
			SELECT count(*)
			FROM information_schema.KEY_COLUMN_USAGE
			WHERE TABLE_SCHEMA ='$db' AND TABLE_NAME ='".$writer->getFormattedTableName($table)."' AND
			CONSTRAINT_NAME <>'PRIMARY' AND REFERENCED_TABLE_NAME is not null
				  ");
		
		if ($fks>0) return false;
		
		if (!$dontCache) self::$fkcache[ $fkCode ] = true;
		$columns = $writer->getColumns($table);
		if ($writer->code($columns[$property1])!==RedBean_QueryWriter_MySQL::C_DATATYPE_UINT32) {
			$writer->widenColumn($table, $property1, RedBean_QueryWriter_MySQL::C_DATATYPE_UINT32);
		}
		if ($writer->code($columns[$property2])!==RedBean_QueryWriter_MySQL::C_DATATYPE_UINT32) {
			$writer->widenColumn($table, $property2, RedBean_QueryWriter_MySQL::C_DATATYPE_UINT32);
		}
		
		$table = $writer->getFormattedTableName($table);
		$table1 = $writer->getFormattedTableName($table1);
		$table2 = $writer->getFormattedTableName($table2);
			
		$sql = "
			ALTER TABLE ".$writer->noKW($table)."
			ADD FOREIGN KEY($property1) references $table1(id) ON DELETE CASCADE;
				  ";
		$adapter->exec( $sql );
		$sql ="
			ALTER TABLE ".$writer->noKW($table)."
			ADD FOREIGN KEY($property2) references $table2(id) ON DELETE CASCADE
				  ";
		$adapter->exec( $sql );
		return true;
	}
	/**
	 * Add the constraints for a specific database driver: SQLite.
	 * @todo Too many arguments; find a way to solve this in a neater way.
	 *
	 * @param RedBean_ToolBox $toolbox   toolbox
	 * @param string			  $table     table
	 * @param string			  $table1    table1
	 * @param string			  $table2    table2
	 * @param string			  $property1 property1
	 * @param string			  $property2 property2
	 * @param boolean			  $dontCache want to have cache?
	 *
	 * @return boolean $succes whether the constraint has been applied
	 */
	private static function constraintSQLite($toolbox, $table, $table1, $table2, $property1, $property2, $dontCache) {
		$writer = $toolbox->getWriter();
		$oodb = $toolbox->getRedBean();
		$adapter = $toolbox->getDatabaseAdapter();
		$fkCode = "fk".md5($table.$property1.$property2);
		
		$table = $writer->getFormattedTableName($table);
		$table1 = $writer->getFormattedTableName($table1);
		$table2 = $writer->getFormattedTableName($table2);
			
		
		$sql1 = "
			CREATE TRIGGER IF NOT EXISTS {$fkCode}a
				BEFORE DELETE ON $table1
				FOR EACH ROW BEGIN
					DELETE FROM $table WHERE  $table.$property1 = OLD.id;
				END;
				  ";
		$sql2 = "
			CREATE TRIGGER IF NOT EXISTS {$fkCode}b
				BEFORE DELETE ON $table2
				FOR EACH ROW BEGIN
					DELETE FROM $table WHERE $table.$property2 = OLD.id;
				END;
				  ";
		$adapter->exec($sql1);
		$adapter->exec($sql2);
		return true;
	}
}

/**
 * @name RedBean IBeanFormatter
 * @file RedBean/IBeanFormatter.php
 * @author Gabor de Mooij and the RedBean Team
 * @copyright Gabor de Mooij (c)
 * @license BSD
 *
 * The RedBean IBeanFormatter interface describes what methods
 * a BeanFormatter class should implement.
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
interface RedBean_IBeanFormatter {
	/**
	 *
	 * @param string $type type
	 */
	public function formatBeanTable( $type );
	/**
	 *
	 * @param string $type type
	 */
	public function formatBeanID( $type );
}
/*
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 * Interface definition of a Model Formatter for Fuse
 */
interface RedBean_IModelFormatter {
	/**
	 * ModelHelper will call this method of the class
	 * you provide to discover the model
	 *
	 * @param string $model
	 * 
	 * @return string $formattedModel
	 */
	public function formatModel( $model );
}

/**
 * @deprecated
 *
 * RedBean Domain Object
 * @file				RedBean/DomainObject.php
 * @description	This class serves as a source of inspiration and
 *						is an example how a layer super type pattern can be
 *						used with RedBean. This class has not been tested.
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 *
 */
abstract class RedBean_DomainObject {
	/**
	 *
	 * @var RedBean_ToolBox
	 */
	protected $tools;
	/**
	 *
	 * @var RedBean_OODB
	 */
	protected $redbean;
	/**
	 *
	 * @var RedBean_OODBBean
	 */
	protected $bean;
	/**
	 *
	 * @var RedBean_AssociationManager
	 */
	protected $associationManager;
	/**
	 *
	 * @var RedBean_TreeManager
	 */
	protected $treeManager;
	/**
	 *
	 * Constructor, requires a type name
	 * @param string $typeName typename
	 */
	public function __construct( $typeName = false ) {
		/**
		 * If no typeName has been specified,
		 * figure out the type of this model yourself.
		 * In this case the following rule applies:
		 * - the name of the model is the LAST part of the
		 * namespace.
		 * - Within that string, the name of the model is the LAST
		 * part of the poorman's name space.
		 *
		 * So the model name for class: /me/him/her is: her
		 * So the model name for class: /me/him/her_lover is: lover
		 */
		if (!$typeName) {
			
			$beanTypeName = get_class( $this );
			
			$a = explode( "\\" , $beanTypeName );
			$lastInNameSpace = array_pop( $a );
			
			$a = explode( "_" , $lastInNameSpace );
			$lastInPoormanNameSpace = array_pop( $a );
			$beanTypeName = $lastInPoormanNameSpace;
		}
		else {
			$beanTypeName = $typeName;
		}
		/*
		 * Now do a little check to see whether this name
		 * can be used. - Just a quick check, we will re-check later on
		*/
		if ($beanTypeName && strlen($beanTypeName)>0) {
			
			$this->tools = RedBean_Setup::getToolBox();
			$this->redbean = $this->tools->getRedBean();
			
			$this->bean = $this->redbean->dispense( strtolower( $beanTypeName ) );
			
			$this->associationManager = new RedBean_AssociationManager($this->tools);
			$this->treeManager = new RedBean_TreeManager($this->tools);
		}
		else {
			throw new Exception("Invalid Domain Object TypeName");
		}
	}
	/**
	 * Associates the bean inside with another OODBBean
	 *
	 * @param RedBean_DomainObject $other other
	 */
	protected function associate(RedBean_DomainObject $other) {
		$this->associationManager->associate($this->bean, $other->bean);
	}
	/**
	 * Breaks the association between this OODBBean an the one belonging
	 * to the other model.
	 *
	 * @param RedBean_DomainObject $other other
	 */
	protected function unassociate(RedBean_DomainObject $other) {
		$this->associationManager->unassociate($this->bean, $other->bean);
	}
	/**
	 * Fetches related domain objects.
	 *
	 * @param string $className      class name
	 * @param mixed  $constructorArg constructor arguments
	 * 
	 * @return mixed $models
	 */
	protected function related( $className, $constructorArg = null ) {
		$models = array();
		$model = new $className;
		$keys = $this->associationManager->related($this->bean, $model->getBeanType());
		foreach($keys as $key) {
			$modelItem = new $className($constructorArg);
			$modelItem->find( (int) $key );
			$models[$key] = $modelItem;
		}
		return $models;
	}
	/**
	 * Returns the type of the bean.
	 *
	 * @return string $type type
	 */
	protected function getBeanType() {
		return $this->bean->getMeta("type");
	}
	/**
	 * Clears associations
	 */
	protected function clearRelations( $type ) {
		$this->associationManager->clearRelations($this->bean, $type);
	}
	/**
	 * Attach
	 * 
	 * @param RedBean_DomainObject $other other
	 */
	protected function attach(RedBean_DomainObject $other) {
		$this->treeManager->attach($this->bean, $other->bean);
	}
	/**
	 * Loads the Bean internally
	 * 
	 * @param integer $id id
	 */
	public function find( $id ) {
		$this->bean = $this->redbean->load( $this->bean->getMeta("type"), (int) $id );
	}
	/**
	 * Saves the current domain object.
	 * The function saves the inner bean to the database.
	 */
	public function save() {
		$this->redbean->store( $this->bean );
	}
	/**
	 * Deletes the inner bean from the database.
	 */
	public function delete() {
		$this->redbean->trash( $this->bean );
	}
	/**
	 * Returns the ID of the Model.
	 */
	public function getID() {
		$idField = $this->tools->getWriter()->getIDField( $this->bean->getMeta("type") );
		return $this->bean->$idField;
	}
	/**
	 * Exports bean.
	 *
	 * @return array $array array
	 */
	public function export() {
		return $this->bean;
	}
	/**
	 * Exports beans.
	 *
	 * @return array $array array
	 */
	public static function exportAll( $objects ) {
		$beans = array();
		foreach($objects as $object) {
			$beans[] = $object->export();
		}
		return $beans;
	}
	/**
	 * Loads bean.
	 * 
	 * @param RedBean_OODBBean $bean bean to load
	 */
	public function loadBean( RedBean_OODBBean $bean ) {
		$this->bean = $bean;
	}
}

/**
 * Optimizer
 * @file 		RedBean/Optimizer.php
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_Plugin_Optimizer extends RedBean_CompatManager implements RedBean_Plugin,RedBean_Observer {
	/**
	 * Specify what database systems are supported by this class.
	 * @var array $databaseSpecs
	 */
	protected $supportedSystems = array(
			  RedBean_CompatManager::C_SYSTEM_MYSQL => "5"
	);
	/**
	 * @var RedBean_Adapter_DBAdapter
	 * Contains a reference to the database adapter.
	 */
	private $adapter;
	/**
	 * @var RedBean_OODB
	 * Contains a reference to the RedBean OODB object.
	 */
	private $oodb;
	/**
	 * @var RedBean_QueryWriter_MySQL
	 * Contains a reference to the query writer.
	 */
	private $writer;
	/**
	 * Contains an array filled with optimizers.
	 * @var RedBean_Plugin_IOptimizer $optimizers
	 */
	protected $optimizers = array();
	/**
	 * Constructor
	 * Handles the toolbox
	 * 
	 * @param RedBean_ToolBox $toolbox
	 */
	public function __construct( RedBean_ToolBox $toolbox ) {
		$this->scanToolBox( $toolbox );
		$this->oodb = $toolbox->getRedBean();
		$this->adapter = $toolbox->getDatabaseAdapter();
		$this->writer = $toolbox->getWriter();
	}
	/**
	 * Runs optimization Queue.
	 *
	 * @param string $table  table to optimize
	 * @param string $column column to optimize
	 * @param string $value  value to scan
	 *
	 */
	protected function optimize($table,$column,$value) {
		foreach($this->optimizers as $optimizer) {
			$optimizer->setTable($table);
			$optimizer->setColumn($column);
			$optimizer->setValue($value);
			if (!$optimizer->optimize()) break;
		}
	}
	/**
	 * Does an optimization cycle for each UPDATE event.
	 *
	 * @param string				$event event
	 * @param RedBean_OODBBean $bean	 bean
	 *
	 * @return void
	 */
	public function onEvent( $event , $bean ) {
		try {
			if ($event=="update") {
				
				$arr = $bean->export();
				
				unset($arr["id"]);
				
				if (count($arr)==0) return;
				
				$table = $this->adapter->escape($bean->getMeta("type"));
				
				$columns = array_keys($arr);
				
				$column = $this->adapter->escape($columns[ array_rand($columns) ]);
				
				$value = $arr[$column];
				$this->optimize($table,$column,$value);
			}
		}catch(RedBean_Exception_SQL $e) {
			
			
		}
	}
	
	/**
	 * Adds an optimizer to the optimizer collection.
	 *
	 * @param RedBean_Plugin_IOptimizer $optimizer
	 */
	public function addOptimizer(RedBean_Plugin_IOptimizer $optimizer) {
		$this->optimizers[] = $optimizer;
	}
}
/**
 * RedBean BeanMachine
 *
 * @file			RedBean/BeanMachine.php
 * @description		Query Building System for Bean Machinery
 * @author			Gabor de Mooij
 * @license			BSD
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_Plugin_BeanMachine implements RedBean_Plugin {
	/**
	 * @var RedBean_Plugin_BeanMachine_Group
	 */
	protected $groups = null;
	/**
	 * @var RedBean_Plugin_BeanMachine_Group
	 */
	protected $selected = null;
	/**
	 * @var array
	 */
	protected $parameters = array();
	/**
	 * @var array
	 */
	protected $bookmarks = array();
	    
	/**
	 * 
	 * Toolbox
	 * @var RedBean_ToolBox
	 */
	protected $toolbox = null;
	/**
	 * Initializes the Bean Machine
	 * @return void
	 */
	private static function init() {
		if (!class_exists("RedBean_Plugin_BeanMachine_Group")) {
			
			
			RedBean_Plugin_BeanMachine_InnerClasses();
		}
	}
	/**
	 * Private - use getInstance() instead, NOT a SINGLETON.
	 * Constructor bootstraps its own classes.
	 *
	 * @param RedBean_ToolBox $toolbox toolbox
	 * 
	 * @return void
	 */
	private function __construct(RedBean_ToolBox $toolbox) {
		$this->groups = new RedBean_Plugin_BeanMachine_Group;
		$this->groups->setTemplate("","");
		$this->groups->setGlue(" \n ");
		$this->selected = $this->groups;
		$this->root = $this->groups;
		$this->toolbox = $toolbox;
	}
	/**
	 * Gets an instance of the BeanMachine.
	 *
	 * @param RedBean_ToolBox $toolbox toolbox
	 * 
	 * @return RedBean_Plugin_BeanMachine $machine the Bean Machine.
	 */
	public static function getInstance( RedBean_ToolBox $toolbox ) {
		
		self::init();
		$inst = new self( $toolbox );
		return $inst;
	}
	/**
	 * Binds a value to a key.
	 * 
	 * @throws Exception
	 *
	 * @param  string $key
	 * @param  mixed $value
	 *
	 * @return void
	 */
	public function bind( $key, $value ) {
		if (isset($this->parameters[$key])) {
			throw new Exception("Parameter set already!");
		}
		$this->parameters[$key] = $Value;
	}
	/**
	 * Finds the group in the Query and selects it, opens it.
	 *
	 * Usage:
	 * 	$q = RedBean_Plugin_BeanMachine::getInstance();
	 *  $q->addGroup("SELECT-CLAUSE", " SELECT @ ", ",");
	 * 	... do all kind of stuff...
	 *  $q->openGroup("SELECT-CLAUSE");
	 *
	 *
	 * @throws Exception
	 * @param  $key
	 * @return RedBean_Plugin_BeanMachine
	 */
	public function openGroup( $key ) {
		if (isset($this->bookmarks[$key])) {
			$this->selected = $this->bookmarks[$key];
			return $this;
		}
		throw new Exception("No Such Group");
		
	}
	/**
	 * Adds a new Group to the Query.
	 * Usage:
	 *
	 * 
	 * $q->addGroup("WHERE-CLAUSE", " WHERE @ ", " AND ");
	 * $q->add(" color = :color ");
	 * $q->add(" smell = :smell ");
	 * (Outputs: WHERE color = :color AND smell = :smell )
	 *
	 * 
	 * $q->openGroup("WHERE-CLAUSE");
	 * $q->addGroup("ROSES", " (@) ", " OR ");
	 * $q->add(" title = 'roses' ");
	 * $q->add(" description = 'roses' ");
	 *
	 *
	 * @param  string $key ID to assign to this part of the Query
	 * @param  string $template Template to use for this part of the Query, '@' is placeholder for SQL
	 * @param  string $glue string to use to glue together SQL parts in group
	 *
	 * @return RedBean_Plugin_BeanMachine $bm Chainable
	 */
	public function addGroup( $key, $template, $glue ) {
		$this->bookmarks[$key]= $this->selected->addGroup( $key );
		$this->latest = $key;
		$this->bookmarks[$key]->setGlue($glue);
		$templateSnippets = explode("@", $template);
		$this->bookmarks[$key]->setTemplate($templateSnippets[0], $templateSnippets[1]);
		return $this;
	}
	public function open() {
		return $this->openGroup($this->latest);
	}
	/**
	 * Resets, re-selects the root group of the query.
	 * @return RedBean_Plugin_BeanMachine $bm Chainable
	 */
	public function reset() {
		$this->selected = $this->root;
		return $this;
	}
	/**
	 * Adds a statement to the current part of the query.
	 * 
	 * @throws Exception
	 *
	 * @param  string $statement statement to add
	 *
	 * @return RedBean_Plugin_BeanMachine $bm Chainable
	 */
	public function add( $statement ) {
		if ($this->selected instanceof RedBean_Plugin_BeanMachine_Group) {
			$this->selected->add( $statement );
		}
		else {
			throw new Exception("No Group has been opened. Please open a group first.");
		}
		return $this;
	}
	/**
	 * Builds the Query, returns the string.
	 *
	 * @return string $querySQL SQL code
	 */
	public function __toString() {
		return (string) $this->groups;
	}
	/**
	 * 
	 * Fetches a BeanMachine Plugin from the BeanMachine folder.
	 * 
	 * @param string $name name ID of the BeanMachine plugin
	 */
	public function getQueryByName( $name ) {
		
		$className = "RedBean_Plugin_BeanMachine_".$name;
		if (class_exists($className)) {
			$inst = self::getInstance( $this->toolbox );
			$beanMachineUser = new $className( $inst );
			return $beanMachineUser;	
		}
		else {
			throw new RedBean_Exception("Could not find BeanMachine $name ", 0);
		}
	}
	
	/**
	 * 
	 * Produces the requested beans
	 * 
	 * 
	 */
	public function getBeans($type, $machinery) {
		$rows = $this->toolbox->getDatabaseAdapter()->get( $machinery );
		$beanCollection = array();
		foreach($rows as $row) {
			$bean = $this->toolbox->getRedbean()->dispense($type);
			foreach($row as $property=>$value) {
				if (strpos($property,"_")===0) {
					
					$bean->setMeta($property, $value);
				}
				else {
					$bean->$property = $value;
				}
			}
			$beanCollection[] = $bean;
		}
		return $beanCollection;
	}
	
	/**
	 * 
	 * Convenience function for bean machine plugins to get hold
	 * of the toolbox.
	 * 
	 * @return RedBean_ToolBox $toolbox toolbox
	 */
	public function getToolBox() {
		return $this->toolbox;
	}
}

function RedBean_Plugin_BeanMachine_InnerClasses() {
	class RedBean_Plugin_BeanMachine_Group {
			private $parent = null;
			private $glueChar = ",";
			private $before = " ( ";
			private $after = " ) ";
			private $statements = array();
			public function __construct( $parent = null ) {
				$this->parent = $parent;
			}
			public function getParent() {
				if ($this->parent) return $this->parent; else return $this;
			}
			public function setGlue( $glueChar ) {
				$this->glueChar = $glueChar;
			}
			public function add( $statement = "" ) {
				$this->statements[] = $statement;
			}
			public function setTemplate($before, $after) {
				$this->before = $before;
				$this->after = $after;
			}
			public function __toString() {
				$gluedStatements = implode($this->glueChar, $this->statements);
				return (string) $this->before . $gluedStatements . $this->after;
			}
			public function addGroup($key) {
				$g = new self($this);
				$this->statements[] = $g;
				return $g;
			}
		}
		return new RedBean_Plugin_BeanMachine_Group;
}
/**
 * RedBean IOptimizer
 * @file				RedBean/Plugin/IOptimizer.php
 * @description	Describes the interface of an optimizer.
 *
 * @author			Gabor de Mooij
 * @license			BSD
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
interface RedBean_Plugin_IOptimizer {
	/**
	 * Each optimizer plugin should have a means to set basic
	 * information; table, column and value.
	 *
	 * @param string $table table
	 */
	public function setTable($table);
	/**
	 * Sets the column.
	 *
	 * @param string $column column
	 */
	public function setColumn($column);
	
	/**
	 * Sets the value.
	 *
	 * @param string $value value
	 */
	public function setValue($value);
	/**
	 * Called by the optimizer. This asks the plugin to optimize
	 * the table based on column and value information provided.
	 * If the optimize() method returns false, no further optimizations
	 * are allowed. In case of true the optimizer will advance to the next
	 * optimizer in the collection.
	 *
	 * @return boolean $yesNo further optimization allowed
	 */
	public function optimize();
}
/**
 * RedBean Optimizer Shrink
 * @file				RedBean/Plugin/Optimizer/Shrink.php
 * @description	An Optimizer Plugin for RedBean.
 *						This optimizer tries to narrow columns on the fly.
 *						If the values in a column can be stored in a smaller
 *						column type this plugin will try to adjust the column to the
 *						smaller type.
 *
 * @author			Gabor de Mooij
 * @license			BSD
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_Plugin_Optimizer_Shrink extends RedBean_CompatManager implements RedBean_Plugin_IOptimizer {
	/**
	 * An optimizer takes three arguments; a table, column and value.
	 * The table is the table that is being used for an update event at the moment,
	 * the Object Database will inform you about this because it might be an
	 * opportunity to perform optimization.
	 * Table to optimize.
	 *
	 * @var string $table name of the table to optimize
	 */
	protected $table;
	/**
	 * An optimizer takes three arguments; a table, column and value.
	 * The column is the column currently being updated, the Object Database
	 * will inform you about this because it might be an
	 * opportunity to perform optimization.
	 * Column to optimize.
	 *
	 * @var string $column column name
	 */
	protected $column;
	/**
	 * An optimizer takes three arguments; a table, column and value.
	 * The value is the piece of data that is being inserted in the column
	 * at this moment. The job of the optimizer is to check whether the column
	 * could be optimized based on the current contents and the value currently
	 * being inserted.
	 *
	 * @var string $value Value currently inserted in the column
	 */
	protected $value;
	/**
	 * Toolbox, contains everyting required for this instance to
	 * perform database operations within the RedBean framework.
	 *
	 * @var RedBean_Toolbox $toolbox a toolbox
	 */
	protected $toolbox;
	/**
	 * This is a convenience property so you don't have to
	 * ask the toolbox for this object every time you need it.
	 *
	 * @var RedBean_QueryWriter $writer query writer
	 */
	protected $writer;
	/**
	 * This is a convenience property so you don't have to
	 * ask the toolbox for this object every time you need it.
	 *
	 * @var RedBean_DatabaseAdapter $adapter database adapter
	 */
	protected $adapter;
	/**
	 * Describes to RedBean what kind of systems are supported.
	 * Associative array: keys are database brands, values are
	 * integer version numbers.
	 *
	 * @var array $collection Collection of Supported Systems and Version.
	 */
	protected $supportedSystems = array(
		RedBean_CompatManager::C_SYSTEM_MYSQL => "5"
	);
	/**
	 * Constructor.
	 * This Object requires a toolbox.
	 *
	 * @param RedBean_ToolBox $toolbox toolbox for DB operations.
	 */
	public function __construct( RedBean_ToolBox $toolbox ) {
		$this->scanToolBox($toolbox);
		$this->writer = $toolbox->getWriter();
		$this->adapter = $toolbox->getDatabaseAdapter();
	}
	/**
	 * An optimizer takes three arguments; a table, column and value.
	 * The table is the table that is being used for an update event at the moment,
	 * the Object Database will inform you about this because it might be an
	 * opportunity to perform optimization.
	 * Table to optimize.
	 *
	 * @param string $table name of the table to optimize
	 */
	public function setTable( $table ) {
		$this->table = $table;
	}
	/**
	 * An optimizer takes three arguments; a table, column and value.
	 * The column is the column currently being updated, the Object Database
	 * will inform you about this because it might be an
	 * opportunity to perform optimization.
	 * Column to optimize.
	 *
	 * @param string $column column name
	 */
	public function setColumn( $column ) {
		$this->column = $column;
	}
	/**
	 * An optimizer takes three arguments; a table, column and value.
	 * The value is the piece of data that is being inserted in the column
	 * at this moment. The job of the optimizer is to check whether the column
	 * could be optimized based on the current contents and the value currently
	 * being inserted.
	 *
	 * @param string $value Value currently inserted in the column
	 */
	public function setValue( $value ) {
		$this->value = $value;
	}
	/**
	 * Performs the actual optimization. In this case the optimizer looks
	 * at the size of the column and the size of the value. If the value size is
	 * smaller than the column size it tries to convert the column to a smaller
	 * size. Next, it counts if there is any different between the smaller column
	 * and the original column. If no differences are found the original column
	 * gets replaced.
	 * Like the other optimizers, this optimizer returns TRUE if it thinks
	 * further optimizations can happen, FALSE otherwise.
	 *
	 * @return boolean $yesNo advance to next optimizer
	 */
	public function optimize() {
		
		$type = $this->writer->scanType($this->value);
		
		$fields = $this->writer->getColumns($this->table);
		
		if (!in_array($this->column,array_keys($fields))) return false;
		
		$typeInField = $this->writer->code($fields[$this->column]);
		
		if ($type < $typeInField) {
			try {
				@$this->adapter->exec("alter table ".$this->writer->noKW($this->table)." drop __test");
			}catch(Exception $e) {}
			
			$type = $this->writer->typeno_sqltype[$type];
			
			@$this->adapter->exec("alter table ".$this->writer->noKW($this->table)." add __test ".$type);
			
			@$this->adapter->exec("update ".$this->writer->noKW($this->table)." set __test=".$this->writer->noKW($this->column)."");
			$rows = $this->adapter->get("select ".$this->writer->noKW($this->column)." as a, __test as b from ".$this->writer->noKW($this->table));
			$diff = 0;
			foreach($rows as $row) {
				$diff += ($row["a"]!=$row["b"]);
			}
			if (!$diff) {
				
				@$this->adapter->exec("alter table ".$this->writer->noKW($this->table)." change ".$this->writer->noKW($this->column)." ".$this->writer->noKW($this->column)." ".$type);
			}
			
			@$this->adapter->exec("alter table ".$this->writer->noKW($this->table)." drop __test");
		}
		return false;
	}
}
/**
 * RedBean Optimizer DateTime
 * @file				RedBean/Plugin/Optimizer/DateTime.php
 * @description	An Optimizer Plugin for RedBean.
 *						Tries to convert columns to MySQL datetime
 *						if possible.
 *
 * @author			Gabor de Mooij
 * @license			BSD
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_Plugin_Optimizer_Datetime extends RedBean_CompatManager implements RedBean_Plugin_IOptimizer {
	/**
	 * An optimizer takes three arguments; a table, column and value.
	 * The table is the table that is being used for an update event at the moment,
	 * the Object Database will inform you about this because it might be an
	 * opportunity to perform optimization.
	 * Table to optimize.
	 *
	 * @var string $table name of the table to optimize
	 */
	protected $table;
	/**
	 * An optimizer takes three arguments; a table, column and value.
	 * The column is the column currently being updated, the Object Database
	 * will inform you about this because it might be an
	 * opportunity to perform optimization.
	 * Column to optimize.
	 *
	 * @var string $column column name
	 */
	protected $column;
	/**
	 * An optimizer takes three arguments; a table, column and value.
	 * The value is the piece of data that is being inserted in the column
	 * at this moment. The job of the optimizer is to check whether the column
	 * could be optimized based on the current contents and the value currently
	 * being inserted.
	 *
	 * @var string $value Value currently inserted in the column
	 */
	protected $value;
	/**
	 * Toolbox, contains everyting required for this instance to
	 * perform database operations within the RedBean framework.
	 * 
	 * @var RedBean_Toolbox $toolbox a toolbox
	 */
	protected $toolbox;
	/**
	 * This is a convenience property so you don't have to
	 * ask the toolbox for this object every time you need it.
	 *
	 * @var RedBean_QueryWriter $writer query writer
	 */
	protected $writer;
	/**
	 * This is a convenience property so you don't have to
	 * ask the toolbox for this object every time you need it.
	 *
	 * @var RedBean_DatabaseAdapter $adapter database adapter
	 */
	protected $adapter;
	/**
	 * Describes to RedBean what kind of systems are supported.
	 * Associative array: keys are database brands, values are
	 * integer version numbers.
	 *
	 * @var array $collection Collection of Supported Systems and Version.
	 */
	protected $supportedSystems = array(
		RedBean_CompatManager::C_SYSTEM_MYSQL => "5"
	);
	/**
	 * Constructor.
	 * This Object requires a toolbox.
	 *
	 * @param RedBean_ToolBox $toolbox toolbox for DB operations.
	 */
	public function __construct( RedBean_ToolBox $toolbox ) {
		$this->scanToolBox($toolbox);
		$this->writer = $toolbox->getWriter();
		$this->adapter = $toolbox->getDatabaseAdapter();
	}
	/**
	 * An optimizer takes three arguments; a table, column and value.
	 * The table is the table that is being used for an update event at the moment,
	 * the Object Database will inform you about this because it might be an
	 * opportunity to perform optimization.
	 * Table to optimize.
	 *
	 * @param string $table name of the table to optimize
	 */
	public function setTable( $table ) {
		$this->table = $table;
	}
	/**
	 * An optimizer takes three arguments; a table, column and value.
	 * The column is the column currently being updated, the Object Database
	 * will inform you about this because it might be an
	 * opportunity to perform optimization.
	 * Column to optimize.
	 *
	 * @param string $column column name
	 */
	public function setColumn( $column ) {
		$this->column = $column;
	}
	/**
	 * An optimizer takes three arguments; a table, column and value.
	 * The value is the piece of data that is being inserted in the column
	 * at this moment. The job of the optimizer is to check whether the column
	 * could be optimized based on the current contents and the value currently
	 * being inserted.
	 *
	 * @param string $value Value currently inserted in the column
	 */
	public function setValue( $value ) {
		$this->value = $value;
	}
	/**
	 * Performs the actual optimization. In this case the optimizer first
	 * scans the value. If the value if of type 'datetime' and the column
	 * is not it tries to make the column datetime. If the column is 'datetime'
	 * and the value 'datetime' it blocks further optimization. If the value
	 * is NOT 'datetime' then it immediately returns true, thus allowing further
	 * optimization.
	 *
	 * @return boolean $yesNo advance to next optimizer
	 */
	public function optimize() {
		if (!$this->matchesDateTime($this->value)) return true;
		
		$type = $this->writer->scanType($this->value);
		
		$fields = $this->writer->getColumns($this->table);
		
		if (!in_array($this->column,array_keys($fields))) return false;
		
		$typeInField = $this->writer->code($fields[$this->column]);
		
		if ($typeInField!="datetime") {
			if ($this->matchesDateTime($this->value)) {
				
				$cnt = (int) $this->adapter->getCell("select count(*) as n from {$this->table} where
						  {$this->column} regexp '[0-9]{4}-[0-1][0-9]-[0-3][0-9] [0-2][0-9]:[0-5][0-9]:[0-5][0-9]'
						  OR {$this->column} IS NULL");
				$total = (int) $this->adapter->getCell("SELECT count(*) FROM ".$this->writer->noKW($this->table));
				
				if ($total===$cnt) { 
					$this->adapter->exec("ALTER TABLE ".$this->writer->noKW($this->table)." change ".$this->writer->noKW($this->column)." ".$this->writer->noKW($this->column)." datetime ");
				}
				
				return false;
			}
			
			return true;
		}
		else {
			
			return false; 
		}
	}
	/**
	 * MatchesDateTime matches a value to determine whether it matches the
	 * MySQL datetime type.
	 *
	 * @param string $value Value to match
	 *
	 * @return boolean $yesNo Whether it is a datetime value
	 */
	public function matchesDateTime($value) {
		$pattern = "/^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9]) (?:([0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?$/";
		return (boolean) (preg_match($pattern, $value));
	}
	
}
/**
 * Created by PhpStorm.
 * User: prive
 * Date: 3-feb-2011
 * Time: 20:31:20
 * To change this template use File | Settings | File Templates.
 */
 
class RedBean_Plugin_QueryLogger implements RedBean_Plugin,RedBean_Observer {
	protected $logs = array();
	public static function getInstanceAndAttach( RedBean_Observable $adapter ) {
		$queryLog = new RedBean_Plugin_QueryLogger;
		$adapter->addEventListener( "sql_exec", $queryLog );
		return $queryLog;
	}
	private function __construct(){
	}
	public function onEvent( $eventName, $adapter ) {
		if ($eventName=="sql_exec") {
			$sql = $adapter->getSQL();
			$this->logs[] = $sql;
			
		}
	}
	public function grep( $word ) {
		$found = array();
		foreach($this->logs as $log) {
			if (strpos($log,$word)!==false) {
				$found[] = $log;
			}
		}
		return $found;
	}
	public function getLogs() {
		return $this->logs;
	}
}

/**
 * RedBean NullWriter
 * @file				RedBean/QueryWriter/NullWriter.php
 * @description	Represents a NULL Database to RedBean
 *						This class simply registers all actions invoked.
 *						It can be used for so-called white box testing, to see
 *						if your algorithms active the right methods with proper
 *						arguments.
 *
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_QueryWriter_NullWriter extends RedBean_QueryWriter_AQueryWriter implements RedBean_QueryWriter {
	/**
	 * @var integer
	 *
	 * DATA TYPE
	 * Boolean Data type
	 * 
	 */
	const C_DATATYPE_BOOL = 0;
	/**
	 * @var integer
	 *
	 * DATA TYPE
	 * Unsigned 8BIT Integer
	 * 
	 */
	const C_DATATYPE_UINT8 = 1;
	/**
	 * @var integer
	 *
	 * DATA TYPE
	 * Unsigned 32BIT Integer
	 * 
	 */
	const C_DATATYPE_UINT32 = 2;
	/**
	 * @var integer
	 *
	 * DATA TYPE
	 * Double precision floating point number and
	 * negative numbers.
	 * 
	 */
	const C_DATATYPE_DOUBLE = 3;
	/**
	 * @var integer
	 *
	 * DATA TYPE
	 * Standard Text column (like varchar255)
	 * At least 8BIT character support.
	 * 
	 */
	const C_DATATYPE_TEXT8 = 4;
	/**
	 * @var integer
	 *
	 * DATA TYPE
	 * Long text column (16BIT)
	 * 
	 */
	const C_DATATYPE_TEXT16 = 5;
	/**
	 * @var integer
	 *
	 * DATA TYPE
	 * 32BIT long textfield (number of characters can be as high as 32BIT) Data type
	 * This is the biggest column that RedBean supports. If possible you may write
	 * an implementation that stores even bigger values.
	 * 
	 */
	const C_DATATYPE_TEXT32 = 6;
	/**
	 * @var integer
	 *
	 * DATA TYPE
	 * Specified. This means the developer or DBA
	 * has altered the column to a different type not
	 * recognized by RedBean. This high number makes sure
	 * it will not be converted back to another type by accident.
	 * 
	 */
	const C_DATATYPE_SPECIFIED = 99;
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 *
	 * @var mixed
	 */
	public $createTableArgument = NULL;
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 *
	 * @var mixed
	 */
	public $getColumnsArgument = NULL;
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 *
	 * @var mixed
	 */
	public $scanTypeArgument = NULL;
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 *
	 * @var mixed
	 */
	public $addColumnArguments = array();
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 *
	 * @var mixed
	 */
	public $codeArgument = NULL;
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 *
	 * @var mixed
	 */
	public $widenColumnArguments = array();
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 *
	 * @var mixed
	 */
	public $updateRecordArguments = array();
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 * 
	 * @var mixed
	 */
	public $insertRecordArguments = array();
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 * 
	 * @var mixed
	 */
	public $selectRecordArguments = array();
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 * 
	 * @var mixed
	 */
	public $deleteRecordArguments = array();
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 * 
	 * @var mixed
	 */
	public $checkChangesArguments = array();
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 * 
	 * @var mixed
	 */
	public $addUniqueIndexArguments = array();
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 *
	 * @var mixed
	 */
	public $selectByCritArguments = array();
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 * 
	 * @var mixed
	 */
	public $deleteByCrit = array();
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 *
	 * @var mixed
	 */
	public $returnTables = array();
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 *
	 * @var mixed
	 */
	public $returnGetColumns = array();
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 *
	 * @var mixed
	 */
	public $returnScanType = 1;
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 *
	 * @var mixed
	 */
	public $returnAddColumn = NULL;
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 *
	 * @var mixed
	 */
	public $returnCode = NULL;
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 *
	 * @var mixed
	 */
	public $returnWidenColumn = NULL;
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 *
	 * @var mixed
	 */
	public $returnUpdateRecord = NULL;
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 * 
	 * @var mixed
	 */
	public $returnInsertRecord = NULL;
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 *
	 * @var mixed
	 */
	public $returnSelectRecord = NULL;
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 *
	 * @var mixed
	 */
	public $returnDeleteRecord = NULL;
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 *
	 * @var mixed
	 */
	public $returnCheckChanges = NULL;
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 *
	 * @var mixed
	 */
	public $returnDeleteByCrit = NULL;
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 *
	 * @var mixed
	 */
	public$returnSelectByCrit = NULL;
	/**
	 * Part of test system. This property captures interactions
	 * between this object and the one that is being tested.
	 * Used for scanning behavior of objects that use query writers.
	 *
	 * @var mixed
	 */
	public $returnAddUniqueIndex = NULL;
	/**
	 * For testing purposes only. Returnes a predefined
	 * value.
	 *
	 * @return mixed
	 */
	public function getTables() {
		return $this->returnTables;
	}
	/**
	 * For testing purposes only. Returnes a predefined
	 * value.
	 *
	 * @return mixed
	 */
	public function createTable( $table ) {
		$this->createTableArgument = $table;
	}
	/**
	 * For testing purposes only. Returnes a predefined
	 * value.
	 *
	 * @return mixed
	 */
	public function getColumns( $table ) {
		$this->getColumnsArgument = $table;
		return $this->returnGetColumns;
	}
	/**
	 * For testing purposes only. Returnes a predefined
	 * value.
	 *
	 * @return mixed
	 */
	public function scanType( $value ) {
		$this->scanTypeArgument = $value;
		return $this->returnScanType;
	}
	/**
	 * For testing purposes only. Returnes a predefined
	 * value.
	 *
	 * @return mixed
	 */
	public function addColumn( $table, $column, $type ) {
		$this->addColumnArguments = array( $table, $column, $type );
		return $this->returnAddColumn;
	}
	/**
	 * For testing purposes only. Returnes a predefined
	 * value.
	 *
	 * @return mixed
	 */
	public function code( $typedescription ) {
		$this->codeArgument = $typedescription;
		return $this->returnCode;
	}
	/**
	 * For testing purposes only. Returnes a predefined
	 * value.
	 *
	 * @return mixed
	 */
	public function widenColumn( $table, $column, $type ) {
		$this->widenColumnArguments = array($table, $column, $type);
		return $this->returnWidenColumn;
	}
	/**
	 * For testing purposes only. Returnes a predefined
	 * value.
	 *
	 * @return mixed
	 */
	public function updateRecord( $table, $updatevalues, $id) {
		$this->updateRecordArguments = array($table, $updatevalues, $id);
		return $this->returnUpdateRecord;
	}
	/**
	 * For testing purposes only. Returnes a predefined
	 * value.
	 *
	 * @return mixed
	 */
	public function insertRecord( $table, $insertcolumns, $insertvalues ) {
		$this->insertRecordArguments = array( $table, $insertcolumns, $insertvalues );
		return $this->returnInsertRecord;
	}
	/**
	 * For testing purposes only. Returnes a predefined
	 * value.
	 *
	 * @return mixed
	 */
	public function selectRecord($type, $ids) {
		$this->selectRecordArguments = array($type, $ids);
		return $this->returnSelectRecord;
	}
	/**
	 * For testing purposes only. Returnes a predefined
	 * value.
	 *
	 * @return mixed
	 */
	public function deleteRecord( $table, $id) {
		$this->deleteRecordArguments = array($table, "id", $id);
		return $this->returnDeleteRecord;
	}
	/**
	 * For testing purposes only. Returnes a predefined
	 * value.
	 *
	 * @return mixed
	 */
	public function checkChanges($type, $id, $logid) {
		$this->checkChangesArguments = array($type, $id, $logid);
		return $this->returnCheckChanges;
	}
	/**
	 * For testing purposes only. Returnes a predefined
	 * value.
	 *
	 * @return mixed
	 */
	public function addUniqueIndex( $table,$columns ) {
		$this->addUniqueIndexArguments=array($table,$columns);
		return $this->returnAddUniqueIndex;
	}
	/**
	 * For testing purposes only. Returnes a predefined
	 * value.
	 *
	 * @return mixed
	 */
	public function selectByCrit( $select, $table, $column, $value, $withUnion=false, $sqlSnippet = false ) {
		$this->selectByCritArguments=array($select, $table, $column, $value, $withUnion);
		return $this->returnSelectByCrit;
	}
	/**
	 * For testing purposes only. Returnes a predefined
	 * value.
	 *
	 * @return mixed
	 */
	public function deleteByCrit( $table, $crits ) {
		$this->deleteByCrit=array($table, $crits );
		return $this->returnDeleteByCrit;
	}
	/**
	 * For testing purposes only. Returnes a predefined
	 * value.
	 *
	 * @return mixed
	 */
	public function getIDField( $type, $safe = null ) {
		return "id";
	}
	/**
	 * For testing purposes only. Returnes a predefined
	 * value.
	 *
	 * @return mixed
	 */
	public function noKW($str) {
		return $str;
	}
	/**
	 * For testing purposes only. Returnes a predefined
	 * value.
	 *
	 * @return mixed
	 */
	public function sqlStateIn($state,$list) {
		return true;
	}
	/**
	 * Resets the mock object. All public
	 * properties will be assigned values like NULL or an empty
	 * array.
	 */
	public function reset() {
		$this->createTableArgument = NULL;
		$this->getColumnsArgument = NULL;
		$this->scanTypeArgument = NULL;
		$this->addColumnArguments = array();
		$this->codeArgument = NULL;
		$this->widenColumnArguments = array();
		$this->updateRecordArguments = array();
		$this->insertRecordArguments = array();
		$this->selectRecordArguments = array();
		$this->deleteRecordArguments = array();
		$this->checkChangesArguments = array();
		$this->addUniqueIndexArguments = array();
		$this->returnTables = array();
		$this->returnGetColumns = array();
		$this->returnScanType = 1;
		$this->returnAddColumn = NULL;
		$this->returnCode = NULL;
		$this->returnWidenColumn = NULL;
		$this->returnUpdateRecord = NULL;
		$this->returnInsertRecord = NULL;
		$this->returnSelectRecord = NULL;
		$this->returnDeleteRecord = NULL;
		$this->returnCheckChanges = NULL;
		$this->returnAddUniqueIndex = NULL;
	}
	
}
/**
 * RedBean Domain Object
 * @file			RedBean/UnitOfWork.php
 * @description		This is an extra convenience class
 *					that implements my own version of the
 *					well known unit of work pattern using PHP 5.3 closures.
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 *
 */
class RedBean_UnitOfWork {
	/**
	 * Associative multi dimensional array
	 * containing all the tasks and their tags.
	 * @var array
	 */
	private $todoList = array();
	/**
	 * Adds a piece of work to the list.
	 * @param string $tagName
	 * @param closure $closure
	 */
	public function addWork( $tagName, $closure ) {
		if (strlen($tagName)>0) {
			if (!isset($this->todoList[$tagName])) {
				
				$this->todoList[$tagName]=array();
			}
			$this->todoList[$tagName][] = $closure;
		}
	}
	/**
	 * Executes a piece of work (job) identified by the
	 * tagname argument.
	 * @param string $tagName
	 */
	public function doWork( $tagName ) {
		if (isset($this->todoList[$tagName])) {
			foreach($this->todoList[$tagName] as $job) {
				$job();
			}
		}
	}
}

/**
 * SimpleModel
 * @file 		RedBean/SimpleModel.php
 * @description		Part of FUSE
 * @author              Gabor de Mooij
 * @license		BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_SimpleModel {
	/**
	 * Contains the inner bean.
	 * @var RedBean_OODBBean
	 */
	protected $bean;
	/**
	 * Used by FUSE: the ModelHelper class to connect a bean to a model.
	 * This method loads a bean in the model.
	 * @param RedBean_OODBBean $bean
	 */
	public function loadBean( RedBean_OODBBean $bean ) {
		$this->bean = $bean;
	}
	/**
	 * Magic Getter to make the bean properties available from
	 * the $this-scope.
	 * @param string $prop
	 * @return mixed $propertyValue
	 */
	public function __get( $prop ) {
		return $this->bean->$prop;
	}
	/**
	 * Magic Setter
	 * @param string $prop
	 * @param mixed $value
	 */
	public function __set( $prop, $value ) {
		$this->bean->$prop = $value;
	}
	protected function __hasProperties( $list ) {
		$missing = array();
		$properties = explode(",", $list);
		foreach($properties as $property) {
			if (empty($this->bean->$property)) {
				$missing[] = $property;
			}
		}
		return $missing;
	}
	public function __isset($key) {
		return (isset($this->bean->$key));
	}
}
/*
 * ModelHelper
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 * Interface definition of a Model Formatter for Fuse
 */
class RedBean_ModelHelper implements RedBean_Observer {
	/**
	 * Holds a model formatter
	 * @var RedBean_IModelFormatter
	 */
	private static $modelFormatter;
	/**
	 * Connects OODB to a model if a model exists for that
	 * type of bean. This connector is used in the facade.
	 *
	 * @param string $eventName
	 * @param RedBean_OODBBean $bean
	 */
	public function onEvent( $eventName, $bean ) {
		$bean->$eventName();
	}
	/**
	 * Given a model ID (model identifier) this method returns the
	 * full model name.
	 *
	 * @param string $model
	 * @return string $fullname
	 */
	public static function getModelName( $model ) {
		if (self::$modelFormatter){
			return self::$modelFormatter->formatModel($model);
		}
		else {
			return "Model_".ucfirst($model);
		}
	}
	/**
	 * Sets the model formatter to be used to discover a model
	 * for Fuse.
	 *
	 * @param string $modelFormatter
	 */
	public static function setModelFormatter( RedBean_IModelFormatter $modelFormatter ) {
		self::$modelFormatter = $modelFormatter;
	}
	
}

/**
 * RedBean Facade
 * @file				RedBean/Facade.php
 * @description	Convenience class for RedBeanPHP.
 *						This class hides the object landscape of
 *						RedBean behind a single letter class providing
 *						almost all functionality with simple static calls.
 *
 * @author			Gabor de Mooij
 * @license			BSD
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 *
 */
class R {
	/**
	 *
	 * Constains an instance of the RedBean Toolbox
	 * @var RedBean_ToolBox
	 *
	 */
	public static $toolbox;
	/**
	 * Constains an instance of RedBean OODB
	 * @var RedBean_OODB
	 */
	public static $redbean;
	/**
	 * Contains an instance of the Query Writer
	 * @var RedBean_QueryWriter
	 */
	public static $writer;
	/**
	 * Contains an instance of the Database
	 * Adapter.
	 * @var RedBean_DBAdapter
	 */
	public static $adapter;
	/**
	 * Contains an instance of the Tree Manager
	 * @var RedBean_TreeManager
	 */
	public static $treeManager;
	/**
	 * Contains an instance of the Association Manager
	 * @var RedBean_AssociationManager
	 */
	public static $associationManager;
	/**
	 * Contains an instance of the Extended Association Manager
	 * @var RedBean_ExtAssociationManager
	 */
	public static $extAssocManager;
	/**
	 *
	 * Constains an instance of the RedBean Link Manager
	 * @var RedBean_LinkManager
	 *
	 */
	public static $linkManager;
	/**
	 * Returns version ID string
	 * Version No format: <Major>.<Minor>.<Maintenance>.<Fix/Update>
	 * 
	 * @return string $version Version ID
	 */
	public static function getVersion() {
		return "1.3";
	}
	/**
	 * Flag to indicate whether experimental (fearless) code might be used.
	 * If you experience any problems with new features these can be degrade
	 * easily by doing R::$flagFearless = false;
	 * @var bool
	 */
	public static $flagFearless = true;
	/**
	 * Kickstarts redbean for you.
	 * @param string $dsn
	 * @param string $username
	 * @param string $password
	 */
	public static function setup( $dsn="sqlite:/tmp/red.db", $username=NULL, $password=NULL ) {
		RedBean_Setup::kickstart( $dsn, $username, $password );
		$toolbox = RedBean_Setup::getToolBox();
		self::configureFacadeWithToolbox($toolbox);
	}
	/**
	 * Toggles DEBUG mode.
	 * In Debug mode all SQL that happens under the hood will
	 * be printed to the screen.
	 *
	 * @param boolean $tf
	 */
	public static function debug( $tf = true ) {
		self::$adapter->getDatabase()->setDebugMode( $tf );
	}
	/**
	 * Stores a RedBean OODB Bean and returns the ID.
	 *
	 * @param  RedBean_OODBBean $bean bean
	 * 
	 * @return integer $id id
	 */
	public static function store( RedBean_OODBBean $bean ) {
		return self::$redbean->store( $bean );
	}
	/**
	 * Freezes RedBean. In frozen mode the schema cannot be altered.
	 * Frozen mode is recommended for production use because it is
	 * secure and fast.
	 *
	 * @param boolean $tf whether to turn it on or off.
	 *
	 * @return void
	 */
	public static function freeze( $tf = true ) {
		self::$redbean->freeze( $tf );
	}
	/**
	 * Loads the bean with the given type and id and returns it.
	 *
	 * @param string  $type type
	 * @param integer $id   id of the bean you want to load
	 *
	 * @return RedBean_OODBBean $bean
	 */
	public static function load( $type, $id ) {
		return self::$redbean->load( $type, $id );
	}
	/**
	 * Deletes the specified bean.
	 *
	 * @param RedBean_OODBBean $bean bean to be deleted
	 *
	 * @return mixed
	 */
	public static function trash( RedBean_OODBBean $bean ) {
		return self::$redbean->trash( $bean );
	}
	/**
	 * Dispenses a new RedBean OODB Bean for use with
	 * the rest of the methods.
	 *
	 * @param string $type type
	 *
	 * @return RedBean_OODBBean $bean a new bean
	 */
	public static function dispense( $type ) {
		return self::$redbean->dispense( $type );
	}
	/**
	 * Loads a bean if ID > 0 else dispenses.
	 *
	 * @param string  $type type
	 * @param integer $id   id
	 *
	 * @return RedBean_OODBBean $bean bean
	 */
	public static function loadOrDispense( $type, $id = 0 ) {
		return ($id ? R::load($type,(int)$id) : R::dispense($type));
	}
	/**
	 * Convience method. Tries to find beans of a certain type,
	 * if no beans are found, it dispenses a bean of that type.
	 *
	 * @param  string $type   type of bean you are looking for
	 * @param  string $sql    SQL code for finding the bean
	 * @param  array  $values parameters to bind to SQL
	 *
	 * @return array $beans Contains RedBean_OODBBean instances
	 */
	public static function findOrDispense( $type, $sql, $values ) {
		$foundBeans = self::find($type,$sql,$values);
		if (count($foundBeans)==0) return array(self::dispense($type)); else return $foundBeans;
	}
	/**
	 * Associates two Beans. This method will associate two beans with eachother.
	 * You can then get one of the beans by using the related() function and
	 * providing the other bean. You can also provide a base bean in the extra
	 * parameter. This base bean allows you to add extra information to the association
	 * record. Note that this is for advanced use only and the information will not
	 * be added to one of the beans, just to the association record.
	 * It's also possible to provide an array or JSON string as base bean. If you
	 * pass a scalar this function will interpret the base bean as having one
	 * property called 'extra' with the value of the scalar.
	 *
	 * @param RedBean_OODBBean $bean1 bean that will be part of the association
	 * @param RedBean_OODBBean $bean2 bean that will be part of the association
	 * @param mixed $extra            bean, scalar, array or JSON providing extra data.
	 *
	 * @return mixed
	 */
	public static function associate( RedBean_OODBBean $bean1, RedBean_OODBBean $bean2, $extra = null ) {
		
		if (!$extra) {
			return self::$associationManager->associate( $bean1, $bean2 );
		}
		else{
			if (!is_array($extra)) {
				$info = json_decode($extra,true);
				if (!$info) $info = array("extra"=>$extra);
			}
			else {
				$info = $extra;
			}
			$bean = R::dispense("typeLess");
			$bean->import($info);
			return self::$extAssocManager->extAssociate($bean1, $bean2, $bean);
		}
		
	}
	
	/**
	 * Breaks the association between two beans.
	 * This functions breaks the association between a pair of beans. After
	 * calling this functions the beans will no longer be associated with
	 * eachother. Calling related() with either one of the beans will no longer
	 * return the other bean.
	 *
	 * @param RedBean_OODBBean $bean1 bean
	 * @param RedBean_OODBBean $bean2 bean
	 *
	 * @return mixed
	 */
	public static function unassociate( RedBean_OODBBean $bean1, RedBean_OODBBean $bean2 ) {
		return self::$associationManager->unassociate( $bean1, $bean2 );
	}
	/**
	 * Returns all the beans associated with $bean.
	 * This method will return an array containing all the beans that have
	 * been associated once with the associate() function and are still
	 * associated with the bean specified. The type parameter indicates the
	 * type of beans you are looking for. You can also pass some extra SQL and
	 * values for that SQL to filter your results after fetching the
	 * related beans.
	 *
	 * If 'fearless' mode is on, this method will try to take a shortcut and
	 * use a subquery instead.
	 *
	 * @param RedBean_OODBBean $bean the bean you have
	 * @param string				$type the type of beans you want
	 * @param string				$sql  SQL snippet for extra filtering
	 * @param array				$val  values to be inserted in SQL slots
	 *
	 * @return array $beans	beans yielded by your query.
	 */
	public static function related( RedBean_OODBBean $bean, $type, $sql=null, $values=array()) {
		if (empty($values) && $sql && method_exists(self::$writer,"__fastSelectCritRelated") && !isset($noFearlessCode)) {
			
			$idfield = self::$writer->getIDField( $type );
			$table = self::$writer->getFormattedTableName($type);
			$rows = self::$associationManager->related($bean,$type, false, self::$writer->__fastSelectCritRelated($table, $idfield, $sql));
			if (count($rows)==0) return array();
			return self::convertToBeans($type,$rows);
		}
		$keys = self::$associationManager->related( $bean, $type );
		if (count($keys)==0) return array();
		if (!$sql) return self::batch($type, $keys);
		$idfield = self::$writer->getIDField( $type );
		$sqlSnippet = self::$writer->getSQLSnippetFilter($idfield, $keys, $sql);
		return self::find( $type, $sqlSnippet, $values );
		
	}
	
	/**
	 * The opposite of related(). Returns all the beans that are not
	 * associated with the bean provided.
	 *
	 * @param RedBean_OODBBean $bean   bean provided
	 * @param string				$type   type of bean you are searching for
	 * @param string				$sql    SQL for extra filtering
	 * @param array				$values values to be inserted in SQL slots
	 *
	 * @return array $beans beans 
	 */
	public static function unrelated(RedBean_OODBBean $bean, $type, $sql=null, $values=array()) {
		
		$keys = self::$associationManager->related( $bean, $type );
		$idfield = self::$writer->getIDField( $type );
		$sqlSnippet = self::$writer->getSQLSnippetFilter($idfield, $keys, $sql, true);
		return self::find( $type, $sqlSnippet, $values );
	}
	/**
	 * Returns only single associated bean. This is the default way RedBean
	 * handles N:1 relations, by just returning the 1st one ;)
	 *
	 * @param RedBean_OODBBean $bean   bean provided
	 * @param string				$type   type of bean you are searching for
	 * @param string				$sql    SQL for extra filtering
	 * @param array				$values values to be inserted in SQL slots
	 *
	 *
	 * @return RedBean_OODBBean $bean
	 */
	public static function relatedOne( RedBean_OODBBean $bean, $type, $sql='1', $values=array() ) {
		$beans = self::related($bean, $type, $sql, $values);
		if (count($beans)==0) return null;
		return reset( $beans );
	}
	/**
	 * Clears all associated beans.
	 * @param RedBean_OODBBean $bean
	 * @param string $type
	 * @return mixed
	 */
	public static function clearRelations( RedBean_OODBBean $bean, $type, RedBean_OODBBean $bean2 = null, $extra = null ) {
		$r = self::$associationManager->clearRelations( $bean, $type );
		if ($bean2) {
			self::associate($bean, $bean2, $extra);
		}
		return $r;
	}
	/**
	 * Attaches $child bean to $parent bean.
	 *
	 * @param RedBean_OODBBean $parent parent
	 * @param RedBean_OODBBean $child  child
	 * @return mixed
	 */
	public static function attach( RedBean_OODBBean $parent, RedBean_OODBBean $child ) {
		return self::$treeManager->attach( $parent, $child );
	}
	/**
	 * @deprecated
	 * Links two beans using a foreign key field, 1-N Assoc only.
	 *
	 * @param RedBean_OODBBean $bean1 bean1
	 * @param RedBean_OODBBean $bean2 bean2
	 *
	 * @return mixed
	 */
	public static function link( RedBean_OODBBean $bean1, RedBean_OODBBean $bean2, $name = null ) {
		return self::$linkManager->link( $bean1, $bean2, $name );
	}
	/**
	 *
	 * @deprecated
	 * @param RedBean_OODBBean $bean     bean
	 * @param string				$typeName type
	 *
	 * @return mixed
	 */
	public static function getBean( RedBean_OODBBean $bean, $typeName, $name = null ) {
		return self::$linkManager->getBean($bean, $typeName, $name );
	}
	/**
	 *	@deprecated
	 * @param RedBean_OODBBean $bean		 bean
	 * @param string				$typeName type
	 *
	 * @return mixed
	 */
	public static function getKey( RedBean_OODBBean $bean, $typeName, $name = null ) {
		return self::$linkManager->getKey($bean, $typeName, $name );
	}
	/**
	 * @deprecated
	 *
	 * @param RedBean_OODBBean $bean		 bean
	 * @param string				$typeName type
	 */
	public static function breakLink( RedBean_OODBBean $bean, $typeName, $name = null ) {
		return self::$linkManager->breakLink( $bean, $typeName, $name );
	}
	/**
	 * Returns all children beans under parent bean $parent
	 *
	 * @param RedBean_OODBBean $parent parent
	 *
	 * @return array $childBeans child beans
	 */
	public static function children( RedBean_OODBBean $parent ) {
		return self::$treeManager->children( $parent );
	}
	/**
	 * Returns the parent of a bean.
	 *
	 * @param RedBean_OODBBean $bean bean
	 *
	 * @return RedBean_OODBBean $bean bean
	 */
	public static function getParent( RedBean_OODBBean $bean ) {
		return self::$treeManager->getParent( $bean );
	}
	/**
	 * Finds a bean using a type and a where clause (SQL).
	 * As with most Query tools in RedBean you can provide values to
	 * be inserted in the SQL statement by populating the value
	 * array parameter; you can either use the question mark notation
	 * or the slot-notation (:keyname).
	 *
	 * @param string $type   type
	 * @param string $sql    sql
	 * @param array  $values values
	 *
	 * @return array $beans  beans
	 */
	public static function find( $type, $sql="1", $values=array() ) {
		return RedBean_Plugin_Finder::where( $type, $sql, $values );
	}
	/**
	 * @deprecated
	 *
	 * Use related() instead.
	 *
	 * Convenience Method
	 *
	 * @param RedBean_OODBBean $bean   bean
	 * @param string				$type   type
	 * @param string				$sql    sql
	 * @param array				$values values
	 *
	 * @return array $beans
	 */
	public static function findRelated( RedBean_OODBBean $bean, $type, $sql=" id IN (:keys) ", $values=array()  ) {
		$keys = self::$associationManager->related($bean,$type);
		$sql=str_replace(":keys",implode(",",$keys),$sql);
		return self::find($type,$sql,$values);
	}
	/**
	 * @deprecated
	 *
	 * Use related() instead.
	 *
	 * Convenience Method
	 *
	 * @param RedBean_OODBBean $bean   bean
	 * @param string				$type   type
	 * @param string				$sql    sql
	 * @param array				$values values
	 *
	 * @return array $beans
	 */
	public static function findLinks( RedBean_OODBBean $bean, $type, $sql=" id IN (:keys) ", $values=array() ) {
		$keys = self::$linkManager->getKeys($bean,$type);
		$sql=str_replace(":keys",implode(",",$keys),$sql);
		return self::find($type,$sql,$values);
	}
	/**
	 * Finds a bean using a type and a where clause (SQL).
	 * As with most Query tools in RedBean you can provide values to
	 * be inserted in the SQL statement by populating the value
	 * array parameter; you can either use the question mark notation
	 * or the slot-notation (:keyname).
	 * The variation also exports the beans (i.e. it returns arrays).
	 *
	 * @param string $type   type
	 * @param string $sql    sql
	 * @param array  $values values
	 *
	 * @return array $arrays arrays
	 */
	public static function findAndExport($type, $sql="1", $values=array()) {
		$items = RedBean_Plugin_Finder::where( $type, $sql, $values );
		$arr = array();
		foreach($items as $key=>$item) {
			$arr[$key]=$item->export();
		}
		return $arr;
	}
	/**
	 * Finds a bean using a type and a where clause (SQL).
	 * As with most Query tools in RedBean you can provide values to
	 * be inserted in the SQL statement by populating the value
	 * array parameter; you can either use the question mark notation
	 * or the slot-notation (:keyname).
	 * This variation returns the first bean only.
	 *
	 * @param string $type	 type
	 * @param string $sql	 sql
	 * @param array  $values values
	 *
	 * @return RedBean_OODBBean $bean
	 */
	public static function findOne( $type, $sql="1", $values=array()) {
		$items = R::find($type,$sql,$values);
		return reset($items);
	}
	/**
	 * Finds a bean using a type and a where clause (SQL).
	 * As with most Query tools in RedBean you can provide values to
	 * be inserted in the SQL statement by populating the value
	 * array parameter; you can either use the question mark notation
	 * or the slot-notation (:keyname).
	 * This variation returns the last bean only.
	 *
	 * @param string $type	 type
	 * @param string $sql	 sql
	 * @param array  $values values
	 *
	 * @return RedBean_OODBBean $bean
	 */
	public static function findLast( $type, $sql="1", $values=array() )
	{
		$items = R::find( $type, $sql, $values );
		return end( $items );
	}
	/**
	 * Returns an array of beans.
	 *
	 * @param string $type type
	 * @param array  $ids  ids
	 * 
	 * @return array $beans
	 */
	public static function batch( $type, $ids ) {
		return self::$redbean->batch($type, $ids);
	}
	/**
	 * Returns a simple list instead of beans, based
	 * on a type, property and an SQL where clause.
	 *
	 * @param string $type  type
	 * @param string $prop  property
	 * @param string $where SQL
	 *
	 * @return array $list array items
	 */
	public static function lst( $type,$prop,$sql=" 1 " ) {
		$list = self::find($type,$sql);
		$listItems = array();
		foreach($list as $id=>$item) {
			$listItems[] = $item->$prop;
		}
		return $listItems;
	}
	/**
	 * Convenience function to execute Queries directly.
	 * Executes SQL.
	 *
	 * @param string $sql	 sql
	 * @param array  $values values
	 *
	 * @return array $results
	 */
	public static function exec( $sql, $values=array() ) {
		return self::secureExec(function($sql, $values) {
			return R::$adapter->exec( $sql, $values );
		}, NULL,$sql, $values );
	}
	/**
	 * Convenience function to execute Queries directly.
	 * Executes SQL.
	 *
	 * @param string $sql	 sql
	 * @param array  $values values
	 *
	 * @return array $results
	 */
	public static function getAll( $sql, $values=array() ) {
		return self::secureExec(function($sql, $values) {
			return R::$adapter->get( $sql, $values );
		}, array(), $sql, $values);
	}
	/**
	 * Convenience function to execute Queries directly.
	 * Executes SQL.
	 *
	 * @param string $sql	 sql
	 * @param array  $values values
	 *
	 * @return string $result scalar
	 */
	public static function getCell( $sql, $values=array() ) {
		return self::secureExec(function($sql, $values) {
			return R::$adapter->getCell( $sql, $values );
		}, NULL, $sql, $values);
	}
	/**
	 * Convenience function to execute Queries directly.
	 * Executes SQL.
	 *
	 * @param string $sql	 sql
	 * @param array  $values values
	 *
	 * @return array $results
	 */
	public static function getRow( $sql, $values=array() ) {
		return self::secureExec(function($sql, $values) {
			return R::$adapter->getRow( $sql, $values );
		}, array(),$sql, $values);
	}
	/**
	 * Convenience function to execute Queries directly.
	 * Executes SQL.
	 *
	 * @param string $sql	 sql
	 * @param array  $values values
	 *
	 * @return array $results
	 */
	public static function getCol( $sql, $values=array() ) {
		return self::secureExec(function($sql, $values) {
			return R::$adapter->getCol( $sql, $values );
		}, array(),$sql, $values);
	}
	/**
	 * Executes SQL function but corrects for SQL states.
	 *
	 * @param closure $func		closure
	 * @param mixed   $default default value to return
	 * @param string  $sql		SQL
	 * @param array   $values  values for slots
	 *
	 * @return mixed $results
	 */
	private static function secureExec( $func, $default=NULL, $sql, $values ) {
		if (!self::$redbean->isFrozen()) {
			try {
				$rs = $func($sql,$values);
			}catch(RedBean_Exception_SQL $e) { 
				if(self::$writer->sqlStateIn($e->getSQLState(),
				array(
				RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN,
				RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE)
				)) {
					return $default;
				}
				else {
					throw $e;
				}
			}
			return $rs;
		}
		else {
			return $func($sql,$values);
		}
	}
	/**
	 * Makes a copy of a bean. This method copies the bean and
	 * adds the specified associations.
	 *
	 * For instance: R::copy( $book, "author,library" );
	 *
	 * Duplicates the $book bean and copies the association links
	 * author and library as well. Note that only many-to-many
	 * associations can be copied. Also note that no author or library
	 * beans are copied, only the connections or references to these
	 * beans.
	 *
	 * @param RedBean_OODBBean $bean							bean
	 * @param string				$associatedBeanTypesStr bean types associated
	 *
	 * @return array $copiedBean the duplicated bean
	 */
	public static function copy($bean, $associatedBeanTypesStr="") {
		$type = $bean->getMeta("type");
		$copy = R::dispense($type);
		$copy->import( $bean->export() );
		$copy->copyMetaFrom( $bean );
		$copy->id = 0;
		R::store($copy);
		$associatedBeanTypes = explode(",",$associatedBeanTypesStr);
		foreach($associatedBeanTypes as $associatedBeanType) {
			$assocBeans = R::related($bean, $associatedBeanType);
			foreach($assocBeans as $assocBean) {
				R::associate($copy,$assocBean);
			}
		}
		$copy->setMeta("original",$bean);
		return $copy;
	}
	/**
	 * Given an array of two beans and a property, this method
	 * swaps the value of the property.
	 * This is handy if you need to swap the priority or orderNo
	 * of an item (i.e. bug-tracking, page order).
	 *
	 * @param array  $beans    beans
	 * @param string $property property
	 */
	public static function swap( $beans, $property ) {
		$bean1 = array_shift($beans);
		$bean2 = array_shift($beans);
		$tmp = $bean1->$property;
		$bean1->$property = $bean2->$property;
		$bean2->$property = $tmp;
		R::store($bean1);
		R::store($bean2);
	}
	/**
	 * Converts a series of rows to beans.
	 *
	 * @param string $type type
	 * @param array  $rows must contain an array of arrays.
	 *
	 * @return array $beans
	 */
	public static function convertToBeans($type,$rows) {
		return self::$redbean->convertToBeans($type,$rows);
	}
	
	
	/**
	 * Tags a bean or returns tags associated with a bean.
	 * If $tagList is null or omitted this method will return a 
	 * comma separated list of tags associated with the bean provided.
	 * If $tagList is a comma separated list (string) of tags all tags will
	 * be associated with the bean. 
	 * You may also pass an array instead of a string.
	 *
	 * @param RedBean_OODBBean $bean    bean
	 * @param mixed				$tagList tags
	 *
	 * @return string $commaSepListTags
	 */
	public static function tag( RedBean_OODBBean $bean, $tagList = null ) {
		if (is_null($tagList)) {
			$tags = R::related( $bean, "tag");
			$foundTags = array();
			foreach($tags as $tag) {
				$foundTags[] = $tag->title;
			}
			return implode(",",$foundTags);
		}
	
		
		if ($tagList!==false && !is_array($tagList)) $tags = explode( ",", (string)$tagList); else $tags=$tagList;
		if (is_array($tags)) {
		foreach($tags as $tag) {
			if (preg_match("/\W/",$tag)) throw new RedBean_Exception("Invalid Tag. Tags may only contain alpha-numeric characters");
		}
		}
		
		R::clearRelations( $bean, "tag" );
		if ($tagList===false) return;
		
		foreach($tags as $tag) {
			
			$t = R::findOne("tag"," title = ? ",array($tag));
			if (!$t) {
				$t = R::dispense("tag");
				$t->title = $tag;
				R::store($t);
			}
			R::associate( $bean, $t ); 
		}
	}
	/**
	 * @static
	 * Returns all beans that have been tagged with one of the tags given.
	 *
	 * @param  $beanType
	 * @param  $tagList
	 *
	 * @return array
	 */
	public static function tagged( $beanType, $tagList ) {
		if ($tagList!==false && !is_array($tagList)) $tags = explode( ",", (string)$tagList); else $tags=$tagList;
		$collection = array();
		foreach($tags as $tag) {
			$retrieved = array();
			$tag = R::findOne("tag"," title = ? ", array($tag));
			if ($tag) $retrieved = R::related($tag, $beanType);
			foreach($retrieved as $key=>$bean) $collection[$key]=$bean;
		}
		return $collection;
	}
	/**
	 * Wipes all beans of type $beanType.
	 *
	 * @param string $beanType type of bean you want to destroy entirely.
	 */
	public static function wipe( $beanType ) {
		R::$redbean->wipe($beanType);
	}
	/**
	 * Counts beans
	 *
	 * @param string $beanType type of bean
	 *
	 * @return integer $numOfBeans
	 */
	public static function count( $beanType ) {
		return R::$redbean->count($beanType);
	}
	/**
	 * Configures the facade, want to have a new Writer? A new Object Database or a new
	 * Adapter and you want it on-the-fly? Use this method to hot-swap your facade with a new
	 * toolbox.
	 *
	 * @static
	 * @param RedBean_ToolBox $tb toolbox
	 *
	 * @return RedBean_ToolBox $tb old, rusty, previously used toolbox
	 */
	public static function configureFacadeWithToolbox( RedBean_ToolBox $tb ) {
		$oldTools = self::$toolbox;
		self::$toolbox = $tb;
		self::$writer = self::$toolbox->getWriter();
		self::$adapter = self::$toolbox->getDatabaseAdapter();
		self::$redbean = self::$toolbox->getRedBean();
		self::$associationManager = new RedBean_AssociationManager( self::$toolbox );
		self::$treeManager = new RedBean_TreeManager( self::$toolbox );
		self::$linkManager = new RedBean_LinkManager( self::$toolbox );
		self::$extAssocManager = new RedBean_ExtAssociationManager( self::$toolbox );
		$helper = new RedBean_ModelHelper();
		self::$redbean->addEventListener("update", $helper );
		self::$redbean->addEventListener("open", $helper );
		self::$redbean->addEventListener("delete", $helper );
		self::$redbean->addEventListener("after_delete", $helper );
		self::$redbean->addEventListener("after_update", $helper );
		self::$redbean->addEventListener("dispense", $helper );
		return $oldTools;
	}
	/**
	 * facade method for Cooker.
	 * 
	 * @static
	 * @param  $arr
	 * @return array
	 */
	public static function cooker($arr) {
		return RedBean_Cooker::load($arr, R::$toolbox);
	}
}

/**
 * BeanCan
 * A Server Interface for RedBean and Fuse.
 *
 * The BeanCan Server is a lightweight, minimalistic server interface for
 * RedBean that can perfectly act as an ORM middleware solution or a backend
 * for an AJAX application.
 *
 * By Gabor de Mooij
 *
 */
class RedBean_BeanCan {
	/**
	 * Holds a FUSE instance.
	 * @var RedBean_ModelHelper
	 */
	private $modelHelper;
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->modelHelper = new RedBean_ModelHelper;
	}
	/**
	 * Writes a response object for the client (JSON encoded).
	 *
	 * @param mixed   $result       result
	 * @param integer $id           request ID
	 * @param integer $errorCode    error code from server
	 * @param string  $errorMessage error message from server
	 *
	 * @return string $json JSON encoded response.
	 */
	private function resp($result=null, $id=null, $errorCode="-32603",$errorMessage="Internal Error") {
		$response = array(
			"jsonrpc"=>"2.0",
		);
		
		if ($id) {
			$response["id"] = $id;
		}
		if ($result) {
			$response["result"]=$result;
		}
		else {
			$response["error"] = array(
				"code"=>$errorCode,
				"message"=>$errorMessage
			);
		}
		return (json_encode($response));
	}
	/**
	 * Processes a JSON object request.
	 *
	 * @param array $jsonObject JSON request object
	 *
	 * @return mixed $result result
	 */
	public function handleJSONRequest( $jsonString ) {
		
		$jsonArray = json_decode($jsonString,true);
		if (!$jsonArray) return $this->resp(null,null,-32700,"Cannot Parse JSON");
		if (!isset($jsonArray["jsonrpc"])) return $this->resp(null,null,-32600,"No RPC version");
		if (($jsonArray["jsonrpc"]!="2.0")) return $this->resp(null,null,-32600,"Incompatible RPC Version");
		
		if (!isset($jsonArray["id"])) return $this->resp(null,null,-32600,"No ID");
		
		$id = $jsonArray["id"];
		
		if (!isset($jsonArray["method"])) return $this->resp(null,$id,-32600,"No method");
		
		if (!isset($jsonArray["params"])) {
			$data = array();
		}
		else {
			$data = $jsonArray["params"];
		}
		
		$method = explode(":",trim($jsonArray["method"]));
		if (count($method)!=2) {
			return $this->resp(null, $id, -32600,"Invalid method signature. Use: BEAN:ACTION");
		}
		
		$beanType = $method[0];
		$action = $method[1];
		
		
		if (preg_match("/\W/",$beanType)) return $this->resp(null, $id, -32600,"Invalid Bean Type String");
		if (preg_match("/\W/",$action)) return $this->resp(null, $id, -32600,"Invalid Action String");
		try {
			switch($action) {
				case "store":
					if (!isset($data[0])) return $this->resp(null, $id, -32602,"First param needs to be Bean Object");
					$data = $data[0];
					if (!isset($data["id"])) $bean = R::dispense($beanType); else
						$bean = R::load($beanType,$data["id"]);
					$bean->import( $data );
					$rid = R::store($bean);
					return $this->resp($rid, $id);
					break;
				case "load":
					if (!isset($data[0])) return $this->resp(null, $id, -32602,"First param needs to be Bean ID");
					$bean = R::load($beanType,$data[0]);
					return $this->resp($bean->export(),$id);
					break;
				case "trash":
					if (!isset($data[0])) return $this->resp(null, $id, -32602,"First param needs to be Bean ID");
					$bean = R::load($beanType,$data[0]);
					R::trash($bean);
					return $this->resp("OK",$id);
					break;
				default:
					$modelName = $this->modelHelper->getModelName( $beanType );
					if (!class_exists($modelName)) return $this->resp(null, $id, -32601,"No such bean in the can!");
					$beanModel = new $modelName;
					if (!method_exists($beanModel,$action)) return $this->resp(null, $id, -32601,"Method not found in Bean: $beanType ");
					return $this->resp( call_user_func_array(array($beanModel,$action), $data), $id);
			}
		}
		catch(Exception $exception) {
			return $this->resp(null, $id, -32099,$exception->getCode()."-".$exception->getMessage());
		}
	}
}

 /**
 * @name RedBean Cooker
 * @file RedBean
 * @author Gabor de Mooij and the RedBean Team
 * @copyright Gabor de Mooij (c)
 * @license BSD
 *
 * The Cooker is a little candy to make it easier to read-in an HTML form.
 * This class turns a form into a collection of beans plus an array
 * describing the desired associations.
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_Cooker {
	/**
	 * This method will inspect the array provided and load/dispense the
	 * desired beans. To dispense a new bean, the array must contain:
	 *
	 * array( "newuser"=> array("type"=>"user","name"=>"John") )
	 *
	 * - Creates a new bean of type user, property name is set to "John"
	 *
	 * To load a bean (for association):
	 *
	 * array( "theaddress"=> array("type"=>"address","id"=>2) )
	 * 
	 * - Loads a bean of type address with ID 2
	 *
	 * Now to associate this bean in your form:
	 *
	 * array("associations"=>array( "0" => array( "newuser-theaddress" ) ))
	 *
	 * - Associates the beans under keys newuser and theaddress.
	 *
	 * To modify an existing bean:
	 *
	 * array("existinguser"=>array("type"=>"user","id"=>2,"name"=>"Peter"))
	 *
	 * - Changes name of bean of type user with ID 2 to 'Peter'
	 *
	 * This function returns:
	 *
	 * array(
	 * 	"can" => an array with beans, either loaded or dispensed and populated
	 *  "pairs" => an array with pairs of beans to be associated
	 * );
	 *
	 * Note that this function actually does not store or associate anything at all,
	 * it just prepares two arrays.
	 *
	 * @static
	 * @param  $post the POST array containing the form data
	 * @return array hash table containing 'can' and 'pairs'
	 *
	 */
	public static function load($post, RedBean_ToolBox $toolbox) {
		$writer = $toolbox->getWriter();
		
		if (isset($post["associations"])) {
			$associations = $post["associations"];
			unset($post["associations"]);
		}
		
		$can = $pairs = array();
		foreach($post as $key => $rawBean) {
			if (is_array($rawBean) && isset($rawBean["type"])) {
				
				$type = $rawBean["type"];
				unset($rawBean["type"]);
				
				$idfield = $writer->getIDField($type);
				if (isset($rawBean[$idfield])) {
					
					$id = $rawBean[$idfield];
					
					if ($id==0 && count($rawBean)===1) continue;
					unset($rawBean[$idfield]);
					
					$bean = R::load($type, $id);
				}
				else { 
					$bean = R::dispense($type);
				}
				
				foreach($rawBean as $field=>$value){
					if (!empty($value)) $bean->$field = $value;
				}
				$can[$key]=$bean;
			}
		}
		if (isset($associations) && is_array($associations)) {
			foreach($associations as $assoc) {
				foreach($assoc as $info) {
					if ($info=="0" || $info=="") continue;
					$keys = explode("-", $info);
					
					if (isset($can[$keys[0]])) $bean1 = $can[$keys[0]]; else {
						$loader = explode(":",$keys[0]);
						$bean1 = R::load( $loader[0], $loader[1] );
					} 
					$bean2 = $can[$keys[1]];
					$pairs[] = array( $bean1, $bean2 );
				}
			}
		}
		return array("can"=>$can, "pairs"=>$pairs);
	}
}
