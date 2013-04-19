<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    // KEEP these in alphabetical order.
    // KEEP them indented correctly.
    // KEEP all the language files up-to-date with each other.
    // DON'T MAKE A MESS!
    return array(
        '<h2>Not so fast</h2><div class="large-icon"></div><p>Configure your email settings before you can send emails.</p>'
            => '<h2>Non così in fretta</h2></i><div class="large-icon"></div><p>È necessario configurare un indirizzo e-mail prima di inviare un e-mail.</p>',
        '<h2>Not so fast</h2><div class="large-icon"></div><p>The administrator must first configure the system outbound email settings.</p>'
            => '<h2>Non così in fretta</h2></i><div class="large-icon"></div><p>L\'amministratore deve configurare la posta in uscita.</p>',
        '<span class="email-from"><strong>From:</strong> {senderContent}</span>'
            => '<span class="email-da"><strong>Da:</strong> {senderContent}</span>',
        '<span class="email-to"><strong>To:</strong> {recipientContent}</span>'
            => '<span class="email-a"><strong>A:</strong> {recipientContent}</span>',
        'A test email address must be entered before you can send a test email.'
            => 'Si prega di inserire un indirizzo e-mail per inviare una email di prova.',
        'A test email from Zurmo'
            => 'Un messaggio di prova da Zurmo',
        'A test text message from Zurmo.'
            => 'Un messaggio SMS di prova da Zurmo.',
        'Access Email Configuration'
            => 'Accesso alla configurazione di mail',
        'Access Emails Tab'
            => 'Accesso ai Emails Tab',
        'Archived'
            => 'Archiviate',
        'Archived Unmatched'
            => 'Archiviate non trovate',
        'At least one archived email message does not match any records in the system. <a href="{url}">Click here</a> to manually match them.'
            => 'Al meno un messagio e-mail archiviate non corresponde ad alcun record nel sistema. href="{url}"> <a Clicca qui</ a> per abbinare manualmente.',
        'Bcc'
            => 'Ccn',
        'Bcc Recipients'
            => 'Destinatari Ccn',
        'Body'
            => 'Corpo',
        'Cc'
            => 'Cc', // Same Word Translated
        'Cc Recipients'
            => 'Destinatari Cc',
        'Clear Old Sent Notifications Email Job'
            => 'Cancellare le vecchie notificazioni inviati',
        'Compose Email'
            => 'Comporre email',
        'Could not connect to IMAP server.'
            => 'Impossibile connettersi al server IMAP.',
        'Create Emails'
            => 'Creare Emails',
        'Currently in the {folderType} folder'
            => 'Attualmente nella cartella {folderType}',
        'Data Cleanup'
            => 'Pulizia dei dati',
        'Delete Emails'
            => 'Cancellare Emails',
        'Draft'
            => 'Bozza',
        'Email address does not exist in system'
            => 'L\'indirizzo e-mail non esiste',
        'Email Archiving Configuration (IMAP)'
            => 'Connessione IMAP per archiviare mail',
        'Email Configuration'
            => 'Configurazione di mail',
        'Email configuration saved successfully.'
            => 'Configurazione salvata con successo.',
        'Email message could not be saved'
            => 'Il messaggio email non può essere salvato',
        'Email message could not be validated'
            => 'Il messaggio email non può essere convalidato',
        'Emails'
            => 'Emails', // Same Word Translated
        'Error Code:'
            => 'Codici d\'errore',
        'Error Message:'
            => 'Messaggio d\'errore',
        'Every 1 minute.'
            => 'Ogni minuto',
        'Extra Mail Settings'
            => 'Impostazioni avanzate',
        'Failed to connect to mailbox'
            => 'Problema di connessione al server di posta',
        'Folder'
            => 'Cartella',
        'From'
            => 'Da',
        'From Address'
            => 'Indirizzo email mittente',
        'From Name'
            => 'Nome mittente',
        'from: {senderContent}'
            => 'da: {senderContent}',
        'Html Content'
            => 'Contenuto HTML',
        'Inbox'
            => 'Posta in arrivo',
        'Invalid email address'
            => 'Indirizzo email non valido',
        'Manage Email Configuration'
            => 'Gestione email',
        'Manage your email preferences'
            => 'Gestire le proprie preferenze e-mail',
        'Match archived emails'
            => 'Collegare mails archiviate',
        'Matching archived emails requires access to either ContactsModulePluralLowerCaseLabel or LeadsModulePluralLowerCaseLabel both of which you do not have. Please contact your administrator.'
            => 'È necessario l\'accesso ai moduli ContactsModulePluralLowerCaseLabel o LeadsModulePluralLowerCaseLabel per collegare mail archiviati. Si prega di contattare l\'amministratore.',
        'Message failed to send'
            => 'Messaggio non inviato',
        'Message successfully sent'
            => 'Messaggio inviato con successo',
        'Missing Rights'
            => 'Diritti mancanti',
        'Outbound Email Configuration (SMTP)'
            => 'Server di posta in uscita (SMTP)',
        'Outbound email configuration saved successfully.'
            => 'Server di posta in uscita (SMTP) salvato.',
        'Outbox'
            => 'Posta in uscita',
        'Outbox Error'
            => 'Errore con la posta in uscita',
        'Person'
            => 'Persona',
        'Person Or Account'
            => 'Person Or Account', // Same Word Translated
        'Process Inbound Email Job'
            => 'Elabora mail in entrata',
        'Process Outbound Email Job'
            => 'Inizia il processo de posta in uscita',
        'Recipient info can\'t be extracted from email message'
            => 'L\'informazione del destinatario non potrà essere estratto del messaggio',
        'Recipients'
            => 'Destinatari',
        'Response from Server'
            => 'Risposta del server',
        'Select ContactsModuleSingularLabel'
            => 'Selezionare ContactsModuleSingularLabel',
        'Select ContactsModuleSingularLabel / LeadsModuleSingularLabel'
            => 'Selezionare ContactsModuleSingularLabel / LeadsModuleSingularLabel',
        'Select LeadsModuleSingularLabel'
            => 'Selezionare LeadsModuleSingularLabel',
        'Send'
            => 'Inviare',
        'Send system notifications from'
            => 'Invia notificazioni dall\'utente',
        'Send Test Email'
            => 'Invia email di test',
        'Sender info can\'t be extracted from email message'
            => 'L\'informazione del mittente non potrà essere estratto del messaggio',
        'Sent'
            => 'Inviata',
        'SSL connection'
            => 'Connessione protetta (SSL)',
        'Successfully connected to IMAP server.'
            => 'Connesso al server IMAP con successo.',
        'Test IMAP connection'
            => 'Prova di connessione IMAP',
        'Test Message Results'
            => 'Risultati del messaggio di prova',
        'Testing Outbound Email Connection Job'
            => 'Prova Server di posta in uscita (SMTP)',
        'Text Content'
            => 'Messaggio',
        'There is no primary email associated with {contactName}. Please add one to continue.'
            => 'Nessuna e-mail primario associato a {contactName}. Si prega di aggiungerne uno per continuare.',
        'This field is required'
            => 'Questo campo è obbligatorio.',
        'This message sent from Zurmo'
            => 'Messaggio inviato da Zurmo',
        'To'
            => 'A',
        'To Address'
            => 'Indirizzo Destinatario',
        'To address cannot be blank'
            => 'L\'indirizzo destinatario non può essere vuoto',
        'To Name'
            => 'Nome Destinatario',
        'To Recipients'
            => 'Ai Destinatari',
        'to: {recipientContent}'
            => 'a: {recipientContent}',
        'Type name or email'
            => 'Inserire nome o email',
        'Unmatched Archived Emails'
            => 'Mails Archiviate Non Collegati',
        'You do not have rights to access, create, or connect emails in the system'
            => 'Non si dispone di diritti di accesso, creare o collegare e-mails nel sistema',
        'Zurmo sends out system notifications.  The notifications must appear as coming from a super administrative user.'
            => 'Notificazioni del sistema.  Le notificazioni devono mandare di un utente super admin.',
    );
?>
