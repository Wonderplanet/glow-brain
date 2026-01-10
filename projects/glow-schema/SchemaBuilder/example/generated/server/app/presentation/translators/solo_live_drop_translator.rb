class SoloLiveDropTranslator
  def self.translate(solo_live_drop_model)
    view_model = SoloLiveDropViewModel.new
    view_model.mst_item_id = solo_live_drop_model.mst_item_id
    view_model.amount = solo_live_drop_model.amount
    view_model
  end
end
