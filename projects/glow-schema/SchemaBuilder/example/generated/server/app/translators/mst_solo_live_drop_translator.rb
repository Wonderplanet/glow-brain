class MstSoloLiveDropTranslator
  def self.translate(mst_solo_live_drop_model)
    view_model = MstSoloLiveDropViewModel.new
    view_model.group_number = mst_solo_live_drop_model.group_number
    view_model.relative_probability = mst_solo_live_drop_model.relative_probability
    view_model.mst_item_id = mst_solo_live_drop_model.mst_item_id
    view_model.amount = mst_solo_live_drop_model.amount
    view_model
  end
end
