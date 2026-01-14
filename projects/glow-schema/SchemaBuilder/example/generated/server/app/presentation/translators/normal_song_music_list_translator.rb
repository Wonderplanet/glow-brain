class NormalSongMusicListTranslator
  def self.translate(normal_song_music_list_model)
    view_model = NormalSongMusicListViewModel.new
    view_model.is_random = normal_song_music_list_model.is_random
    view_model.mst_music_ids = normal_song_music_list_model.mst_music_ids
    view_model
  end
end
