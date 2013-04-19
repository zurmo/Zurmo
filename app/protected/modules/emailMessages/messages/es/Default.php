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
            => '<h2>No tan rápido</h2></i><div class="large-icon"></div><p>Configurar los ajustes de email antes de enviar mensajes de correo electrónico.</p>',
        '<h2>Not so fast</h2><div class="large-icon"></div><p>The administrator must first configure the system outbound email settings.</p>'
            => '<h2>No tan rápido</h2></i><div class="large-icon"></div><p>El administrador debe configurar los ajustes del sistema de correo electrónico salientes.</p>',
        '<span class="email-from"><strong>From:</strong> {senderContent}</span>'
            => '<span class="email-de"><strong>De:</strong> {senderContent}</span>',
        '<span class="email-to"><strong>To:</strong> {recipientContent}</span>'
            => '<span class="email-para"><strong>Para:</strong> {recipientContent}</span>',
        'A test email address must be entered before you can send a test email.'
            => 'Por favor, introduzca una dirección de email.',
        'A test email from Zurmo'
            => 'Un mensaje de prueba de  Zurmo',
        'A test text message from Zurmo.'
            => 'Un mensaje de texto de Zurmo.',
        'Access Email Configuration'
            => 'Acceso a la configuración email',
        'Access Emails Tab'
            => 'Acceso a Emails',
        'Archived'
            => 'Elemento archivado',
        'Archived Unmatched'
            => 'Elemento archivado inigualable',
        'At least one archived email message does not match any records in the system. <a href="{url}">Click here</a> to manually match them.'
            => 'Por lo menos un email mensaje archivado no han encontrado registros en el sistema. <a href="{url}">Haga clic aquí</a> para igualar manualmente.',
        'Bcc'
            => 'CCO',
        'Bcc Recipients'
            => 'Destinatarios CCO',
        'Body'
            => 'Cuerpo',
        'Cc'
            => 'Cc', // Same Word Translated
        'Cc Recipients'
            => 'Destinatarios de CC',
        'Clear Old Sent Notifications Email Job'
            => 'Eliminar mensajes de notificación viejos',
        'Compose Email'
            => 'Componer Email',
        'Could not connect to IMAP server.'
            => 'Verificación de la cuenta IMAP ha fallado.',
        'Create Emails'
            => 'Crear Emails',
        'Currently in the {folderType} folder'
            => 'Actualmente en la carpeta {folderType}',
        'Data Cleanup'
            => 'Limpieza de datos',
        'Delete Emails'
            => 'Eliminar Emails',
        'Draft'
            => 'Borrador ',
        'Email address does not exist in system'
            => 'La dirección de email no existe',
        'Email Archiving Configuration (IMAP)'
            => 'Configuración de la cuenta para los emails archivados (IMAP)',
        'Email Configuration'
            => 'Configuraciones de Email',
        'Email configuration saved successfully.'
            => 'Configuración de email correctamente guardado.',
        'Email message could not be saved'
            => 'Mensaje de Email no se pudo guardar',
        'Email message could not be validated'
            => 'Mensaje de Email no se pudo validar',
        'Emails'
            => 'Emails', // Same Word Translated
        'Error Code:'
            => 'Código de error:',
        'Error Message:'
            => 'Mensaje de error:',
        'Every 1 minute.'
            => 'Cada minuto.',
        'Extra Mail Settings'
            => 'Configuración avanzada',
        'Failed to connect to mailbox'
            => 'Error al conectarse al buzón de correo',
        'Folder'
            => 'Carpeta',
        'From'
            => 'De',
        'From Address'
            => 'Dirección del remitente',
        'From Name'
            => 'Nombre del remitente',
        'from: {senderContent}'
            => 'de: {senderContent}',
        'Html Content'
            => 'Contenido HTML',
        'Inbox'
            => 'Buzón de entrada',
        'Invalid email address'
            => 'Dirección de email no válida',
        'Manage Email Configuration'
            => 'Configuración del email',
        'Manage your email preferences'
            => 'Administrar sus preferencias de email',
        'Match archived emails'
            => 'Coincidir email archivados',
        'Matching archived emails requires access to either ContactsModulePluralLowerCaseLabel or LeadsModulePluralLowerCaseLabel both of which you do not have. Please contact your administrator.'
            => 'Es necesario tener acceso a los módulos LeadsModulePluralLowerCaseLabel o ContactsModulePluralLowerCaseLabel para vincular correos archivados. Por favor, póngase en contacto con el administrador.',
        'Message failed to send'
            => 'Mensaje no enviado',
        'Message successfully sent'
            => 'Mensaje enviado con éxito',
        'Missing Rights'
            => 'Derechos que faltan',
        'Outbound Email Configuration (SMTP)'
            => 'Servidor de salida (SMTP)',
        'Outbound email configuration saved successfully.'
            => 'Servidor de salida (SMTP) ha guardado correctamente.',
        'Outbox'
            => 'Buzón de salida',
        'Outbox Error'
            => 'Error de buzón de salida',
        'Person'
            => 'Nombre',
        'Person Or Account'
            => 'Person Or Account', // Same Word Translated
        'Process Inbound Email Job'
            => 'Procesar el trabajo de email entrante',
        'Process Outbound Email Job'
            => 'Inicie el proceso de correo saliente',
        'Recipient info can\'t be extracted from email message'
            => 'Información del destinatario no se puede extraer del email',
        'Recipients'
            => 'Destinatarios',
        'Response from Server'
            => 'Respuesta del Servidor',
        'Select ContactsModuleSingularLabel'
            => 'Seleccionar ContactsModuleSingularLabel',
        'Select ContactsModuleSingularLabel / LeadsModuleSingularLabel'
            => 'Seleccionar ContactsModuleSingularLabel / LeadsModuleSingularLabel',
        'Select LeadsModuleSingularLabel'
            => 'Seleccionar LeadsModuleSingularLabel',
        'Send'
            => 'Enviar',
        'Send system notifications from'
            => 'Enviar notificaciones del sistema de',
        'Send Test Email'
            => 'Enviar un mensaje de prueba',
        'Sender info can\'t be extracted from email message'
            => 'Información del remitente no se puede extraer del Email',
        'Sent'
            => 'Mensaje enviado.',
        'SSL connection'
            => 'Conexión SSL',
        'Successfully connected to IMAP server.'
            => 'Conectado correctamente al servidor IMAP.',
        'Test IMAP connection'
            => 'Prueba conexión IMAP',
        'Test Message Results'
            => 'Resultados del mensaje de prueba',
        'Testing Outbound Email Connection Job'
            => 'Los parámetros de prueba para la cuenta de email',
        'Text Content'
            => 'Mensaje',
        'There is no primary email associated with {contactName}. Please add one to continue.'
            => 'No hay ninguna principal de correo electrónico asociada a {contactName}. Por favor, añada una para continuar.',
        'This field is required'
            => 'Este campo es obligatorio',
        'This message sent from Zurmo'
            => 'Este mensaje enviado desde Zurmo',
        'To'
            => 'Para',
        'To Address'
            => 'Dirección de destino',
        'To address cannot be blank'
            => 'La dirección de destino no puede estar en blanco',
        'To Name'
            => 'Nombre del destinatario',
        'To Recipients'
            => 'Para destinatarios',
        'to: {recipientContent}'
            => 'Para: {recipientContent}',
        'Type name or email'
            => 'Escriba el nombre o dirección de correo electrónico',
        'Unmatched Archived Emails'
            => 'Inigualables Emails Archivados',
        'You do not have rights to access, create, or connect emails in the system'
            => 'No tiene derechos de acceso, crear o vincular los emails en el sistema',
        'Zurmo sends out system notifications.  The notifications must appear as coming from a super administrative user.'
            => 'Zurmo envía notificaciones del sistema. Las notificaciones deben aparecer como provenientes de un usuario de super administrator.',
    );
?>
