class MstMquestPstageSongTranslator
  def self.translate(mst_mquest_pstage_song_model)
    view_model = MstMquestPstageSongViewModel.new
    view_model.id = mst_mquest_pstage_song_model.id
    view_model.mst_main_quest_puzzle_stage_id = mst_mquest_pstage_song_model.mst_main_quest_puzzle_stage_id
    view_model.song_number = mst_mquest_pstage_song_model.song_number
    view_model.mst_music_id = mst_mquest_pstage_song_model.mst_music_id
    view_model
  end
end
