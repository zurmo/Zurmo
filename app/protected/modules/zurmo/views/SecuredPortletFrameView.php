<?php
    /**
     * Extended class to handle security considerations for rights/permissions for the current user.
     */
    class SecuredPortletFrameView extends PortletFrameView
    {
        protected function getPortlets($uniqueLayoutId, $metadata)
        {
            assert('is_string($uniqueLayoutId)');
            assert('is_array($metadata)');
            $portlets = parent::getPortlets($uniqueLayoutId, $metadata);
            return PortletsSecurityUtil::resolvePortletsForCurrentUser($portlets);
        }
    }
?>