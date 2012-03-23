<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    // KEEP these in alphabetical order.
    // KEEP them indented correctly.
    // KEEP all the language files up-to-date with each other.
    // DON'T MAKE A MESS!
    return array(
        'Apache'
            => 'Apache',
        'APC'
            => 'APC',
        'Below you will find the results of the system check. If any required ' .
        'services are not setup correctly, you will need to make sure they are ' .
        'installed correctly before you can continue.'
            => 'Ecco i risultati del controllo de sistema.  Si alcuno dei servizi richiesti ' .
               'non sono installati correttamente, è necessario ripararle avanti di continuare.',
        'Can either be a domain name or an IP address.'
            => 'Posso essere un nome de dominio o un indirizzo IP.',
        'Click below to go to the login page. The username is <b>super</b>'
            => 'Clicca sul link per continuare alla pagina di login.  Il nome d\'utente è <b>super</b>',
        'Click Here to install the demo data'
            => 'Clicca qui per popolare il database con dati di demo',
        'Click to start'
            => 'Clicca per cominciare',
        'Congratulations! The demo data has been successfully loaded.'
            => 'Congratulazioni! I dati di demo si sono installati con successo.',
        'Congratulations! The installation of Zurmo is complete.'
            => 'Congratulazioni! L\'installazione di Zurmo è completata con successo.',
        'Connecting to Database.'
            => 'Connessione al database.',
        'Continue'
            => 'Continua',
        'Correctly Installed Services'
            => 'Servizi installati correttamente',
        'Creating super user.'
            => 'Creazione del utente super.',
        'Ctype extension is loaded.'
            => 'L\'estensione Ctype è stata caricata.',
        'Ctype extension is not loaded.'
            => 'L\'estensione Ctype non è stata caricata.',
        'Curl'
            => 'Curl',
        'Database admin password'
            => 'Password relativa all\'utente del database',
        'Database admin username'
            => 'Nome dell\'utente admin del database',
        'Database default collation is: {collation}'
            => 'Collazione default del database: {collation}',
        'Database default collation meets minimum requirement.'
            => 'Collazione default del database soddisfa il requisito minimale.',
        'Database default collation should not be in: {listOfCollations}'
            => 'Collazione default del database non dovrebbe essere en: {listOfCollations}',
        'Database host'
            => 'Host del database',
        'Database is in strict mode.'
            => 'Il database è in modalità strict.',
        'Database is not in strict mode.'
            => 'Il database non è in modalità strict.',
        'Database log_bin=off and therefore satisfies this requirement.' // Not Coding Standard
            => 'Nel database, il valore del parametro log_bin=off e soddisfa il requisito.', // Not Coding Standard
        'Database log_bin=on and log_bin_trust_function_creators=on and therefore satisfies this requirement' // Not Coding Standard
            => 'Nel database, i valori dei parametri log_bin=on e log_bin_trust_function_creators=on soddisfanno i requisiti.', // Not Coding Standard
        'Database log_bin=on. Either set log_bin=off or set log_bin_trust_function_creators=on.' // Not Coding Standard
            => 'Nel database, il valore del parametro log_bin=on. Preggo di modificare il valore a log_bin=off o log_bin_trust_function_creators=on.', // Not Coding Standard
        'Database max_allowed_packet size is:'
            => 'Il valore max_allowed_packet del database è:',
        'Database max_allowed_packet size meets minimum requirement.'
            => 'Il valore max_allowed_packet del database soddisfa il requisito minimale.',
        'Database max_sp_recursion_depth size is:'
            => 'Il valore max_sp_recursion_depth del database è:',
        'Database max_sp_recursion_depth size meets minimum requirement.'
            => 'Il valore max_sp_recursion_depth del database soddisfa il requisito minimale.',
        'Database name'
            => 'Nome del database',
        'Database optimizer_search_depth size meets requirement.'
            => 'Il valore del parametro optimizer_search_depth soddisfa il requisite.',
        'Database optimizer_search_depth value is {searchDepth}. It is required to be set to 0.'
            => 'Il valore del parametro optimizer_search_depth è {searchDepth}. Il valore dovrebbe essere 0.?',
        'Database password'
            => 'Password del database',
        'Database schema creation complete.'
            => 'Creazione schema di database è completato.',
        'Database thread_stack value is:'
            => 'Il valore thread_stack del database è:',
        'Database thread_stack value meets minimum requirement.'
            => 'Il valore thread_stack del database soddisfa il requisito minimale.',
        'Database username'
            => 'Nome d\'utente del database',
        'Dropping existing tables.'
            => 'Rimuovendo tavole esistente.',
        'Error code:'
            => 'Codice Errore:',
        'FAIL'
            => 'FALLA',
        'Failed Optional Services'
            => 'Servizi opzionali non installati',
        'Failed Required Services'
            => 'Servizi richiesti non isntallati',
        'Finished loading demo data.'
            => 'I dati di demo sono installati.',
        'Freezing database.'
            => 'Freezing el database.',
        'Host name where Zurmo will be installed.'
            => 'Il nome di host dove Zurmo va essere installato.',
        'If this website is in production mode, please remove the app/test.php file.'
            => 'Si questo webpage è en produzions, prego di rimuovere ile file app/test.php.',
        'In all likelihood, these items were supplied to you by your Web Host. '.
        'If you do not have this information, then you will need to contact them ' .
        'before you can continue. If you\'re all ready...'
            => 'Questa informazione è fornita dal vostro host Web.' .
               'Si non avete questa informazione, prego di contattare il vostro ' .
               'amministratore avanti di continuare. Si tutto fa in ordine...',
        'Install'
            => 'Installazione',
        'Install demo data.'
            => 'Installa i dati di demo.',
        'Installation Complete.'
            => 'Installazione completata.',
        'Installation in progress. Please wait.'
            => 'Installazione in corso. Attendere prego.',
        'Installation Output:'
            => 'Log da installazione:',
        'is installed, but the version is unknown.'
            => 'è installato, ma la versione non è conosciuta.',
        'is not installed'
            => 'non è installato',
        'It is highly recommended that all optional services are installed and ' .
        'working before continuing.'
            => 'È molto consigliato installare tutti i servizi opzionali ' .
               'avanti di continuare.',
        'Leave this blank unless you would like to create the user and database ' .
        'for Zurmo to run in.'
            => 'Lascia il valore vuoto si voglio creare l\'utente e il database ' .
               'per Zurmo.',
        'Loading demo data. Please wait.'
            => 'Installa i dati di demo.  Attendere prego.',
        'Locking Installation.'
            => 'Proteggere l\'installazione.',
        'Login'
            => 'Login',
        'Mbstring is installed.'
            => 'Mbstring è installato.',
        'Mbstring is not installed.'
            => 'Mbstring non è installato.',
        'Memcache'
            => 'Memcache',
        'Memcache host'
            => 'Host Memcache',
        'Memcache host name. Default is 127.0.0.1'
            => 'Nome di host Memcache.  Il default è 127.0.0.1',
        'Memcache port number'
            => 'Numero di porta Memcache',
        'Memcache port number. Default is 11211'
            => 'Numero di porta Memcache.  Il default è 11211',
        'Microsoft-IIS'
            => 'Microsoft-IIS',
        'minimum requirement is:'
            => 'requisito minimo è:',
        'Minimum version required:'
            => 'La versione richiesta minimale è',
        'Mysql'
            => 'Mysql',
        'Oh no!'
            => 'Oh no!',
        'PASS'
            => 'PASS',
        'PCRE extension is loaded.'
            => 'L\'estensione PCRE è stata caricata.',
        'PCRE extension is not loaded.'
            => 'L\'estensione PCRE non è stata caricata.',
        'PHP'
            => 'PHP',
        'PHP date.timezone is not set.'
            => 'Il parametro PHP date.timezone non è configurato.',
        'PHP date.timezone is set.'
            => 'Il parametro PHP date.timezone è impostato.',
        'PHP file_uploads is Off.  This should be on.'
            => 'PHP file_uploads è disabilitato. Questa funzionq deve essere attivata.',
        'PHP file_uploads is on which is ok.'
            => 'PHP file_uploads è attivato.',
        'PHP memory_limit is:'
            => 'Configurazione PHP memory_limit è:?',
        'PHP memory_limit meets minimum requirement.'
            => 'Configurazione PHP memory_limit soddisfa il requisito minimale.',
        'PHP post_max_size meets minimum requirement.'
            => 'PHP post_max_size soddisfa il requisito minimale.',
        'PHP post_max_size setting is:'
            => 'PHP post_max_size è:',
        'PHP upload_max_filesize value is:'
            => 'Il valore PHP upload_max_filesize è:',
        'PHP upload_max_filesize value meets minimum requirement.'
            => 'PHP upload_max_filesize soddisfa il requisito minimale.',
        'Please delete all files from assets folder on server.'
            => 'Prego di eliminare tutti i file della cartella Assets.',
        'Rebuilding Permissions.'
            => 'Ricostruzione dei privilegi.',
        'Recheck System'
            => 'Re-controlla il sistema',
        'RedBean'
            => 'RedBean',
        'RedBean file is missing patch.'
            => 'Il file RedBean manca il patch.',
        'RedBean file is patched correctly'
            => 'Il file RedBean è corregge correttamente.',
        'Schema update complete.'
            => 'L\'aggiornamento dello schema è completo.',
        'Service Status Partially Known'
            => 'Il stato del servizio è parzialmente  conosciuto.',
        'Setting up default data.'
            => 'installazione degli dati di default.',
        'Since you specified an existing database you must check this box in order ' .
        'to proceed. THIS WILL REMOVE ALL EXISTING DATA.'
            => 'Avete specificato un database esistente, deve contrassegnare questo ' .
               'box per continuare. QUESTO ELIMINERÀ TUTTI I DATI ESISTENTI.',
        'SOAP is installed.'
            => 'SOAP è installato.',
        'SOAP is not installed.'
            => 'SOAP non è installato.',
        'SPL extension is loaded.'
            => 'L\'estensione SPL è stata caricata.',
        'SPL extension is not loaded.'
            => 'L\'estensione SPL non è stata caricata.',
        'Starting database schema creation.'
            => 'Creazione del schema del database.',
        'Starting schema update process.'
            => 'Inizio del processo di update del schema.',
        'Starting to load demo data.'
            => 'Installazione dei dati di demo.',
        'The database name specified does not exist or the user specified does not have access.'
            => 'Il nome del database specificato non esiste o el utente specificato non ha acceso.',
        'The debug.php config file is not writable.'
            => 'Il file di config depbug.php non è scrivibile.',
        'The debug.php config file is writable.'
            => 'Il file di config depbug.php è scrivibile',
        'The instance folders are present and writable.'
            => 'I cartelle di sistema sono presenti e scrivibili.',
        'The name of the database you want to run Zurmo in.'
            => 'Il nome del database per Zurmo.',
        'The next step is to install the demo data.'
            => 'Il prossimo passo è d\'installare i dati di demo.',
        'The perInstance.php config file is not writable.'
            => 'Il file di config perInstance.php non è scrivibile',
        'The perInstance.php config file is writable.'
            => 'Il file di config perInstance.php è scrivibile',
        'The relative path where Zurmo will be installed.'
            => 'Il percorso relativo del file dove Zurmo va essere installato.',
        'The system has detected that the hostInfo and/or scriptUrl are not set up. Please open the perInstance.php config file and set up these parameters.'
            => 'Il sistema ha trovato che hostInfo e/o scriptUrl non sono configurati.  Prego di aprire il file config perInstance.php e configurare questi parametri.',
        'There was a problem creating the database Error code:'
            => 'C\'era un errore durante la creazione del database dei codice Errore:',
        'There was a problem creating the user Error code:'
            => 'C\'era un error durante la creazione del utente dei codice Errore:',
        'Tidy'
            => 'Tidy',
        'User who can connect to the database.'
            => 'Utente che posso connettere al database.',
        'User`s password.'
            => 'Password dell\'utente.',
        'version installed:'
            => 'versione installata:',
        'WARNING'
            => 'AVVISO',
        'WARNING! - If the database already exists the data will be completely removed. ' .
        'This must be checked if you are specifying an existing database.'
            => 'AVVISO!  Si esiste già il database, tutti i dati serano eliminati. ' .
               'Contrassegna questo box si specificate un database esistente.',
        'Welcome to Zurmo. Before getting started, we need some information on the database. ' .
        'You will need to know the following items before proceeding:'
            => 'Benvenuto a Zurmo.  Avanti di cominciare, è necessario ottenere informazione del vostro database.' .
               'È necessario avere LE informazioni seguenti:',
        'Writing Configuration File.'
            => 'Creazione del file di configurazione.',
        'Yii'
            => 'Yii',
        'You cannot access the installation area because the application is already installed.'
            => 'Non può accedere l\'area di installazione perche l\'applicazione è già installata.',
        'You have specified an existing database. If you would like to use this database, ' .
        'then do not specify the database admin username and password. Otherwise pick a ' .
        'database name that does not exist.'
            => 'Non avete specificato un database esistente.  Si volete utilizzare questo database, ' .
               'non specificato il nome di utente di database o la password di database.  Altrimenti, ' .
               'selezionate un nome di database che non esiste.',
        'You have specified an existing user. If you would like to use this user, then do ' .
        'not specify the database admin username and password. Otherwise pick a database ' .
        'username that does not exist.'
            => 'Avete specificato un nome di utente che esiste.  Si volete selezionare questo utente ' .
               'non specificate il nome di utente di database or la password di database.  Altrimenti, ' .
               'selezionate un nome di database che non esiste.',
        'Zurmo administrative password. The username is `super`.  You can change this later.'
            => 'Password amministrativa di Zurmo.  Il utente è `super`.  Si può cambiare questo più tardi.',
        'Zurmo Installation'
            => 'Installazione di Zurmo',
        '{folderPath} is missing.'
            => '{folderPath} mancante.',
        '{folderPath} is not writable.'
            => '{folderPath} non è scrivibile.',
    );
?>
