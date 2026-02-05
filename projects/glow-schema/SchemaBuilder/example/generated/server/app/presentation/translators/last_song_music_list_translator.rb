class LastSongMusicListTranslator
  def self.translate(last_song_music_list_model)
    view_model = LastSongMusicListViewModel.new
    view_model.is_random = last_song_music_list_model.is_random
    view_model.enabled = last_song_music_list_model.enabled
    view_model.mst_music_ids = last_song_music_list_model.mst_music_ids
    view_model
  end
end
