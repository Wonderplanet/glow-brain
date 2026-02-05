class OprEventSquestPstageSongTranslator
  def self.translate(opr_event_squest_pstage_song_model)
    view_model = OprEventSquestPstageSongViewModel.new
    view_model.id = opr_event_squest_pstage_song_model.id
    view_model.opr_event_special_quest_puzzle_stage_id = opr_event_squest_pstage_song_model.opr_event_special_quest_puzzle_stage_id
    view_model.song_number = opr_event_squest_pstage_song_model.song_number
    view_model.mst_music_id = opr_event_squest_pstage_song_model.mst_music_id
    view_model
  end
end
