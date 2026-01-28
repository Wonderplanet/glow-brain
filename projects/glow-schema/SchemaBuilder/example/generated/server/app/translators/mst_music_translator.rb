class MstMusicTranslator
  def self.translate(mst_music_model)
    view_model = MstMusicViewModel.new
    view_model.id = mst_music_model.id
    view_model.track_number = mst_music_model.track_number
    view_model.title = mst_music_model.title
    view_model.bpm = mst_music_model.bpm
    view_model.artist_name = mst_music_model.artist_name
    view_model.composer_name = mst_music_model.composer_name
    view_model.arranger_name = mst_music_model.arranger_name
    view_model.lyric_writer_name = mst_music_model.lyric_writer_name
    view_model.mst_artist_group_id = mst_music_model.mst_artist_group_id
    view_model.asset_key = mst_music_model.asset_key
    view_model.effect_description = mst_music_model.effect_description
    view_model.effect_target = mst_music_model.effect_target
    view_model.hp_effect = mst_music_model.hp_effect
    view_model.performance_effect = mst_music_model.performance_effect
    view_model.heal_effect = mst_music_model.heal_effect
    view_model
  end
end
