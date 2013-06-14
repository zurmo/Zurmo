/**
 * Copy the product template data for creation of product
 */
function copyProductTemplateDataForProduct(templateId, url)
{
    url = url + "?id=" + templateId;
    $.ajax(
        {
            type: 'GET',
            url: url,
            dataType: 'json',
            success: function(data)
                     {
                         $("#ProductCategoriesForm_ProductCategory_ids").tokenInput("clear");
                         $(data.categoryOutput).each(function(index)
                         {
                            $("#ProductCategoriesForm_ProductCategory_ids").tokenInput("add", {id: this.id, name: this.name});
                         });
                         $('#Product_type_value').val(data.productType);
                         $('#Product_priceFrequency_value').val(data.productPriceFrequency);
                         $('#Product_sellPrice_currency_id').val(data.productSellPriceCurrency);
                         $('#Product_sellPrice_value').val(data.productSellPriceValue);
                         $('#Product_name').val(data.productName);
                     }
        }
    );
}

/**
 * Adds the product row to the product portlet on details view
 */
function addProductRowToPortletGridView(productTemplateId, url, relationAttributeName, relationModelId, uniquePortletPageId, errorInProcess)
{
    url = url + "&id=" + productTemplateId + "&relationModelId=" + relationModelId + "&relationAttributeName=" + relationAttributeName;
    $.ajax(
        {
            type: 'GET',
            url: url,
            beforeSend: function(xhr)
                       {
                           $('#modalContainer').html('');
                           $(this).makeLargeLoadingSpinner(true, '#modalContainer');
                       },
            success: function(dataOrHtml, textStatus, xmlReq)
                     {
                         $(this).processAjaxSuccessUpdateHtmlOrShowDataOnFailure(dataOrHtml, uniquePortletPageId);
                     },
            complete:function(XMLHttpRequest, textStatus)
                     {
                       $('#modalContainer').dialog('close');
                       //$('#product_opportunity_name').val('');
                       //$('#product_opportunity_id').val('');
                       //$('#product-configuration-form').hide('slow');
                       //juiPortlets.refresh();
                     },
            error:function(xhr, textStatus, errorThrown)
                  {
                      alert(errorInProcess);
                  }
        }
    );
}