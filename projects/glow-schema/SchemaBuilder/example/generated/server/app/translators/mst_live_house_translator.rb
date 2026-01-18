class MstLiveHouseTranslator
  def self.translate(mst_live_house_model)
    view_model = MstLiveHouseViewModel.new
    view_model.id = mst_live_house_model.id
    view_model.name = mst_live_house_model.name
    view_model.asset_key = mst_live_house_model.asset_key
    view_model.is_gorgeous = mst_live_house_model.is_gorgeous
    view_model.sort_number = mst_live_house_model.sort_number
    view_model
  end
end
