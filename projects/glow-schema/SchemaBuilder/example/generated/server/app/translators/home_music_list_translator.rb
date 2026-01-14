class HomeMusicListTranslator
  def self.translate(home_music_list_model)
    view_model = HomeMusicListViewModel.new
    view_model.is_random = home_music_list_model.is_random
    view_model.mst_music_ids = home_music_list_model.mst_music_ids
    view_model
  end
end
