class MstArtistGroupTranslator
  def self.translate(mst_artist_group_model)
    view_model = MstArtistGroupViewModel.new
    view_model.id = mst_artist_group_model.id
    view_model.name = mst_artist_group_model.name
    view_model.asset_key = mst_artist_group_model.asset_key
    view_model
  end
end
