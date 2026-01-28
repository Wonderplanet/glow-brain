class MstArtistGroupCharacterTranslator
  def self.translate(mst_artist_group_character_model)
    view_model = MstArtistGroupCharacterViewModel.new
    view_model.mst_artist_group_id = mst_artist_group_character_model.mst_artist_group_id
    view_model.mst_character_id = mst_artist_group_character_model.mst_character_id
    view_model
  end
end
