class OprItemTranslator
  def self.translate(opr_item_model)
    view_model = OprItemViewModel.new
    view_model.mst_item_id = opr_item_model.mst_item_id
    view_model.expire_at = opr_item_model.expire_at
    view_model
  end
end
