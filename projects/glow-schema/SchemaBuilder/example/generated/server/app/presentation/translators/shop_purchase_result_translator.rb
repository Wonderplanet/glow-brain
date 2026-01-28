class ShopPurchaseResultTranslator
  def self.translate(shop_purchase_result_model)
    view_model = ShopPurchaseResultViewModel.new
    view_model.received = shop_purchase_result_model.received
    view_model.shop_limited_purchase_history = shop_purchase_result_model.shop_limited_purchase_history
    view_model.updated_mission = shop_purchase_result_model.updated_mission
    view_model.updated_solo_stories = shop_purchase_result_model.updated_solo_stories
    view_model
  end
end
