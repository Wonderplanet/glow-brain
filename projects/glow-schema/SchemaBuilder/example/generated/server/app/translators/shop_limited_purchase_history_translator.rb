class ShopLimitedPurchaseHistoryTranslator
  def self.translate(shop_limited_purchase_history_model)
    view_model = ShopLimitedPurchaseHistoryViewModel.new
    view_model.opr_shop_id = shop_limited_purchase_history_model.opr_shop_id
    view_model.count = shop_limited_purchase_history_model.count
    view_model
  end
end
